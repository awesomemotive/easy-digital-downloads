import { registerBlockType } from '@wordpress/blocks';
import './style.scss';
import Edit from './edit';
import metadata from './block.json';
import { Icon } from '../utilities/icons';
import Save from './save';

// Define the save function for the deprecated version.
// Assuming the original block did not save any static content.
const legacySave = () => {
	return null;
};

registerBlockType( metadata.name, {

	icon: Icon( metadata.icon ),

	/**
	 * @see ./edit.js
	 */
	edit: Edit,
	/**
	 * @see ./save.js
	 */
	save: Save, // The NEW save function (from save.js) which includes InnerBlocks.Content

	// Add deprecation handling for the older version of the block.
	deprecated: [
		{
			// Use the attributes directly from the imported metadata since they haven't changed.
			attributes: metadata.attributes,
			save: legacySave, // The OLD save function
		},
	],
} );
