<?php
/**
 * Dashboard Columns
 *
 * @package     EDD
 * @subpackage  Admin/Downloads
 * @copyright   Copyright (c) 2013, Pippin Williamson
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       1.0
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Download Columns
 *
 * Defines the custom columns and their order
 *
 * @since 1.0
 * @param array $download_columns Array of download columns
 * @return array $download_columns Updated array of download columns for Downloads
 *  Post Type List Table
 */
function edd_download_columns( $download_columns ) {
	$download_columns = array(
		'cb'                => '<input type="checkbox"/>',
		'title'             => __( 'Name', 'edd' ),
		'download_category' => __( 'Categories', 'edd' ),
		'download_tag'      => __( 'Tags', 'edd' ),
		'price'             => __( 'Price', 'edd' ),
		'sales'             => __( 'Sales', 'edd' ),
		'earnings'          => __( 'Earnings', 'edd' ),
		'shortcode'         => __( 'Purchase Short Code', 'edd' ),
		'date'              => __( 'Date', 'edd' )
	);

	if ( ! current_user_can( 'view_shop_reports' ) ) {
		unset( $download_columns['sales'] );
		unset( $download_columns['earnings'] );
	}

	return apply_filters( 'edd_download_columns', $download_columns );
}
add_filter( 'manage_edit-download_columns', 'edd_download_columns' );

/**
 * Render Download Columns
 *
 * @since 1.0
 * @param string $column_name Column name
 * @param int $post_id Download (Post) ID
 * @return void
 */
function edd_render_download_columns( $column_name, $post_id ) {
	if ( get_post_type( $post_id ) == 'download' ) {
		global $edd_options;

		$style 			= isset( $edd_options['button_style'] ) ? $edd_options['button_style'] : 'button';
		$color 			= isset( $edd_options['checkout_color'] ) ? $edd_options['checkout_color'] : 'blue';
		$color			= ( $color == 'inherit' ) ? '' : $color;
		$purchase_text 	= ! empty( $edd_options['add_to_cart_text'] ) ? $edd_options['add_to_cart_text'] : __( 'Purchase', 'edd' );

		switch ( $column_name ) {
			case 'download_category':
				echo get_the_term_list( $post_id, 'download_category', '', ', ', '');
				break;
			case 'download_tag':
				echo get_the_term_list( $post_id, 'download_tag', '', ', ', '');
				break;
			case 'price':
				if ( edd_has_variable_prices( $post_id ) ) {
					echo edd_price_range( $post_id );
				} else {
					echo edd_price( $post_id, false );
					echo '<input type="hidden" class="downloadprice-' . $post_id . '" value="' . edd_get_download_price( $post_id ) . '" />';
				}
				break;
			case 'sales':
				echo edd_get_download_sales_stats( $post_id );
				break;
			case 'earnings':
				echo edd_currency_filter( edd_format_amount( edd_get_download_earnings_stats( $post_id ) ) );
				break;
			case 'shortcode':
				echo '[purchase_link id="' . absint( $post_id ) . '" text="' . esc_html( $purchase_text ) . '" style="' . $style . '" color="' . esc_attr( $color ) . '"]';
				break;
		}
	}
}
add_action( 'manage_posts_custom_column', 'edd_render_download_columns', 10, 2 );

/**
 * Registers the sortable columns in the list table
 *
 * @since 1.0
 * @param array $columns Array of the columns
 * @return array $columns Array of sortable columns
 */
function edd_sortable_download_columns( $columns ) {
	$columns['price'] = 'price';
	$columns['sales'] = 'sales';
	$columns['earnings'] = 'earnings';

	return $columns;
}
add_filter( 'manage_edit-download_sortable_columns', 'edd_sortable_download_columns' );

/**
 * Sorts Columns in the Downloads List Table
 *
 * @since 1.0
 * @param array $vars Array of all the sort variables
 * @return array $vars Array of all the sort variables
 */
function edd_sort_downloads( $vars ) {
	// Check if we're viewing the "download" post type
	if ( isset( $vars['post_type'] ) && 'download' == $vars['post_type'] ) {
		// Check if 'orderby' is set to "sales"
		if ( isset( $vars['orderby'] ) && 'sales' == $vars['orderby'] ) {
			$vars = array_merge(
				$vars,
				array(
					'meta_key' => '_edd_download_sales',
					'orderby'  => 'meta_value_num'
				)
			);
		}

		// Check if "orderby" is set to "earnings"
		if ( isset( $vars['orderby'] ) && 'earnings' == $vars['orderby'] ) {
			$vars = array_merge(
				$vars,
				array(
					'meta_key' => '_edd_download_earnings',
					'orderby' => 'meta_value_num'
				)
			);
		}

		// Check if "orderby" is set to "earnings"
		if ( isset( $vars['orderby'] ) && 'price' == $vars['orderby'] ) {
			$vars = array_merge(
				$vars,
				array(
					'meta_key' => 'edd_price',
					'orderby' => 'meta_value_num'
				)
			);
		}
	}

	return $vars;
}

/**
 * Download Load
 *
 * Sorts the downloads.
 *
 * @since 1.0
 * @return void
 */
function edd_download_load() {
	add_filter( 'request', 'edd_sort_downloads' );
}
add_action( 'load-edit.php', 'edd_download_load', 9999 );

/**
 * Add Download Filters
 *
 * Adds taxonomy drop down filters for downloads.
 *
 * @since 1.0
 * @return void
 */
function edd_add_download_filters() {
	global $typenow;

	// Checks if the current post type is 'download'
	if ( $typenow == 'download') {
		$terms = get_terms( 'download_category' );
		if ( count( $terms ) > 0 ) {
			echo "<select name='download_category' id='download_category' class='postform'>";
				echo "<option value=''>" . __( 'Show all categories', 'edd' ) . "</option>";
				foreach ( $terms as $term ) {
					$selected = isset( $_GET['download_category'] ) && $_GET['download_category'] == $term->slug ? ' selected="selected"' : '';
					echo '<option value="' . esc_attr( $term->slug ) . '"' . $selected . '>' . esc_html( $term->name ) .' (' . $term->count .')</option>';
				}
			echo "</select>";
		}

		$terms = get_terms( 'download_tag' );
		if ( count( $terms ) > 0) {
			echo "<select name='download_tag' id='download_tag' class='postform'>";
				echo "<option value=''>" . __( 'Show all tags', 'edd' ) . "</option>";
				foreach ( $terms as $term ) {
					$selected = isset( $_GET['download_tag']) && $_GET['download_tag'] == $term->slug ? ' selected="selected"' : '';
					echo '<option value="' . esc_attr( $term->slug ) . '"' . $selected . '>' . esc_html( $term->name ) .' (' . $term->count .')</option>';
				}
			echo "</select>";
		}
	}

}
add_action( 'restrict_manage_posts', 'edd_add_download_filters', 100 );

/**
 * Adds price field to Quick Edit options
 *
 * @since 1.1.3.4
 * @param string $column_name Name of the column
 * @param string $post_type Current Post Type (i.e. download)
 * @return void
 */
function edd_price_field_quick_edit( $column_name, $post_type ) {
	if ( $column_name != 'price' || $post_type != 'download' ) return;
	?>
	<fieldset class="inline-edit-col-left">
		<div id="edd-download-data" class="inline-edit-col">
			<h4><?php echo sprintf( __( '%s Configuration', 'edd' ), edd_get_label_singular() ); ?></h4>
			<label>
				<span class="title"><?php _e( 'Price', 'edd' ); ?></span>
				<span class="input-text-wrap">
					<input type="text" name="_edd_regprice" class="text regprice" />
				</span>
			</label>
			<br class="clear" />
		</div>
	</fieldset>
	<?php
}
add_action( 'quick_edit_custom_box', 'edd_price_field_quick_edit', 10, 2 );
add_action( 'bulk_edit_custom_box', 'edd_price_field_quick_edit', 10, 2 );

/**
 * Updates price when saving post
 *
 * @since 1.1.3.4
 * @param int $post_id Download (Post) ID
 * @return void
 */
function edd_price_save_quick_edit( $post_id ) {
	if ( ! isset( $_POST['post_type']) || 'download' !== $_POST['post_type'] ) return;
	if ( ! current_user_can( 'edit_post', $post_id ) ) return $post_id;
	if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) return $post_id;

	if ( isset( $_REQUEST['_edd_regprice'] ) ) {
		update_post_meta( $post_id, 'edd_price', strip_tags( stripslashes( $_REQUEST['_edd_regprice'] ) ) );
	}
}
add_action( 'save_post', 'edd_price_save_quick_edit' );

/**
 * Process bulk edit actions via AJAX
 *
 * @since 1.4.4
 * @return void
 */
function edd_save_bulk_edit() {
	$post_ids = ( isset( $_POST[ 'post_ids' ] ) && ! empty( $_POST[ 'post_ids' ] ) ) ? $_POST[ 'post_ids' ] : array();

	if ( ! empty( $post_ids ) && is_array( $post_ids ) ) {
		$price = isset( $_POST['price'] ) ? strip_tags( stripslashes( $_POST['price'] ) ) : 0;
		foreach ( $post_ids as $post_id ) {
			if ( ! empty( $price ) ) {
				update_post_meta( $post_id, 'edd_price', edd_sanitize_amount( $price ) );
			}
		}
	}

	die();
}
add_action( 'wp_ajax_edd_save_bulk_edit', 'edd_save_bulk_edit' );
