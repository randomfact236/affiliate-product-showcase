import { InspectorControls, useBlockProps } from '@wordpress/block-editor';
import { PanelBody, RangeControl } from '@wordpress/components';

export default function Edit( { attributes, setAttributes } ) {
	const { perPage = 6 } = attributes;
	const blockProps = useBlockProps({ className: 'aps-block aps-block--grid' });

	return (
		<>
			<InspectorControls>
				<PanelBody title="Grid Settings">
					<RangeControl
						label="Products per page"
						min={2}
						max={12}
						value={perPage}
						onChange={( value ) => setAttributes( { perPage: value } )}
					/>
				</PanelBody>
			</InspectorControls>
			<div {...blockProps}>
				<p>Product Grid block (shows {perPage} items on the frontend).</p>
			</div>
		</>
	);
}
