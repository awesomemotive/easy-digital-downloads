<?php
/**
 * Cache helper
 *
 * @package     EDD
 * @subpackage  Core
 * @copyright   Copyright (c) 2018, Easy Digital Downloads, LLC
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       1.7
*/

// Exit if accessed directly
defined( 'ABSPATH' ) || exit;

/**
 * EDD_Cache_Helper class.
 *
 * @since 1.7
 */
class EDD_Cache_Helper {

	/**
	 * Constructor.
	 *
	 * @since 1.7
	 */
	public function __construct() {
		add_action( 'init', array( $this, 'init' ), 0 );
		add_action( 'admin_notices', array( $this, 'notices' ) );
	}

	/**
	 * Prevent caching on dynamic pages.
	 *
	 * @since 1.7
	 */
	public function init() {
		$page_uris = get_transient( 'edd_cache_excluded_uris' );

		if ( false === $page_uris ) {
			$purchase_page = edd_get_option( 'purchase_page', '' );
			$success_page  = edd_get_option( 'success_page', '' );

			// Bail if purchase or success page has not been set.
			if ( empty( $purchase_page ) || empty( $success_page ) ) {
				return;
			}

			$page_uris = array();

			// Exclude query string when using page ID.
			$page_uris[] = 'p=' . $purchase_page;
			$page_uris[] = 'p=' . $success_page;

			// Exclude permalinks.
			$checkout_page = get_post( $purchase_page );
			$success_page  = get_post( $success_page );

			if ( ! is_null( $checkout_page ) ) {
				$page_uris[] = '/' . $checkout_page->post_name;
			}

			if ( ! is_null( $success_page ) ) {
				$page_uris[] = '/' . $success_page->post_name;
			}

			set_transient( 'edd_cache_excluded_uris', $page_uris );
		}

		if ( is_array( $page_uris ) ) {
			foreach ( $page_uris as $uri ) {
				if ( strstr( $_SERVER['REQUEST_URI'], $uri ) ) {
					$this->nocache();
					break;
				}
			}
		}
	}

	/**
	 * Set nocache constants and headers.
	 *
	 * @since 1.7
	 * @access private
	 */
	private function nocache() {
		if ( ! defined( 'DONOTCACHEPAGE' ) ) {
			define( 'DONOTCACHEPAGE', 'true' );
		}

		nocache_headers();
	}

	/**
	 * Admin notices.
	 *
	 * @since 1.7
	 */
	public function notices() {

		// W3 Total Cache.
		if ( function_exists( 'w3tc_pgcache_flush' ) && function_exists( 'w3_instance' ) ) {
			$config   = w3_instance( 'W3_Config' );
			$enabled  = $config->get_integer( 'dbcache.enabled' );
			$settings = $config->get_array( 'dbcache.reject.sql' );

			if ( $enabled && ! in_array( '_wp_session_', $settings, true ) ) {
				?>
				<div class="error">
					<p>
						<?php
						printf(
							__( 'In order for <strong>database caching</strong> to work with Easy Digital Downloads you must add <code>_wp_session_</code> to the "Ignored query stems" option in W3 Total Cache settings <a href="%s">here</a>.', 'easy-digital-downloads' ),
							esc_url( admin_url( 'admin.php?page=w3tc_dbcache' ) )
						);
						?>
					</p>
				</div>
				<?php
			}
		}

	}

	/**
	 * Prevents W3TC from adding to the cache prior to modifying data.
	 *
	 * @since 1.7
	 * @since 3.0.4 Removed the cache suspend call.
	 */
	public function w3tc_suspend_cache_addition_pre() {
		// This function does nothing as of EDD 3.0.4, it is only left here to prevent fatal errors in case it was used.
	}

	/**
	 * Prevents W3TC from adding to the cache after modifying data.
	 *
	 * @since 1.7
	 * @since 3.0.4 Removed the cache suspend call.
	 */
	public function w3tc_suspend_cache_addition_post() {
		// This function does nothing as of EDD 3.0.4, it is only left here to prevent fatal errors in case it was used.
	}
}
