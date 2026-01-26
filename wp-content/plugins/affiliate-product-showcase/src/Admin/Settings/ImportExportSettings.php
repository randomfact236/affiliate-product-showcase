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
	const SECTION_DESCRIPTION = 'Configure data import/export settings and automatic backups.';
	
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
	 * Register settings section and fields
	 *
	 * @return void
	 */
	public function register_section_and_fields(): void {
		// Register section
		\add_settings_section(
			self::SECTION_ID,
			self::SECTION_TITLE,
			[$this, 'render_section_description'],
			'affiliate-product-showcase'
		);
		
		// Import Settings
		$this->add_field(
			'import_encoding',
			'Import File Encoding',
			'Select the character encoding for imported files. UTF-8 is recommended for most modern systems.',
			'select',
			$this->encoding_options
		);
		
		// Export Settings
		$this->add_field(
			'export_format',
			'Export Format',
			'Choose the default format for exported data. CSV is compatible with spreadsheet applications.',
			'select',
			$this->export_format_options
		);
		
		$this->add_field(
			'export_include_images',
			'Include Image URLs',
			'Include full image URLs in export data. This increases file size but preserves complete product data.',
			'checkbox'
		);
		
		$this->add_field(
			'export_include_metadata',
			'Include Metadata',
			'Include product metadata (custom fields, SEO data, etc.) in export.',
			'checkbox'
		);
		
		$this->add_field(
			'export_delimiter',
			'CSV Delimiter',
			'Character used to separate values in CSV files. Comma is standard.',
			'select',
			$this->delimiter_options
		);
		
		$this->add_field(
			'export_enclosure',
			'CSV Enclosure',
			'Character used to enclose text fields in CSV files. Double quote is standard.',
			'select',
			$this->enclosure_options
		);
		
		$this->add_field(
			'export_line_ending',
			'CSV Line Ending',
			'Line ending style for CSV files. Windows format (CRLF) is most compatible.',
			'select',
			$this->line_ending_options
		);
		
		// Backup Settings
		$this->add_field(
			'enable_auto_backup',
			'Enable Automatic Backups',
			'Automatically create backups of settings at regular intervals.',
			'checkbox'
		);
		
		$this->add_field(
			'backup_frequency',
			'Backup Frequency',
			'How often to create automatic backups when auto-backup is enabled.',
			'select',
			$this->backup_frequency_options
		);
		
		$this->add_field(
			'backup_retention',
			'Backup Retention',
			'Number of backup files to retain. Older backups will be automatically deleted.',
			'number',
			['min' => 1, 'max' => 30]
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
	 * Render number field with attributes
	 *
	 * @param array $args
	 * @return void
	 */
	protected function render_number_field(array $args): void {
		$name = $args['label_for'];
		$value = $this->get_value($name);
		$attrs = $args['attrs'] ?? [];
		
		$min = $attrs['min'] ?? 1;
		$max = $attrs['max'] ?? 100;
		$step = $attrs['step'] ?? 1;
		
		printf(
			'<input type="number" id="%1$s" name="%2$s[%1$s]" value="%3$s" min="%4$d" max="%5$d" step="%6$d" class="regular-text">',
			esc_attr($name),
			esc_attr($this->option_name),
			esc_attr($value),
			(int) $min,
			(int) $max,
			(int) $step
		);
		
		if (isset($args['description'])) {
			printf('<p class="description">%s</p>', wp_kses_post($args['description']));
		}
	}
}