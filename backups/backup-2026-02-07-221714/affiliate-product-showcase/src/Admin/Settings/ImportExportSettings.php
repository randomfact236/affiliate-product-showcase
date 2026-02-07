<?php
declare(strict_types=1);

namespace AffiliateProductShowcase\Admin\Settings;

use AffiliateProductShowcase\Admin\Settings\AbstractSettingsSection;

/**
 * Import/Export Settings Section
 *
 * Handles settings for data import/export functionality including
 * file encoding, export formats, delimiters, and automatic backups.
 *
 * @package AffiliateProductShowcase\Admin\Settings
 * @since 1.0.0
 * @author Development Team
 */
final class ImportExportSettings extends AbstractSettingsSection {
	
	const SECTION_ID = 'affiliate_product_showcase_import_export';
	const SECTION_TITLE = 'Import/Export';
	
	/**
	 * @var array
	 */
	private array $encoding_options = [
		'UTF-8' => 'UTF-8',
		'ISO-8859-1' => 'ISO-8859-1',
		'Windows-1252' => 'Windows-1252',
	];
	
	/**
	 * @var array
	 */
	private array $export_format_options = [
		'csv' => 'CSV',
		'xml' => 'XML',
		'json' => 'JSON',
	];
	
	/**
	 * @var array
	 */
	private array $delimiter_options = [
		',' => 'Comma (,)',
		';' => 'Semicolon (;)',
		"\t" => 'Tab',
	];
	
	/**
	 * @var array
	 */
	private array $enclosure_options = [
		'"' => 'Double Quote (")',
		"'" => 'Single Quote (\')',
		'none' => 'None',
	];
	
	/**
	 * @var array
	 */
	private array $line_ending_options = [
		'CRLF' => 'Windows (CRLF)',
		'LF' => 'Unix/Linux (LF)',
		'CR' => 'Mac (CR)',
	];
	
	/**
	 * @var array
	 */
	private array $backup_frequency_options = [
		'daily' => 'Daily',
		'weekly' => 'Weekly',
		'monthly' => 'Monthly',
	];
	
	/**
	 * Get default settings values
	 *
	 * @return array
	 */
	public function get_defaults(): array {
		return [
			'import_encoding' => 'UTF-8',
			'export_format' => 'csv',
			'export_include_images' => false,
			'export_include_metadata' => true,
			'export_delimiter' => ',',
			'export_enclosure' => '"',
			'export_line_ending' => 'CRLF',
			'enable_auto_backup' => false,
			'backup_frequency' => 'daily',
			'backup_retention' => 7,
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
			__('Import/Export', 'affiliate-product-showcase'),
			[$this, 'render_section_description'],
			'affiliate-product-showcase',
			['data-section' => 'import_export']
		);
		
		// Import Settings
		\add_settings_field(
			'import_encoding',
			__('Import File Encoding', 'affiliate-product-showcase'),
			[$this, 'render_import_encoding_field'],
			'affiliate-product-showcase',
			self::SECTION_ID,
			['label_for' => 'import_encoding']
		);
		
		// Export Settings
		\add_settings_field(
			'export_format',
			__('Export Format', 'affiliate-product-showcase'),
			[$this, 'render_export_format_field'],
			'affiliate-product-showcase',
			self::SECTION_ID,
			['label_for' => 'export_format']
		);
		
		\add_settings_field(
			'export_include_images',
			__('Include Image URLs', 'affiliate-product-showcase'),
			[$this, 'render_export_include_images_field'],
			'affiliate-product-showcase',
			self::SECTION_ID,
			['label_for' => 'export_include_images']
		);
		
		\add_settings_field(
			'export_include_metadata',
			__('Include Metadata', 'affiliate-product-showcase'),
			[$this, 'render_export_include_metadata_field'],
			'affiliate-product-showcase',
			self::SECTION_ID,
			['label_for' => 'export_include_metadata']
		);
		
		\add_settings_field(
			'export_delimiter',
			__('CSV Delimiter', 'affiliate-product-showcase'),
			[$this, 'render_export_delimiter_field'],
			'affiliate-product-showcase',
			self::SECTION_ID,
			['label_for' => 'export_delimiter']
		);
		
		\add_settings_field(
			'export_enclosure',
			__('CSV Enclosure', 'affiliate-product-showcase'),
			[$this, 'render_export_enclosure_field'],
			'affiliate-product-showcase',
			self::SECTION_ID,
			['label_for' => 'export_enclosure']
		);
		
		\add_settings_field(
			'export_line_ending',
			__('CSV Line Ending', 'affiliate-product-showcase'),
			[$this, 'render_export_line_ending_field'],
			'affiliate-product-showcase',
			self::SECTION_ID,
			['label_for' => 'export_line_ending']
		);
		
		// Backup Settings
		\add_settings_field(
			'enable_auto_backup',
			__('Enable Automatic Backups', 'affiliate-product-showcase'),
			[$this, 'render_enable_auto_backup_field'],
			'affiliate-product-showcase',
			self::SECTION_ID,
			['label_for' => 'enable_auto_backup']
		);
		
		\add_settings_field(
			'backup_frequency',
			__('Backup Frequency', 'affiliate-product-showcase'),
			[$this, 'render_backup_frequency_field'],
			'affiliate-product-showcase',
			self::SECTION_ID,
			['label_for' => 'backup_frequency']
		);
		
		\add_settings_field(
			'backup_retention',
			__('Backup Retention', 'affiliate-product-showcase'),
			[$this, 'render_backup_retention_field'],
			'affiliate-product-showcase',
			self::SECTION_ID,
			['label_for' => 'backup_retention']
		);
	}
	
	/**
	 * Sanitize settings options
	 *
	 * @param array $input
	 * @return array
	 */
	public function sanitize_options(array $input): array {
		$sanitized = [];
		
		// Import Settings
		if (isset($input['import_encoding'])) {
			$sanitized['import_encoding'] = in_array($input['import_encoding'], array_keys($this->encoding_options))
				? sanitize_text_field($input['import_encoding'])
				: 'UTF-8';
		}
		
		// Export Settings
		if (isset($input['export_format'])) {
			$sanitized['export_format'] = in_array($input['export_format'], array_keys($this->export_format_options))
				? sanitize_text_field($input['export_format'])
				: 'csv';
		}
		
		$sanitized['export_include_images'] = isset($input['export_include_images']) ? (bool) $input['export_include_images'] : false;
		$sanitized['export_include_metadata'] = isset($input['export_include_metadata']) ? (bool) $input['export_include_metadata'] : true;
		
		if (isset($input['export_delimiter'])) {
			$sanitized['export_delimiter'] = in_array($input['export_delimiter'], array_keys($this->delimiter_options))
				? $input['export_delimiter']
				: ',';
		}
		
		if (isset($input['export_enclosure'])) {
			$sanitized['export_enclosure'] = in_array($input['export_enclosure'], array_keys($this->enclosure_options))
				? sanitize_text_field($input['export_enclosure'])
				: '"';
		}
		
		if (isset($input['export_line_ending'])) {
			$sanitized['export_line_ending'] = in_array($input['export_line_ending'], array_keys($this->line_ending_options))
				? sanitize_text_field($input['export_line_ending'])
				: 'CRLF';
		}
		
		// Backup Settings
		$sanitized['enable_auto_backup'] = isset($input['enable_auto_backup']) ? (bool) $input['enable_auto_backup'] : false;
		
		if (isset($input['backup_frequency'])) {
			$sanitized['backup_frequency'] = in_array($input['backup_frequency'], array_keys($this->backup_frequency_options))
				? sanitize_text_field($input['backup_frequency'])
				: 'daily';
		}
		
		if (isset($input['backup_retention'])) {
			$retention = intval($input['backup_retention']);
			$sanitized['backup_retention'] = max(1, min(30, $retention));
		}
		
		return $sanitized;
	}
	
	/**
	 * Render section description
	 *
	 * @return void
	 */
	public function render_section_description(): void {
		echo '<p>' . esc_html__('Configure how products and settings are imported from and exported to files, plus automatic backup settings.', 'affiliate-product-showcase') . '</p>';
	}
	
	/**
	 * Render import encoding field
	 *
	 * @return void
	 */
	public function render_import_encoding_field(): void {
		$settings = $this->get_settings();
		echo '<select name="' . esc_attr($this->option_name) . '[import_encoding]">';
		foreach ($this->encoding_options as $value => $label) {
			$selected = selected($settings['import_encoding'] ?? 'UTF-8', $value, false);
			echo '<option value="' . esc_attr($value) . '" ' . $selected . '>' . esc_html($label) . '</option>';
		}
		echo '</select>';
		echo '<p class="description">' . esc_html__('Select character encoding for imported files. UTF-8 is recommended for most modern systems.', 'affiliate-product-showcase') . '</p>';
	}
	
	/**
	 * Render export format field
	 *
	 * @return void
	 */
	public function render_export_format_field(): void {
		$settings = $this->get_settings();
		echo '<select name="' . esc_attr($this->option_name) . '[export_format]">';
		foreach ($this->export_format_options as $value => $label) {
			$selected = selected($settings['export_format'] ?? 'csv', $value, false);
			echo '<option value="' . esc_attr($value) . '" ' . $selected . '>' . esc_html($label) . '</option>';
		}
		echo '</select>';
		echo '<p class="description">' . esc_html__('Choose the default format for exported data. CSV is compatible with spreadsheet applications.', 'affiliate-product-showcase') . '</p>';
	}
	
	/**
	 * Render export include images field
	 *
	 * @return void
	 */
	public function render_export_include_images_field(): void {
		$settings = $this->get_settings();
		$value = $settings['export_include_images'] ?? false;
		echo '<input type="checkbox" name="' . esc_attr($this->option_name) . '[export_include_images]" value="1" ' . checked($value, true, false) . '>';
		echo '<p class="description">' . esc_html__('Include full image URLs in export data. This increases file size but preserves complete product data.', 'affiliate-product-showcase') . '</p>';
	}
	
	/**
	 * Render export include metadata field
	 *
	 * @return void
	 */
	public function render_export_include_metadata_field(): void {
		$settings = $this->get_settings();
		$value = $settings['export_include_metadata'] ?? true;
		echo '<input type="checkbox" name="' . esc_attr($this->option_name) . '[export_include_metadata]" value="1" ' . checked($value, true, false) . '>';
		echo '<p class="description">' . esc_html__('Include product metadata (custom fields, SEO data, etc.) in export.', 'affiliate-product-showcase') . '</p>';
	}
	
	/**
	 * Render export delimiter field
	 *
	 * @return void
	 */
	public function render_export_delimiter_field(): void {
		$settings = $this->get_settings();
		echo '<select name="' . esc_attr($this->option_name) . '[export_delimiter]">';
		foreach ($this->delimiter_options as $value => $label) {
			$selected = selected($settings['export_delimiter'] ?? ',', $value, false);
			echo '<option value="' . esc_attr($value) . '" ' . $selected . '>' . esc_html($label) . '</option>';
		}
		echo '</select>';
		echo '<p class="description">' . esc_html__('Character used to separate values in CSV files. Comma is standard.', 'affiliate-product-showcase') . '</p>';
	}
	
	/**
	 * Render export enclosure field
	 *
	 * @return void
	 */
	public function render_export_enclosure_field(): void {
		$settings = $this->get_settings();
		echo '<select name="' . esc_attr($this->option_name) . '[export_enclosure]">';
		foreach ($this->enclosure_options as $value => $label) {
			$selected = selected($settings['export_enclosure'] ?? '"', $value, false);
			echo '<option value="' . esc_attr($value) . '" ' . $selected . '>' . esc_html($label) . '</option>';
		}
		echo '</select>';
		echo '<p class="description">' . esc_html__('Character used to enclose text fields in CSV files. Double quote is standard.', 'affiliate-product-showcase') . '</p>';
	}
	
	/**
	 * Render export line ending field
	 *
	 * @return void
	 */
	public function render_export_line_ending_field(): void {
		$settings = $this->get_settings();
		echo '<select name="' . esc_attr($this->option_name) . '[export_line_ending]">';
		foreach ($this->line_ending_options as $value => $label) {
			$selected = selected($settings['export_line_ending'] ?? 'CRLF', $value, false);
			echo '<option value="' . esc_attr($value) . '" ' . $selected . '>' . esc_html($label) . '</option>';
		}
		echo '</select>';
		echo '<p class="description">' . esc_html__('Line ending style for CSV files. Windows format (CRLF) is most compatible.', 'affiliate-product-showcase') . '</p>';
	}
	
	/**
	 * Render enable auto backup field
	 *
	 * @return void
	 */
	public function render_enable_auto_backup_field(): void {
		$settings = $this->get_settings();
		$value = $settings['enable_auto_backup'] ?? false;
		echo '<input type="checkbox" name="' . esc_attr($this->option_name) . '[enable_auto_backup]" value="1" ' . checked($value, true, false) . '>';
		echo '<p class="description">' . esc_html__('Automatically create backups of settings at regular intervals.', 'affiliate-product-showcase') . '</p>';
	}
	
	/**
	 * Render backup frequency field
	 *
	 * @return void
	 */
	public function render_backup_frequency_field(): void {
		$settings = $this->get_settings();
		echo '<select name="' . esc_attr($this->option_name) . '[backup_frequency]">';
		foreach ($this->backup_frequency_options as $value => $label) {
			$selected = selected($settings['backup_frequency'] ?? 'daily', $value, false);
			echo '<option value="' . esc_attr($value) . '" ' . $selected . '>' . esc_html($label) . '</option>';
		}
		echo '</select>';
		echo '<p class="description">' . esc_html__('How often to create automatic backups when auto-backup is enabled.', 'affiliate-product-showcase') . '</p>';
	}
	
	/**
	 * Render backup retention field
	 *
	 * @return void
	 */
	public function render_backup_retention_field(): void {
		$settings = $this->get_settings();
		$value = $settings['backup_retention'] ?? 7;
		echo '<input type="number" name="' . esc_attr($this->option_name) . '[backup_retention]" value="' . esc_attr($value) . '" min="1" max="30" class="regular-text">';
		echo '<p class="description">' . esc_html__('Number of backup files to retain. Older backups will be automatically deleted.', 'affiliate-product-showcase') . '</p>';
	}
}