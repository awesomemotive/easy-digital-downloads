<?php
/**
 * Handles compatibility with WordPress performance features.
 *
 * @package EDD\Compatibility\WP
 * @since   3.3.8
 * @copyright Copyright (c) 2025, Sandhills Development, LLC
 * @license http://opensource.org/licenses/gpl-2.0.php GNU Public License
 */

namespace EDD\Compatibility\WP;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/**
 * WordPress performance compatibility
 *
 * @since 3.3.8
 */
class Performance {

	/**
	 * Constructor.
	 *
	 * @since 3.3.8
	 */
	public function __construct() {
		// We only want to run this on the frontend.
		if ( is_admin() ) {
			return;
		}

		add_filter( 'wp_speculation_rules_href_exclude_paths', array( $this, 'exclude_paths_from_speculation' ) );
		add_filter( 'plsr_speculation_rules_href_exclude_paths', array( $this, 'exclude_paths_from_speculation' ) );
	}

	/**
	 * Exclude the checkout page from speculation.
	 *
	 * @since 3.3.8
	 *
	 * @param array $href_exclude_paths The paths to exclude from speculation.
	 * @return array
	 */
	public function exclude_paths_from_speculation( $href_exclude_paths ) {
		// Exclude whatever the checkout slug is.
		$uri = edd_get_option( 'purchase_page', false );

		// If the purchase page is not set, bail.
		if ( ! $uri ) {
			return $href_exclude_paths;
		}

		// Get the slug of the purchase page.
		$slug = get_post_field( 'post_name', $uri );

		// Now exclude the checkout page from speculation.
		$href_exclude_paths[] = '/' . $slug . '/*';

		return $href_exclude_paths;
	}
}
