<?php
/**
 * Email related admin functionality.
 *
 * @package     EDD\Admin\Customers
 * @copyright   Copyright (c) 2025, Sandhills Development, LLC
 * @license     https://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       3.3.8
 */

namespace EDD\Admin\Customers;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit; // @codeCoverageIgnore

use EDD\EventManagement\SubscriberInterface;

/**
 * Class Emails
 *
 * @since 3.3.8
 * @package EDD\Admin\Customers
 */
class Emails implements SubscriberInterface {

	/**
	 * Returns an array of events that this subscriber wants to listen to.
	 *
	 * @since 3.3.8
	 * @return array
	 */
	public static function get_subscribed_events() {
		return array(
			'edd_customer-add-email' => 'maybe_add_email',
		);
	}

	/**
	 * Handles adding a new email address to a customer.
	 *
	 * @since 3.3.8
	 * @param array $args The arguments.
	 * @return void|array
	 */
	public function maybe_add_email( $args = array() ) {
		if ( ! is_admin() || ! current_user_can( edd_get_edit_customers_role() ) ) {
			wp_die( __( 'You do not have permission to edit this customer.', 'easy-digital-downloads' ) );
		}

		if ( empty( $args ) || empty( $args['email'] ) || empty( $args['customer_id'] ) ) {

			$response = array(
				'success' => false,
				'message' => __( 'An error has occured. Please try again.', 'easy-digital-downloads' ),
			);

			if ( empty( $args['email'] ) ) {
				$response['message'] = __( 'Email address is missing.', 'easy-digital-downloads' );
			} elseif ( empty( $args['customer_id'] ) ) {
				$response['message'] = __( 'Customer ID is required.', 'easy-digital-downloads' );
			}

			return $this->handle_response( $response );
		}

		return $this->handle_response( $this->get_response( $args ) );
	}

	/**
	 * Gets the response for the request.
	 *
	 * @since 3.3.8
	 * @param array $args The arguments.
	 * @return array
	 */
	private function get_response( $args ) {
		if ( ! wp_verify_nonce( $args['_wpnonce'], 'edd-add-customer-email' ) ) {
			return array(
				'success' => false,
				'message' => __( 'Nonce verification failed.', 'easy-digital-downloads' ),
			);
		}

		if ( ! is_email( $args['email'] ) ) {
			return array(
				'success' => false,
				'message' => __( 'Invalid email address.', 'easy-digital-downloads' ),
			);
		}

		$email       = sanitize_email( $args['email'] );
		$customer_id = (int) $args['customer_id'];
		$user        = get_user_by( 'email', $email );
		if ( $user ) {
			return array(
				'success' => false,
				'message' => sprintf(
					/* translators: 1: email address, 2: link to edit customer profile */
					__( 'Email address may already associated with a user. Did you mean to link to the user instead of adding a new email? You can %1$sedit the customer profile%2$s and link to the user.', 'easy-digital-downloads' ),
					'<a href="' . esc_url(
						edd_get_admin_url(
							array(
								'page' => 'edd-customers',
								'view' => 'overview',
								'id'   => $customer_id,
							)
						) . '#edd_general_overview'
					) . '">',
					'</a>'
				),
			);
		}

		$customer = new \EDD_Customer( $customer_id );
		if ( in_array( $email, $customer->emails, true ) ) {
			return array(
				'success' => false,
				'message' => __( 'Email already associated with this customer.', 'easy-digital-downloads' ),
			);
		}

		$primary           = 'true' === $args['primary'];
		$customer_email_id = $customer->add_email( $email, $primary );
		if ( false === $customer_email_id ) {
			return array(
				'success' => false,
				'message' => __( 'Email address may already be associated with another customer.', 'easy-digital-downloads' ),
			);
		}

		$user       = wp_get_current_user();
		$user_login = ! empty( $user->user_login ) ? $user->user_login : edd_get_bot_name();
		/* translators: 1: email address, 2: username */
		$customer_note = sprintf( __( 'Email address %1$s added by %2$s', 'easy-digital-downloads' ), $email, $user_login );
		$customer->add_note( $customer_note );

		if ( $primary ) {
			/* translators: 1: email address, 2: username */
			$customer_note = sprintf( __( 'Email address %1$s set as primary by %2$s', 'easy-digital-downloads' ), $email, $user_login );
			$customer->add_note( $customer_note );
		}

		do_action( 'edd_post_add_customer_email', $customer_id, $args );

		$redirect = edd_get_admin_url(
			array(
				'page'         => 'edd-customers',
				'view'         => 'overview',
				'id'           => absint( $customer_id ),
				'edd-message'  => 'email-added',
				'edd-email-id' => absint( $customer_email_id ),
			)
		);

		return array(
			'success'  => true,
			'message'  => __( 'Email successfully added to customer.', 'easy-digital-downloads' ),
			'redirect' => $redirect . '#edd_general_emails',
		);
	}

	/**
	 * Handles the response.
	 *
	 * @since 3.3.8
	 * @param array $response The output.
	 * @return array
	 */
	private function handle_response( $response ) {
		if ( edd_doing_ajax() ) {
			wp_send_json( $response );
		}

		return $response;
	}
}
