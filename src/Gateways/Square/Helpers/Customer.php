<?php
/**
 * Customer helper for the Square integration.
 *
 * @package     EDD\Gateways\Square
 * @copyright   Copyright (c) 2025, Sandhills Development, LLC
 * @license     https://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       3.4.0
 */

namespace EDD\Gateways\Square\Helpers;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

use EDD\Vendor\Square\Models\CreateCustomerRequest;
use EDD\Gateways\Square\Helpers\Api;
use EDD\Gateways\Square\Helpers\Address;
use EDD\Gateways\Square\Helpers\Mode;

/**
 * Customer helper for the Square integration.
 *
 * @since 3.4.0
 */
class Customer {

	/**
	 * Maybe create a customer.
	 *
	 * @since 3.4.0
	 * @return array
	 * @throws \Exception If there is an error creating the customer.
	 */
	public static function maybe_create_customer( $purchase_data ) {
		// Check if there is already and EDD customer with this email.
		$edd_customer = self::get_edd_customer( $purchase_data['user_info']['email'] );

		if ( false === $edd_customer ) {
			$edd_customer = self::create_edd_customer( $purchase_data['user_info'] );
			if ( false === $edd_customer ) {
				throw new \Exception(
					sprintf(
						/* translators: %s is a reference code for the error, to help with customer support */
						__( 'There was an error creating your customer record. Please try again, or contact support. Reference: %s', 'easy-digital-downloads' ),
						'SQ1001'
					)
				);
			}
		}

		$square_customer_id = self::has_square_customer_id( $edd_customer->id );

		// If the customer does not have an square Customer ID, create a new one.
		if ( empty( $square_customer_id ) ) {
			$square_customer_result = self::create_square_customer(
				$edd_customer->id,
				$purchase_data['user_info'],
			);

			if ( ! $square_customer_result['success'] ) {
				edd_debug_log( 'Square: Error creating Square customer record for EDD customer ID: ' . $edd_customer->id );
				edd_debug_log( 'Square: Error: ' . $square_customer_result['error'] );
				throw new \Exception(
					sprintf(
						/* translators: %s is a reference code for the error, to help with customer support */
						__( 'There was an error creating your customer record. Please try again, or contact support. Reference: %s', 'easy-digital-downloads' ),
						'SQ1002'
					)
				);
			}

			$square_customer_id = $square_customer_result['square_customer_id'];
		}

		// Return the necessary customer data.
		return array(
			'edd_customer'       => $edd_customer,
			'square_customer_id' => $square_customer_id,
		);
	}

	/**
	 * Get the EDD customer.
	 *
	 * @since 3.4.0
	 * @param string $email The email address of the customer.
	 * @return \EDD_Customer|false The EDD customer object or false if the customer is not found.
	 */
	protected static function get_edd_customer( $email ) {
		$customer = edd_get_customer_by( 'email', $email );
		return $customer;
	}

	/**
	 * Check if the customer has a Square customer ID.
	 *
	 * @since 3.4.0
	 * @param string $customer_id The ID of the customer.
	 * @return string|false The Square customer ID or false if the customer does not have a Square customer ID.
	 */
	public static function has_square_customer_id( $customer_id ) {
		return edd_get_customer_meta( $customer_id, self::get_customer_id_meta_key(), true );
	}

	/**
	 * Create an EDD customer.
	 *
	 * @since 3.4.0
	 * @param array $billing_details The billing details of the customer.
	 * @return \EDD_Customer|false The EDD customer object or false if the customer is not created.
	 */
	protected static function create_edd_customer( $billing_details ) {
		$user_id = get_current_user_id();

		$customer_args = array(
			'name'  => $billing_details['first_name'] . ' ' . $billing_details['last_name'],
			'email' => $billing_details['email'],
		);

		if ( $user_id ) {
			$customer_args['user_id'] = $user_id;
		}

		$customer_id = edd_add_customer( $customer_args );

		if ( false === $customer_id ) {
			edd_debug_log( 'Square: Error creating EDD customer record for: ' . $billing_details['email'] );
			return false;
		}

		return edd_get_customer( $customer_id );
	}

	/**
	 * Create a Square customer.
	 *
	 * @since 3.4.0
	 * @param string $edd_customer_id The ID of the EDD customer.
	 * @param array  $billing_details The billing details of the customer.
	 * @return array The Square customer ID or an error message if the customer is not created.
	 */
	protected static function create_square_customer( $edd_customer_id, $billing_details ) {
		$customer_address_data               = $billing_details['address'];
		$customer_address_data['first_name'] = $billing_details['first_name'];
		$customer_address_data['last_name']  = $billing_details['last_name'];

		$address = Address::build_address_object( $customer_address_data );

		// Create the customer in Square.
		$request = new CreateCustomerRequest();

		$request->setIdempotencyKey( Api::get_idempotency_key( 'square_customer_' ) );
		$request->setGivenName( $billing_details['first_name'] );
		$request->setFamilyName( $billing_details['last_name'] );
		$request->setEmailAddress( $billing_details['email'] );
		$request->setPhoneNumber( $billing_details['address']['phone'] );
		$request->setReferenceId( $edd_customer_id );
		$request->setNote( __( 'Created by Easy Digital Downloads', 'easy-digital-downloads' ) );
		$request->setAddress( $address );

		// Make the request.
		$response = Api::client()->getCustomersApi()->createCustomer( $request );

		if ( ! $response->isSuccess() ) {
			return array(
				'success' => false,
				'error'   => $response->getErrors()[0]->getDetail(),
			);
		}

		$customer = $response->getResult()->getCustomer();

		// Now add the square customer ID to the EDD customer.
		edd_add_customer_meta( $edd_customer_id, self::get_customer_id_meta_key(), $customer->getId(), true );

		return array(
			'success'            => true,
			'square_customer_id' => $customer->getId(),
		);
	}

	/**
	 * Get the meta key for the Square customer ID.
	 *
	 * @since 3.4.0
	 * @return string The meta key for the Square customer ID.
	 */
	public static function get_customer_id_meta_key() {
		return '_edd_square_' . Mode::get() . '_customer_id';
	}
}
