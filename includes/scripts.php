<?php
/**
 * Scripts
 *
 * @package     Easy Digital Downloads
 * @subpackage  Scripts
 * @copyright   Copyright (c) 2012, Pippin Williamson
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       1.0 
*/


/**
 * Load Scripts
 *
 * Enqueues the required scripts.
 *
 * @access      private
 * @since       1.0 
 * @return      void
*/

function edd_load_scripts() {

	global $edd_options, $post;

	wp_enqueue_script('jquery');
	
	// Get position in cart of current download
	if(isset($post->ID)) {
	    $position = edd_get_item_position_in_cart($post->ID);
	}
		
	// Load AJAX scripts, if enabled
	if( edd_is_ajax_enabled()) {
		wp_enqueue_script('edd-ajax', EDD_PLUGIN_URL . 'includes/js/edd-ajax.js');
		wp_localize_script('edd-ajax', 'edd_scripts', array(
				'ajaxurl' 					=> admin_url( 'admin-ajax.php' ),
				'ajax_nonce' 				=> wp_create_nonce( 'edd_ajax_nonce' ),
				'no_discount' 				=> __('Please enter a discount code', 'edd'), // blank discount code message
				'discount_applied' 			=> __('Discount Applied', 'edd'), // discount verified message
				'no_email' 					=> __('Please enter an email address before applying a discount code', 'edd'),
				'position_in_cart' 			=> isset($position) ? $position : -1,
				'already_in_cart_message' 	=> __('You have already added this item to your cart', 'edd'), // item already in the cart message
				'empty_cart_message' 		=> __('Your cart is empty', 'edd'), // item already in the cart message
				'loading' 					=> __('Loading', 'edd') , // general loading message
				'ajax_loader' 				=> EDD_PLUGIN_URL . 'includes/images/loading.gif', // ajax loading image
				'checkout_page' 			=> isset($edd_options['purchase_page']) ? get_permalink($edd_options['purchase_page']) : '',
				'permalinks' 				=> get_option( 'permalink_structure' ) ? '1' : '0'
			)
		);
	}
	
	// Load jQuery validation
	if(isset($edd_options['jquery_validation']) && is_page($edd_options['purchase_page'])) {
		wp_enqueue_script('jquery-validation', EDD_PLUGIN_URL . 'includes/js/jquery.validate.min.js');
		wp_enqueue_script('edd-validation', EDD_PLUGIN_URL . 'includes/js/form-validation.js');
		$required = array( 'firstname' => true, 'lastname' => true );
		wp_localize_script('edd-validation', 'edd_scripts_validation', apply_filters('edd_scripts_validation',$required));
	}
	wp_enqueue_script('edd-checkout-global', EDD_PLUGIN_URL . 'includes/js/edd-checkout-global.js');
}
add_action('wp_enqueue_scripts', 'edd_load_scripts');


/**
 * Register Styles
 *
 * Checks the styles option and hooks the required filter.
 *
 * @access      private
 * @since       1.0 
 * @return      void
*/

function edd_register_styles() {
	global $edd_options;
	if(!isset($edd_options['disable_styles'])) {
		wp_enqueue_style('edd-styles', EDD_PLUGIN_URL . 'includes/css/edd.css');
	}
}
add_action('wp_enqueue_scripts', 'edd_register_styles');


/**
 * Load Admin Scripts
 *
 * Enqueues the required admin scripts.
 *
 * @access      private
 * @since       1.0 
 * @return      void
*/

function edd_load_admin_scripts($hook) {

	global $post, $pagenow, $edd_discounts_page, $edd_payments_page, $edd_settings_page, $edd_reports_page, $edd_add_ons_page;

	$edd_pages = array($edd_discounts_page, $edd_payments_page, $edd_settings_page, $edd_reports_page, $edd_add_ons_page);
	$edd_cpt   = apply_filters( 'edd_load_scripts_for_these_types', array( 'download', 'edd_payment' ) );

	if ( ! in_array( $hook, $edd_pages ) && ! is_object( $post ) )
	    return;

	if ( is_object( $post ) && ! in_array( $post->post_type, $edd_cpt ) )
	    return;
	
	if($hook == 'download_page_edd-reports') {
		wp_enqueue_script('google-charts', 'https://www.google.com/jsapi');
	}
	if($hook == 'download_page_edd-discounts') {
		wp_enqueue_script('jquery-ui-datepicker');
	}
	if($hook == $edd_settings_page) {
		wp_enqueue_style('colorbox', EDD_PLUGIN_URL . 'includes/css/colorbox.css');
		wp_enqueue_script('colorbox', EDD_PLUGIN_URL . 'includes/js/jquery.colorbox-min.js', array('jquery'), '1.3.19.3');
	}
	wp_enqueue_script('media-upload'); 
	wp_enqueue_script('thickbox');
	wp_enqueue_script('edd-admin-scripts', EDD_PLUGIN_URL . 'includes/js/admin-scripts.js');	
	wp_localize_script('edd-admin-scripts', 'edd_vars', array(
        'post_id' 			=> isset($post->ID) ? $post->ID : null,
        'add_new_download' 	=> __('Add New Download', 'edd'), // thickbox title
        'use_this_file' 	=> __('Use This File','edd'), // "use this file" button
        'quick_edit_warning'=> __('Sorry, not available for variable priced products.', 'edd'),
        'delete_payment' 	=> __('Are you sure you wish to delete this payment?', 'edd')
    ));
	wp_enqueue_style('thickbox');
	wp_enqueue_style('jquery-ui-css', 'http://ajax.googleapis.com/ajax/libs/jqueryui/1.8/themes/base/jquery-ui.css', false, '1.8', 'all');
	wp_enqueue_style('edd-admin', EDD_PLUGIN_URL . 'includes/css/edd-admin.css');
}
add_action('admin_enqueue_scripts', 'edd_load_admin_scripts', 100);


/**
 * Admin Downloads Icon
 *
 * Echoes the CSS for the downloads post type icon.
 *
 * @access      private
 * @since       1.0 
 * @return      void
*/

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