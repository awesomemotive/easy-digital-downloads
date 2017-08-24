<?php

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Cache helper
 *
 * @package     EDD
 * @subpackage  Classes/Cache Helper
 * @copyright   Copyright (c) 2015, Pippin Williamson
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       1.7
*/
class EDD_Cache_Helper {

	/**
	 * Initializes the object instance
	 */
	public function __construct() {

		add_action( 'init', array( $this, 'init' ), 0 );
		add_action( 'admin_notices', array( $this, 'notices' ) );
	}

	/**
	 * Prevent caching on dynamic pages.
	 *
	 * @access public
	 * @return void
	 */
	public function init() {

		if ( false === ( $page_uris = get_transient( 'edd_cache_excluded_uris' ) ) ) {

			$purchase_page = edd_get_option( 'purchase_page', '' );
			$success_page  = edd_get_option( 'success_page', '' );
			if ( empty( $purchase_page ) || empty( $success_page ) ) {
				return;
			}

			$page_uris   = array();

			// Exclude querystring when using page ID
			$page_uris[] = 'p=' . $purchase_page;
			$page_uris[] = 'p=' . $success_page;

			// Exclude permalinks
			$checkout_page  = get_post( $purchase_page );
			$success_page   = get_post( $success_page );

			if ( ! is_null( $checkout_page ) )
				$page_uris[] = '/' . $checkout_page->post_name;
			if ( ! is_null( $success_page ) )
				$page_uris[] = '/' . $success_page->post_name;

			set_transient( 'edd_cache_excluded_uris', $page_uris );
		}

		if ( is_array( $page_uris ) ) {
			foreach( $page_uris as $uri ) {
				if ( strstr( $_SERVER['REQUEST_URI'], $uri ) ) {
					$this->nocache();
					break;
				}
			}
		}

		if( function_exists( 'wp_suspend_cache_addition' ) ) {

			add_action('edd_pre_update_discount',         array( $this, 'w3tc_suspend_cache_addition_pre' ) );
			add_action('edd_pre_insert_discount',         array( $this, 'w3tc_suspend_cache_addition_pre' ) );
			add_action('edd_pre_delete_discount',         array( $this, 'w3tc_suspend_cache_addition_pre' ) );
			add_action('edd_pre_update_discount_status',  array( $this, 'w3tc_suspend_cache_addition_pre' ) );
			add_action('edd_pre_remove_cart_discount',    array( $this, 'w3tc_suspend_cache_addition_pre' ) );

			add_action('edd_post_update_discount',        array( $this, 'w3tc_suspend_cache_addition_post' ) );
			add_action('edd_post_insert_discount',        array( $this, 'w3tc_suspend_cache_addition_post' ) );
			add_action('edd_post_delete_discount',        array( $this, 'w3tc_suspend_cache_addition_post' ) );
			add_action('edd_post_update_discount_status', array( $this, 'w3tc_suspend_cache_addition_post' ) );
			add_action('edd_post_remove_cart_discount',   array( $this, 'w3tc_suspend_cache_addition_post' ) );

		}
	}

	/**
	 * Set nocache constants and headers.
	 *
	 * @access private
	 * @return void
	 */
	private function nocache() {
		if ( ! defined( 'DONOTCACHEPAGE' ) )
			define( "DONOTCACHEPAGE", "true" );

		nocache_headers();
	}

	/**
	 * notices function.
	 *
	 * @access public
	 * @return void
	 */
	public function notices() {

		// W3 Total Cache
		if ( function_exists( 'w3tc_pgcache_flush' ) && function_exists( 'w3_instance' ) ) {

			$config   = w3_instance('W3_Config');
			$enabled  = $config->get_integer( 'dbcache.enabled' );
			$settings = $config->get_array( 'dbcache.reject.sql' );

			if ( $enabled && ! in_array( '_wp_session_', $settings ) ) {
				?>
				<div class="error">
					<p><?php printf( __( 'In order for <strong>database caching</strong> to work with Easy Digital Downloads you must add <code>_wp_session_</code> to the "Ignored query stems" option in W3 Total Cache settings <a href="%s">here</a>.', 'easy-digital-downloads' ), admin_url( 'admin.php?page=w3tc_dbcache' ) ); ?></p>
				</div>
				<?php
			}
		}

	}

	/**
	 * Prevents W3TC from adding to the cache prior to modifying data
	 *
	 * @access public
	 * @return void
	 */
	function w3tc_suspend_cache_addition_pre() {
		wp_suspend_cache_addition(true);
	}

	/**
	 * Prevents W3TC from adding to the cache after modifying data
	 *
	 * @access public
	 * @return void
	 */
	function w3tc_suspend_cache_addition_post() {
		wp_suspend_cache_addition();
	}
}

new EDD_Cache_Helper();
