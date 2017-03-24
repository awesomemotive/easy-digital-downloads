<?php
/**
 * AJAX Functions
 *
 * Process the front-end AJAX actions.
 *
 * @package     EDD
 * @subpackage  Functions/AJAX
 * @copyright   Copyright (c) 2015, Pippin Williamson
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
 * @return bool True when EDD AJAX is enabled (for the cart), false otherwise.
 */
function edd_is_ajax_enabled() {
	$retval = ! edd_is_ajax_disabled();
	return apply_filters( 'edd_is_ajax_enabled', $retval );
}

/**
 * Checks whether AJAX is disabled.
 *
 * @since 2.0
 * @since 2.7 Setting to disable AJAX was removed. See https://github.com/easydigitaldownloads/easy-digital-downloads/issues/4758
 * @return bool True when EDD AJAX is disabled (for the cart), false otherwise.
 */
function edd_is_ajax_disabled() {
	return apply_filters( 'edd_is_ajax_disabled', false );
}

/**
 * Check if AJAX works as expected
 *
 * @since 2.2
 * @return bool True if AJAX works, false otherwise
 */
function edd_test_ajax_works() {

	// Check if the Airplane Mode plugin is installed
	if ( class_exists( 'Airplane_Mode_Core' ) ) {

		$airplane = Airplane_Mode_Core::getInstance();

		if ( method_exists( $airplane, 'enabled' ) ) {

			if ( $airplane->enabled() ) {
				return true;
			}

		} else {

			if ( $airplane->check_status() == 'on' ) {
				return true;
			}
		}
	}

	add_filter( 'block_local_requests', '__return_false' );

	if ( get_transient( '_edd_ajax_works' ) ) {
		return true;
	}

	$params = array(
		'sslverify'  => false,
		'timeout'    => 30,
		'body'       => array(
			'action' => 'edd_test_ajax'
		)
	);

	$ajax  = wp_remote_post( edd_get_ajax_url(), $params );
	$works = true;

	if ( is_wp_error( $ajax ) ) {

		$works = false;

	} else {

		if( empty( $ajax['response'] ) ) {
			$works = false;
		}

		if( empty( $ajax['response']['code'] ) || 200 !== (int) $ajax['response']['code'] ) {
			$works = false;
		}

		if( empty( $ajax['response']['message'] ) || 'OK' !== $ajax['response']['message'] ) {
			$works = false;
		}

		if( ! isset( $ajax['body'] ) || 0 !== (int) $ajax['body'] ) {
			$works = false;
		}

	}

	if ( $works ) {
		set_transient( '_edd_ajax_works', '1', DAY_IN_SECONDS );
	}

	return $works;
}

/**
 * Get AJAX URL
 *
 * @since 1.3
 * @return string URL to the AJAX file to call during AJAX requests.
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
			'removed'       => 1,
			'subtotal'      => html_entity_decode( edd_currency_filter( edd_format_amount( edd_get_cart_subtotal() ) ), ENT_COMPAT, 'UTF-8' ),
			'total'         => html_entity_decode( edd_currency_filter( edd_format_amount( edd_get_cart_total() ) ), ENT_COMPAT, 'UTF-8' ),
			'cart_quantity' => html_entity_decode( edd_get_cart_quantity() ),
		);

		if ( edd_use_taxes() ) {
			$cart_tax = (float) edd_get_cart_tax();
			$return['tax'] = html_entity_decode( edd_currency_filter( edd_format_amount( $cart_tax ) ), ENT_COMPAT, 'UTF-8' );
		}

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

			if( $_POST['download_id'] == $options['price_id'] ) {
				$options = array();
			}

			parse_str( $_POST['post_data'], $post_data );

			if( isset( $options['price_id'] ) && isset( $post_data['edd_download_quantity_' . $options['price_id'] ] ) ) {

				$options['quantity'] = absint( $post_data['edd_download_quantity_' . $options['price_id'] ] );

			} else {

				$options['quantity'] = isset( $post_data['edd_download_quantity'] ) ? absint( $post_data['edd_download_quantity'] ) : 1;

			}

			$key = edd_add_to_cart( $_POST['download_id'], $options );

			$item = array(
				'id'      => $_POST['download_id'],
				'options' => $options
			);

			$item   = apply_filters( 'edd_ajax_pre_cart_item_template', $item );
			$items .= html_entity_decode( edd_get_cart_item_template( $key, $item, true ), ENT_COMPAT, 'UTF-8' );

		}

		$return = array(
			'subtotal'      => html_entity_decode( edd_currency_filter( edd_format_amount( edd_get_cart_subtotal() ) ), ENT_COMPAT, 'UTF-8' ),
			'total'         => html_entity_decode( edd_currency_filter( edd_format_amount( edd_get_cart_total() ) ), ENT_COMPAT, 'UTF-8' ),
			'cart_item'     => $items,
			'cart_quantity' => html_entity_decode( edd_get_cart_quantity() )
		);

		if ( edd_use_taxes() ) {
			$cart_tax = (float) edd_get_cart_tax();
			$return['tax'] = html_entity_decode( edd_currency_filter( edd_format_amount( $cart_tax ) ), ENT_COMPAT, 'UTF-8' );
		}

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

		$discount_code = sanitize_text_field( $_POST['code'] );

		$return = array(
			'msg'  => '',
			'code' => $discount_code
		);

		$user = '';

		if ( is_user_logged_in() ) {
			$user = get_current_user_id();
		} else {
			parse_str( $_POST['form'], $form );
			if ( ! empty( $form['edd_email'] ) ) {
				$user = urldecode( $form['edd_email'] );
			}
		}

		if ( edd_is_discount_valid( $discount_code, $user ) ) {
			$discount  = edd_get_discount_by_code( $discount_code );
			$amount    = edd_format_discount_rate( edd_get_discount_type( $discount->ID ), edd_get_discount_amount( $discount->ID ) );
			$discounts = edd_set_cart_discount( $discount_code );
			$total     = edd_get_cart_total( $discounts );

			$return = array(
				'msg'         => 'valid',
				'amount'      => $amount,
				'total_plain' => $total,
				'total'       => html_entity_decode( edd_currency_filter( edd_format_amount( $total ) ), ENT_COMPAT, 'UTF-8' ),
				'code'        => $discount_code,
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
		$options     = json_decode( stripslashes( $_POST['options'] ), true );

		EDD()->cart->set_item_quantity( $download_id, $quantity, $options );

		$return = array(
			'download_id' => $download_id,
			'quantity'    => EDD()->cart->get_item_quantity( $download_id, $options ),
			'subtotal'    => html_entity_decode( edd_currency_filter( edd_format_amount( EDD()->cart->get_subtotal() ) ), ENT_COMPAT, 'UTF-8' ),
			'taxes'       => html_entity_decode( edd_currency_filter( edd_format_amount( EDD()->cart->get_tax() ) ), ENT_COMPAT, 'UTF-8' ),
			'total'       => html_entity_decode( edd_currency_filter( edd_format_amount( EDD()->cart->get_total() ) ), ENT_COMPAT, 'UTF-8' )
		);

		// Allow for custom cart item quantity handling
		$return = apply_filters( 'edd_ajax_cart_item_quantity_response', $return );

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
	$cart     = ob_get_clean();
	$response = array(
		'html'         => $cart,
		'tax_raw'      => edd_get_cart_tax(),
		'tax'          => html_entity_decode( edd_cart_tax( false ), ENT_COMPAT, 'UTF-8' ),
		'tax_rate_raw' => edd_get_tax_rate(),
		'tax_rate'     => html_entity_decode( edd_get_formatted_tax_rate(), ENT_COMPAT, 'UTF-8' ),
		'total'        => html_entity_decode( edd_cart_total( false ), ENT_COMPAT, 'UTF-8' ),
		'total_raw'    => edd_get_cart_total(),
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
			'class'   => $_POST['field_name'] . '  edd-select',
			'options' => $states,
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

	$search   = esc_sql( sanitize_text_field( $_GET['s'] ) );
	$excludes = ( isset( $_GET['current_id'] ) ? (array) $_GET['current_id'] : array() );

	$no_bundles = isset( $_GET['no_bundles'] ) ? filter_var( $_GET['no_bundles'], FILTER_VALIDATE_BOOLEAN ) : false;
	if( true === $no_bundles ) {
		$bundles  = $wpdb->get_results( "SELECT post_id FROM $wpdb->postmeta WHERE meta_key = '_edd_product_type' AND meta_value = 'bundle';", ARRAY_A );
		$bundles  = wp_list_pluck( $bundles, 'post_id' );
		$excludes = array_merge( $excludes, $bundles );
	}

	$variations = isset( $_GET['variations'] ) ? filter_var( $_GET['variations'], FILTER_VALIDATE_BOOLEAN ) : false;

	$excludes = array_unique( array_map( 'absint', $excludes ) );
	$exclude  = implode( ",", $excludes );

	$results = array();

	// Setup the SELECT statement
	$select = "SELECT ID,post_title FROM $wpdb->posts ";

	// Setup the WHERE clause
	$where = "WHERE `post_type` = 'download' and `post_title` LIKE '%s' ";

	// If we have items to exclude, exclude them
	if( ! empty( $exclude ) ) {
		$where .= "AND `ID` NOT IN (" . $exclude . ") ";
	}

	// If the user can't edit products, limit to just published items
	if( ! current_user_can( 'edit_products' ) ) {
		$where .= "AND `post_status` = 'publish' ";
	}

	// Limit the result sets
	$limit = "LIMIT 50";

	$sql = $select . $where . $limit;

	$prepared_statement = $wpdb->prepare( $sql, '%' . $search . '%' );

	$items = $wpdb->get_results( $prepared_statement );

	if( $items ) {

		foreach( $items as $item ) {

			$results[] = array(
				'id'   => $item->ID,
				'name' => $item->post_title
			);

			if ( $variations && edd_has_variable_prices( $item->ID ) ) {
				$prices = edd_get_variable_prices( $item->ID );

				foreach ( $prices as $key => $value ) {
					$name   = ! empty( $value['name'] )   ? $value['name']   : '';
					$amount = ! empty( $value['amount'] ) ? $value['amount'] : '';
					$index  = ! empty( $value['index'] )  ? $value['index']  : $key;

					if ( $name && $index ) {
						$results[] = array(
							'id'   => $item->ID . '_' . $key,
							'name' => esc_html( $item->post_title . ': ' . $name ),
						);
					}
				}
			}
		}

	} else {

		$results[] = array(
			'id'   => 0,
			'name' => __( 'No results found', 'easy-digital-downloads' )
		);

	}

	echo json_encode( $results );

	edd_die();
}
add_action( 'wp_ajax_edd_download_search', 'edd_ajax_download_search' );
add_action( 'wp_ajax_nopriv_edd_download_search', 'edd_ajax_download_search' );

/**
 * Search the customers database via AJAX
 *
 * @since 2.2
 * @return void
 */
function edd_ajax_customer_search() {
	global $wpdb;

	$search  = esc_sql( sanitize_text_field( $_GET['s'] ) );
	$results = array();
	$customer_view_role = apply_filters( 'edd_view_customers_role', 'view_shop_reports' );
	if ( ! current_user_can( $customer_view_role ) ) {
		$customers = array();
	} else {
		$select = "SELECT id, name, email FROM {$wpdb->prefix}edd_customers ";
		if ( is_numeric( $search ) ) {
			$where = "WHERE `id` LIKE '%$search%' OR `user_id` LIKE '%$search%' ";
		} else {
			$where = "WHERE `name` LIKE '%$search%' OR `email` LIKE '%$search%' ";
		}
		$limit = "LIMIT 50";

		$customers = $wpdb->get_results( $select . $where . $limit );
	}

	if( $customers ) {

		foreach( $customers as $customer ) {

			$results[] = array(
				'id'   => $customer->id,
				'name' => $customer->name . '(' .  $customer->email . ')'
			);
		}

	} else {

		$customers[] = array(
			'id'   => 0,
			'name' => __( 'No results found', 'easy-digital-downloads' )
		);

	}

	echo json_encode( $results );

	edd_die();
}
add_action( 'wp_ajax_edd_customer_search', 'edd_ajax_customer_search' );

/**
 * Search the users database via AJAX
 *
 * @since 2.6.9
 * @return void
 */
function edd_ajax_user_search() {
	global $wpdb;

	$search         = esc_sql( sanitize_text_field( $_GET['s'] ) );
	$results        = array();
	$user_view_role = apply_filters( 'edd_view_users_role', 'view_shop_reports' );

	if ( ! current_user_can( $user_view_role ) ) {
		$results = array();
	} else {
		$user_args = array(
			'search' => '*' . esc_attr( $search ) . '*',
			'number' => 50,
		);

		$users = get_users( $user_args );
	}

	if ( $users ) {

		foreach( $users as $user ) {

			$results[] = array(
				'id'   => $user->ID,
				'name' => $user->display_name,
			);
		}

	} else {

		$results[] = array(
			'id'   => 0,
			'name' => __( 'No users found', 'easy-digital-downloads' )
		);

	}

	echo json_encode( $results );

	edd_die();
}
add_action( 'wp_ajax_edd_user_search', 'edd_ajax_user_search' );

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

				if( isset( $_POST['all_prices'] ) ) {
					$ajax_response .= '<option value="">' . __( 'All Prices', 'easy-digital-downloads' ) . '</option>';
				}

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
		$exclude      = trim( $_POST['exclude'] );

		$get_users_args = array(
			'number' => 9999,
			'search' => $search_query . '*'
		);

		if ( ! empty( $exclude ) ) {
			$exclude_array = explode( ',', $exclude );
			$get_users_args['exclude'] = $exclude_array;
		}

		$get_users_args = apply_filters( 'edd_search_users_args', $get_users_args );

		$found_users = apply_filters( 'edd_ajax_found_users', get_users( $get_users_args ), $search_query );

		$user_list = '<ul>';
		if( $found_users ) {
			foreach( $found_users as $user ) {
				$user_list .= '<li><a href="#" data-userid="' . esc_attr( $user->ID ) . '" data-login="' . esc_attr( $user->user_login ) . '">' . esc_html( $user->user_login ) . '</a></li>';
			}
		} else {
			$user_list .= '<li>' . __( 'No users found', 'easy-digital-downloads' ) . '</li>';
		}
		$user_list .= '</ul>';

		echo json_encode( array( 'results' => $user_list ) );

	}
	die();
}
add_action( 'wp_ajax_edd_search_users', 'edd_ajax_search_users' );
