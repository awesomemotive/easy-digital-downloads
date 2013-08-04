<?php

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

/*
 * Add default EDD email template tags
 */
function edd_setup_default_email_tags() {
	edd_add_email_tag( 'download_list', __( 'A list of download links for each download purchased', 'edd' ), 'edd_email_tag_download_list' );
	edd_add_email_tag( 'file_urls', __( 'A plain-text list of download URLs for each download purchased', 'edd' ), 'edd_email_tag_file_urls' );
	edd_add_email_tag( 'name', __( "The buyer's first name", 'edd' ), 'edd_email_tag_name' );
	edd_add_email_tag( 'fullname', __( "The buyer's full name, first and last", 'edd' ), 'edd_email_tag_fullname' );
	edd_add_email_tag( 'username', __( "The buyer's user name on the site, if they registered an account", 'edd' ), 'edd_email_tag_username' );
	edd_add_email_tag( 'user_email', __( "The buyer's email address", 'edd' ), 'edd_email_tag_user_email' );
	edd_add_email_tag( 'date', __( 'The date of the purchase', 'edd' ), 'edd_email_tag_date' );
	edd_add_email_tag( 'subtotal', __( 'The price of the purchase before taxes', 'edd' ), 'edd_email_tag_subtotal' );
	edd_add_email_tag( 'tax', __( 'The taxed amount of the purchase', 'edd' ), 'edd_email_tag_tax' );
	edd_add_email_tag( 'price', __( 'The total price of the purchase', 'edd' ), 'edd_email_tag_price' );
	edd_add_email_tag( 'payment_id', __( 'The unique ID number for this purchase', 'edd' ), 'edd_email_tag_payment_id' );
	edd_add_email_tag( 'receipt_id', __( 'The unique ID number for this purchase receipt', 'edd' ), 'edd_email_tag_receipt_id' );
	edd_add_email_tag( 'payment_method', __( 'The method of payment used for this purchase', 'edd' ), 'edd_email_tag_payment_method' );
	edd_add_email_tag( 'sitename', __( 'Your site name', 'edd' ), 'edd_email_tag_sitename' );
	edd_add_email_tag( 'receipt_link', __( 'Adds a link so users can view their receipt directly on your website if they are unable to view it in the browser correctly.', 'edd' ), 'edd_email_tag_receipt_link' );
}
add_action( 'edd_add_email_tags', 'edd_setup_default_email_tags' );

function edd_email_tag_download_list( $payment_id ) {
	return '!{download_list}';
}

/**
 * Email template tag: file_urls
 * A plain-text list of download URLs for each download purchased
 *
 * @param int $payment_id
 *
 * @return string $file_urls
 */
function edd_email_tag_file_urls( $payment_id ) {

	$payment_data = edd_get_payment_meta( $payment_id );
	$file_urls 		= '';
	$cart_items 	= edd_get_payment_meta_cart_details( $payment_id );

	foreach ( $cart_items as $item ) {

		$price_id = edd_get_cart_item_price_id( $item );
		$files 		= edd_get_download_files( $item['id'], $price_id );

		if ( $files ) {
			foreach ( $files as $filekey => $file ) {
				$file_url = edd_get_download_file_url( $payment_data['key'], $payment_data['email'], $filekey, $item['id'], $price_id );

				$file_urls .= esc_html( $file_url ) . '<br/>';
			}
		} elseif( edd_is_bundled_product( $item['id'] ) ) {

			$bundled_products = edd_get_bundled_products( $item['id'] );

			foreach( $bundled_products as $bundle_item ) {

				$files = edd_get_download_files( $bundle_item );
				foreach ( $files as $filekey => $file ) {
					$file_url = edd_get_download_file_url( $payment_data['key'], $payment_data['email'], $filekey, $bundle_item, $price_id );
					$file_urls .= esc_html( $file_url ) . '<br/>';
				}

			}
		}

	}

	return $file_urls;
}

/**
 * Email template tag: name
 * The buyer's first name
 *
 * @param int $payment_id
 *
 * @return string name
 */
function edd_email_tag_name( $payment_id ) {
	$payment_data = edd_get_payment_meta( $payment_id );
	$email_name 	= edd_get_email_names( $payment_data['user_info'] );
	return $email_name[ 'name' ];
}

/**
 * Email template tag: fullname
 * The buyer's full name, first and last
 *
 * @param int $payment_id
 *
 * @return string fullname
 */
function edd_email_tag_fullname( $payment_id ) {
	$payment_data = edd_get_payment_meta( $payment_id );
	$email_name 	= edd_get_email_names( $payment_data['user_info'] );
	return $email_name[ 'fullname' ];
}

/**
 * Email template tag: username
 * The buyer's user name on the site, if they registered an account
 *
 * @param int $payment_id
 *
 * @return string username
 */
function edd_email_tag_username( $payment_id ) {
	$payment_data = edd_get_payment_meta( $payment_id );
	$email_name 	= edd_get_email_names( $payment_data['user_info'] );
	return $email_name[ 'username' ];
}

/**
 * Email template tag: user_email
 * The buyer's email address
 *
 * @param int $payment_id
 *
 * @return string user_email
 */
function edd_email_tag_user_email( $payment_id ) {
	return edd_get_payment_user_email( $payment_id );
}

/**
 * Email template tag: date
 * Date of purchase
 *
 * @param int $payment_id
 *
 * @return string date
 */
function edd_email_tag_date( $payment_id ) {
	$payment_data = edd_get_payment_meta( $payment_id );
	return date_i18n( get_option( 'date_format' ), strtotime( $payment_data[ 'date' ] ) );
}

/**
 * Email template tag: subtotal
 * Price of purchase before taxes
 *
 * @param int $payment_id
 *
 * @return string subtotal
 */
function edd_email_tag_subtotal( $payment_id ) {
	return edd_currency_filter( edd_format_amount( edd_get_payment_subtotal( $payment_id ) ) );
}

/**
 * Email template tag: tax
 * The taxed amount of the purchase
 *
 * @param int $payment_id
 *
 * @return string tax
 */
function edd_email_tag_tax( $payment_id ) {
	return edd_currency_filter( edd_format_amount( edd_get_payment_tax( $payment_id ) ) );
}

/**
 * Email template tag: price
 * The total price of the purchase
 *
 * @param int $payment_id
 *
 * @return string price
 */
function edd_email_tag_price( $payment_id ) {
	return edd_currency_filter( edd_format_amount( edd_get_payment_amount( $payment_id ) ) );
}

/**
 * Email template tag: payment_id
 * The unique ID number for this purchase
 *
 * @param int $payment_id
 *
 * @return int payment_id
 */
function edd_email_tag_payment_id( $payment_id ) {
	return $payment_id;
}

/**
 * Email template tag: receipt_id
 * The unique ID number for this purchase receipt
 *
 * @param int $payment_id
 *
 * @return string receipt_id
 */
function edd_email_tag_receipt_id( $payment_id ) {
	$payment_data = edd_get_payment_meta( $payment_id );
	return $payment_data[ 'key' ];
}

/**
 * Email template tag: payment_method
 * The method of payment used for this purchase
 *
 * @param int $payment_id
 *
 * @return string gateway
 */
function edd_email_tag_payment_method( $payment_id ) {
	return edd_get_gateway_checkout_label( get_post_meta( $payment_id, '_edd_payment_gateway', true ) );
}

/**
 * Email template tag: sitename
 * Your site name
 *
 * @param int $payment_id
 *
 * @return string sitename
 */
function edd_email_tag_sitename( $payment_id ) {
	return get_bloginfo( 'name' );
}

/**
 * Email template tag: receipt_link
 * Adds a link so users can view their receipt directly on your website if they are unable to view it in the browser correctly
 *
 * @param $int payment_id
 *
 * @return string receipt_link
 */
function edd_email_tag_receipt_link( $payment_id ) {
	$payment_data = edd_get_payment_meta( $payment_id );
	return sprintf( __( '%1$sView it in your browser.%2$s', 'edd' ), '<a href="' . add_query_arg( array ( 'payment_key' => $payment_data[ 'key' ], 'edd_action' => 'view_receipt' ), home_url() ) . '">', '</a>' );
}