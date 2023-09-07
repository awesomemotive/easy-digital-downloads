<?php

namespace EDD\Blocks;

defined( 'ABSPATH' ) || exit;

add_action( 'plugins_loaded', __NAMESPACE__ . '\init_core_blocks', 500 );
/**
 * Initialize the blocks.
 *
 * @since 2.0
 * @return void
 */
function init_core_blocks() {
	if ( ! defined( 'EDD_BLOCKS_DIR' ) ) {
		define( 'EDD_BLOCKS_DIR', plugin_dir_path( __FILE__ ) );
	}

	if ( ! defined( 'EDD_BLOCKS_URL' ) ) {
		define( 'EDD_BLOCKS_URL', plugin_dir_url( __FILE__ ) );
	}

	$files = array(
		'functions',
		'styles',
		'downloads/downloads',
		'forms/forms',
		'orders/orders',
		'terms/terms',
		'checkout/checkout',
	);

	foreach ( $files as $file ) {
		require_once trailingslashit( EDD_BLOCKS_DIR . 'includes' ) . $file . '.php';
	}

	if ( is_admin() ) {
		$admin_files = array(
			'functions',
			'notices',
			'recaptcha',
			'scripts',
			'settings',
		);

		foreach ( $admin_files as $file ) {
			require_once trailingslashit( EDD_BLOCKS_DIR . 'includes/admin' ) . $file . '.php';
		}
	}

	if ( edd_is_pro() ) {
		if ( file_exists( EDD_BLOCKS_DIR . 'pro/pro.php' ) ) {
			require_once EDD_BLOCKS_DIR . 'pro/pro.php';
			Pro\init();
		}
	}
}


add_filter( 'edd_required_pages', __NAMESPACE__ . '\update_core_required_pages' );
/**
 * Update the EDD required pages array to include blocks.
 * This is in the main plugin file so that it's available to the EDD installer.
 *
 * @since 2.0
 */
function update_core_required_pages( $pages ) {

	$pages['confirmation_page']             = array(
		'post_title'   => __( 'Confirmation', 'easy-digital-downloads' ),
		'post_content' => '<!-- wp:paragraph --><p>' . __( 'Thank you for your purchase!', 'easy-digital-downloads' ) . '</p><!-- /wp:paragraph --><!-- wp:edd/confirmation /-->',
	);
	$pages['success_page']                  = array(
		'post_title'   => __( 'Receipt', 'easy-digital-downloads' ),
		'post_content' => '<!-- wp:edd/receipt /-->',
	);
	$pages['purchase_history_page']         = array(
		'post_title'   => __( 'Order History', 'easy-digital-downloads' ),
		'post_content' => '<!-- wp:edd/order-history /-->',
	);
	$pages['purchase_page']['post_content'] = '<!-- wp:edd/checkout /-->';

	return $pages;
}
