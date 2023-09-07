import { __ } from '@wordpress/i18n';
import { Disabled, PanelBody, RangeControl, ToggleControl } from '@wordpress/components';
import ServerSideRender from '@wordpress/server-side-render';
import { useBlockProps, InspectorControls } from '@wordpress/block-editor';
import './editor.scss';

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
			<p className="description">{__( 'This is an example of a user\'s order history.', 'easy-digital-downloads' )}</p>
			<InspectorControls>
				<PanelBody title={__( 'Order History Settings', 'easy-digital-downloads' )}>
					<RangeControl
						label={__( 'Columns', 'easy-digital-downloads' )}
						value={attributes.columns}
						onChange={toggleAttribute( 'columns' )}
						min={1}
						max={6}
					/>
					<RangeControl
						label={__( 'Orders per Page', 'easy-digital-downloads' )}
						value={attributes.number}
						onChange={toggleAttribute( 'number' )}
						min={1}
						max={100}
					/>
					{!!EDDBlocks.recurring && (
						<ToggleControl
							label={__( 'Do Not Show Renewal Orders', 'easy-digital-downloads' )}
							checked={!!attributes.recurring}
							onChange={toggleAttribute( 'recurring' )}
						/>
					)}
				</PanelBody>
			</InspectorControls>
			<Disabled>
				<ServerSideRender
					block="edd/order-history"
					attributes={{ ...attributes }}
				/>
			</Disabled>
		</div>
	);
}
