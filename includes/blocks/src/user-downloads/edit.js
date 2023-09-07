import { __ } from '@wordpress/i18n';
import { Disabled, PanelBody, ToggleControl, TextControl } from '@wordpress/components';
import ServerSideRender from '@wordpress/server-side-render';
import { useBlockProps, InspectorControls } from '@wordpress/block-editor';
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

	const toggleAttribute = ( attributeName ) => ( newValue ) =>
		setAttributes( { [ attributeName ]: newValue } );

	return (
		<div {...useBlockProps()}>
			<p className="description">{__( 'This is an example of a user\'s available downloads.', 'easy-digital-downloads' )}</p>
			{EDDBlocks.no_redownload && (
				<p className="warning">{__( 'Your store has disabled redownloading files, so your users will not be able to access their files from this block. You can change the "Disable Redownload" setting by visiting Downloads > Settings > Misc > File Downloads.', 'easy-digital-downloads' )}</p>
			)}
			<InspectorControls>
				<PanelBody title={__( 'User Download Settings', 'easy-digital-downloads' )}>
					{EDDBlocks.is_pro && (
						<ToggleControl
							label={__( 'Show a Search Form', 'easy-digital-downloads' )}
							checked={!!attributes.search}
							onChange={toggleAttribute( 'search' )}
						/>
					)}
					{!EDDBlocks.is_pro && (
						<ToggleControl
							label={__( 'Show a Search Form', 'easy-digital-downloads' )}
							checked=""
							disabled="true"
							help={__( 'This feature is available in EDD (Pro).', 'easy-digital-downloads' )}
						/>
					)}
					<ToggleControl
						label={__( 'Show Product Variations', 'easy-digital-downloads' )}
						checked={!!attributes.variations}
						onChange={toggleAttribute( 'variations' )}
						help={__( 'If your product variations all use the same deliverable files, you may want to disable this.', 'easy-digital-downloads' )}
					/>
					<ToggleControl
						label={__( 'Hide Products With No Files', 'easy-digital-downloads' )}
						checked={!!attributes.hide_empty}
						onChange={toggleAttribute( 'hide_empty' )}
					/>
					{!attributes.hide_empty && (
						<TextControl
							label={__( 'Text to show if there are no files', 'easy-digital-downloads' )}
							value={attributes.nofiles}
							onChange={toggleAttribute( 'nofiles' )}
						/>
					)}
				</PanelBody>
			</InspectorControls>
			<Disabled>
				<ServerSideRender
					block="edd/user-downloads"
					attributes={{ ...attributes }}
					urlQueryArgs={queryArgs}
				/>
			</Disabled>
		</div>
	);
}
