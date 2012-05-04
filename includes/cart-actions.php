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

function edd_process_collection_purchase($data) {
	$taxonomy = urldecode($data['taxonomy']);
	$terms = urldecode($data['terms']);
	$cart_items = edd_add_collection_to_cart($taxonomy, $terms);
	wp_redirect(add_query_arg('added', '1', remove_query_arg(array('edd_action', 'taxonomy', 'terms')))); exit;
}
add_action('edd_purchase_collection', 'edd_process_collection_purchase');