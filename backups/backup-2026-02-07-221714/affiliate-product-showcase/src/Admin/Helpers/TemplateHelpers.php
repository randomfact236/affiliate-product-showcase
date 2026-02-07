<?php

declare(strict_types=1);

namespace AffiliateProductShowcase\Admin\Helpers;

/**
 * Template Helpers
 *
 * Provides reusable template rendering functions for the admin interface.
 * Separates presentation logic from business logic.
 *
 * @package AffiliateProductShowcase\Admin\Helpers
 * @since 1.0.0
 */
class TemplateHelpers {

	/**
	 * Render image upload area
	 *
	 * @param string $id        Base ID for the upload area
	 * @param string $label     Label text
	 * @param string $value     Current URL value
	 * @param string $icon      Font Awesome icon class
	 * @return string HTML output
	 */
	public static function renderImageUploadArea(
		string $id,
		string $label,
		string $value = '',
		string $icon = 'fa-camera'
	): string {
		$hasImage = !empty($value);
		$previewClass = $hasImage ? 'has-image' : 'no-image';
		$removeClass = $hasImage ? '' : 'aps-hidden';
		$placeholderStyle = $hasImage ? 'display: none;' : '';

		ob_start();
		?>
		<div class="aps-upload-group">
			<label><?php echo esc_html($label); ?></label>
			<div class="aps-upload-area" id="<?php echo esc_attr($id); ?>-upload">
				<div class="upload-placeholder" style="<?php echo esc_attr($placeholderStyle); ?>">
					<i class="fas <?php echo esc_attr($icon); ?>"></i>
					<p>Click to upload or enter URL below</p>
				</div>
				<div class="image-preview aps-<?php echo esc_attr($id); ?>-preview <?php echo $previewClass; ?>" 
				     id="<?php echo esc_attr($id); ?>-preview" 
				     data-image-url="<?php echo esc_url($value); ?>"></div>
				<input type="hidden" name="<?php echo esc_attr($id); ?>_url" id="<?php echo esc_attr($id); ?>-url" value="<?php echo esc_attr($value); ?>">
				<button type="button" class="aps-upload-btn" id="<?php echo esc_attr($id); ?>-upload-btn">
					<i class="fas fa-upload"></i> Select from Media Library
				</button>
				<button type="button" class="aps-upload-btn aps-btn-cancel <?php echo $removeClass; ?>" id="<?php echo esc_attr($id); ?>-remove-btn">
					<i class="fas fa-times"></i> Remove
				</button>
			</div>
			<div class="aps-url-input">
				<input type="url" name="<?php echo esc_attr($id); ?>_url_input" class="aps-input"
					   placeholder="https://..." id="<?php echo esc_attr($id); ?>-url-input"
					   value="<?php echo esc_attr($value); ?>">
			</div>
		</div>
		<?php
		return ob_get_clean();
	}

	/**
	 * Render form field group
	 *
	 * @param string $label        Field label
	 * @param string $name         Field name
	 * @param string $id           Field ID
	 * @param string $type         Input type (text, number, url, etc.)
	 * @param string $value        Current value
	 * @param string $placeholder   Placeholder text
	 * @param bool   $required      Whether field is required
	 * @param array  $attributes   Additional HTML attributes
	 * @return string HTML output
	 */
	public static function renderField(
		string $label,
		string $name,
		string $id,
		string $type = 'text',
		string $value = '',
		string $placeholder = '',
		bool $required = false,
		array $attributes = []
	): string {
		$requiredAttr = $required ? 'required' : '';
		$requiredMark = $required ? ' <span class="required">*</span>' : '';
		$attrString = '';
		
		foreach ($attributes as $key => $val) {
			$attrString .= sprintf(' %s="%s"', esc_attr($key), esc_attr($val));
		}

		ob_start();
		?>
		<div class="aps-field-group">
			<label for="<?php echo esc_attr($id); ?>"><?php echo esc_html($label); ?><?php echo $requiredMark; ?></label>
			<input type="<?php echo esc_attr($type); ?>" 
				   id="<?php echo esc_attr($id); ?>" 
				   name="<?php echo esc_attr($name); ?>" 
				   class="aps-input"
				   placeholder="<?php echo esc_attr($placeholder); ?>"
				   value="<?php echo esc_attr($value); ?>"
				   <?php echo $requiredAttr; ?>
				   <?php echo $attrString; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>>
		</div>
		<?php
		return ob_get_clean();
	}

	/**
	 * Render textarea field
	 *
	 * @param string $label        Field label
	 * @param string $name         Field name
	 * @param string $id           Field ID
	 * @param string $value        Current value
	 * @param int    $rows         Number of rows
	 * @param string $placeholder   Placeholder text
	 * @param bool   $required      Whether field is required
	 * @param array  $attributes   Additional HTML attributes
	 * @return string HTML output
	 */
	public static function renderTextarea(
		string $label,
		string $name,
		string $id,
		string $value = '',
		int $rows = 6,
		string $placeholder = '',
		bool $required = false,
		array $attributes = []
	): string {
		$requiredAttr = $required ? 'required' : '';
		$requiredMark = $required ? ' <span class="required">*</span>' : '';
		$attrString = '';
		
		foreach ($attributes as $key => $val) {
			$attrString .= sprintf(' %s="%s"', esc_attr($key), esc_attr($val));
		}

		ob_start();
		?>
		<div class="aps-field-group">
			<label for="<?php echo esc_attr($id); ?>"><?php echo esc_html($label); ?><?php echo $requiredMark; ?></label>
			<textarea id="<?php echo esc_attr($id); ?>" 
					  name="<?php echo esc_attr($name); ?>" 
					  class="aps-textarea aps-full-page"
					  rows="<?php echo esc_attr($rows); ?>"
					  placeholder="<?php echo esc_attr($placeholder); ?>"
					  <?php echo $requiredAttr; ?>
					  <?php echo $attrString; ?>><?php echo esc_textarea($value); ?></textarea>
		</div>
		<?php
		return ob_get_clean();
	}

	/**
	 * Render select dropdown
	 *
	 * @param string $label        Field label
	 * @param string $name         Field name
	 * @param string $id           Field ID
	 * @param array  $options      Array of value => label pairs
	 * @param string $selected     Currently selected value
	 * @param bool   $required      Whether field is required
	 * @return string HTML output
	 */
	public static function renderSelect(
		string $label,
		string $name,
		string $id,
		array $options,
		string $selected = '',
		bool $required = false
	): string {
		$requiredAttr = $required ? 'required' : '';
		$requiredMark = $required ? ' <span class="required">*</span>' : '';

		ob_start();
		?>
		<div class="aps-field-group">
			<label for="<?php echo esc_attr($id); ?>"><?php echo esc_html($label); ?><?php echo $requiredMark; ?></label>
			<select id="<?php echo esc_attr($id); ?>" name="<?php echo esc_attr($name); ?>" class="aps-select" <?php echo $requiredAttr; ?>>
				<?php foreach ($options as $value => $optionLabel): ?>
					<option value="<?php echo esc_attr($value); ?>" <?php selected($selected, $value); ?>>
						<?php echo esc_html($optionLabel); ?>
					</option>
				<?php endforeach; ?>
			</select>
		</div>
		<?php
		return ob_get_clean();
	}

	/**
	 * Render checkbox field
	 *
	 * @param string $label     Checkbox label
	 * @param string $name      Field name
	 * @param string $id        Field ID
	 * @param string $value     Field value
	 * @param bool   $checked    Whether checkbox is checked
	 * @return string HTML output
	 */
	public static function renderCheckbox(
		string $label,
		string $name,
		string $id,
		string $value = '1',
		bool $checked = false
	): string {
		$checkedAttr = $checked ? 'checked' : '';

		ob_start();
		?>
		<div class="aps-field-group">
			<label class="aps-checkbox-label">
				<input type="checkbox" id="<?php echo esc_attr($id); ?>" name="<?php echo esc_attr($name); ?>" value="<?php echo esc_attr($value); ?>" <?php echo $checkedAttr; ?>>
				<span><?php echo esc_html($label); ?></span>
			</label>
		</div>
		<?php
		return ob_get_clean();
	}

	/**
	 * Render multi-select dropdown
	 *
	 * @param string $label           Field label
	 * @param string $containerId     Container element ID
	 * @param string $selectedId      Selected items container ID
	 * @param string $dropdownId      Dropdown element ID
	 * @param string $inputId        Hidden input element ID
	 * @param string $placeholder     Placeholder text
	 * @param array  $items           Dropdown items (array of ['value' => ..., 'text' => ...])
	 * @return string HTML output
	 */
	public static function renderMultiSelect(
		string $label,
		string $containerId,
		string $selectedId,
		string $dropdownId,
		string $inputId,
		string $placeholder,
		array $items = []
	): string {
		ob_start();
		?>
		<div class="aps-field-group">
			<label><?php echo esc_html($label); ?></label>
			<div class="aps-multi-select" id="<?php echo esc_attr($containerId); ?>">
				<div class="aps-selected-tags" id="<?php echo esc_attr($selectedId); ?>">
					<span class="multi-select-placeholder"><?php echo esc_html($placeholder); ?></span>
				</div>
				<input type="text" class="aps-multiselect-input" placeholder="<?php echo esc_attr($placeholder); ?>">
				<div class="aps-dropdown aps-hidden" id="<?php echo esc_attr($dropdownId); ?>">
					<?php foreach ($items as $item): ?>
						<div class="dropdown-item" data-value="<?php echo esc_attr($item['value'] ?? ''); ?>">
							<?php echo $item['html'] ?? esc_html($item['text'] ?? ''); ?>
						</div>
					<?php endforeach; ?>
				</div>
			</div>
			<input type="hidden" name="<?php echo esc_attr($inputId); ?>" id="<?php echo esc_attr($inputId); ?>">
		</div>
		<?php
		return ob_get_clean();
	}

	/**
	 * Render word counter
	 *
	 * @param int    $current   Current word count
	 * @param int    $max       Maximum word count
	 * @param string $label     Counter label
	 * @return string HTML output
	 */
	public static function renderWordCounter(int $current, int $max, string $label = 'Words'): string {
		ob_start();
		?>
		<div class="word-counter">
			<span id="aps-word-count"><?php echo esc_html($current); ?></span>/<?php echo esc_html($max); ?> <?php echo esc_html($label); ?>
		</div>
		<?php
		return ob_get_clean();
	}

	/**
	 * Render section header
	 *
	 * @param string $title Section title
	 * @param string $id   Section ID for anchor links
	 * @return string HTML output
	 */
	public static function renderSectionHeader(string $title, string $id): string {
		ob_start();
		?>
		<section id="<?php echo esc_attr($id); ?>" class="aps-section">
			<h2 class="section-title"><?php echo esc_html($title); ?></h2>
		<?php
		return ob_get_clean();
	}

	/**
	 * Render section footer
	 *
	 * @return string HTML output
	 */
	public static function renderSectionFooter(): string {
		ob_start();
		?>
		</section>
		<?php
		return ob_get_clean();
	}

	/**
	 * Escape and render HTML attributes
	 *
	 * @param array $attributes Array of attribute => value pairs
	 * @return string HTML attribute string
	 */
	public static function renderAttributes(array $attributes): string {
		$output = '';
		foreach ($attributes as $key => $value) {
			if (is_bool($value)) {
				if ($value) {
					$output .= ' ' . esc_attr($key);
				}
			} else {
				$output .= sprintf(' %s="%s"', esc_attr($key), esc_attr($value));
			}
		}
		return $output;
	}
}
