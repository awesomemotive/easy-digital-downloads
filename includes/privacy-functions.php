<?php
/**
 * Privacy Functions
 *
 * @package     EDD
 * @subpackage  Functions
 * @copyright   Copyright (c) 2018, Easy Digital Downloads, LLC
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       2.9.x
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;


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
' ) ) );

	$content .= "\n\n";

	$additional_collection = array(
		__( 'Location and traffic data (including IP address and browser type) if you place an order, or if we need to estimate taxes and shipping costs based on your location.', 'easy-digital-downloads' ),
		__( 'Product pages visited and content viewed whist your session is active.', 'easy-digital-downloads' ),
		__( 'Your comments and product reviews if you choose to leave them on our website.', 'easy-digital-downloads' ),
		__( 'Account email/password to allow you to access your account, if you have one.', 'easy-digital-downloads' ),
		__( 'If you choose to create an account with us, your name, address, and email address, which will be used to populate the checkout for future orders.', 'easy-digital-downloads' ),
	);

	$additional_collection = apply_filters( 'edd_privacy_policy_additinal_collection', $additional_collection );

	$content .= __( 'Additionally we may also collect the following information:', 'easy-digital-downloads' ) . "\n";
	if ( ! empty( $additional_collection ) ) {
		foreach ( $additional_collection as $item ) {
			$content .= '-' . $item . "\n";
		}
	}

	wp_add_privacy_policy_content( 'Easy Digital Downloads', wpautop( $content ) );
}
add_action( 'admin_init', 'edd_register_privacy_policy_template' );