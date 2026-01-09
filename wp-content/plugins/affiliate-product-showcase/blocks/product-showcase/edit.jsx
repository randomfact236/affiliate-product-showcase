import { useBlockProps } from '@wordpress/block-editor';

export default function Edit() {
	const blockProps = useBlockProps({ className: 'aps-block aps-block--showcase' });
	return (
		<div {...blockProps}>
			<p>Product Showcase block renders selected products on the frontend.</p>
		</div>
	);
}
