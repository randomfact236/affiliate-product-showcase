import { registerBlockType } from '@wordpress/blocks';
import { lazy, Suspense } from '@wordpress/element';
import { Spinner } from '@wordpress/components';
import save from './save.jsx';
import metadata from './block.json';

// Lazy load the edit component for better performance
const Edit = lazy(() => import('./edit.jsx'));

// Wrapper component to handle Suspense
const EditWithSuspense = (props) => (
	<Suspense fallback={<div className="aps-block-loading"><Spinner /></div>}>
		<Edit {...props} />
	</Suspense>
);

registerBlockType(metadata.name, {
	edit: EditWithSuspense,
	save,
});
