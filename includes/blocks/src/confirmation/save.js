import { useBlockProps, InnerBlocks } from '@wordpress/block-editor';

export default function Save ( { attributes } ) {
	const blockProps = useBlockProps.save();

	return (
		<div {...blockProps}>
			<InnerBlocks.Content />
		</div>
	);
}
