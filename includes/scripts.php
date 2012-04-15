<?php

function edd_load_scripts() {

	global $edd_options;

	wp_enqueue_script('jquery');
	
	// load Stripe JS, if enabled
	if(edd_is_gateway_active('stripe')) {
		wp_enqueue_script('stripe', 'https://js.stripe.com/v1/');
	}
	// load ajax JS, if enabled
	if(edd_is_ajax_enabled()) {
		wp_enqueue_script('edd-ajax', EDD_PLUGIN_URL . 'includes/js/edd-ajax.js');
		wp_localize_script('edd-ajax', 'edd_scripts', array(
				'ajaxurl' => admin_url( 'admin-ajax.php' ),
				'ajax_nonce' => wp_create_nonce( 'edd_ajax_nonce' ),
				'no_discount' => __('Please enter a discount code', 'edd'), // blank discount code message
				'discount_applied' => __('Discount Applied', 'edd'), // discount verified message
				'already_in_cart_message' => __('You have already added this item to your cart', 'edd'), // item already in the cart message
				'empty_cart_message' => __('Your cart is empty', 'edd'), // item already in the cart message
				'loading' => __('Loading', 'edd') , // general loading message
				'ajax_loader' => EDD_PLUGIN_URL . 'includes/images/loading.gif', // ajax loading image
				'checkout_page' => get_permalink($edd_options['purchase_page'])
			)
		);
	}
	if(isset($edd_options['jquery_validation']) && is_page($edd_options['purchase_page'])) {
		wp_enqueue_script('jquery-validation', EDD_PLUGIN_URL . 'includes/js/jquery.validate.min.js');
		wp_enqueue_script('edd-validation', EDD_PLUGIN_URL . 'includes/js/form-validation.js');
	}
}
add_action('wp_enqueue_scripts', 'edd_load_scripts');

function edd_register_styles() {
	wp_enqueue_style('edd-styles', EDD_PLUGIN_URL . 'includes/css/edd.css');
}
add_action('wp_enqueue_scripts', 'edd_register_styles');


function edd_load_admin_scripts($hook) {

	global $post, $pagenow, $edd_discounts_page, $edd_payments_page, $edd_settings_page, $edd_reports_page;

	$edd_pages = array($edd_discounts_page, $edd_payments_page, $edd_settings_page, $edd_reports_page);
		
	if( ( !isset($post) || 'download' != $post->post_type ) && !in_array($hook, $edd_pages) )
		return; // load the scripts only on the Download pages
	
	if($hook == 'download_page_edd-reports') {
		wp_enqueue_script('google-charts', 'https://www.google.com/jsapi');
	}
	if($hook == 'download_page_edd-discounts') {
		wp_enqueue_script('jquery-ui-datepicker');
	}
	wp_enqueue_script('media-upload'); 
	wp_enqueue_script('thickbox');
	wp_enqueue_script('edd-admin-scripts', EDD_PLUGIN_URL . 'includes/js/admin-scripts.js');
	wp_enqueue_style('thickbox');
	wp_enqueue_style('jquery-ui-css', 'http://ajax.googleapis.com/ajax/libs/jqueryui/1.8/themes/base/jquery-ui.css', false, '1.8', 'all');
	wp_localize_script('edd-admin-scripts', 'edd_vars', array('post_id' => isset($post->ID) ? $post->ID : null));
}
add_action('admin_enqueue_scripts', 'edd_load_admin_scripts', 100);