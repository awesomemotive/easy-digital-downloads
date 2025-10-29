import { sprintf, __ } from '@wordpress/i18n';
import { Disabled, PanelBody, ToggleControl, SelectControl, RangeControl } from '@wordpress/components';
import ServerSideRender from '@wordpress/server-side-render';
import { useBlockProps, InspectorControls } from '@wordpress/block-editor';
import './editor.scss';
import { queryArgs as baseQueryArgs } from '../utilities/query-args';
import { useState } from '@wordpress/element';
import { DownloadOptions } from '../utilities/downloads';

/**
 * The edit function describes the structure of your block in the context of the
 * editor. This represents what the editor will render when the block is used.
 *
 * @see https://developer.wordpress.org/block-editor/developers/block-api/block-edit-save/#edit
 *
 * @return {WPElement} Element to render.
 */
export default function Edit ( { attributes, setAttributes } ) {
	const [ currentQueryArgs, setCurrentQueryArgs ] = useState( { ...baseQueryArgs, preview: true } );

	const layoutOptions = [
		{ label: __( 'Single Column', 'easy-digital-downloads' ), value: '' },
		{ label: __( '50/50 Split', 'easy-digital-downloads' ), value: 'half' },
		{ label: __( '70/30 Split', 'easy-digital-downloads' ), value: 'two-thirds' },
		{ label: __( '80/20 Split', 'easy-digital-downloads' ), value: 'four-fifths' },
		{ label: __( 'Bottom 50/50 Split', 'easy-digital-downloads' ), value: 'half-bottom' },
		{ label: __( 'Bottom 70/30 Split', 'easy-digital-downloads' ), value: 'two-thirds-bottom' },
	];

	return (
		<div {...useBlockProps()}>
			<p className="description">{__( 'This is an example of a cart with a product in it.', 'easy-digital-downloads' )}</p>
			<InspectorControls>
				<PanelBody title={__( 'Preview', 'easy-digital-downloads' )}>
					<ToggleControl
						label={__( 'Preview as Guest', 'easy-digital-downloads' )}
						checked={!!currentQueryArgs.preview}
						onChange={( isChecked ) => setCurrentQueryArgs( { ...currentQueryArgs, preview: isChecked } )}
					/>
					<SelectControl
						icon="download"
						/* translators: %s: Download label singular */
						label={sprintf( __( 'Select a %s:', 'easy-digital-downloads' ), EDDBlocks.download_label_singular )}
						onChange={( value ) => setCurrentQueryArgs( { ...currentQueryArgs, cart_item: value } )}
						options={DownloadOptions( true )}
						help={__( 'Select a product to preview the checkout form with a specific item in the cart.', 'easy-digital-downloads' )}
					/>
				</PanelBody>
				<PanelBody title={__( 'Settings', 'easy-digital-downloads' )}>
					<SelectControl
						label={__( 'Layout', 'easy-digital-downloads' )}
						value={attributes.layout}
						onChange={( value ) => setAttributes( { layout: value } )}
						options={layoutOptions}
					/>
					<RangeControl
						label={__( 'Cart Thumbnail Size', 'easy-digital-downloads' )}
						value={attributes.thumbnail_width}
						onChange={( value ) => setAttributes( { thumbnail_width: value } )}
						min={10}
						max={100}
						step={1}
					/>
					<ToggleControl
						label={__( 'Show Discount Form', 'easy-digital-downloads' )}
						checked={attributes.show_discount_form !== false}
						onChange={( checked ) => setAttributes( { show_discount_form: checked } )}
					/>
				</PanelBody>
			</InspectorControls>
			<Disabled>
				<ServerSideRender
					block="edd/checkout"
					attributes={{ ...attributes }}
					urlQueryArgs={currentQueryArgs}
				/>
			</Disabled>
		</div>
	);
}
