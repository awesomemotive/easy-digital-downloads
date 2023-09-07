import { sprintf, __ } from '@wordpress/i18n';
import { Disabled, PanelBody, ToggleControl, RangeControl, SelectControl } from '@wordpress/components';
import ServerSideRender from '@wordpress/server-side-render';
import { ImageAlignmentOptions } from '../utilities/image-alignment';
import { OrderOptions } from '../utilities/order';
import { ImageSizeOptions } from '../utilities/image-size';
import { useBlockProps, InspectorControls } from '@wordpress/block-editor';
import './editor.scss';
import { ButtonAlignmentOptions } from '../utilities/buy-button-alignment';
import { DownloadOrderBy } from '../utilities/download-order-by';
import { DownloadCategoryTerms } from '../utilities/download-terms';
import { DownloadContent } from '../utilities/download-content';
import { DownloadImageLocations } from '../utilities/download-image-location';
import { queryArgs } from '../utilities/query-args';
import { newDownload } from '../utilities/download-new';
import { Users } from '../utilities/users';

/**
 * The edit function describes the structure of your block in the context of the
 * editor. This represents what the editor will render when the block is used.
 *
 * @see https://developer.wordpress.org/block-editor/developers/block-api/block-edit-save/#edit
 *
 * @return {WPElement} Element to render.
 */
export default function Edit ( { attributes, setAttributes } ) {
	if ( !EDDBlocks.has_published_downloads ) {
		return newDownload();
	}

	const toggleAttribute = ( attributeName ) => ( newValue ) =>
		setAttributes( { [ attributeName ]: newValue } );

	const categories = DownloadCategoryTerms( 'download_category', __( 'All Categories', 'easy-digital-downloads' ) );
	const tags = DownloadCategoryTerms( 'download_tag', __( 'All Categories', 'easy-digital-downloads' ) );

	return (
		<div {...useBlockProps()}>
			<InspectorControls>
				<PanelBody
					title={__( 'Product Block Settings', 'easy-digital-downloads' )}
				>
					<p className="description">{__( 'Decide how to display your products.', 'easy-digital-downloads' )}</p>
					<RangeControl
						label={__( 'Downloads per Page', 'easy-digital-downloads' )}
						value={attributes.number}
						onChange={toggleAttribute( 'number' )}
						min={1}
						max={100}
					/>
					{!!EDDBlocks.all_access && (
						<ToggleControl
							label={__( 'Show All Access Downloads', 'easy-digital-downloads' )}
							checked={!!attributes.all_access}
							onChange={toggleAttribute( 'all_access' )}
						/>
					)}
					<RangeControl
						label={__( 'Number of Columns', 'easy-digital-downloads' )}
						value={attributes.columns}
						onChange={toggleAttribute( 'columns' )}
						min={1}
						max={6}
					/>
					<SelectControl
						label={__( 'Order By', 'easy-digital-downloads' )}
						value={attributes.orderby}
						options={DownloadOrderBy}
						onChange={toggleAttribute( 'orderby' )}
					/>
					<SelectControl
						label={__( 'Order', 'easy-digital-downloads' )}
						value={attributes.order}
						options={OrderOptions}
						onChange={toggleAttribute( 'order' )}
					/>
					{'rand' !== attributes.orderby && (
						<ToggleControl
							label={__( 'Show Pagination', 'easy-digital-downloads' )}
							checked={!!attributes.pagination}
							onChange={toggleAttribute( 'pagination' )}
						/>
					)}
					<SelectControl
						label={__( 'Author', 'easy-digital-downloads' )}
						options={Users( true )}
						onChange={toggleAttribute( 'author' )}
					/>
				</PanelBody>
				{!attributes.all_access && (
					<PanelBody
						title={__( 'Download Term Settings', 'easy-digital-downloads' )}
						initialOpen={false}
					>
						<SelectControl
							multiple
							className="edd-blocks-term-selector"
							label={__( 'Show Downloads From Categories', 'easy-digital-downloads' )}
							value={attributes.category}
							options={categories}
							onChange={toggleAttribute( 'category' )}
						/>
						<SelectControl
							multiple
							className="edd-blocks-term-selector"
							label={__( 'Show Downloads From Tags', 'easy-digital-downloads' )}
							value={attributes.tag}
							options={tags}
							onChange={toggleAttribute( 'tag' )}
						/>
					</PanelBody>
				)}
				<PanelBody
					title={__( 'Individual Product Settings', 'easy-digital-downloads' )}
					initialOpen={false}
				>
					<ToggleControl
						label={__( 'Show Title', 'easy-digital-downloads' )}
						checked={!!attributes.title}
						onChange={toggleAttribute( 'title' )}
					/>
					<SelectControl
						label={__( 'Featured Image Location', 'easy-digital-downloads' )}
						value={attributes.image_location}
						options={DownloadImageLocations}
						onChange={toggleAttribute( 'image_location' )}
					/>
					{!!attributes.image_location && (
						<ToggleControl
							label={__( 'Should the featured image link to the product?', 'easy-digital-downloads' )}
							checked={!!attributes.image_link}
							onChange={toggleAttribute( 'image_link' )}
						/>
					)}
					{!!attributes.image_location && (
						<SelectControl
							label={__( 'Featured Image Size', 'easy-digital-downloads' )}
							value={attributes.image_size}
							options={ImageSizeOptions}
							onChange={toggleAttribute( 'image_size' )}
						/>
					)}
					{!!attributes.image_location && (
						<SelectControl
							label={__( 'Featured Image Alignment', 'easy-digital-downloads' )}
							value={attributes.image_alignment}
							options={ImageAlignmentOptions}
							onChange={toggleAttribute( 'image_alignment' )}
						/>
					)}
					<SelectControl
						label={__( 'Content', 'easy-digital-downloads' )}
						value={attributes.content}
						options={DownloadContent}
						onChange={toggleAttribute( 'content' )}
					/>
					<ToggleControl
						label={__( 'Show Price', 'easy-digital-downloads' )}
						checked={!!attributes.price}
						onChange={toggleAttribute( 'price' )}
					/>
					<ToggleControl
						label={__( 'Show Purchase Button', 'easy-digital-downloads' )}
						checked={!!attributes.purchase_link}
						onChange={toggleAttribute( 'purchase_link' )}
					/>
					{!!attributes.purchase_link && (
						<>
							<SelectControl
								label={__( 'Purchase Button Alignment', 'easy-digital-downloads' )}
								value={attributes.purchase_link_align}
								options={ButtonAlignmentOptions}
								onChange={toggleAttribute( 'purchase_link_align' )}
							/>
							<ToggleControl
								label={__( 'Show Price on Button', 'easy-digital-downloads' )}
								checked={!!attributes.show_price}
								onChange={toggleAttribute( 'show_price' )}
							/>
						</>
					)}
				</PanelBody>
			</InspectorControls>
			<Disabled>
				<ServerSideRender
					block="edd/downloads"
					attributes={{ ...attributes }}
					urlQueryArgs={queryArgs}
				/>
			</Disabled>
		</div>
	);
}
