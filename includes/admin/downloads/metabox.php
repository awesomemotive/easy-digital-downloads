<?php
/**
 * Metabox Functions
 *
 * @package     EDD
 * @subpackage  Admin/Downloads
 * @copyright   Copyright (c) 2018, Easy Digital Downloads, LLC
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       1.0
 */

// Exit if accessed directly
defined( 'ABSPATH' ) || exit;

/** All Downloads *************************************************************/

/**
 * Register all the meta boxes for the Download custom post type
 *
 * @since 1.0
 * @return void
 */
function edd_add_download_meta_box() {
	$reviews_location = edd_reviews_location();
	$is_promo_active  = edd_is_promo_active();
	$post_types       = apply_filters( 'edd_download_metabox_post_types', array( 'download' ) );

	foreach ( $post_types as $post_type ) {

		/** Product Prices **/
		add_meta_box( 'edd_product_prices', sprintf( __( '%1$s Prices', 'easy-digital-downloads' ), edd_get_label_singular(), edd_get_label_plural() ),  'edd_render_download_meta_box', $post_type, 'normal', 'high' );

		/** Product Files (and bundled products) **/
		add_meta_box( 'edd_product_files', sprintf( __( '%1$s Files', 'easy-digital-downloads' ), edd_get_label_singular(), edd_get_label_plural() ),  'edd_render_files_meta_box', $post_type, 'normal', 'high' );

		/** Product Settings **/
		add_meta_box( 'edd_product_settings', sprintf( __( '%1$s Settings', 'easy-digital-downloads' ), edd_get_label_singular(), edd_get_label_plural() ),  'edd_render_settings_meta_box', $post_type, 'side', 'default' );

		/** Product Notes */
		add_meta_box( 'edd_product_notes', sprintf( __( '%1$s Instructions', 'easy-digital-downloads' ), edd_get_label_singular(), edd_get_label_plural() ), 'edd_render_product_notes_meta_box', $post_type, 'normal', 'high' );

		if ( current_user_can( 'view_product_stats', get_the_ID() ) ) {
			/** Product Stats */
			add_meta_box( 'edd_product_stats', sprintf( __( '%1$s Stats', 'easy-digital-downloads' ), edd_get_label_singular(), edd_get_label_plural() ), 'edd_render_stats_meta_box', $post_type, 'side', 'high' );
		}

		if ( ! class_exists( 'EDD_Reviews' ) ) {
			add_meta_box( 'edd-reviews-status', __( 'Product Reviews', 'easy-digital-downloads' ), 'edd_render_review_status_metabox', 'download', 'side', 'low' );
		}

		// If a promotion is active and Product Reviews is either activated or installed but not activated, show promo.
		if ( true === $is_promo_active ) {
			if ( class_exists( 'EDD_Reviews' ) || ( ! class_exists( 'EDD_Reviews' ) && ! empty( $reviews_location ) ) ) {
				add_meta_box( 'edd-promo', __( 'Black Friday & Cyber Monday sale!', 'easy-digital-downloads' ), 'edd_render_promo_metabox', 'download', 'side', 'low' );
			}
		}
	}
}
add_action( 'add_meta_boxes', 'edd_add_download_meta_box' );

/**
 * Returns default EDD Download meta fields.
 *
 * @since 1.9.5
 * @return array $fields Array of fields.
 */
function edd_download_metabox_fields() {

	$fields = array(
		'_edd_product_type',
		'edd_price',
		'_variable_pricing',
		'_edd_price_options_mode',
		'edd_variable_prices',
		'edd_download_files',
		'_edd_purchase_text',
		'_edd_purchase_style',
		'_edd_purchase_color',
		'_edd_bundled_products',
		'_edd_hide_purchase_link',
		'_edd_download_tax_exclusive',
		'_edd_button_behavior',
		'_edd_quantities_disabled',
		'edd_product_notes',
		'_edd_default_price_id',
		'_edd_bundled_products_conditions'
	);

	if ( current_user_can( 'manage_shop_settings' ) ) {
		$fields[] = '_edd_download_limit';
		$fields[] = '_edd_refundability';
		$fields[] = '_edd_refund_window';
	}

	if ( edd_use_skus() ) {
		$fields[] = 'edd_sku';
	}

	return apply_filters( 'edd_metabox_fields_save', $fields );
}

/**
 * Save post meta when the save_post action is called
 *
 * @since 1.0
 * @param int $post_id Download (Post) ID
 * @global array $post All the data of the the current post
 * @return void
 */
function edd_download_meta_box_save( $post_id, $post ) {
	if ( ! isset( $_POST['edd_download_meta_box_nonce'] ) || ! wp_verify_nonce( $_POST['edd_download_meta_box_nonce'], basename( __FILE__ ) ) ) {
		return;
	}

	if ( edd_doing_autosave() || edd_doing_ajax() || isset( $_REQUEST['bulk_edit'] ) ) {
		return;
	}

	if ( isset( $post->post_type ) && 'revision' == $post->post_type ) {
		return;
	}

	if ( ! current_user_can( 'edit_product', $post_id ) ) {
		return;
	}

	// The default fields that get saved
	$fields = edd_download_metabox_fields();
	foreach ( $fields as $field ) {
		if ( '_edd_default_price_id' == $field && edd_has_variable_prices( $post_id ) ) {

			if ( isset( $_POST[ $field ] ) ) {
				$new_default_price_id = ( ! empty( $_POST[ $field ] ) && is_numeric( $_POST[ $field ] ) ) || ( 0 === (int) $_POST[ $field ] ) ? (int) $_POST[ $field ] : 1;
			} else {
				$new_default_price_id = 1;
			}

			update_post_meta( $post_id, $field, $new_default_price_id );
		} elseif ( '_edd_product_type' === $field && '0' === $_POST[ $field ] ) {
			// No value stored when product type is "default" ("0") for backwards compatibility.
			delete_post_meta( $post_id, '_edd_product_type' );
		} else {
			if ( isset( $_POST[ $field ] ) ) {
				$new = apply_filters( 'edd_metabox_save_' . $field, $_POST[ $field ] );
				update_post_meta( $post_id, $field, $new );
			} else {
				delete_post_meta( $post_id, $field );
			}
		}
	}

	if ( edd_has_variable_prices( $post_id ) ) {
		$lowest = edd_get_lowest_price_option( $post_id );
		update_post_meta( $post_id, 'edd_price', $lowest );
	}

	do_action( 'edd_save_download', $post_id, $post );
}

add_action( 'save_post', 'edd_download_meta_box_save', 10, 2 );

/**
 * Sanitize bundled products on save
 *
 * Ensures a user doesn't try and include a product's ID in the products bundled with that product
 *
 * @since       1.6
 *
 * @param array $products
 * @return array
 */
function edd_sanitize_bundled_products_save( $products = array() ) {

	$products = array_map( function( $value ) {
		return preg_replace( '/[^0-9_]/', '', $value );
	}, (array) $products );

	foreach ( $products as $key => $value ) {
		$underscore_pos = strpos( $value, '_' );
		if ( is_numeric( $underscore_pos ) ) {
			$product_id = substr( $value, 0, $underscore_pos );
		} else {
			$product_id = $value;
		}

		if ( $product_id === get_the_ID() ) {
			unset( $products[ $key ] );
		}
	}

	return array_values( array_unique( $products ) );
}
add_filter( 'edd_metabox_save__edd_bundled_products', 'edd_sanitize_bundled_products_save' );

/**
 * Don't save blank rows.
 *
 * When saving, check the price and file table for blank rows.
 * If the name of the price or file is empty, that row should not
 * be saved.
 *
 * @since 1.2.2
 * @param array $new Array of all the meta values
 * @return array $new New meta value with empty keys removed
 */
function edd_metabox_save_check_blank_rows( $new ) {
	foreach ( $new as $key => $value ) {
		if ( empty( $value['name'] ) && empty( $value['amount'] ) && empty( $value['file'] ) )
			unset( $new[ $key ] );
	}

	return $new;
}

/** Download Configuration ****************************************************/

/**
 * Download Metabox
 *
 * Extensions (as well as the core plugin) can add items to the main download
 * configuration metabox via the `edd_meta_box_fields` action.
 *
 * @since 1.0
 * @return void
 */
function edd_render_download_meta_box() {
	$post_id = get_the_ID();

	/*
	 * Output the price fields
	 * @since 1.9
	 */
	do_action( 'edd_meta_box_price_fields', $post_id );

	/*
	 * Output the price fields
	 *
	 * Left for backwards compatibility
	 *
	 */
	do_action( 'edd_meta_box_fields', $post_id );

	wp_nonce_field( basename( __FILE__ ), 'edd_download_meta_box_nonce' );
}

/**
 * Download Files Metabox
 *
 * @since 1.9
 * @return void
 */
function edd_render_files_meta_box() {
	/*
	 * Output the files fields
	 * @since 1.9
	 */
	do_action( 'edd_meta_box_files_fields', get_the_ID() );
}

/**
 * Download Settings Metabox
 *
 * @since 1.9
 * @return void
 */
function edd_render_settings_meta_box() {
	/*
	 * Output the files fields
	 * @since 1.9
	 */
	do_action( 'edd_meta_box_settings_fields', get_the_ID() );
}

/**
 * Price Section
 *
 * If variable pricing is not enabled, simply output a single input box.
 *
 * If variable pricing is enabled, outputs a table of all current prices.
 * Extensions can add column heads to the table via the `edd_download_file_table_head`
 * hook, and actual columns via `edd_download_file_table_row`
 *
 * @since 1.0
 *
 * @see edd_render_price_row()
 *
 * @param $post_id
 */
function edd_render_price_field( $post_id ) {
	$price              = edd_get_download_price( $post_id );
	$variable_pricing   = edd_has_variable_prices( $post_id );
	$prices             = edd_get_variable_prices( $post_id );
	$single_option_mode = edd_single_price_option_mode( $post_id );

	$price_display      = $variable_pricing ? ' style="display:none;"' : '';
	$variable_display   = $variable_pricing ? '' : ' style="display:none;"';
	$currency_position  = edd_get_option( 'currency_position', 'before' );
	?>
	<p>
		<strong><?php echo apply_filters( 'edd_price_options_heading', __( 'Pricing Options:', 'easy-digital-downloads' ) ); ?></strong>
	</p>

	<div class="edd-form-group">
		<div class="edd-form-group__control">
			<input type="checkbox" class="edd-form-group__input" name="_variable_pricing" id="edd_variable_pricing" value="1" <?php checked( 1, $variable_pricing ); ?> />
			<label for="edd_variable_pricing">
				<?php echo esc_html( apply_filters( 'edd_variable_pricing_toggle_text', __( 'Enable variable pricing', 'easy-digital-downloads' ) ) ); ?>
			</label>
		</div>
	</div>

	<div id="edd_regular_price_field" class="edd-form-group edd_pricing_fields" <?php echo $price_display; ?>>
		<label for="edd_price" class="edd-form-group__label screen-reader-text"><?php esc_html_e( 'Price', 'easy-digital-downloads' ); ?></label>
		<div class="edd-form-group__control">
		<?php
			$price_args = array(
				'name'  => 'edd_price',
				'id'    => 'edd_price',
				'value' => isset( $price ) ? esc_attr( edd_format_amount( $price ) ) : '',
				'class' => 'edd-form-group__input edd-price-field',
			);
			if ( 'before' === $currency_position ) {
				?>
				<span class="edd-amount-control__currency is-before"><?php echo esc_html( edd_currency_filter( '' ) ); ?></span>
				<?php
				echo EDD()->html->text( $price_args );
			} else {
				echo EDD()->html->text( $price_args );
				?>
				<span class="edd-amount-control__currency is-after"><?php echo esc_html( edd_currency_filter( '' ) ); ?></span>
				<?php
			}

			do_action( 'edd_price_field', $post_id );
			?>
		</div>
	</div>

	<?php do_action( 'edd_after_price_field', $post_id ); ?>

	<div id="edd_variable_price_fields" class="edd_pricing_fields" <?php echo $variable_display; ?>>
		<input type="hidden" id="edd_variable_prices" class="edd_variable_prices_name_field" value=""/>
			<div class="edd-form-group">
				<div class="edd-form-group__control">
					<?php echo EDD()->html->checkbox( array( 'name' => '_edd_price_options_mode', 'current' => $single_option_mode, 'class' => 'edd-form-group__input', ) ); ?>
					<label for="_edd_price_options_mode"><?php echo esc_html( apply_filters( 'edd_multi_option_purchase_text', __( 'Enable multi-option purchase mode. Allows multiple price options to be added to your cart at once', 'easy-digital-downloads' ) ) ); ?></label>
				</div>
			</div>
		<div id="edd_price_fields" class="edd_meta_table_wrap">
			<div class="widefat edd_repeatable_table">

				<div class="edd-price-option-fields edd-repeatables-wrap">
					<?php
						if ( ! empty( $prices ) ) :

							foreach ( $prices as $key => $value ) :
								$name   = ( isset( $value['name'] ) && ! empty( $value['name'] ) ) ? $value['name']   : '';
								$index  = ( isset( $value['index'] ) && $value['index'] !== '' )   ? $value['index']  : $key;
								$amount = isset( $value['amount'] ) ? $value['amount'] : '';
								$args   = apply_filters( 'edd_price_row_args', compact( 'name', 'amount' ), $value );
								?>
								<div class="edd_variable_prices_wrapper edd_repeatable_row" data-key="<?php echo esc_attr( $key ); ?>">
									<?php do_action( 'edd_render_price_row', $key, $args, $post_id, $index ); ?>
								</div>
							<?php
							endforeach;
						else :
					?>
						<div class="edd_variable_prices_wrapper edd_repeatable_row" data-key="1">
							<?php do_action( 'edd_render_price_row', 1, array(), $post_id, 1 ); ?>
						</div>
					<?php endif; ?>

				</div>

				<div class="edd-add-repeatable-row">
					<button class="button-secondary edd_add_repeatable"><?php _e( 'Add New Price', 'easy-digital-downloads' ); ?></button>
				</div>
			</div>
		</div>
	</div><!--end #edd_variable_price_fields-->
<?php
}
add_action( 'edd_meta_box_price_fields', 'edd_render_price_field', 10 );

/**
 * Individual Price Row
 *
 * Used to output a table row for each price associated with a download.
 * Can be called directly, or attached to an action.
 *
 * @since 1.2.2
 *
 * @param       $key
 * @param array $args
 * @param       $post_id
 */
function edd_render_price_row( $key, $args, $post_id, $index ) {
	global $wp_filter;

	$defaults = array(
		'name'   => null,
		'amount' => null
	);

	$args = wp_parse_args( $args, $defaults );

	$default_price_id     = edd_get_default_variable_price( $post_id );
	$currency_position    = edd_get_option( 'currency_position', 'before' );
	$custom_price_options = isset( $wp_filter['edd_download_price_option_row'] ) ? true : false;

	// Run our advanced settings now, so we know if we need to display the settings.
	// Output buffer so that the headers run, so we can log them and use them later
	ob_start();
	if ( has_action( 'edd_download_price_table_head' ) ) {
		do_action_deprecated( 'edd_download_price_table_head', array( $post_id ), '2.10', 'edd_download_price_option_row' );
	}
	ob_end_clean();

	ob_start();
	$found_fields = isset( $wp_filter['edd_download_price_table_row'] ) ? $wp_filter['edd_download_price_table_row'] : false;
	if ( ! empty( $found_fields->callbacks ) ) {
		if ( 1 !== count( $found_fields->callbacks ) ) {
			do_action_deprecated( 'edd_download_price_table_row', array( $post_id, $key, $args ), '2.10', 'edd_download_price_option_row' );
		} else {
			do_action( 'edd_download_price_table_row', $post_id, $key, $args );
		}
	}
	$show_advanced = ob_get_clean();
?>
	<div class="edd-repeatable-row-header edd-draghandle-anchor">
		<span class="edd-repeatable-row-title" title="<?php _e( 'Click and drag to re-order price options', 'easy-digital-downloads' ); ?>">
			<?php printf( __( 'Price ID: %s', 'easy-digital-downloads' ), '<span class="edd_price_id">' . $key . '</span>' ); ?>
			<input type="hidden" name="edd_variable_prices[<?php echo $key; ?>][index]" class="edd_repeatable_index" value="<?php echo $index; ?>"/>
		</span>
		<?php
		$actions = array();
		if ( ! empty( $show_advanced ) || $custom_price_options ) {
			$actions['show_advanced'] = '<a href="#" class="toggle-custom-price-option-section">' . __( 'Show advanced settings', 'easy-digital-downloads' ) . '</a>';
		}

		$actions['remove'] = '<a class="edd-remove-row edd-delete" data-type="price">' . sprintf( __( 'Remove', 'easy-digital-downloads' ), $key ) . '<span class="screen-reader-text">' . sprintf( __( 'Remove price option %s', 'easy-digital-downloads' ), $key ) . '</span></a>';
		?>
		<span class="edd-repeatable-row-actions">
			<?php echo implode( '&nbsp;&#124;&nbsp;', $actions ); ?>
		</span>
	</div>

	<div class="edd-repeatable-row-standard-fields">

		<div class="edd-form-group edd-option-name">
			<label for="edd_variable_prices-<?php echo esc_attr( $key ); ?>-name" class="edd-form-group__label edd-repeatable-row-setting-label"><?php esc_html_e( 'Option Name', 'easy-digital-downloads' ); ?></label>
			<div class="edd-form-group__control">
			<?php echo EDD()->html->text( array(
				'name'        => 'edd_variable_prices[' . $key . '][name]',
				'id'          => 'edd_variable_prices-' . $key . '-name',
				'value'       => esc_attr( $args['name'] ),
				'placeholder' => __( 'Option Name', 'easy-digital-downloads' ),
				'class'       => 'edd_variable_prices_name large-text'
			) ); ?>
			</div>
		</div>

		<div class="edd-form-group edd-option-price">
			<label for="edd_variable_prices-<?php echo esc_attr( $key ); ?>-amount" class="edd-repeatable-row-setting-label"><?php esc_html_e( 'Price', 'easy-digital-downloads' ); ?></label>
			<?php
			$price_args = array(
				'name'        => 'edd_variable_prices[' . $key . '][amount]',
				'id'          => 'edd_variable_prices-' . $key . '-amount',
				'value'       => $args['amount'],
				'placeholder' => edd_format_amount( 9.99 ),
				'class'       => 'edd-form-group__input edd-price-field',
			);
			?>

			<div class="edd-form-group__control edd-price-input-group">
				<?php
				if ( 'before' === $currency_position ) {
					?>
					<span class="edd-amount-control__currency is-before"><?php echo esc_html( edd_currency_filter( '' ) ); ?></span>
					<?php
					echo EDD()->html->text( $price_args );
				} else {
					echo EDD()->html->text( $price_args );
					?>
					<span class="edd-amount-control__currency is-after"><?php echo esc_html( edd_currency_filter( '' ) ); ?></span>
					<?php
				}
				?>
			</div>
		</div>

		<div class="edd-form-group edd_repeatable_default edd_repeatable_default_wrapper">
			<div class="edd-form-group__control">
			<label for="edd_default_price_id_<?php echo esc_attr( $key ); ?>" class="edd-repeatable-row-setting-label"><?php esc_html_e( 'Default', 'easy-digital-downloads' ); ?></label>
				<input type="radio" <?php checked( $default_price_id, $key, true ); ?> class="edd_repeatable_default_input" name="_edd_default_price_id" id="edd_default_price_id_<?php echo esc_attr( $key ); ?>" value="<?php echo $key; ?>" />
				<span class="screen-reader-text"><?php printf( __( 'Set ID %s as default price', 'easy-digital-downloads' ), $key ); ?></span>
			</div>
		</div>

	</div>

	<?php
		/**
		 * Intercept extension-specific settings and rebuild the markup
		 */
		if ( ! empty( $show_advanced ) || $custom_price_options ) {
			?>

			<div class="edd-custom-price-option-sections-wrap">
				<?php
				$elements = str_replace(
					array(
						'<td>',
						'<td ',
						'</td>',
						'<th>',
						'<th ',
						'</th>',
						'class="times"',
						'class="signup_fee"',
					),
					array(
						'<span class="edd-custom-price-option-section">',
						'<span ',
						'</span>',
						'<label class="edd-legacy-setting-label">',
						'<label ',
						'</label>',
						'class="edd-recurring-times times"', // keep old class for back compat
						'class="edd-recurring-signup-fee signup_fee"' // keep old class for back compat
					),
					$show_advanced
				);
				?>
				<div class="edd-custom-price-option-sections">
					<?php
						echo $elements;
						do_action( 'edd_download_price_option_row', $post_id, $key, $args );
					?>
				</div>
			</div>

			<?php
		}
}
add_action( 'edd_render_price_row', 'edd_render_price_row', 10, 4 );

/**
 * Product type options
 *
 * @access      private
 * @since       1.6
 * @return      void
 */
function edd_render_product_type_field( $post_id = 0 ) {

	$types = edd_get_download_types();
	$type  = edd_get_download_type( $post_id );
	?>
	<div class="edd-form-group">
		<label for="_edd_product_type" class="edd-form-group__label"><?php echo apply_filters( 'edd_product_type_options_heading', __( 'Product Type Options:', 'easy-digital-downloads' ) ); ?></label>
		<div class="edd-form-group__control">
			<?php echo EDD()->html->select(
				array(
					'options'          => $types,
					'name'             => '_edd_product_type',
					'id'               => '_edd_product_type',
					'selected'         => $type,
					'show_option_all'  => false,
					'show_option_none' => false,
					'class'            => 'edd-form-group__input',
				)
			);
			?>
			<span class="description"><?php esc_html_e( 'Select a product type', 'easy-digital-downloads' ); ?></span>
			<span alt="f223" class="edd-help-tip dashicons dashicons-editor-help" title="<?php _e( '<strong>Product Type</strong>: Sell this item as a single product, or use the Bundle type to sell a collection of products.', 'easy-digital-downloads' ); ?>"></span>
		</div>
	</div>
	<?php
}
add_action( 'edd_meta_box_files_fields', 'edd_render_product_type_field', 10 );

/**
 * Renders product field
 * @since 1.6
 *
 * @param $post_id
 */
function edd_render_products_field( $post_id ) {
	$download         = new EDD_Download( $post_id );
	$type             = $download->get_type();
	$display          = $type == 'bundle' ? '' : ' style="display:none;"';
	$products         = $download->get_bundled_downloads();
	$variable_pricing = $download->has_variable_prices();
	$variable_display = $variable_pricing ? '' : 'display:none;';
	$variable_class   = $variable_pricing ? ' has-variable-pricing' : '';
	$prices           = $download->get_prices(); ?>

	<div id="edd_products"<?php echo $display; ?>>
		<div id="edd_file_fields_bundle" class="edd_meta_table_wrap">
			<div class="widefat edd_repeatable_table">

				<?php do_action( 'edd_download_products_table_head', $post_id ); ?>

				<div class="edd-bundled-product-select edd-repeatables-wrap">

					<?php if ( $products ) : ?>

						<div class="edd-bundle-products-header">
							<span class="edd-bundle-products-title"><?php printf( __( 'Bundled %s', 'easy-digital-downloads' ), edd_get_label_plural() ); ?></span>
						</div>

						<?php $index = 1; ?>
						<?php foreach ( $products as $key => $product ) : ?>
							<div class="edd_repeatable_product_wrapper edd_repeatable_row" data-key="<?php echo esc_attr( $index ); ?>">
								<div class="edd-bundled-product-row<?php echo $variable_class; ?>">
									<div class="edd-bundled-product-item-reorder">
										<span class="edd-product-file-reorder edd-draghandle-anchor dashicons dashicons-move"  title="<?php printf( __( 'Click and drag to re-order bundled %s', 'easy-digital-downloads' ), edd_get_label_plural() ); ?>"></span>
										<input type="hidden" name="edd_bundled_products[<?php echo $index; ?>][index]" class="edd_repeatable_index" value="<?php echo $index; ?>"/>
									</div>
									<div class="edd-form-group edd-bundled-product-item">
										<label for="edd_bundled_products_<?php echo esc_attr( $index ); ?>" class="edd-form-group__label edd-repeatable-row-setting-label"><?php printf( esc_html__( 'Select %s:', 'easy-digital-downloads' ), edd_get_label_singular() ); ?></label>
										<div class="edd-form-group__control">
										<?php
										echo EDD()->html->product_dropdown(
											array(
												'name'                 => '_edd_bundled_products[]',
												'id'                   => 'edd_bundled_products_' . esc_attr( $index ),
												'selected'             => $product,
												'multiple'             => false,
												'chosen'               => true,
												'bundles'              => false,
												'variations'           => true,
												'show_variations_only' => true,
												'class'                => 'edd-form-group__input',
											)
										);
										?>
										</div>
									</div>
									<div class="edd-form-group edd-bundled-product-price-assignment pricing" style="<?php echo $variable_display; ?>">
										<label class="edd-form-group__label edd-repeatable-row-setting-label" for="edd_bundled_products_conditions_<?php echo esc_attr( $index ); ?>"><?php esc_html_e( 'Price assignment:', 'easy-digital-downloads' ); ?></label>
										<div class="edd-form-group__control">
										<?php
											$options = array();

											if ( $prices ) {
												foreach ( $prices as $price_key => $price ) {
													$options[ $price_key ] = $prices[ $price_key ]['name'];
												}
											}

											$price_assignments = edd_get_bundle_pricing_variations( $post_id );
											$price_assignments = $price_assignments[0];

											$selected = isset( $price_assignments[ $index ] ) ? $price_assignments[ $index ] : null;

											echo EDD()->html->select( array(
												'name'             => '_edd_bundled_products_conditions['. $index .']',
												'id'               => 'edd_bundled_products_conditions_'. esc_attr( $index ),
												'class'            => 'edd_repeatable_condition_field',
												'options'          => $options,
												'show_option_none' => false,
												'selected'         => $selected
											) );
										?>
										</div>
									</div>
									<div class="edd-bundled-product-actions">
										<a class="edd-remove-row edd-delete" data-type="file"><?php printf( __( 'Remove', 'easy-digital-downloads' ), $index ); ?><span class="screen-reader-text"><?php printf( __( 'Remove bundle option %s', 'easy-digital-downloads' ), $index ); ?></span></a>
									</div>
									<?php do_action( 'edd_download_products_table_row', $post_id ); ?>
								</div>
							</div>
							<?php $index++; ?>
						<?php endforeach; ?>

					<?php else: ?>

						<div class="edd-bundle-products-header">
							<span class="edd-bundle-products-title"><?php printf( __( 'Bundled %s:', 'easy-digital-downloads' ), edd_get_label_plural() ); ?></span>
						</div>
						<div class="edd_repeatable_product_wrapper edd_repeatable_row" data-key="1">
							<div class="edd-bundled-product-row<?php echo $variable_class; ?>">

								<div class="edd-bundled-product-item-reorder">
									<span class="edd-product-file-reorder edd-draghandle-anchor dashicons dashicons-move" title="<?php printf( __( 'Click and drag to re-order bundled %s', 'easy-digital-downloads' ), edd_get_label_plural() ); ?>"></span>
									<input type="hidden" name="edd_bundled_products[1][index]" class="edd_repeatable_index" value="1"/>
								</div>
								<div class="edd-form-group edd-bundled-product-item">
									<label class="edd-form-group__label edd-repeatable-row-setting-label" for="edd_bundled_products_1"><?php printf( esc_html__( 'Select %s:', 'easy-digital-downloads' ), edd_get_label_singular() ); ?></label>
									<div class="edd-form-group__control">
									<?php
									echo EDD()->html->product_dropdown( array(
										'name'                 => '_edd_bundled_products[]',
										'id'                   => 'edd_bundled_products_1',
										'multiple'             => false,
										'chosen'               => true,
										'bundles'              => false,
										'variations'           => true,
										'show_variations_only' => true,
									) );
									?>
									</div>
								</div>
								<div class="edd-form-group edd-bundled-product-price-assignment pricing" style="<?php echo $variable_display; ?>">
									<label class="edd-form-group__label edd-repeatable-row-setting-label" for="edd_bundled_products_conditions_1"><?php esc_html_e( 'Price assignment:', 'easy-digital-downloads' ); ?></label>
									<div class="edd-form-group__control">
									<?php
										$options = array();

										if ( $prices ) {
											foreach ( $prices as $price_key => $price ) {
												$options[ $price_key ] = $prices[ $price_key ]['name'];
											}
										}

										$price_assignments = edd_get_bundle_pricing_variations( $post_id );

										echo EDD()->html->select( array(
											'name'             => '_edd_bundled_products_conditions[1]',
											'id'               => 'edd_bundled_products_conditions_1',
											'class'            => 'edd-form-group__input edd_repeatable_condition_field',
											'options'          => $options,
											'show_option_none' => false,
											'selected'         => null,
										) );
									?>
									</div>
								</div>
								<div class="edd-bundled-product-actions">
									<a class="edd-remove-row edd-delete" data-type="file" ><?php printf( __( 'Remove', 'easy-digital-downloads' ) ); ?><span class="screen-reader-text"><?php __( 'Remove bundle option 1', 'easy-digital-downloads' ); ?></span></a>
								</div>
								<?php do_action( 'edd_download_products_table_row', $post_id ); ?>
							</div>
						</div>

					<?php endif; ?>

				</div>

				<div class="edd-add-repeatable-row">
					<button class="button-secondary edd_add_repeatable"><?php _e( 'Add New File', 'easy-digital-downloads' ); ?></button>
				</div>
			</div>
		</div>
	</div>
<?php
}
add_action( 'edd_meta_box_files_fields', 'edd_render_products_field', 10 );

/**
 * File Downloads section.
 *
 * Outputs a table of all current files. Extensions can add column heads to the table
 * via the `edd_download_file_table_head` hook, and actual columns via
 * `edd_download_file_table_row`
 *
 * @since 1.0
 * @see edd_render_file_row()
 * @param int $post_id Download (Post) ID
 * @return void
 */
function edd_render_files_field( $post_id = 0 ) {
	$type    = edd_get_download_type( $post_id );
	$files   = edd_get_download_files( $post_id );
	$display = $type == 'bundle' ? ' style="display:none;"' : ''; ?>

	<div id="edd_download_files"<?php echo $display; ?>>
		<div id="edd_file_fields_default" class="edd_meta_table_wrap">
			<div class="widefat edd_repeatable_table">

				<div class="edd-file-fields edd-repeatables-wrap">
					<?php

					if ( ! empty( $files ) && is_array( $files ) ) :
						foreach ( $files as $key => $value ) :
							$index          = isset( $value['index'] )          ? $value['index']                   : $key;
							$name           = isset( $value['name'] )           ? $value['name']                    : '';
							$file           = isset( $value['file'] )           ? $value['file']                    : '';
							$condition      = isset( $value['condition'] )      ? $value['condition']               : false;
							$thumbnail_size = isset( $value['thumbnail_size'] ) ? $value['thumbnail_size']          : '';
							$attachment_id  = isset( $value['attachment_id'] )  ? absint( $value['attachment_id'] ) : false;

							$args = apply_filters( 'edd_file_row_args', compact( 'name', 'file', 'condition', 'attachment_id', 'thumbnail_size' ), $value ); ?>

							<div class="edd_repeatable_upload_wrapper edd_repeatable_row" data-key="<?php echo esc_attr( $key ); ?>">
								<?php do_action( 'edd_render_file_row', $key, $args, $post_id, $index ); ?>
							</div>

							<?php
						endforeach;
					else : ?>

						<div class="edd_repeatable_upload_wrapper edd_repeatable_row">
							<?php do_action( 'edd_render_file_row', 1, array(), $post_id, 0 ); ?>
						</div>

					<?php endif; ?>

				</div>

				<div class="edd-add-repeatable-row">
					<button class="button-secondary edd_add_repeatable"><?php _e( 'Add New File', 'easy-digital-downloads' ); ?></button>
				</div>
			</div>
		</div>
	</div>
<?php
}
add_action( 'edd_meta_box_files_fields', 'edd_render_files_field', 20 );


/**
 * Individual file row.
 *
 * Used to output a table row for each file associated with a download.
 * Can be called directly, or attached to an action.
 *
 * @since 1.2.2
 * @param string $key Array key
 * @param array $args Array of all the arguments passed to the function
 * @param int $post_id Download (Post) ID
 * @return void
 */
function edd_render_file_row( $key, $args, $post_id, $index ) {

	$args = wp_parse_args( $args, array(
		'name'           => null,
		'file'           => null,
		'condition'      => null,
		'attachment_id'  => null,
		'thumbnail_size' => null,
	) );

	$prices           = edd_get_variable_prices( $post_id );
	$variable_pricing = edd_has_variable_prices( $post_id );
	$variable_display = $variable_pricing ? '' : ' style="display:none;"';
	$variable_class   = $variable_pricing ? ' has-variable-pricing' : ''; ?>

	<div class="edd-repeatable-row-header edd-draghandle-anchor">
		<span class="edd-repeatable-row-title" title="<?php _e( 'Click and drag to re-order files', 'easy-digital-downloads' ); ?>">
			<?php printf( __( '%1$s file ID: %2$s', 'easy-digital-downloads' ), edd_get_label_singular(), '<span class="edd_file_id">' . esc_html( $key ) . '</span>' ); ?>
			<input type="hidden" name="edd_download_files[<?php echo esc_attr( $key ); ?>][index]" class="edd_repeatable_index" value="<?php echo esc_attr( $index ); ?>"/>
		</span>
		<span class="edd-repeatable-row-actions">
			<a class="edd-remove-row edd-delete" data-type="file">
				<?php _e( 'Remove', 'easy-digital-downloads' ); ?><span class="screen-reader-text"><?php printf( __( 'Remove file %s', 'easy-digital-downloads' ), $key ); ?></span>
			</a>
		</span>
	</div>

	<div class="edd-repeatable-row-standard-fields<?php echo $variable_class; ?>">
		<div class="edd-form-group edd-file-name">
			<label for="edd_download_files-<?php echo esc_attr( $key ); ?>-name" class="edd-form-group__label edd-repeatable-row-setting-label"><?php esc_html_e( 'File Name', 'easy-digital-downloads' ); ?></label>
			<div class="edd-form-group__control">
			<input type="hidden" name="edd_download_files[<?php echo absint( $key ); ?>][attachment_id]" class="edd_repeatable_attachment_id_field" value="<?php echo esc_attr( absint( $args['attachment_id'] ) ); ?>"/>
			<input type="hidden" name="edd_download_files[<?php echo absint( $key ); ?>][thumbnail_size]" class="edd_repeatable_thumbnail_size_field" value="<?php echo esc_attr( $args['thumbnail_size'] ); ?>"/>
			<?php echo EDD()->html->text( array(
				'name'        => 'edd_download_files[' . $key . '][name]',
				'id'          => 'edd_download_files-' . $key . '-name',
				'value'       => $args['name'],
				'placeholder' => __( 'My Neat File', 'easy-digital-downloads' ),
				'class'       => 'edd-form-group__input edd_repeatable_name_field large-text',
			) ); ?>
			</div>
		</div>

		<div class="edd-form-group edd-file-url">
			<label for="edd_download_files-<?php echo esc_attr( $key ); ?>-file" class="edd-form-group__label edd-repeatable-row-setting-label"><?php esc_html_e( 'File URL', 'easy-digital-downloads' ); ?></label>
			<div class="edd-form-group__control edd_repeatable_upload_field_container">
				<?php echo EDD()->html->text( array(
					'name'        => 'edd_download_files[' . $key . '][file]',
					'id'          => 'edd_download_files-' . $key . '-file',
					'value'       => $args['file'],
					'placeholder' => __( 'Enter, upload, choose from Media Library', 'easy-digital-downloads' ),
					'class'       => 'edd-form-group__input edd_repeatable_upload_field edd_upload_field large-text',
				) ); ?>

				<span class="edd_upload_file">
					<button data-uploader-title="<?php esc_attr_e( 'Select Files', 'easy-digital-downloads' ); ?>" data-uploader-button-text="<?php esc_attr_e( 'Select', 'easy-digital-downloads' ); ?>" class="edd_upload_file_button" onclick="return false;">
						<span class="dashicons dashicons-admin-links"></span>
						<span class="screen-reader-text"><?php esc_html_e( 'Select Files', 'easy-digital-downloads' ); ?></span>
				</button>
				</span>
			</div>
		</div>

		<div class="edd-form-group edd-file-assignment pricing"<?php echo $variable_display; ?>>

			<label for="edd_download_files_<?php echo esc_attr( $key ); ?>_condition" class="edd-form-group__label edd-repeatable-row-setting-label"><?php esc_html_e( 'Price Assignment', 'easy-digital-downloads' ); ?><span alt="f223" class="edd-help-tip dashicons dashicons-editor-help" title="<?php _e( '<strong>Price Assignment</strong>: With variable pricing enabled, you can choose to allow certain price variations access to specific files, or allow all price variations to access a file.', 'easy-digital-downloads' ); ?>"></span></label>
			<div class="edd-form-group__control">
			<?php
				$options = array();

				if ( ! empty( $prices ) ) {
					foreach ( $prices as $price_key => $price ) {
						$options[ $price_key ] = $prices[ $price_key ]['name'];
					}
				}

				echo EDD()->html->select( array(
					'name'             => 'edd_download_files[' . $key . '][condition]',
					'id'               => 'edd_download_files-' . $key . '-condition',
					'class'            => 'edd-form-group__input edd_repeatable_condition_field',
					'options'          => $options,
					'selected'         => $args['condition'],
					'show_option_none' => false,
				) );
			?>
			</div>
		</div>

		<?php do_action( 'edd_download_file_table_row', $post_id, $key, $args ); ?>

	</div>
<?php
}
add_action( 'edd_render_file_row', 'edd_render_file_row', 10, 4 );

/**
 * Alter the Add to post button in the media manager for downloads
 *
 * @since  2.2
 * @param  array $strings Array of default strings for media manager
 * @return array          The altered array of strings for media manager
 */
function edd_download_media_strings( $strings ) {
	global $post;

	if ( empty( $post ) || ( $post->post_type !== 'download' ) ) {
		return $strings;
	}

	$downloads_object = get_post_type_object( 'download' );
	$labels           = $downloads_object->labels;

	$strings['insertIntoPost'] = sprintf( __( 'Insert into %s', 'easy-digital-downloads' ), strtolower( $labels->singular_name ) );

	return $strings;
}
add_filter( 'media_view_strings', 'edd_download_media_strings', 10, 1 );

/**
 * Refund Window
 *
 * The refund window is the maximum number of days each
 * can be downloaded by the buyer
 *
 * @since 3.0
 * @param int $post_id Download (Post) ID
 * @return void
 */
function edd_render_refund_row( $post_id ) {

	// Bail if user cannot manage shop settings
	if ( ! current_user_can( 'manage_shop_settings' ) ) {
		return;
	}

	$types             = edd_get_refundability_types();
	$global_ability    = edd_get_option( 'refundability', 'refundable' );
	$refundability     = edd_get_download_refundability( $post_id );
	$global_window     = edd_get_option( 'refund_window', 30 );
	$edd_refund_window = edd_get_download_refund_window( $post_id ); ?>

	<div class="edd-form-group edd-product-options-wrapper">
		<div class="edd-product-options__title">
				<?php esc_html_e( 'Refunds', 'easy-digital-downloads' ); ?>
				<span alt="f223" class="edd-help-tip dashicons dashicons-editor-help" title="<?php echo wp_kses( __( '<strong>Refundable</strong>: Allow or disallow refunds for this specific product. When allowed, the refund window will be used on all future purchases.<br /><strong>Refund Window</strong>: Limit the number of days this product can be refunded after purchasing.', 'easy-digital-downloads' ), array( 'strong' => true, 'br' => true ) ); ?>"></span>
		</div>

		<div class="edd-form-group__control">
			<label for="edd_refundability" class="edd-form-group__label">
				<?php esc_html_e( 'Refund Status', 'easy-digital-downloads' ); ?>
			</label>
			<?php echo EDD()->html->select( array(
				'name'             => '_edd_refundability',
				'id'               => 'edd_refundability',
				'class'            => 'edd-form-group__input',
				'options'          => array_merge(
					// Manually define a "none" option to set a blank value, vs. -1.
					array(
						'' => sprintf(
							/* translators: Default refund status */
							esc_html_x( 'Default (%1$s)', 'Download refund status', 'easy-digital-downloads' ),
							ucwords( $refundability )
						),
					),
					$types
				),
				// Use the direct meta value to avoid falling back to default.
				'selected'         => get_post_meta( $post_id, '_edd_refundability', true ),
				'show_option_all'  => '',
				'show_option_none' => false,
			) ); ?>
		</div>

		<div class="edd-form-group__control">
			<label for="_edd_refund_window" class="edd-form-group__label">
				<?php esc_html_e( 'Refund Window', 'easy-digital-downloads' ); ?>
			</label>
			<input class="edd-form-group__input small-text" id="_edd_refund_window" name="_edd_refund_window" type="number" min="0" max="3650" step="1" value="<?php echo esc_attr( $edd_refund_window ); ?>" placeholder="<?php echo absint( $global_window ); ?>" />
			<?php echo esc_html( _x( 'Days', 'refund window interval', 'easy-digital-downloads' ) ); ?>
		</div>
		<p class="edd-form-group__help description">
			<?php _e( 'Leave blank to use global setting. Enter <code>0</code> for unlimited', 'easy-digital-downloads' ); ?>
		</p>
	</div>
<?php
}
add_action( 'edd_meta_box_settings_fields', 'edd_render_refund_row', 25 );

/**
 * File Download Limit Row
 *
 * The file download limit is the maximum number of times each file
 * can be downloaded by the buyer
 *
 * @since 1.3.1
 * @param int $post_id Download (Post) ID
 * @return void
 */
function edd_render_download_limit_row( $post_id ) {
	if ( ! current_user_can( 'manage_shop_settings' ) ) {
		return;
	}

	$edd_download_limit = edd_get_file_download_limit( $post_id );
	$display = 'bundle' == edd_get_download_type( $post_id ) ? ' style="display: none;"' : '';
?>
	<div class="edd-form-group edd-product-options-wrapper" id="edd_download_limit_wrap"<?php echo $display; ?>>
		<div class="edd-form-group__control">
			<label class="edd-form-group__label edd-product-options__title" for="edd_download_limit">
				<?php esc_html_e( 'File Download Limit', 'easy-digital-downloads' ); ?><span alt="f223" class="edd-help-tip dashicons dashicons-editor-help" title="<?php _e( '<strong>File Download Limit</strong>: Limit the number of times a customer who purchased this product can access their download links.', 'easy-digital-downloads' ); ?>"></span>
			</label>
			<input class="edd-form-group__input small-text" name="_edd_download_limit" id="edd_download_limit" type="number" min="0" max="5000" step="1" value="<?php echo esc_attr ( $edd_download_limit ); ?>" />
		</div>
		<p class="edd-form-group__help description">
			<?php _e( 'Leave blank to use global setting. Enter <code>0</code> for unlimited', 'easy-digital-downloads' ); ?>
		</p>
	</div>
<?php
}
add_action( 'edd_meta_box_settings_fields', 'edd_render_download_limit_row', 20 );

/**
 * Product tax settings
 *
 * Outputs the option to mark whether a product is exclusive of tax
 *
 * @since 1.9
 * @param int $post_id Download (Post) ID
 * @return void
 */
function edd_render_dowwn_tax_options( $post_id = 0 ) {
	edd_render_down_tax_options( $post_id );
}

/**
 * Product tax settings
 *
 * Outputs the option to mark whether a product is exclusive of tax
 *
 * @since 1.9
 * @since 2.8.12 Fixed miss-spelling in function name. See https://github.com/easydigitaldownloads/easy-digital-downloads/issues/5101
 * @param int $post_id Download (Post) ID
 * @return void
 */
function edd_render_down_tax_options( $post_id = 0 ) {

	// Bail if current user cannot view shop reports, or taxes are disabled,
	if ( ! current_user_can( 'view_shop_reports' ) || ! edd_use_taxes() ) {
		return;
	}

	$exclusive = edd_download_is_tax_exclusive( $post_id ); ?>

	<div class="edd-form-group edd-product-options-wrapper">
		<div class="edd-product-options__title"><?php esc_html_e( 'Taxability', 'easy-digital-downloads' ); ?><span alt="f223" class="edd-help-tip dashicons dashicons-editor-help" title="<?php _e( '<strong>Taxability</strong>: When taxes are enabled, all products are taxable by default. Check this box to mark this product as non-taxable.', 'easy-digital-downloads' ); ?>"></span></div>
		<div class="edd-form-group__control">
			<?php echo EDD()->html->checkbox(
				array(
					'name'    => '_edd_download_tax_exclusive',
					'id'      => '_edd_download_tax_exclusive',
					'current' => $exclusive,
					'class'   => 'edd-form-group__input',
				)
			); ?>
			<label for="_edd_download_tax_exclusive" class="edd-form-group__label">
				<?php esc_html_e( 'This product is non-taxable', 'easy-digital-downloads' ); ?>
			</label>
		</div>
	</div>
	<?php
}
add_action( 'edd_meta_box_settings_fields', 'edd_render_down_tax_options', 30 );

/**
 * Product quantity settings
 *
 * Outputs the option to disable quantity field on product.
 *
 * @since 2.7
 * @param int $post_id Download (Post) ID
 * @return void
 */
function edd_render_download_quantity_option( $post_id = 0 ) {
	if ( ! current_user_can( 'manage_shop_settings' ) || ! edd_item_quantities_enabled() ) {
		return;
	}

	$disabled = edd_download_quantities_disabled( $post_id ); ?>

	<div class="edd-form-group edd-product-options-wrapper">
		<div class="edd-product-options__title"><?php esc_html_e( 'Item Quantities', 'easy-digital-downloads' ); ?><span alt="f223" class="edd-help-tip dashicons dashicons-editor-help" title="<?php _e( '<strong>Item Quantities</strong>: if disabled, customers will not be provided an option to change the number they wish to purchase.', 'easy-digital-downloads' ); ?>"></span></div>
		<div class="edd-form-group__control">
			<?php
			echo EDD()->html->checkbox(
				array(
					'name'    => '_edd_quantities_disabled',
					'id'      => '_edd_quantities_disabled',
					'current' => $disabled,
					'class'   => 'edd-form-group__input',
				)
			);
			?>
			<label for="_edd_quantities_disabled" class="edd-form-group__label">
				<?php esc_html_e( 'Disable quantity input for this product', 'easy-digital-downloads' ); ?>
			</label>
		</div>
	</div>

<?php
}
add_action( 'edd_meta_box_settings_fields', 'edd_render_download_quantity_option', 30 );

/**
 * Add shortcode to settings meta box
 *
 * @since 2.5
 *
 * @return void
 */
function edd_render_meta_box_shortcode() {

	if ( get_post_type() !== 'download' ) {
		return;
	}

	$purchase_text = edd_get_option( 'add_to_cart_text', __( 'Purchase', 'easy-digital-downloads' ) );
	$style         = edd_get_option( 'button_style', 'button' );
	$color         = edd_get_option( 'checkout_color', 'blue' );
	$color         = ( $color == 'inherit' ) ? '' : $color;
	$shortcode     = '[purchase_link id="' . absint( get_the_ID() ) . '" text="' . esc_html( $purchase_text ) . '" style="' . $style . '" color="' . esc_attr( $color ) . '"]'; ?>

	<div class="edd-form-group edd-product-options-wrapper">
		<div class="edd-form-group__control">
			<label class="edd-form-group__label edd-product-options__title" for="edd-purchase-shortcode">
				<?php esc_html_e( 'Purchase Shortcode', 'easy-digital-downloads' ); ?><span alt="f223" class="edd-help-tip dashicons dashicons-editor-help" title="<?php _e( '<strong>Purchase Shortcode</strong>: Use this shortcode to output a purchase link for this product in the location of your choosing.', 'easy-digital-downloads' ); ?>"></span>
			</label>
			<input type="text" id="edd-purchase-shortcode" class="edd-form-group__input" readonly value="<?php echo htmlentities( $shortcode ); ?>">
		</div>
	</div>
	<?php
}
add_action( 'edd_meta_box_settings_fields', 'edd_render_meta_box_shortcode', 35 );

/**
 * Render Accounting Options
 *
 * @since 1.6
 * @param int $post_id Download (Post) ID
 * @return void
 */
function edd_render_accounting_options( $post_id ) {
	if ( ! edd_use_skus() ) {
		return;
	}

	$edd_sku = get_post_meta( $post_id, 'edd_sku', true ); ?>

	<div class="edd-form-group edd-product-options-wrapper">
		<div class="edd-product-options__title"><?php esc_html_e( 'Accounting Options', 'easy-digital-downloads' ); ?><span alt="f223" class="edd-help-tip dashicons dashicons-editor-help" title="<?php _e( '<strong>SKU</strong>: If an SKU is entered for this product, it will be shown on the purchase receipt and exported purchase histories.', 'easy-digital-downloads' ); ?>"></span></div>
		<div class="edd-form-group__control">
			<label class="edd-form-group__label" for="edd_sku">
				<?php esc_html_e( 'Enter an SKU for this product.', 'easy-digital-downloads' ); ?>
			</label>
			<?php echo EDD()->html->text(
				array(
					'name'  => 'edd_sku',
					'id'    => 'edd_sku',
					'value' => $edd_sku,
					'class' => 'edd-form-group__input small-text',
				)
			);
			?>
		</div>
	</div>
<?php
}
add_action( 'edd_meta_box_settings_fields', 'edd_render_accounting_options', 25 );


/**
 * Render Disable Button
 *
 * @since 1.0
 * @param int $post_id Download (Post) ID
 * @return void
 */
function edd_render_disable_button( $post_id ) {
	$supports_buy_now           = edd_shop_supports_buy_now();
	$hide_button                = get_post_meta( $post_id, '_edd_hide_purchase_link', true ) ? 1 : 0;
	$behavior                   = get_post_meta( $post_id, '_edd_button_behavior',    true );
	$buy_now_support_tooltip    = __( '<strong>Purchase button behavior</strong>: Add to Cart buttons follow a traditional eCommerce flow. A Buy Now button bypasses most of the process, taking the customer directly from button click to payment, greatly speeding up the process of buying the product.', 'easy-digital-downloads' );
	$no_buy_now_support_tooltip = __( '<strong>Purchase button behavior</strong>: Add to Cart buttons follow a traditional eCommerce flow. Buy Now buttons are only available for stores that have a single supported gateway active and that do not use taxes.', 'easy-digital-downloads' );
	?>

	<div class="edd-form-group edd-product-options-wrapper">
		<div class="edd-product-options__title"><?php esc_html_e( 'Button Options', 'easy-digital-downloads' ); ?><span alt="f223" class="edd-help-tip dashicons dashicons-editor-help" title="<?php _e( '<strong>Hide purchase button</strong>: By default, the purchase buttons will be displayed at the bottom of the download, when disabled you will need to use the Purchase link shortcode below to output the ability to buy the product where you prefer.', 'easy-digital-downloads' ); echo '<br><br>'; echo ! empty( $supports_buy_now ) ? $buy_now_support_tooltip : $no_buy_now_support_tooltip; ?>"></span></div>
		<div class="edd-form-group__control">
			<?php echo EDD()->html->checkbox(
				array(
					'name'    => '_edd_hide_purchase_link',
					'id'      => '_edd_hide_purchase_link',
					'current' => $hide_button,
					'class'   => 'edd-form-group__input',
				)
			);
			?>
			<label class="edd-form-group__label" for="_edd_hide_purchase_link">
				<?php esc_html_e( 'Hide purchase button', 'easy-digital-downloads' ); ?>
			</label>
		</div>
		<?php if ( ! empty( $supports_buy_now ) ) { ?>
			<div class="edd-form-group__control">
				<label for="edd_button_behavior" class="edd-form-group__label">
					<?php esc_html_e( 'Purchase button behavior', 'easy-digital-downloads' ); ?>
				</label>
				<?php
				$args = array(
					'name'             => '_edd_button_behavior',
					'id'               => 'edd_button_behavior',
					'selected'         => $behavior,
					'options'          => array(
						'add_to_cart' => __( 'Add to Cart', 'easy-digital-downloads' ),
						'direct'      => __( 'Buy Now', 'easy-digital-downloads' ),
					),
					'show_option_all'  => null,
					'show_option_none' => null,
					'class'            => 'edd-form-group__input',
				);
				echo EDD()->html->select( $args );
				?>
			</div>
			<?php
		}
		?>
	</div>

<?php
}
add_action( 'edd_meta_box_settings_fields', 'edd_render_disable_button', 30 );


/** Product Notes *************************************************************/

/**
 * Product Notes Meta Box
 *
 * Renders the Product Notes meta box
 *
 * @since 1.2.1
 *
 * @return void
 */
function edd_render_product_notes_meta_box() {
	do_action( 'edd_product_notes_meta_box_fields', get_the_ID() );
}

/**
 * Render Product Notes Field
 *
 * @since 1.2.1
 * @param int $post_id Download (Post) ID
 * @return void
 */
function edd_render_product_notes_field( $post_id ) {
	$product_notes = edd_get_product_notes( $post_id );
	?>
	<div class="edd-form-group">
		<div class="edd-form-group__control">
			<label for="edd_product_notes_field" class="edd-form-group__label screen-reader-text"><?php esc_html_e( 'Download Instructions', 'easy-digital-downloads' ); ?></label>
			<textarea rows="1" cols="40" class="edd-form-group__input large-textarea" name="edd_product_notes" id="edd_product_notes_field"><?php echo esc_textarea( $product_notes ); ?></textarea>
		</div>
		<p><?php printf( esc_html__( 'Special instructions for this %s. These will be added to the purchase receipt, and may be used by some extensions or themes.', 'easy-digital-downloads' ), edd_get_label_singular() ); ?></p>
	</div>
	<?php
}
add_action( 'edd_product_notes_meta_box_fields', 'edd_render_product_notes_field' );


/** Stats *********************************************************************/

/**
 * Render Stats Meta Box
 *
 * @since 1.0
 * @return void
 */
function edd_render_stats_meta_box() {
	$post_id = get_the_ID();

	if ( ! current_user_can( 'view_product_stats', $post_id ) ) {
		return;
	}

	$earnings = edd_get_download_earnings_stats( $post_id );
	$sales    = edd_get_download_sales_stats( $post_id );

	$sales_url = add_query_arg( array(
		'page'       => 'edd-payment-history',
		'product-id' => urlencode( $post_id )
	), edd_get_admin_base_url() );

	$earnings_report_url = edd_get_admin_url( array(
		'page'     => 'edd-reports',
		'view'     => 'downloads',
		'products' => $post_id,
	) );
	?>

	<p class="product-sales-stats">
		<span class="label"><?php _e( 'Sales:', 'easy-digital-downloads' ); ?></span>
		<span><a href="<?php echo esc_url( $sales_url ); ?>"><?php echo esc_html( $sales ); ?></a></span>
	</p>

	<p class="product-earnings-stats">
		<span class="label"><?php esc_html_e( 'Gross Revenue:', 'easy-digital-downloads' ); ?></span>
		<span><a href="<?php echo esc_url( $earnings_report_url ); ?>"><?php echo edd_currency_filter( edd_format_amount( $earnings ) ); ?></a></span>
	</p>

	<hr />

	<p class="file-download-log">
		<span><a href="<?php echo admin_url( 'edit.php?page=edd-tools&view=file_downloads&post_type=download&tab=logs&download=' . $post_id ); ?>"><?php _e( 'View File Download Log', 'easy-digital-downloads' ); ?></a></span><br/>
	</p>
<?php
	do_action('edd_stats_meta_box');
}

/**
 * Get the path of the Product Reviews plugin
 *
 * @since 2.9.20
 *
 * @return mixed|string
 */
function edd_reviews_location() {
	$possible_locations = array( 'edd-reviews/edd-reviews.php', 'EDD-Reviews/edd-reviews.php' );
	$reviews_location   = '';

	foreach ( $possible_locations as $location ) {

		if ( 0 !== validate_plugin( $location ) ) {
			continue;
		}
		$reviews_location = $location;
	}

	return $reviews_location;
}

/**
 * Outputs a metabox for the Product Reviews extension to show or activate it.
 *
 * @since 2.8
 * @return void
 */
function edd_render_review_status_metabox() {
	$reviews_location = edd_reviews_location();
	$is_promo_active  = edd_is_promo_active();

	ob_start();

	if ( ! empty( $reviews_location ) ) {
		$review_path  = '';
		$base_url     = wp_nonce_url( admin_url( 'plugins.php' ), 'activate-plugin_' . $reviews_location );
		$args         = array(
			'action'        => 'activate',
			'plugin'        => sanitize_text_field( $reviews_location ),
			'plugin_status' => 'all',
		);
		$activate_url = add_query_arg( $args, $base_url );
		?><p style="text-align: center;"><a href="<?php echo esc_url( $activate_url ); ?>" class="button-secondary"><?php _e( 'Activate Reviews', 'easy-digital-downloads' ); ?></a></p><?php

	} else {

		// Adjust UTM params based on state of promotion.
		if ( true === $is_promo_active ) {
			$args = array(
				'utm_source'   => 'download-metabox',
				'utm_medium'   => 'wp-admin',
				'utm_campaign' => 'bfcm2019',
				'utm_content'  => 'product-reviews-metabox-bfcm',
			);
		} else {
			$args = array(
				'utm_source'   => 'edit-download',
				'utm_medium'   => 'enable-reviews',
				'utm_campaign' => 'admin',
			);
		}

		$base_url = 'https://easydigitaldownloads.com/downloads/product-reviews';
		$url      = add_query_arg( $args, $base_url );
		?>
		<p>
			<?php
			// Translators: The %s represents the link to the Product Reviews extension.
			echo wp_kses_post( sprintf( __( 'Would you like to enable reviews for this product? Check out our <a target="_blank" href="%s">Product Reviews</a> extension.', 'easy-digital-downloads' ), esc_url( $url ) ) );
			?>
		</p>
		<?php
		// Add an additional note if a promotion is active.
		if ( true === $is_promo_active ) {
			?>
			<p>
				<?php echo wp_kses_post( __( 'Act now and <strong>SAVE 25%</strong> on your purchase. Sale ends <em>23:59 PM December 6th CST</em>. Use code <code>BFCM2019</code> at checkout.', 'easy-digital-downloads' ) ); ?>
			</p>
			<?php
		}
	}

	$rendered = ob_get_contents();
	ob_end_clean();

	echo wp_kses_post( $rendered );
}

/**
 * Outputs a metabox for promotional content.
 *
 * @since 2.9.20
 * @return void
 */
function edd_render_promo_metabox() {
	ob_start();

	// Build the main URL for the promotion.
	$args = array(
		'utm_source'   => 'download-metabox',
		'utm_medium'   => 'wp-admin',
		'utm_campaign' => 'bfcm2019',
		'utm_content'  => 'bfcm-metabox',
	);
	$url  = add_query_arg( $args, 'https://easydigitaldownloads.com/pricing/' );
	?>
	<p>
		<?php
		// Translators: The %s represents the link to the pricing page on the Easy Digital Downloads website.
		echo wp_kses_post( sprintf( __( 'Save 25&#37; on all Easy Digital Downloads purchases <strong>this week</strong>, including renewals and upgrades! Sale ends 23:59 PM December 6th CST. <a target="_blank" href="%s">Don\'t miss out</a>!', 'easy-digital-downloads' ), $url ) );
		?>
	</p>
	<?php
	$rendered = ob_get_contents();
	ob_end_clean();

	echo wp_kses_post( $rendered );
}

/**
 * Internal use only: This is to help with https://github.com/easydigitaldownloads/easy-digital-downloads/issues/2704
 *
 * This function takes any hooked functions for edd_download_price_table_head and re-registers them into the edd_download_price_table_row
 * action. It will also de-register any original table_row data, so that labels appear before their setting, then re-registers the table_row.
 *
 * @since 2.8
 *
 * @param $arg1
 * @param $arg2
 * @param $arg3
 *
 * @return void
 */
function edd_hijack_edd_download_price_table_head( $arg1, $arg2, $arg3 ) {
	global $wp_filter;

	$found_fields  = isset( $wp_filter['edd_download_price_table_row'] )  ? $wp_filter['edd_download_price_table_row']  : false;
	$found_headers = isset( $wp_filter['edd_download_price_table_head'] ) ? $wp_filter['edd_download_price_table_head'] : false;

	$re_register = array();

	if ( ! $found_fields && ! $found_headers ) {
		return;
	}

	foreach ( $found_fields->callbacks as $priority => $callbacks ) {
		if ( -1 === $priority ) {
			continue; // Skip our -1 priority so we don't break the interwebs
		}

		if ( is_object( $found_headers ) && property_exists( $found_headers, 'callbacks' ) && array_key_exists( $priority, $found_headers->callbacks ) ) {

			// De-register any row data.
			foreach ( $callbacks as $callback ) {
				$re_register[ $priority ][] = $callback;
				remove_action( 'edd_download_price_table_row', $callback['function'], $priority, $callback['accepted_args'] );
			}

			// Register any header data.
			foreach( $found_headers->callbacks[ $priority ] as $callback ) {
				if ( is_callable( $callback['function'] ) ) {
					add_action( 'edd_download_price_table_row', $callback['function'], $priority, 1 );
				}
			}
		}

	}

	// Now that we've re-registered our headers first...re-register the inputs
	foreach ( $re_register as $priority => $callbacks ) {
		foreach ( $callbacks as $callback ) {
			add_action( 'edd_download_price_table_row', $callback['function'], $priority, $callback['accepted_args'] );
		}
	}
}
add_action( 'edd_download_price_table_row', 'edd_hijack_edd_download_price_table_head', -1, 3 );
