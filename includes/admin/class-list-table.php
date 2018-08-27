<?php
/**
 * List Table Base Class.
 *
 * @package     EDD
 * @subpackage  Admin
 * @copyright   Copyright (c) 2018, Easy Digital Downloads, LLC
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       3.0
 */
namespace EDD\Admin;

// Exit if accessed directly
defined( 'ABSPATH' ) || exit;

// Load WP_List_Table if not loaded
if ( ! class_exists( 'WP_List_Table' ) ) {
	require_once ABSPATH . 'wp-admin/includes/class-wp-list-table.php';
}

/**
 * Implements a base list table class to be extended by core.
 *
 * @since 3.0
 * @abstract
 */
abstract class List_Table extends \WP_List_Table {

	/**
	 * Number of results to show per page.
	 *
	 * @since 3.0
	 * @var   int
	 */
	public $per_page = 30;

	/**
	 * Counts.
	 *
	 * @since 3.0
	 * @var   array
	 */
	public $counts = array(
		'total' => 0
	);

	/**
	 * Get a request var, or return the default if not set.
	 *
	 * @since 3.0
	 *
	 * @param string $var
	 * @param mixed  $default
	 * @return mixed Un-sanitized request var
	 */
	public function get_request_var( $var = '', $default = false ) {
		return isset( $_REQUEST[ $var ] )
			? $_REQUEST[ $var ]
			: $default;
	}

	/**
	 * Get a status request var, if set.
	 *
	 * @since 3.0
	 *
	 * @param mixed $default
	 * @return string
	 */
	protected function get_status( $default = '' ) {
		return sanitize_key( $this->get_request_var( 'status', $default ) );
	}

	/**
	 * Retrieve the current page number.
	 *
	 * @since 3.0
     *
	 * @return int Current page number.
	 */
	protected function get_paged() {
		return absint( $this->get_request_var( 'paged', 1 ) );
	}

	/**
	 * Retrieve the current page number.
	 *
	 * @since 3.0
     *
	 * @return int Current page number.
	 */
	protected function get_search() {
		return urldecode( trim( $this->get_request_var( 's', '' ) ) );
	}

	/**
	 * Retrieve the view types
	 *
	 * @since 1.4
	 *
	 * @return array $views All the views available
	 */
	public function get_views() {

		// Get the current status
		$current = $this->get_status();

		// Args to remove
		$remove = array( 'edd-message', 'status', 'paged', '_wpnonce' );

		// Base URL
		$url = remove_query_arg( $remove, $this->get_base_url() );

		// Is all selected?
		$class = in_array( $current, array( '', 'all' ), true )
			? ' class="current"'
			: '';

		// All
		$count = '&nbsp;<span class="count">(' . esc_attr( $this->counts['total'] ) . ')</span>';
		$label = __( 'All', 'easy-digital-downloads' ) . $count;
		$views = array(
			'all' => sprintf( '<a href="%s"%s>%s</a>', $url, $class, $label ),
		);

		// Remove total from counts array
		$counts = $this->counts;
		unset( $counts['total'] );

		// Loop through statuses.
		if ( ! empty( $counts ) ) {
			foreach ( $counts as $status => $count ) {
				$count_url = add_query_arg( array(
					'status' => $status,
					'paged'  => false,
				), $url );

				$class = ( $current === $status )
					? ' class="current"'
					: '';

				$count = '&nbsp;<span class="count">(' . absint( $this->counts[ $status ] ) . ')</span>';

				$label            = edd_get_status_label( $status ) . $count;
				$views[ $status ] = sprintf( '<a href="%s"%s>%s</a>', $count_url, $class, $label );
			}
		}

		return $views;
	}
}