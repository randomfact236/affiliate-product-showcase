<?php
/**
 * Format Helper Tests
 *
 * @package AffiliateProductShowcase\Tests\Unit\Helpers
 */

namespace AffiliateProductShowcase\Tests\Unit\Helpers;

use AffiliateProductShowcase\Helpers\FormatHelper;
use PHPUnit\Framework\TestCase;

/**
 * Test FormatHelper class
 */
class FormatHelperTest extends TestCase {

	/**
	 * Test limit words function
	 */
	public function testLimitWords() {
		$text = 'This is a long text that needs to be limited';
		$result = FormatHelper::limit_words($text, 5);

		$this->assertIsString($result);
		$this->assertLessThanOrEqual(5, str_word_count($result));
		$this->assertEquals('This is a long text', $result);
	}

	/**
	 * Test limit words with ellipsis
	 */
	public function testLimitWordsWithEllipsis() {
		$text = 'This is a long text that needs to be limited';
		$result = FormatHelper::limit_words($text, 5, true);

		$this->assertStringEndsWith('...', $result);
		$this->assertEquals('This is a long text...', $result);
	}

	/**
	 * Test limit words with short text
	 */
	public function testLimitWordsWithShortText() {
		$text = 'Short text';
		$result = FormatHelper::limit_words($text, 10);

		$this->assertEquals('Short text', $result);
	}

	/**
	 * Test limit words with zero limit
	 */
	public function testLimitWordsWithZeroLimit() {
		$text = 'This is a long text';
		$result = FormatHelper::limit_words($text, 0);

		$this->assertEmpty($result);
	}

	/**
	 * Test truncate text
	 */
	public function testTruncate() {
		$text = 'This is a very long text that should be truncated';
		$result = FormatHelper::truncate($text, 20);

		$this->assertIsString($result);
		$this->assertLessThanOrEqual(20, strlen($result));
		$this->assertEquals('This is a very lo...', $result);
	}

	/**
	 * Test truncate without ellipsis
	 */
	public function testTruncateWithoutEllipsis() {
		$text = 'This is a very long text that should be truncated';
		$result = FormatHelper::truncate($text, 20, false);

		$this->assertStringEndsNotWith('...', $result);
	}

	/**
	 * Test truncate with short text
	 */
	public function testTruncateWithShortText() {
		$text = 'Short';
		$result = FormatHelper::truncate($text, 20);

		$this->assertEquals('Short', $result);
	}

	/**
	 * Test sanitize output
	 */
	public function testSanitizeOutput() {
		$unsafe = '<script>alert("xss")</script>';
		$result = FormatHelper::sanitize_output($unsafe);

		$this->assertStringNotContainsString('<script>', $result);
		$this->assertStringContainsString('alert("xss")', $result);
	}

	/**
	 * Test sanitize output with safe content
	 */
	public function testSanitizeOutputWithSafeContent() {
		$safe = 'This is safe content';
		$result = FormatHelper::sanitize_output($safe);

		$this->assertEquals('This is safe content', $result);
	}

	/**
	 * Test escape attribute
	 */
	public function testEscapeAttribute() {
		$unsafe = 'value" onclick="alert(1)';
		$result = FormatHelper::escape_attribute($unsafe);

		$this->assertStringNotContainsString('"', $result);
		$this->assertStringContainsString('"', $result);
	}

	/**
	 * Test escape class names
	 */
	public function testEscapeClassNames() {
		$classes = 'class1 class-2 class_3';
		$result = FormatHelper::escape_class_names($classes);

		$this->assertIsArray($result);
		$this->assertEquals(['class1', 'class-2', 'class_3'], $result);
	}

	/**
	 * Test escape class names with invalid characters
	 */
	public function testEscapeClassNamesWithInvalidCharacters() {
		$classes = 'valid@class invalid.class another/class';
		$result = FormatHelper::escape_class_names($classes);

		$this->assertIsArray($result);
		foreach ($result as $class) {
			$this->assertMatchesRegularExpression('/^[a-zA-Z0-9_-]+$/', $class);
		}
	}

	/**
	 * Test format bytes
	 */
	public function testFormatBytes() {
		$result = FormatHelper::format_bytes(1024);

		$this->assertIsString($result);
		$this->assertStringContainsString('KB', $result);
	}

	/**
	 * Test format bytes with bytes
	 */
	public function testFormatBytesWithBytes() {
		$result = FormatHelper::format_bytes(512);

		$this->assertStringContainsString('B', $result);
	}

	/**
	 * Test format bytes with megabytes
	 */
	public function testFormatBytesWithMegabytes() {
		$result = FormatHelper::format_bytes(1024 * 1024 * 5);

		$this->assertStringContainsString('MB', $result);
	}

	/**
	 * Test format bytes with gigabytes
	 */
	public function testFormatBytesWithGigabytes() {
		$result = FormatHelper::format_bytes(1024 * 1024 * 1024 * 2);

		$this->assertStringContainsString('GB', $result);
	}

	/**
	 * Test make clickable
	 */
	public function testMakeClickable() {
		$text = 'Visit https://example.com for more info';
		$result = FormatHelper::make_clickable($text);

		$this->assertStringContainsString('<a', $result);
		$this->assertStringContainsString('href="https://example.com"', $result);
	}

	/**
	 * Test make clickable with multiple URLs
	 */
	public function testMakeClickableWithMultipleUrls() {
		$text = 'Visit https://example.com or http://test.com';
		$result = FormatHelper::make_clickable($text);

		$this->assertStringContainsString('https://example.com', $result);
		$this->assertStringContainsString('http://test.com', $result);
	}

	/**
	 * Test make clickable with no URLs
	 */
	public function testMakeClickableWithNoUrls() {
		$text = 'Just regular text';
		$result = FormatHelper::make_clickable($text);

		$this->assertEquals('Just regular text', $result);
	}

	/**
	 * Test strip shortcodes
	 */
	public function testStripShortcodes() {
		$text = 'Hello [shortcode]world[/shortcode]';
		$result = FormatHelper::strip_shortcodes($text);

		$this->assertStringNotContainsString('[shortcode]', $result);
		$this->assertStringNotContainsString('[/shortcode]', $result);
		$this->assertEquals('Hello world', $result);
	}

	/**
	 * Test strip shortcodes with nested
	 */
	public function testStripShortcodesWithNested() {
		$text = 'Before [outer][inner]content[/inner][/outer] after';
		$result = FormatHelper::strip_shortcodes($text);

		$this->assertStringNotContainsString('[', $result);
		$this->assertStringNotContainsString(']', $result);
		$this->assertEquals('Before content after', $result);
	}

	/**
	 * Test normalize whitespace
	 */
	public function testNormalizeWhitespace() {
		$text = "This  has   multiple    spaces\tand\nnewlines";
		$result = FormatHelper::normalize_whitespace($text);

		$this->assertStringNotContainsString('  ', $result);
		$this->assertStringNotContainsString("\t", $result);
		$this->assertStringNotContainsString("\n", $result);
	}

	/**
	 * Test capitalize first letter
	 */
	public function testCapitalizeFirstLetter() {
		$result = FormatHelper::capitalize_first('hello world');

		$this->assertEquals('Hello world', $result);
	}

	/**
	 * Test capitalize first letter with uppercase
	 */
	public function testCapitalizeFirstLetterWithUppercase() {
		$result = FormatHelper::capitalize_first('HELLO WORLD');

		$this->assertEquals('HELLO WORLD', $result);
	}

	/**
	 * Test convert to array
	 */
	public function testConvertToArray() {
		$string = 'item1,item2,item3';
		$result = FormatHelper::convert_to_array($string, ',');

		$this->assertIsArray($result);
		$this->assertEquals(['item1', 'item2', 'item3'], $result);
	}

	/**
	 * Test convert to array with trim
	 */
	public function testConvertToArrayWithTrim() {
		$string = 'item1, item2 , item3';
		$result = FormatHelper::convert_to_array($string, ',', true);

		$this->assertEquals(['item1', 'item2', 'item3'], $result);
	}

	/**
	 * Test convert to array with empty string
	 */
	public function testConvertToArrayWithEmptyString() {
		$result = FormatHelper::convert_to_array('', ',');

		$this->assertIsArray($result);
		$this->assertEmpty($result);
	}

	/**
	 * Test convert to array with single item
	 */
	public function testConvertToArrayWithSingleItem() {
		$result = FormatHelper::convert_to_array('single', ',');

		$this->assertEquals(['single'], $result);
	}
}
