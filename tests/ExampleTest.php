<?php
declare(strict_types=1);

namespace AffiliateProductShowcase\Tests;

use PHPUnit\Framework\TestCase;
use AffiliateProductShowcase\Example;

/**
 * Basic test to verify the Example class is autoloaded correctly.
 */
final class ExampleTest extends TestCase
{
    public function testGetMessageReturnsHello(): void
    {
        $example = new Example();
        $this->assertSame('hello', $example->getMessage());
    }
}
