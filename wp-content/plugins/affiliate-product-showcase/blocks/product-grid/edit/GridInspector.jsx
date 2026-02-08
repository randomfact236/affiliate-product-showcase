import { __ } from '@wordpress/i18n';
import { InspectorControls } from '@wordpress/block-editor';
import { PanelBody, PanelRow, RangeControl, ToggleControl, SelectControl } from '@wordpress/components';
import { grid } from '@wordpress/icons';
import { memo } from '@wordpress/element';
import PropTypes from 'prop-types';
import { useDebounce } from '../../shared/hooks';
import { DEBOUNCE_DELAY_SHORT, DEBOUNCE_DELAY_LONG } from '../../shared/constants';

const GridInspector = memo(({ attributes, setAttributes }) => {
    const {
        perPage,
        columns,
        gap,
        showPrice,
        showRating,
        showBadge,
        hoverEffect,
    } = attributes;

    const debouncedSetPerPage = useDebounce(
        (value) => setAttributes({ perPage: value }),
        DEBOUNCE_DELAY_LONG,
        {},
        [setAttributes]
    );

    const debouncedSetColumns = useDebounce(
        (value) => setAttributes({ columns: value }),
        DEBOUNCE_DELAY_SHORT,
        {},
        [setAttributes]
    );

    const debouncedSetGap = useDebounce(
        (value) => setAttributes({ gap: value }),
        DEBOUNCE_DELAY_SHORT,
        {},
        [setAttributes]
    );

    return (
        <InspectorControls>
            <PanelBody
                title={__('Grid Settings', 'affiliate-product-showcase')}
                icon={grid}
                initialOpen={true}
            >
                <RangeControl
                    label={__('Products per page', 'affiliate-product-showcase')}
                    min={2}
                    max={12}
                    value={perPage}
                    onChange={debouncedSetPerPage}
                    help={__('Number of products to display', 'affiliate-product-showcase')}
                />

                <RangeControl
                    label={__('Columns', 'affiliate-product-showcase')}
                    min={1}
                    max={6}
                    value={columns}
                    onChange={debouncedSetColumns}
                    help={__('Number of columns in grid layout', 'affiliate-product-showcase')}
                />

                <RangeControl
                    label={__('Gap (px)', 'affiliate-product-showcase')}
                    min={0}
                    max={48}
                    value={gap}
                    onChange={debouncedSetGap}
                    help={__('Spacing between grid items', 'affiliate-product-showcase')}
                />
            </PanelBody>

            <PanelBody title={__('Display Options', 'affiliate-product-showcase')} initialOpen={false}>
                <ToggleControl
                    label={__('Show Price', 'affiliate-product-showcase')}
                    checked={showPrice}
                    onChange={(value) => setAttributes({ showPrice: value })}
                    help={__('Display product prices in grid', 'affiliate-product-showcase')}
                />

                <ToggleControl
                    label={__('Show Rating', 'affiliate-product-showcase')}
                    checked={showRating}
                    onChange={(value) => setAttributes({ showRating: value })}
                    help={__('Display star ratings', 'affiliate-product-showcase')}
                />

                <ToggleControl
                    label={__('Show Badge', 'affiliate-product-showcase')}
                    checked={showBadge}
                    onChange={(value) => setAttributes({ showBadge: value })}
                    help={__('Display product badges (e.g., Sale, New)', 'affiliate-product-showcase')}
                />
            </PanelBody>

            <PanelBody title={__('Hover Effect', 'affiliate-product-showcase')} initialOpen={false}>
                <SelectControl
                    label={__('Hover Effect Type', 'affiliate-product-showcase')}
                    value={hoverEffect}
                    options={[
                        { label: __('None', 'affiliate-product-showcase'), value: 'none' },
                        { label: __('Lift Up', 'affiliate-product-showcase'), value: 'lift' },
                        { label: __('Scale', 'affiliate-product-showcase'), value: 'scale' },
                        { label: __('Shadow', 'affiliate-product-showcase'), value: 'shadow' },
                    ]}
                    onChange={(value) => setAttributes({ hoverEffect: value })}
                    help={__('Animation effect when hovering over product cards', 'affiliate-product-showcase')}
                />
            </PanelBody>

            <PanelBody title={__('Style Presets', 'affiliate-product-showcase')} initialOpen={false}>
                <div className="aps-style-presets">
                    <button
                        className="aps-preset-btn"
                        aria-label={__('Apply default style preset', 'affiliate-product-showcase')}
                        onClick={() => setAttributes({
                            columns: 3,
                            gap: 16,
                            showPrice: true,
                            showRating: true,
                            showBadge: true,
                            hoverEffect: 'lift'
                        })}
                    >
                        {__('Default', 'affiliate-product-showcase')}
                    </button>
                    <button
                        className="aps-preset-btn"
                        aria-label={__('Apply compact style preset', 'affiliate-product-showcase')}
                        onClick={() => setAttributes({
                            columns: 4,
                            gap: 12,
                            showPrice: true,
                            showRating: false,
                            showBadge: false,
                            hoverEffect: 'lift'
                        })}
                    >
                        {__('Compact', 'affiliate-product-showcase')}
                    </button>
                    <button
                        className="aps-preset-btn"
                        aria-label={__('Apply featured style preset', 'affiliate-product-showcase')}
                        onClick={() => setAttributes({
                            columns: 2,
                            gap: 24,
                            showPrice: true,
                            showRating: true,
                            showBadge: true,
                            hoverEffect: 'shadow'
                        })}
                    >
                        {__('Featured', 'affiliate-product-showcase')}
                    </button>
                    <button
                        className="aps-preset-btn"
                        aria-label={__('Apply minimal style preset', 'affiliate-product-showcase')}
                        onClick={() => setAttributes({
                            columns: 6,
                            gap: 8,
                            showPrice: false,
                            showRating: false,
                            showBadge: false,
                            hoverEffect: 'none'
                        })}
                    >
                        {__('Minimal', 'affiliate-product-showcase')}
                    </button>
                </div>
            </PanelBody>

            <PanelBody title={__('Advanced', 'affiliate-product-showcase')} initialOpen={false}>
                <PanelRow>
                    <span>{__('Total Products:', 'affiliate-product-showcase')}</span>
                    <strong>{perPage}</strong>
                </PanelRow>
                <PanelRow>
                    <span>{__('Grid Columns:', 'affiliate-product-showcase')}</span>
                    <strong>{columns}</strong>
                </PanelRow>
                <PanelRow>
                    <span>{__('Gap Size:', 'affiliate-product-showcase')}</span>
                    <strong>{gap}px</strong>
                </PanelRow>
            </PanelBody>
        </InspectorControls>
    );
});

GridInspector.displayName = 'GridInspector';

GridInspector.propTypes = {
    attributes: PropTypes.object.isRequired,
    setAttributes: PropTypes.func.isRequired,
};

export default GridInspector;
