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


/**
 * Donwload Columns
 *
 * Defines the custom columns and their order.
 *
 * @access      private
 * @since       1.0 
 * @return      array
*/

function edd_download_columns($download_columns){
	$download_columns = array(
		'cb' => '<input type="checkbox"/>',
		'title' => __('Name', 'edd'),
		'download_category' => __('Categories', 'edd'),
		'download_tag' => __('Tags', 'edd'),
		'sales' => __('Sales', 'edd'),
		'earnings' => __('Earnings', 'edd'),
		'shortcode' => __('Short Code', 'edd'),
		'date' => __('Date', 'edd')
	);
	return $download_columns;
}
add_filter('manage_edit-download_columns', 'edd_download_columns');


/**
 * Render Donwload Columns
 *
 * Render the custom columns content.
 *
 * @access      private
 * @since       1.0 
 * @return      void
*/

function edd_render_download_columns($column_name, $post_id) {
	if(get_post_type($post_id) == 'download') {
		
		$sales = edd_get_download_sales_stats($post_id);
		$earnings = edd_get_download_earnings_stats($post_id);
		
		switch ($column_name) {			
			case 'download_category':
				echo get_the_term_list($post_id, 'download_category', '', ', ', '');
				break;
			case 'download_tag':
				echo get_the_term_list($post_id, 'download_tag', '', ', ', '');
				break;
			case 'sales':
				echo $sales;
				break;
			case 'earnings':
				echo edd_currency_filter($earnings);
				break;
			case 'shortcode':
				echo '[purchase_link id="' . $post_id . '" text="' . __('Purchase', 'edd') . '" style="button" color="' . get_post_meta( $post_id, '_edd_purchase_color', true ) . ']';
				break;
		}
	}
}
add_action('manage_posts_custom_column', 'edd_render_download_columns', 10, 2);


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
	// check if we're viewing the "movie" post type
	if ( isset( $vars['post_type'] ) && 'download' == $vars['post_type'] ) {

		// check if 'orderby' is set to "sales"
		if ( isset( $vars['orderby'] ) && isset( $vars['sales'] ) && 'sales' == $vars['sales'] ) {

			// merge the query vars with our custom variables
			$vars = array_merge(
				$vars,
				array(
					'meta_key' => '_edd_download_sales',
					'orderby' => '_edd_download_sales'
				)
			);
		}
		
		// check if "orderby" is set to "earnings"
		if ( isset( $vars['orderby'] ) && isset( $vars['earnings'] ) && 'earnings' == $vars['earnings'] ) {

			// merge the query vars with our custom variables
			$vars = array_merge(
				$vars,
				array(
					'meta_key' => '_edd_download_earnings',
					'orderby' => '_edd_download_earnings'
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
add_action( 'load-edit.php', 'edd_download_load' );


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
	if($typenow == 'download') {
		
		$terms = get_terms('download_category');
		if(count($terms) > 0) {
			echo "<select name='download_category' id='download_category' class='postform'>";
				echo "<option value=''>" . __('Show all categories', 'edd') . "</option>";
				foreach ($terms as $term) { 
					$selected = isset($_GET['download_category']) && $_GET['download_category'] == $term->slug ? ' selected="selected"' : '';
					echo '<option value="' . $term->slug . '"' . $selected . '>' . $term->name .' (' . $term->count .')</option>'; 
				}
			echo "</select>";
		}
		
		$terms = get_terms('download_tag');
		if(count($terms) > 0) {
			echo "<select name='download_tag' id='download_tag' class='postform'>";
				echo "<option value=''>" . __('Show all tags', 'edd') . "</option>";
				foreach ($terms as $term) { 
					$selected = isset($_GET['download_tag']) && $_GET['download_tag'] == $term->slug ? ' selected="selected"' : '';
					echo '<option value="' . $term->slug . '"' . $selected . '>' . $term->name .' (' . $term->count .')</option>'; 
				}
			echo "</select>";
		}
		
	}

}
add_action( 'restrict_manage_posts', 'edd_add_download_filters', 100 );