import { sprintf, __ } from '@wordpress/i18n';
import { Disabled, PanelBody, ToggleControl, RangeControl, SelectControl, TextControl } from '@wordpress/components';
import ServerSideRender from '@wordpress/server-side-render';
import { ImageAlignmentOptions } from '../utilities/image-alignment';
import { OrderOptions } from '../utilities/order';
import { ImageSizeOptions } from '../utilities/image-size';
import { useBlockProps, InspectorControls, PanelColorSettings, withColors, ColorPalette } from '@wordpress/block-editor';
import { useEffect } from '@wordpress/element';
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
function Edit ( { attributes, setAttributes, featuredBadgeColor, setFeaturedBadgeColor, featuredBadgeBackgroundColor, setFeaturedBadgeBackgroundColor } ) {
	if ( !EDDBlocks.has_published_downloads ) {
		return newDownload();
	}

	const toggleAttribute = ( attributeName ) => ( newValue ) =>
		setAttributes( { [ attributeName ]: newValue } );

	const categories = DownloadCategoryTerms( 'download_category', __( 'All Categories', 'easy-digital-downloads' ) );
	const tags = DownloadCategoryTerms( 'download_tag', __( 'All Categories', 'easy-digital-downloads' ) );

	// Handle featured styling in block editor.
	useEffect( () => {
		if ( !EDDBlocks.is_pro || !attributes.featured_styling_enabled ) {
			// Remove existing styles if styling is disabled.
			const existingStyle = document.getElementById( 'edd-featured-styles' );
			if ( existingStyle ) {
				existingStyle.remove();
			}
			return;
		}

		// Generate CSS styles.
		const styleVars = [];

		// Badge styling (only if badge is enabled).
		if ( attributes.featured_badge_enabled ) {
			// Badge text color.
			let badgeColor = '';
			if ( attributes.featuredBadgeColor ) {
				badgeColor = `var( --wp--preset--color--${ attributes.featuredBadgeColor } )`;
			} else if ( attributes.customFeaturedBadgeColor ) {
				badgeColor = attributes.customFeaturedBadgeColor;
			} else if ( EDDBlocks.button_colors?.text ) {
				badgeColor = EDDBlocks.button_colors.text;
			}
			if ( badgeColor ) {
				styleVars.push( `--edd-featured-badge-color: ${ badgeColor }` );
			}

			// Badge background color.
			let badgeBgColor = '';
			if ( attributes.featuredBadgeBackgroundColor ) {
				badgeBgColor = `var( --wp--preset--color--${ attributes.featuredBadgeBackgroundColor } )`;
			} else if ( attributes.customFeaturedBadgeBackgroundColor ) {
				badgeBgColor = attributes.customFeaturedBadgeBackgroundColor;
			} else if ( EDDBlocks.button_colors?.background ) {
				badgeBgColor = EDDBlocks.button_colors.background;
			}
			if ( badgeBgColor ) {
				styleVars.push( `--edd-featured-badge-bg: ${ badgeBgColor }` );
			}

			// Badge text.
			if ( attributes.featured_badge_text ) {
				styleVars.push( `--edd-featured-badge-text: "${ attributes.featured_badge_text }"` );
			}
		}

		// Border styling (only if border is enabled).
		if ( attributes.featured_border_enabled ) {
			// Border color.
			let borderColor = attributes.featured_border_color || EDDBlocks.button_colors?.background || '#007cba';
			if ( borderColor ) {
				styleVars.push( `--edd-featured-border-color: ${ borderColor }` );
			}

			// Border width.
			if ( attributes.featured_border_width ) {
				styleVars.push( `--edd-featured-border-width: ${ attributes.featured_border_width }` );
			}

			// Border style.
			if ( attributes.featured_border_style ) {
				styleVars.push( `--edd-featured-border-style: ${ attributes.featured_border_style }` );
			}

			// Border radius.
			if ( attributes.featured_border_radius ) {
				styleVars.push( `--edd-featured-border-radius: ${ attributes.featured_border_radius }` );
			}
		}

		if ( styleVars.length > 0 ) {
			const css = `.wp-block-edd-downloads { ${ styleVars.join( '; ' ) } }`;

			// Add or update the style element.
			let styleElement = document.getElementById( 'edd-featured-styles' );
			if ( !styleElement ) {
				styleElement = document.createElement( 'style' );
				styleElement.id = 'edd-featured-styles';
				document.head.appendChild( styleElement );
			}
			styleElement.textContent = css;
		}

		// Cleanup function.
		return () => {
			const styleElement = document.getElementById( 'edd-featured-styles' );
			if ( styleElement ) {
				styleElement.remove();
			}
		};
	}, [
		attributes.featured_styling_enabled,
		attributes.featured_badge_enabled,
		attributes.featured_border_enabled,
		attributes.featuredBadgeColor,
		attributes.customFeaturedBadgeColor,
		attributes.featuredBadgeBackgroundColor,
		attributes.customFeaturedBadgeBackgroundColor,
		attributes.featured_badge_text,
		attributes.featured_border_color,
		attributes.featured_border_width,
		attributes.featured_border_style,
		attributes.featured_border_radius
	] );

	return (
		<div {...useBlockProps()}>
			<InspectorControls>
				<PanelBody
					title={__( 'Product Block Settings', 'easy-digital-downloads' )}
				>
					<p className="description">{__( 'Decide how to display your products.', 'easy-digital-downloads' )}</p>
					<SelectControl
						label={__( 'Featured Downloads', 'easy-digital-downloads' )}
						value={attributes.featured}
						options={
							[
								{
									'value': '',
									'label': __( 'Show All Downloads', 'easy-digital-downloads' )
								},
								{
									'value': 'yes',
									'label': __( 'Show Only Featured Downloads', 'easy-digital-downloads' )
								},
								{
									'value': 'orderby',
									'label': __( 'Show Featured Downloads First', 'easy-digital-downloads' )
								}
							]
						}
						onChange={toggleAttribute( 'featured' )}
					/>
					{EDDBlocks.featured_promo ? (
						<div
							className="edd-promo-notice__trigger edd-promo-notice__trigger--ajax"
							data-id="featureddownloads"
							onClick={(e) => {
								e.preventDefault();
							}}
						>
							<div style={{ pointerEvents: 'none' }}>
								<ToggleControl
									label={__( 'Enable Featured Styling', 'easy-digital-downloads' )}
									checked={false}
									disabled={true}
									onChange={() => {}}
									help={__( 'Apply special styling to featured downloads to make them stand out.', 'easy-digital-downloads' )}
								/>
							</div>
						</div>
					) : (
						<ToggleControl
							label={__( 'Enable Featured Styling', 'easy-digital-downloads' )}
							checked={EDDBlocks.is_pro ? !!attributes.featured_styling_enabled : false}
							onChange={EDDBlocks.is_pro ? toggleAttribute( 'featured_styling_enabled' ) : () => { }}
							disabled={!EDDBlocks.is_pro}
							help={__( 'Apply special styling to featured downloads to make them stand out.', 'easy-digital-downloads' )}
						/>
					)}

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
				{!!attributes.featured_styling_enabled && EDDBlocks.is_pro && (
					<PanelBody
						title={__( 'Featured Downloads Styling', 'easy-digital-downloads' )}
						initialOpen={false}
					>
						<ToggleControl
							label={__( 'Show Featured Badge', 'easy-digital-downloads' )}
							checked={!!attributes.featured_badge_enabled}
							onChange={toggleAttribute( 'featured_badge_enabled' )}
							help={__( 'Display a badge on featured downloads.', 'easy-digital-downloads' )}
						/>
						{!!attributes.featured_badge_enabled && (
							<>
								<TextControl
									label={__( 'Featured Badge Text', 'easy-digital-downloads' )}
									value={attributes.featured_badge_text}
									onChange={toggleAttribute( 'featured_badge_text' )}
									help={__( 'Text to display on the featured badge.', 'easy-digital-downloads' )}
								/>
								<PanelColorSettings
									title={__( 'Featured Badge Colors', 'easy-digital-downloads' )}
									colorSettings={[
										{
											label: __( 'Badge Text Color', 'easy-digital-downloads' ),
											value: featuredBadgeColor.color || attributes.customFeaturedBadgeColor || EDDBlocks.button_colors?.text || '#ffffff',
											onChange: setFeaturedBadgeColor,
										},
										{
											label: __( 'Badge Background Color', 'easy-digital-downloads' ),
											value: featuredBadgeBackgroundColor.color || attributes.customFeaturedBadgeBackgroundColor || EDDBlocks.button_colors?.background || '#007cba',
											onChange: setFeaturedBadgeBackgroundColor,
										},
									]}
								/>
							</>
						)}
						<ToggleControl
							label={__( 'Enable Featured Border', 'easy-digital-downloads' )}
							checked={!!attributes.featured_border_enabled}
							onChange={toggleAttribute( 'featured_border_enabled' )}
							help={__( 'Add a custom border to featured downloads.', 'easy-digital-downloads' )}
						/>
						{!!attributes.featured_border_enabled && (
							<>
								<p><strong>{__( 'Border Color', 'easy-digital-downloads' )}</strong></p>
								<ColorPalette
									value={attributes.featured_border_color || EDDBlocks.button_colors?.background || '#007cba'}
									onChange={toggleAttribute( 'featured_border_color' )}
								/>
								<RangeControl
									label={__( 'Border Width (px)', 'easy-digital-downloads' )}
									value={parseInt( attributes.featured_border_width ) || 2}
									onChange={( value ) => toggleAttribute( 'featured_border_width' )( value + 'px' )}
									min={1}
									max={10}
								/>
								<SelectControl
									label={__( 'Border Style', 'easy-digital-downloads' )}
									value={attributes.featured_border_style}
									onChange={toggleAttribute( 'featured_border_style' )}
									options={[
										{ value: 'solid', label: __( 'Solid', 'easy-digital-downloads' ) },
										{ value: 'dashed', label: __( 'Dashed', 'easy-digital-downloads' ) },
										{ value: 'dotted', label: __( 'Dotted', 'easy-digital-downloads' ) },
										{ value: 'double', label: __( 'Double', 'easy-digital-downloads' ) },
									]}
								/>
								<RangeControl
									label={__( 'Border Radius (px)', 'easy-digital-downloads' )}
									value={parseInt( attributes.featured_border_radius ) || 3}
									onChange={( value ) => toggleAttribute( 'featured_border_radius' )( value + 'px' )}
									min={0}
									max={10}
									help={__( 'Rounded corners for the border.', 'easy-digital-downloads' )}
								/>

							</>
						)}
					</PanelBody>
				)}
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

export default withColors( 'featuredBadgeColor', 'featuredBadgeBackgroundColor' )( Edit );
