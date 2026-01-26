<?php
declare(strict_types=1);

namespace AffiliateProductShowcase\Tests;

use PHPUnit\Framework\TestCase;
use AffiliateProductShowcase\Rest\TagsController;
use AffiliateProductShowcase\Repositories\TagRepository;
use AffiliateProductShowcase\Factories\TagFactory;
use AffiliateProductShowcase\Models\Tag;
use WP_REST_Request;
use WP_Error;

/**
 * Test TagsController directly (without REST API registration)
 *
 * @group tags-controller
 */
final class TagsControllerDirectTest extends TestCase {
    private TagsController $controller;
    private TagRepository $repository;

    protected function setUp(): void {
        parent::setUp();
        $this->repository = $this->createMock(TagRepository::class);
        $this->controller = new TagsController($this->repository);
    }

    /** @test */
    public function it_can_be_instantiated(): void {
        $this->assertInstanceOf(TagsController::class, $this->controller);
    }

    /** @test */
    public function it_has_register_routes_method(): void {
        $this->assertTrue(method_exists($this->controller, 'register_routes'));
    }

    /** @test */
    public function it_can_register_routes(): void {
        // This should not throw an error
        $this->controller->register_routes();
        $this->assertTrue(true); // If we get here, it worked
    }

    /** @test */
    public function it_has_get_items_method(): void {
        $this->assertTrue(method_exists($this->controller, 'get_items'));
    }

    /** @test */
    public function it_has_get_item_method(): void {
        $this->assertTrue(method_exists($this->controller, 'get_item'));
    }

    /** @test */
    public function it_has_create_item_method(): void {
        $this->assertTrue(method_exists($this->controller, 'create_item'));
    }

    /** @test */
    public function it_has_update_item_method(): void {
        $this->assertTrue(method_exists($this->controller, 'update_item'));
    }

    /** @test */
    public function it_has_delete_item_method(): void {
        $this->assertTrue(method_exists($this->controller, 'delete_item'));
    }

    /** @test */
    public function it_has_permission_methods(): void {
        $this->assertTrue(method_exists($this->controller, 'get_items_permissions_check'));
        $this->assertTrue(method_exists($this->controller, 'get_item_permissions_check'));
        $this->assertTrue(method_exists($this->controller, 'create_item_permissions_check'));
        $this->assertTrue(method_exists($this->controller, 'update_item_permissions_check'));
        $this->assertTrue(method_exists($this->controller, 'delete_item_permissions_check'));
    }

    /** @test */
    public function it_has_schema_methods(): void {
        $this->assertTrue(method_exists($this->controller, 'get_collection_params'));
        $this->assertTrue(method_exists($this->controller, 'get_item_schema'));
    }

    /** @test */
    public function get_items_returns_array_when_no_tags(): void {
        $this->repository
            ->expects($this->once())
            ->method('all')
            ->with([])
            ->willReturn([]);

        $request = $this->createMock(WP_REST_Request::class);
        $request->method('get_params')->willReturn([]);

        $result = $this->controller->get_items($request);

        $this->assertInstanceOf(\WP_REST_Response::class, $result);
        $this->assertEquals([], $result->get_data());
    }

    /** @test */
    public function get_items_returns_tags_when_available(): void {
        $tag = new Tag(1, 'Test Tag', 'test-tag', 'Test description', 'USD', 29.99);
        
        $this->repository
            ->expects($this->once())
            ->method('all')
            ->with([])
            ->willReturn([$tag]);

        $request = $this->createMock(WP_REST_Request::class);
        $request->method('get_params')->willReturn([]);

        $result = $this->controller->get_items($request);

        $this->assertInstanceOf(\WP_REST_Response::class, $result);
        $data = $result->get_data();
        $this->assertCount(1, $data);
        $this->assertEquals('Test Tag', $data[0]['name']);
    }

    /** @test */
    public function get_item_returns_404_when_not_found(): void {
        $this->repository
            ->expects($this->once())
            ->method('find')
            ->with(999)
            ->willReturn(null);

        $request = $this->createMock(WP_REST_Request::class);
        $request->method('offsetGet')->with('id')->willReturn('999');

        $result = $this->controller->get_item($request);

        $this->assertInstanceOf(WP_Error::class, $result);
        $this->assertEquals('tag_not_found', $result->get_error_code());
    }

    /** @test */
    public function get_item_returns_tag_when_found(): void {
        $tag = new Tag(1, 'Test Tag', 'test-tag', 'Test description', 'USD', 29.99);
        
        $this->repository
            ->expects($this->once())
            ->method('find')
            ->with(1)
            ->willReturn($tag);

        $request = $this->createMock(WP_REST_Request::class);
        $request->method('offsetGet')->with('id')->willReturn('1');

        $result = $this->controller->get_item($request);

        $this->assertInstanceOf(\WP_REST_Response::class, $result);
        $data = $result->get_data();
        $this->assertEquals('Test Tag', $data['name']);
    }

    /** @test */
    public function create_item_validates_required_fields(): void {
        $request = $this->createMock(WP_REST_Request::class);
        $request->method('get_params')->willReturn([]);

        $result = $this->controller->create_item($request);

        $this->assertInstanceOf(WP_Error::class, $result);
        $this->assertEquals('missing_name', $result->get_error_code());
    }

    /** @test */
    public function create_item_creates_tag_when_valid(): void {
        $params = ['name' => 'New Tag'];
        
        $this->repository
            ->expects($this->once())
            ->method('create')
            ->willReturnCallback(function($tag) {
                return new Tag(1, $tag->name, $tag->slug, $tag->description, $tag->currency, $tag->price);
            });

        $request = $this->createMock(WP_REST_Request::class);
        $request->method('get_params')->willReturn($params);

        $result = $this->controller->create_item($request);

        $this->assertInstanceOf(\WP_REST_Response::class, $result);
        $data = $result->get_data();
        $this->assertEquals('New Tag', $data['name']);
    }

    /** @test */
    public function update_item_returns_404_when_not_found(): void {
        $this->repository
            ->expects($this->once())
            ->method('find')
            ->with(999)
            ->willReturn(null);

        $request = $this->createMock(WP_REST_Request::class);
        $request->method('offsetGet')->with('id')->willReturn('999');
        $request->method('get_params')->willReturn(['name' => 'Updated Tag']);

        $result = $this->controller->update_item($request);

        $this->assertInstanceOf(WP_Error::class, $result);
        $this->assertEquals('tag_not_found', $result->get_error_code());
    }

    /** @test */
    public function update_item_updates_tag_when_found(): void {
        $existing = new Tag(1, 'Old Tag', 'old-tag', 'Old description', 'USD', 29.99);
        $updated = new Tag(1, 'Updated Tag', 'updated-tag', 'Old description', 'USD', 29.99);
        
        $this->repository
            ->expects($this->once())
            ->method('find')
            ->with(1)
            ->willReturn($existing);

        $this->repository
            ->expects($this->once())
            ->method('update')
            ->with(1, $this->isInstanceOf(Tag::class))
            ->willReturn($updated);

        $request = $this->createMock(WP_REST_Request::class);
        $request->method('offsetGet')->with('id')->willReturn('1');
        $request->method('get_params')->willReturn(['name' => 'Updated Tag']);

        $result = $this->controller->update_item($request);

        $this->assertInstanceOf(\WP_REST_Response::class, $result);
        $data = $result->get_data();
        $this->assertEquals('Updated Tag', $data['name']);
    }

    /** @test */
    public function delete_item_returns_404_when_not_found(): void {
        $this->repository
            ->expects($this->once())
            ->method('find')
            ->with(999)
            ->willReturn(null);

        $request = $this->createMock(WP_REST_Request::class);
        $request->method('offsetGet')->with('id')->willReturn('999');

        $result = $this->controller->delete_item($request);

        $this->assertInstanceOf(WP_Error::class, $result);
        $this->assertEquals('tag_not_found', $result->get_error_code());
    }

    /** @test */
    public function delete_item_deletes_tag_when_found(): void {
        $tag = new Tag(1, 'Test Tag', 'test-tag', 'Test description', 'USD', 29.99);
        
        $this->repository
            ->expects($this->once())
            ->method('find')
            ->with(1)
            ->willReturn($tag);

        $this->repository
            ->expects($this->once())
            ->method('delete')
            ->with(1);

        $request = $this->createMock(WP_REST_Request::class);
        $request->method('offsetGet')->with('id')->willReturn('1');

        $result = $this->controller->delete_item($request);

        $this->assertInstanceOf(\WP_REST_Response::class, $result);
        $data = $result->get_data();
        $this->assertTrue($data['deleted']);
        $this->assertEquals('Test Tag', $data['previous']['name']);
    }

    /** @test */
    public function permission_methods_return_boolean(): void {
        $request = $this->createMock(WP_REST_Request::class);

        $this->assertIsBool($this->controller->get_items_permissions_check($request));
        $this->assertIsBool($this->controller->get_item_permissions_check($request));
        $this->assertIsBool($this->controller->create_item_permissions_check($request));
        $this->assertIsBool($this->controller->update_item_permissions_check($request));
        $this->assertIsBool($this->controller->delete_item_permissions_check($request));
    }

    /** @test */
    public function schema_methods_return_arrays(): void {
        $this->assertIsArray($this->controller->get_collection_params());
        $this->assertIsArray($this->controller->get_item_schema());
    }

    /** @test */
    public function get_collection_params_has_expected_keys(): void {
        $params = $this->controller->get_collection_params();
        
        $this->assertArrayHasKey('context', $params);
        $this->assertArrayHasKey('search', $params);
        $this->assertArrayHasKey('orderby', $params);
        $this->assertArrayHasKey('order', $params);
        $this->assertArrayHasKey('page', $params);
        $this->assertArrayHasKey('per_page', $params);
    }

    /** @test */
    public function get_item_schema_has_expected_properties(): void {
        $schema = $this->controller->get_item_schema();
        
        $this->assertArrayHasKey('properties', $schema);
        $this->assertArrayHasKey('id', $schema['properties']);
        $this->assertArrayHasKey('name', $schema['properties']);
        $this->assertArrayHasKey('slug', $schema['properties']);
        $this->assertArrayHasKey('description', $schema['properties']);
        $this->assertArrayHasKey('currency', $schema['properties']);
        $this->assertArrayHasKey('price', $schema['properties']);
        $this->assertArrayHasKey('count', $schema['properties']);
    }
}