<?php

// retrieves a download and displays the purchase form
function edd_download_shortcode( $atts, $content = null ) {	
	extract( shortcode_atts( array(
			'id' => '',
			'text' => __('Purchase', 'edd'),
			'style' => 'button',
			'color' => 'blue'
		), $atts )
	);
	
	$download = edd_get_download($id);
	
	if($download) {
		return edd_get_purchase_link($download->ID, $text, $style, $color);
	}
}
add_shortcode('purchase_link', 'edd_download_shortcode');

// displays a user's download/purchsae history
function edd_download_history() {
	
	global $user_ID, $edd_options;
	
	if(is_user_logged_in()) {
		$purchases = edd_get_users_purchases($user_ID);
		
		ob_start();
			if($purchases) { ?>
				<table id="edd_user_history">
					<thead>
						<tr>
							<?php do_action('edd_user_history_header_before'); ?>
							<th class="edd_download_download_name_header"><?php _e('Download Name', 'edd'); ?></th>
							<th class="edd_download_download_files_header"><?php _e('Files', 'edd'); ?></th>
							<?php do_action('edd_user_history_header_after'); ?>
						</tr>
					</thead>
					<?php 
					foreach($purchases as $purchase) {
						$downloads = edd_get_downloads_of_purchase($purchase->ID);
						$payment_meta = get_post_meta($purchase->ID, '_edd_payment_meta', true);
						if($downloads) {
							foreach($downloads as $download) {
								echo '<tr>';
									$id = isset($payment_meta['cart_details']) ? $download['id'] : $download;
									$download_files = get_post_meta($id, 'edd_download_files', true);
									do_action('edd_user_history_table_begin', $purchase->ID);
									echo '<td>' . get_the_title($id) . '</td>';
									echo '<td>';
									if($download_files) {
										foreach($download_files as $filekey => $file) {
												$download_url = edd_get_download_file_url($payment_meta['key'], $payment_meta['email'], $filekey, $id);
												echo'<div class="edd_download_file"><a href="' . $download_url . '" class="edd_download_file_link">' . $file['name'] . '</a></div>';
										} 
									} else {
										_e('No downloadable files found.', 'edd');
									}
									echo '</td>';
									do_action('edd_user_history_table_end', $purchase->ID);
								echo '</tr>';
							}
						}
					}
				echo '</table>';
			} else {
				echo '<p class="edd-no-downloads">' . __('You have not purchased any downloads', 'edd') . '</p>';
			}
		return ob_get_clean();
	}
}
add_shortcode('download_history', 'edd_download_history');

// show the checkout form
function edd_checkout_form_shortcode($atts, $content = null) {
	return edd_checkout_form();
}
add_shortcode('download_checkout', 'edd_checkout_form_shortcode');

// show the shopping cart
function edd_cart_shortcode($atts, $content = null) {
	return edd_shopping_cart();
}
add_shortcode('download_cart', 'edd_cart_shortcode');

/*
* Displays a collection purchase link for adding all items in a taxonomy term to the cart
* @since v1.0.6
*/
function edd_purchase_collection_shortcode($atts, $content = null) {
	extract( shortcode_atts( array(
			'taxonomy' => '',
			'terms' => ''
		), $atts )
	);
	
	return '<a href="' . add_query_arg(array('edd_action' => 'purchase_collection', 'taxonomy' => $taxonomy, 'terms' => $terms)) . '">Purchase All Items</a>';
}
add_shortcode('purchase_collection', 'edd_purchase_collection_shortcode');


// incomplete short code for querying downloads
function edd_downloads_query($atts, $content = null) {
	extract( shortcode_atts( array(
			'category' => '',
			'tags' => '',
			'relation' => 'OR',
			'number' => 10,
			'style' => 'button',
			'color' => 'blue',
			'text' => __('Add to Cart', 'edd')
		), $atts )
	);

	$query = array(
		'post_type' => 'download',
		'posts_per_page' => absint($number),
	);

	if($tags) {
		$query['download_tag'] = $tags;
	}
	if($category) {
		$query['download_category'] = $category;
	}
	
	// allow the query to be manipulated by other plugins
	$query = apply_filters('edd_downloads_query', $query);
	
	$downloads = get_posts($query);
	if($downloads) :
		$display = '<ul class="edd_downloads_list">';
		foreach($downloads as $download) :
			$display .= '<li class="edd_download">' . get_the_title($download->ID) . ' - ' . edd_get_purchase_link($download->ID, $text, $style, $color) . '</li>';
		endforeach;
		$display .= '</ul>';
	else:
		$display = __('No downloads found', 'edd');
	endif;
	return $display;
}
add_shortcode('downloads', 'edd_downloads_query');