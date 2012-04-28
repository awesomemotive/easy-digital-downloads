<?php

function edd_cart_get_actions() {
	if(isset($_GET['edd_action'])) {
		do_action('edd_' . $_GET['edd_action'], $_GET);		
	}
}
add_action('init', 'edd_cart_get_actions');

function edd_cart_post_actions() {
	if(isset($_POST['edd_action'])) {
		do_action('edd_' . $_POST['edd_action'], $_POST);		
	}
}
add_action('init', 'edd_cart_post_actions');

function edd_process_add_to_cart($data) {
	$download_id = $data['download_id'];
	$options = isset($data['edd_options']) ? $data['edd_options'] : array(); 
	$cart = edd_add_to_cart($download_id, $options);
}
add_action('edd_add_to_cart', 'edd_process_add_to_cart');

function edd_process_remove_fromt_cart($data) {
	$cart_key = $_GET['cart_item'];
	$cart = edd_remove_from_cart($cart_key);
}
add_action('edd_remove', 'edd_process_remove_fromt_cart');