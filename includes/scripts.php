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
		$required = array( 'firstname' => true, 'lastname' => true );
		wp_localize_script('edd-validation', 'edd_scripts_validation', apply_filters('edd_scripts_validation',$required));
	}
}
add_action('wp_enqueue_scripts', 'edd_load_scripts');

function edd_register_styles() {
	global $edd_options;
	if(!isset($edd_options['disable_styles'])) {
		wp_enqueue_style('edd-styles', EDD_PLUGIN_URL . 'includes/css/edd.css');
	}
}
add_action('wp_enqueue_scripts', 'edd_register_styles');


function edd_load_admin_scripts($hook) {

	global $post, $pagenow, $edd_discounts_page, $edd_payments_page, $edd_settings_page, $edd_reports_page, $edd_add_ons_page;

	$edd_pages = array($edd_discounts_page, $edd_payments_page, $edd_settings_page, $edd_reports_page, $edd_add_ons_page);
		
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
	wp_localize_script('edd-admin-scripts', 'edd_vars', array(
        'post_id' => isset($post->ID) ? $post->ID : null,
        'add_new_download' => __('Add New Download', 'edd'), // thickbox title
        'use_this_file' => __('Use This File','edd'), // "use this file" button
    ));
	wp_enqueue_style('thickbox');
	wp_enqueue_style('jquery-ui-css', 'http://ajax.googleapis.com/ajax/libs/jqueryui/1.8/themes/base/jquery-ui.css', false, '1.8', 'all');
	wp_enqueue_style('edd-admin', EDD_PLUGIN_URL . 'includes/css/edd-admin.css');
}
add_action('admin_enqueue_scripts', 'edd_load_admin_scripts', 100);

// adds edd custom post type icon
function edd_admin_downloads_icon() {
    global $post_type;
	$icon_url = EDD_PLUGIN_URL . 'includes/images/edd-icon.png';	
	?>
	<style type="text/css" media="screen">
		body #adminmenu #menu-posts-download div.wp-menu-image { background:transparent url( "<?php echo $icon_url; ?>" ) no-repeat 7px -32px; }
		body #adminmenu #menu-posts-download:hover div.wp-menu-image, 
		body #adminmenu #menu-posts-download.wp-has-current-submenu div.wp-menu-image { background:transparent url( "<?php echo $icon_url; ?>" ) no-repeat 7px 0; }
		<?php if (( isset($_GET['post_type'])) && ($_GET['post_type'] == 'download') || ($post_type == 'download')) : ?>
        #icon-edit { background:transparent url("<?php echo EDD_PLUGIN_URL .'includes/images/edd-cpt.png'; ?>") no-repeat; }		
        <?php endif; ?>
	</style>
    <?php
}
add_action('admin_head','edd_admin_downloads_icon');