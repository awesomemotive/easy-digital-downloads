<?php
/**
 * Admin downloads filters.
 *
 * @package     EDD\Admin\Downloads\Filters
 * @copyright   Copyright (c) 2025, Sandhills Development, LLC
 * @license     https://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       3.5.1
 */

namespace EDD\Admin\Downloads;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit; // @codeCoverageIgnore

use EDD\EventManagement\SubscriberInterface;

/**
 * Admin downloads filters.
 *
 * @since 3.5.1
 */
class Filters implements SubscriberInterface {

	/**
	 * Get the events that this class subscribes to.
	 *
	 * @since 3.5.1
	 * @return array
	 */
	public static function get_subscribed_events(): array {
		return array(
			'display_post_states' => array( 'mark_featured_downloads', 10, 2 ),
			'views_edit-download' => 'add_featured_view',
			'load-edit.php'       => array( 'add_featured_filter', 999 ),
		);
	}

	/**
	 * Mark featured downloads in the list table.
	 *
	 * @since 3.5.1
	 * @param array   $post_states Array of post states.
	 * @param WP_Post $post        Post object.
	 * @return array Modified array of post states.
	 */
	public function mark_featured_downloads( $post_states, $post ) {
		if ( 'download' !== $post->post_type ) {
			return $post_states;
		}

		// Check if the download is featured.
		if ( ! get_post_meta( $post->ID, 'edd_feature_download', true ) ) {
			return $post_states;
		}

		$post_states['edd_featured'] = __( 'Featured', 'easy-digital-downloads' );

		return $post_states;
	}

	/**
	 * Add Featured view to the downloads list table
	 *
	 * @since 3.5.1
	 * @param array $views Array of view links.
	 * @return array Modified array of view links with Featured added.
	 */
	public function add_featured_view( $views ) {

		$current = filter_input( INPUT_GET, 'featured', FILTER_VALIDATE_INT );
		if ( ! empty( $current ) ) {
			return $views;
		}

		// Count featured downloads.
		$featured_count = $this->get_featured_downloads_count();
		if ( ! $featured_count ) {
			return $views;
		}

		$featured_url = add_query_arg( array( 'featured' => '1' ), admin_url( 'edit.php?post_type=download' ) );

		$views['featured'] = sprintf(
			'<a href="%s">%s <span class="count">(%d)</span></a>',
			esc_url( $featured_url ),
			__( 'Featured', 'easy-digital-downloads' ),
			$featured_count
		);

		return $views;
	}

	/**
	 * Add featured filter to the downloads list table.
	 *
	 * @since 3.5.1
	 * @return void
	 */
	public function add_featured_filter() {
		add_filter( 'request', array( $this, 'filter_downloads' ) );
	}

	/**
	 * Filter downloads by featured.
	 *
	 * @since 3.5.1
	 * @param array $vars Query vars.
	 * @return array Modified query vars.
	 */
	public function filter_downloads( $vars ) {
		// Check if we're viewing the download post type and featured filter is active.
		if ( ! isset( $vars['post_type'] ) || 'download' !== $vars['post_type'] ) {
			return $vars;
		}

		// Check if featured filter is requested.
		if ( isset( $_GET['featured'] ) && '1' === $_GET['featured'] ) {
			$vars = array_merge(
				$vars,
				array(
					'meta_query' => array(
						array(
							'key'     => 'edd_feature_download',
							'value'   => '1',
							'compare' => '=',
						),
					),
				)
			);
		}

		return $vars;
	}

	/**
	 * Get count of featured downloads.
	 *
	 * @since 3.5.1
	 * @return int Number of featured downloads.
	 */
	private function get_featured_downloads_count() {
		$query = new \WP_Query(
			array(
				'post_type'      => 'download',
				'post_status'    => array( 'publish', 'draft', 'pending', 'private', 'future' ),
				'meta_query'     => array(
					array(
						'key'     => 'edd_feature_download',
						'value'   => '1',
						'compare' => '=',
					),
				),
				'posts_per_page' => -1,
				'fields'         => 'ids',
			)
		);

		return $query->found_posts;
	}
}
