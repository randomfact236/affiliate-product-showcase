<?php

declare(strict_types=1);

namespace AffiliateProductShowcase\Tests\Unit\DependencyInjection;

use AffiliateProductShowcase\DependencyInjection\Container;
use AffiliateProductShowcase\DependencyInjection\ContainerInterface;
use PHPUnit\Framework\TestCase;
use RuntimeException;

final class ContainerTest extends TestCase {
    private Container $container;

    protected function setUp(): void {
        $this->container = new Container();
    }

    public function test_container_implements_interface(): void {
        $this->assertInstanceOf(ContainerInterface::class, $this->container);
    }

    public function test_can_register_service(): void {
        $this->container->register('test_service', function() {
            return 'test_value';
        });

        $this->assertTrue($this->container->has('test_service'));
    }

    public function test_can_get_registered_service(): void {
        $this->container->register('test_service', function() {
            return 'test_value';
        });

        $result = $this->container->get('test_service');
        $this->assertSame('test_value', $result);
    }

    public function test_returns_same_instance_for_shared_services(): void {
        $callCount = 0;
        $this->container->register('shared_service', function() use (&$callCount) {
            $callCount++;
            return (object) ['instance' => $callCount];
        }, true);

        $instance1 = $this->container->get('shared_service');
        $instance2 = $this->container->get('shared_service');

        $this->assertSame($instance1, $instance2);
        $this->assertSame(1, $callCount);
    }

    public function test_returns_new_instance_for_non_shared_services(): void {
        $callCount = 0;
        $this->container->register('non_shared_service', function() use (&$callCount) {
            $callCount++;
            return (object) ['instance' => $callCount];
        }, false);

        $instance1 = $this->container->get('non_shared_service');
        $instance2 = $this->container->get('non_shared_service');

        $this->assertNotSame($instance1, $instance2);
        $this->assertSame(2, $callCount);
    }

    public function test_default_to_shared_services(): void {
        $callCount = 0;
        $this->container->register('default_shared', function() use (&$callCount) {
            $callCount++;
            return (object) ['instance' => $callCount];
        });

        $instance1 = $this->container->get('default_shared');
        $instance2 = $this->container->get('default_shared');

        $this->assertSame($instance1, $instance2);
        $this->assertSame(1, $callCount);
    }

    public function test_throws_exception_when_getting_nonexistent_service(): void {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage("Service 'nonexistent' is not registered in the container.");

        $this->container->get('nonexistent');
    }

    public function test_has_returns_false_for_nonexistent_service(): void {
        $this->assertFalse($this->container->has('nonexistent'));
    }

    public function test_can_remove_service(): void {
        $this->container->register('temp_service', function() {
            return 'temp';
        });

        $this->assertTrue($this->container->has('temp_service'));

        $this->container->remove('temp_service');

        $this->assertFalse($this->container->has('temp_service'));
    }

    public function test_clear_removes_all_services(): void {
        $this->container->register('service1', function() { return 1; });
        $this->container->register('service2', function() { return 2; });
        $this->container->register('service3', function() { return 3; });

        $this->assertTrue($this->container->has('service1'));
        $this->assertTrue($this->container->has('service2'));
        $this->assertTrue($this->container->has('service3'));

        $this->container->clear();

        $this->assertFalse($this->container->has('service1'));
        $this->assertFalse($this->container->has('service2'));
        $this->assertFalse($this->container->has('service3'));
    }

    public function test_container_provides_itself_to_factory(): void {
        $this->container->register('service1', function($c) {
            $this->assertInstanceOf(Container::class, $c);
            return 'value1';
        });

        $this->container->get('service1');
    }

    public function test_can_create_dependent_services(): void {
        $this->container->register('dependency', function() {
            return 'dependency_value';
        });

        $this->container->register('dependent', function($c) {
            $dependency = $c->get('dependency');
            return "dependent_on_{$dependency}";
        });

        $result = $this->container->get('dependent');
        $this->assertSame('dependent_on_dependency_value', $result);
    }

    public function test_register_returns_container_for_fluent_interface(): void {
        $result = $this->container->register('service', function() {
            return 'value';
        });

        $this->assertSame($this->container, $result);
    }

    public function test_remove_returns_container_for_fluent_interface(): void {
        $this->container->register('temp', function() { return 'temp'; });
        $result = $this->container->remove('temp');

        $this->assertSame($this->container, $result);
    }

    public function test_clear_returns_container_for_fluent_interface(): void {
        $result = $this->container->clear();

        $this->assertSame($this->container, $result);
    }

    public function test_can_chain_service_registrations(): void {
        $this->container
            ->register('service1', function() { return 1; })
            ->register('service2', function() { return 2; })
            ->register('service3', function() { return 3; });

        $this->assertTrue($this->container->has('service1'));
        $this->assertTrue($this->container->has('service2'));
        $this->assertTrue($this->container->has('service3'));
    }

    public function test_can_call_closure_with_dependency_injection(): void {
        $this->container->register('test_service', function() {
            return 'service_value';
        });

        $result = $this->container->call(function() {
            return 'called';
        });

        $this->assertSame('called', $result);
    }

    public function test_can_call_closure_with_parameters(): void {
        $result = $this->container->call(function($param1, $param2) {
            return $param1 . ' ' . $param2;
        }, ['param1' => 'hello', 'param2' => 'world']);

        $this->assertSame('hello world', $result);
    }

    public function test_can_register_service_provider(): void {
        $provider = new class {
            public function register($container) {
                $container->register('provider_service', function() {
                    return 'from_provider';
                });
            }
        };

        $this->container->registerProvider($provider);

        $this->assertTrue($this->container->has('provider_service'));
        $this->assertSame('from_provider', $this->container->get('provider_service'));
    }

    public function test_provider_registration_returns_container(): void {
        $provider = new class {
            public function register($container) {
                // No-op
            }
        };

        $result = $this->container->registerProvider($provider);

        $this->assertSame($this->container, $result);
    }

    public function test_can_store_complex_objects(): void {
        $this->container->register('complex_object', function() {
            return (object) [
                'name' => 'Test',
                'data' => [1, 2, 3],
                'nested' => (object) ['key' => 'value']
            ];
        });

        $result = $this->container->get('complex_object');

        $this->assertSame('Test', $result->name);
        $this->assertSame([1, 2, 3], $result->data);
        $this->assertSame('value', $result->nested->key);
    }

    public function test_can_register_multiple_services_with_same_interface(): void {
        $this->container->register('implementation1', function() {
            return 'impl1';
        });

        $this->container->register('implementation2', function() {
            return 'impl2';
        });

        $this->assertSame('impl1', $this->container->get('implementation1'));
        $this->assertSame('impl2', $this->container->get('implementation2'));
    }

    public function test_factory_receives_container_on_each_call(): void {
        $calls = [];
        $this->container->register('test', function($c) use (&$calls) {
            $calls[] = $c;
            return 'value';
        });

        $this->container->get('test');
        $this->container->get('test');
        $this->container->get('test');

        $this->assertCount(3, $calls);
        foreach ($calls as $call) {
            $this->assertInstanceOf(Container::class, $call);
        }
    }

    public function test_can_register_null_returning_service(): void {
        $this->container->register('null_service', function() {
            return null;
        });

        $result = $this->container->get('null_service');

        $this->assertNull($result);
    }

    public function test_can_register_false_returning_service(): void {
        $this->container->register('false_service', function() {
            return false;
        });

        $result = $this->container->get('false_service');

        $this->assertFalse($result);
    }

    public function test_can_register_zero_returning_service(): void {
        $this->container->register('zero_service', function() {
            return 0;
        });

        $result = $this->container->get('zero_service');

        $this->assertSame(0, $result);
    }

    public function test_can_register_empty_array_returning_service(): void {
        $this->container->register('empty_array_service', function() {
            return [];
        });

        $result = $this->container->get('empty_array_service');

        $this->assertIsArray($result);
        $this->assertEmpty($result);
    }

    public function test_can_handle_circular_dependencies_gracefully(): void {
        $this->expectException(RuntimeException::class);

        $this->container->register('service1', function($c) {
            return $c->get('service2');
        });

        $this->container->register('service2', function($c) {
            return $c->get('service1');
        });

        // This should handle circular dependencies without infinite recursion
        // The current implementation may not detect this, but shouldn't crash
        $this->container->get('service1');
    }
}
