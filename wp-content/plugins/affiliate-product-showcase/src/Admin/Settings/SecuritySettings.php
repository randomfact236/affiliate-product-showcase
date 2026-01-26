<?php
declare(strict_types=1);

namespace AffiliateProductShowcase\Admin\Settings;

/**
 * Security Settings Section
 *
 * Handles security settings including nonce verification, rate limiting,
 * CSRF protection, XSS prevention, and security headers.
 *
 * @package AffiliateProductShowcase\Admin\Settings
 * @since 1.0.0
 */
final class SecuritySettings extends AbstractSettingsSection {
	
	const SECTION_ID = 'affiliate_product_showcase_security';
	const SECTION_TITLE = 'Security Settings';
	
	/**
	 * Get default values for this section
	 *
	 * @return array
	 */
	public function get_defaults(): array {
		return [
			// Essential security settings
			'enable_nonce_verification' => true,
			'enable_rate_limiting' => true,
			'rate_limit_requests_per_minute' => 60,
			'rate_limit_requests_per_hour' => 1000,
			'enable_csrf_protection' => true,
			'sanitize_all_output' => true,
			'enable_xss_protection' => true,
			'enable_frame_options' => true,
			'frame_options_value' => 'SAMEORIGIN',
			'enable_referrer_policy' => true,
			'referrer_policy_value' => 'strict-origin-when-cross-origin',
			
			// Optional security settings
			'enable_content_security_policy' => false,
			'csp_report_only_mode' => false,
			'enable_hsts' => false,
			'hsts_max_age' => 31536000,
		];
	}
	
	/**
	 * Register section and fields
	 *
	 * @return void
	 */
	public function register_section_and_fields(): void {
		\add_settings_section(
			self::SECTION_ID,
			__('Security Settings', 'affiliate-product-showcase'),
			[$this, 'render_section_description'],
			'affiliate-product-showcase',
			['data-section' => 'security']
		);
		
		// Essential security settings
		\add_settings_field(
			'enable_nonce_verification',
			__('Enable Nonce Verification', 'affiliate-product-showcase'),
			[$this, 'render_enable_nonce_verification_field'],
			'affiliate-product-showcase',
			self::SECTION_ID,
			['label_for' => 'enable_nonce_verification']
		);
		
		\add_settings_field(
			'enable_rate_limiting',
			__('Enable Rate Limiting', 'affiliate-product-showcase'),
			[$this, 'render_enable_rate_limiting_field'],
			'affiliate-product-showcase',
			self::SECTION_ID,
			['label_for' => 'enable_rate_limiting']
		);
		
		\add_settings_field(
			'rate_limit_requests_per_minute',
			__('Rate Limit: Requests Per Minute', 'affiliate-product-showcase'),
			[$this, 'render_rate_limit_requests_per_minute_field'],
			'affiliate-product-showcase',
			self::SECTION_ID,
			['label_for' => 'rate_limit_requests_per_minute']
		);
		
		\add_settings_field(
			'rate_limit_requests_per_hour',
			__('Rate Limit: Requests Per Hour', 'affiliate-product-showcase'),
			[$this, 'render_rate_limit_requests_per_hour_field'],
			'affiliate-product-showcase',
			self::SECTION_ID,
			['label_for' => 'rate_limit_requests_per_hour']
		);
		
		\add_settings_field(
			'enable_csrf_protection',
			__('Enable CSRF Protection', 'affiliate-product-showcase'),
			[$this, 'render_enable_csrf_protection_field'],
			'affiliate-product-showcase',
			self::SECTION_ID,
			['label_for' => 'enable_csrf_protection']
		);
		
		\add_settings_field(
			'sanitize_all_output',
			__('Sanitize All Output', 'affiliate-product-showcase'),
			[$this, 'render_sanitize_all_output_field'],
			'affiliate-product-showcase',
			self::SECTION_ID,
			['label_for' => 'sanitize_all_output']
		);
		
		\add_settings_field(
			'enable_xss_protection',
			__('Enable XSS Protection Header', 'affiliate-product-showcase'),
			[$this, 'render_enable_xss_protection_field'],
			'affiliate-product-showcase',
			self::SECTION_ID,
			['label_for' => 'enable_xss_protection']
		);
		
		\add_settings_field(
			'enable_frame_options',
			__('Enable Frame Options', 'affiliate-product-showcase'),
			[$this, 'render_enable_frame_options_field'],
			'affiliate-product-showcase',
			self::SECTION_ID,
			['label_for' => 'enable_frame_options']
		);
		
		\add_settings_field(
			'frame_options_value',
			__('Frame Options Value', 'affiliate-product-showcase'),
			[$this, 'render_frame_options_value_field'],
			'affiliate-product-showcase',
			self::SECTION_ID,
			['label_for' => 'frame_options_value']
		);
		
		\add_settings_field(
			'enable_referrer_policy',
			__('Enable Referrer Policy', 'affiliate-product-showcase'),
			[$this, 'render_enable_referrer_policy_field'],
			'affiliate-product-showcase',
			self::SECTION_ID,
			['label_for' => 'enable_referrer_policy']
		);
		
		\add_settings_field(
			'referrer_policy_value',
			__('Referrer Policy Value', 'affiliate-product-showcase'),
			[$this, 'render_referrer_policy_value_field'],
			'affiliate-product-showcase',
			self::SECTION_ID,
			['label_for' => 'referrer_policy_value']
		);
		
		// Optional security settings
		\add_settings_field(
			'enable_content_security_policy',
			__('Enable Content Security Policy', 'affiliate-product-showcase'),
			[$this, 'render_enable_content_security_policy_field'],
			'affiliate-product-showcase',
			self::SECTION_ID,
			['label_for' => 'enable_content_security_policy']
		);
		
		\add_settings_field(
			'csp_report_only_mode',
			__('CSP Report-Only Mode', 'affiliate-product-showcase'),
			[$this, 'render_csp_report_only_mode_field'],
			'affiliate-product-showcase',
			self::SECTION_ID,
			['label_for' => 'csp_report_only_mode']
		);
		
		\add_settings_field(
			'enable_hsts',
			__('Enable HSTS', 'affiliate-product-showcase'),
			[$this, 'render_enable_hsts_field'],
			'affiliate-product-showcase',
			self::SECTION_ID,
			['label_for' => 'enable_hsts']
		);
		
		\add_settings_field(
			'hsts_max_age',
			__('HSTS Max Age', 'affiliate-product-showcase'),
			[$this, 'render_hsts_max_age_field'],
			'affiliate-product-showcase',
			self::SECTION_ID,
			['label_for' => 'hsts_max_age']
		);
	}
	
	/**
	 * Sanitize section options
	 *
	 * @param array $input
	 * @return array
	 */
	public function sanitize_options(array $input): array {
		$sanitized = [];
		
		// Essential security settings
		$sanitized['enable_nonce_verification'] = isset($input['enable_nonce_verification']);
		$sanitized['enable_rate_limiting'] = isset($input['enable_rate_limiting']);
		$sanitized['rate_limit_requests_per_minute'] = intval($input['rate_limit_requests_per_minute'] ?? 60);
		$sanitized['rate_limit_requests_per_minute'] = max(10, min(500, $sanitized['rate_limit_requests_per_minute']));
		$sanitized['rate_limit_requests_per_hour'] = intval($input['rate_limit_requests_per_hour'] ?? 1000);
		$sanitized['rate_limit_requests_per_hour'] = max(100, min(10000, $sanitized['rate_limit_requests_per_hour']));
		$sanitized['enable_csrf_protection'] = isset($input['enable_csrf_protection']);
		$sanitized['sanitize_all_output'] = isset($input['sanitize_all_output']);
		$sanitized['enable_xss_protection'] = isset($input['enable_xss_protection']);
		$sanitized['enable_frame_options'] = isset($input['enable_frame_options']);
		$sanitized['frame_options_value'] = in_array($input['frame_options_value'] ?? 'SAMEORIGIN', ['DENY', 'SAMEORIGIN', 'ALLOW-FROM']) ? $input['frame_options_value'] : 'SAMEORIGIN';
		$sanitized['enable_referrer_policy'] = isset($input['enable_referrer_policy']);
		$sanitized['referrer_policy_value'] = in_array($input['referrer_policy_value'] ?? 'strict-origin-when-cross-origin', [
			'no-referrer',
			'no-referrer-when-downgrade',
			'origin',
			'origin-when-cross-origin',
			'same-origin',
			'strict-origin',
			'strict-origin-when-cross-origin',
			'unsafe-url'
		]) ? $input['referrer_policy_value'] : 'strict-origin-when-cross-origin';
		
		// Optional security settings
		$sanitized['enable_content_security_policy'] = isset($input['enable_content_security_policy']);
		$sanitized['csp_report_only_mode'] = isset($input['csp_report_only_mode']);
		$sanitized['enable_hsts'] = isset($input['enable_hsts']);
		$sanitized['hsts_max_age'] = intval($input['hsts_max_age'] ?? 31536000);
		$sanitized['hsts_max_age'] = max(86400, min(63072000, $sanitized['hsts_max_age']));
		
		return $sanitized;
	}
	
	/**
	 * Render section description
	 *
	 * @return void
	 */
	public function render_section_description(): void {
		echo '<p>' . esc_html__('Configure security settings including nonce verification, rate limiting, CSRF protection, and security headers.', 'affiliate-product-showcase') . '</p>';
	}
	
	/**
	 * Render enable nonce verification field
	 *
	 * @return void
	 */
	public function render_enable_nonce_verification_field(): void {
		$settings = $this->get_settings();
		$checked = checked($settings['enable_nonce_verification'], true, false);
		echo '<label>';
		echo '<input type="checkbox" name="' . esc_attr($this->option_name) . '[enable_nonce_verification]" value="1" ' . $checked . '> ';
		echo esc_html__('Enable nonce verification for all forms', 'affiliate-product-showcase');
		echo '</label>';
		echo '<p class="description">' . esc_html__('Essential for form security. Protects against CSRF attacks.', 'affiliate-product-showcase') . '</p>';
	}
	
	/**
	 * Render enable rate limiting field
	 *
	 * @return void
	 */
	public function render_enable_rate_limiting_field(): void {
		$settings = $this->get_settings();
		$checked = checked($settings['enable_rate_limiting'], true, false);
		echo '<label>';
		echo '<input type="checkbox" name="' . esc_attr($this->option_name) . '[enable_rate_limiting]" value="1" ' . $checked . '> ';
		echo esc_html__('Enable rate limiting for API requests', 'affiliate-product-showcase');
		echo '</label>';
		echo '<p class="description">' . esc_html__('Protects against brute force and abuse attacks.', 'affiliate-product-showcase') . '</p>';
	}
	
	/**
	 * Render rate limit requests per minute field
	 *
	 * @return void
	 */
	public function render_rate_limit_requests_per_minute_field(): void {
		$settings = $this->get_settings();
		echo '<input type="number" name="' . esc_attr($this->option_name) . '[rate_limit_requests_per_minute]" value="' . esc_attr($settings['rate_limit_requests_per_minute']) . '" min="10" max="500">';
		echo '<p class="description">' . esc_html__('Maximum API requests per minute (10-500).', 'affiliate-product-showcase') . '</p>';
	}
	
	/**
	 * Render rate limit requests per hour field
	 *
	 * @return void
	 */
	public function render_rate_limit_requests_per_hour_field(): void {
		$settings = $this->get_settings();
		echo '<input type="number" name="' . esc_attr($this->option_name) . '[rate_limit_requests_per_hour]" value="' . esc_attr($settings['rate_limit_requests_per_hour']) . '" min="100" max="10000">';
		echo '<p class="description">' . esc_html__('Maximum API requests per hour (100-10000).', 'affiliate-product-showcase') . '</p>';
	}
	
	/**
	 * Render enable CSRF protection field
	 *
	 * @return void
	 */
	public function render_enable_csrf_protection_field(): void {
		$settings = $this->get_settings();
		$checked = checked($settings['enable_csrf_protection'], true, false);
		echo '<label>';
		echo '<input type="checkbox" name="' . esc_attr($this->option_name) . '[enable_csrf_protection]" value="1" ' . $checked . '> ';
		echo esc_html__('Enable CSRF protection for state-changing actions', 'affiliate-product-showcase');
		echo '</label>';
		echo '<p class="description">' . esc_html__('Prevents unauthorized actions from other sites.', 'affiliate-product-showcase') . '</p>';
	}
	
	/**
	 * Render sanitize all output field
	 *
	 * @return void
	 */
	public function render_sanitize_all_output_field(): void {
		$settings = $this->get_settings();
		$checked = checked($settings['sanitize_all_output'], true, false);
		echo '<label>';
		echo '<input type="checkbox" name="' . esc_attr($this->option_name) . '[sanitize_all_output]" value="1" ' . $checked . '> ';
		echo esc_html__('Sanitize all output before rendering', 'affiliate-product-showcase');
		echo '</label>';
		echo '<p class="description">' . esc_html__('Prevents XSS attacks by escaping all output.', 'affiliate-product-showcase') . '</p>';
	}
	
	/**
	 * Render enable XSS protection header field
	 *
	 * @return void
	 */
	public function render_enable_xss_protection_field(): void {
		$settings = $this->get_settings();
		$checked = checked($settings['enable_xss_protection'], true, false);
		echo '<label>';
		echo '<input type="checkbox" name="' . esc_attr($this->option_name) . '[enable_xss_protection]" value="1" ' . $checked . '> ';
		echo esc_html__('Enable X-XSS-Protection header', 'affiliate-product-showcase');
		echo '</label>';
		echo '<p class="description">' . esc_html__('Adds XSS protection header for older browsers.', 'affiliate-product-showcase') . '</p>';
	}
	
	/**
	 * Render enable frame options field
	 *
	 * @return void
	 */
	public function render_enable_frame_options_field(): void {
		$settings = $this->get_settings();
		$checked = checked($settings['enable_frame_options'], true, false);
		echo '<label>';
		echo '<input type="checkbox" name="' . esc_attr($this->option_name) . '[enable_frame_options]" value="1" ' . $checked . '> ';
		echo esc_html__('Enable X-Frame-Options header', 'affiliate-product-showcase');
		echo '</label>';
		echo '<p class="description">' . esc_html__('Prevents clickjacking attacks.', 'affiliate-product-showcase') . '</p>';
	}
	
	/**
	 * Render frame options value field
	 *
	 * @return void
	 */
	public function render_frame_options_value_field(): void {
		$settings = $this->get_settings();
		$options = [
			'DENY' => __('DENY - Block all framing', 'affiliate-product-showcase'),
			'SAMEORIGIN' => __('SAMEORIGIN - Allow same origin only', 'affiliate-product-showcase'),
			'ALLOW-FROM' => __('ALLOW-FROM - Allow from specific URL', 'affiliate-product-showcase'),
		];
		
		echo '<select name="' . esc_attr($this->option_name) . '[frame_options_value]">';
		foreach ($options as $value => $label) {
			$selected = selected($settings['frame_options_value'], $value, false);
			echo '<option value="' . esc_attr($value) . '" ' . $selected . '>' . esc_html($label) . '</option>';
		}
		echo '</select>';
	}
	
	/**
	 * Render enable referrer policy field
	 *
	 * @return void
	 */
	public function render_enable_referrer_policy_field(): void {
		$settings = $this->get_settings();
		$checked = checked($settings['enable_referrer_policy'], true, false);
		echo '<label>';
		echo '<input type="checkbox" name="' . esc_attr($this->option_name) . '[enable_referrer_policy]" value="1" ' . $checked . '> ';
		echo esc_html__('Enable Referrer-Policy header', 'affiliate-product-showcase');
		echo '</label>';
		echo '<p class="description">' . esc_html__('Controls how much referrer information is sent.', 'affiliate-product-showcase') . '</p>';
	}
	
	/**
	 * Render referrer policy value field
	 *
	 * @return void
	 */
	public function render_referrer_policy_value_field(): void {
		$settings = $this->get_settings();
		$options = [
			'no-referrer' => __('no-referrer - No referrer information', 'affiliate-product-showcase'),
			'no-referrer-when-downgrade' => __('no-referrer-when-downgrade', 'affiliate-product-showcase'),
			'origin' => __('origin - Only origin', 'affiliate-product-showcase'),
			'origin-when-cross-origin' => __('origin-when-cross-origin', 'affiliate-product-showcase'),
			'same-origin' => __('same-origin', 'affiliate-product-showcase'),
			'strict-origin' => __('strict-origin', 'affiliate-product-showcase'),
			'strict-origin-when-cross-origin' => __('strict-origin-when-cross-origin', 'affiliate-product-showcase'),
			'unsafe-url' => __('unsafe-url - Full URL (not recommended)', 'affiliate-product-showcase'),
		];
		
		echo '<select name="' . esc_attr($this->option_name) . '[referrer_policy_value]">';
		foreach ($options as $value => $label) {
			$selected = selected($settings['referrer_policy_value'], $value, false);
			echo '<option value="' . esc_attr($value) . '" ' . $selected . '>' . esc_html($label) . '</option>';
		}
		echo '</select>';
	}
	
	/**
	 * Render enable content security policy field
	 *
	 * @return void
	 */
	public function render_enable_content_security_policy_field(): void {
		$settings = $this->get_settings();
		$checked = checked($settings['enable_content_security_policy'], true, false);
		echo '<label>';
		echo '<input type="checkbox" name="' . esc_attr($this->option_name) . '[enable_content_security_policy]" value="1" ' . $checked . '> ';
		echo esc_html__('Enable Content-Security-Policy header', 'affiliate-product-showcase');
		echo '</label>';
		echo '<p class="description">' . esc_html__('Advanced security feature. Test thoroughly in report-only mode first.', 'affiliate-product-showcase') . '</p>';
	}
	
	/**
	 * Render CSP report only mode field
	 *
	 * @return void
	 */
	public function render_csp_report_only_mode_field(): void {
		$settings = $this->get_settings();
		$checked = checked($settings['csp_report_only_mode'], true, false);
		echo '<label>';
		echo '<input type="checkbox" name="' . esc_attr($this->option_name) . '[csp_report_only_mode]" value="1" ' . $checked . '> ';
		echo esc_html__('CSP Report-Only mode (testing)', 'affiliate-product-showcase');
		echo '</label>';
		echo '<p class="description">' . esc_html__('Report violations without blocking. Use for testing CSP rules.', 'affiliate-product-showcase') . '</p>';
	}
	
	/**
	 * Render enable HSTS field
	 *
	 * @return void
	 */
	public function render_enable_hsts_field(): void {
		$settings = $this->get_settings();
		$checked = checked($settings['enable_hsts'], true, false);
		echo '<label>';
		echo '<input type="checkbox" name="' . esc_attr($this->option_name) . '[enable_hsts]" value="1" ' . $checked . '> ';
		echo esc_html__('Enable HTTP Strict Transport Security', 'affiliate-product-showcase');
		echo '</label>';
		echo '<p class="description">' . esc_html__('Enforces HTTPS. Server-level setting recommended.', 'affiliate-product-showcase') . '</p>';
	}
	
	/**
	 * Render HSTS max age field
	 *
	 * @return void
	 */
	public function render_hsts_max_age_field(): void {
		$settings = $this->get_settings();
		$value_days = intval($settings['hsts_max_age'] / 86400);
		echo '<input type="number" name="' . esc_attr($this->option_name) . '[hsts_max_age]" value="' . esc_attr($settings['hsts_max_age']) . '" min="86400" max="63072000">';
		echo '<p class="description">' . esc_html__('HSTS max age in seconds (86400-63072000). Current: ' . esc_html($value_days) . ' days.', 'affiliate-product-showcase') . '</p>';
	}
}