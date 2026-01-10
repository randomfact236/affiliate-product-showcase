<?php

declare(strict_types=1);

namespace AffiliateProductShowcase\Tests\Unit\Assets;

use AffiliateProductShowcase\Assets\Manifest;
use AffiliateProductShowcase\Assets\SRI;
use Brain\Monkey\Functions;
use function Brain\Monkey\setUp;
use function Brain\Monkey\tearDown;
use PHPUnit\Framework\TestCase;
use ReflectionClass;
use WP_Error;

final class SRITest extends TestCase {
	private string $tempDir;
	/** @var array<string, string> */
	private array $transients = [];

	protected function setUp(): void {
		parent::setUp();
		setUp();

		$this->tempDir = sys_get_temp_dir() . '/aps-sri-' . uniqid( '', true );
		mkdir( $this->tempDir, 0777, true );

		Functions::when( 'wp_cache_get' )->justReturn( false );
		Functions::when( 'wp_cache_set' )->justReturn( true );
		Functions::when( 'esc_url_raw' )->alias( static fn ( $url ) => $url );
		Functions::when( 'sanitize_text_field' )->alias( static fn ( $value ) => (string) $value );
		Functions::when( 'trailingslashit' )->alias( static fn ( $value ) => rtrim( (string) $value, '/\\' ) . '/' );
		Functions::when( 'sanitize_title_with_dashes' )->alias( static fn ( $value ) => preg_replace( '/[^a-z0-9\-]/i', '-', strtolower( (string) $value ) ) ?? '' );

		Functions::when( 'get_transient' )->alias( function ( string $key ) {
			return $this->transients[ $key ] ?? false;
		} );

		Functions::when( 'set_transient' )->alias( function ( string $key, $value, int $expiration = 0 ): bool {
			$this->transients[ $key ] = $value;
			return true;
		} );
	}

	protected function tearDown(): void {
		tearDown();
		$this->removeTempDir();
		$this->transients = [];
		Manifest::reset_instance();
		parent::tearDown();
	}

	public function test_generate_hash_and_verify(): void {
		$assetPath = $this->tempDir . '/bundle.js';
		file_put_contents( $assetPath, 'alert("hash");' );

		$manifestFile = $this->tempDir . '/manifest.json';
		file_put_contents( $manifestFile, wp_json_encode( [ 'bundle.js' => [ 'file' => 'bundle.js' ] ] ) );

		$manifest = Manifest::get_instance();
		$this->primeManifestPaths( $manifest, $manifestFile );
		$sri = new SRI( $manifest, 60 );
		$manifest->set_sri( $sri );

		$hash = $sri->generate_hash( $assetPath );
		$this->assertIsString( $hash );
		$this->assertStringStartsWith( 'sha384-', $hash );
		$this->assertTrue( $sri->verify_hash( $assetPath, $hash ) );
		$this->assertFalse( $sri->verify_hash( $assetPath, 'sha384-invalid' ) );
	}

	public function test_integrity_attribute_uses_transient_cache(): void {
		$assetPath = $this->tempDir . '/frontend.js';
		file_put_contents( $assetPath, 'console.log("integrity");' );
		$manifestFile = $this->tempDir . '/manifest.json';
		file_put_contents( $manifestFile, wp_json_encode( [ 'frontend.js' => [ 'file' => 'frontend.js' ] ] ) );

		$manifest = Manifest::get_instance();
		$this->primeManifestPaths( $manifest, $manifestFile );
		$sri = new SRI( $manifest, 60 );
		$manifest->set_sri( $sri );

		$first = $sri->get_integrity_attribute( 'frontend.js' );
		$this->assertNotSame( '', $first );

		$second = $sri->get_integrity_attribute( 'frontend.js' );
		$this->assertSame( $first, $second );
	}

	public function test_integrity_attribute_returns_empty_on_error(): void {
		$manifest = Manifest::get_instance();
		$this->primeManifestPaths( $manifest, $this->tempDir . '/missing.json' );
		$sri = new SRI( $manifest, 60 );
		$manifest->set_sri( $sri );

		$result = $sri->get_integrity_attribute( 'does-not-exist.js' );
		$this->assertSame( '', $result );
	}

	private function primeManifestPaths( Manifest $manifest, string $manifestFile ): void {
		$this->setPrivateProperty( $manifest, 'manifest_path', $manifestFile );
		$this->setPrivateProperty( $manifest, 'dist_path', $this->tempDir . '/' );
		$this->setPrivateProperty( $manifest, 'dist_url', 'https://example.com/assets/dist/' );
		$this->setPrivateProperty( $manifest, 'manifest', [] );
	}

	private function setPrivateProperty( object $object, string $property, $value ): void {
		$ref = new ReflectionClass( $object );
		$prop = $ref->getProperty( $property );
		$prop->setAccessible( true );
		$prop->setValue( $object, $value );
	}

	private function removeTempDir(): void {
		if ( ! is_dir( $this->tempDir ) ) {
			return;
		}

		$files = new \RecursiveIteratorIterator(
			new \RecursiveDirectoryIterator( $this->tempDir, \FilesystemIterator::SKIP_DOTS ),
			\RecursiveIteratorIterator::CHILD_FIRST
		);

		foreach ( $files as $file ) {
			$file->isDir() ? rmdir( $file->getRealPath() ) : unlink( $file->getRealPath() );
		}

		rmdir( $this->tempDir );
	}
}
