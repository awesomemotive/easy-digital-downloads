<?php

// define the custom columns and the order of the columns
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


// render the custom column content
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
				echo '[purchase_link id="' . $post_id . '" text="' . __('Purchase', 'edd') . '" style="button" color="gray"]';
				break;
		}
	}
}
add_action('manage_posts_custom_column', 'edd_render_download_columns', 10, 2);

function edd_sortable_download_columns( $columns ) {

	$columns['sales'] = 'sales';
	$columns['earnings'] = 'earnings';

	return $columns;
}
add_filter( 'manage_edit-download_sortable_columns', 'edd_sortable_download_columns' );


/* Sorts the downloads */
function edd_sort_downloads( $vars ) {
	/* Check if we're viewing the 'movie' post type. */
	if ( isset( $vars['post_type'] ) && 'download' == $vars['post_type'] ) {

		/* Check if 'orderby' is set to 'sales'. */
		if ( isset( $vars['orderby'] ) && isset( $vars['sales'] ) && 'sales' == $vars['sales'] ) {

			/* Merge the query vars with our custom variables. */
			$vars = array_merge(
				$vars,
				array(
					'meta_key' => '_edd_download_sales',
					'orderby' => '_edd_download_sales'
				)
			);
		}
		
		/* Check if 'orderby' is set to 'earnings'. */
		if ( isset( $vars['orderby'] ) && isset( $vars['earnings'] ) && 'earnings' == $vars['earnings'] ) {

			/* Merge the query vars with our custom variables. */
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
function edd_download_load() {
	add_filter( 'request', 'edd_sort_downloads' );
}
add_action( 'load-edit.php', 'edd_download_load' );


// add taxonomy drop down filters for downloads
function edd_add_download_filters() {
	global $typenow; 			// the current post type
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
