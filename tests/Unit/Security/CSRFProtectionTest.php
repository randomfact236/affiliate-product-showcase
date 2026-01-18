<?php
/**
 * CSRF Protection Tests
 *
 * @package AffiliateProductShowcase\Tests\Unit\Security
 */

namespace AffiliateProductShowcase\Tests\Unit\Security;

use AffiliateProductShowcase\Security\CSRFProtection;
use PHPUnit\Framework\TestCase;

/**
 * Test CSRFProtection class
 */
class CSRFProtectionTest extends TestCase {

	/**
	 * Test generateNonce with default action
	 */
	public function testGenerateNonceWithDefaultAction() {
		$nonce = CSRFProtection::generateNonce();

		$this->assertIsString($nonce);
		$this->assertNotEmpty($nonce);
	}

	/**
	 * Test generateNonce with custom action
	 */
	public function testGenerateNonceWithCustomAction() {
		$nonce = CSRFProtection::generateNonce('custom_action');

		$this->assertIsString($nonce);
		$this->assertNotEmpty($nonce);
	}

	/**
	 * Test verifyNonce with valid nonce
	 */
	public function testVerifyNonceWithValidNonce() {
		$nonce = CSRFProtection::generateNonce('test_action');
		$result = CSRFProtection::verifyNonce($nonce, 'test_action');

		$this->assertTrue($result);
	}

	/**
	 * Test verifyNonce with invalid nonce
	 */
	public function testVerifyNonceWithInvalidNonce() {
		$result = CSRFProtection::verifyNonce('invalid_nonce', 'test_action');

		$this->assertFalse($result);
	}

	/**
	 * Test verifyNonce with empty nonce
	 */
	public function testVerifyNonceWithEmptyNonce() {
		$result = CSRFProtection::verifyNonce('', 'test_action');

		$this->assertFalse($result);
	}

	/**
	 * Test generateTimedNonce
	 */
	public function testGenerateTimedNonce() {
		$nonce = CSRFProtection::generateTimedNonce('timed_action', 3600);

		$this->assertIsString($nonce);
		$this->assertNotEmpty($nonce);
	}

	/**
	 * Test verifyTimedNonce with valid nonce
	 */
	public function testVerifyTimedNonceWithValidNonce() {
		$nonce = CSRFProtection::generateTimedNonce('timed_test', 3600);
		$result = CSRFProtection::verifyTimedNonce($nonce, 'timed_test', 3600);

		$this->assertTrue($result);
	}

	/**
	 * Test verifyTimedNonce with invalid nonce
	 */
	public function testVerifyTimedNonceWithInvalidNonce() {
		$result = CSRFProtection::verifyTimedNonce('invalid', 'timed_test', 3600);

		$this->assertFalse($result);
	}

	/**
	 * Test generateUserNonce
	 */
	public function testGenerateUserNonce() {
		$nonce = CSRFProtection::generateUserNonce(123, 'user_action');

		$this->assertIsString($nonce);
		$this->assertNotEmpty($nonce);
	}

	/**
	 * Test verifyUserNonce with valid nonce
	 */
	public function testVerifyUserNonceWithValidNonce() {
		$user_id = 456;
		$nonce = CSRFProtection::generateUserNonce($user_id, 'user_test');
		$result = CSRFProtection::verifyUserNonce($nonce, $user_id, 'user_test');

		$this->assertTrue($result);
	}

	/**
	 * Test verifyUserNonce with invalid user
	 */
	public function testVerifyUserNonceWithInvalidUser() {
		$nonce = CSRFProtection::generateUserNonce(123, 'user_test');
		$result = CSRFProtection::verifyUserNonce($nonce, 456, 'user_test');

		$this->assertFalse($result);
	}

	/**
	 * Test getNonceFromRequest with POST
	 */
	public function testGetNonceFromRequestWithPost() {
		$_POST['_wpnonce'] = 'test_nonce_value';

		$nonce = CSRFProtection::getNonceFromRequest('_wpnonce');

		$this->assertEquals('test_nonce_value', $nonce);

		// Clean up
		unset($_POST['_wpnonce']);
	}

	/**
	 * Test getNonceFromRequest with GET
	 */
	public function testGetNonceFromRequestWithGet() {
		$_GET['custom_nonce'] = 'get_nonce_value';
		unset($_POST['custom_nonce']);

		$nonce = CSRFProtection::getNonceFromRequest('custom_nonce');

		$this->assertEquals('get_nonce_value', $nonce);

		// Clean up
		unset($_GET['custom_nonce']);
	}

	/**
	 * Test getNonceFromRequest with REQUEST
	 */
	public function testGetNonceFromRequestWithRequest() {
		$_REQUEST['request_nonce'] = 'request_nonce_value';
		unset($_POST['request_nonce']);
		unset($_GET['request_nonce']);

		$nonce = CSRFProtection::getNonceFromRequest('request_nonce');

		$this->assertEquals('request_nonce_value', $nonce);

		// Clean up
		unset($_REQUEST['request_nonce']);
	}

	/**
	 * Test getNonceFromRequest when not found
	 */
	public function testGetNonceFromRequestNotFound() {
		unset($_POST['nonexistent']);
		unset($_GET['nonexistent']);
		unset($_REQUEST['nonexistent']);

		$nonce = CSRFProtection::getNonceFromRequest('nonexistent');

		$this->assertNull($nonce);
	}

	/**
	 * Test isPost with POST request
	 */
	public function testIsPostWithPostRequest() {
		$_SERVER['REQUEST_METHOD'] = 'POST';

		$this->assertTrue(CSRFProtection::isPost());

		// Clean up
		unset($_SERVER['REQUEST_METHOD']);
	}

	/**
	 * Test isPost with GET request
	 */
	public function testIsPostWithGetRequest() {
		$_SERVER['REQUEST_METHOD'] = 'GET';

		$this->assertFalse(CSRFProtection::isPost());

		// Clean up
		unset($_SERVER['REQUEST_METHOD']);
	}

	/**
	 * Test isGet with GET request
	 */
	public function testIsGetWithGetRequest() {
		$_SERVER['REQUEST_METHOD'] = 'GET';

		$this->assertTrue(CSRFProtection::isGet());

		// Clean up
		unset($_SERVER['REQUEST_METHOD']);
	}

	/**
	 * Test isGet with POST request
	 */
	public function testIsGetWithPostRequest() {
		$_SERVER['REQUEST_METHOD'] = 'POST';

		$this->assertFalse(CSRFProtection::isGet());

		// Clean up
		unset($_SERVER['REQUEST_METHOD']);
	}

	/**
	 * Test getAjaxErrorResponse
	 */
	public function testGetAjaxErrorResponse() {
		$response = CSRFProtection::getAjaxErrorResponse();

		$this->assertIsArray($response);
		$this->assertArrayHasKey('success', $response);
		$this->assertArrayHasKey('message', $response);
		$this->assertArrayHasKey('code', $response);
		$this->assertFalse($response['success']);
		$this->assertEquals('invalid_nonce', $response['code']);
	}

	/**
	 * Test addNonceToData
	 */
	public function testAddNonceToData() {
		$data = [
			'field1' => 'value1',
			'field2' => 'value2',
		];

		$result = CSRFProtection::addNonceToData($data, 'test_action');

		$this->assertArrayHasKey('_wpnonce', $result);
		$this->assertNotEmpty($result['_wpnonce']);
		$this->assertEquals('value1', $result['field1']);
		$this->assertEquals('value2', $result['field2']);
	}

	/**
	 * Test addNonceToData with custom field name
	 */
	public function testAddNonceToDataWithCustomFieldName() {
		$data = ['field1' => 'value1'];

		$result = CSRFProtection::addNonceToData($data, 'test_action', 'custom_nonce');

		$this->assertArrayHasKey('custom_nonce', $result);
		$this->assertArrayNotHasKey('_wpnonce', $result);
	}

	/**
	 * Test wrapFormSubmission
	 */
	public function testWrapFormSubmissionWithValidNonce() {
		$_POST['_wpnonce'] = CSRFProtection::generateNonce('wrapped_action');

		$callback_called = false;
		$callback = function() use (&$callback_called) {
			$callback_called = true;
			return 'success';
		};

		$wrapped = CSRFProtection::wrapFormSubmission($callback, 'wrapped_action');
		$result = $wrapped();

		$this->assertTrue($callback_called);
		$this->assertEquals('success', $result);

		// Clean up
		unset($_POST['_wpnonce']);
	}

	/**
	 * Test wrapFormSubmission with invalid nonce
	 */
	public function testWrapFormSubmissionWithInvalidNonce() {
		$_POST['_wpnonce'] = 'invalid_nonce';

		$callback_called = false;
		$callback = function() use (&$callback_called) {
			$callback_called = true;
			return 'success';
		};

		$wrapped = CSRFProtection::wrapFormSubmission($callback, 'wrapped_action');
		$result = $wrapped();

		$this->assertFalse($callback_called);
		$this->assertNull($result);

		// Clean up
		unset($_POST['_wpnonce']);
	}

	/**
	 * Test nonce uniqueness
	 */
	public function testNonceUniqueness() {
		$nonce1 = CSRFProtection::generateNonce('test');
		$nonce2 = CSRFProtection::generateNonce('test');

		$this->assertNotEquals($nonce1, $nonce2, 'Nonces should be unique even with same action');
	}

	/**
	 * Test different actions produce different nonces
	 */
	public function testDifferentActionsDifferentNonces() {
		$nonce1 = CSRFProtection::generateNonce('action1');
		$nonce2 = CSRFProtection::generateNonce('action2');

		$this->assertNotEquals($nonce1, $nonce2);
	}

	/**
	 * Test verifyRequest with valid POST nonce
	 */
	public function testVerifyRequestWithValidPostNonce() {
		$_POST['_wpnonce'] = CSRFProtection::generateNonce('request_action');

		$result = CSRFProtection::verifyRequest('_wpnonce', 'request_action');

		$this->assertTrue($result);

		// Clean up
		unset($_POST['_wpnonce']);
	}

	/**
	 * Test verifyRequest with valid GET nonce
	 */
	public function testVerifyRequestWithValidGetNonce() {
		$_GET['_wpnonce'] = CSRFProtection::generateNonce('request_action');
		unset($_POST['_wpnonce']);

		$result = CSRFProtection::verifyRequest('_wpnonce', 'request_action');

		$this->assertTrue($result);

		// Clean up
		unset($_GET['_wpnonce']);
	}

	/**
	 * Test verifyRequest with missing nonce
	 */
	public function testVerifyRequestWithMissingNonce() {
		unset($_POST['_wpnonce']);
		unset($_GET['_wpnonce']);

		$result = CSRFProtection::verifyRequest('_wpnonce', 'request_action');

		$this->assertFalse($result);
	}

	/**
	 * Test nonceField output
	 */
	public function testNonceFieldOutput() {
		ob_start();
		CSRFProtection::nonceField('field_action', 'custom_nonce', false);
		$output = ob_get_clean();

		$this->assertStringContainsString('name="custom_nonce"', $output);
		$this->assertStringContainsString('value=', $output);
	}

	/**
	 * Test nonceUrl
	 */
	public function testNonceUrl() {
		$url = 'https://example.com/action';
		$nonce_url = CSRFProtection::nonceUrl($url, 'url_action');

		$this->assertStringContainsString($url, $nonce_url);
		$this->assertStringContainsString('_wpnonce=', $nonce_url);
	}

	/**
	 * Test nonceUrl with custom nonce name
	 */
	public function testNonceUrlWithCustomName() {
		$url = 'https://example.com/action';
		$nonce_url = CSRFProtection::nonceUrl($url, 'url_action', 'custom_nonce');

		$this->assertStringContainsString('custom_nonce=', $nonce_url);
		$this->assertStringNotContainsString('_wpnonce=', $nonce_url);
	}

	/**
	 * Test timed nonce expiration
	 */
	public function testTimedNonceExpiration() {
		$nonce = CSRFProtection::generateTimedNonce('expire_test', 1);
		
		// Should be valid immediately
		$result = CSRFProtection::verifyTimedNonce($nonce, 'expire_test', 1);
		$this->assertTrue($result);

		// Wait 2 seconds
		sleep(2);

		// Should still be valid (checks current and previous tick)
		$result = CSRFProtection::verifyTimedNonce($nonce, 'expire_test', 1);
		$this->assertTrue($result);
	}
}
