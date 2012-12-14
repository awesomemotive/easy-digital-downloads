<?php
/**
 * Install Function
 *
 * @package     Easy Digital Downloads
 * @subpackage  Install Function
 * @copyright   Copyright (c) 2012, Pippin Williamson
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       1.0
*/

// Exit if accessed directly
if ( !defined( 'ABSPATH' ) ) exit;

/**
 * Install
 *
 * Runs on plugin install.
 *
 * @access      private
 * @since       1.0
 * @return      void
*/

function edd_install() {
	global $wpdb, $edd_options;

	// Checks if the purchase page option exists
	if( !isset( $edd_options['purchase_page'] ) ) {
	    // Checkout Page
		$checkout = wp_insert_post(
			array(
				'post_title'     => __('Checkout', 'edd'),
				'post_content'   => '[download_checkout]',
				'post_status'    => 'publish',
				'post_author'    => 1,
				'post_type'      => 'page',
				'comment_status' => 'closed'
			)
		);
		// Purchase Confirmation (Success) Page
		$success = wp_insert_post(
			array(
				'post_title'     => __('Purchase Confirmation', 'edd'),
				'post_content'   => __('Thank you for your purchase!', 'edd'),
				'post_status'    => 'publish',
				'post_author'    => 1,
				'post_type'      => 'page',
				'comment_status' => 'closed'
			)
		);
		// Failed Purchase Page
		$failed = wp_insert_post(
			array(
				'post_title'     => __( 'Transaction Failed', 'edd' ),
				'post_content'   => __( 'Your transaction failed, please try again or contact site support.', 'edd' ),
				'post_status'    => 'publish',
				'post_author'    => 1,
				'post_type'      => 'page',
				'post_parent'    => $checkout,
				'comment_status' => 'closed'
			)
		);
		// Purchase History (History) Page
		$history = wp_insert_post(
			array(
				'post_title'     => __('Purchase History', 'edd'),
				'post_content'   => '[download_history]',
				'post_status'    => 'publish',
				'post_author'    => 1,
				'post_type'      => 'page',
				'post_parent'    => $checkout,
				'comment_status' => 'closed'
			)
		);
	}

	// Setup the Downloads Custom Post Type
	edd_setup_edd_post_types();

	// Setup the Download Taxonomies
	edd_setup_download_taxonomies();

	// Clear the permalinks
	flush_rewrite_rules();
}
register_activation_hook(EDD_PLUGIN_FILE, 'edd_install');