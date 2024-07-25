<?php
/**
 * Represents purchase data for Easy Digital Downloads.
 *
 * This class provides methods to handle and manipulate purchase data.
 * It is used to store information related to a specific purchase.
 *
 * @package EDD
 * @subpackage Sessions
 */

namespace EDD\Sessions;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit; // @codeCoverageIgnore

/**
 * PurchaseData class.
 */
class PurchaseData {

	/**
	 * Sets the purchase data for a user.
	 *
	 * @since 3.3.2
	 * @param array $valid_data The valid data for the purchase.
	 * @param array $user       The user data.
	 * @return array The purchase data.
	 */
	public static function set( $valid_data, $user ) {

		$user_info     = self::get_user_info( $valid_data, $user );
		$purchase_data = array(
			'downloads'    => edd_get_cart_contents(),
			'fees'         => edd_get_cart_fees(), // Any arbitrary fees that have been added to the cart.
			'subtotal'     => edd_get_cart_subtotal(), // Amount before taxes and discounts.
			'discount'     => edd_get_cart_discounted_amount(), // Discounted amount.
			'tax'          => edd_get_cart_tax(), // Taxed amount.
			'tax_rate'     => self::get_tax_rate( $valid_data ),
			'price'        => edd_get_cart_total(), // Amount after taxes.
			'user_email'   => $user['user_email'],
			'purchase_key' => self::get_purchase_key( $user ),
			'user_info'    => stripslashes_deep( $user_info ),
			'post_data'    => $_POST,
			'gateway'      => $valid_data['gateway'],
			'card_info'    => $valid_data['cc_info'],
			'cart_details' => edd_get_cart_content_details(),
		);

		// Add the user data for hooks.
		$valid_data['user'] = $user;

		/**
		 * Allow themes and plugins to hook to the purchase data.
		 *
		 * @param array $post_data  The POST data.
		 * @param array $user_info  The user information.
		 * @param array $valid_data The valid data.
		 */
		do_action( 'edd_checkout_before_gateway', $_POST, $user_info, $valid_data );

		$purchase_data['gateway_nonce'] = wp_create_nonce( 'edd-gateway' );

		/**
		 * Allow the purchase data to be modified before it is sent to the gateway.
		 *
		 * @param array $purchase_data The purchase data.
		 * @param array $valid_data    The valid data.
		 */
		$purchase_data = apply_filters(
			'edd_purchase_data_before_gateway',
			$purchase_data,
			$valid_data
		);

		edd_set_purchase_session( $purchase_data );

		return $purchase_data;
	}

	/**
	 * Retrieves the purchase data from the session, or attampts to generate it if it does not exist.
	 *
	 * @since 3.3.2
	 * @param bool|null $doing_ajax Whether the request is being made via AJAX.
	 * @return mixed The purchase data.
	 */
	public static function get( $doing_ajax = null ) {
		$session = edd_get_purchase_session();
		if ( ! empty( $session ) ) {
			return $session;
		}

		$valid_data = edd_purchase_form_validate_fields();
		if ( empty( $valid_data ) ) {
			return $session;
		}

		$user = edd_get_purchase_form_user( $valid_data, $doing_ajax );

		return self::set( $valid_data, $user );
	}

	/**
	 * Retrieves the tax rate for the purchase data.
	 *
	 * @since 3.3.2
	 * @param array $valid_data The valid data for the purchase.
	 * @return float The tax rate for the purchase data.
	 */
	private static function get_tax_rate( $valid_data ) {
		if ( ! edd_use_taxes() ) {
			return 0;
		}

		$card_country = isset( $valid_data['cc_info']['card_country'] ) ? $valid_data['cc_info']['card_country'] : false;
		$card_state   = isset( $valid_data['cc_info']['card_state'] ) ? $valid_data['cc_info']['card_state'] : false;
		$card_zip     = isset( $valid_data['cc_info']['card_zip'] ) ? $valid_data['cc_info']['card_zip'] : false;

		return edd_get_cart_tax_rate( $card_country, $card_state, $card_zip );
	}

	/**
	 * Retrieves the purchase key for an order.
	 *
	 * @since 3.3.2
	 * @param array $user The user for whom to retrieve the purchase key.
	 * @return string The purchase key for the user.
	 */
	private static function get_purchase_key( $user ) {
		$existing_payment = EDD()->session->get( 'edd_resume_payment' );
		if ( ! empty( $existing_payment ) ) {
			$order = edd_get_order( $existing_payment );
			if ( ! empty( $order->payment_key ) && $order->is_recoverable() ) {
				return $order->payment_key;
			}
		}

		return edd_generate_order_payment_key( $user['user_email'] );
	}

	/**
	 * Retrieves user information based on the provided valid data and user object.
	 *
	 * @since 3.3.2
	 * @param array $valid_data The valid data for the user.
	 * @param array $user       The user data.
	 * @return array
	 */
	private static function get_user_info( $valid_data, $user ) {
		return array(
			'id'         => $user['user_id'],
			'email'      => $user['user_email'],
			'first_name' => $user['user_first'],
			'last_name'  => $user['user_last'],
			'discount'   => $valid_data['discount'],
			'address'    => ! empty( $user['address'] ) ? $user['address'] : false,
		);
	}
}
