<?php
/**
 * Metabox Functions
 *
 * @package     Easy Digital Downloads
 * @subpackage  Metabox Functions
 * @copyright   Copyright (c) 2013, Pippin Williamson
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       1.0
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

/** All Downloads *****************************************************************/

/**
 * Register all metaboxes for downloads.
 *
 * @access      private
 * @since       1.0
 * @return      void
 */
function edd_add_download_meta_box() {

	/** Download Configuration */
	add_meta_box( 'downloadinformation', sprintf( __( '%1$s Configuration', 'edd' ), edd_get_label_singular(), edd_get_label_plural() ),  'edd_render_download_meta_box', 'download', 'normal', 'default' );

	/** Product Notes */
	add_meta_box( 'edd_product_notes', __( 'Product Notes', 'edd' ), 'edd_render_product_notes_meta_box', 'download', 'normal', 'default' );

	if ( current_user_can( 'edit_product', get_the_ID() ) ) {

		/** Download Stats */
		add_meta_box( 'edd_download_stats', sprintf( __( '%1$s Stats', 'edd' ), edd_get_label_singular(), edd_get_label_plural() ), 'edd_render_stats_meta_box', 'download', 'side', 'high' );

	}
}
add_action( 'add_meta_boxes', 'edd_add_download_meta_box' );

/**
 * Download Meta Box Save
 *
 * Save data from meta box.
 *
 * @access      private
 * @since       1.0
 * @return      void
 */
function edd_download_meta_box_save( $post_id) {
	global $post;

	if ( ! isset( $_POST['edd_download_meta_box_nonce'] ) || ! wp_verify_nonce( $_POST['edd_download_meta_box_nonce'], basename( __FILE__ ) ) )
		return $post_id;

	if ( ( defined('DOING_AUTOSAVE') && DOING_AUTOSAVE ) || ( defined( 'DOING_AJAX') && DOING_AJAX ) || isset( $_REQUEST['bulk_edit'] ) ) return $post_id;

	if ( isset( $post->post_type ) && $post->post_type == 'revision' )
		return $post_id;

	if ( ! current_user_can( 'edit_pages', $post_id ) ) {
		return $post_id;
	}

	// The default fields that get saved
	$fields = apply_filters( 'edd_metabox_fields_save', array(
			'edd_price',
			'_variable_pricing',
			'_edd_price_options_mode',
			'edd_variable_prices',
			'edd_download_files',
			'_edd_purchase_text',
			'_edd_purchase_style',
			'_edd_purchase_color',
			'_edd_download_limit',
			'_edd_hide_purchase_link',
			'edd_product_notes'
		)
	);

	foreach ( $fields as $field ) {
		if ( isset( $_POST[ $field ] ) ) {
			if ( is_string( $_POST[ $field ] ) ) {
				$new = esc_attr( $_POST[ $field ] );
			} else {
				$new = $_POST[ $field ];
			}

			$new = apply_filters( 'edd_metabox_save_' . $field, $new );

			update_post_meta( $post_id, $field, $new );
		} else {
			delete_post_meta( $post_id, $field );
		}
	}
}
add_action( 'save_post', 'edd_download_meta_box_save' );

/**
 * Sanitize the price before it is saved
 *
 * This is mostly for ensuring commas aren't saved in the price
 *
 * @access      private
 * @since       1.3.2
 * @return      float
 */
function edd_sanitize_price_save( $price ) {
	global $edd_options;

	$thousands_sep = ! empty( $edd_options['thousands_separator'] ) ? $edd_options['thousands_separator'] : ',';
	$decimal_sep   = ! empty( $edd_options['decimal_separator'] )   ? $edd_options['decimal_separator'] 	: '.';

	if ( $thousands_sep == ',' ) {
		$price = str_replace( ',', '', $price );
	}

	return $price;
}
add_filter( 'edd_metabox_save_edd_price', 'edd_sanitize_price_save' );

/**
 * Sanitize the variable prices
 *
 * Ensures prices are correctly mapped to an array starting with an index of 0
 *
 * @access      private
 * @since       1.4.2
 * @return      float
 */
function edd_sanitize_variable_prices_save( $prices ) {
	// Make sure all prices are rekeyed starting at 0
	return array_values( $prices );
}
add_filter( 'edd_metabox_save_edd_variable_prices', 'edd_sanitize_variable_prices_save' );

/**
 * Sanitize the file downloads
 *
 * Ensures files are correctly mapped to an array starting with an index of 0
 *
 * @access      private
 * @since       1.5.1
 * @return      array
 */
function edd_sanitize_files_save( $files ) {
	// Make sure all files are rekeyed starting at 0
	return array_values( $files );
}
add_filter( 'edd_metabox_save_edd_download_files', 'edd_sanitize_files_save' );


/** Download Configuration *****************************************************************/

/**
 * Download Metabox
 *
 * Extensions (as well as the core plugin) can add items to the main download
 * configuration metabox via the `edd_meta_box_fields` action.
 *
 * @access      private
 * @since       1.0
 * @return      void
 */
function edd_render_download_meta_box() {
	global $post, $edd_options;

	do_action( 'edd_meta_box_fields', $post->ID );
	wp_nonce_field( basename( __FILE__ ), 'edd_download_meta_box_nonce' );
}

/**
 * Price section
 *
 * If variable pricing is not enabled, simply output a single input box.
 *
 * If variable pricing is enabled, outputs a table of all current prices.
 * Extensions can add column heads to the table via the `edd_download_file_table_head`
 * hook, and actual columns via `edd_download_file_table_row`
 *
 * @see         edd_render_price_row()
 *
 * @access      private
 * @since       1.0
 * @return      void
 */
function edd_render_price_field( $post_id ) {
	global $edd_options;

	$price 				= edd_get_download_price( $post_id );
	$variable_pricing 	= edd_has_variable_prices( $post_id );
	$prices 			= edd_get_variable_prices( $post_id );
	$single_option_mode = edd_single_price_option_mode( $post_id );

	$price_display    	= $variable_pricing ? ' style="display:none;"' : '';
	$variable_display 	= $variable_pricing ? '' : ' style="display:none;"';
?>

	<p>
		<strong><?php echo apply_filters( 'edd_price_options_heading', __( 'Pricing Options:', 'edd' ) ); ?></strong>
	</p>

	<p>
		<label for="edd_variable_pricing">
			<input type="checkbox" name="_variable_pricing" id="edd_variable_pricing" value="1" <?php checked( 1, $variable_pricing ); ?> />
			<?php echo apply_filters( 'edd_variable_pricing_toggle_text', __( 'Enable variable pricing', 'edd' ) ); ?>
		</label>
	</p>

	<div id="edd_regular_price_field" class="edd_pricing_fields" <?php echo $price_display; ?>>
		<?php if ( ! isset( $edd_options['currency_position'] ) || $edd_options['currency_position'] == 'before' ) : ?>
			<?php echo edd_currency_filter( '' ); ?><input type="text" name="edd_price" id="edd_price" value="<?php echo isset( $price ) ? esc_attr( edd_format_amount( $price ) ) : ''; ?>" size="30" style="width: 80px;" placeholder="9.99"/>
		<?php else : ?>
			<input type="text" name="edd_price" id="edd_price" value="<?php echo isset( $price ) ? esc_attr( edd_format_amount( $price ) ) : ''; ?>" size="30" style="width: 80px;" placeholder="9.99"/><?php echo edd_currency_filter( '' ); ?>
		<?php endif; ?>

		<?php do_action( 'edd_price_field', $post_id ); ?>
	</div>

	<?php do_action( 'edd_after_price_field', $post_id ); ?>

	<div id="edd_variable_price_fields" class="edd_pricing_fields" <?php echo $variable_display; ?>>
		<input type="hidden" id="edd_variable_prices" class="edd_variable_prices_name_field" value=""/>
		<p>
			<input type="checkbox" name="_edd_price_options_mode" id="edd_price_options_mode"<?php checked( 1, $single_option_mode ); ?> />
			<label for="edd_price_options_mode"><?php apply_filters( 'edd_multi_option_purchase_text', _e( 'Enable multi option purchase mode. Leave unchecked to only permit a single price option to be purchased', 'edd' ) ); ?></label>
		</p>
		<div id="edd_price_fields" class="edd_meta_table_wrap">
			<table class="widefat" width="100%" cellpadding="0" cellspacing="0">
				<thead>
					<tr>
						<th><?php _e( 'Option Name', 'edd' ); ?></th>
						<th style="width: 90px"><?php _e( 'Price', 'edd' ); ?></th>
						<?php do_action( 'edd_download_price_table_head', $post_id ); ?>
						<th style="width: 2%"></th>
					</tr>
				</thead>
				<tbody>
					<?php
						if ( ! empty( $prices ) ) :
							foreach ( $prices as $key => $value ) :
								$name   = isset( $value['name'] ) ? $value['name'] : '';
								$amount = isset( $value['amount'] ) ? $value['amount'] : '';

								$args = apply_filters( 'edd_price_row_args', compact( 'name', 'amount' ), $value );
					?>
						<tr class="edd_variable_prices_wrapper">
							<?php do_action( 'edd_render_price_row', $key, $args, $post_id ); ?>
						</tr>
					<?php
							endforeach;
						else :
					?>
						<tr class="edd_variable_prices_wrapper">
							<?php do_action( 'edd_render_price_row', 0, array(), $post_id ); ?>
						</tr>
					<?php endif; ?>

					<tr>
						<td class="submit" colspan="4" style="float: none; clear:both; background:#fff;">
							<a class="button-secondary edd_add_repeatable" style="margin: 6px 0;"><?php _e( 'Add New Price', 'edd' ); ?></a>
						</td>
					</tr>
				</tbody>
			</table>
		</div>
	</div><!--end #edd_variable_price_fields-->
<?php
}
add_action( 'edd_meta_box_fields', 'edd_render_price_field', 10 );

/**
 * Individual price row.
 *
 * Used to output a table row for each price associated with a download.
 * Can be called directly, or attached to an action.
 *
 * @access      private
 * @since       1.2.2
 * @return      void
 */
function edd_render_price_row( $key, $args = array(), $post_id ) {
	global $edd_options;

	$defaults = array(
		'name'   => null,
		'amount' => null
	);

	$args = wp_parse_args( $args, $defaults );
	extract( $args, EXTR_SKIP );
?>
	<td>
		<input type="text" class="edd_variable_prices_name" placeholder="<?php _e( 'Option Name', 'edd' ); ?>" name="edd_variable_prices[<?php echo $key; ?>][name]" id="edd_variable_prices[<?php echo $key; ?>][name]" value="<?php echo esc_attr( $name ); ?>" size="20" style="width:100%" />
	</td>

	<td>
		<?php if( ! isset( $edd_options['currency_position'] ) || $edd_options['currency_position'] == 'before' ) : ?>
			<span><?php echo edd_currency_filter( '' ); ?></span> <input type="text" class="edd_variable_prices_amount text" value="<?php echo $amount; ?>" placeholder="9.99" name="edd_variable_prices[<?php echo $key; ?>][amount]" id="edd_variable_prices[<?php echo $key; ?>][amount]" size="30" style="width:80px;" />
		<?php else : ?>
			<input type="text" class="edd_variable_prices_amount text" value="<?php echo $amount; ?>" placeholder="9.99" name="edd_variable_prices[<?php echo $key; ?>][amount]" id="edd_variable_prices[<?php echo $key; ?>][amount]" size="30" style="width:80px;" /><?php echo edd_currency_filter( '' ); ?>
		<?php endif; ?>
	</td>

	<?php do_action( 'edd_download_price_table_row', $post_id, $key, $args ); ?>

	<td>
		<a href="#" class="edd_remove_repeatable" data-type="price" style="background: url(<?php echo admin_url('/images/xit.gif'); ?>) no-repeat;">&times;</a>
	</td>
<?php
}
add_action( 'edd_render_price_row', 'edd_render_price_row', 10, 3 );

/**
 * File Downloads section.
 *
 * Outputs a table of all current files. Extensions can add column heads to the table
 * via the `edd_download_file_table_head` hook, and actual columns via
 * `edd_download_file_table_row`
 *
 * @see         edd_render_file_row()
 *
 * @access      private
 * @since       1.0
 * @return      void
 */
function edd_render_files_field( $post_id ) {
	$files 			= edd_get_download_files( $post_id );
	$variable_pricing 	= edd_has_variable_prices( $post_id );
	$variable_display 	= $variable_pricing ? '' : 'display:none;';
?>
	<div id="edd_download_files">
		<p>
			<strong><?php _e( 'File Downloads:', 'edd' ); ?></strong>
		</p>

		<input type="hidden" id="edd_download_files" class="edd_repeatable_upload_name_field" value=""/>

		<div id="edd_file_fields" class="edd_meta_table_wrap">
			<table class="widefat" width="100%" cellpadding="0" cellspacing="0">
				<thead>
					<tr>
						<th style="width: 20%"><?php _e( 'File Name', 'edd' ); ?></th>
						<th><?php _e( 'File URL', 'edd' ); ?></th>
						<th class="pricing" style="width: 20%; <?php echo $variable_display; ?>"><?php _e( 'Price Assignment', 'edd' ); ?></th>
						<?php do_action( 'edd_download_file_table_head', $post_id ); ?>
						<th style="width: 2%"></th>
					</tr>
				</thead>
				<tbody>
				<?php
					if ( ! empty( $files ) ) :
						foreach ( $files as $key => $value ) :
							$name = isset( $value['name'] ) ? $value['name'] : '';
							$file = isset( $value['file'] ) ? $value['file'] : '';
							$condition = isset( $value['condition'] ) ? $value['condition'] : false;

							$args = apply_filters( 'edd_file_row_args', compact( 'name', 'file', 'condition' ), $value );
				?>
						<tr class="edd_repeatable_upload_wrapper">
							<?php do_action( 'edd_render_file_row', $key, $args, $post_id ); ?>
						</tr>
				<?php
						endforeach;
					else :
				?>
					<tr class="edd_repeatable_upload_wrapper">
						<?php do_action( 'edd_render_file_row', 0, array(), $post_id ); ?>
					</tr>
				<?php endif; ?>
					<tr>
						<td class="submit" colspan="4" style="float: none; clear:both; background: #fff;">
							<a class="button-secondary edd_add_repeatable" style="margin: 6px 0;"><?php _e( 'Add New File', 'edd' ); ?></a>
						</td>
					</tr>
				</tbody>
			</table>
		</div>
	</div>
<?php
}
add_action( 'edd_meta_box_fields', 'edd_render_files_field', 20 );

/**
 * Individual file row.
 *
 * Used to output a table row for each file associated with a download.
 * Can be called directly, or attached to an action.
 *
 * @access      private
 * @since       1.2.2
 * @return      void
 */
function edd_render_file_row( $key = '', $args = array(), $post_id ) {
	$defaults = array(
		'name'      => null,
		'file'      => null,
		'condition' => null
	);

	$args = wp_parse_args( $args, $defaults );
	extract( $args, EXTR_SKIP );

	$prices = edd_get_variable_prices( $post_id );

	$variable_pricing = edd_has_variable_prices( $post_id );
	$variable_display = $variable_pricing ? '' : ' style="display:none;"';
?>
	<td>
		<input type="text" class="edd_repeatable_name_field" name="edd_download_files[<?php echo $key; ?>][name]" id="edd_download_files[<?php echo $key; ?>][name]" value="<?php echo $name; ?>" placeholder="<?php _e( 'File Name', 'edd' ); ?>" style="width:100%" />
	</td>

	<td>
		<div class="edd_repeatable_upload_field_container">
			<input type="text" class="edd_repeatable_upload_field edd_upload_field" name="edd_download_files[<?php echo $key; ?>][file]" id="edd_download_files[<?php echo $key; ?>][file]" value="<?php echo $file; ?>" placeholder="<?php _e( 'http://', 'edd' ); ?>" style="width:100%" />

			<span class="edd_upload_file">
				<a href="#" data-uploader_title="" data-uploader_button_text="<?php _e( 'Insert', 'edd' ); ?>" class="edd_upload_image_button" onclick="return false;"><?php _e( 'Upload a File', 'edd' ); ?></a>
			</span>
		</div>
	</td>

	<td class="pricing"<?php echo $variable_display; ?>>
		<select class="edd_repeatable_condition_field" name="edd_download_files[<?php echo $key; ?>][condition]" id="edd_download_files[<?php echo $key; ?>][condition]" <?php echo $variable_display; ?>>
			<option value="all"><?php _e( 'All Prices', 'edd' ); ?></option>
			<?php if ( $prices ) : foreach ( $prices as $price_key => $price ) : ?>
				<option value="<?php echo $price_key; ?>" <?php selected( $price_key, $condition ); ?>><?php echo $prices[ $price_key ]['name']; ?></option>
			<?php endforeach; endif; ?>
		</select>
	</td>

	<?php do_action( 'edd_download_file_table_row', $post_id, $key, $args ); ?>

	<td>
		<a href="#" class="edd_remove_repeatable" data-type="file" style="background: url(<?php echo admin_url('/images/xit.gif'); ?>) no-repeat;">&times;</a>
	</td>
<?php
}
add_action( 'edd_render_file_row', 'edd_render_file_row', 10, 3 );

/**
 * File download limit row
 *
 * The file download limit is the maximum number of times each file
 * can be downloaded by the buyer
 *
 * @access      private
 * @since       1.3.1
 * @return      void
 */
function edd_render_download_limit_row( $post_id ) {
	global $edd_options;
	$edd_download_limit = edd_get_file_download_limit( $post_id );
?>
	<p>
		<strong><?php _e( 'File Download Limit:', 'edd' ); ?></strong>
	</p>
	<label for="edd_download_limit">
		<input type="text" name="_edd_download_limit" id="edd_download_limit" value="<?php echo esc_attr( $edd_download_limit ); ?>" size="30" style="width: 80px;" placeholder="0"/>
		<?php _e( 'The maximum number of times a buyer can download each file. Leave blank or set to 0 for unlimited', 'edd' ); ?>
	</label>

<?php
}
add_action( 'edd_meta_box_fields', 'edd_render_download_limit_row', 20 );


/**
 * Render Disable Button
 *
 * @access      private
 * @since       1.0
 * @return      void
 */

function edd_render_disable_button( $post_id ) {
	$hide_button = get_post_meta( $post_id, '_edd_hide_purchase_link', true ) ? true : false;
?>
	<p>
		<strong><?php _e( 'Button Options:', 'edd' ); ?></strong>
	</p>

	<p>
		<label for="_edd_hide_purchase_link">
			<input type="checkbox" name="_edd_hide_purchase_link" id="_edd_hide_purchase_link" value="1" <?php checked( true, $hide_button ); ?> />
			<?php _e( 'Disable the automatic output of the purchase button', 'edd' ); ?>
		</label>
	</p>
<?php
}
add_action( 'edd_meta_box_fields', 'edd_render_disable_button', 30 );

/**
 * Don't save blank rows.
 *
 * When saving, check the price and file table for blank rows.
 * If the name of the price or file is empty, that row should not
 * be saved.
 *
 * @access      private
 * @since       1.2.2
 * @return      array $new New meta value with empty keys removed
 */
function edd_metabox_save_check_blank_rows( $new ) {
	foreach ( $new as $key => $value ) {
		if ( empty( $value['name'] ) && empty( $value['amount'] ) && empty( $value['file'] ) )
			unset( $new[ $key ] );
	}

	return $new;
}
add_filter( 'edd_metabox_save_edd_variable_prices', 'edd_metabox_save_check_blank_rows' );
add_filter( 'edd_metabox_save_edd_download_files', 'edd_metabox_save_check_blank_rows' );


/** Product Notes *****************************************************************/

/**
 * Product Notes Meta Box
 *
 * Render the product notes meta box.
 *
 * @access      private
 * @since       1.2.1
 * @return      void
 */
function edd_render_product_notes_meta_box() {
	global $post, $edd_options;

	do_action( 'edd_product_notes_meta_box_fields', $post->ID );
}

/**
 * Render Product Notes Field
 *
 * @access      private
 * @since       1.2.1
 * @return      void
 */
function edd_render_product_notes_field( $post_id ) {
	global $edd_options;

	$product_notes = edd_get_product_notes( $post_id );
?>
	<textarea rows="1" cols="40" class="large-texarea" name="edd_product_notes" id="edd_product_notes"><?php echo esc_textarea( $product_notes ); ?></textarea>
	<p><?php _e( 'Special notes or instructions for this product. These notes will be added to the purchase receipt.', 'edd' ); ?></p>
<?php
}
add_action( 'edd_product_notes_meta_box_fields', 'edd_render_product_notes_field' );


/** Stats *****************************************************************/

/**
 * Render Stats Meta Box
 *
 * @access      private
 * @since       1.0
 * @return      void
 */
function edd_render_stats_meta_box() {
	global $post;

	$earnings = edd_get_download_earnings_stats( $post->ID );
	$sales    = edd_get_download_sales_stats( $post->ID );

	echo '<table class="form-table">';
		echo '<tr>';
			echo '<th style="width: 20%">' . __( 'Sales:', 'edd' ) . '</th>';
			echo '<td class="edd_download_stats">';
				echo $sales . '&nbsp;&ndash;&nbsp;<a href="' . admin_url( '/edit.php?page=edd-reports&view=sales&post_type=download&tab=logs&download=' . $post->ID ) . '">' . __( 'View Sales Log', 'edd' ) . '</a>';
			echo '</td>';
		echo '</tr>';
		echo '<tr>';
			echo '<th style="width: 20%">' . __( 'Earnings:', 'edd' ) . '</th>';
			echo '<td class="edd_download_stats">';
				echo edd_currency_filter( edd_format_amount( $earnings ) );
			echo '</td>';
		echo '</tr>';
		echo '<tr>';
			echo '<td colspan="2" class="edd_download_stats">';
				echo '<a href="' . admin_url( '/edit.php?page=edd-reports&view=file_downloads&post_type=download&tab=logs&download=' . $post->ID ) . '">' . __( 'View File Download Log', 'edd' ) . '</a>';
			echo '</td>';
		echo '</tr>';
		do_action('edd_stats_meta_box');
	echo '</table>';
}

