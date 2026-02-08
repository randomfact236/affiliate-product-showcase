/**
 * Product Showcase Block - Edit Component
 *
 * Main entry point for the editor side of the block.
 * Uses a component-based architecture.
 *
 * @package AffiliateProductShowcase
 * @since 2.0.0
 */

import ShowcaseInspector from './edit/ShowcaseInspector';
import ShowcasePreview from './edit/ShowcasePreview';
import PropTypes from 'prop-types';

export default function Edit({ attributes, setAttributes, clientId }) {
	return (
		<>
			<ShowcaseInspector
				attributes={attributes}
				setAttributes={setAttributes}
			/>
			<ShowcasePreview
				attributes={attributes}
				clientId={clientId}
			/>
		</>
	);
}

Edit.propTypes = {
	attributes: PropTypes.object.isRequired,
	setAttributes: PropTypes.func.isRequired,
	clientId: PropTypes.string,
};
