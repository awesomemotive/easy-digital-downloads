<?php
/**
 * Privacy Functions
 *
 * @package     EDD
 * @subpackage  Functions
 * @copyright   Copyright (c) 2018, Easy Digital Downloads, LLC
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       2.9.2
 */

// Exit if accessed directly
defined( 'ABSPATH' ) || exit;

/**
 * Register the EDD template for a privacy policy.
 *
 * Note, this is just a suggestion and should be customized to meet your businesses needs.
 *
 * @since 2.9.2
 */
function edd_register_privacy_policy_template() {
	if ( ! function_exists( 'wp_add_privacy_policy_content' ) ) {
		return;
	}

	$content = wp_kses_post( apply_filters( 'edd_privacy_policy_content', __( '
We collect information about you during the checkout process on our store. This information may include, but is not limited to, your name, billing address, shipping address, email address, phone number, credit card/payment details and any other details that might be requested from you for the purpose of processing your orders.
Handling this data also allows us to:
- Send you important account/order/service information.
- Respond to your queries, refund requests, or complaints.
- Process payments and to prevent fraudulent transactions. We do this on the basis of our legitimate business interests.
- Set up and administer your account, provide technical and/or customer support, and to verify your identity.
', 'easy-digital-downloads' ) ) );

	$content .= "\n\n";

	$additional_collection = array(
		__( 'Location and traffic data (including IP address and browser type) if you place an order, or if we need to estimate taxes and shipping costs based on your location.', 'easy-digital-downloads' ),
		__( 'Product pages visited and content viewed while your session is active.', 'easy-digital-downloads' ),
		__( 'Your comments and product reviews if you choose to leave them on our website.', 'easy-digital-downloads' ),
		__( 'Account email/password to allow you to access your account, if you have one.', 'easy-digital-downloads' ),
		__( 'If you choose to create an account with us, your name, address, and email address, which will be used to populate the checkout for future orders.', 'easy-digital-downloads' ),
	);

	$additional_collection = apply_filters( 'edd_privacy_policy_additional_collection', $additional_collection );

	$content .= __( 'Additionally we may also collect the following information:', 'easy-digital-downloads' ) . "\n";
	if ( ! empty( $additional_collection ) ) {
		foreach ( $additional_collection as $item ) {
			$content .= '- ' . $item . "\n";
		}
	}

	wp_add_privacy_policy_content( 'Easy Digital Downloads', wpautop( $content ) );
}

add_action( 'admin_init', 'edd_register_privacy_policy_template' );

/** Helper Functions *********************************************************/

/**
 * Given a string, mask it with the * character.
 *
 * First and last character will remain with the filling characters being changed to *. One Character will
 * be left in tact as is. Two character strings will have the first character remain and the second be a *.
 *
 * @since 2.9.2
 *
 * @param string $string
 *
 * @return string
 */
function edd_mask_string( $string = '' ) {
	if ( empty( $string ) ) {
		return '';
	}

	$first_char = substr( $string, 0, 1 );
	$last_char  = substr( $string, -1, 1 );

	$masked_string = $string;

	if ( strlen( $string ) > 2 ) {
		$total_stars   = strlen( $string ) - 2;
		$masked_string = $first_char . str_repeat( '*', $total_stars ) . $last_char;

	} elseif ( strlen( $string ) === 2 ) {
		$masked_string = $first_char . '*';
	}

	return $masked_string;
}

/**
 * Given a domain, mask it with the * character.
 *
 * TLD parts will remain intact (.com, .co.uk, etc). All subdomains will be masked t**t.e*****e.co.uk.
 *
 * @since 2.9.2
 *
 * @param string $domain
 *
 * @return string
 */
function edd_mask_domain( $domain = '' ) {

	if ( empty( $domain ) ) {
		return '';
	}

	$domain_parts = explode( '.', $domain );

	if ( count( $domain_parts ) === 2 ) {

		// We have a single entry tld like .org or .com
		$domain_parts[0] = edd_mask_string( $domain_parts[0] );

	} else {

		$part_count     = count( $domain_parts );
		$possible_cctld = strlen( $domain_parts[ $part_count - 2 ] ) <= 3 ? true : false;

		$mask_parts = $possible_cctld
			? array_slice( $domain_parts, 0, $part_count - 2 )
			: array_slice( $domain_parts, 0, $part_count - 1 );

		$mask_parts = count( $mask_parts );

		$i = 0;
		while ( $i < $mask_parts ) {
			$domain_parts[ $i ] = edd_mask_string( $domain_parts[ $i ] );
			$i++;
		}
	}

	return implode( '.', $domain_parts );
}

/**
 * Given an email address, mask the name and domain according to domain and string masking functions.
 *
 * Will result in an email address like a***n@e*****e.org for admin@example.org.
 *
 * @since 2.9.2
 *
 * @param $email_address
 *
 * @return string
 */
function edd_pseudo_mask_email( $email_address ) {
	if ( ! is_email( $email_address ) ) {
		return $email_address;
	}

	$email_parts = explode( '@', $email_address );
	$name        = edd_mask_string( $email_parts[0] );
	$domain      = edd_mask_domain( $email_parts[1] );

	$email_address = $name . '@' . $domain;

	return $email_address;
}

/**
 * Log the privacy and terms timestamp for the last completed purchase for a customer.
 *
 * Stores the timestamp of the last time the user clicked the 'Complete Purchase'
 * button for the Agree to Terms and/or Privacy Policy checkboxes during the
 * checkout process.
 *
 * @since 2.9.2
 * @since 3.0 Updated to use new query methods.
 *
 * @param int   $order_id   Order ID.
 * @param array $order_data Order data.
 */
function edd_log_terms_and_privacy_times( $order_id = 0, $order_data = array() ) {

	// Bail if order ID or order data was not passed.
	if ( empty( $order_id ) || empty( $order_data ) ) {
		return;
	}

	$order = edd_get_order( $order_id );

	if ( $order ) {
		if ( ! empty( $order_data['agree_to_terms_time'] ) ) {
			edd_add_customer_meta( $order->customer_id, 'agree_to_terms_time', $order_data['agree_to_terms_time'] );
		}

		if ( ! empty( $order_data['agree_to_privacy_time'] ) ) {
			edd_add_customer_meta( $order->customer_id, 'agree_to_privacy_time', $order_data['agree_to_privacy_time'] );
		}
	}
}

add_action( 'edd_insert_payment', 'edd_log_terms_and_privacy_times', 10, 2 );

/*
 * Return an anonymized email address.
 *
 * While WP Core supports anonymizing email addresses with the wp_privacy_anonymize_data function,
 * it turns every email address into deleted@site.invalid, which does not work when some purchase/customer records
 * are still needed for legal and regulatory reasons.
 *
 * This function will anonymize the email with an MD5 that is salted
 * and given a randomized uniqid prefixed with the store URL in order to prevent connecting a single customer across
 * multiple stores, as well as the timestamp at the time of anonymization (so it trying the same email again will not be
 * repeatable and therefore connected), and return the email address as <hash>@site.invalid.
 *
 * @since 2.9.2
 *
 * @param string $email_address
 *
 * @return string
 */
function edd_anonymize_email( $email_address ) {

	// Bail if no email address passed.
	if ( empty( $email_address ) ) {
		return $email_address;
	}

	$email_address    = strtolower( $email_address );
	$email_parts      = explode( '@', $email_address );
	$anonymized_email = wp_hash( uniqid( get_option( 'site_url' ), true ) . $email_parts[0] . current_time( 'timestamp' ), 'nonce' );

	return $anonymized_email . '@site.invalid';
}

/**
 * Given a customer ID, anonymize the data related to that customer.
 *
 * Only the customer record is affected in this function. The data that is changed:
 * - The name is changed to 'Anonymized Customer'
 * - The email address is anonymized, but kept in a format that passes is_email checks
 * - The date created is set to the timestamp of 0 (January 1, 1970)
 * - Notes are fully cleared
 * - Any additional email addresses are removed
 *
 * Once completed, a note is left stating when the customer was anonymized.
 *
 * @param int $customer_id Customer ID.
 *
 * @return array
 */
function _edd_anonymize_customer( $customer_id = 0 ) {
	$customer = edd_get_customer( $customer_id );

	if ( empty( $customer->id ) ) {
		return array(
			'success' => false,
			/* translators: %d is the customer ID. */
			'message' => sprintf( __( 'No customer with ID %d', 'easy-digital-downloads' ), $customer_id ),
		);
	}

	/**
	 * Determines if this customer should be allowed to be anonymized.
	 *
	 * Developers and extensions can use this filter to make it possible to not anonymize a customer. A sample use case
	 * would be if the customer has pending orders, and that payment requires shipping, anonymizing the customer may
	 * not be ideal.
	 *
	 * @since 2.9.2
	 *
	 * @param array {
	 *     Contains data related to if the anonymization should take place
	 *
	 *     @type bool   $should_anonymize If the customer should be anonymized.
	 *     @type string $message          A message to display if the customer could not be anonymized.
	 * }
	 */
	$should_anonymize_customer = apply_filters(
		'edd_should_anonymize_customer',
		array(
			'should_anonymize' => true,
			'message'          => '',
		),
		$customer
	);

	if ( empty( $should_anonymize_customer['should_anonymize'] ) ) {
		return array(
			'success' => false,
			'message' => $should_anonymize_customer['message'],
		);
	}

	remove_action( 'edd_customer_updated', 'edd_process_customer_updated', 10, 3 );

	// Now we should look at payments this customer has associated, and if there are any payments that should not be modified,
	// do not modify the customer.
	$orders = edd_get_orders(
		array(
			'customer_id' => $customer->id,
			'number'      => 9999999,
		)
	);

	foreach ( $orders as $order ) {
		$action = _edd_privacy_get_payment_action( $order );
		if ( 'none' === $action ) {
			return array(
				'success' => false,
				'message' => __( 'Customer could not be anonymized due to payments that could not be anonymized or deleted.', 'easy-digital-downloads' ),
			);
		}
	}

	$anonymized_email = edd_anonymize_email( $customer->email );

	$emails = edd_get_customer_email_addresses(
		array(
			'number'      => 9999999,
			'customer_id' => $customer->id,
		)
	);

	// Loop through all their email addresses, and remove any additional email addresses.
	foreach ( $emails as $email ) {
		edd_delete_customer_email_address( $email->id );
	}

	$addresses = edd_get_customer_addresses(
		array(
			'number'      => 9999999,
			'customer_id' => $customer->id,
		)
	);

	foreach ( $addresses as $address ) {
		edd_delete_customer_address( $address->id );
	}

	edd_update_customer(
		$customer->id,
		array(
			'name'         => __( 'Anonymized Customer', 'easy-digital-downloads' ),
			'email'        => $anonymized_email,
			'date_created' => date( 'Y-m-d H:i:s', 0 ),
			'notes'        => '',
			'user_id'      => 0,
			'status'       => 'disabled',
		)
	);

	/**
	 * Run further anonymization on a customer
	 *
	 * Developers and extensions can use the EDD_Customer object passed into the edd_anonymize_customer action
	 * to complete further anonymization.
	 *
	 * @since 2.9.2
	 *
	 * @param EDD_Customer $customer The EDD_Customer object that was found.
	 */
	do_action( 'edd_anonymize_customer', $customer );

	$customer->add_note( __( 'Customer anonymized successfully', 'easy-digital-downloads' ) );

	return array(
		'success' => true,
		/* translators: %d is the customer ID. */
		'message' => sprintf( __( 'Customer ID %d successfully anonymized.', 'easy-digital-downloads' ), $customer_id ),
	);
}

/**
 * Given an order ID, anonymize the data related to that order.
 *
 * Only the order record is affected in this function. The data that is changed:
 * - First Name is made blank
 * - Last  Name is made blank
 * - All email addresses are converted to the anonymized email address on the customer
 * - The IP address is run to only be the /24 IP Address (ending in .0) so it cannot be traced back to a user
 * - Address line 1 is made blank
 * - Address line 2 is made blank
 *
 * @param int $order_id Order ID.
 * @return array
 */
function _edd_anonymize_payment( $order_id = 0 ) {

	$order = edd_get_order( $order_id );
	if ( ! $order ) {
		return array(
			'success' => false,
			'message' => sprintf( __( 'No order with ID %d.', 'easy-digital-downloads' ), $order_id ),
		);
	}

	/**
	 * Determines if this order should be allowed to be anonymized.
	 *
	 * Developers and extensions can use this filter to make it possible to not
	 * anonymize an order. A sample use case would be if the order is pending,
	 * and the order requires shipping, anonymizing the order may not be ideal.
	 *
	 * @since 2.9.2
	 *
	 * @param array {
	 *     Contains data related to if the anonymization should take place
	 *
	 *     @type bool   $should_anonymize If the payment should be anonymized.
	 *     @type string $message          A message to display if the customer could not be anonymized.
	 * }
	 * @param \EDD\Orders\Order Order object.
	 */
	$should_anonymize_payment = apply_filters( 'edd_should_anonymize_payment', array(
		'should_anonymize' => true,
		'message'          => '',
	), $order );

	if ( empty( $should_anonymize_payment['should_anonymize'] ) ) {
		return array( 'success' => false, 'message' => $should_anonymize_payment['message'] );
	}

	$action = _edd_privacy_get_payment_action( $order );

	switch ( $action ) {
		case 'none':
		default:
			$return = array(
				'success' => false,
				'message' => sprintf( __( 'Order not modified, due to status: %s.', 'easy-digital-downloads' ), $order->status ),
			);
			break;

		case 'delete':
			edd_delete_purchase( $order->id, true, true );

			$return = array(
				'success' => true,
				'message' => sprintf( __( 'Order %d with status %s deleted.', 'easy-digital-downloads' ), $order->id, $order->status ),
			);
			break;

		case 'anonymize':
			$customer = new EDD_Customer( $order->customer_id );

			$order_data = array(
				'ip'    => wp_privacy_anonymize_ip( $order->ip ),
				'email' => $customer->email,
			);

			edd_update_order( $order->id, $order_data );

			// Anonymize the line 1 and line 2 of the address. City, state, zip, and country are possibly needed for taxes.
			$order_address_data = array(
				'name'       => '',
				'address'    => '',
				'address2'   => '',
			);

			edd_update_order_address( $order->address->id, $order_address_data );

			/**
			 * Run further anonymization on an order.
			 *
			 * Developers and extensions can use the \EDD\Orders\Order object passed
			 * into the edd_anonymize_payment action to complete further anonymization.
			 *
			 * @since 2.9.2
			 * @since 3.0 Updated to pass \EDD\Orders\Order object.
			 *
			 * @param \EDD\Orders\Order Order object.
			 */
			do_action( 'edd_anonymize_payment', $order );

			$return = array(
				'success' => true,
				'message' => sprintf( __( 'Order ID %d successfully anonymized.', 'easy-digital-downloads' ), $order_id ),
			);
			break;
	}

	return $return;
}

/**
 * Given an EDD_Payment, determine what action should be taken during the eraser processes.
 *
 * @since 2.9.2
 * @since 3.0 Updated to allow \EDD\Orders\Order objects to be passed.
 *
 * @param EDD_Payment|\EDD\Orders\Order $order
 *
 * @return string
 */
function _edd_privacy_get_payment_action( $order ) {
	$action = edd_get_option( 'payment_privacy_status_action_' . $order->status, false );

	// If the store owner has not saved any special settings for the actions to be taken, use defaults.
	if ( empty( $action ) ) {
		switch ( $order->status ) {
			case 'complete':
			case 'refunded':
			case 'revoked':
				$action = 'anonymize';
				break;

			case 'failed':
			case 'abandoned':
				$action = 'delete';
				break;

			case 'pending':
			case 'processing':
			default:
				$action = 'none';
				break;
		}
	}

	/**
	 * Allow filtering of what type of action should be taken for an order.
	 *
	 * Developers and extensions can use this filter to modify how Easy Digital Downloads will treat an order
	 * that has been requested to be deleted or anonymized.
	 *
	 * @since 2.9.2
	 *
	 * @param string      $action                  What action will be performed (none, delete, anonymize)
	 * @param EDD_Payment|\EDD\Orders\Order $order The order object that has been requested to be anonymized or deleted.
	 */
	$action = apply_filters( 'edd_privacy_payment_status_action_' . $order->status, $action, $order );

	return $action;

}

/**
 * Since our eraser callbacks need to look up a stored customer ID by hashed email address, developers can use this
 * to retrieve the customer ID associated with an email address that's being requested to be deleted even after the
 * customer has been anonymized.
 *
 * @since 2.9.2
 *
 * @param $email_address
 *
 * @return EDD_Customer
 */
function _edd_privacy_get_customer_id_for_email( $email_address ) {
	$customer_id = get_option( 'edd_priv_' . md5( $email_address ), true );
	$customer    = new EDD_Customer( $customer_id );

	return $customer;
}

/** Exporter Functions */

/**
 * Register any of our Privacy Data Exporters.
 *
 * @since 2.9.2
 *
 * @param array $exporters Privacy exporters.
 * @return array
 */
function edd_register_privacy_exporters( $exporters = array() ) {
	$exporters[] = array(
		'exporter_friendly_name' => __( 'Customer Record', 'easy-digital-downloads' ),
		'callback'               => 'edd_privacy_customer_record_exporter',
	);

	$exporters[] = array(
		'exporter_friendly_name' => __( 'Billing Information', 'easy-digital-downloads' ),
		'callback'               => 'edd_privacy_billing_information_exporter',
	);


	$exporters[] = array(
		'exporter_friendly_name' => __( 'File Downloads', 'easy-digital-downloads' ),
		'callback'               => 'edd_privacy_file_download_log_exporter',
	);

	$exporters[] = array(
		'exporter_friendly_name' => __( 'API Access Logs', 'easy-digital-downloads' ),
		'callback'               => 'edd_privacy_api_access_log_exporter',
	);

	return $exporters;
}
add_filter( 'wp_privacy_personal_data_exporters', 'edd_register_privacy_exporters' );

/**
 * Retrieves the Customer record for the Privacy Data Exporter
 *
 * @since 2.9.2
 * @since 3.0 Convert `date_created` for customers to use WordPress time.
 *
 * @param string $email_address Email address.
 * @param int    $page          Page number (for batch exporter).
 *
 * @return array
 */
function edd_privacy_customer_record_exporter( $email_address = '', $page = 1 ) {
	$customer = new EDD_Customer( $email_address );

	if ( empty( $customer->id ) ) {
		return array(
			'data' => array(),
			'done' => true
		);
	}

	$export_data = array(
		'group_id'    => 'edd-customer-record',
		'group_label' => __( 'Customer Record', 'easy-digital-downloads' ),
		'item_id'     => "edd-customer-record-{$customer->id}",
		'data'        => array(
			array(
				'name'  => __( 'Customer ID', 'easy-digital-downloads' ),
				'value' => $customer->id,
			),
			array(
				'name'  => __( 'Primary Email', 'easy-digital-downloads' ),
				'value' => $customer->email,
			),
			array(
				'name'  => __( 'Name', 'easy-digital-downloads' ),
				'value' => $customer->name,
			),
			array(
				'name'  => __( 'Date Created', 'easy-digital-downloads' ),
				'value' => EDD()->utils->date( $customer->date_created, 'UTC' )->setTimezone( edd_get_timezone_id() )->toDateTimeString(),
			),
			array(
				'name'  => __( 'All Email Addresses', 'easy-digital-downloads' ),
				'value' => implode( ', ', $customer->emails ),
			),
		),
	);

	$agree_to_terms_time = $customer->get_meta( 'agree_to_terms_time', false );
	if ( ! empty( $agree_to_terms_time ) ) {
		foreach ( $agree_to_terms_time as $timestamp ) {
			$export_data['data'][] = array(
				'name'  => __( 'Agreed to Terms', 'easy-digital-downloads' ),
				'value' => date_i18n( get_option( 'date_format' ) . ' H:i:s', $timestamp ),
			);
		}
	}

	$agree_to_privacy_time = $customer->get_meta( 'agree_to_privacy_time', false );
	if ( ! empty( $agree_to_privacy_time ) ) {
		foreach ( $agree_to_privacy_time as $timestamp ) {
			$export_data['data'][] = array(
				'name'  => __( 'Agreed to Privacy Policy', 'easy-digital-downloads' ),
				'value' => date_i18n( get_option( 'date_format' ) . ' H:i:s', $timestamp ),
			);
		}
	}

	$export_data = apply_filters( 'edd_privacy_customer_record', $export_data, $customer );

	return array(
		'data' => array( $export_data ),
		'done' => true,
	);
}

/**
 * Retrieves the billing information for the Privacy Exporter.
 *
 * @since 2.9.2
 * @since 3.0 Updated to use new query methods.
 *
 * @param string $email_address Email address.
 * @param int    $page          Page number (for batch exporter).
 *
 * @return array
 */
function edd_privacy_billing_information_exporter( $email_address = '', $page = 1 ) {
	$customer = new EDD_Customer( $email_address );

	if ( empty( $customer->id ) ) {
		return array( 'data' => array(), 'done' => true );
	}

	$orders = edd_get_orders( array(
		'customer_id' => $customer->id,
		'number'      => 30,
		'offset'      => ( 30 * $page ) - 30,
	) );

	// Bail if we haven't found any orders for this page.
	if ( empty( $orders ) ) {
		return array(
			'data' => array(),
			'done' => true,
		);
	}

	$export_items = array();

	if ( $orders ) {
		foreach ( $orders as $order ) {
			$order_items = array();

			foreach ( $order->items as $order_item ) {
				$download = edd_get_download( $order_item->product_id );
				$name     = $download->get_name();

				if ( $download->has_variable_prices() && ! empty( $order_item->price_id ) ) {
					$variation_name = edd_get_price_option_name( $download->ID, $order_item->price_id );
					if ( ! empty( $variation_name ) ) {
						$name .= ' - ' . $variation_name;
					}
				}

				$order_items[] = $name . ' &times; ' . $order_item->quantity;
			}

			$items_purchased = implode( ', ', $order_items );

			$billing_name = array();
			if ( ! empty( $order->address->first_name ) ) {
				$billing_name[] = $order->address->first_name;
			}

			if ( ! empty( $order->address->last_name ) ) {
				$billing_name[] = $order->address->last_name;
			}
			$billing_name = implode( ' ', array_values( $billing_name ) );

			$billing_street = array();
			if ( ! empty( $order->address->address ) ) {
				$billing_street[] = $order->address->address;
			}

			if ( ! empty( $order->address->address2 ) ) {
				$billing_street[] = $order->address->address2;
			}
			$billing_street = implode( "\n", array_values( $billing_street ) );


			$billing_city_state = array();
			if ( ! empty( $order->address->city ) ) {
				$billing_city_state[] = $order->address->city;
			}

			if ( ! empty( $order->address->region ) ) {
				$billing_city_state[] = $order->address->region;
			}
			$billing_city_state = implode( ', ', array_values( $billing_city_state ) );

			$billing_country_postal = array();
			if ( ! empty( $order->address->postal_code ) ) {
				$billing_country_postal[] = $order->address->postal_code;
			}

			if ( ! empty( $order->address->country ) ) {
				$billing_country_postal[] = $order->address->country;
			}
			$billing_country_postal = implode( "\n", array_values( $billing_country_postal ) );

			$full_billing_address = '';
			if ( ! empty( $billing_name ) ) {
				$full_billing_address .= $billing_name . "\n";
			}

			if ( ! empty( $billing_street ) ) {
				$full_billing_address .= $billing_street . "\n";
			}

			if ( ! empty( $billing_city_state ) ) {
				$full_billing_address .= $billing_city_state . "\n";
			}

			if ( ! empty( $billing_country_postal ) ) {
				$full_billing_address .= $billing_country_postal . "\n";
			}


			$data_points = array(
				array(
					'name'  => __( 'Order ID / Number', 'easy-digital-downloads' ),
					'value' => $order->get_number(),
				),
				array(
					'name'  => __( 'Order Date', 'easy-digital-downloads' ),
					'value' => date_i18n( get_option( 'date_format' ) . ' H:i:s', strtotime( EDD()->utils->date( $order->date_created, 'UTC' )->setTimezone( edd_get_timezone_id() )->toDateTimeString() ) ),
				),
				array(
					'name'  => __( 'Order Completed Date', 'easy-digital-downloads' ),
					'value' => ! empty( $order->date_completed ) && '0000-00-00 00:00:00' === $order->date_completed
						? date_i18n( get_option( 'date_format' ) . ' H:i:s', strtotime( EDD()->utils->date( $order->date_completed, 'UTC' )->setTimezone( edd_get_timezone_id() )->toDateTimeString() ) )
						: '',
				),
				array(
					'name'  => __( 'Order Total', 'easy-digital-downloads' ),
					'value' => edd_currency_filter( edd_format_amount( $order->total ), $order->currency ),
				),
				array(
					'name'  => __( 'Order Items', 'easy-digital-downloads' ),
					'value' => $items_purchased,
				),
				array(
					'name'  => __( 'Email Address', 'easy-digital-downloads' ),
					'value' => ! empty( $order->email )
						? $order->email
						: '',
				),
				array(
					'name'  => __( 'Billing Address', 'easy-digital-downloads' ),
					'value' => $full_billing_address,
				),
				array(
					'name'  => __( 'IP Address', 'easy-digital-downloads' ),
					'value' => ! empty( $order->ip )
						? $order->ip
						: '',
				),
				array(
					'name'  => __( 'Status', 'easy-digital-downloads' ),
					'value' => edd_get_payment_status_label( $order->status ),
				),
			);

			/**
			 * Filter each order.
			 *
			 * @since 2.9.2
			 * @since 3.0 Changed EDD_Payment object to \EDD\Orders\Order object.
			 *
			 * @param array             $data_points Data points.
			 * @param \EDD\Orders\Order $order       Order.
			 */
			$data_points = apply_filters( 'edd_privacy_order_details_item', $data_points, $order );

			$export_items[] = array(
				'group_id'    => 'edd-order-details',
				'group_label' => __( 'Customer Orders', 'easy-digital-downloads' ),
				'item_id'     => "edd-order-details-{$order->id}",
				'data'        => $data_points,
			);
		}
	}

	// Add the data to the list, and tell the exporter to come back for the next page of payments.
	return array(
		'data' => $export_items,
		'done' => false,
	);
}

/**
 * Adds the file download logs for a customer to the WP Core Privacy Data exporter
 *
 * @since 2.9.2
 * @since 3.0 Updated to use new query methods.
 *
 * @param string $email_address The email address to look up file download logs for.
 * @param int    $page          The page of logs to request.
 *
 * @return array
 */
function edd_privacy_file_download_log_exporter( $email_address = '', $page = 1 ) {
	$customer = new EDD_Customer( $email_address );

	if ( empty( $customer->id ) ) {
		return array( 'data' => array(), 'done' => true );
	}

	$logs = edd_get_file_download_logs( array(
		'customer_id' => $customer->id,
		'number'      => 100,
		'offset'      => ( 100 * $page ) - 100,
	) );

	// Bail if we haven't found any logs for this page.
	if ( empty( $logs ) ) {
		return array(
			'data' => array(),
			'done' => true,
		);
	}

	$export_items = array();

	foreach ( $logs as $log ) {
		$download = edd_get_download( $log->product_id );

		$data_points = array(
			array(
				'name'  => __( 'Date of Download', 'easy-digital-downloads' ),
				'value' => date_i18n( get_option( 'date_format' ) . ' H:i:s', strtotime( EDD()->utils->date( $log->date_created, 'UTC' )->setTimezone( edd_get_timezone_id() )->toDateTimeString() ) ),
			),
			array(
				'name'  => __( 'Product Downloaded', 'easy-digital-downloads' ),
				'value' => $download->get_name(),
			),
			array(
				'name'  => __( 'Order ID', 'easy-digital-downloads' ),
				'value' => $log->order_id,
			),
			array(
				'name'  => __( 'Customer ID', 'easy-digital-downloads' ),
				'value' => $log->customer_id,
			),
			array(
				'name'  => __( 'IP Address', 'easy-digital-downloads' ),
				'value' => $log->ip,
			),
		);

		/**
		 * Filter item.
		 *
		 * @since 2.9.2
		 * @since 3.0 Updated to pass \EDD\Logs\File_Download_Log object to filter.
		 *
		 * @param array                       $data_points Data points.
		 * @param \EDD\Logs\File_Download_Log $log         File download log.
		 */
		$data_points = apply_filters( 'edd_privacy_file_download_log_item', $data_points, $log );

		$export_items[] = array(
			'group_id'    => 'edd-file-download-logs',
			'group_label' => __( 'File Download Logs', 'easy-digital-downloads' ),
			'item_id'     => "edd-file-download-logs-{$log->id}",
			'data'        => $data_points,
		);
	}

	// Add the data to the list, and tell the exporter to come back for the next page of logs.
	return array(
		'data' => $export_items,
		'done' => false,
	);
}

/**
 * Adds the api access logs for a user to the WP Core Privacy Data exporter
 *
 * @since 2.9.2
 *
 * @param string $email_address The email address to look up api access logs for.
 * @param int    $page          The page of logs to request.
 *
 * @return array
 */
function edd_privacy_api_access_log_exporter( $email_address = '', $page = 1 ) {
	$user = get_user_by( 'email', $email_address );

	if ( false === $user ) {
		return array(
			'data' => array(),
			'done' => true,
		);
	}

	$logs = edd_get_api_request_logs( array(
		'user_id' => $user->ID,
		'number'  => 100,
		'offset'  => ( 100 * $page ) - 100,
	) );

	// If we haven't found any api access logs for this page, just return that we're done.
	if ( empty( $logs ) ) {
		return array(
			'data' => array(),
			'done' => true,
		);
	}

	$export_items = array();

	foreach ( $logs as $log ) {
		$data_points = array(
			array(
				'name'  => __( 'Date', 'easy-digital-downloads' ),
				'value' => date_i18n( get_option( 'date_format' ) . ' H:i:s', strtotime( EDD()->utils->date( $log->date_created, 'UTC' )->setTimezone( edd_get_timezone_id() )->toDateTimeString() ) ),
			),
			array(
				'name'  => __( 'Request', 'easy-digital-downloads' ),
				'value' => $log->request,
			),
			array(
				'name'  => __( 'IP Address', 'easy-digital-downloads' ),
				'value' => $log->ip,
			),
		);

		/**
		 * Filter item.
		 *
		 * @since 2.9.2
		 * @since 3.0 Updated to pass \EDD\Logs\\EDD\Logs\Api_Request_Log object to filter.
		 *
		 * @param array                     $data_points Data points.
		 * @param \EDD\Logs\Api_Request_Log $log         API request log.
		 */
		$data_points = apply_filters( 'edd_privacy_api_access_log_item', $data_points, $log );

		$export_items[] = array(
			'group_id'    => 'edd-api-access-logs',
			'group_label' => __( 'API Access Logs', 'easy-digital-downloads' ),
			'item_id'     => "edd-api-access-logs-{$log->id}",
			'data'        => $data_points,
		);

	}

	// Add the data to the list, and tell the exporter to come back for the next page of payments.
	return array(
		'data' => $export_items,
		'done' => false,
	);
}

/** Anonymization Functions */

/**
 * This registers a single eraser _very_ early to avoid any other hook into the EDD data from running first.
 *
 * We are going to set an option of what customer we're currently deleting for what email address, so that after the
 * customer is anonymized we can still find them. Then we'll delete it.
 *
 * @param array $erasers
 * @return array $erasers
 */
function edd_register_privacy_eraser_customer_id_lookup( $erasers = array() ) {
	$erasers[] = array(
		'eraser_friendly_name' => 'pre-eraser-customer-id-lookup',
		'callback'             => 'edd_privacy_prefetch_customer_id',
	);

	return $erasers;
}
add_filter( 'wp_privacy_personal_data_erasers', 'edd_register_privacy_eraser_customer_id_lookup', 5, 1 );

/**
 * Lookup the customer ID for this email address so that we can use it later in the anonymization process.
 *
 * @param     $email_address
 * @param int $page
 *
 * @return array
 */
function edd_privacy_prefetch_customer_id( $email_address, $page = 1 ) {
	$customer = new EDD_Customer( $email_address );
	update_option( 'edd_priv_' . md5( $email_address ), $customer->id, false );

	return array(
		'items_removed'  => false,
		'items_retained' => false,
		'messages'       => array(),
		'done'           => true,
	);
}

/**
 * This registers a single eraser _very_ late to remove a customer ID that was found for the erasers.
 *
 * We are now assumed done with our exporters, so we can go ahead and delete the customer ID we found for this eraser.
 *
 * @param array $erasers
 * @return array $erasers
 */
function edd_register_privacy_eraser_customer_id_removal( $erasers = array() ) {
	$erasers[] = array(
		'eraser_friendly_name' => __( 'Possibly Delete Customer', 'easy-digital-downloads' ),
		'callback'             => 'edd_privacy_maybe_delete_customer_eraser',
	);

	$erasers[] = array(
		'eraser_friendly_name' => 'post-eraser-customer-id-lookup',
		'callback'             => 'edd_privacy_remove_customer_id',
	);

	return $erasers;
}
add_filter( 'wp_privacy_personal_data_erasers', 'edd_register_privacy_eraser_customer_id_removal', 9999, 1 );

/**
 * Delete the customer ID for this email address that was found in edd_privacy_prefetch_customer_id()
 *
 * @param string $email_address
 * @param int    $page
 *
 * @return array
 */
function edd_privacy_remove_customer_id( $email_address, $page = 1 ) {
	delete_option( 'edd_priv_' . md5( $email_address ) );

	return array(
		'items_removed'  => false,
		'items_retained' => false,
		'messages'       => array(),
		'done'           => true,
	);
}

/**
 * If after the payment anonymization/erasure methods have been run, and there are no longer payments
 * for the requested customer, go ahead and delete the customer
 *
 * @since 2.9.2
 * @since 3.0 Updated to use new query methods.
 *
 * @param string $email_address The email address requesting anonymization/erasure
 * @param int    $page          The page (not needed for this query)
 *
 * @return array
 */
function edd_privacy_maybe_delete_customer_eraser( $email_address, $page = 1 ) {
	$customer = _edd_privacy_get_customer_id_for_email( $email_address );

	if ( empty( $customer->id ) ) {
		return array(
			'items_removed'  => false,
			'items_retained' => false,
			'messages'       => array(),
			'done'           => true,
		);
	}

	$orders = edd_get_orders( array(
		'customer_id' => $customer->id,
		'number'      => 30,
		'offset'      => ( 30 * $page ) - 30,
	) );

	if ( ! empty( $orders ) ) {
		return array(
			'items_removed'  => false,
			'items_retained' => false,
			'messages'       => array(
				sprintf( __( 'Customer for %s not deleted, due to remaining payments.', 'easy-digital-downloads' ), $email_address ),
			),
			'done'           => true,
		);
	}

	if ( empty( $orders ) ) {
		$deleted_customer = edd_destroy_customer( $customer->id );

		if ( $deleted_customer ) {
			return array(
				'items_removed'  => true,
				'items_retained' => false,
				'messages'       => array(
					sprintf( __( 'Customer for %s successfully deleted.', 'easy-digital-downloads' ), $email_address ),
				),
				'done'           => true,
			);
		}
	}

	return array(
		'items_removed'  => false,
		'items_retained' => false,
		'messages'       => array(
			sprintf( __( 'Customer for %s failed to be deleted.', 'easy-digital-downloads' ), $email_address ),
		),
		'done'           => true,
	);
}

/**
 * Register eraser for EDD Data
 *
 * @since 2.9.2
 *
 * @param array $erasers
 *
 * @return array
 */
function edd_register_privacy_erasers( $erasers = array() ) {

	// The order of these matter, customer needs to be anonymized prior to the customer, so that the order can adopt
	// properties of the customer like email.

	$erasers[] = array(
		'eraser_friendly_name' => __( 'Customer Record', 'easy-digital-downloads' ),
		'callback'             => 'edd_privacy_customer_anonymizer',
	);

	$erasers[] = array(
		'eraser_friendly_name' => __( 'Order Record', 'easy-digital-downloads' ),
		'callback'             => 'edd_privacy_payment_eraser',
	);

	$erasers[] = array(
		'eraser_friendly_name' => __( 'File Download Logs', 'easy-digital-downloads' ),
		'callback'             => 'edd_privacy_file_download_logs_eraser',
	);

	$erasers[] = array(
		'eraser_friendly_name' => __( 'API Access Logs', 'easy-digital-downloads' ),
		'callback'             => 'edd_privacy_api_access_logs_eraser',
	);

	return $erasers;
}
add_filter( 'wp_privacy_personal_data_erasers', 'edd_register_privacy_erasers', 11, 1 );

/**
 * Anonymize a customer record through the WP Core Privacy Data Eraser methods.
 *
 * @since 2.9.2
 *
 * @param     $email_address
 * @param int $page
 *
 * @return array
 */
function edd_privacy_customer_anonymizer( $email_address, $page = 1 ) {
	$customer = _edd_privacy_get_customer_id_for_email( $email_address );

	$anonymized = _edd_anonymize_customer( $customer->id );
	if ( empty( $anonymized['success'] ) ) {
		return array(
			'items_removed'  => false,
			'items_retained' => false,
			'messages'       => array( $anonymized['message'] ),
			'done'           => true,
		);
	}

	return array(
		'items_removed'  => true,
		'items_retained' => false,
		'messages'       => array( sprintf( __( 'Customer for %s has been anonymized.', 'easy-digital-downloads' ), $email_address ) ),
		'done'           => true,
	);
}

/**
 * Anonymize a payment record through the WP Core Privacy Data Eraser methods.
 *
 * @param string $email_address
 * @param int    $page
 *
 * @return array
 */
function edd_privacy_payment_eraser( $email_address, $page = 1 ) {
	$customer = _edd_privacy_get_customer_id_for_email( $email_address );

	$orders = edd_get_orders( array(
		'customer_id' => $customer->id,
		'number'      => 30,
		'offset'      => ( 30 * $page ) - 30,
	) );

	if ( empty( $orders ) ) {
		$message = 1 === $page
			? sprintf( __( 'No orders found for %s.', 'easy-digital-downloads' ), $email_address )
			: sprintf( __( 'All eligible orders anonymized or deleted for %s.', 'easy-digital-downloads' ), $email_address );

		return array(
			'items_removed'  => false,
			'items_retained' => false,
			'messages'       => array( $message ),
			'done'           => true,
		);
	}

	$items_removed  = null;
	$items_retained = null;
	$messages       = array();

	foreach ( $orders as $order ) {
		$result = _edd_anonymize_payment( $order->id );

		if ( ! is_null( $items_removed ) && $result['success'] ) {
			$items_removed = true;
		}

		if ( ! is_null( $items_removed ) && ! $result['success'] ) {
			$items_retained = true;
		}

		$messages[] = $result['message'];
	}

	return array(
		'items_removed'  => ! is_null( $items_removed ) ? $items_removed : false,
		'items_retained' => ! is_null( $items_retained ) ? $items_retained : false,
		'messages'       => $messages,
		'done'           => false,
	);
}

/**
 * Anonymize the file download logs.
 *
 * @since 2.9.2
 * @since 3.0 Updated to use new query methods.
 *
 * @param string $email_address
 * @param int    $page
 *
 * @return array
 */
function edd_privacy_file_download_logs_eraser( $email_address, $page = 1 ) {
	$customer  = _edd_privacy_get_customer_id_for_email( $email_address );
	$log_query = array(
		'customer_id' => $customer->id,
		'number'      => 30,
		'offset'      => ( 30 * $page ) - 30,
	);

	$logs = edd_get_file_download_logs( $log_query );

	if ( empty( $logs ) ) {
		return array(
			'items_removed'  => false,
			'items_retained' => false,
			'messages'       => array( sprintf( __( 'All eligible file download logs anonymized or deleted for %s.', 'easy-digital-downloads' ), $email_address ) ),
			'done'           => true,
		);
	}

	foreach ( $logs as $log ) {
		edd_update_file_download_log( $log->id, array(
			'ip' => wp_privacy_anonymize_ip( $log->ip ),
		) );

		/**
		 * Run further anonymization on a file download log
		 *
		 * Developers and extensions can use the $log WP_Post object passed into the edd_anonymize_file_download_log action
		 * to complete further anonymization.
		 *
		 * @since 2.9.2
		 *
		 * @param WP_Post $log The WP_Post object for the log
		 */
		do_action( 'edd_anonymize_file_download_log', $log );
	}

	return array(
		'items_removed'  => true,
		'items_retained' => false,
		'messages'       => array(),
		'done'           => false,
	);
}

/**
 * Delete API Access Logs
 *
 * @since 2.9.2
 *
 * @param string $email_address
 * @param int    $page
 *
 * @return array
 */
function edd_privacy_api_access_logs_eraser( $email_address, $page = 1 ) {

	$user = get_user_by( 'email', $email_address );

	if ( false === $user ) {
		return array(
			'items_removed'  => false,
			'items_retained' => false,
			'messages'       => array( sprintf( __( 'No User found for %s, no access logs to remove.', 'easy-digital-downloads' ), $email_address ) ),
			'done'           => true,
		);
	}

	$log_query = array(
		'user_id' => $user->ID,
		'number'  => 30,
		'offset'  => ( 30 * $page ) - 30,
	);

	$logs = edd_get_api_request_logs( $log_query );

	if ( empty( $logs ) ) {
		return array(
			'items_removed'  => false,
			'items_retained' => false,
			'messages'       => array( sprintf( __( 'All API access logs deleted for %s.', 'easy-digital-downloads' ), $email_address ) ),
			'done'           => true,
		);
	}

	foreach ( $logs as $log ) {
		edd_delete_api_request_log( $log->id );

		/**
		 * Run further actions on an api access log
		 *
		 * Developers and extensions can use the $log WP_Post object passed into the edd_delete_api_access_log action
		 * to complete further actions.
		 *
		 * @since 2.9.2
		 * @since 3.0 Updated to pass \EDD\Logs\Api_Request_Log object.
		 *
		 * @param \EDD\Logs\Api_Request_Log $log API request log object.
		 */
		do_action( 'edd_delete_api_access_log', $log );
	}

	return array(
		'items_removed'  => true,
		'items_retained' => false,
		'messages'       => array(),
		'done'           => false,
	);
}
