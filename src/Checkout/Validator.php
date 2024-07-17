<?php
/**
 * This class provides helper functions for the checkout process.
 *
 * @package EDD
 * @subpackage Checkout
 *
 * @since 3.3.0
 */

namespace EDD\Checkout;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit; // @codeCoverageIgnore

/**
 * Class Validator
 */
class Validator {

	/**
	 * Flag indicating whether the object is set or not.
	 *
	 * @var bool
	 */
	private static $is_object_set;

	/**
	 * Indicates whether the object ID is set or not.
	 *
	 * @var bool
	 */
	private static $is_object_id_set;

	/**
	 * Flag indicating whether the current page is the checkout page or not.
	 *
	 * @since 3.3.0
	 * @var bool
	 */
	private static $is_checkout;

	/**
	 * Flag indicating whether the WP_Query object is set or not.
	 *
	 * @since 3.3.1
	 * @var bool
	 */
	private static $is_wp_query_set;

	/**
	 * Checks if the current page is the checkout page.
	 * Use `edd_is_checkout` instead of using this method directly, as the result is filtered.
	 *
	 * @since 3.3.0
	 * @return bool Returns true if the current page is the checkout page, false otherwise.
	 */
	public static function is_checkout() {
		if ( self::can_return_static_value() ) {
			return self::$is_checkout;
		}

		self::check_wp_query();
		$is_checkout = self::is_checkout_query();

		if ( self::$is_wp_query_set ) {
			self::$is_checkout = $is_checkout;
		}
		self::reset_wp_query();

		return $is_checkout;
	}

	/**
	 * Checks the query for the Validator class.
	 *
	 * @since 3.3.0
	 * @return bool
	 */
	private static function is_checkout_query() {
		// If the current page is the purchase page, return true.
		if ( is_page( edd_get_option( 'purchase_page' ) ) ) {
			return true;
		}

		if ( is_singular() ) {
			return self::has_checkout( get_queried_object_id() );
		}

		if ( edd_doing_ajax() ) {
			$current_page = ! empty( $_POST['current_page'] ) ? absint( $_POST['current_page'] ) : false;

			return $current_page && self::has_checkout( $current_page );
		}

		return false;
	}

	/**
	 * Checks if a post has a checkout block or shortcode.
	 *
	 * @param int $post_id The ID of the post to check.
	 * @return bool True if the post has a checkout block or shortcode, false otherwise.
	 */
	private static function has_checkout( $post_id ) {

		if ( has_block( 'edd/checkout', absint( $post_id ) ) ) {
			return true;
		}

		return has_shortcode( self::get_content( $post_id ), 'download_checkout' );
	}

	/**
	 * Retrieves the content of a post by its ID.
	 *
	 * @param int $post_id The ID of the post.
	 * @return string The content of the post.
	 */
	private static function get_content( $post_id ) {
		$post = get_post( $post_id );

		return $post ? $post->post_content : '';
	}

	/**
	 * Checks the WP_Query object.
	 *
	 * @since 3.3.0
	 */
	private static function check_wp_query() {
		global $wp_query;

		self::$is_object_set    = isset( $wp_query->queried_object );
		self::$is_object_id_set = isset( $wp_query->queried_object_id );
		self::$is_wp_query_set  = ! empty( $wp_query->query );
	}

	/**
	 * Resets the WP_Query object if needed.
	 *
	 * @since 3.3.0
	 */
	private static function reset_wp_query() {
		global $wp_query;

		if ( ! self::$is_object_set ) {
			unset( $wp_query->queried_object );
		}
		if ( ! self::$is_object_id_set ) {
			unset( $wp_query->queried_object_id );
		}
	}

	/**
	 * Checks if the static value can be returned.
	 *
	 * @since 3.3.0
	 * @return bool
	 */
	private static function can_return_static_value() {
		if ( ! is_bool( self::$is_checkout ) ) {
			return false;
		}

		return did_action( 'template_redirect' ) && ! edd_is_doing_unit_tests();
	}
}
