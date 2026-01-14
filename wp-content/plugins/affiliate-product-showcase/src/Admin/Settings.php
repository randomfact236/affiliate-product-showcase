<?php
declare(strict_types=1);

namespace AffiliateProductShowcase\Admin;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use AffiliateProductShowcase\Plugin\Constants;
use AffiliateProductShowcase\Repositories\SettingsRepository;

final class Settings {
	private SettingsRepository $repository;

	public function __construct() {
		$this->repository = new SettingsRepository();
	}

	public function register(): void {
		register_setting( Constants::SLUG, 'aps_settings', [
			'sanitize_callback' => [ $this, 'sanitize' ],
			'show_in_rest' => false,
		] );
		
		add_settings_section( 'aps_general', __( 'General', Constants::TEXTDOMAIN ), '__return_false', Constants::SLUG );

		add_settings_field( 'aps_currency', __( 'Currency', Constants::TEXTDOMAIN ), [ $this, 'field_currency' ], Constants::SLUG, 'aps_general' );
		add_settings_field( 'aps_affiliate_id', __( 'Affiliate ID', Constants::TEXTDOMAIN ), [ $this, 'field_affiliate_id' ], Constants::SLUG, 'aps_general' );
		add_settings_field( 'aps_enable_ratings', __( 'Enable ratings', Constants::TEXTDOMAIN ), [ $this, 'field_enable_ratings' ], Constants::SLUG, 'aps_general' );
		add_settings_field( 'aps_enable_cache', __( 'Enable cache', Constants::TEXTDOMAIN ), [ $this, 'field_enable_cache' ], Constants::SLUG, 'aps_general' );
		add_settings_field( 'aps_cta_label', __( 'CTA label', Constants::TEXTDOMAIN ), [ $this, 'field_cta_label' ], Constants::SLUG, 'aps_general' );
	}

	public function get(): array {
		return $this->repository->get_settings();
	}

	public function sanitize( array $input ): array {
		// Verify nonce for CSRF protection
		if ( ! isset( $_POST['option_page'] ) || 
		     ! isset( $_POST['_wpnonce'] ) || 
		     ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['_wpnonce'] ) ), 'aps_settings-options' ) ) {
			add_settings_error( 
				Constants::SLUG, 
				'invalid_nonce', 
				__( 'Security check failed. Please try again.', Constants::TEXTDOMAIN ), 
				'error' 
			);
			return $this->repository->get_settings();
		}

		$this->repository->update_settings( $input );
		return $this->repository->get_settings();
	}

	public function field_currency(): void {
		$value = $this->get()['currency'];
		?>
		<input type="text" name="aps_settings[currency]" value="<?php echo esc_attr( $value ); ?>" class="regular-text" />
		<?php
	}

	public function field_affiliate_id(): void {
		$value = $this->get()['affiliate_id'];
		?>
		<input type="text" name="aps_settings[affiliate_id]" value="<?php echo esc_attr( $value ); ?>" class="regular-text" />
		<?php
	}

	public function field_enable_ratings(): void {
		$checked = $this->get()['enable_ratings'];
		?>
		<label>
			<input type="checkbox" name="aps_settings[enable_ratings]" value="1" <?php checked( $checked ); ?> />
			<?php esc_html_e( 'Show ratings on product cards', Constants::TEXTDOMAIN ); ?>
		</label>
		<?php
	}

	public function field_enable_cache(): void {
		$checked = $this->get()['enable_cache'];
		?>
		<label>
			<input type="checkbox" name="aps_settings[enable_cache]" value="1" <?php checked( $checked ); ?> />
			<?php esc_html_e( 'Use WordPress object cache when available', Constants::TEXTDOMAIN ); ?>
		</label>
		<?php
	}

	public function field_cta_label(): void {
		$value = $this->get()['cta_label'];
		?>
		<input type="text" name="aps_settings[cta_label]" value="<?php echo esc_attr( $value ); ?>" class="regular-text" />
		<?php
	}
}
