/**
 * Product Grid Block - Edit Component
 * 
 * Main entry point for the editor side of the block.
 * Uses a component-based architecture for better maintainability.
 * 
 * @package AffiliateProductShowcase
 * @since 2.0.0
 */

import GridInspector from './edit/GridInspector';
import GridPreview from './edit/GridPreview';
import PropTypes from 'prop-types';

export default function Edit({ attributes, setAttributes, clientId }) {
	return (
		<>
			<GridInspector
				attributes={attributes}
				setAttributes={setAttributes}
			/>
			<GridPreview
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
