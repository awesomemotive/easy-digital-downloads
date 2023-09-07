<?php
/**
 * AJAX Functions
 *
 * Process the front-end AJAX actions.
 *
 * @package     EDD
 * @subpackage  Functions/AJAX
 * @copyright   Copyright (c) 2018, Easy Digital Downloads, LLC
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       1.0
 */

// Exit if accessed directly
defined( 'ABSPATH' ) || exit;

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

	$works = true;
	$ajax  = wp_safe_remote_post( esc_url_raw( edd_get_ajax_url() ), array(
		'sslverify'  => false,
		'timeout'    => 30,
		'body'       => array(
			'action' => 'edd_test_ajax'
		)
	) );

	if ( is_wp_error( $ajax ) ) {
		$works = false;

	} else {

		if ( empty( $ajax['response'] ) ) {
			$works = false;
		}

		if ( empty( $ajax['response']['code'] ) || 200 !== (int) $ajax['response']['code'] ) {
			$works = false;
		}

		if ( empty( $ajax['response']['message'] ) || 'OK' !== $ajax['response']['message'] ) {
			$works = false;
		}

		if ( ! isset( $ajax['body'] ) || 0 !== (int) $ajax['body'] ) {
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
	if ( ! isset( $_POST['nonce'] ) ) {
		edd_debug_log( __( 'Missing nonce when removing an item from the cart. Please read the following for more information: https://easydigitaldownloads.com/development/2018/07/05/important-update-to-ajax-requests-in-easy-digital-downloads-2-9-4', 'easy-digital-downloads' ), true );
	}

	if ( isset( $_POST['cart_item'] ) && isset( $_POST['nonce'] ) ) {


		$cart_item = absint( $_POST['cart_item'] );
		$nonce     = sanitize_text_field( $_POST['nonce'] );

		$nonce_verified = wp_verify_nonce( $nonce, 'edd-remove-cart-widget-item' );

		if ( false === $nonce_verified ) {
			$return = array( 'removed' => 0 );
		} else {
			edd_remove_from_cart( $cart_item );

			$return = array(
				'removed'       => 1,
				'subtotal'      => html_entity_decode( edd_currency_filter( edd_format_amount( edd_get_cart_subtotal() ) ), ENT_COMPAT, 'UTF-8' ),
				'total'         => html_entity_decode( edd_currency_filter( edd_format_amount( edd_get_cart_total() ) ), ENT_COMPAT, 'UTF-8' ),
				'cart_quantity' => html_entity_decode( edd_get_cart_quantity() ),
			);

			if ( edd_use_taxes() ) {
				$cart_tax      = (float) edd_get_cart_tax();
				$return['tax'] = html_entity_decode( edd_currency_filter( edd_format_amount( $cart_tax ) ), ENT_COMPAT, 'UTF-8' );
			}
		}

		$return = apply_filters( 'edd_ajax_remove_from_cart_response', $return );

		echo json_encode( $return );

	}
	edd_die();
}
add_action( 'wp_ajax_edd_remove_from_cart',        'edd_ajax_remove_from_cart' );
add_action( 'wp_ajax_nopriv_edd_remove_from_cart', 'edd_ajax_remove_from_cart' );

/**
 * Adds item to the cart via AJAX.
 *
 * @since 1.0
 * @return void
 */
function edd_ajax_add_to_cart() {
	if ( ! isset( $_POST['download_id'] ) ) {
		edd_die();
	}

	$download_id = absint( $_POST['download_id'] );
	$request_validated = false;
	if ( isset( $_POST['timestamp'] ) && isset( $_POST['token'] ) && EDD\Utils\Tokenizer::is_token_valid( $_POST['token'], $_POST['timestamp'] ) ) {
		$request_validated = true;
	} elseif ( isset( $_POST['nonce'] ) && wp_verify_nonce( $_POST['nonce'], 'edd-add-to-cart-' . $download_id ) ) {
		$request_validated = true;
	}

	if ( ! $request_validated ) {
		edd_debug_log( __( 'Missing nonce when adding an item to the cart. Please read the following for more information: https://easydigitaldownloads.com/development/2018/07/05/important-update-to-ajax-requests-in-easy-digital-downloads-2-9-4', 'easy-digital-downloads' ), true );
		edd_die( '', '', 403 );
	}

	$to_add = array();

	if ( isset( $_POST['price_ids'] ) && is_array( $_POST['price_ids'] ) ) {
		foreach ( $_POST['price_ids'] as $price ) {
			$to_add[] = array( 'price_id' => $price );
		}
	}

	$items = '';

	if ( isset( $_POST['post_data'] ) ) {
		parse_str( $_POST['post_data'], $post_data );
	} else {
		$post_data = array();
	}

	foreach ( $to_add as $options ) {

		if ( $_POST['download_id'] == $options['price_id'] ) {
			$options = array();
		}

		if ( isset( $options['price_id'] ) && isset( $post_data['edd_download_quantity_' . $options['price_id'] ] ) ) {

			$options['quantity'] = absint( $post_data['edd_download_quantity_' . $options['price_id'] ] );

		} else {

			$options['quantity'] = isset( $post_data['edd_download_quantity'] ) ? absint( $post_data['edd_download_quantity'] ) : 1;

		}

		$key = edd_add_to_cart( $_POST['download_id'], $options );

		$item = array(
			'id'      => $_POST['download_id'],
			'options' => $options,
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
		$cart_tax      = (float) edd_get_cart_tax();
		$return['tax'] = html_entity_decode( edd_currency_filter( edd_format_amount( $cart_tax ) ), ENT_COMPAT, 'UTF-8' );
	}

	$return = apply_filters( 'edd_ajax_add_to_cart_response', $return );

	echo json_encode( $return );
	edd_die();
}
add_action( 'wp_ajax_edd_add_to_cart',        'edd_ajax_add_to_cart' );
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
add_action( 'wp_ajax_edd_get_subtotal',        'edd_ajax_get_subtotal' );
add_action( 'wp_ajax_nopriv_edd_get_subtotal', 'edd_ajax_get_subtotal' );

/**
 * Validates the supplied discount sent via AJAX.
 *
 * @since 1.0
 * @return void
 */
function edd_ajax_apply_discount() {
	if ( isset( $_POST['code'] ) ) { // WPCS: CSRF ok.
		$discount_code = sanitize_text_field( $_POST['code'] );

		$return = array(
			'msg'  => '',
			'code' => $discount_code,
		);

		$user = '';

		if ( is_user_logged_in() ) {
			$user = get_current_user_id();
		} else {
			parse_str( $_POST['form'], $form ); // WPCS: CSRF ok.
			if ( ! empty( $form['edd_email'] ) ) {
				$user = urldecode( $form['edd_email'] );
			}
		}

		if ( edd_is_discount_valid( $discount_code, $user ) ) {
			$discount  = edd_get_discount_by( 'code', $discount_code );
			$amount    = edd_format_discount_rate( edd_get_discount_type( $discount->id ), edd_get_discount_amount( $discount->id ) );
			$discounts = edd_set_cart_discount( $discount_code );
			$total     = edd_get_cart_total( $discounts );

			$return = array(
				'msg'               => 'valid',
				'amount'            => $amount,
				'total_plain'       => $total,
				'total'             => html_entity_decode( edd_currency_filter( edd_format_amount( $total ) ), ENT_COMPAT, 'UTF-8' ),
				'code'              => $discount_code,
				'html'              => edd_get_cart_discounts_html( $discounts ),
				'complete_purchase' => edd_get_checkout_button_purchase_label(),
			);
		} else {
			$errors        = edd_get_errors();
			$return['msg'] = $errors['edd-discount-error'];
			edd_unset_error( 'edd-discount-error' );
		}

		// Allow for custom discount code handling
		$return = apply_filters( 'edd_ajax_discount_response', $return );

		echo wp_json_encode( $return );
	}

	edd_die();
}
add_action( 'wp_ajax_edd_apply_discount',        'edd_ajax_apply_discount' );
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

		$subtotal = EDD()->cart->get_subtotal();
		$taxes    = EDD()->cart->get_tax();
		$total    = EDD()->cart->get_total();

		$return = array(
			'download_id'       => $download_id,
			'quantity'          => EDD()->cart->get_item_quantity( $download_id, $options ),
			'subtotal_raw'      => $subtotal,
			'taxes_raw'         => $taxes,
			'total_raw'         => $total,
			'subtotal'          => html_entity_decode( edd_currency_filter( edd_format_amount( $subtotal ) ), ENT_COMPAT, 'UTF-8' ),
			'taxes'             => html_entity_decode( edd_currency_filter( edd_format_amount( $taxes ) ), ENT_COMPAT, 'UTF-8' ),
			'total'             => html_entity_decode( edd_currency_filter( edd_format_amount( $total ) ), ENT_COMPAT, 'UTF-8' ),
			'discounts'         => edd_get_cart_discounts_html(),
			'complete_purchase' => edd_get_checkout_button_purchase_label(),
		);

		// Allow for custom cart item quantity handling
		$return = apply_filters( 'edd_ajax_cart_item_quantity_response', $return );

		echo json_encode($return);
	}
	edd_die();
}
add_action( 'wp_ajax_edd_update_quantity',        'edd_ajax_update_cart_item_quantity' );
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
			'total_plain'       => $total,
			'total'             => html_entity_decode( edd_currency_filter( edd_format_amount( $total ) ), ENT_COMPAT, 'UTF-8' ),
			'code'              => sanitize_text_field( $_POST['code'] ),
			'discounts'         => edd_get_cart_discounts(),
			'html'              => edd_get_cart_discounts_html(),
			'complete_purchase' => edd_get_checkout_button_purchase_label(),
		);

		/**
		 * Allow for custom remove discount code handling.
		 *
		 * @since 2.11.4
		 */
		$return = apply_filters( 'edd_ajax_remove_discount_response', $return );

		wp_send_json( $return );
	}
	edd_die();
}
add_action( 'wp_ajax_edd_remove_discount',        'edd_ajax_remove_discount' );
add_action( 'wp_ajax_nopriv_edd_remove_discount', 'edd_ajax_remove_discount' );

/**
 * Loads Checkout Login Fields the via AJAX
 *
 * @since 1.0
 * @return void
 */
function edd_load_checkout_login_fields() {
	$action = sanitize_text_field( $_POST['action'] );
	$nonce  = sanitize_text_field( $_POST['nonce'] );

	$nonce_verified = wp_verify_nonce( $nonce, 'edd_' . $action );

	if ( $nonce_verified ) {
		do_action( 'edd_purchase_form_login_fields' );
	}

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
	$action = sanitize_text_field( $_POST['action'] );
	$nonce  = sanitize_text_field( $_POST['nonce'] );

	$nonce_verified = wp_verify_nonce( $nonce, 'edd_' . $action );

	if ( $nonce_verified ) {
		do_action( 'edd_purchase_form_register_fields' );
	}

	edd_die();
}
add_action('wp_ajax_nopriv_checkout_register', 'edd_load_checkout_register_fields');

/**
 * Get Download Title via AJAX
 *
 * @since 1.0
 * @since 2.8 Restrict to just the download post type
 * @return void
 */
function edd_ajax_get_download_title() {
	if ( isset( $_POST['download_id'] ) ) {
		$post_id   = absint( $_POST['download_id'] );
		$post_type = get_post_type( $post_id );
		$title     = 'fail';

		if ( 'download' === $post_type ) {
			$post_title = get_the_title( $_POST['download_id'] );
			if ( $post_title ) {
				echo $title = $post_title;
			}
		}

		echo $title;
	}
	edd_die();
}
add_action( 'wp_ajax_edd_get_download_title',        'edd_ajax_get_download_title' );
add_action( 'wp_ajax_nopriv_edd_get_download_title', 'edd_ajax_get_download_title' );

/**
 * Recalculate cart taxes
 *
 * @since 1.6
 * @return void
 */
function edd_ajax_recalculate_taxes() {
	if ( ! isset( $_POST['nonce'] ) ) {
		edd_debug_log( __( 'Missing nonce when recalculating taxes. Please read the following for more information: https://easydigitaldownloads.com/development/2018/07/05/important-update-to-ajax-requests-in-easy-digital-downloads-2-9-4', 'easy-digital-downloads' ), true );
	}

	$nonce = isset( $_POST['nonce'] ) ? sanitize_text_field( $_POST['nonce'] ) : '';
	$nonce_verified = wp_verify_nonce( $nonce, 'edd-checkout-address-fields' );

	if ( false === $nonce_verified ) {
		return false;
	}

	if ( ! edd_get_cart_contents() ) {
		return false;
	}

	if ( empty( $_POST['billing_country'] ) ) {
		$_POST['billing_country'] = edd_get_shop_country();
	}

	ob_start();
	edd_checkout_cart();
	/**
	 * Allows the cart content to be filtered.
	 * @since 3.1
	 */
	$cart     = apply_filters( 'edd_get_checkout_cart', ob_get_clean() );
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
add_action( 'wp_ajax_edd_recalculate_taxes',        'edd_ajax_recalculate_taxes' );
add_action( 'wp_ajax_nopriv_edd_recalculate_taxes', 'edd_ajax_recalculate_taxes' );

/**
 * Retrieve a states drop down
 *
 * @since 1.6
 * @since 2.9.4 Added nonce verification.
 * @since 3.0 Updated listbox with placeholder values.
 */
function edd_ajax_get_states_field() {

	// Check a nonce was sent.
	if ( empty( $_POST['nonce'] ) ) {
		edd_debug_log( __( 'Missing nonce when retrieving state list. Please read the following for more information: https://easydigitaldownloads.com/development/2018/07/05/important-update-to-ajax-requests-in-easy-digital-downloads-2-9-4', 'easy-digital-downloads' ), true );
	}

	$nonce = ! empty( $_POST['nonce'] )
		? sanitize_text_field( $_POST['nonce'] )
		: '';

	$nonce_verified = wp_verify_nonce( $nonce, 'edd-country-field-nonce' );

	// Bail if nonce verification failed.
	if ( false === $nonce_verified ) {
		edd_die();
	}

	// Get country.
	$country = ! empty( $_POST['country'] )
		? sanitize_text_field( $_POST['country'] ) // Exactly matched
		: edd_get_shop_country();

	// Get states for country.
	$states = edd_get_shop_states( $country );

	// Chosen
	$chosen = ! isset( $_POST['chosen'] ) || ( 'true' === $_POST['chosen'] )
		? true
		: false;

	// Maybe setup the new listbox.
	if ( ! empty( $states ) ) {
		$field_name = isset( $_POST['field_name'] )
			? sanitize_text_field( $_POST['field_name'] )
			: 'edd-state-select';

		$field_id = isset( $_POST['field_id'] )
			? sanitize_text_field( $_POST['field_id'] )
			: $field_name;

		$response = EDD()->html->region_select(
			array(
				'name'            => $field_name,
				'id'              => $field_id,
				'class'           => $field_name . ' edd-select',
				'options'         => $states,
				'chosen'          => $chosen,
				'show_option_all' => false,
			)
		);
	} else {
		$response = 'nostates';
	}

	echo $response;

	edd_die();
}
add_action( 'wp_ajax_edd_get_shop_states',        'edd_ajax_get_states_field' );
add_action( 'wp_ajax_nopriv_edd_get_shop_states', 'edd_ajax_get_states_field' );

/**
 * Retrieve a downloads drop down
 *
 * @since 1.6
 * @since 3.0 Use `get_posts()` instead of multiple direct queries (yay caching)
 * @since 3.1.0.5 Uses EDD\Downloads\Search.
 *
 * @return void
 */
function edd_ajax_download_search() {

	$search = new EDD\Downloads\Search();
	$search->ajax_search();
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

	if ( $customers ) {

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
 * Search the download categories via AJAX
 *
 * @since 3.1.0.4
 * @return void
 */
function edd_ajax_download_category_search() {
	$search  = esc_sql( sanitize_text_field( $_GET['s'] ) );
	$results = array();

	$category_args = array(
		'taxonomy'   => array( 'download_category' ),
		'orderby'    => 'id',
		'order'      => 'ASC',
		'hide_empty' => true,
		'fields'     => 'all',
		'name__like' => $search,
	);

	$categories_found = get_terms( $category_args );

	if ( ! empty( $categories_found ) ) {
		foreach ( $categories_found as $category ) {
			$results[] = array(
				'id'   => $category->slug,
				'name' => $category->name . ' (' . $category->count . ')',
			);
		}
	} else {
		$results[] = array(
			'id'   => 0,
			'name' => __( 'No categories found', 'easy-digital-downloads' ),
		);
	}

	echo wp_send_json( $results );
}
add_action( 'wp_ajax_edd_download_category_search', 'edd_ajax_download_category_search' );

/**
 * Search the users database via AJAX
 *
 * @since 2.6.9
 * @return void
 */
function edd_ajax_user_search() {

	// Default results
	$results = array(
		'id'   => 0,
		'name' => __( 'No users found', 'easy-digital-downloads' )
	);

	// Default user role
	$user_view_role = apply_filters( 'edd_view_users_role', 'view_shop_reports' );

	// User can view users
	if ( current_user_can( $user_view_role ) ) {
		$search = esc_sql( sanitize_text_field( $_GET['s'] ) );
		$users  = array();

		// Searching
		if ( ! empty( $search ) ) {
			$users  = get_users( array(
				'search' => '*' . $search . '*',
				'number' => 50
			) );
		}

		// Setup results based on users
		if ( ! empty( $users ) ) {
			$results = array();

			foreach( $users as $user ) {
				$results[] = array(
					'id'   => $user->ID,
					'name' => $user->display_name,
				);
			}
		}
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
	if ( ! current_user_can( 'edit_products' ) ) {
		die( '-1' );
	}

	$download_id = intval( $_POST['download_id'] );
	$download    = get_post( $download_id );

	if ( 'download' != $download->post_type ) {
		die( '-2' );
	}

	if ( edd_has_variable_prices( $download_id ) ) {
		$variable_prices = edd_get_variable_prices( $download_id );

		if ( ! empty( $variable_prices ) ) {
			$ajax_response = '<select class="edd_price_options_select edd-select edd-select" name="edd_price_option">';

				if ( isset( $_POST['all_prices'] ) ) {
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

	// Bail if user cannot manage shop settings
	if ( ! current_user_can( 'manage_shop_settings' ) ) {
		die();
	}

	// To search for
	$search_query = ! empty( $_POST['user_name'] )
		? trim( $_POST['user_name'] )
		: '';

	// To exclude
	$exclude = ! empty( $_POST['exclude'] )
		? trim( $_POST['exclude'] )
		: '';

	// Default args
	$defaults = array(
		'number' => 50,
		'search' => $search_query . '*'
	);

	// Maybe exclude users
	if ( ! empty( $exclude ) ) {
		$exclude_array      = explode( ',', $exclude );
		$defaults['exclude'] = $exclude_array;
	}

	// Filter query args
	$get_users_args = apply_filters( 'edd_search_users_args', $defaults );

	// Maybe get users
	$users = ! empty( $get_users_args ) && ! empty( $search_query )
		? get_users( $get_users_args )
		: array();

	// Filter users
	$found_users = apply_filters( 'edd_ajax_found_users', $users, $search_query );

	// Put together the results string
	$user_list = '<ul>';
	if ( ! empty( $found_users ) ) {
		foreach( $found_users as $user ) {
			$user_list .= '<li><a href="#" data-userid="' . esc_attr( $user->ID ) . '" data-login="' . esc_attr( $user->user_login ) . '">' . esc_html( $user->user_login ) . '</a></li>';
		}
	} else {
		$user_list .= '<li class="no-users">' . __( 'No users found', 'easy-digital-downloads' ) . '</li>';
	}
	$user_list .= '</ul>';

	echo json_encode( array( 'results' => $user_list ) );

	edd_die();
}
add_action( 'wp_ajax_edd_search_users', 'edd_ajax_search_users' );

/**
 * Search for download, build, and return HTML.
 *
 * This is used in the Admin for Adding items to an order.
 *
 * @since 3.0
 */
function edd_ajax_add_order_item() {

	// Bail if user cannot manage shop settings.
	if ( ! current_user_can( 'manage_shop_settings' ) ) {
		wp_send_json_error();
	}

	// Set up parameters.
	$nonce = isset( $_POST['nonce'] )
		? sanitize_text_field( $_POST['nonce'] )
		: '';

	$download = isset( $_POST['download'] )
		? edd_parse_product_dropdown_value( sanitize_text_field( $_POST['download'] ) )
		: array();

	$country = isset( $_POST['country'] )
		? sanitize_text_field( $_POST['country'] )
		: '';

	$region = isset( $_POST['region'] )
		? sanitize_text_field( $_POST['region'] )
		: '';

	$editable = 1 !== absint( $_POST['editable'] );

	// Bail if missing any data.
	if ( empty( $nonce ) || empty( $download ) ) {
		wp_send_json_error();
	}

	// Bail if nonce fails.
	if ( ! wp_verify_nonce( $nonce, 'edd_add_order_nonce' ) ) {
		wp_send_json_error();
	}

	$response = array();

	$d = edd_get_download( $download['download_id'] );

	if ( $d ) {
		$name = $d->get_name();

		if ( ! $d->has_variable_prices() ) {
			$amount = floatval( $d->get_price() );
		} else {
			$prices = $d->get_prices();

			if ( isset( $prices[ $download['price_id'] ] ) ) {
				$price  = $prices[ $download['price_id'] ];
				$amount = floatval( $price['amount'] );
				$name  .= ' &mdash; ' . esc_html( $price['name'] );
			}
		}

		$quantity = edd_item_quantities_enabled() && isset( $_POST['quantity'] )
			? absint( $_POST['quantity'] )
			: 1;

		$response['name']     = $name;
		$response['discount'] = 0.00;
		$response['tax']      = edd_calculate_tax( $amount * $quantity, $country, $region );
		$response['quantity'] = $quantity;
		$response['amount']   = $amount;
		$response['total']    = floatval( ( $amount * $quantity ) + $response['tax'] );

		static $symbol = null;

		if ( null === $symbol ) {
			$symbol = edd_currency_symbol( edd_get_currency() );
		}

		ob_start(); ?>

		<tr class="edd-add-order-item" data-key="0">
			<td class="name column-name column-primary"><a class="row-title" href=""><?php echo esc_html( $response['name'] ); ?></a></td>

			<td class="overridable amount column-amount" data-type="amount">
				<?php echo esc_html( $symbol ); ?>
				<input type="text" class="download-amount" name="downloads[0][amount]" value="<?php echo esc_attr( edd_format_amount( $response['amount'] ) ); ?>" <?php wp_readonly( $editable ); ?> />
			</td>

			<?php if ( edd_item_quantities_enabled() ) : ?>
				<td class="overridable quantity column-quantity" data-type="quantity">
					<input type="text" class="download-quantity" name="downloads[0][quantity]" value="<?php echo esc_attr( $quantity ); ?>" <?php wp_readonly( $editable ); ?> />
				</td>
			<?php endif; ?>

			<?php if ( edd_use_taxes() ) : ?>
				<td class="overridable tax column-tax" data-type="tax">
					<?php echo esc_html( $symbol ); ?>
					<input type="text" class="download-tax" name="downloads[0][tax]" value="<?php echo esc_attr( edd_format_amount( $response['tax'] ) ); ?>" <?php wp_readonly( $editable ); ?> />
				</td>
			<?php endif; ?>

			<td class="overridable total column-total" data-type="total">
					<?php echo esc_html( $symbol ); ?>
					<input type="text" class="download-total" name="downloads[0][total]" value="<?php echo esc_attr( edd_format_amount( $response['total'] ) ); ?>" <?php wp_readonly( $editable ); ?> />
			</td>

			<th scope="row" class="check-column"><a href="#" class="remove-item"><span class="dashicons dashicons-no"></span></a></th>
			<input type="hidden" class="download-id" name="downloads[0][id]" value="<?php echo esc_attr( $download['download_id'] ); ?>" />
			<input type="hidden" class="download-price-id" name="downloads[0][price_id]" value="<?php echo esc_attr( $download['price_id'] ); // WPCS: XSS ok. ?>" />
		</tr>

		<?php
		$html = ob_get_contents();

		ob_end_clean();

		$response['html'] = $html;
	}

	return wp_send_json_success( $response );
}
add_action( 'wp_ajax_edd_add_order_item', 'edd_ajax_add_order_item' );

function edd_ajax_add_adjustment_to_order() {

	// Bail if user cannot manage shop settings.
	if ( ! current_user_can( 'manage_shop_settings' ) ) {
		wp_send_json_error();
	}

	// Set up parameters.
	$nonce = isset( $_POST['nonce'] )
		? sanitize_text_field( $_POST['nonce'] )
		: '';

	$type = isset( $_POST['type'] )
		? sanitize_text_field( $_POST['type'] )
		: '';

	// Bail if missing any data.
	if ( empty( $nonce ) || empty( $type ) ) {
		edd_die( '-1' );
	}

	// Bail if nonce fails.
	if ( ! wp_verify_nonce( $nonce, 'edd_add_order_nonce' ) ) {
		wp_send_json_error();
	}

	$response = array();

	$valid_types = array( 'fee', 'discount', 'credit' );

	// Bail if an invalid type is passed.
	if ( ! in_array( $type, $valid_types, true ) ) {
		wp_send_json_error();
	}

	static $symbol = null;

	if ( null === $symbol ) {
		$symbol = edd_currency_symbol( edd_get_currency() );
	}

	switch ( $type ) {
		case 'discount':
			$discount = isset( $_POST['adjustment_data']['discount'] )
				? absint( $_POST['adjustment_data']['discount'] )
				: 0;

			// Bail if no discount ID passed.
			if ( empty( $discount ) ) {
				wp_send_json_error();
			}

			$discount = edd_get_discount( $discount );

			// Bail if discount not found.
			if ( ! $discount ) {
				wp_send_json_error();
			}

			ob_start(); ?>

			<tr data-key="0" data-adjustment="discount">
				<td class="name column-name column-primary"><a class="row-title" href=""><?php echo esc_html( $discount->name ) ?></a></td>
				<td class="type column-type"><?php esc_html_e( 'Discount', 'easy-digital-downloads' ); ?></td>
				<td class="description column-description"><code><?php echo esc_html( $discount->code ); ?></code></span></td>
				<td class="amount column-amount"><span class="value"><?php echo edd_format_discount_rate( $discount->type, $discount->amount ); ?></span></td>
				<th scope="row" class="check-column"><a href="#" class="remove-item"><span class="dashicons dashicons-no"></span></a></th>
				<input type="hidden" class="discount-id" name="adjustments[discount][0][id]" value="<?php echo $discount->id; // WPCS: XSS ok. ?>" />
				<input type="hidden" class="discount-amount" name="adjustments[discount][0][amount]" value="<?php echo $discount->amount; // WPCS: XSS ok. ?>" />
				<input type="hidden" class="discount-type" name="adjustments[discount][0][type]" value="<?php echo $discount->type; // WPCS: XSS ok. ?>" />
			</tr>

			<?php
			$html = ob_get_contents();
			ob_end_clean();

			$response['html'] = $html;
			$response['amount'] = $discount->amount;
			$response['type'] = $discount->type;

			break;

		// We just need to generate HTML if credit is being applied.
		case 'credit':
			$amount = isset( $_POST['adjustment_data']['credit']['amount'] )
				? floatval( $_POST['adjustment_data']['credit']['amount'] )
				: 0.00;

			$description = isset( $_POST['adjustment_data']['credit']['description'] )
				? esc_html( $_POST['adjustment_data']['credit']['description'] )
				: '';

			ob_start(); ?>

			<tr data-key="0" data-adjustment="credit">
				<td class="name column-name column-primary"><a class="row-title" href=""><?php esc_html_e( 'Order Credit', 'easy-digital-downloads' ); ?></a></td>
				<td class="type column-type"><?php esc_html_e( 'Credit', 'easy-digital-downloads' ); ?></td>
				<td class="description column-description"><?php echo $description; // WPCS: XSS ok. ?></span></td>
				<td class="amount column-amount"><?php echo esc_html( $symbol ); ?><span class="value"><?php echo esc_html( edd_format_amount( $amount ) ); ?></span></td>
				<th scope="row" class="check-column"><a href="#" class="remove-item"><span class="dashicons dashicons-no"></span></a></th>
				<input type="hidden" class="credit-description" name="adjustments[credit][0][description]" value="<?php echo $description; // WPCS: XSS ok. ?>" />
				<input type="hidden" class="credit-amount" name="adjustments[credit][0][amount]" value="<?php echo $amount; // WPCS: XSS ok. ?>" />
			</tr>

			<?php
			$html = ob_get_contents();
			ob_end_clean();

			$response['amount'] = $amount;
			$response['html']   = $html;
			break;
	}

	return wp_send_json_success( $response );
}
add_action( 'wp_ajax_edd_add_adjustment_to_order', 'edd_ajax_add_adjustment_to_order' );

/**
 * Search for customer addresses and return a list.
 *
 * @since 3.0
 * @return array Custom address data.
 */
function edd_ajax_customer_addresses() {

	// Bail if user cannot manage shop settings.
	if ( ! current_user_can( 'manage_shop_settings' ) ) {
		return wp_send_json_error();
	}

	// Set up parameters.
	$nonce = isset( $_POST['nonce'] )
		? sanitize_text_field( $_POST['nonce'] )
		: '';

	$customer_id = isset( $_POST['customer_id'] )
		? absint( $_POST['customer_id'] )
		: 0;

	// Bail if missing any data.
	if ( empty( $nonce ) || empty( $customer_id ) ) {
		return wp_send_json_error();
	}

	$response = array();

	// Fetch customer.
	$customer = edd_get_customer( $customer_id );

	if ( $customer ) {

		// Fetch customer addresses.
		$addresses = $customer->get_addresses();

		if ( $addresses ) {
			$response['addresses'] = array();
			$options               = array();

			foreach ( $addresses as $address ) {

				// Convert EDD\Customer\Customer_Address object to array.
				$a = $address->to_array();

				// Pass array back as response.
				$response['addresses'][ $address->id ] = $a;

				$address_keys = array_flip( array( 'address', 'address2', 'city', 'region', 'country', 'postal_code' ) );

				$a = array_filter( array_intersect_key( $a, $address_keys ) );

				if ( isset( $a['region'] ) && isset( $a['country'] ) ) {
					$a['region'] = edd_get_state_name( $a['country'], $a['region'] );
				}

				if ( isset( $a['country'] ) ) {
					$a['country'] = edd_get_country_name( $a['country'] );
				}

				$a = implode( ', ', $a );

				$response['formatted'][ $address->id ] = $a;
				$options[ $address->id ] = $a;
			}

			// Fetch the select
			if ( ! empty( $options ) ) {
				$html  = '<select id="edd_customer_existing_addresses" data-nonce="' . wp_create_nonce( 'edd-country-field-nonce' ) . '" data-placeholder="Select a previously used address" class="add-order-customer-address-select edd-form-group__input">';
				$html .= '<option data-key="0" value="0"></option>';
				foreach ( $options as $key => $value ) {
					$html .= '<option data-key="' . esc_attr( $key ) . '" value="' . esc_attr( $key ) . '">' . esc_attr( $value ). '</option>';
				}
				$html .= '</select>';

				$response['html'] = $html;
			}
		}
	}

	return wp_send_json_success( $response );
}
add_action( 'wp_ajax_edd_customer_addresses', 'edd_ajax_customer_addresses' );

/**
 * Returns details about a Customer.
 *
 * @since 3.0
 */
function edd_ajax_customer_details() {
	// Bail if user cannot manage shop settings.
	if ( ! current_user_can( 'manage_shop_settings' ) ) {
		return wp_send_json_error();
	}

	// Set up parameters.
	$nonce = isset( $_POST['nonce'] )
		? sanitize_text_field( $_POST['nonce'] )
		: '';

	$customer_id = isset( $_POST['customer_id'] )
		? absint( $_POST['customer_id'] )
		: 0;

	// Bail if missing any data.
	if ( empty( $nonce ) || empty( $customer_id ) ) {
		return wp_send_json_error();
	}

	// Bail if nonce verification failed.
	if ( ! wp_verify_nonce( $nonce, 'edd_customer_details_nonce' ) ) {
		return wp_send_json_error();
	}

	// Fetch customer.
	$customer = edd_get_customer( $customer_id );

	if ( ! $customer ) {
		return wp_send_json_error();
	}

	$response = array(
		'id'                => esc_html( $customer->id ),
		'name'              => esc_html( $customer->name ),
		'email'             => esc_html( $customer->email ),
		'avatar'            => get_avatar( $customer->email, 50 ),
		'date_created'      => esc_html( $customer->date_created ),
		'date_created_i18n' => esc_html( edd_date_i18n( $customer->date_created ) ),
		'_links'            => array(
			'self' => esc_url_raw( admin_url( 'edit.php?post_type=download&page=edd-customers&view=overview&id=' . absint( $customer->id ) ) ),
		),
	);

	return wp_send_json_success( $response );
}
add_action( 'wp_ajax_edd_customer_details', 'edd_ajax_customer_details' );

/**
 * Recalculates taxes when adding a new order and the country/region field is changed.
 *
 * @since 3.0
 */
function edd_ajax_get_tax_rate() {

	// Bail if user cannot manage shop settings.
	if ( ! current_user_can( 'manage_shop_settings' ) ) {
		return wp_send_json_error();
	}

	// Set up parameters.
	$nonce = isset( $_POST['nonce'] )
		? sanitize_text_field( $_POST['nonce'] )
		: '';

	$country = isset( $_POST['country'] )
		? sanitize_text_field( $_POST['country'] )
		: '';

	$region = isset( $_POST['region'] )
		? sanitize_text_field( $_POST['region'] )
		: '';

	// Bail if missing any data.
	if ( empty( $nonce ) ) {
		return wp_send_json_error();
	}

	// Bail if nonce verification failed.
	if ( ! wp_verify_nonce( $nonce, 'edd_get_tax_rate_nonce' ) ) {
		return wp_send_json_error();
	}

	$response = array();

	$rate = edd_get_tax_rate( $country, $region, $fallback = false );

	$response['tax_rate'] = $rate;
	$response['prices_include_tax'] = (bool) edd_prices_include_tax();

	return wp_send_json_success( $response );
}
add_action( 'wp_ajax_edd_get_tax_rate', 'edd_ajax_get_tax_rate' );

/**
 * Retrieves a potential Order Item's amounts.
 *
 * @since 3.0
 */
function edd_admin_order_get_item_amounts() {
	// Set up parameters.
	$nonce = isset( $_POST['nonce'] )
		? sanitize_text_field( $_POST['nonce'] )
		: '';

	// Bail if missing any data.
	if ( empty( $nonce ) ) {
		return wp_send_json_error( array(
			'message' => esc_html__( 'Unable to verify action. Please refresh the page and try again.', 'easy-digital-downloads' ),
		) );
	}

	// Bail if nonce verification failed.
	if ( ! wp_verify_nonce( $nonce, 'edd_admin_order_get_item_amounts' ) ) {
		return wp_send_json_error( array(
			'message' => esc_html__( 'Unable to verify action. Please refresh the page and try again.', 'easy-digital-downloads' ),
		) );
	}

	$is_adjusting_manually = isset( $_POST['_isAdjustingManually'] ) && false !== $_POST['_isAdjustingManually'];

	$product_id = isset( $_POST['productId'] )
		? intval( sanitize_text_field( $_POST['productId'] ) )
		: 0;

	$price_id = isset( $_POST['priceId'] )
		? intval( sanitize_text_field( $_POST['priceId'] ) )
		: 0;

	$quantity = isset( $_POST['quantity'] )
		? intval( sanitize_text_field( $_POST['quantity'] ) )
		: 0;

	$country = isset( $_POST['country'] )
		? sanitize_text_field( $_POST['country'] )
		: '';

	$region = isset( $_POST['region'] )
		? sanitize_text_field( $_POST['region'] )
		: '';

	$products = isset( $_POST['products'] )
		? $_POST['products']
		: array();

	$discounts = isset( $_POST['discounts'] )
		? array_unique( array_map( 'intval', $_POST['discounts'] ) )
		: array();

	$download = edd_get_download( $product_id );

	// Bail if no Download is found.
	if ( ! $download ) {
		return wp_send_json_error( array(
			'message' => esc_html__( 'Unable to find download. Please refresh the page and try again.', 'easy-digital-downloads' ),
		) );
	}

	// Use base Amount if sent.
	if ( isset( $_POST['amount'] ) && '0' !== $_POST['amount'] ) {
		$amount = edd_sanitize_amount( sanitize_text_field( $_POST['amount'] ) );

	// Determine amount from Download record.
	} else {
		if ( ! $download->has_variable_prices() ) {
			$amount = floatval( $download->get_price() );
		} else {
			$prices = $download->get_prices();

			if ( isset( $prices[ $price_id ] ) ) {
				$price  = $prices[ $price_id ];
				$amount = floatval( $price['amount'] );
			}
		}
	}

	// Use base Subtotal if sent.
	if ( isset( $_POST['subtotal'] ) && '0' !== $_POST['subtotal'] ) {
		$subtotal = edd_sanitize_amount( sanitize_text_field( $_POST['subtotal'] ) );
	} else {
		$subtotal = $amount * $quantity;
	}

	$discount = 0;

	// Track how much of each Discount is applied to an `OrderItem`.
	// There is not currently API support for `OrderItem`-level `OrderAdjustment`s.
	$adjustments = array();

	global $edd_flat_discount_total;

	foreach ( $discounts as $discount_id ) {
		$edd_flat_discount_total = 0;

		$d = edd_get_discount( $discount_id );

		if ( ! $d ) {
			continue;
		}

		// Retrieve total flat rate amount.
		if ( 'flat' === $d->get_type() ) {
			foreach ( $products as $product ) {
				// This incremements the `$edd_flat_discount_total` global.
				edd_get_item_discount_amount( $product, $products, array( $d ) );
			}
		}

		// Store total discount and reset global.
		$total_discount = $edd_flat_discount_total;

		$item = array(
			'id'       => $download->id,
			'quantity' => $quantity,
			'options'  => array(
				'price_id' => $price_id,
			),
		);

		$discount_amount = edd_get_item_discount_amount( $item, $products, array( $d ) );

		if (
			0 !== $discount_amount &&
			'flat' === $d->get_type() &&
			$item['id'] == end( $products )['id']
		) {
			if ( $total_discount < $d->get_amount() ) {
				$adjustment       = ( $d->get_amount() - $total_discount );
				$discount_amount += $adjustment;
			} else if ( $total_discount > $d->get_amount() ) {
				$adjustment       = ( $total_discount - $d->get_amount() );
				$discount_amount -= $adjustment;
			}
		}

		$adjustments[] = array(
			'objectType'  => 'order_item',
			'type'        => 'discount',
			'typeId'      => $d->id,
			'description' => $d->code,
			'subtotal'    => $discount_amount,
			'total'       => $discount_amount,
		);

		$discount += $discount_amount;
	}

	if (
		true === edd_use_taxes() &&
		false === edd_download_is_tax_exclusive( $product_id )
	) {
		$tax = edd_calculate_tax( floatval( $subtotal - $discount ), $country, $region, false );
	} else {
		$tax = 0;
	}

	wp_send_json_success( array(
		'amount'      => $amount,
		'subtotal'    => $subtotal,
		'discount'    => $discount,
		'tax'         => $tax,
		'total'       => $subtotal + $tax,
		'adjustments' => $adjustments,
	) );
}
add_action( 'wp_ajax_edd-admin-order-get-item-amounts', 'edd_admin_order_get_item_amounts' );
