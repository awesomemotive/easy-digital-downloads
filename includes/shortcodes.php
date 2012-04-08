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
function edd_download_history($atts, $content = null) {
	extract( shortcode_atts( array(
			'id' => ''
		), $atts )
	);
	
	global $user_ID;
	
	if(is_user_logged_in()) {
		$purchases = edd_get_users_purchases($user_ID);
		
		$list = '<div id="edd_user_history" class="edd_user_downloads_wrap">';
			$list .= '<ul id="edd_user_downloads" class="edd_list">';
		
			if($purchases) {
				foreach($purchases as $purchased_download) {
					$payment_meta = get_post_meta($purchased_download->ID, '_edd_payment_meta', true);
					$downloads = maybe_unserialize($payment_meta['downloads']);
					foreach($downloads as $download) {
						$list .= '<li class="edd_purchased_download">';
							$list .= get_the_title($download);
							$list .= '<ul class="edd_purchased_download_files">';
								$download_files = get_post_meta($download, 'edd_download_files', true);
								foreach($download_files as $filekey => $file) {
									$download_url = edd_get_download_file_url($payment_meta['key'], $payment_meta['email'], $filekey, $download);
									$list .= '<li class="edd_download_file"><a href="' . $download_url . '" class="edd_download_file_link">' . $file['name'] . '</a></li>';
								}
							$list .= '</ul>';
						$list .= '</li>';
					}
				}
			} else {
				$list .= '<li>' . __('You have not purchased any downloads', 'edd') . '</li>';
			}
		
			$list .= '</ul>';
		$list .= '</div>';
		
		return apply_filters('edd_purchase_history_html', $list);
	}
}
add_shortcode('download_history', 'edd_download_history');

// show the checkout form
function edd_checkout_form_shortcode($atts, $content = null) {
	return edd_checkout_form();
}
add_shortcode('download_checkout', 'edd_checkout_form_shortcode');

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