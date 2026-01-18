<?php
/**
 * Sample Test
 * 
 * This is a placeholder test to ensure PHPUnit can run.
 */

namespace AffiliateProductShowcase\Tests;

use PHPUnit\Framework\TestCase;

class SampleTest extends TestCase
{
    /**
     * Test that true is true
     */
    public function testTrueIsTrue()
    {
        $this->assertTrue(true);
    }
    
    /**
     * Test that array contains value
     */
    public function testArrayContainsValue()
    {
        $array = ['apple', 'banana', 'orange'];
        $this->assertContains('banana', $array);
    }
    
    /**
     * Test that exception is thrown
     */
    public function testExceptionThrown()
    {
        $this->expectException(\Exception::class);
        throw new \Exception('Test exception');
    }
}
