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
		'edd_feature_download',
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
	$nonce = filter_input( INPUT_POST, 'edd_download_meta_box_nonce', FILTER_SANITIZE_SPECIAL_CHARS );
	if ( ! wp_verify_nonce( $nonce, 'edd_metabox_download_details' ) ) {
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

	$prices              = edd_get_variable_prices( $post_id );
	$has_variable_prices = edd_has_variable_prices( $post_id );
	$file_row_classes    = array( 'edd-repeatable-row-standard-fields' );
	if ( $has_variable_prices ) {
		$file_row_classes[] = 'has-variable-pricing';
	}
	?>

	<div class="edd-repeatable-row-header">
		<span class="edd-repeatable-row-title">
			<?php
			printf(
				/* translators: %1$s is the singular label, %2$s is the file ID. */
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
			<div class="edd__handle-actions-order hide-if-no-js">
				<button type="button" class="edd__handle-actions edd__handle-actions-order--higher" aria-disabled="false" aria-describedby="edd-download-file-<?php echo esc_attr( $index ); ?>-edd__handle-actions-order--higher-description">
					<span class="screen-reader-text"><?php esc_html_e( 'Move up', 'easy-digital-downloads' ); ?></span>
					<span class="dashicons dashicons-arrow-up-alt2" aria-hidden="true"></span>
				</button>
				<span class="hidden" id="edd-download-file-<?php echo esc_attr( $index ); ?>-edd__handle-actions-order--higher-description">
						<?php
						/* translators: %s: Download singular label */
						printf( esc_html__( 'Move %s up', 'easy-digital-downloads' ), edd_get_label_singular() );
						?>
				</span>
				<button type="button" class="edd__handle-actions edd__handle-actions-order--lower" aria-disabled="false" aria-describedby="edd-download-file-<?php echo esc_attr( $index ); ?>-edd__handle-actions-order--lower-description">
					<span class="screen-reader-text"><?php esc_html_e( 'Move down', 'easy-digital-downloads' ); ?></span>
					<span class="dashicons dashicons-arrow-down-alt2" aria-hidden="true"></span>
				</button>
				<span class="hidden" id="edd-download-file-<?php echo esc_attr( $index ); ?>-edd__handle-actions-order--lower-description">
						<?php
						/* translators: %s: Download singular label */
						printf( esc_html__( 'Move %s down', 'easy-digital-downloads' ), edd_get_label_singular() );
						?>
				</span>
			</div>
			<a class="edd-remove-row edd-delete" data-type="file">
				<?php esc_html_e( 'Remove', 'easy-digital-downloads' ); ?>
				<span class="screen-reader-text">
					<?php
					/* translators: %s: file ID. */
					printf( esc_html__( 'Remove file %s', 'easy-digital-downloads' ), esc_html( $key ) );
					?>
				</span>
			</a>
		</span>
	</div>

	<div class="<?php echo esc_attr( implode( ' ', $file_row_classes ) ); ?>">
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
					<button type="button" data-uploader-title="<?php esc_attr_e( 'Select Files', 'easy-digital-downloads' ); ?>" data-uploader-button-text="<?php esc_attr_e( 'Select', 'easy-digital-downloads' ); ?>" class="edd_upload_file_button" onclick="return false;">
						<span class="dashicons dashicons-admin-links"></span>
						<span class="screen-reader-text"><?php esc_html_e( 'Select Files', 'easy-digital-downloads' ); ?></span>
				</button>
				</span>
			</div>
		</div>

		<?php
		$file_assignment_classes = array(
			'edd-form-group',
			'edd-file-assignment',
			'pricing',
		);
		if ( ! $has_variable_prices ) {
			$file_assignment_classes[] = 'edd-hidden';
		}
		?>
		<div class="<?php echo esc_attr( implode( ' ', $file_assignment_classes ) ); ?>" data-edd-requires-variable-pricing="true">

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

				$select = new \EDD\HTML\Select(
					array(
						'name'             => 'edd_download_files[' . $key . '][condition]',
						'id'               => 'edd_download_files-' . $key . '-condition',
						'class'            => 'edd-form-group__input edd_repeatable_condition_field',
						'options'          => $options,
						'selected'         => $args['condition'],
						'show_option_none' => false,
					)
				);
				$select->output();
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

	/* translators: %s: Download singular label, in lowercase form. */
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
function edd_render_refund_row( $post_id, $download = null ) {

	// Bail if user cannot manage shop settings.
	if ( ! current_user_can( 'manage_shop_settings' ) ) {
		return;
	}

	$types             = edd_get_refundability_types();
	$global_ability    = edd_get_option( 'refundability', 'refundable' );
	$global_window     = edd_get_option( 'refund_window', 30 );
	$edd_refund_window = $download ? $download->get_refund_window() : false;
	?>

	<div class="edd-form-group">
		<label for="edd_refundability" class="edd-form-group__label">
			<?php
			esc_html_e( 'Refund Status', 'easy-digital-downloads' );
			$tooltip = new EDD\HTML\Tooltip(
				array(
					'title'   => __( 'Refundable', 'easy-digital-downloads' ),
					'content' => __( 'Allow or disallow refunds for this specific product. When allowed, the refund window will be used on all future purchases.<br /><strong>Refund Window</strong>: Limit the number of days this product can be refunded after purchasing.', 'easy-digital-downloads' ),
				)
			);
			$tooltip->output();
			?>
		</label>
		<div class="edd-form-group__control">
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
								/* translators: %s: Default refund status */
								esc_html_x( 'Default (%s)', 'Download refund status', 'easy-digital-downloads' ),
								$types[ $global_ability ],
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
	</div>

	<div class="edd-form-group">
		<label for="_edd_refund_window" class="edd-form-group__label">
			<?php esc_html_e( 'Refund Window', 'easy-digital-downloads' ); ?>
		</label>
		<div class="edd-form-group__control edd-amount-type-wrapper">
			<input class="edd-form-group__input small-text edd__input edd__input--left" id="_edd_refund_window" name="_edd_refund_window" type="number" min="0" max="3650" step="1" value="<?php echo esc_attr( $edd_refund_window ); ?>" placeholder="<?php echo absint( $global_window ); ?>" />
			<span class="edd-input__symbol edd-input__symbol--suffix"><?php echo esc_html( _x( 'Days', 'refund window interval', 'easy-digital-downloads' ) ); ?></span>
		</div>
		<p class="edd-form-group__help description">
			<?php _e( 'Leave blank to use global setting. Enter <code>0</code> for unlimited', 'easy-digital-downloads' ); ?>
		</p>
	</div>
	<?php
}
add_action( 'edd_meta_box_settings_fields', 'edd_render_refund_row', 25, 2 );

/**
 * File Download Limit Row
 *
 * The file download limit is the maximum number of times each file
 * can be downloaded by the buyer
 *
 * @since 1.3.1
 * @param int          $post_id  Download (Post) ID.
 * @param EDD_Download $download Download object.
 * @return void
 */
function edd_render_download_limit_row( $post_id, $download = null ) {
	if ( ! current_user_can( 'manage_shop_settings' ) ) {
		return;
	}

	// Get the download limit directly from the post meta so that the global is not involved.
	$edd_download_limit = get_post_meta( $post_id, '_edd_download_limit', true );
	?>
	<div
		class="edd-form-group"
		id="edd_download_limit_wrap"
		<?php
		if ( empty( $download->get_files() ) ) {
			echo 'data-edd-supports-product-type="false"';
		}
		?>
	>
		<label class="edd-form-group__label" for="edd_download_limit">
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
		<div class="edd-form-group__control">
			<input class="edd-form-group__input small-text" name="_edd_download_limit" id="edd_download_limit" type="number" min="0" max="5000" step="1" value="<?php echo esc_attr( $edd_download_limit ); ?>" />
		</div>
		<p class="edd-form-group__help description">
			<?php _e( 'Leave blank to use global setting. Enter <code>0</code> for unlimited', 'easy-digital-downloads' ); ?>
		</p>
	</div>
	<?php
}
add_action( 'edd_meta_box_settings_fields', 'edd_render_download_limit_row', 20, 2 );

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
function edd_render_down_tax_options( $post_id = 0, $download = null ) {

	// Bail if current user cannot view shop reports, or taxes are disabled.
	if ( ! current_user_can( 'view_shop_reports' ) || ! edd_use_taxes() ) {
		return;
	}

	$exclusive = edd_download_is_tax_exclusive( $post_id );
	?>

	<div class="edd-form-group">
		<div class="edd-form-group__control">
			<?php
			$checkbox = new EDD\HTML\CheckboxToggle(
				array(
					'name'    => '_edd_download_tax_exclusive',
					'id'      => '_edd_download_tax_exclusive',
					'current' => $exclusive,
					'class'   => 'edd-form-group__input',
					'label'   => __( 'This product is non-taxable', 'easy-digital-downloads' ),
				)
			);
			$checkbox->output();
			?>
		</div>
		<p class="description edd-form-group__help">
			<?php esc_html_e( 'When taxes are enabled, all products are taxable by default. Check this box to mark this product as non-taxable.', 'easy-digital-downloads' ); ?>
		</p>
	</div>
	<?php
}
add_action( 'edd_meta_box_settings_fields', 'edd_render_down_tax_options', 30, 2 );

/**
 * Product quantity settings
 *
 * Outputs the option to disable quantity field on product.
 *
 * @since 2.7
 * @param int $post_id Download (Post) ID.
 * @return void
 */
function edd_render_download_quantity_option( $post_id = 0, $download = null ) {
	if ( ! current_user_can( 'manage_shop_settings' ) || ! edd_item_quantities_enabled() ) {
		return;
	}

	$disabled = $download ? $download->quantities_disabled() : false;
	?>

	<div class="edd-form-group">
		<div class="edd-form-group__control">
			<?php
			$checkbox = new EDD\HTML\CheckboxToggle(
				array(
					'name'    => '_edd_quantities_disabled',
					'id'      => '_edd_quantities_disabled',
					'current' => $disabled,
					'class'   => 'edd-form-group__input',
					'label'   => __( 'Disable quantity input for this product', 'easy-digital-downloads' ),
				)
			);
			$checkbox->output();
			?>
		</div>
		<p class="description edd-form-group__help">
			<?php esc_html_e( 'If disabled, customers will not be provided an option to change the number they wish to purchase.', 'easy-digital-downloads' ); ?>
	</div>

	<?php
}
add_action( 'edd_meta_box_settings_fields', 'edd_render_download_quantity_option', 30, 2 );

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

	<div class="edd-form-group">
		<label class="edd-form-group__label" for="edd_sku">
			<?php
			esc_html_e( 'Enter an SKU for this product.', 'easy-digital-downloads' );
			$tooltip = new EDD\HTML\Tooltip(
				array(
					'title'   => __( 'SKU', 'easy-digital-downloads' ),
					'content' => __( 'If an SKU is entered for this product, it will be shown on the purchase receipt and exported purchase histories.', 'easy-digital-downloads' ),
				)
			);
			$tooltip->output();
			?>
		</label>
		<div class="edd-form-group__control">
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
 * @param int $download_id Download (Post) ID.
 * @return void
 */
function edd_render_disable_button( $download_id ) {
	if ( ! current_user_can( 'edit_product', $download_id ) ) {
		return;
	}

	$shortcode        = sprintf(
		'[purchase_link id="%1$d"]',
		absint( $download_id )
	);
	$buy_button       = sprintf( '<!-- wp:edd/buy-button {"download_id":"%d"} /-->', $download_id );
	$add_to_cart_link = add_query_arg(
		array(
			'edd_action'  => 'add_to_cart',
			'download_id' => (int) $download_id,
		),
		edd_get_checkout_uri()
	);
	$supports_buy_now = edd_shop_supports_buy_now();
	$content          = __( 'By default, the buy button will be displayed at the bottom of the download. Disable the default buy button and use the EDD Buy Button block to place the button where you prefer.', 'easy-digital-downloads' );
	if ( $supports_buy_now ) {
		$content .= '<br /><br />';
		$content .= __( '<strong>Purchase button behavior</strong>: Add to Cart buttons follow a traditional eCommerce flow. A Buy Now button bypasses most of the process, taking the customer directly from button click to payment, greatly speeding up the process of buying the product.', 'easy-digital-downloads' );
	}
	?>

	<div class="edd-metabox__buy-buttons">
		<h3>
			<?php
			esc_html_e( 'Buy Buttons', 'easy-digital-downloads' );
			$tooltip = new EDD\HTML\Tooltip(
				array(
					'title'   => __( 'Buy Buttons', 'easy-digital-downloads' ),
					'content' => $content,
				)
			);
			$tooltip->output();
			?>
		</h3>
		<div class="edd-buy-buttons">
			<div class="edd-buy-buttons__button">
				<input type="text" class="edd-hidden" id="edd-buy-button-block" value="<?php echo htmlentities( $buy_button ); ?>">
				<button type="button" class="button button-secondary edd-button__copy" data-clipboard-target="#edd-buy-button-block"><?php esc_html_e( 'Copy Buy Button Block', 'easy-digital-downloads' ); ?></button>
			</div>
			<div class="edd-buy-buttons__button">
				<input type="text" class="edd-hidden" id="edd-purchase-shortcode" value="<?php echo htmlentities( $shortcode ); ?>">
				<button type="button" class="button button-secondary edd-button__copy" data-clipboard-target="#edd-purchase-shortcode"><?php esc_html_e( 'Copy Buy Button Shortcode', 'easy-digital-downloads' ); ?></button>
			</div>
			<div class="edd-buy-buttons__button" data-edd-requires-variable-pricing="false">
				<input type="text" class="edd-hidden" id="edd-add-to-cart-link" value="<?php echo esc_html( $add_to_cart_link ); ?>">
				<button type="button" class="button button-secondary edd-button__copy" data-clipboard-target="#edd-add-to-cart-link"><?php esc_html_e( 'Copy Add to Cart Link', 'easy-digital-downloads' ); ?></button>
			</div>
		</div>

		<div class="edd-form-group">
			<div class="edd-form-group__control">
				<?php
				$is_disabled   = get_post_meta( $download_id, '_edd_hide_purchase_link', true ) ? 1 : 0;
				$checkbox_args = array(
					'name'    => '_edd_hide_purchase_link',
					'id'      => '_edd_hide_purchase_link',
					'current' => $is_disabled,
					'class'   => 'edd-form-group__input',
					'label'   => __( 'Hide default purchase button.', 'easy-digital-downloads' ),
				);
				if ( ! $is_disabled ) {
					$post_content = get_post_field( 'post_content', $download_id );
					if ( ! empty( $post_content ) && false !== strpos( $post_content, $buy_button ) ) {
						$checkbox_args['tooltip'] = array(
							'title'    => __( 'Buy Button Block Detected', 'easy-digital-downloads' ),
							'content'  => __( 'The Buy Button block is in the post content, so we recommend disabling the default purchase button.', 'easy-digital-downloads' ),
							'dashicon' => 'dashicons-warning',
						);
					}
				}
				$checkbox = new EDD\HTML\CheckboxToggle( $checkbox_args );
				$checkbox->output();
				?>
			</div>
		</div>
		<?php if ( ! empty( $supports_buy_now ) ) { ?>
			<div class="edd-form-group">
				<label for="edd_button_behavior" class="edd-form-group__label">
					<?php esc_html_e( 'Purchase button behavior', 'easy-digital-downloads' ); ?>
				</label>
				<div class="edd-form-group__control">
					<?php
					$select = new EDD\HTML\Select(
						array(
							'name'             => '_edd_button_behavior',
							'id'               => 'edd_button_behavior',
							'selected'         => get_post_meta( $download_id, '_edd_button_behavior', true ),
							'options'          => array(
								'add_to_cart' => __( 'Add to Cart', 'easy-digital-downloads' ),
								'direct'      => __( 'Buy Now', 'easy-digital-downloads' ),
							),
							'show_option_all'  => null,
							'show_option_none' => null,
							'class'            => 'edd-form-group__input',
						)
					);
					$select->output();
					?>
				</div>
			</div>
		<?php } ?>
	</div>
	<?php
}
add_action( 'edd_meta_box_settings_fields', 'edd_render_disable_button', 999 );


/** Product Notes *************************************************************/

/**
 * Render Product Notes Field
 *
 * @since 1.2.1
 * @param int $post_id Download (Post) ID.
 * @return void
 */
function edd_render_product_notes_field( $post_id, $download = null ) {
	// Check if the user can edit this specific download ID (post ID).
	if ( ! current_user_can( 'edit_product', $post_id ) ) {
		return;
	}

	$product_notes = $download ? $download->notes : false;
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
				/* translators: %s: singular label. */
				esc_html__( 'Special instructions for this %s. These will be added to the purchase receipt, and may be used by some extensions or themes.', 'easy-digital-downloads' ),
				edd_get_label_singular()
			);
			?>
		</p>
	</div>
	<?php
}
add_action( 'edd_product_notes_meta_box_fields', 'edd_render_product_notes_field', 10, 2 );
