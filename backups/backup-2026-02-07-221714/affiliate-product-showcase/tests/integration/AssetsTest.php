<?php

declare(strict_types=1);

namespace AffiliateProductShowcase\Tests\Integration;

use AffiliateProductShowcase\Assets\Assets;
use AffiliateProductShowcase\Assets\Manifest;
use AffiliateProductShowcase\Assets\SRI;
use Brain\Monkey\Functions;
use function Brain\Monkey\setUp;
use function Brain\Monkey\tearDown;
use PHPUnit\Framework\TestCase;
use ReflectionClass;

final class AssetsTest extends TestCase {
	private string $tempDir;
	/** @var array<string, array<string, mixed>> */
	private array $scripts = [];
	/** @var array<string, array<string, mixed>> */
	private array $styles = [];
	/** @var array<string, array<string, mixed>> */
	private array $scriptData = [];
	/** @var array<string, array<string, mixed>> */
	private array $styleData = [];
	/** @var array<string, string> */
	private array $transients = [];

	protected function setUp(): void {
		parent::setUp();
		setUp();

		$this->tempDir = sys_get_temp_dir() . '/aps-assets-int-' . uniqid( '', true );
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

		Functions::when( 'wp_register_script' )->alias( function ( $handle, $src, $deps, $ver, $in_footer ) {
			$this->scripts[ $handle ] = [
				'src'       => $src,
				'deps'      => $deps,
				'version'   => $ver,
				'in_footer' => $in_footer,
			];
			return true;
		} );

		Functions::when( 'wp_enqueue_script' )->alias( function ( $handle ) {
			$this->scripts[ $handle ]['enqueued'] = true;
			return true;
		} );

		Functions::when( 'wp_script_add_data' )->alias( function ( $handle, $key, $value ) {
			$this->scriptData[ $handle ][ $key ] = $value;
			return true;
		} );

		Functions::when( 'wp_register_style' )->alias( function ( $handle, $src, $deps, $ver, $media ) {
			$this->styles[ $handle ] = [
				'src'     => $src,
				'deps'    => $deps,
				'version' => $ver,
				'media'   => $media,
			];
			return true;
		} );

		Functions::when( 'wp_enqueue_style' )->alias( function ( $handle ) {
			$this->styles[ $handle ]['enqueued'] = true;
			return true;
		} );

		Functions::when( 'wp_style_add_data' )->alias( function ( $handle, $key, $value ) {
			$this->styleData[ $handle ][ $key ] = $value;
			return true;
		} );
	}

	protected function tearDown(): void {
		tearDown();
		$this->removeTempDir();
		$this->scripts     = [];
		$this->styles      = [];
		$this->scriptData  = [];
		$this->styleData   = [];
		$this->transients  = [];
		Manifest::reset_instance();
		parent::tearDown();
	}

	public function test_enqueue_frontend_with_sri(): void {
		$manifestFile = $this->tempDir . '/manifest.json';
		file_put_contents(
			$manifestFile,
			wp_json_encode(
				[
					'frontend.js'  => [ 'file' => 'frontend.js' ],
					'frontend.css' => [ 'file' => 'frontend.css' ],
				]
			)
		);

		file_put_contents( $this->tempDir . '/frontend.js', 'console.log("frontend");' );
		file_put_contents( $this->tempDir . '/frontend.css', 'body { color: #000; }' );

		$manifest = Manifest::get_instance();
		$this->setPrivateProperty( $manifest, 'manifest_path', $manifestFile );
		$this->setPrivateProperty( $manifest, 'dist_path', $this->tempDir . '/' );
		$this->setPrivateProperty( $manifest, 'dist_url', 'https://example.com/assets/dist/' );
		$this->setPrivateProperty( $manifest, 'manifest', [] );

		$sri = new SRI( $manifest, 120 );
		$manifest->set_sri( $sri );
		$assets = new Assets( $manifest );

		$assets->enqueue_frontend();

		$this->assertArrayHasKey( 'aps-frontend', $this->scripts );
		$this->assertArrayHasKey( 'aps-frontend-style', $this->styles );
		$this->assertTrue( $this->scripts['aps-frontend']['enqueued'] );
		$this->assertTrue( $this->styles['aps-frontend-style']['enqueued'] );
		$this->assertArrayHasKey( 'integrity', $this->scriptData['aps-frontend'] );
		$this->assertArrayHasKey( 'crossorigin', $this->scriptData['aps-frontend'] );
		$this->assertStringStartsWith( 'sha384-', $this->scriptData['aps-frontend']['integrity'] );
		$this->assertSame( 'anonymous', $this->scriptData['aps-frontend']['crossorigin'] );
		$this->assertArrayHasKey( 'integrity', $this->styleData['aps-frontend-style'] );
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
