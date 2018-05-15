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
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Given a string, mask it with the * character.
 *
 * First and last character will remain with the filling characters being changed to *. One Character will
 * be left in tact as is. Two character strings will have the first character remain and the second be a *.
 *
 * @since 2.9.2
 * @param string $string
 *
 * @return string
 */
function edd_mask_string( $string = '' ) {

	if ( empty( $string ) ) {
		return '';
	}

	$first_char  = substr( $string, 0, 1 );
	$last_char   = substr( $string, -1, 1 );

	$masked_string = $string;

	if ( strlen( $string ) > 2 ) {

		$total_stars = strlen( $string ) - 2;
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

		$mask_parts = $possible_cctld ? array_slice( $domain_parts, 0, $part_count - 2 ) : array_slice( $domain_parts, 0, $part_count - 1 );

		$i = 0;
		while ( $i < count( $mask_parts ) ) {
			$domain_parts[ $i ] = edd_mask_string( $domain_parts[ $i ]);
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
 * Register any of our Privacy Data Exporters
 *
 * @since 2.9.2
 *
 * @param $exporters
 *
 * @return array
 */
function edd_register_privacy_exporters( $exporters ) {

	$exporters[] = array(
		'exporter_friendly_name' => __( 'Customer Record', 'easy-digital-downloads' ),
		'callback'               => 'edd_privacy_customer_record_exporter',
	);

	$exporters[] = array(
		'exporter_friendly_name' => __( 'Billing Information', 'easy-digital-downloads' ),
		'callback'               => 'edd_privacy_billing_information_exporter',
	);

	return $exporters;

}
add_filter( 'wp_privacy_personal_data_exporters', 'edd_register_privacy_exporters' );

/**
 * Retrieves the Customer record for the Privacy Data Exporter
 *
 * @since 2.9.2
 * @param string $email_address
 * @param int    $page
 *
 * @return array
 */
function edd_privacy_customer_record_exporter( $email_address = '', $page = 1 ) {

	$customer    = new EDD_Customer( $email_address );
	$export_data = array();

	if ( ! empty( $customer->id ) ) {
		$export_data = array(
			'group_id'    => 'edd-customer-record',
			'group_label' => __( 'Customer Record', 'easy-digital-downloads' ),
			'item_id'     => "edd-customer-record-{$customer->id}",
			'data'        => array(
				array(
					'name'  => __( 'Customer ID', 'easy-digital-downloads' ),
					'value' => $customer->id
				),
				array(
					'name'  => __( 'Primary Email', 'easy-digital-downloads' ),
					'value' => $customer->email
				),
				array(
					'name'  => __( 'Name', 'easy-digital-downloads' ),
					'value' => $customer->name
				),
				array(
					'name'  => __( 'Date Created', 'easy-digital-downloads' ),
					'value' => $customer->date_created
				),
				array(
					'name'  => __( 'All Email Addresses', 'easy-digital-downloads' ),
					'value' => implode( ', ', $customer->emails )
				),
			)
		);

		$agree_to_terms_time = $customer->get_meta( 'agree_to_terms_time' );
		if ( ! empty( $agree_to_terms_time ) ) {
			$export_data['data'][] = array(
				'name' => __( 'Agreed to Terms' ),
				'value' => date_i18n( get_option( 'date_format' ) . ' H:i:s', strtotime( $customer->get_meta( 'agree_to_terms_time' ) ) )
			);
		}

		$agree_to_privacy_time = $customer->get_meta( 'agree_to_privacy_time' );
		if ( ! empty( $agree_to_privacy_time ) ) {
			$export_data['data'][] = array(
				'name' => __( 'Agreed to Privacy Policy' ),
				'value' => date_i18n( get_option( 'date_format' ) . ' H:i:s', strtotime( $customer->get_meta( 'agree_to_privacy_time' ) ) )
			);
		}
	}

	return array( 'data' => array( $export_data ), 'done' => true );
}

/**
 * Retrieves the billing information for the Privacy Exporter
 *
 * @since 2.9.2
 * @param string $email_address
 * @param int    $page
 *
 * @return array
 */
function edd_privacy_billing_information_exporter( $email_address = '', $page = 1 ) {

	$customer = new EDD_Customer( $email_address );
	$payments = edd_get_payments( array(
		'customer' => $customer->id,
		'output'   => 'payments',
		'page'     => $page,
	) );

	// If we haven't found any payments for this page, just return that we're done.
	if ( empty( $payments ) ) {
		return array( 'data' => array(), 'done' => true );
	}

	$export_items = array();
	foreach ( $payments as $payment ) {

		$order_items = array();
		foreach ( $payment->downloads as $cart_item ) {
			$download = new EDD_Download( $cart_item['id'] );
			$name     = $download->get_name();

			if ( $download->has_variable_prices() && isset( $cart_item['options']['price_id'] ) ) {
				$variation_name = edd_get_price_option_name( $download->ID, $cart_item['options']['price_id'] );
				if ( ! empty( $variation_name ) ) {
					$name .= ' - ' . $variation_name;
				}
			}

			$order_items[] = $name . ' &times; ' . $cart_item['quantity'];
		}

		$items_purchased = implode( ', ', $order_items );

		$billing_name = array();
		if ( ! empty( $payment->user_info['first_name'] ) ) {
			$billing_name[] = $payment->user_info['first_name'];
		}

		if ( ! empty( $payment->user_info['last_name'] ) ) {
			$billing_name[] = $payment->user_info['last_name'];
		}
		$billing_name = implode( ' ', array_values( $billing_name ) );

		$billing_street = array();
		if ( ! empty( $payment->address['line1'] ) ) {
			$billing_street[] = $payment->address['line1'];
		}

		if ( ! empty( $payment->address['line2'] ) ) {
			$billing_street[] = $payment->address['line2'];
		}
		$billing_street = implode( "\n", array_values( $billing_street ) );


		$billing_city_state = array();
		if ( ! empty( $payment->address['city'] ) ) {
			$billing_city_state[] = $payment->address['city'];
		}

		if ( ! empty( $payment->address['state'] ) ) {
			$billing_city_state[] = $payment->address['state'];
		}
		$billing_city_state = implode( ', ', array_values( $billing_city_state ) );

		$billing_country_postal = array();
		if ( ! empty( $payment->address['zip'] ) ) {
			$billing_country_postal[] = $payment->address['zip'];
		}

		if ( ! empty( $payment->address['country'] ) ) {
			$billing_country_postal[] = $payment->address['country'];
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
				'value' => $payment->number,
			),
			array(
				'name' => __( 'Order Date', 'easy-digital-downloads' ),
				'value' => date_i18n( get_option( 'date_format' ) . ' H:i:s', strtotime( $payment->date ) ),
			),
			array(
				'name' => __( 'Order Completed Date', 'easy-digital-downloads' ),
				'value' =>  ! empty( $payment->completed_date )
					? date_i18n( get_option( 'date_format' ) . ' H:i:s', strtotime( $payment->completed_date ) )
					: '',
			),
			array(
				'name' => __( 'Order Total', 'easy-digital-downloads' ),
				'value' => edd_currency_filter( edd_format_amount( $payment->total ), $payment->currency ),
			),
			array(
				'name' => __( 'Order Items', 'easy-digital-downloads' ),
				'value' => $items_purchased,
			),
			array(
				'name'  => __( 'Email Address', 'easy-digital-downloads' ),
				'value' => ! empty( $payment->email ) ? $payment->email : '',
			),
			array(
				'name'  => __( 'Billing Address', 'easy-digital-downloads' ),
				'value' => $full_billing_address,
			),
			array(
				'name'  => __( 'IP Address', 'easy-digital-downloads' ),
				'value' => ! empty( $payment->ip ) ? $payment->ip : '',
			),
			array(
				'name'  => __( 'Status', 'easy-digital-downloads' ),
				'value' => edd_get_payment_status_label( $payment->status ),
			),
		);

		$data_points = apply_filters( 'edd_privacy_order_details_item', $data_points, $payment );

		$export_items[] = array(
			'group_id'    => 'edd-order-details',
			'group_label' => __( 'Customer Orders', 'easy-digital-downloads' ),
			'item_id'     => "edd-order-details-{$payment->ID}",
			'data'        => $data_points,
		);

	}


	// Add the data to the list, and tell the exporter to come back for the next page of payments.
	return array(
		'data' => $export_items,
		'done' => false,
	);

}