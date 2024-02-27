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

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/** All Downloads *************************************************************/

/**
 * Register all the meta boxes for the Download custom post type
 *
 * @since 1.0
 * @return void
 */
function edd_add_download_meta_box() {
	$post_types = apply_filters( 'edd_download_metabox_post_types', array( 'download' ) );

	foreach ( $post_types as $post_type ) {

		/** Product Prices */
		add_meta_box(
			'edd_product_prices',
			sprintf(
				// translators: %1$s is the singular label.
				__( '%1$s Details', 'easy-digital-downloads' ),
				edd_get_label_singular(),
			),
			'edd_render_download_meta_box',
			$post_type,
			'normal',
			'high'
		);

		/** Product Files (and bundled products) */
		add_meta_box(
			'edd_product_files',
			sprintf(
				// translators: %1$s is the singular label.
				__( '%1$s Files', 'easy-digital-downloads' ),
				edd_get_label_singular(),
			),
			'edd_render_files_meta_box',
			$post_type,
			'normal',
			'high'
		);

		/** Product Settings */
		add_meta_box(
			'edd_product_settings',
			sprintf(
				// translators: %1$s is the singular label.
				__( '%1$s Settings', 'easy-digital-downloads' ),
				edd_get_label_singular(),
			),
			'edd_render_settings_meta_box',
			$post_type,
			'side',
			'default'
		);

		/** Product Notes */
		add_meta_box(
			'edd_product_notes',
			sprintf(
				// translators: %1$s is the singular label.
				__( '%1$s Instructions', 'easy-digital-downloads' ),
				edd_get_label_singular(),
			),
			'edd_render_product_notes_meta_box',
			$post_type,
			'normal',
			'high'
		);

		if ( current_user_can( 'view_product_stats', get_the_ID() ) ) {
			/** Product Stats */
			add_meta_box(
				'edd_product_stats',
				sprintf(
					// translators: %1$s is the singular label.
					__( '%1$s Stats', 'easy-digital-downloads' ),
					edd_get_label_singular(),
				),
				'edd_render_stats_meta_box',
				$post_type,
				'side',
				'high'
			);
		}
	}
}
add_action( 'add_meta_boxes', 'edd_add_download_meta_box', 9 );

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
		'_edd_bundled_products_conditions',
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
 * @param int $post_id Download (Post) ID.
 * @global array $post All the data of the the current post
 * @return void
 */
function edd_download_meta_box_save( $post_id, $post ) {
	if (
		! isset( $_POST['edd_download_meta_box_nonce'] ) ||
		! wp_verify_nonce( $_POST['edd_download_meta_box_nonce'], basename( __FILE__ ) )
	) {
		return;
	}

	if ( edd_doing_autosave() || edd_doing_ajax() || isset( $_REQUEST['bulk_edit'] ) ) {
		return;
	}

	if ( isset( $post->post_type ) && 'revision' === $post->post_type ) {
		return;
	}

	if ( ! current_user_can( 'edit_product', $post_id ) ) {
		return;
	}

	edd_download_meta_box_fields_save( $post_id, $post );
}

add_action( 'save_post', 'edd_download_meta_box_save', 10, 2 );


/**
 * Save post meta when the save_post action is called
 *
 * As a note, this entire function is reliant on the fact that the edd_download_metabox_fields() function
 * orders the _variable_pricing field before the edd_variable_prices field. If this is changed, this function
 * will fail to detect that variable pricing is enabled since the option hasn't been updated to enable it  yet.
 *
 * This shouldn't be an issue, but since there is a filter on these fields, it is possible for a developer to adjust the order
 * of the fields, causing problems, but we should fix that in a future refactor of this.
 *
 * @since 3.2
 * @param int $post_id Download (Post) ID.
 * @global WP_Post $post All the data of the the current post.
 * @return void
 */
function edd_download_meta_box_fields_save( $post_id, $post ) {
	if ( ! current_user_can( 'edit_product', $post_id ) ) {
		return;
	}

	// The default fields that get saved.
	$fields = edd_download_metabox_fields();

	foreach ( $fields as $field ) {

		if ( '_edd_default_price_id' === $field && edd_has_variable_prices( $post_id ) ) {
			if ( isset( $_POST[ $field ] ) ) {
				$use_value = ( ! empty( $_POST[ $field ] ) && is_numeric( $_POST[ $field ] ) ) || ( 0 === (int) $_POST[ $field ] );

				$new_default_price_id = $use_value ?
						intval( $_POST[ $field ] ) :
						1;
			} else {
				$new_default_price_id = 1;
			}

			update_post_meta( $post_id, $field, $new_default_price_id );
			continue;
		}

		// No value stored when product type is "default" ("0") for backwards compatibility.
		if ( '_edd_product_type' === $field && empty( $_POST[ $field ] ) ) {
			delete_post_meta( $post_id, '_edd_product_type' );
			continue;
		}

		// Skip saving bundled products if not set so that a previous value is not lost.
		if ( '_edd_bundled_products' === $field && ! isset( $_POST[ $field ] ) ) {
			continue;
		}

		$new = false;
		if ( ! empty( $_POST[ $field ] ) ) {
			$new = apply_filters( 'edd_metabox_save_' . $field, $_POST[ $field ] );
		}

		if ( ! empty( $new ) ) {
			update_post_meta( $post_id, $field, $new );
		} else {
			delete_post_meta( $post_id, $field );
		}
	}

	if ( edd_has_variable_prices( $post_id ) ) {
		$lowest = edd_get_lowest_price_option( $post_id );
		update_post_meta( $post_id, 'edd_price', $lowest );
	}

	do_action( 'edd_save_download', $post_id, $post );
}


/**
 * Sanitize bundled products on save
 *
 * Ensures a user doesn't try and include a product's ID in the products bundled with that product
 *
 * @since       1.6
 *
 * @param array $products Array of product IDs.
 * @return array
 */
function edd_sanitize_bundled_products_save( $products = array() ) {

	$products = array_map(
		function ( $value ) {
			return preg_replace( '/[^0-9_]/', '', $value );
		},
		(array) $products
	);

	foreach ( $products as $key => $value ) {
		$underscore_pos = strpos( $value, '_' );
		if ( is_numeric( $underscore_pos ) ) {
			$product_id = substr( $value, 0, $underscore_pos );
		} else {
			$product_id = $value;
		}

		if ( in_array( intval( $product_id ), array( 0, get_the_ID() ), true ) ) {
			unset( $products[ $key ] );
		}
	}

	$products = array_unique( $products );

	return ! empty( $products ) ? array_combine(
		range( 1, count( $products ) ),
		array_values( $products )
	) : false;
}
add_filter( 'edd_metabox_save__edd_bundled_products', 'edd_sanitize_bundled_products_save' );

/**
 * Sanitize bundled products conditions on save
 *
 * @since 3.1
 *
 * @param array $bundled_products_conditions Array of bundled products conditions.
 * @return array
 */
function edd_sanitize_bundled_products_conditions_save( $bundled_products_conditions = array() ) {
	return ! empty( $bundled_products_conditions ) ? array_combine(
		range( 1, count( $bundled_products_conditions ) ),
		array_values( $bundled_products_conditions )
	) : false;
}
add_filter( 'edd_metabox_save__edd_bundled_products_conditions', 'edd_sanitize_bundled_products_conditions_save' );

/**
 * Don't save blank rows.
 *
 * When saving, check the price and file table for blank rows.
 * If the name of the price or file is empty, that row should not
 * be saved.
 *
 * @since 1.2.2
 * @param array $updated_meta Array of all the meta values.
 * @return array $new New meta value with empty keys removed
 */
function edd_metabox_save_check_blank_rows( $updated_meta ) {
	foreach ( $updated_meta as $key => $value ) {
		if ( empty( $value['name'] ) && empty( $value['amount'] ) && empty( $value['file'] ) ) {
			unset( $updated_meta[ $key ] );
		}
	}

	return $updated_meta;
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
	do_action( 'edd_meta_box_files_fields', get_the_ID(), '' );
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
 * @param int $post_id Download (Post) ID.
 */
function edd_render_price_field( $post_id ) {
	if ( is_numeric( $post_id ) && ! current_user_can( 'edit_product', $post_id ) ) {
		return;
	}

	if ( is_null( $post_id ) && ! current_user_can( 'edit_products' ) ) {
		return;
	}

	$price              = edd_get_download_price( $post_id );
	$variable_pricing   = edd_has_variable_prices( $post_id );
	$prices             = edd_get_variable_prices( $post_id );
	$single_option_mode = edd_single_price_option_mode( $post_id );

	$price_display     = $variable_pricing ? ' style="display:none;"' : '';
	$variable_display  = $variable_pricing ? '' : ' style="display:none;"';
	$currency_position = edd_get_option( 'currency_position', 'before' );
	?>
	<p>
		<strong><?php echo apply_filters( 'edd_price_options_heading', __( 'Pricing Options:', 'easy-digital-downloads' ) ); ?></strong>
	</p>

	<div id="edd_variable_pricing_control" class="edd-form-group">
		<div class="edd-form-group__control">
			<input type="checkbox" class="edd-form-group__input" name="_variable_pricing" id="edd_variable_pricing" value="1" <?php checked( 1, $variable_pricing ); ?> />
			<label for="edd_variable_pricing">
				<?php
				echo esc_html(
					apply_filters( 'edd_variable_pricing_toggle_text', __( 'Enable variable pricing', 'easy-digital-downloads' ) )
				);
				?>
			</label>
		</div>
	</div>

	<div id="edd_regular_price_field" class="edd-form-group edd_pricing_fields" <?php echo $price_display; ?>>
		<label for="edd_price" class="edd-form-group__label screen-reader-text">
			<?php esc_html_e( 'Price', 'easy-digital-downloads' ); ?>
		</label>
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
				<?php
				echo EDD()->html->checkbox(
					array(
						'name'    => '_edd_price_options_mode',
						'current' => $single_option_mode,
						'class'   => 'edd-form-group__input',
					)
				);
				?>
				<label for="_edd_price_options_mode">
					<?php
					echo esc_html(
						apply_filters(
							'edd_multi_option_purchase_text',
							__( 'Enable multi-option purchase mode. Allows multiple price options to be added to your cart at once', 'easy-digital-downloads' )
						)
					);
					?>
				</label>
			</div>
		</div>
		<div id="edd_price_fields" class="edd_meta_table_wrap">
			<div class="widefat edd_repeatable_table">

				<div class="edd-price-option-fields edd-repeatables-wrap">
					<?php
					if ( ! empty( $prices ) ) :

						foreach ( $prices as $key => $value ) :
							$name   = ( isset( $value['name'] ) && ! empty( $value['name'] ) ) ? $value['name'] : '';
							$index  = ( isset( $value['index'] ) && '' !== $value['index'] ) ? $value['index'] : $key;
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
					<button class="button-secondary edd_add_repeatable">
						<?php _e( 'Add New Price', 'easy-digital-downloads' ); ?>
					</button>
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
 * @param int   $key   The cart item key.
 * @param array $args  Array of arguments for the price row.
 * @param int   $post_id The ID of the download.
 */
function edd_render_price_row( $key, $args, $post_id, $index ) {
	global $wp_filter;

	if ( is_numeric( $post_id ) && ! current_user_can( 'edit_product', $post_id ) ) {
		return;
	}

	if ( is_null( $post_id ) && ! current_user_can( 'edit_products' ) ) {
		return;
	}

	$defaults = array(
		'name'   => null,
		'amount' => null,
	);

	$args = wp_parse_args( $args, $defaults );

	$default_price_id     = edd_get_default_variable_price( $post_id );
	$currency_position    = edd_get_option( 'currency_position', 'before' );
	$custom_price_options = isset( $wp_filter['edd_download_price_option_row'] ) ? true : false;
	?>
	<div class="edd-repeatable-row-header edd-draghandle-anchor">
		<span class="edd-repeatable-row-title" title="<?php _e( 'Click and drag to re-order price options', 'easy-digital-downloads' ); ?>">
			<?php
			printf(
				// translators: %s is the price ID.
				__( 'Price ID: %s', 'easy-digital-downloads' ),
				'<span class="edd_price_id">' . esc_html( $key ) . '</span>'
			);
			?>
			<input type="hidden" name="edd_variable_prices[<?php echo esc_attr( $key ); ?>][index]" class="edd_repeatable_index" value="<?php echo esc_attr( $index ); ?>"/>
		</span>
		<?php
		$actions = array();
		if ( $custom_price_options ) {
			$actions['show_advanced'] = sprintf(
				'<a href="#" class="toggle-custom-price-option-section">%s</a>',
				__( 'Show advanced settings', 'easy-digital-downloads' )
			);
		}

		$actions['remove'] = sprintf(
			// translators: %1$s is the remove link, %2$s is the screen reader text.
			'<a class="edd-remove-row edd-delete" data-type="price">%1$s<span class="screen-reader-text">%2$s</span></a>',
			__( 'Remove', 'easy-digital-downloads' ),
			sprintf(
				// translators: %s is the price ID.
				__( 'Remove price option %s', 'easy-digital-downloads' ),
				esc_html( $key )
			)
		);
		?>
		<span class="edd-repeatable-row-actions">
			<?php echo implode( '&nbsp;&#124;&nbsp;', $actions ); ?>
		</span>
	</div>

	<div class="edd-repeatable-row-standard-fields">

		<div class="edd-form-group edd-option-name">
			<label for="edd_variable_prices-<?php echo esc_attr( $key ); ?>-name" class="edd-form-group__label edd-repeatable-row-setting-label">
				<?php esc_html_e( 'Option Name', 'easy-digital-downloads' ); ?>
			</label>
			<div class="edd-form-group__control">
			<?php
			echo EDD()->html->text(
				array(
					'name'        => 'edd_variable_prices[' . $key . '][name]',
					'id'          => 'edd_variable_prices-' . $key . '-name',
					'value'       => esc_attr( $args['name'] ),
					'placeholder' => __( 'Option Name', 'easy-digital-downloads' ),
					'class'       => 'edd_variable_prices_name large-text',
				)
			);
			?>
			</div>
		</div>

		<div class="edd-form-group edd-option-price">
			<label for="edd_variable_prices-<?php echo esc_attr( $key ); ?>-amount" class="edd-repeatable-row-setting-label">
				<?php esc_html_e( 'Price', 'easy-digital-downloads' ); ?>
			</label>
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
			<label for="edd_default_price_id_<?php echo esc_attr( $key ); ?>" class="edd-repeatable-row-setting-label">
				<?php esc_html_e( 'Default', 'easy-digital-downloads' ); ?>
			</label>
			<?php
			printf(
				'<input type="radio" %1$s class="edd_repeatable_default_input" name="_edd_default_price_id" id="%2$s" value="%3$d" />',
				checked( $default_price_id, $key, false ),
				'edd_default_price_id_' . esc_attr( $key ),
				esc_attr( $key )
			);
			?>
			<span class="screen-reader-text">
				<?php
				// translators: %s is the price ID.
				printf( __( 'Set ID %s as default price', 'easy-digital-downloads' ), $key );
				?>
			</span>
			</div>
		</div>

	</div>

	<?php
	if ( $custom_price_options ) {
		?>

		<div class="edd-custom-price-option-sections-wrap">
			<div class="edd-custom-price-option-sections">
				<?php
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
	if ( ! current_user_can( 'edit_product', $post_id ) ) {
		return;
	}

	$types = edd_get_download_types();
	$type  = edd_get_download_type( $post_id );
	ksort( $types );
	?>
	<div class="edd-form-group">
		<label for="_edd_product_type" class="edd-form-group__label">
			<?php
			echo esc_html(
				apply_filters( 'edd_product_type_options_heading', __( 'Product Type Options:', 'easy-digital-downloads' ) )
			);
			?>
		</label>
		<div class="edd-form-group__control">
			<?php
			echo EDD()->html->select(
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
		</div>
		<p class="edd-form-group__help description">
			<?php esc_html_e( 'Sell this item as a single product with download files, or select a custom product type with different options, which may not necessarily include download files.', 'easy-digital-downloads' ); ?>
		</p>
	</div>
	<?php
}
add_action( 'edd_meta_box_price_fields', 'edd_render_product_type_field', 5 );

/**
 * Renders the bundled products fields.
 *
 * @since 1.6
 *
 * @param int    $post_id Download (Post) ID.
 * @param string $type    The download type (used in ajax requests since 3.2.0).
 */
function edd_render_products_field( $post_id, $type = '' ) {
	$download = new EDD_Download( $post_id );
	if ( empty( $type ) ) {
		$type = $download->get_type();
	}
	if ( 'bundle' !== $type ) {
		return;
	}
	include 'views/metabox-bundled-products.php';
}
add_action( 'edd_meta_box_files_fields', 'edd_render_products_field', 10, 2 );

/**
 * File Downloads section.
 *
 * Outputs a table of all current files. Extensions can add column heads to the table
 * via the `edd_download_file_table_head` hook, and actual columns via
 * `edd_download_file_table_row`
 *
 * @since 1.0
 * @see edd_render_file_row()
 * @param int    $post_id Download (Post) ID.
 * @param string $type    The download type (used in ajax requests since 3.2.0).
 * @return void
 */
function edd_render_files_field( $post_id = 0, $type = '' ) {
	if ( is_numeric( $post_id ) && ! current_user_can( 'edit_product', $post_id ) ) {
		return;
	}

	if ( is_null( $post_id ) && ! current_user_can( 'edit_products' ) ) {
		return;
	}

	if ( empty( $type ) ) {
		$type = edd_get_download_type( $post_id );
	}

	if ( 'bundle' === $type ) {
		return;
	}

	include 'views/metabox-files.php';
}
add_action( 'edd_meta_box_files_fields', 'edd_render_files_field', 20, 2 );


/**
 * Individual file row.
 *
 * Used to output a table row for each file associated with a download.
 * Can be called directly, or attached to an action.
 *
 * @since 1.2.2
 * @param string $key Array key.
 * @param array  $args Array of all the arguments passed to the function.
 * @param int    $post_id Download (Post) ID.
 * @return void
 */
function edd_render_file_row( $key, $args, $post_id, $index ) {

	$args = wp_parse_args(
		$args,
		array(
			'name'           => null,
			'file'           => null,
			'condition'      => null,
			'attachment_id'  => null,
			'thumbnail_size' => null,
		)
	);

	$prices           = edd_get_variable_prices( $post_id );
	$variable_pricing = edd_has_variable_prices( $post_id );
	$variable_display = $variable_pricing ? '' : ' style="display:none;"';
	$variable_class   = $variable_pricing ? ' has-variable-pricing' : '';
	?>

	<div class="edd-repeatable-row-header edd-draghandle-anchor">
		<span class="edd-repeatable-row-title" title="<?php _e( 'Click and drag to re-order files', 'easy-digital-downloads' ); ?>">
			<?php
			printf(
				// translators: %1$s is the singular label, %2$s is the file ID.
				esc_html__( '%1$s file ID: %2$s', 'easy-digital-downloads' ),
				esc_html( edd_get_label_singular() ),
				'<span class="edd_file_id">' . esc_html( $key ) . '</span>'
			);

			printf(
				'<input type="hidden" name="edd_download_files[%1$s][index]" class="edd_repeatable_index" value="%2$s"/>',
				esc_attr( $key ),
				esc_attr( $index )
			);
			?>
		</span>
		<span class="edd-repeatable-row-actions">
			<a class="edd-remove-row edd-delete" data-type="file">
				<?php esc_html_e( 'Remove', 'easy-digital-downloads' ); ?>
				<span class="screen-reader-text">
					<?php
					// translators: %s is the file ID.
					printf( esc_html__( 'Remove file %s', 'easy-digital-downloads' ), esc_html( $key ) );
					?>
				</span>
			</a>
		</span>
	</div>

	<div class="edd-repeatable-row-standard-fields<?php echo esc_attr( $variable_class ); ?>">
		<div class="edd-form-group edd-file-name">
			<label for="edd_download_files-<?php echo esc_attr( $key ); ?>-name" class="edd-form-group__label edd-repeatable-row-setting-label">
				<?php esc_html_e( 'File Name', 'easy-digital-downloads' ); ?>
			</label>
			<div class="edd-form-group__control">
				<input type="hidden" name="edd_download_files[<?php echo absint( $key ); ?>][attachment_id]" class="edd_repeatable_attachment_id_field" value="<?php echo esc_attr( absint( $args['attachment_id'] ) ); ?>"/>
				<input type="hidden" name="edd_download_files[<?php echo absint( $key ); ?>][thumbnail_size]" class="edd_repeatable_thumbnail_size_field" value="<?php echo esc_attr( $args['thumbnail_size'] ); ?>"/>
				<?php
				echo EDD()->html->text(
					array(
						'name'        => 'edd_download_files[' . $key . '][name]',
						'id'          => 'edd_download_files-' . $key . '-name',
						'value'       => $args['name'],
						'placeholder' => __( 'My Neat File', 'easy-digital-downloads' ),
						'class'       => 'edd-form-group__input edd_repeatable_name_field large-text',
					)
				);
				?>
			</div>
		</div>

		<div class="edd-form-group edd-file-url">
			<label for="edd_download_files-<?php echo esc_attr( $key ); ?>-file" class="edd-form-group__label edd-repeatable-row-setting-label">
				<?php esc_html_e( 'File URL', 'easy-digital-downloads' ); ?>
			</label>
			<div class="edd-form-group__control edd_repeatable_upload_field_container">
				<?php
				echo EDD()->html->text(
					array(
						'name'        => 'edd_download_files[' . $key . '][file]',
						'id'          => 'edd_download_files-' . $key . '-file',
						'value'       => $args['file'],
						'placeholder' => __( 'Enter, upload, choose from Media Library', 'easy-digital-downloads' ),
						'class'       => 'edd-form-group__input edd_repeatable_upload_field edd_upload_field large-text',
					)
				);
				?>

				<span class="edd_upload_file">
					<button data-uploader-title="<?php esc_attr_e( 'Select Files', 'easy-digital-downloads' ); ?>" data-uploader-button-text="<?php esc_attr_e( 'Select', 'easy-digital-downloads' ); ?>" class="edd_upload_file_button" onclick="return false;">
						<span class="dashicons dashicons-admin-links"></span>
						<span class="screen-reader-text"><?php esc_html_e( 'Select Files', 'easy-digital-downloads' ); ?></span>
				</button>
				</span>
			</div>
		</div>

		<div class="edd-form-group edd-file-assignment pricing"<?php echo $variable_display; ?>>

			<label for="edd_download_files_<?php echo esc_attr( $key ); ?>_condition" class="edd-form-group__label edd-repeatable-row-setting-label">
				<?php
				esc_html_e( 'Price Assignment', 'easy-digital-downloads' );
				$tooltip = new EDD\HTML\Tooltip(
					array(
						'title'   => __( 'Price Assignment', 'easy-digital-downloads' ),
						'content' => __( 'With variable pricing enabled, you can choose to allow certain price variations access to specific files, or allow all price variations to access a file.', 'easy-digital-downloads' ),
					)
				);
				$tooltip->output();
				?>
			</label>
			<div class="edd-form-group__control">
			<?php
				$options = array();

			if ( ! empty( $prices ) ) {
				foreach ( $prices as $price_key => $price ) {
					$options[ $price_key ] = $prices[ $price_key ]['name'];
				}
			}

				echo EDD()->html->select(
					array(
						'name'             => 'edd_download_files[' . $key . '][condition]',
						'id'               => 'edd_download_files-' . $key . '-condition',
						'class'            => 'edd-form-group__input edd_repeatable_condition_field',
						'options'          => $options,
						'selected'         => $args['condition'],
						'show_option_none' => false,
					)
				);
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
 * @param  array $strings Array of default strings for media manager.
 * @return array          The altered array of strings for media manager
 */
function edd_download_media_strings( $strings ) {
	global $post;

	if ( empty( $post ) || ( 'download' !== $post->post_type ) ) {
		return $strings;
	}

	$downloads_object = get_post_type_object( 'download' );
	$labels           = $downloads_object->labels;

	// translators: %s is the singular label for downloads, in lowercase form.
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
 * @param int $post_id Download (Post) ID.
 * @return void
 */
function edd_render_refund_row( $post_id ) {

	// Bail if user cannot manage shop settings.
	if ( ! current_user_can( 'manage_shop_settings' ) ) {
		return;
	}

	$types             = edd_get_refundability_types();
	$global_ability    = edd_get_option( 'refundability', 'refundable' );
	$global_window     = edd_get_option( 'refund_window', 30 );
	$edd_refund_window = edd_get_download_refund_window( $post_id );
	?>

	<div class="edd-form-group edd-product-options-wrapper">
		<div class="edd-product-options__title">
			<?php
			esc_html_e( 'Refunds', 'easy-digital-downloads' );
			$tooltip = new EDD\HTML\Tooltip(
				array(
					'title'   => __( 'Refundable', 'easy-digital-downloads' ),
					'content' => __( 'Allow or disallow refunds for this specific product. When allowed, the refund window will be used on all future purchases.<br /><strong>Refund Window</strong>: Limit the number of days this product can be refunded after purchasing.', 'easy-digital-downloads' ),
				)
			);
			$tooltip->output();
			?>
		</div>

		<div class="edd-form-group__control">
			<label for="edd_refundability" class="edd-form-group__label">
				<?php esc_html_e( 'Refund Status', 'easy-digital-downloads' ); ?>
			</label>
			<?php
			echo EDD()->html->select(
				array(
					'name'             => '_edd_refundability',
					'id'               => 'edd_refundability',
					'class'            => 'edd-form-group__input',
					'options'          => array_merge(
					// Manually define a "none" option to set a blank value, vs. -1.
						array(
							'' => sprintf(
							/* translators: Default refund status */
								esc_html_x( 'Default (%1$s)', 'Download refund status', 'easy-digital-downloads' ),
								ucwords( $global_ability )
							),
						),
						$types
					),
					// Use the direct meta value to avoid falling back to default.
					'selected'         => get_post_meta( $post_id, '_edd_refundability', true ),
					'show_option_all'  => '',
					'show_option_none' => false,
				)
			);
			?>
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
 * @param int $post_id Download (Post) ID.
 * @return void
 */
function edd_render_download_limit_row( $post_id ) {
	if ( ! current_user_can( 'manage_shop_settings' ) ) {
		return;
	}

	// Get the download limit directly from the post meta so that the global is not involved.
	$edd_download_limit = get_post_meta( $post_id, '_edd_download_limit', true );

	// Determine whether to show the row or not.
	$display = 'default' === edd_get_download_type( $post_id ) || ! empty( edd_get_download_files( $post_id ) ) ?
		'' :
		' style="display: none;"';
	?>
	<div class="edd-form-group edd-product-options-wrapper" id="edd_download_limit_wrap"<?php echo $display; ?>>
		<div class="edd-form-group__control">
			<label class="edd-form-group__label edd-product-options__title" for="edd_download_limit">
				<?php
				esc_html_e( 'File Download Limit', 'easy-digital-downloads' );
				$tooltip = new EDD\HTML\Tooltip(
					array(
						'title'   => __( 'File Download Limit', 'easy-digital-downloads' ),
						'content' => __( 'Limit the number of times a customer who purchased this product can access their download links.', 'easy-digital-downloads' ),
					)
				);
				$tooltip->output();
				?>
			</label>
			<input class="edd-form-group__input small-text" name="_edd_download_limit" id="edd_download_limit" type="number" min="0" max="5000" step="1" value="<?php echo esc_attr( $edd_download_limit ); ?>" />
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
 * @since 2.8.12 Fixed miss-spelling in function name. See https://github.com/easydigitaldownloads/easy-digital-downloads/issues/5101
 * @param int $post_id Download (Post) ID.
 * @return void
 */
function edd_render_down_tax_options( $post_id = 0 ) {

	// Bail if current user cannot view shop reports, or taxes are disabled.
	if ( ! current_user_can( 'view_shop_reports' ) || ! edd_use_taxes() ) {
		return;
	}

	$exclusive = edd_download_is_tax_exclusive( $post_id );
	?>

	<div class="edd-form-group edd-product-options-wrapper">
		<div class="edd-product-options__title">
			<?php
			esc_html_e( 'Taxability', 'easy-digital-downloads' );
			$tooltip = new EDD\HTML\Tooltip(
				array(
					'title'   => __( 'Taxability', 'easy-digital-downloads' ),
					'content' => __( 'When taxes are enabled, all products are taxable by default. Check this box to mark this product as non-taxable.', 'easy-digital-downloads' ),
				)
			);
			$tooltip->output();
			?>
		</div>
		<div class="edd-form-group__control">
			<?php
			echo EDD()->html->checkbox(
				array(
					'name'    => '_edd_download_tax_exclusive',
					'id'      => '_edd_download_tax_exclusive',
					'current' => $exclusive,
					'class'   => 'edd-form-group__input',
				)
			);
			?>
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
 * @param int $post_id Download (Post) ID.
 * @return void
 */
function edd_render_download_quantity_option( $post_id = 0 ) {
	if ( ! current_user_can( 'manage_shop_settings' ) || ! edd_item_quantities_enabled() ) {
		return;
	}

	$disabled = edd_download_quantities_disabled( $post_id );
	?>

	<div class="edd-form-group edd-product-options-wrapper">
		<div class="edd-product-options__title">
			<?php
			esc_html_e( 'Item Quantities', 'easy-digital-downloads' );
			$tooltip = new EDD\HTML\Tooltip(
				array(
					'title'   => __( 'Item Quantities', 'easy-digital-downloads' ),
					'content' => __( 'If disabled, customers will not be provided an option to change the number they wish to purchase.', 'easy-digital-downloads' ),
				)
			);
			$tooltip->output();
			?>
		</div>
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

	if ( ! current_user_can( 'edit_product', get_the_ID() ) ) {
		return;
	}

	$purchase_text = edd_get_option( 'add_to_cart_text', __( 'Purchase', 'easy-digital-downloads' ) );
	$style         = edd_get_option( 'button_style', 'button' );
	$color         = edd_get_button_color_class();
	$shortcode     = sprintf(
		// translators: %1$d is the download ID, %2$s is the purchase text, %3$s is the button style, %4$s is the button color.
		'[purchase_link id="%1$d" text="%2$s" style="%3$s" color="%4$s"]',
		absint( get_the_ID() ),
		esc_html( $purchase_text ),
		$style,
		$color
	);
	?>

	<div class="edd-form-group edd-product-options-wrapper">
		<div class="edd-form-group__control">
			<label class="edd-form-group__label edd-product-options__title" for="edd-purchase-shortcode">
				<?php
				esc_html_e( 'Purchase Shortcode', 'easy-digital-downloads' );
				$tooltip = new EDD\HTML\Tooltip(
					array(
						'title'   => __( 'Purchase Shortcode', 'easy-digital-downloads' ),
						'content' => __( 'Use this shortcode to output a purchase link for this product in the location of your choosing.', 'easy-digital-downloads' ),
					)
				);
				$tooltip->output();
				?>
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
 * @param int $post_id Download (Post) ID.
 * @return void
 */
function edd_render_accounting_options( $post_id ) {
	if ( ! current_user_can( 'edit_product', $post_id ) ) {
		return;
	}

	if ( ! edd_use_skus() ) {
		return;
	}

	$edd_sku = get_post_meta( $post_id, 'edd_sku', true );
	?>

	<div class="edd-form-group edd-product-options-wrapper">
		<div class="edd-product-options__title">
			<?php
			esc_html_e( 'Accounting Options', 'easy-digital-downloads' );
			$tooltip = new EDD\HTML\Tooltip(
				array(
					'title'   => __( 'SKU', 'easy-digital-downloads' ),
					'content' => __( 'If an SKU is entered for this product, it will be shown on the purchase receipt and exported purchase histories.', 'easy-digital-downloads' ),
				)
			);
			$tooltip->output();
			?>
		</div>
		<div class="edd-form-group__control">
			<label class="edd-form-group__label" for="edd_sku">
				<?php esc_html_e( 'Enter an SKU for this product.', 'easy-digital-downloads' ); ?>
			</label>
			<?php
			echo EDD()->html->text(
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
 * @param int $post_id Download (Post) ID.
 * @return void
 */
function edd_render_disable_button( $post_id ) {
	if ( ! current_user_can( 'edit_product', $post_id ) ) {
		return;
	}

	$supports_buy_now = edd_shop_supports_buy_now();
	$hide_button      = get_post_meta( $post_id, '_edd_hide_purchase_link', true ) ? 1 : 0;
	$behavior         = get_post_meta( $post_id, '_edd_button_behavior', true );
	$content          = __( 'By default, the purchase buttons will be displayed at the bottom of the download, when disabled you will need to use the Purchase link shortcode below to output the ability to buy the product where you prefer.', 'easy-digital-downloads' );
	$content         .= '<br /><br />';
	if ( $supports_buy_now ) {
		$content .= __( '<strong>Purchase button behavior</strong>: Add to Cart buttons follow a traditional eCommerce flow. A Buy Now button bypasses most of the process, taking the customer directly from button click to payment, greatly speeding up the process of buying the product.', 'easy-digital-downloads' );
	} else {
		$content .= __( '<strong>Purchase button behavior</strong>: Add to Cart buttons follow a traditional eCommerce flow. Buy Now buttons are only available for stores that have a single supported gateway active and that do not use taxes.', 'easy-digital-downloads' );
	}
	?>

	<div class="edd-form-group edd-product-options-wrapper">
		<div class="edd-product-options__title">
			<?php
			esc_html_e( 'Button Options', 'easy-digital-downloads' );
			$tooltip = new EDD\HTML\Tooltip(
				array(
					'title'   => __( 'Hide purchase buttons', 'easy-digital-downloads' ),
					'content' => $content,
				)
			);
			$tooltip->output();
			?>
		</div>
		<div class="edd-form-group__control">
			<?php
			echo EDD()->html->checkbox(
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
 * @param int $post_id Download (Post) ID.
 * @return void
 */
function edd_render_product_notes_field( $post_id ) {
	// Check if the user can edit this specific download ID (post ID).
	if ( ! current_user_can( 'edit_product', $post_id ) ) {
		return;
	}

	$product_notes = edd_get_product_notes( $post_id );
	?>
	<div class="edd-form-group">
		<div class="edd-form-group__control">
			<label for="edd_product_notes_field" class="edd-form-group__label screen-reader-text">]
				<?php esc_html_e( 'Download Instructions', 'easy-digital-downloads' ); ?>
			</label>
			<textarea rows="1" cols="40" class="edd-form-group__input large-textarea" name="edd_product_notes" id="edd_product_notes_field"><?php echo esc_textarea( $product_notes ); ?></textarea>
		</div>
		<p>
			<?php
			printf(
				// translators: %s is the singular label.
				esc_html__( 'Special instructions for this %s. These will be added to the purchase receipt, and may be used by some extensions or themes.', 'easy-digital-downloads' ),
				edd_get_label_singular()
			);
			?>
		</p>
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

	$sales_url = add_query_arg(
		array(
			'page'       => 'edd-payment-history',
			'product-id' => urlencode( $post_id ),
		),
		edd_get_admin_base_url()
	);

	$earnings_report_url = edd_get_admin_url(
		array(
			'page'     => 'edd-reports',
			'view'     => 'downloads',
			'products' => absint( $post_id ),
		)
	);
	?>

	<p class="product-sales-stats">
		<span class="label"><?php esc_html_e( 'Net Sales:', 'easy-digital-downloads' ); ?></span>
		<span><a href="<?php echo esc_url( $sales_url ); ?>"><?php echo esc_html( $sales ); ?></a></span>
	</p>

	<p class="product-earnings-stats">
		<span class="label"><?php esc_html_e( 'Net Revenue:', 'easy-digital-downloads' ); ?></span>
		<span>
			<a href="<?php echo esc_url( $earnings_report_url ); ?>">
				<?php echo edd_currency_filter( edd_format_amount( $earnings ) ); ?>
			</a>
		</span>
	</p>

	<hr />

	<p class="file-download-log">
		<?php
		$url = edd_get_admin_url(
			array(
				'page'     => 'edd-tools',
				'view'     => 'file_downloads',
				'tab'      => 'logs',
				'download' => absint( $post_id ),
			)
		);
		?>
		<span>
			<a href="<?php echo esc_url( $url ); ?>">
				<?php esc_html_e( 'View File Download Log', 'easy-digital-downloads' ); ?>
			</a>
		</span>
		<br/>
	</p>
	<?php
	do_action( 'edd_stats_meta_box' );
}
