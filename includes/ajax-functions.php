<?php
/**
 * AJAX Functions
 *
 * Process the front-end AJAX actions.
 *
 * @package     EDD
 * @subpackage  Functions/AJAX
 * @copyright   Copyright (c) 2014, Pippin Williamson
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       1.0
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Checks whether AJAX is enabled.
 *
 * This will be deprecated soon in favor of edd_is_ajax_disabled()
 *
 * @since 1.0
 * @return bool
 */
function edd_is_ajax_enabled() {
	$retval = ! edd_is_ajax_disabled();
	return apply_filters( 'edd_is_ajax_enabled', $retval );
}

/**
 * Checks whether AJAX is disabled.
 *
 * @since 2.0
 * @return bool
 */
function edd_is_ajax_disabled() {
	$retval = ! edd_get_option( 'enable_ajax_cart' );
	return apply_filters( 'edd_is_ajax_disabled', $retval );
}


/**
 * Get AJAX URL
 *
 * @since 1.3
 * @return string
*/
function edd_get_ajax_url() {
	$scheme = defined( 'FORCE_SSL_ADMIN' ) && FORCE_SSL_ADMIN ? 'https' : 'admin';

	$current_url = edd_get_current_page_url();
	$ajax_url    = admin_url( 'admin-ajax.php', $scheme );

	if ( preg_match( '/^https/', $current_url ) && ! preg_match( '/^https/', $ajax_url ) ) {
		$ajax_url = preg_replace( '/^http/', 'https', $ajax_url );
	}

	return apply_filters( 'edd_ajax_url', $ajax_url );
}

/**
 * Removes item from cart via AJAX.
 *
 * @since 1.0
 * @return void
 */
function edd_ajax_remove_from_cart() {
	if ( isset( $_POST['cart_item'] ) ) {

		edd_remove_from_cart( $_POST['cart_item'] );

		$return = array(
			'removed'  => 1,
			'subtotal' => html_entity_decode( edd_currency_filter( edd_format_amount( edd_get_cart_subtotal() ) ), ENT_COMPAT, 'UTF-8' ),
			'total'    => html_entity_decode( edd_currency_filter( edd_format_amount( edd_get_cart_total() ) ), ENT_COMPAT, 'UTF-8' )
		);

		echo json_encode( $return );

	}
	edd_die();
}
add_action( 'wp_ajax_edd_remove_from_cart', 'edd_ajax_remove_from_cart' );
add_action( 'wp_ajax_nopriv_edd_remove_from_cart', 'edd_ajax_remove_from_cart' );

/**
 * Adds item to the cart via AJAX.
 *
 * @since 1.0
 * @return void
 */
function edd_ajax_add_to_cart() {
	if ( isset( $_POST['download_id'] ) ) {
		$to_add = array();

		if ( isset( $_POST['price_ids'] ) && is_array( $_POST['price_ids'] ) ) {
			foreach ( $_POST['price_ids'] as $price ) {
				$to_add[] = array( 'price_id' => $price );
			}
		}

		$items = '';

		foreach ( $to_add as $options ) {

			if( $_POST['download_id'] == $options['price_id'] )
				$options = array();

			$key = edd_add_to_cart( $_POST['download_id'], $options );

			$item = array(
				'id'      => $_POST['download_id'],
				'options' => $options
			);

			$item   = apply_filters( 'edd_ajax_pre_cart_item_template', $item );
			$items .= html_entity_decode( edd_get_cart_item_template( $key, $item, true ), ENT_COMPAT, 'UTF-8' );

		}

		$return = array(
			'subtotal'  => html_entity_decode( edd_currency_filter( edd_format_amount( edd_get_cart_subtotal() ) ), ENT_COMPAT, 'UTF-8' ),
			'total'     => html_entity_decode( edd_currency_filter( edd_format_amount( edd_get_cart_total() ) ), ENT_COMPAT, 'UTF-8' ),
			'cart_item' => $items
		);

		echo json_encode( $return );
	}
	edd_die();
}
add_action( 'wp_ajax_edd_add_to_cart', 'edd_ajax_add_to_cart' );
add_action( 'wp_ajax_nopriv_edd_add_to_cart', 'edd_ajax_add_to_cart' );



/**
 * Gets the cart's subtotal via AJAX.
 *
 * @since 1.0
 * @return void
 */
function edd_ajax_get_subtotal() {
	echo edd_currency_filter( edd_get_cart_subtotal() );
	edd_die();
}

add_action( 'wp_ajax_edd_get_subtotal', 'edd_ajax_get_subtotal' );
add_action( 'wp_ajax_nopriv_edd_get_subtotal', 'edd_ajax_get_subtotal' );

/**
 * Validates the supplied discount sent via AJAX.
 *
 * @since 1.0
 * @return void
 */
function edd_ajax_apply_discount() {
	if ( isset( $_POST['code'] ) ) {

		$discount_code = $_POST['code'];

		$return = array(
			'msg'  => '',
			'code' => $discount_code
		);

		if ( edd_is_discount_valid( $discount_code ) ) {
			$discount  = edd_get_discount_by_code( $discount_code );
			$amount    = edd_format_discount_rate( edd_get_discount_type( $discount->ID ), edd_get_discount_amount( $discount->ID ) );
			$discounts = edd_set_cart_discount( $discount_code );
			$total     = edd_get_cart_total( $discounts );

			$return = array(
				'msg'         => 'valid',
				'amount'      => $amount,
				'total_plain' => $total,
				'total'       => html_entity_decode( edd_currency_filter( edd_format_amount( $total ) ), ENT_COMPAT, 'UTF-8' ),
				'code'        => $_POST['code'],
				'html'        => edd_get_cart_discounts_html( $discounts )
			);
		} else {
			$errors = edd_get_errors();
			$return['msg']  = $errors['edd-discount-error'];
			edd_unset_error( 'edd-discount-error' );
		}

		// Allow for custom discount code handling
		$return = apply_filters( 'edd_ajax_discount_response', $return );

		echo json_encode($return);
	}
	edd_die();
}
add_action( 'wp_ajax_edd_apply_discount', 'edd_ajax_apply_discount' );
add_action( 'wp_ajax_nopriv_edd_apply_discount', 'edd_ajax_apply_discount' );

/**
 * Validates the supplied discount sent via AJAX.
 *
 * @since 1.0
 * @return void
 */
function edd_ajax_update_cart_item_quantity() {
	if ( ! empty( $_POST['quantity'] ) && ! empty( $_POST['download_id'] ) ) {

		$download_id = absint( $_POST['download_id'] );
		$quantity    = absint( $_POST['quantity'] );

		edd_set_cart_item_quantity( $download_id, absint( $_POST['quantity'] ) );
		$total = edd_get_cart_total();

		$return = array(
			'download_id' => $download_id,
			'quantity'    => $quantity,
			'subtotal'    => html_entity_decode( edd_currency_filter( edd_format_amount( edd_get_cart_subtotal() ) ), ENT_COMPAT, 'UTF-8' ),
			'total'       => html_entity_decode( edd_currency_filter( edd_format_amount( $total ) ), ENT_COMPAT, 'UTF-8' )
		);
		echo json_encode($return);
	}
	edd_die();
}
add_action( 'wp_ajax_edd_update_quantity', 'edd_ajax_update_cart_item_quantity' );
add_action( 'wp_ajax_nopriv_edd_update_quantity', 'edd_ajax_update_cart_item_quantity' );

/**
 * Removes a discount code from the cart via ajax
 *
 * @since 1.7
 * @return void
 */
function edd_ajax_remove_discount() {
	if ( isset( $_POST['code'] ) ) {

		edd_unset_cart_discount( urldecode( $_POST['code'] ) );

		$total = edd_get_cart_total();

		$return = array(
			'total'     => html_entity_decode( edd_currency_filter( edd_format_amount( $total ) ), ENT_COMPAT, 'UTF-8' ),
			'code'      => $_POST['code'],
			'discounts' => edd_get_cart_discounts(),
			'html'      => edd_get_cart_discounts_html()
		);

		echo json_encode( $return );
	}
	edd_die();
}
add_action( 'wp_ajax_edd_remove_discount', 'edd_ajax_remove_discount' );
add_action( 'wp_ajax_nopriv_edd_remove_discount', 'edd_ajax_remove_discount' );

/**
 * Loads Checkout Login Fields the via AJAX
 *
 * @since 1.0
 * @return void
 */
function edd_load_checkout_login_fields() {
	do_action( 'edd_purchase_form_login_fields' );
	edd_die();
}
add_action('wp_ajax_nopriv_checkout_login', 'edd_load_checkout_login_fields');

/**
 * Load Checkout Register Fields via AJAX
 *
 * @since 1.0
 * @return void
*/
function edd_load_checkout_register_fields() {
	do_action( 'edd_purchase_form_register_fields' );
	edd_die();
}
add_action('wp_ajax_nopriv_checkout_register', 'edd_load_checkout_register_fields');

/**
 * Get Download Title via AJAX (used only in WordPress Admin)
 *
 * @since 1.0
 * @return void
 */
function edd_ajax_get_download_title() {
	if ( isset( $_POST['download_id'] ) ) {
		$title = get_the_title( $_POST['download_id'] );
		if ( $title ) {
			echo $title;
		} else {
			echo 'fail';
		}
	}
	edd_die();
}
add_action( 'wp_ajax_edd_get_download_title', 'edd_ajax_get_download_title' );
add_action( 'wp_ajax_nopriv_edd_get_download_title', 'edd_ajax_get_download_title' );

/**
 * Recalculate cart taxes
 *
 * @since 1.6
 * @return void
 */
function edd_ajax_recalculate_taxes() {
	if ( ! edd_get_cart_contents() ) {
		return false;
	}

	if ( empty( $_POST['billing_country'] ) ) {
		$_POST['billing_country'] = edd_get_shop_country();
	}

	ob_start();
	edd_checkout_cart();
	$cart = ob_get_clean();
	$response = array(
		'html'  => $cart,
		'total' => html_entity_decode( edd_cart_total( false ), ENT_COMPAT, 'UTF-8' ),
	);

	echo json_encode( $response );

	edd_die();
}
add_action( 'wp_ajax_edd_recalculate_taxes', 'edd_ajax_recalculate_taxes' );
add_action( 'wp_ajax_nopriv_edd_recalculate_taxes', 'edd_ajax_recalculate_taxes' );

/**
 * Retrieve a states drop down
 *
 * @since 1.6
 * @return void
 */
function edd_ajax_get_states_field() {
	if( empty( $_POST['country'] ) ) {
		$_POST['country'] = edd_get_shop_country();
	}
	$states = edd_get_shop_states( $_POST['country'] );

	if( ! empty( $states ) ) {

		$args = array(
			'name'    => $_POST['field_name'],
			'id'      => $_POST['field_name'],
			'options' => edd_get_shop_states( $_POST['country'] ),
			'show_option_all'  => false,
			'show_option_none' => false
		);

		$response = EDD()->html->select( $args );

	} else {

		$response = 'nostates';
	}

	echo $response;

	edd_die();
}
add_action( 'wp_ajax_edd_get_shop_states', 'edd_ajax_get_states_field' );
add_action( 'wp_ajax_nopriv_edd_get_shop_states', 'edd_ajax_get_states_field' );

/**
 * Retrieve a states drop down
 *
 * @since 1.6
 * @return void
 */
function edd_ajax_download_search() {
	global $wpdb;

	$search  = esc_sql( sanitize_text_field( $_GET['s'] ) );
	$results = array();
	if ( current_user_can( 'edit_products' ) ) {
		$items = $wpdb->get_results( "SELECT ID,post_title FROM $wpdb->posts WHERE `post_type` = 'download' AND `post_title` LIKE '%$search%' LIMIT 50" );
	} else {
		$items = $wpdb->get_results( "SELECT ID,post_title FROM $wpdb->posts WHERE `post_type` = 'download' AND `post_status` = 'publish' AND `post_title` LIKE '%$search%' LIMIT 50" );
	}

	if( $items ) {

		foreach( $items as $item ) {

			$results[] = array(
				'id'   => $item->ID,
				'name' => $item->post_title
			);
		}

	} else {

		$items[] = array(
			'id'   => 0,
			'name' => __( 'No results found', 'edd' )
		);

	}

	echo json_encode( $results );

	edd_die();
}
add_action( 'wp_ajax_edd_download_search', 'edd_ajax_download_search' );
add_action( 'wp_ajax_nopriv_edd_download_search', 'edd_ajax_download_search' );

/**
 * Check for Download Price Variations via AJAX (this function can only be used
 * in WordPress Admin). This function is used for the Edit Payment screen when downloads
 * are added to the purchase. When each download is chosen, an AJAX call is fired
 * to this function which will check if variable prices exist for that download.
 * If they do, it will output a dropdown of all the variable prices available for
 * that download.
 *
 * @author Sunny Ratilal
 * @since 1.5
 * @return void
 */
function edd_check_for_download_price_variations() {
	if( ! current_user_can( 'edit_products' ) ) {
		die( '-1' );
	}

	$download_id = intval( $_POST['download_id'] );
	$download    = get_post( $download_id );

	if( 'download' != $download->post_type ) {
		die( '-2' );
	}

	if ( edd_has_variable_prices( $download_id ) ) {
		$variable_prices = edd_get_variable_prices( $download_id );

		if ( $variable_prices ) {
			$ajax_response = '<select class="edd_price_options_select edd-select edd-select" name="edd_price_option">';
				foreach ( $variable_prices as $key => $price ) {
					$ajax_response .= '<option value="' . esc_attr( $key ) . '">' . esc_html( $price['name'] )  . '</option>';
				}
			$ajax_response .= '</select>';
			echo $ajax_response;
		}

	}

	edd_die();
}
add_action( 'wp_ajax_edd_check_for_download_price_variations', 'edd_check_for_download_price_variations' );


/**
 * Searches for users via ajax and returns a list of results
 *
 * @since 2.0
 * @return void
 */
function edd_ajax_search_users() {

	if( current_user_can( 'manage_shop_settings' ) ) {

		$search_query = trim( $_POST['user_name'] );

		$found_users = get_users( array(
				'number' => 9999,
				'search' => $search_query . '*'
			)
		);

		$user_list = '<ul>';
		if( $found_users ) {
			foreach( $found_users as $user ) {
				$user_list .= '<li><a href="#" data-login="' . esc_attr( $user->user_login ) . '">' . esc_html( $user->user_login ) . '</a></li>';
			}
		} else {
			$user_list .= '<li>' . __( 'No users found', 'edd' ) . '</li>';
		}
		$user_list .= '</ul>';

		echo json_encode( array( 'results' => $user_list ) );

	}
	die();
}
add_action( 'wp_ajax_edd_search_users', 'edd_ajax_search_users' );