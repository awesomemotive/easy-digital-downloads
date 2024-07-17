<?php

namespace EDD\Emails\Templates\Previews;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit; // @codeCoverageIgnore

/**
 * Class Data
 *
 * @since 3.3.0
 * @package EDD
 * @subpackage Emails
 */
class Data {

	/**
	 * An order ID.
	 *
	 * @since 3.3.0
	 * @var int
	 */
	private static $order_id;

	/**
	 * A refund ID.
	 *
	 * @since 3.3.0
	 * @var int
	 */
	private static $refund_id;

	/**
	 * A user ID.
	 *
	 * @since 3.3.0
	 * @var int
	 */
	private static $user_id;

	/**
	 * A user object.
	 *
	 * @since 3.3.0
	 * @var \WP_User
	 */
	private static $user;

	/**
	 * Gets a completed order ID.
	 *
	 * @since 3.3.0
	 * @return int
	 */
	public static function get_complete_order_id() {
		if ( ! self::current_user_can() ) {
			return false;
		}

		if ( is_null( self::$order_id ) ) {
			$orders = edd_get_orders(
				array(
					'number'     => 10,
					'type'       => 'sale',
					'status__in' => edd_get_complete_order_statuses(),
					'fields'     => 'ids',
				)
			);

			self::$order_id = ! empty( $orders ) ? array_rand( array_flip( $orders ) ) : false;
		}

		return self::$order_id;
	}

	/**
	 * Gets a refunded order ID.
	 *
	 * @since 3.3.0
	 * @return int
	 */
	public static function get_refund_id() {
		if ( ! self::current_user_can() ) {
			return false;
		}

		if ( is_null( self::$refund_id ) ) {
			$orders = edd_get_orders(
				array(
					'number' => 10,
					'type'   => 'refund',
					'status' => 'complete',
					'fields' => 'ids',
				)
			);

			self::$refund_id = ! empty( $orders ) ? array_rand( array_flip( $orders ) ) : false;
		}

		return self::$refund_id;
	}

	/**
	 * Gets a user and related data.
	 *
	 * @since 3.3.0
	 * @return array
	 */
	public static function get_user_id_and_data() {
		if ( ! self::current_user_can() ) {
			return false;
		}

		if ( is_null( self::$user_id ) ) {
			self::$user_id = get_current_user_id();
			self::$user    = get_user_by( 'id', self::$user_id );
		}

		return array(
			self::$user_id,
			(array) self::$user->data,
		);
	}

	/**
	 * Determines if the current user has the capability to perform a specific action.
	 *
	 * @since 3.3.0
	 * @return bool True if the current user has the capability, false otherwise.
	 */
	protected static function current_user_can() {
		return current_user_can( 'manage_shop_settings' );
	}
}
