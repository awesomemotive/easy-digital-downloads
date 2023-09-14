
import { __ } from '@wordpress/i18n';
import { PanelBody, ToggleControl, TextControl } from '@wordpress/components';
import ServerSideRender from '@wordpress/server-side-render';
import { useBlockProps, InspectorControls } from '@wordpress/block-editor';
import './editor.scss';
import { queryArgs } from '../utilities/query-args';
import { Disabled } from '@wordpress/components';

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
						label={__( 'Mini Cart', 'easy-digital-downloads' )}
						checked={!!attributes.mini}
						onChange={toggleAttribute( 'mini' )}
					/>
					{!attributes.mini && (
						<TextControl
							label={__( 'Title', 'easy-digital-downloads' )}
							value={attributes.title}
							onChange={toggleAttribute( 'title' )}
						/>
					)}
					<ToggleControl
						label={__( 'Hide When Empty', 'easy-digital-downloads' )}
						checked={!!attributes.hide_empty}
						onChange={toggleAttribute( 'hide_empty' )}
					/>
					<ToggleControl
						label={__( 'Hide on Checkout', 'easy-digital-downloads' )}
						checked={!!attributes.hide_on_checkout}
						onChange={toggleAttribute( 'hide_on_checkout' )}
					/>
					{attributes.mini && (
						<>
							<ToggleControl
								label={__( 'Link Cart to Checkout', 'easy-digital-downloads' )}
								checked={!!attributes.link}
								onChange={toggleAttribute( 'link' )}
							/>
							<ToggleControl
								label={__( 'Show Number of Items in Cart', 'easy-digital-downloads' )}
								checked={!!attributes.show_quantity}
								onChange={toggleAttribute( 'show_quantity' )}
							/>
							<ToggleControl
								label={__( 'Show Cart Total', 'easy-digital-downloads' )}
								checked={!!attributes.show_total}
								onChange={toggleAttribute( 'show_total' )}
							/>
						</>
					)}
				</PanelBody>
			</InspectorControls>
			<Disabled>
				<ServerSideRender
					block="edd/cart"
					attributes={{ ...attributes }}
					urlQueryArgs={queryArgs}
				/>
			</Disabled>
		</div>
	);
}
