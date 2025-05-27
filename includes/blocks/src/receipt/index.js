import { registerBlockType } from '@wordpress/blocks';
import './style.scss';
import Edit from './edit';
import metadata from './block.json';
import { Icon } from '../utilities/icons';
import Save from './save';

const legacySave = () => {
	return null;
};

registerBlockType( metadata.name, {

	icon: Icon( metadata.icon ),
	/**
	 * @see ./edit.js
	 */
	edit: Edit,
	save: Save,

	deprecated: [
		{
			// Use the attributes directly from the imported metadata since they haven't changed.
			attributes: metadata.attributes,
			save: legacySave, // The OLD save function
		},
	],
} );
