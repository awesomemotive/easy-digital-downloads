import { BlockControls, BlockAlignmentToolbar } from '@wordpress/block-editor';

export const BlockAlignment = () => (
	<BlockControls>
		<BlockAlignmentToolbar value={attributes.align} onChange={toggleAttribute.align} />
	</BlockControls>
);
