import { __ } from '@wordpress/i18n';
import { Disabled, PanelBody, ToggleControl } from '@wordpress/components';
import ServerSideRender from '@wordpress/server-side-render';
import { useBlockProps, InspectorControls } from '@wordpress/block-editor';
import './editor.scss';
import { emptyPlaceholder } from '../utilities/no-orders-placeholder';
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

	const toggleAttribute = ( attributeName ) => ( newValue ) =>
		setAttributes( { [ attributeName ]: newValue } );

	return (
		<div {...useBlockProps()}>
			<InspectorControls>
				<PanelBody
					title={__( 'Settings', 'easy-digital-downloads' )}
				>
					<ToggleControl
						label={__( 'Show Payment Key', 'easy-digital-downloads' )}
						checked={!!attributes.payment_key}
						onChange={toggleAttribute( 'payment_key' )}
					/>
					<ToggleControl
						label={__( 'Show Gateway', 'easy-digital-downloads' )}
						checked={!!attributes.payment_method}
						onChange={toggleAttribute( 'payment_method' )}
					/>
				</PanelBody>
			</InspectorControls>
			<p className="description">{__( 'The editor will display a recent random order from your site.', 'easy-digital-downloads' )}</p>
			<Disabled>
				<ServerSideRender
					block="edd/confirmation"
					attributes={{ ...attributes }}
					urlQueryArgs={queryArgs}
					EmptyResponsePlaceholder={emptyPlaceholder}
				/>
			</Disabled>
		</div>
	);
}
