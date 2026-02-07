import { registerBlockType } from '@wordpress/blocks';
import edit from './edit.jsx';
import save from './save.jsx';
import metadata from './block.json';

registerBlockType(metadata.name, {
	edit,
	save,
});
