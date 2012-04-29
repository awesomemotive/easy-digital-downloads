<?php

function edd_install() {
	global $wpdb, $edd_options;
	
	if(!isset($edd_options['purchase_page'])) {	
		$checkout = wp_insert_post(
			array(
				'post_title' => 'Checkout',
				'post_content' => '[download_checkout]',
				'post_status' => 'publish',
				'post_author' => 1,
				'post_type' => 'page'
			)
		);
		$success = wp_insert_post(
			array(
				'post_title' => 'Purchase Confirmation',
				'post_content' => 'Thank you for your purchase!',
				'post_status' => 'publish',
				'post_author' => 1,
				'post_type' => 'page'
			)
		);
		$history = wp_insert_post(
			array(
				'post_title' => 'Purchase History',
				'post_content' => '[download_history]',
				'post_status' => 'publish',
				'post_author' => 1,
				'post_type' => 'page'
			)
		);
	}
	edd_setup_download_post_type();
	edd_setup_download_taxonomies();
	
	// clear permalinks
	flush_rewrite_rules();
}
register_activation_hook(EDD_PLUGIN_FILE, 'edd_install');