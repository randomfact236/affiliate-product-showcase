<?php
/**
 * Price Formatter Tests
 *
 * @package AffiliateProductShowcase\Tests\Unit\Formatters
 */

namespace AffiliateProductShowcase\Tests\Unit\Formatters;

use AffiliateProductShowcase\Formatters\PriceFormatter;
use PHPUnit\Framework\TestCase;

/**
 * Test PriceFormatter class
 */
class PriceFormatterTest extends TestCase {

	private PriceFormatter $formatter;

	protected function setUp(): void {
		$this->formatter = new PriceFormatter();
	}

	/**
	 * Test format USD currency
	 */
	public function testFormatUSD() {
		$result = $this->formatter->format(99.99, 'USD');

		$this->assertStringContainsString('$', $result);
		$this->assertStringContainsString('99.99', $result);
		$this->assertEquals('$99.99', $result);
	}

	/**
	 * Test format EUR currency
	 */
	public function testFormatEUR() {
		$result = $this->formatter->format(149.99, 'EUR');

		$this->assertStringContainsString('€', $result);
		$this->assertStringContainsString('149.99', $result);
		$this->assertEquals('€149.99', $result);
	}

	/**
	 * Test format GBP currency
	 */
	public function testFormatGBP() {
		$result = $this->formatter->format(79.99, 'GBP');

		$this->assertStringContainsString('£', $result);
		$this->assertStringContainsString('79.99', $result);
		$this->assertEquals('£79.99', $result);
	}

	/**
	 * Test format with unknown currency
	 */
	public function testFormatUnknownCurrency() {
		$result = $this->formatter->format(100.00, 'XYZ');

		$this->assertStringContainsString('XYZ', $result);
		$this->assertStringContainsString('100.00', $result);
		$this->assertEquals('XYZ 100.00', $result);
	}

	/**
	 * Test format with lowercase currency code
	 */
	public function testFormatLowercaseCurrency() {
		$result1 = $this->formatter->format(50.00, 'usd');
		$result2 = $this->formatter->format(50.00, 'USD');

		$this->assertEquals($result1, $result2);
		$this->assertStringContainsString('$', $result1);
	}

	/**
	 * Test format with zero price
	 */
	public function testFormatZeroPrice() {
		$result = $this->formatter->format(0.00, 'USD');

		$this->assertStringContainsString('$', $result);
		$this->assertStringContainsString('0.00', $result);
		$this->assertEquals('$0.00', $result);
	}

	/**
	 * Test format with very small price
	 */
	public function testFormatVerySmallPrice() {
		$result = $this->formatter->format(0.01, 'USD');

		$this->assertStringContainsString('$', $result);
		$this->assertStringContainsString('0.01', $result);
		$this->assertEquals('$0.01', $result);
	}

	/**
	 * Test format with very large price
	 */
	public function testFormatVeryLargePrice() {
		$result = $this->formatter->format(999999.99, 'EUR');

		$this->assertStringContainsString('€', $result);
		$this->assertStringContainsString('999,999.99', $result);
	}

	/**
	 * Test format with decimal rounding
	 */
	public function testFormatWithDecimalRounding() {
		$result = $this->formatter->format(99.999, 'USD');

		$this->assertStringContainsString('100.00', $result);
		$this->assertEquals('$100.00', $result);
	}

	/**
	 * Test format with exact decimal
	 */
	public function testFormatWithExactDecimal() {
		$result = $this->formatter->format(99.50, 'USD');

		$this->assertEquals('$99.50', $result);
	}

	/**
	 * Test format JPY currency (unknown, falls back to code)
	 */
	public function testFormatJPY() {
		$result = $this->formatter->format(1000, 'JPY');

		$this->assertStringContainsString('JPY', $result);
		$this->assertEquals('JPY 1,000.00', $result);
	}

	/**
	 * Test format CAD currency (unknown, falls back to code)
	 */
	public function testFormatCAD() {
		$result = $this->formatter->format(199.99, 'CAD');

		$this->assertStringContainsString('CAD', $result);
		$this->assertEquals('CAD 199.99', $result);
	}

	/**
	 * Test format with default currency
	 */
	public function testFormatWithDefaultCurrency() {
		$result = $this->formatter->format(59.99);

		// Default should be USD
		$this->assertEquals('$59.99', $result);
	}

	/**
	 * Test format with mixed case currency
	 */
	public function testFormatWithMixedCaseCurrency() {
		$result1 = $this->formatter->format(25.00, 'UsD');
		$result2 = $this->formatter->format(25.00, 'uSd');

		$this->assertEquals($result1, $result2);
		$this->assertStringContainsString('$', $result1);
	}

	/**
	 * Test format returns string
	 */
	public function testFormatReturnsString() {
		$result = $this->formatter->format(99.99, 'USD');

		$this->assertIsString($result);
	}

	/**
	 * Test format with negative price
	 */
	public function testFormatNegativePrice() {
		$result = $this->formatter->format(-10.99, 'USD');

		$this->assertStringContainsString('$', $result);
		$this->assertStringContainsString('-10.99', $result);
		$this->assertEquals('$-10.99', $result);
	}
}
