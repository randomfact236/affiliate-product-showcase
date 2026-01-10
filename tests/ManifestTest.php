<?php

use PHPUnit\Framework\TestCase;
use Brain\Monkey;
use Brain\Monkey\Functions;
use org\bovigo\vfs\vfsStream;
use org\bovigo\vfs\vfsStreamDirectory;

use AffiliateProductShowcase\Assets\Manifest;
use AffiliateProductShowcase\Assets\SRI as SRIClass;

class ManifestTest extends TestCase
{
    private vfsStreamDirectory $root;

    protected function setUp(): void
    {
        parent::setUp();
        Monkey\setUp();

        $this->root = vfsStream::setup('root');
    }

    protected function tearDown(): void
    {
        Monkey\tearDown();
        parent::tearDown();
    }

    private function makeManifestInstance(string $manifestJson): Manifest
    {
        // create virtual dist and manifest
        $dist = vfsStream::newDirectory('assets/dist')->at($this->root);
        vfsStream::newFile('manifest.json')->at($dist)->setContent($manifestJson);
        // add referenced file
        vfsStream::newDirectory('js')->at($dist)->addChild(vfsStream::newFile('admin.js')->setContent("console.log('ok');"));

        // instantiate Manifest without running constructor and set private props
        $ref = new ReflectionClass(Manifest::class);
        $inst = $ref->newInstanceWithoutConstructor();

        $manifestPath = $dist->url() . '/manifest.json';
        $distPath = $dist->url() . '/';
        $distUrl = 'https://example.test/assets/dist/';

        $prop = $ref->getProperty('manifest_path');
        $prop->setAccessible(true);
        $prop->setValue($inst, $manifestPath);

        $prop = $ref->getProperty('dist_path');
        $prop->setAccessible(true);
        $prop->setValue($inst, $dist->url() . '/');

        $prop = $ref->getProperty('dist_url');
        $prop->setAccessible(true);
        $prop->setValue($inst, $distUrl);

        return $inst;
    }

    public function test_load_manifest_valid_json_caches_and_get_asset()
    {
        $json = json_encode([
            'admin.js' => ['file' => 'js/admin.js']
        ]);

        $man = $this->makeManifestInstance($json);

        // Expect cache miss first
            Functions\expect('wp_cache_get')->once()->andReturn(false);
            Functions\when('wp_cache_set')->justReturn(true);

        $res = $man->load_manifest();
        $this->assertTrue($res, 'load_manifest should return true for valid manifest');

        // get_asset should return url and version
        // stub esc_url_raw and sanitize_text_field used inside get_asset
        Functions\expect('esc_url_raw')->andReturnUsing(function($u){ return $u; });
        Functions\expect('sanitize_text_field')->andReturnUsing(function($v){ return (string)$v; });

        $asset = $man->get_asset('admin.js');
        $this->assertIsArray($asset);
        $this->assertArrayHasKey('url', $asset);
        $this->assertArrayHasKey('version', $asset);
        $this->assertStringContainsString('admin.js', $asset['url']);
        $this->assertMatchesRegularExpression('/^\d+$/', $asset['version']);

        // calling load_manifest again should hit cache; prepare cached value
        $cached = ['admin.js' => ['file' => 'js/admin.js']];
        Functions\expect('wp_cache_get')->once()->andReturn($cached);

        // create a fresh instance to trigger cache path
        $man2 = $this->makeManifestInstance($json);
        $this->assertTrue($man2->load_manifest());
    }

    public function test_load_manifest_invalid_json_returns_wp_error()
    {
        $bad = "{ this is not: valid json }";
        $man = $this->makeManifestInstance($bad);

        Functions\expect('wp_cache_get')->once()->andReturn(false);

        $res = $man->load_manifest();
        $this->assertTrue(function_exists('is_wp_error'));
        $this->assertTrue(is_wp_error($res));
        $this->assertInstanceOf(WP_Error::class, $res);
        $this->assertStringContainsString('invalid JSON', $res->get_error_message() ?? '');
    }

    public function test_get_asset_missing_entry_returns_wp_error()
    {
        $json = json_encode([
            'other.js' => ['file' => 'js/other.js']
        ]);
        $man = $this->makeManifestInstance($json);
            Functions\expect('wp_cache_get')->andReturn(false);
            Functions\when('wp_cache_set')->justReturn(true);
        $man->load_manifest();

        $res = $man->get_asset('admin.js');
        $this->assertTrue(is_wp_error($res));
        $this->assertInstanceOf(WP_Error::class, $res);
    }

    public function test_get_asset_referenced_file_missing_returns_wp_error()
    {
        $json = json_encode([
            'admin.js' => ['file' => 'js/missing.js']
        ]);
        $man = $this->makeManifestInstance($json);
            Functions\expect('wp_cache_get')->andReturn(false);
            Functions\when('wp_cache_set')->justReturn(true);
        $man->load_manifest();

        $res = $man->get_asset('admin.js');
        $this->assertTrue(is_wp_error($res));
        $this->assertInstanceOf(WP_Error::class, $res);
    }

    public function test_enqueue_script_registers_and_adds_integrity()
    {
        $json = json_encode([
            'admin.js' => ['file' => 'js/admin.js']
        ]);
        $man = $this->makeManifestInstance($json);

            Functions\expect('wp_cache_get')->andReturn(false);
            Functions\expect('wp_cache_set')->andReturn(true);
            $this->assertTrue($man->load_manifest());

            // stub asset helpers
            Functions\expect('esc_url_raw')->andReturnUsing(function($u){ return $u; });
            Functions\expect('sanitize_text_field')->andReturnUsing(function($v){ return (string)$v; });
            Functions\expect('sanitize_title_with_dashes')->andReturnUsing(function($v){ return (string)$v; });

            // Create a real SRI instance (no constructor side-effects) and attach to manifest
            $sriRef = new ReflectionClass(SRIClass::class);
            $sriInst = $sriRef->newInstanceWithoutConstructor();
            // provide a simple get_integrity_attribute method via closure binding isn't possible on final class,
            // so instead instantiate a real SRI with the manifest when possible
            try {
                $realSRI = new SRIClass($man);
                $man->set_sri($realSRI);
            } catch (\Throwable $e) {
                // If the real SRI cannot be instantiated in this environment, fall back to skipping integrity assertions
                $man->set_sri(null);
            }

            // Expect registration and enqueue calls
            $called = [];
            Functions\expect('wp_register_script')->once()->andReturnUsing(function($handle, $url, $deps, $ver, $in_footer) use (&$called) {
                $called['register'] = compact('handle','url','deps','ver','in_footer');
                return true;
            });

            Functions\expect('wp_script_add_data')->times(2)->andReturnUsing(function($handle, $key, $value) use (&$called){
                $called['script_add_data'][] = compact('handle','key','value');
                return true;
            });

            Functions\expect('wp_enqueue_script')->once()->andReturnUsing(function($handle) use (&$called){
                $called['enqueue'] = $handle;
                return true;
            });

            $result = $man->enqueue_script('aps-admin', 'admin.js', [], true);
        $this->assertTrue($result);
        $this->assertArrayHasKey('register', $called);
        $this->assertArrayHasKey('enqueue', $called);
        $this->assertNotEmpty($called['script_add_data']);
        $this->assertStringContainsString('sha384-', $called['script_add_data'][0]['value']);
    }
}
