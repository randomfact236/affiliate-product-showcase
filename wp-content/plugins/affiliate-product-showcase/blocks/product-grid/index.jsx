import { registerBlockType } from '@wordpress/blocks';
import { lazy, Suspense } from '@wordpress/element';
import { Spinner } from '@wordpress/components';
import metadata from './block.json';
import save from './save.jsx';

// Lazy load edit component for code splitting
const Edit = lazy(() => import('./edit.jsx'));

// Wrapper component to handle Suspense
const EditWithSuspense = (props) => (
	<Suspense
		fallback={
			<div className="aps-block-loading-fallback">
				<Spinner />
			</div>
		}
	>
		<Edit {...props} />
	</Suspense>
);

EditWithSuspense.displayName = 'EditWithSuspense';

registerBlockType(metadata.name, {
	edit: EditWithSuspense,
	save,
});
