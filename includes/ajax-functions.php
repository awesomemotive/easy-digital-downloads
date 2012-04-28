<?php

/****************************************************
* Ajax actions from the frontend are processed here
****************************************************/

// checks whether ajax is enabled
function edd_is_ajax_enabled() {
	global $edd_options;
	if(isset($edd_options['ajax_cart'])) {
		return true;
	}
	return false;
}

function edd_ajax_remove_from_cart() {
	if(isset($_POST['cart_item']) && check_ajax_referer( 'edd_ajax_nonce', 'nonce' )) {
		edd_remove_from_cart($_POST['cart_item']);
		echo 'removed';
	}
	die();
}
add_action('wp_ajax_edd_remove_from_cart', 'edd_ajax_remove_from_cart');
add_action('wp_ajax_nopriv_edd_remove_from_cart', 'edd_ajax_remove_from_cart');

function edd_ajax_add_to_cart() {
	if(isset($_POST['download_id']) && check_ajax_referer( 'edd_ajax_nonce', 'nonce' )) {
		global $post;
		if(!edd_item_in_cart($_POST['download_id'])) {
			$options = is_numeric($_POST['price_id']) ? array('price_id' => $_POST['price_id']) : array();
			$key = edd_add_to_cart($_POST['download_id'], $options);
			$cart_item = edd_get_cart_item_template($key, $_POST['download_id'], true);
			echo $cart_item;
		} else {
			echo 'incart';
		}
	}
	die();
}
add_action('wp_ajax_edd_add_to_cart', 'edd_ajax_add_to_cart');
add_action('wp_ajax_nopriv_edd_add_to_cart', 'edd_ajax_add_to_cart');

function edd_ajax_validate_discount() {
	if(isset($_POST['code']) && check_ajax_referer( 'edd_ajax_nonce', 'nonce' )) {
		if(edd_is_discount_valid($_POST['code'])) {
			$price = edd_get_cart_amount();
			$discounted_price = edd_get_discounted_amount($_POST['code'], $price);
		
			$return = array(
				'msg' => 'valid',
				'amount' => edd_currency_filter(edd_format_amount($discounted_price)),
				'code' => $_POST['code']
			);
			
		} else {
			$return = array(
				'msg' => __('The discount you entered is invalid', 'edd'),
				'code' => $_POST['code']
			);
		}
		echo json_encode($return);
	}
	die();
}
add_action('wp_ajax_edd_apply_discount', 'edd_ajax_validate_discount');
add_action('wp_ajax_nopriv_edd_apply_discount', 'edd_ajax_validate_discount');

function edd_load_checkout_login_fields() {
	echo edd_get_login_fields(); die();
}
add_action('wp_ajax_nopriv_checkout_login', 'edd_load_checkout_login_fields');

function edd_load_checkout_register_fields() {
	echo edd_get_register_fields(); die();
}
add_action('wp_ajax_nopriv_checkout_register', 'edd_load_checkout_register_fields');

// used only in the admin
function edd_ajax_get_download_title() {
	if(isset($_POST['download_id'])) {
		$title = get_the_title($_POST['download_id']);
		if($title) {
			echo $title;
		} else {
			echo 'fail';
		}
	}
	die();
}
add_action('wp_ajax_edd_get_download_title', 'edd_ajax_get_download_title');
add_action('wp_ajax_nopriv_edd_get_download_title', 'edd_ajax_get_download_title');
