/**
 * ShowcaseInspector Component
 *
 * Inspector controls for the Product Showcase block.
 */

import { __ } from '@wordpress/i18n';
import { InspectorControls } from '@wordpress/block-editor';
import { PanelBody, PanelRow, RangeControl, ToggleControl, SelectControl, TextControl } from '@wordpress/components';
import { layout } from '@wordpress/icons';
import { memo } from '@wordpress/element';
import PropTypes from 'prop-types';
import { useDebounce } from '../../shared/hooks';
import { DEBOUNCE_DELAY_SHORT, DEBOUNCE_DELAY_LONG } from '../../shared/constants';

const ShowcaseInspector = memo(({ attributes, setAttributes }) => {
    const {
        layout: layoutType, // Rename to avoid conflict with icon import
        columns,
        gap,
        showPrice,
        showDescription,
        showButton,
        buttonText,
        perPage,
    } = attributes;

    const effectiveButtonText = buttonText || __('View Details', 'affiliate-product-showcase');

    const debouncedSetLayout = useDebounce(
        (value) => setAttributes({ layout: value }),
        DEBOUNCE_DELAY_SHORT, {}, [setAttributes]
    );

    const debouncedSetColumns = useDebounce(
        (value) => setAttributes({ columns: value }),
        DEBOUNCE_DELAY_SHORT, {}, [setAttributes]
    );

    const debouncedSetGap = useDebounce(
        (value) => setAttributes({ gap: value }),
        DEBOUNCE_DELAY_SHORT, {}, [setAttributes]
    );

    const debouncedSetPerPage = useDebounce(
        (value) => setAttributes({ perPage: value }),
        DEBOUNCE_DELAY_LONG, {}, [setAttributes]
    );

    return (
        <InspectorControls>
            <PanelBody
                title={__('Layout Settings', 'affiliate-product-showcase')}
                icon={layout}
                initialOpen={true}
            >
                <SelectControl
                    label={__('Layout Type', 'affiliate-product-showcase')}
                    value={layoutType}
                    options={[
                        { label: __('Grid Layout', 'affiliate-product-showcase'), value: 'grid' },
                        { label: __('List Layout', 'affiliate-product-showcase'), value: 'list' },
                    ]}
                    onChange={debouncedSetLayout}
                    help={__('Choose between grid or list layout', 'affiliate-product-showcase')}
                />

                <RangeControl
                    label={__('Products to Show', 'affiliate-product-showcase')}
                    min={1}
                    max={12}
                    value={perPage}
                    onChange={debouncedSetPerPage}
                    help={__('Number of products to display', 'affiliate-product-showcase')}
                />

                {layoutType === 'grid' && (
                    <RangeControl
                        label={__('Columns', 'affiliate-product-showcase')}
                        min={1}
                        max={6}
                        value={columns}
                        onChange={debouncedSetColumns}
                        help={__('Number of columns in grid layout', 'affiliate-product-showcase')}
                    />
                )}

                <RangeControl
                    label={__('Gap (px)', 'affiliate-product-showcase')}
                    min={0}
                    max={48}
                    value={gap}
                    onChange={debouncedSetGap}
                    help={__('Spacing between items', 'affiliate-product-showcase')}
                />
            </PanelBody>

            <PanelBody title={__('Display Options', 'affiliate-product-showcase')} initialOpen={false}>
                <ToggleControl
                    label={__('Show Price', 'affiliate-product-showcase')}
                    checked={showPrice}
                    onChange={(value) => setAttributes({ showPrice: value })}
                    help={__('Display product prices', 'affiliate-product-showcase')}
                />

                <ToggleControl
                    label={__('Show Description', 'affiliate-product-showcase')}
                    checked={showDescription}
                    onChange={(value) => setAttributes({ showDescription: value })}
                    help={__('Display product descriptions', 'affiliate-product-showcase')}
                />

                <ToggleControl
                    label={__('Show Button', 'affiliate-product-showcase')}
                    checked={showButton}
                    onChange={(value) => setAttributes({ showButton: value })}
                    help={__('Display call-to-action button', 'affiliate-product-showcase')}
                />

                {showButton && (
                    <TextControl
                        label={__('Button Text', 'affiliate-product-showcase')}
                        value={effectiveButtonText}
                        onChange={(value) => setAttributes({ buttonText: value })}
                        help={__('Custom text for CTA button', 'affiliate-product-showcase')}
                    />
                )}
            </PanelBody>

            <PanelBody title={__('Style Presets', 'affiliate-product-showcase')} initialOpen={false}>
                <div className="aps-style-presets">
                    <button
                        className="aps-preset-btn"
                        aria-label={__('Apply default grid style preset', 'affiliate-product-showcase')}
                        onClick={() => setAttributes({
                            layout: 'grid',
                            columns: 3,
                            gap: 16,
                            showPrice: true,
                            showDescription: true,
                            showButton: true,
                            buttonText: __('View Details', 'affiliate-product-showcase')
                        })}
                    >
                        {__('Default Grid', 'affiliate-product-showcase')}
                    </button>
                    <button
                        className="aps-preset-btn"
                        aria-label={__('Apply list view style preset', 'affiliate-product-showcase')}
                        onClick={() => setAttributes({
                            layout: 'list',
                            gap: 16,
                            showPrice: true,
                            showDescription: true,
                            showButton: true,
                            buttonText: __('Learn More', 'affiliate-product-showcase')
                        })}
                    >
                        {__('List View', 'affiliate-product-showcase')}
                    </button>
                    <button
                        className="aps-preset-btn"
                        aria-label={__('Apply compact style preset', 'affiliate-product-showcase')}
                        onClick={() => setAttributes({
                            layout: 'grid',
                            columns: 4,
                            gap: 12,
                            showPrice: true,
                            showDescription: false,
                            showButton: false
                        })}
                    >
                        {__('Compact', 'affiliate-product-showcase')}
                    </button>
                    <button
                        className="aps-preset-btn"
                        aria-label={__('Apply featured style preset', 'affiliate-product-showcase')}
                        onClick={() => setAttributes({
                            layout: 'grid',
                            columns: 2,
                            gap: 24,
                            showPrice: true,
                            showDescription: true,
                            showButton: true,
                            buttonText: __('Buy Now', 'affiliate-product-showcase')
                        })}
                    >
                        {__('Featured', 'affiliate-product-showcase')}
                    </button>
                </div>
            </PanelBody>

            <PanelBody title={__('Advanced', 'affiliate-product-showcase')} initialOpen={false}>
                <PanelRow>
                    <span>{__('Layout Type:', 'affiliate-product-showcase')}</span>
                    <strong>{layoutType === 'grid' ? __('Grid', 'affiliate-product-showcase') : __('List', 'affiliate-product-showcase')}</strong>
                </PanelRow>
                {layoutType === 'grid' && (
                    <PanelRow>
                        <span>{__('Grid Columns:', 'affiliate-product-showcase')}</span>
                        <strong>{columns}</strong>
                    </PanelRow>
                )}
                <PanelRow>
                    <span>{__('Gap Size:', 'affiliate-product-showcase')}</span>
                    <strong>{gap}px</strong>
                </PanelRow>
            </PanelBody>
        </InspectorControls>
    );
});

ShowcaseInspector.displayName = 'ShowcaseInspector';

ShowcaseInspector.propTypes = {
    attributes: PropTypes.object.isRequired,
    setAttributes: PropTypes.func.isRequired,
};

export default ShowcaseInspector;
