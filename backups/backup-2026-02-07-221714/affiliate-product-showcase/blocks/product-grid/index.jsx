import { registerBlockType } from '@wordpress/blocks';
import { lazy, Suspense } from '@wordpress/element';
import { Spinner } from '@wordpress/components';
import metadata from './block.json';
import save from './save.jsx';

// Lazy load edit component for code splitting
const Edit = lazy(() => import('./edit.jsx'));

registerBlockType(metadata.name, {
	edit: (props) => (
		<Suspense
			fallback={
				<div style={{ display: 'flex', justifyContent: 'center', alignItems: 'center', height: '200px' }}>
					<Spinner />
				</div>
			}
		>
			<Edit {...props} />
		</Suspense>
	),
	save,
});
