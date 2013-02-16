<?php
/**
 * AJAX Functions
 *
 * Process the front-end AJAX actions.
 *
 * @package     Easy Digital Downloads
 * @subpackage  AJAX
 * @copyright   Copyright (c) 2013, Pippin Williamson
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       1.0
*/

// Exit if accessed directly
if ( !defined( 'ABSPATH' ) ) exit;

/**
 * AJAX enabled
 *
 * Checks whether AJAX is enabled.
 *
 * @access      private
 * @since       1.0
 * @deprecated  1.0.8.3
 * @return      boolean
*/

function edd_is_ajax_enabled() {
	global $edd_options;
	if( ! isset( $edd_options['disable_ajax_cart'] ) ) {
		return true;
	}
	return false;
}

/**
 * AJAX Remove From Cart
 *
 * Removes item from cart.
 *
 * @access      private
 * @since       1.0
 * @return      string
*/

function edd_ajax_remove_from_cart() {
	if( isset( $_POST['cart_item'] ) && check_ajax_referer( 'edd_ajax_nonce', 'nonce' ) ) {
		edd_remove_from_cart( $_POST['cart_item'] );
		echo 'removed';
	}
	die();
}
add_action( 'wp_ajax_edd_remove_from_cart', 'edd_ajax_remove_from_cart' );
add_action( 'wp_ajax_nopriv_edd_remove_from_cart', 'edd_ajax_remove_from_cart' );


/**
 * AJAX Add To Cart
 *
 * Adds item to the cart.
 *
 * @access      private
 * @since       1.0
 * @return      string
*/

function edd_ajax_add_to_cart() {
	if( isset( $_POST['download_id'] ) && check_ajax_referer( 'edd_ajax_nonce', 'nonce' ) ) {
		global $post;

		$to_add = array();

		if( isset( $_POST['price_ids'] ) && is_array( $_POST['price_ids'] ) ) {

			foreach( $_POST['price_ids'] as $price ) {
				$to_add[] = array( 'price_id' => $price );
			}

		}

		foreach( $to_add as $options ) {

			if( ! edd_item_in_cart( $_POST['download_id'], $options ) ) {

				$key          = edd_add_to_cart( $_POST['download_id'], $options );

				$item         = array(
					'id'      => $_POST['download_id'],
					'options' => $options
				);

				$cart_item    = edd_get_cart_item_template( $key, $item, true );

				echo $cart_item;

			} else {
				echo 'incart';
			}
		}
	}
	die();
}
add_action( 'wp_ajax_edd_add_to_cart', 'edd_ajax_add_to_cart' );
add_action( 'wp_ajax_nopriv_edd_add_to_cart', 'edd_ajax_add_to_cart' );


/**
 * AJAX Validate Discount
 *
 * Validates the supplied discount.
 *
 * @access      private
 * @since       1.0
 * @return      string
*/

function edd_ajax_apply_discount() {
	if( isset( $_POST['code'] ) && check_ajax_referer( 'edd_checkout_nonce', 'nonce' ) ) {

		$user = isset( $_POST['user'] ) ? $_POST['user'] : $_POST['email'];

		$return = array(
			'msg'  => '',
			'code' => $_POST['code']
		);

		if( edd_is_discount_used( $_POST['code'], $user ) ) {  // Called twice if discount is not used (again by edd_is_discount_valid) but allows for beter usr msg and less execution if discount is used.

			$return['msg']  = __('This discount code has been used already', 'edd');

		} else {

			if( edd_is_discount_valid( $_POST['code'], $user ) ) {

				$discount  = edd_get_discount_by_code( $_POST['code'] );
				$amount    = edd_format_discount_rate( edd_get_discount_type( $discount->ID ), edd_get_discount_amount( $discount->ID ) );
				$discounts = edd_set_cart_discount( $_POST['code'] );
				$total     = edd_get_cart_total( $discounts );

				$return = array(
					'msg'    => 'valid',
					'amount' => $amount,
					'total'  => html_entity_decode( edd_currency_filter( edd_format_amount( $total ) ), ENT_COMPAT, 'UTF-8' ),
					'code'   => $_POST['code'],
					'html'   => edd_get_cart_discounts_html( $discounts )
				);

			} else {

				$return['msg']  = __('The discount you entered is invalid', 'edd');

			}
		}
		echo json_encode($return);
	}
	die();
}
add_action( 'wp_ajax_edd_apply_discount', 'edd_ajax_apply_discount' );
add_action( 'wp_ajax_nopriv_edd_apply_discount', 'edd_ajax_apply_discount' );


/**
 * Load Checkout Login Fields
 *
 * Echoes the login fields
 *
 * @access      private
 * @since       1.0
 * @return      void
*/

function edd_load_checkout_login_fields() {
	do_action( 'edd_purchase_form_login_fields' );
	die();
}
add_action('wp_ajax_nopriv_checkout_login', 'edd_load_checkout_login_fields');


/**
 * Load Checkout Register Fields
 *
 * Echoes the register fields
 *
 * @access      private
 * @since       1.0
 * @return      void
*/

function edd_load_checkout_register_fields() {
	do_action( 'edd_purchase_form_register_fields' );
	die();
}
add_action('wp_ajax_nopriv_checkout_register', 'edd_load_checkout_register_fields');


/**
 * Get Download Title
 *
 * Used only in the admin
 *
 * @access      private
 * @since       1.0
 * @return      string
*/

function edd_ajax_get_download_title() {
	if( isset( $_POST['download_id'] ) ) {
		$title = get_the_title( $_POST['download_id'] );
		if( $title ) {
			echo $title;
		} else {
			echo 'fail';
		}
	}
	die();
}
add_action( 'wp_ajax_edd_get_download_title', 'edd_ajax_get_download_title' );
add_action( 'wp_ajax_nopriv_edd_get_download_title', 'edd_ajax_get_download_title' );



/**
 * Opt into local taxes
 *
 * @access      private
 * @since       1.4.1
 * @return      string
*/

function edd_ajax_opt_into_local_taxes() {
	if( check_ajax_referer( 'edd_checkout_nonce', 'nonce' ) )
		if( edd_opt_into_local_taxes() )
			die('1');
	die('-1');
}
add_action( 'wp_ajax_edd_local_tax_opt_in', 'edd_ajax_opt_into_local_taxes' );
add_action( 'wp_ajax_nopriv_edd_local_tax_opt_in', 'edd_ajax_opt_into_local_taxes' );


/**
 * Opt out of local taxes
 *
 * @access      private
 * @since       1.4.1
 * @return      string
*/

function edd_ajax_opt_out_local_taxes() {
	if( check_ajax_referer( 'edd_checkout_nonce', 'nonce' ) )
		if( edd_opt_out_local_taxes() )
			die('1');
	die('-1');
}
add_action( 'wp_ajax_edd_local_tax_opt_out', 'edd_ajax_opt_out_local_taxes' );
add_action( 'wp_ajax_nopriv_edd_local_tax_opt_out', 'edd_ajax_opt_out_local_taxes' );


/**
 * Get AJAX URL
 *
 * @access      public
 * @since       1.3
 * @return      string
*/

function edd_get_ajax_url() {

	$scheme = defined( 'FORCE_SSL_ADMIN' ) && FORCE_SSL_ADMIN ? 'https' : 'admin';

	$current_url = edd_get_current_page_url();
	$ajax_url = admin_url( 'admin-ajax.php', $scheme );

	if( preg_match( '/^https/', $current_url ) && ! preg_match( '/^https/', $ajax_url ) ) {
		$ajax_url = preg_replace( '/^http/', 'https', $ajax_url );
	}

	return apply_filters( 'edd_ajax_url', $ajax_url );
}