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

function edd_email_tag_file_urls( $payment_id ) {
	return '!{file_urls}';
}

/**
 * Email template tag: name
 *
 * @param int $payment_id
 *
 * @return string The buyer's first name
 */
function edd_email_tag_name( $payment_id ) {
	$payment_data = edd_get_payment_meta( $payment_id );
	$email_name 	= edd_get_email_names( $payment_data['user_info'] );
	return $email_name[ 'name' ];
}

/**
 * Email template tag: fullname
 *
 * @param int $payment_id
 *
 * @return string The buyer's full name
 */
function edd_email_tag_fullname( $payment_id ) {
	$payment_data = edd_get_payment_meta( $payment_id );
	$email_name 	= edd_get_email_names( $payment_data['user_info'] );
	return $email_name[ 'fullname' ];
}

/**
 * Email template tag: username
 *
 * @param int $payment_id
 *
 * @return string The buyer's username
 */
function edd_email_tag_username( $payment_id ) {
	$payment_data = edd_get_payment_meta( $payment_id );
	$email_name 	= edd_get_email_names( $payment_data['user_info'] );
	return $email_name[ 'username' ];
}

/**
 * Email template tag: user_email
 *
 * @param int $payment_id
 *
 * @return string User's email
 */
function edd_email_tag_user_email( $payment_id ) {
	return edd_get_payment_user_email( $payment_id );
}

/**
 * Email template tag: date
 *
 * @param int $payment_id
 *
 * @return string Date of purchase
 */
function edd_email_tag_date( $payment_id ) {
	$payment_data = edd_get_payment_meta( $payment_id );
	return date_i18n( get_option( 'date_format' ), strtotime( $payment_data[ 'date' ] ) );
}

/**
 * Email template tag: subtotal
 *
 * @param int $payment_id
 *
 * @return string Price of purchase before taxes
 */
function edd_email_tag_subtotal( $payment_id ) {
	return edd_currency_filter( edd_format_amount( edd_get_payment_subtotal( $payment_id ) ) );
}

/**
 * Email template tag: tax
 *
 * @param int $payment_id
 *
 * @return string The taxed amount of the purchase
 */
function edd_email_tag_tax( $payment_id ) {
	return edd_currency_filter( edd_format_amount( edd_get_payment_tax( $payment_id ) ) );
}

/**
 * Email template tag: price
 *
 * @param int $payment_id
 *
 * @return string The total price of the purchase
 */
function edd_email_tag_price( $payment_id ) {
	return edd_currency_filter( edd_format_amount( edd_get_payment_amount( $payment_id ) ) );
}

/**
 * Email template tag: payment_id
 *
 * @param int $payment_id
 *
 * @return int The unique ID number for this purchase
 */
function edd_email_tag_payment_id( $payment_id ) {
	return $payment_id;
}

/**
 * Email template tag: receipt_id
 *
 * @param int $payment_id
 *
 * @return The method of payment used for this purchase
 */
function edd_email_tag_receipt_id( $payment_id ) {
	$payment_data = edd_get_payment_meta( $payment_id );
	return $payment_data[ 'key' ];
}

/**
 * Email template tag: payment_method
 *
 * @param int $payment_id
 *
 * @return string The method of payment used for this purchase
 */
function edd_email_tag_payment_method( $payment_id ) {
	return edd_get_gateway_checkout_label( get_post_meta( $payment_id, '_edd_payment_gateway', true ) );
}

/**
 * Email template tag: sitename
 *
 * @param int $payment_id
 *
 * @return string Your site name
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