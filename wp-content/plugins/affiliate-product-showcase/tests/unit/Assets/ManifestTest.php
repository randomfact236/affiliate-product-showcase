<?php

declare(strict_types=1);

namespace AffiliateProductShowcase\Tests\Unit\Assets;

use AffiliateProductShowcase\Assets\Manifest;
use Brain\Monkey\Functions;
use function Brain\Monkey\setUp;
use function Brain\Monkey\tearDown;
use PHPUnit\Framework\TestCase;
use ReflectionClass;
use WP_Error;

final class ManifestTest extends TestCase {
	private string $tempDir;

	protected function setUp(): void {
		parent::setUp();
		setUp();

		$this->tempDir = sys_get_temp_dir() . '/aps-manifest-' . uniqid( '', true );
		mkdir( $this->tempDir, 0777, true );

		Functions::when( 'wp_cache_get' )->justReturn( false );
		Functions::when( 'wp_cache_set' )->justReturn( true );
		Functions::when( 'esc_url_raw' )->alias( static fn ( $url ) => $url );
		Functions::when( 'sanitize_text_field' )->alias( static fn ( $value ) => (string) $value );
		Functions::when( 'trailingslashit' )->alias( static fn ( $value ) => rtrim( (string) $value, '/\\' ) . '/' );
		Functions::when( 'sanitize_title_with_dashes' )->alias( static fn ( $value ) => preg_replace( '/[^a-z0-9\-]/i', '-', strtolower( (string) $value ) ) ?? '' );
	}

	protected function tearDown(): void {
		tearDown();
		$this->removeTempDir();
		Manifest::reset_instance();
		parent::tearDown();
	}

	public function test_load_manifest_and_get_asset(): void {
		$manifestFile = $this->tempDir . '/manifest.json';
		$assetPath    = $this->tempDir . '/frontend.js';

		file_put_contents( $manifestFile, wp_json_encode( [ 'frontend.js' => [ 'file' => 'frontend.js' ] ] ) );
		file_put_contents( $assetPath, 'console.log("ok");' );

		$manifest = Manifest::get_instance();
		$this->setPrivateProperty( $manifest, 'manifest_path', $manifestFile );
		$this->setPrivateProperty( $manifest, 'dist_path', $this->tempDir . '/' );
		$this->setPrivateProperty( $manifest, 'dist_url', 'https://example.com/assets/dist/' );
		$this->setPrivateProperty( $manifest, 'manifest', [] );

		$result = $manifest->load_manifest();
		$this->assertTrue( $result );

		$asset = $manifest->get_asset( 'frontend.js' );
		$this->assertIsArray( $asset );
		$this->assertSame( 'https://example.com/assets/dist/frontend.js', $asset['url'] );
		$this->assertNotEmpty( $asset['version'] );
	}

	public function test_invalid_key_returns_error(): void {
		$manifestFile = $this->tempDir . '/manifest.json';
		file_put_contents( $manifestFile, wp_json_encode( [ 'frontend.js' => [ 'file' => 'frontend.js' ] ] ) );

		$manifest = Manifest::get_instance();
		$this->setPrivateProperty( $manifest, 'manifest_path', $manifestFile );
		$this->setPrivateProperty( $manifest, 'dist_path', $this->tempDir . '/' );
		$this->setPrivateProperty( $manifest, 'dist_url', 'https://example.com/assets/dist/' );
		$this->setPrivateProperty( $manifest, 'manifest', [] );

		$result = $manifest->get_asset( '../bad-key' );
		$this->assertInstanceOf( WP_Error::class, $result );
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
