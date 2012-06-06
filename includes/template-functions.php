<?php
/**
 * Template Functions
 *
 * @package     Easy Digital Downloads
 * @subpackage  Template Functions
 * @copyright   Copyright (c) 2012, Pippin Williamson
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       1.0 
*/


/**
 * Append Purchase Link
 *
 * Automatically appends the purchase link to download content, if enabled.
 *
 * @access      private
 * @since       1.0 
 * @return      string
*/

function edd_append_purchase_link($content) {
	global $post;
	if($post->post_type == 'download' && is_singular() && is_main_query()) {
		if(!get_post_meta($post->ID, '_edd_hide_purchase_link', true)) {			
			$content .= edd_get_purchase_link($post->ID);
		}
	}
	return $content;
}
add_filter('the_content', 'edd_append_purchase_link');


/**
 * Get Purchase Link
 *
 * Returns the purchase link.
 *
 * @access      public
 * @since       1.0 
 * @return      string
*/

function edd_get_purchase_link($download_id = null, $link_text = null, $style = null, $color = null) {
	global $edd_options, $post, $user_ID;

	$page = get_permalink($post->ID); // current page
	$link_args = array('download_id' => $download_id, 'edd_action' => 'add_to_cart');
	$link = add_query_arg($link_args, $page);
	$checkout_url = get_permalink($edd_options['purchase_page']);
	$variable_pricing = get_post_meta($download_id, '_variable_pricing', true);
	
	if(is_null($link_text)) {
		$link_text = get_post_meta($post->ID, '_edd_purchase_text', true) ? get_post_meta($post->ID, '_edd_purchase_text', true) : __('Purchase', 'edd');
	}
	if(is_null($style)) {
		$style = get_post_meta($post->ID, '_edd_purchase_style', true) ? get_post_meta($post->ID, '_edd_purchase_style', true) : 'button';
	}
	if(is_null($color)) {		
		$color = get_post_meta($post->ID, '_edd_purchase_color', true) ? str_replace(' ', '_', get_post_meta($post->ID, '_edd_purchase_color', true)) : 'blue';
	}
	
	$purchase_form = '<form id="edd_purchase_' . $download_id . '" class="edd_download_purchase_form" action="" method="POST">';
		
		if($variable_pricing) {
			$prices = get_post_meta($download_id, 'edd_variable_prices', true);
			$purchase_form .= '<div class="edd_price_options">';
				if($prices) {
					foreach($prices as $key => $price) {
						$checked = '';
						if($key == 0) {
							$checked = 'checked="checked"';
						}
						$purchase_form .= '<input type="radio" ' . $checked . ' name="edd_options[price_id]" id="edd_price_option_' . $download_id . '_' . $key . '" class="edd_price_option_' . $download_id . '" value="' . $key . '"/>&nbsp;';
						$purchase_form .= '<label for="edd_price_option_' . $download_id . '_' . $key . '">' . $price['name'] . ' - ' . edd_currency_filter($price['amount']) . '</label><br/>';
					}
				}
			$purchase_form .= '</div><!--end .edd_price_options-->';
		}
		
		$purchase_form .= '<div class="edd_purchase_submit_wrapper">';
		
			if(edd_has_user_purchased($user_ID, $download_id)) {
				do_action('edd_has_purchased_item_message', $user_ID, $download_id);
			}
				
			$data_variable = $variable_pricing ? ' data-variable-price="yes"' : '';
			
			if( edd_item_in_cart($download_id) ) {
				$button_display = 'style="display:none;"';
				$checkout_display = '';
			} else {
				$button_display = '';
				$checkout_display = 'style="display:none;"';
			}
			
			if($style == 'button') {
				
				$purchase_button = '<span class="edd_button edd_add_to_cart_wrap edd_' . $color . '"' . $button_display . '>';
					$purchase_button .= '<span class="edd_button_outer">';
						$purchase_button .= '<span class="edd_button_inner">';
							$purchase_button .= '<input type="submit" class="edd_button_text edd-submit edd-add-to-cart" name="edd_purchase_download" value="' . $link_text . '" data-action="edd_add_to_cart" data-download-id="' . $download_id . '"' . $data_variable . '/>';
						$purchase_button .= '</span>';
					$purchase_button .= '</span>';
				$purchase_button .= '</span>';
				
				$checkout_link = '<a href="' . $checkout_url . '" class="edd_go_to_checkout edd_button edd_' . $color . '" ' . $checkout_display . '>';
				 	$checkout_link .= '<span class="edd_button_outer"><span class="edd_button_inner">';
						$checkout_link .= '<span class="edd_button_text"><span>' . __('Checkout', 'edd') . '</span></span>';
					$checkout_link .= '</span></span>';
				$checkout_link .= '</a>';
				
				$purchase_form .= $purchase_button . $checkout_link;
				
			} else {
				
				$purchase_text = '<input type="submit" class="edd_submit_plain edd-add-to-cart" name="edd_purchase_download" value="' . $link_text . '" data-action="edd_add_to_cart" data-download-id="' . $download_id . '"' . $data_variable . ' ' . $button_display . '/>';
				
				$checkout_link = '<a href="' . $checkout_url . '" class="edd_go_to_checkout edd_button edd_' . $color . '" ' . $checkout_display . '>';
				 	$checkout_link .= __('Checkout', 'edd');
				$checkout_link .= '</a>';
				
				$purchase_form .= $purchase_text . $checkout_link;
			}
			if( edd_is_ajax_enabled()) {
				$purchase_form .= '<div class="edd-cart-ajax-alert"><img src="' . EDD_PLUGIN_URL . 'includes/images/loading.gif" class="edd-cart-ajax" style="display: none;"/>';
				$purchase_form .= '&nbsp;<span style="display:none;" class="edd-cart-added-alert">' . __('added to your cart', 'edd') . '</span></div>';
			}
	
		$purchase_form .= '</div><!--end .edd_purchase_submit_wrapper-->';	
		$purchase_form .= '<input type="hidden" name="download_id" value="' . $download_id . '">';
		$purchase_form .= '<input type="hidden" name="edd_action" value="add_to_cart">';
	$purchase_form .= '</form><!--end #edd_purchase_' . $download_id . '-->';
		
	return apply_filters('edd_purchase_download_form', $purchase_form, $download_id, $link_text, $style, $color);
}


/**
 * Remove Item URL
 *
 * Returns the URL to remove an item.
 *
 * @access      public
 * @since       1.0 
 * @return      string
*/

function edd_remove_item_url($cart_key, $post, $ajax = false) {
	global $post;
	$current_page = ($ajax || !isset($post->ID)) ? home_url() : get_permalink($post->ID);
	$remove_url = add_query_arg('cart_item', $cart_key, add_query_arg('edd_action', 'remove', $current_page));
	return apply_filters('edd_remove_item_url', $remove_url);
}


/**
 * After Download Content
 *
 * Adds an action to the end of download post content 
 * that can be hooked to by other functions
 *
 * @access      private
 * @since       1.0.8
 * @param       $content string the the_content field of the download object
 * @return      $content string the content with any additional data attached
*/

function edd_after_download_content($content) {
	global $post;
	if($post->post_type == 'download' && is_singular() && is_main_query()) {
		ob_start();
			do_action('edd_after_download_content', $post->ID);
		$content .= ob_get_clean();
	}
	return $content;
}
add_filter('the_content', 'edd_after_download_content');


/**
 * Filter Success Page Content
 *
 * Applies filters to the success page content.
 *
 * @access      private
 * @since       1.0 
 * @return      string
*/

function edd_filter_success_page_content($content) {
	
	global $edd_options;
	
	if(isset($edd_options['success_page']) && isset($_GET['payment-confirmation']) && is_page($edd_options['success_page'])) {
		
		if(has_filter('edd_payment_confirm_' . $_GET['payment-confirmation'])) {
			$content = apply_filters('edd_payment_confirm_' . $_GET['payment-confirmation'], $content);
		}
	}
	return $content;
}
add_filter('the_content', 'edd_filter_success_page_content');


/**
 * Get Button Colors
 *
 * Returns an array of button colors.
 *
 * @access      public
 * @since       1.0 
 * @return      array
*/

function edd_get_button_colors() {
	$colors = array(
		'gray' => __('Gray', 'edd'), 
		'pink' => __('Pink', 'edd'), 
		'blue' => __('Blue', 'edd'), 
		'green' => __('Green', 'edd'), 
		'teal' => __('Teal', 'edd'), 
		'black' => __('Black', 'edd'), 
		'dark gray' => __('Dark Gray', 'edd'), 
		'orange' => __('Orange', 'edd'), 
		'purple' => __('Purple', 'edd'), 
		'slate' => __('Slate', 'edd')
	);
	return apply_filters('edd_button_colors', $colors);
}


/**
 * Show Has Purchased Item Message
 *
 * Prints a notice when user has already purchased the item.
 *
 * @access      private
 * @since       1.0 
 * @return      void
*/

function edd_show_has_purchased_item_message($user_id, $download_id) {
	echo '<p class="edd_has_purchased">' . __('You have already purchased this item, but you may purchase it again.', 'edd') . '</p>';
}
add_action('edd_has_purchased_item_message', 'edd_show_has_purchased_item_message', 10, 2);