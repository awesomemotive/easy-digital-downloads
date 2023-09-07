import { registerBlockType } from '@wordpress/blocks';
import './style.scss';
import Edit from './edit';
import metadata from './block.json';
import { Icon } from '../utilities/icons';

registerBlockType( metadata.name, {

	icon: Icon( metadata.icon ),
	/**
	 * @see ./edit.js
	 */
	edit: Edit,
} );
