<?php
declare(strict_types=1);

namespace AffiliateProductShowcase\Admin;

use AffiliateProductShowcase\Plugin\Constants;

/**
 * Ribbon custom fields
 *
 * Adds custom fields to the ribbon taxonomy edit form.
 *
 * @package AffiliateProductShowcase\Admin
 * @since 1.0.0
 */
final class RibbonFields {
    /**
     * Initialize ribbon fields
     *
     * @return void
     * @since 1.0.0
     */
    public function init(): void {
        // Add fields to add term form
        add_action( 'aps_ribbon_add_form_fields', [ $this, 'render_add_fields' ] );
        
        // Add fields to edit term form
        add_action( 'aps_ribbon_edit_form_fields', [ $this, 'render_edit_fields' ] );
        
        // Save fields on term creation
        add_action( 'created_aps_ribbon', [ $this, 'save_fields' ], 10, 2 );
        
        // Save fields on term update
        add_action( 'edited_aps_ribbon', [ $this, 'save_fields' ], 10, 2 );
    }

    /**
     * Render fields in add form
     *
     * @param string $taxonomy Taxonomy name
     */
    public function render_add_fields( string $taxonomy ): void {
        ?>
        <div class="form-field">
            <label for="aps_ribbon_color"><?php esc_html_e( 'Color', Constants::TEXTDOMAIN ); ?></label>
            <input type="color" name="aps_ribbon_color" id="aps_ribbon_color" value="#ff6b6b" class="color-picker" />
            <p class="description"><?php esc_html_e( 'Select a color for the ribbon badge.', Constants::TEXTDOMAIN ); ?></p>
        </div>

        <div class="form-field">
            <label for="aps_ribbon_icon"><?php esc_html_e( 'Icon', Constants::TEXTDOMAIN ); ?></label>
            <input type="text" name="aps_ribbon_icon" id="aps_ribbon_icon" value="" class="regular-text" />
            <p class="description"><?php esc_html_e( 'Enter an icon class or identifier (e.g., "star", "badge").', Constants::TEXTDOMAIN ); ?></p>
        </div>

        <div class="form-field">
            <label for="aps_ribbon_priority"><?php esc_html_e( 'Priority', Constants::TEXTDOMAIN ); ?></label>
            <input type="number" name="aps_ribbon_priority" id="aps_ribbon_priority" value="10" min="1" max="100" class="small-text" />
            <p class="description"><?php esc_html_e( 'Lower numbers appear first. Default: 10', Constants::TEXTDOMAIN ); ?></p>
        </div>

        <div class="form-field">
            <label for="aps_ribbon_status"><?php esc_html_e( 'Status', Constants::TEXTDOMAIN ); ?></label>
            <select name="aps_ribbon_status" id="aps_ribbon_status" class="postform">
                <option value="published"><?php esc_html_e( 'Published', Constants::TEXTDOMAIN ); ?></option>
                <option value="draft"><?php esc_html_e( 'Draft', Constants::TEXTDOMAIN ); ?></option>
            </select>
            <p class="description"><?php esc_html_e( 'Draft ribbons won\'t be displayed on the frontend.', Constants::TEXTDOMAIN ); ?></p>
        </div>

        <div class="form-field">
            <label for="aps_ribbon_featured"><?php esc_html_e( 'Featured', Constants::TEXTDOMAIN ); ?></label>
            <input type="checkbox" name="aps_ribbon_featured" id="aps_ribbon_featured" value="1" />
            <p class="description"><?php esc_html_e( 'Mark as featured ribbon for special highlighting.', Constants::TEXTDOMAIN ); ?></p>
        </div>

        <div class="form-field">
            <label for="aps_ribbon_is_default"><?php esc_html_e( 'Default Ribbon', Constants::TEXTDOMAIN ); ?></label>
            <input type="checkbox" name="aps_ribbon_is_default" id="aps_ribbon_is_default" value="1" />
            <p class="description"><?php esc_html_e( 'Set as default ribbon for new products. Only one ribbon can be default.', Constants::TEXTDOMAIN ); ?></p>
        </div>

        <div class="form-field">
            <label for="aps_ribbon_image_url"><?php esc_html_e( 'Image URL', Constants::TEXTDOMAIN ); ?></label>
            <input type="url" name="aps_ribbon_image_url" id="aps_ribbon_image_url" value="" class="large-text" />
            <p class="description"><?php esc_html_e( 'Optional image URL for the ribbon badge.', Constants::TEXTDOMAIN ); ?></p>
        </div>
        
        <?php wp_nonce_field( 'aps_ribbon_fields', 'aps_ribbon_fields_nonce' ); ?>
        <?php
    }

    /**
     * Render fields in edit form
     *
     * @param WP_Term $term Term object
     */
    public function render_edit_fields( \WP_Term $term ): void {
        $color = get_term_meta( $term->term_id, '_aps_ribbon_color', true ) ?: '#ff6b6b';
        $icon = get_term_meta( $term->term_id, '_aps_ribbon_icon', true ) ?: '';
        $priority = (int) get_term_meta( $term->term_id, '_aps_ribbon_priority', true ) ?: 10;
        $status = get_term_meta( $term->term_id, '_aps_ribbon_status', true ) ?: 'published';
        $featured = (bool) get_term_meta( $term->term_id, '_aps_ribbon_featured', true );
        $is_default = (bool) get_term_meta( $term->term_id, '_aps_ribbon_is_default', true );
        $image_url = get_term_meta( $term->term_id, '_aps_ribbon_image_url', true ) ?: '';

        ?>
        <tr class="form-field">
            <th scope="row">
                <label for="aps_ribbon_color"><?php esc_html_e( 'Color', Constants::TEXTDOMAIN ); ?></label>
            </th>
            <td>
                <input type="color" name="aps_ribbon_color" id="aps_ribbon_color" value="<?php echo esc_attr( $color ); ?>" class="color-picker" />
                <p class="description"><?php esc_html_e( 'Select a color for the ribbon badge.', Constants::TEXTDOMAIN ); ?></p>
            </td>
        </tr>

        <tr class="form-field">
            <th scope="row">
                <label for="aps_ribbon_icon"><?php esc_html_e( 'Icon', Constants::TEXTDOMAIN ); ?></label>
            </th>
            <td>
                <input type="text" name="aps_ribbon_icon" id="aps_ribbon_icon" value="<?php echo esc_attr( $icon ); ?>" class="regular-text" />
                <p class="description"><?php esc_html_e( 'Enter an icon class or identifier (e.g., "star", "badge").', Constants::TEXTDOMAIN ); ?></p>
            </td>
        </tr>

        <tr class="form-field">
            <th scope="row">
                <label for="aps_ribbon_priority"><?php esc_html_e( 'Priority', Constants::TEXTDOMAIN ); ?></label>
            </th>
            <td>
                <input type="number" name="aps_ribbon_priority" id="aps_ribbon_priority" value="<?php echo esc_attr( $priority ); ?>" min="1" max="100" class="small-text" />
                <p class="description"><?php esc_html_e( 'Lower numbers appear first. Default: 10', Constants::TEXTDOMAIN ); ?></p>
            </td>
        </tr>

        <tr class="form-field">
            <th scope="row">
                <label for="aps_ribbon_status"><?php esc_html_e( 'Status', Constants::TEXTDOMAIN ); ?></label>
            </th>
            <td>
                <select name="aps_ribbon_status" id="aps_ribbon_status" class="postform">
                    <option value="published" <?php selected( $status, 'published' ); ?>><?php esc_html_e( 'Published', Constants::TEXTDOMAIN ); ?></option>
                    <option value="draft" <?php selected( $status, 'draft' ); ?>><?php esc_html_e( 'Draft', Constants::TEXTDOMAIN ); ?></option>
                </select>
                <p class="description"><?php esc_html_e( 'Draft ribbons won\'t be displayed on the frontend.', Constants::TEXTDOMAIN ); ?></p>
            </td>
        </tr>

        <tr class="form-field">
            <th scope="row">
                <label for="aps_ribbon_featured"><?php esc_html_e( 'Featured', Constants::TEXTDOMAIN ); ?></label>
            </th>
            <td>
                <input type="checkbox" name="aps_ribbon_featured" id="aps_ribbon_featured" value="1" <?php checked( $featured, true ); ?> />
                <p class="description"><?php esc_html_e( 'Mark as featured ribbon for special highlighting.', Constants::TEXTDOMAIN ); ?></p>
            </td>
        </tr>

        <tr class="form-field">
            <th scope="row">
                <label for="aps_ribbon_is_default"><?php esc_html_e( 'Default Ribbon', Constants::TEXTDOMAIN ); ?></label>
            </th>
            <td>
                <input type="checkbox" name="aps_ribbon_is_default" id="aps_ribbon_is_default" value="1" <?php checked( $is_default, true ); ?> />
                <p class="description"><?php esc_html_e( 'Set as default ribbon for new products. Only one ribbon can be default.', Constants::TEXTDOMAIN ); ?></p>
            </td>
        </tr>

        <tr class="form-field">
            <th scope="row">
                <label for="aps_ribbon_image_url"><?php esc_html_e( 'Image URL', Constants::TEXTDOMAIN ); ?></label>
            </th>
            <td>
                <input type="url" name="aps_ribbon_image_url" id="aps_ribbon_image_url" value="<?php echo esc_attr( $image_url ); ?>" class="large-text" />
                <p class="description"><?php esc_html_e( 'Optional image URL for the ribbon badge.', Constants::TEXTDOMAIN ); ?></p>
            </td>
        </tr>
        
        <?php wp_nonce_field( 'aps_ribbon_fields', 'aps_ribbon_fields_nonce' ); ?>
        <?php
    }

    /**
     * Save ribbon fields
     *
     * @param int $term_id Term ID
     * @param int $tt_id Term taxonomy ID
     */
    public function save_fields( int $term_id, int $tt_id ): void {
        // Verify nonce
        if ( ! isset( $_POST['aps_ribbon_fields_nonce'] ) || 
             ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['aps_ribbon_fields_nonce'] ) ), 'aps_ribbon_fields' ) ) {
            return;
        }

        // Check user permissions
        if ( ! current_user_can( 'manage_categories' ) ) {
            return;
        }

        // TRUE HYBRID: Save to term meta with underscore prefix
        if ( isset( $_POST['aps_ribbon_color'] ) ) {
            $color = sanitize_hex_color( wp_unslash( $_POST['aps_ribbon_color'] ) );
            if ( $color ) {
                update_term_meta( $term_id, '_aps_ribbon_color', $color );
            } else {
                delete_term_meta( $term_id, '_aps_ribbon_color' );
            }
        }

        if ( isset( $_POST['aps_ribbon_icon'] ) ) {
            $icon = sanitize_text_field( wp_unslash( $_POST['aps_ribbon_icon'] ) );
            if ( $icon ) {
                update_term_meta( $term_id, '_aps_ribbon_icon', $icon );
            } else {
                delete_term_meta( $term_id, '_aps_ribbon_icon' );
            }
        }

        if ( isset( $_POST['aps_ribbon_priority'] ) ) {
            $priority = (int) wp_unslash( $_POST['aps_ribbon_priority'] );
            $priority = max( 1, min( 100, $priority ) );
            update_term_meta( $term_id, '_aps_ribbon_priority', $priority );
        }

        if ( isset( $_POST['aps_ribbon_status'] ) ) {
            $status = sanitize_text_field( wp_unslash( $_POST['aps_ribbon_status'] ) );
            update_term_meta( $term_id, '_aps_ribbon_status', $status );
        }

        $featured = isset( $_POST['aps_ribbon_featured'] ) && '1' === $_POST['aps_ribbon_featured'];
        update_term_meta( $term_id, '_aps_ribbon_featured', $featured ? '1' : '0' );

        // Exclusive behavior for default ribbon
        $is_default = isset( $_POST['aps_ribbon_is_default'] ) && '1' === $_POST['aps_ribbon_is_default'];
        if ( $is_default ) {
            // Remove default flag from all other ribbons
            $all_ribbons = get_terms( [
                'taxonomy' => Constants::TAX_RIBBON,
                'hide_empty' => false,
                'fields' => 'ids',
            ] );

            if ( ! is_wp_error( $all_ribbons ) && ! empty( $all_ribbons ) ) {
                foreach ( $all_ribbons as $other_ribbon_id ) {
                    if ( intval( $other_ribbon_id ) !== $term_id ) {
                        update_term_meta( $other_ribbon_id, '_aps_ribbon_is_default', '0' );
                    }
                }
            }
            update_term_meta( $term_id, '_aps_ribbon_is_default', '1' );
        } else {
            update_term_meta( $term_id, '_aps_ribbon_is_default', '0' );
        }

        if ( isset( $_POST['aps_ribbon_image_url'] ) ) {
            $image_url = esc_url_raw( wp_unslash( $_POST['aps_ribbon_image_url'] ) );
            if ( $image_url ) {
                update_term_meta( $term_id, '_aps_ribbon_image_url', $image_url );
            } else {
                delete_term_meta( $term_id, '_aps_ribbon_image_url' );
            }
        }

        // Update timestamp
        update_term_meta( $term_id, '_aps_ribbon_updated_at', current_time( 'mysql' ) );
    }
}