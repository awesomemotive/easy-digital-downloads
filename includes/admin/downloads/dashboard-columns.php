<?php
/**
 * Dashboard Columns
 *
 * @package     Easy Digital Downloads
 * @subpackage  Dashboard Columns
 * @copyright   Copyright (c) 2012, Pippin Williamson
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       1.0
*/

// Exit if accessed directly
if ( !defined( 'ABSPATH' ) ) exit;

/**
 * Donwload Columns
 *
 * Defines the custom columns and their order.
 *
 * @access      private
 * @since       1.0
 * @return      array
*/

function edd_download_columns( $download_columns ){
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
	return $download_columns;
}
add_filter( 'manage_edit-download_columns', 'edd_download_columns' );


/**
 * Render Donwload Columns
 *
 * Render the custom columns content.
 *
 * @access      private
 * @since       1.0
 * @return      void
*/

function edd_render_download_columns( $column_name, $post_id ) {
	if( get_post_type( $post_id ) == 'download' ) {
		global $edd_options;
		$style 			= isset( $edd_options['button_style'] ) ? $edd_options['button_style'] : 'button';
		$color 			= isset( $edd_options['checkout_color'] ) ? $edd_options['checkout_color'] : 'blue';
		$purchase_text 	= isset( $edd_options['add_to_cart_text'] ) ? $edd_options['add_to_cart_text'] : __( 'Purchase', 'edd' );

		switch ( $column_name) {
			case 'download_category':
				echo get_the_term_list( $post_id, 'download_category', '', ', ', '');
				break;
			case 'download_tag':
				echo get_the_term_list( $post_id, 'download_tag', '', ', ', '');
				break;
			case 'price':
				echo edd_price( $post_id, false);
				if ( !edd_has_variable_prices( $post_id) ) {
					echo '<input type="hidden" class="downloadprice-' . $post_id . '" value="' . edd_get_download_price( $post_id) . '" />';
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
 * Sortable Donwload Columns
 *
 * Set the sortable columns content.
 *
 * @access      private
 * @since       1.0
 * @return      array
*/

function edd_sortable_download_columns( $columns ) {

	$columns['price'] = 'price';
	$columns['sales'] = 'sales';
	$columns['earnings'] = 'earnings';

	return $columns;
}
add_filter( 'manage_edit-download_sortable_columns', 'edd_sortable_download_columns' );


/**
 * Sorts Downloads
 *
 * Sorts the downloads.
 *
 * @access      private
 * @since       1.0
 * @return      array
*/

function edd_sort_downloads( $vars ) {

	// check if we're viewing the "download" post type
	if ( isset( $vars['post_type'] ) && 'download' == $vars['post_type'] ) {

		// check if 'orderby' is set to "sales"
		if ( isset( $vars['orderby'] ) && 'sales' == $vars['orderby'] ) {

			// merge the query vars with our custom variables
			$vars = array_merge(
				$vars,
				array(
					'meta_key' => '_edd_download_sales',
					'orderby' => 'meta_value_num'
				)
			);
		}

		// check if "orderby" is set to "earnings"
		if ( isset( $vars['orderby'] ) && 'earnings' == $vars['orderby'] ) {
			// merge the query vars with our custom variables
			$vars = array_merge(
				$vars,
				array(
					'meta_key' => '_edd_download_earnings',
					'orderby' => 'meta_value_num'
				)
			);
		}

		// check if "orderby" is set to "earnings"
		if ( isset( $vars['orderby'] ) && 'price' == $vars['orderby'] ) {
			// merge the query vars with our custom variables
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
 * @access      private
 * @since       1.0
 * @return      void
*/

function edd_download_load() {
	add_filter( 'request', 'edd_sort_downloads' );
}
add_action( 'load-edit.php', 'edd_download_load', 9999 );


/**
 * Add Download Filters
 *
 * Add taxonomy drop down filters for downloads.
 *
 * @access      private
 * @since       1.0
 * @return      void
*/

function edd_add_download_filters() {
	global $typenow;

	// the current post type
	if( $typenow == 'download') {

		$terms = get_terms( 'download_category' );
		if(count( $terms) > 0) {
			echo "<select name='download_category' id='download_category' class='postform'>";
				echo "<option value=''>" . __( 'Show all categories', 'edd' ) . "</option>";
				foreach ( $terms as $term) {
					$selected = isset( $_GET['download_category']) && $_GET['download_category'] == $term->slug ? ' selected="selected"' : '';
					echo '<option value="' . esc_attr( $term->slug ) . '"' . $selected . '>' . esc_html( $term->name ) .' (' . $term->count .')</option>';
				}
			echo "</select>";
		}

		$terms = get_terms('download_tag');
		if(count( $terms) > 0) {
			echo "<select name='download_tag' id='download_tag' class='postform'>";
				echo "<option value=''>" . __( 'Show all tags', 'edd' ) . "</option>";
				foreach ( $terms as $term) {
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
 * @access		public
 * @since 		1.1.3.4
 * @return		void
*/

function edd_price_field_quick_edit( $column_name, $post_type ) {
	if ( $column_name != 'price' || $post_type != 'download' ) return;
	?>
	<fieldset class="inline-edit-col-left">
		<div id="edd-download-data" class="inline-edit-col">

			<h4><?php echo sprintf( __( '%s Data', 'edd' ), edd_get_label_singular() ); ?></h4>

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


/**
 * Updates price when saving post
 *
 * @access		private
 * @since		1.1.3.4
 * @return		void
 */

function edd_price_save_quick_edit( $post_id ) {
	if ( ! isset( $_POST['post_type']) || 'download' !== $_POST['post_type'] ) return;
	if ( ! current_user_can( 'edit_post', $post_id ) ) return $post_id;
	if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) return $post_id;

	if ( isset( $_REQUEST['_edd_regprice'] ) ) {
		update_post_meta( $post_id, 'edd_price', esc_html( stripslashes( $_REQUEST['_edd_regprice'] ) ) );
	}
}
add_action( 'save_post', 'edd_price_save_quick_edit' );
