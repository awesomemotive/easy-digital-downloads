import { __ } from '@wordpress/i18n';
import { Disabled, PanelBody, ToggleControl, SelectControl, RangeControl } from '@wordpress/components';
import ServerSideRender from '@wordpress/server-side-render';
import { useBlockProps, InspectorControls } from '@wordpress/block-editor';
import './editor.scss';
import { ImageAlignmentOptions } from '../utilities/image-alignment';
import { OrderOptions } from '../utilities/order';
import { ImageSizeOptions } from '../utilities/image-size';
import { DownloadTaxonomies } from '../utilities/download-taxonomies';

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

	const orderbyOptions = [
		{
			'value': 'count',
			'label': __( 'Count', 'easy-digital-downloads' )
		},
		{
			'value': 'id',
			'label': __( 'ID', 'easy-digital-downloads' )
		},
		{
			'value': 'name',
			'label': __( 'Name', 'easy-digital-downloads' )
		},
		{
			'value': 'slug',
			'label': __( 'Slug', 'easy-digital-downloads' )
		},
	];

	return (
		<div {...useBlockProps()}>
			<InspectorControls>
				<PanelBody
					title={__( 'Term Block Settings', 'easy-digital-downloads' )}
				>
					<SelectControl
						label={__( 'Select Taxonomy', 'easy-digital-downloads' )}
						value={attributes.taxonomy}
						options={DownloadTaxonomies}
						onChange={toggleAttribute( 'taxonomy' )}
					/>
					<SelectControl
						label={__( 'Order By', 'easy-digital-downloads' )}
						value={attributes.orderby}
						options={orderbyOptions}
						onChange={toggleAttribute( 'orderby' )}
					/>
					<SelectControl
						label={__( 'Order', 'easy-digital-downloads' )}
						value={attributes.order}
						options={OrderOptions}
						onChange={toggleAttribute( 'order' )}
					/>
					<RangeControl
						label={__( 'Number of Columns', 'easy-digital-downloads' )}
						value={attributes.columns}
						onChange={toggleAttribute( 'columns' )}
						min={1}
						max={6}
					/>
					<ToggleControl
						label={__( 'Show Empty Categories', 'easy-digital-downloads' )}
						checked={!!attributes.show_empty}
						onChange={toggleAttribute( 'show_empty' )}
					/>
				</PanelBody>
				<PanelBody
					title={__( 'Individual Term Settings', 'easy-digital-downloads' )}
					initialOpen={false}
				>
					<ToggleControl
						label={__( 'Show Title', 'easy-digital-downloads' )}
						checked={!!attributes.title}
						onChange={toggleAttribute( 'title' )}
					/>
					<ToggleControl
						label={__( 'Show Thumbnails', 'easy-digital-downloads' )}
						checked={!!attributes.thumbnails}
						onChange={toggleAttribute( 'thumbnails' )}
					/>
					{!!attributes.thumbnails && (
						<SelectControl
							label={__( 'Image Size', 'easy-digital-downloads' )}
							value={attributes.image_size}
							options={ImageSizeOptions}
							onChange={toggleAttribute( 'image_size' )}
						/>
					)}
					{!!attributes.thumbnails && (
						<SelectControl
							label={__( 'Image Alignment', 'easy-digital-downloads' )}
							value={attributes.image_alignment}
							options={ImageAlignmentOptions}
							onChange={toggleAttribute( 'image_alignment' )}
						/>
					)}
					<ToggleControl
						label={__( 'Show Description', 'easy-digital-downloads' )}
						checked={!!attributes.description}
						onChange={toggleAttribute( 'description' )}
					/>
					<ToggleControl
						label={__( 'Show Count', 'easy-digital-downloads' )}
						checked={!!attributes.count}
						onChange={toggleAttribute( 'count' )}
					/>
				</PanelBody>
			</InspectorControls>
			<Disabled>
				<ServerSideRender
					block="edd/terms"
					attributes={{ ...attributes }}
				/>
			</Disabled>
		</div>
	);
}
