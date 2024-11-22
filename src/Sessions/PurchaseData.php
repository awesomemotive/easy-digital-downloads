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
	 * Starts the purchase data for a user.
	 *
	 * @since 3.3.5
	 * @param bool|null $doing_ajax Whether the request is being made via AJAX.
	 * @return null|array The purchase data.
	 */
	public static function start( $doing_ajax = null ) {
		$valid_data = edd_purchase_form_validate_fields();

		do_action( 'edd_checkout_error_checks', $valid_data, $_POST );

		if ( empty( $valid_data ) ) {
			return null;
		}

		$user = edd_get_purchase_form_user( $valid_data, $doing_ajax );

		do_action( 'edd_checkout_user_error_checks', $user, $valid_data, $_POST );

		if ( empty( $user ) ) {
			return null;
		}

		return self::set( $valid_data, $user );
	}

	/**
	 * Sets the purchase data for a user.
	 *
	 * @since 3.3.2
	 * @param array $valid_data The valid data for the purchase.
	 * @param array $user       The user data.
	 * @return null|array The purchase data.
	 */
	public static function set( $valid_data, $user ) {

		$user_info = self::get_user_info( $valid_data, $user );
		self::maybe_update_customer( $user_info );

		$purchase_data = array(
			'downloads'    => edd_get_cart_contents(),
			'fees'         => edd_get_cart_fees(), // Any arbitrary fees that have been added to the cart.
			'subtotal'     => edd_get_cart_subtotal(), // Amount before taxes and discounts.
			'discount'     => edd_get_cart_discounted_amount(), // Discounted amount.
			'tax'          => edd_get_cart_tax(), // Taxed amount.
			'tax_rate'     => self::get_tax_rate( $valid_data ),
			'price'        => edd_get_cart_total(), // Amount after taxes.
			'user_email'   => $user_info['email'],
			'purchase_key' => self::get_purchase_key( $user_info['email'] ),
			'user_info'    => stripslashes_deep( $user_info ),
			'gateway'      => $valid_data['gateway'],
			'cart_details' => edd_get_cart_content_details(),
			'date'         => false, // unused key, but kept for backwards compatibility.
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

		if ( empty( $purchase_data['price'] ) ) {
			// Revert to manual.
			$purchase_data['gateway'] = 'manual';
			$_POST['edd-gateway']     = 'manual';
		}

		edd_set_purchase_session( $purchase_data );

		// Send the card info and post data back to the purchase data, even though it's not stored in the session.
		$purchase_data['card_info'] = $valid_data['cc_info'] ?? array();
		$purchase_data['post_data'] = $_POST;

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

		// Generally we expected the purchase data to be in the session, but if not, log it.
		edd_debug_log( 'Purchase data not found in session. Attempting to generate it.' );

		return self::start( $doing_ajax );
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
	 * @param string $email The email address for the user.
	 * @return string The purchase key for the user.
	 */
	private static function get_purchase_key( $email ) {
		$existing_payment = EDD()->session->get( 'edd_resume_payment' );
		if ( ! empty( $existing_payment ) ) {
			$order = edd_get_order( $existing_payment );
			if ( ! empty( $order->payment_key ) && $order->is_recoverable() ) {
				return $order->payment_key;
			}
		}

		return edd_generate_order_payment_key( $email );
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
			'id'         => ! empty( $user['user_id'] ) ? $user['user_id'] : false,
			'email'      => ! empty( $user['user_email'] ) ? $user['user_email'] : '',
			'first_name' => ! empty( $user['user_first'] ) ? $user['user_first'] : '',
			'last_name'  => ! empty( $user['user_last'] ) ? $user['user_last'] : '',
			'discount'   => ! empty( $valid_data['discount'] ) ? $valid_data['discount'] : false,
			'address'    => ! empty( $user['address'] ) ? $user['address'] : false,
		);
	}

	/**
	 * Updates the customer record if the user has added or updated information.
	 *
	 * @since 3.3.5
	 * @param array $user_info The user information.
	 */
	private static function maybe_update_customer( $user_info ) {
		$customer = edd_get_customer_by( 'email', $user_info['email'] );
		if ( ! $customer ) {
			return;
		}
		$name = trim( $user_info['first_name'] . ' ' . $user_info['last_name'] );
		if ( empty( $customer->name ) || $name !== $customer->name ) {
			$customer->update(
				array(
					'name' => $name,
				)
			);
		}

		if ( empty( $user_info['address'] ) ) {
			return;
		}

		$address = wp_parse_args(
			$user_info['address'],
			array(
				'line1'   => '',
				'line2'   => '',
				'city'    => '',
				'state'   => '',
				'country' => '',
				'zip'     => '',
			)
		);

		$address = array(
			'address'     => $address['line1'],
			'address2'    => $address['line2'],
			'city'        => $address['city'],
			'region'      => $address['state'],
			'country'     => $address['country'],
			'postal_code' => $address['zip'],
		);

		edd_maybe_add_customer_address( $customer->id, $address );
	}
}
