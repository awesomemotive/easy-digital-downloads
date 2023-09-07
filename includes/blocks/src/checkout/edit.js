import { __ } from '@wordpress/i18n';
import { Disabled } from '@wordpress/components';
import ServerSideRender from '@wordpress/server-side-render';
import { useBlockProps } from '@wordpress/block-editor';
import './editor.scss';
import { queryArgs } from '../utilities/query-args';

/**
 * The edit function describes the structure of your block in the context of the
 * editor. This represents what the editor will render when the block is used.
 *
 * @see https://developer.wordpress.org/block-editor/developers/block-api/block-edit-save/#edit
 *
 * @return {WPElement} Element to render.
 */
export default function Edit ( { attributes, setAttributes } ) {

	return (
		<div {...useBlockProps()}>
			<p className="description">{__( 'This is an example of a cart with a product in it.', 'easy-digital-downloads' )}</p>
			<Disabled>
				<ServerSideRender
					block="edd/checkout"
					attributes={{ ...attributes }}
					urlQueryArgs={queryArgs}
				/>
			</Disabled>
		</div>
	);
}
