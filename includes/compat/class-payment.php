<?php
/**
 * Backwards Compatibility Handler for Payments.
 *
 * @package     EDD
 * @subpackage  Compat
 * @copyright   Copyright (c) 2018, Easy Digital Downloads, LLC
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       3.0.0
 */
namespace EDD\Compat;

// Exit if accessed directly
defined( 'ABSPATH' ) || exit;

/**
 * Payment Class.
 *
 * EDD 3.0 moves away from storing payment data in wp_posts. This class handles all the backwards compatibility for the
 * transition to custom tables.
 *
 * @since 3.0
 */
class Payment extends Base {

	/**
	 * Holds the component for which we are handling back-compat. There is a chance that two methods have the same name
	 * and need to be dispatched to completely other methods. When a new instance of Back_Compat is created, a component
	 * can be passed to the constructor which will allow __call() to dispatch to the correct methods.
	 *
	 * @since 3.0
	 * @access protected
	 * @var string
	 */
	protected $component = 'payment';

	/**
	 * Backwards compatibility hooks for customers.
	 *
	 * @since 3.0
	 * @access protected
	 */
	protected function hooks() {
		add_filter( 'query', array( $this, 'wp_count_posts' ), 10, 1 );
	}

	/**
	 * Backwards compatibility layer for wp_count_posts().
	 *
	 * This is here for backwards compatibility purposes with the migration to custom tables in EDD 3.0.
	 *
	 * @since 3.0
	 *
	 * @param string $query SQL request.
	 *
	 * @return string $request Rewritten SQL query.
	 */
	public function wp_count_posts( $query ) {
		global $wpdb;

		$expected = "SELECT post_status, COUNT( * ) AS num_posts FROM {$wpdb->posts} WHERE post_type = 'edd_payment' GROUP BY post_status";

		if ( $expected === $query ) {
			$query = "SELECT status AS post_status, COUNT( * ) AS num_posts FROM {$wpdb->edd_orders} GROUP BY post_status";
		}

		return $query;
	}

}