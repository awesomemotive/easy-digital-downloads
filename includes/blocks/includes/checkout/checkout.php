<?php
/**
 * Checkout blocks for EDD.
 *
 * @package   edd-blocks
 * @copyright 2022 Easy Digital Downloads
 * @license   GPL2+
 * @since 2.0
 */

namespace EDD\Blocks\Checkout;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

use EDD\Blocks\Functions as Helpers;

require_once EDD_BLOCKS_DIR . 'includes/checkout/ajax.php';
require_once EDD_BLOCKS_DIR . 'includes/checkout/forms.php';
require_once EDD_BLOCKS_DIR . 'includes/checkout/functions.php';
require_once EDD_BLOCKS_DIR . 'includes/checkout/gateways.php';

add_action( 'init', __NAMESPACE__ . '\register' );
/**
 * Registers all of the EDD core blocks.
 *
 * @since 2.0
 * @return void
 */
function register() {
	$blocks = array(
		'cart'     => array(
			'render_callback' => __NAMESPACE__ . '\cart',
			'view_script'     => 'edd-blocks-cart',
		),
		'checkout' => array(
			'render_callback' => __NAMESPACE__ . '\checkout',
		),
	);

	foreach ( $blocks as $block => $args ) {
		register_block_type( EDD_BLOCKS_DIR . 'build/' . $block, $args );
	}
}

/**
 * Renders the cart.
 *
 * @since 2.0
 * @param array $block_attributes The block attributes.
 * @return string Cart HTML.
 */
function cart( $block_attributes = array() ) {

	$block_attributes = wp_parse_args(
		$block_attributes,
		array(
			'hide_on_checkout' => true,
			'mini'             => true,
			'show_quantity'    => true,
			'show_total'       => true,
			'link'             => true,
			'hide_empty'       => false,
			'title'            => '',
		)
	);
	if ( edd_is_checkout() && $block_attributes['hide_on_checkout'] ) {
		return '';
	}

	wp_enqueue_script( 'edd-blocks-cart' );

	$classes = Helpers\get_block_classes(
		$block_attributes,
		array(
			'wp-block-edd-cart',
			'edd-blocks__cart',
		)
	);
	if ( ! empty( $block_attributes['mini'] ) ) {
		$classes[] = 'edd-blocks__cart-mini';
	} else {
		$classes[] = 'edd-blocks__cart-full';
	}

	ob_start();
	?>
	<div class="<?php echo esc_attr( implode( ' ', array_filter( $classes ) ) ); ?>">
		<?php
		do_cart_form( $block_attributes );
		?>
	</div>
	<?php

	return ob_get_clean();
}

/**
 * Generates the cart form depending on contents and options.
 *
 * @since 2.0
 * @param array $block_attributes
 * @return void
 */
function do_cart_form( $block_attributes ) {
	$cart_items        = get_cart_contents();
	$cart_has_contents = $cart_items || edd_cart_has_fees();
	if ( ! $cart_has_contents && ! empty( $block_attributes['hide_empty'] ) ) {
		return;
	}
	if ( ! empty( $block_attributes['mini'] ) ) {
		include EDD_BLOCKS_DIR . 'views/checkout/cart/mini.php';
		return;
	}
	if ( ! empty( $block_attributes['title'] ) ) {
		?>
		<h3><?php echo esc_html( $block_attributes['title'] ); ?></h3>
		<?php
	}
	if ( ! $cart_has_contents ) {
		?>
		<p class="edd-blocks-form__cart"><?php esc_html_e( 'Your cart is empty.', 'easy-digital-downloads' ); ?></p>
		<?php
		return;
	}
	$is_cart_widget = true;
	include EDD_BLOCKS_DIR . 'views/checkout/cart/cart.php';
	// Link to checkout if it's not currently the checkout screen.
	if ( ! edd_is_checkout() ) {
		?>
		<a class="edd-blocks__button" href="<?php echo esc_url( edd_get_checkout_uri() ); ?>"><?php esc_html_e( 'Checkout', 'easy-digital-downloads' ); ?></a>
		<?php
	}
}

/**
 * Renders the entire EDD checkout block.
 *
 * @since 2.0
 * @param array  $block_attributes The block attributes.
 * @return string Checkout HTML.
 */
function checkout( $block_attributes = array() ) {
	$block_attributes = wp_parse_args(
		$block_attributes,
		array(
			'show_register_form' => edd_get_option( 'show_register_form' ),
		)
	);

	$classes = Helpers\get_block_classes(
		$block_attributes,
		array(
			'wp-block-edd-checkout',
			'edd-blocks__checkout',
		)
	);

	$cart_items = get_cart_contents();

	if ( ! $cart_items && ! edd_cart_has_fees() ) {
		return '<p>' . esc_html( __( 'Your cart is empty.', 'easy-digital-downloads' ) ) . '</p>';
	}

	if ( edd_item_quantities_enabled() ) {
		add_action( 'edd_cart_footer_buttons', 'edd_update_cart_button' );
	}

	// Check if the Save Cart button should be shown.
	if ( ! edd_is_cart_saving_disabled() ) {
		add_action( 'edd_cart_footer_buttons', 'edd_save_cart_button' );
	}

	ob_start();
	?>
	<div id="edd_checkout_form_wrap" class="<?php echo esc_attr( implode( ' ', array_filter( $classes ) ) ); ?>">
		<?php
		if ( is_user_logged_in() ) {
			$customer = get_customer();
			include EDD_BLOCKS_DIR . 'views/checkout/logged-in.php';
		}
		do_action( 'edd_before_checkout_cart' );
		include EDD_BLOCKS_DIR . 'views/checkout/cart/cart.php';
		do_action( 'edd_after_checkout_cart' );
		Forms\do_purchase_form( $block_attributes );
		?>
	</div>
	<?php

	return ob_get_clean();
}

/**
 * Gets the cart contents.
 * In the block editor, generates a sample cart.
 *
 * @since 2.0
 * @return false|array
 */
function get_cart_contents() {
	if ( ! Helpers\is_block_editor() ) {
		return edd_get_cart_contents();
	}
	$downloads = new \WP_Query(
		array(
			'post_type'      => 'download',
			'fields'         => 'ids',
			'posts_per_page' => 10,
			'no_found_rows'  => true,
		)
	);
	if ( empty( $downloads->posts ) ) {
		return false;
	}
	$download_id = array_rand( array_flip( $downloads->posts ) );
	$download    = edd_get_download( $download_id );
	$price_id    = null;
	if ( $download->has_variable_prices() ) {
		$price_ids = $download->get_prices();
		$price_ids = wp_list_pluck( $price_ids, 'index' );
		$price_id  = array_rand( array_flip( $price_ids ) );
	}

	return array(
		array(
			'id'       => $download_id,
			'options'  => array(
				'price_id' => $price_id,
			),
			'quantity' => 1,
		),
	);
}

add_action( 'edd_purchase_form_top', __NAMESPACE__ . '\remove_default_purchase_fields' );
/**
 * Remove some of the default EDD fields from the purchase form.
 * Loaded in this hook because we have to account for ajax.
 *
 * @since 2.0
 * @return void
 */
function remove_default_purchase_fields() {
	if ( ! Functions\checkout_has_blocks() ) {
		return;
	}
	remove_action( 'edd_purchase_form_after_user_info', 'edd_user_info_fields' );
	remove_action( 'edd_register_fields_before', 'edd_user_info_fields' );
	remove_action( 'edd_purchase_form_before_submit', 'edd_checkout_final_total', 999 );
	add_filter( 'edd_get_option_show_register_form', function() { return 'none'; } );
	add_filter( 'edd_pre_cc_address_fields', '__return_true' );
	add_filter( 'edd_pre_cc_fields', '__return_true' );
}

add_filter( 'edd_get_checkout_cart', __NAMESPACE__ . '\do_checkout_cart' );
/**
 * Gets the checkout cart markup when EDD recalculates taxes.
 *
 * @param string $cart The cart HTML markup.
 * @return string
 */
function do_checkout_cart( $cart ) {
	if ( ! Functions\checkout_has_blocks() ) {
		return $cart;
	}
	$cart_items = get_cart_contents();
	ob_start();
	do_action( 'edd_before_checkout_cart' );
	include EDD_BLOCKS_DIR . 'views/checkout/cart/cart.php';

	return ob_get_clean();
}

/**
 * Gets the array of customer information from the session and potentially the logged in user information.
 *
 * @since 2.0
 * @return array
 */
function get_customer() {
	$session  = EDD()->session->get( 'customer' );
	$customer = wp_parse_args(
		$session,
		array(
			'first_name' => '',
			'last_name'  => '',
			'email'      => '',
		)
	);

	if ( is_user_logged_in() ) {
		$user_data = get_userdata( get_current_user_id() );
		foreach ( $customer as $key => $field ) {
			if ( 'email' === $key && empty( $field ) ) {
				$customer[ $key ] = $user_data->user_email;
			} elseif ( empty( $field ) ) {
				$customer[ $key ] = $user_data->$key;
			}
		}
	}

	return array_map( 'sanitize_text_field', $customer );
}

/**
 * Gets the customer address for checkout.
 *
 * @since 2.0
 * @param array $customer
 * @return array
 */
function get_customer_address( $customer ) {
	$address = array(
		'line1'   => '',
		'line2'   => '',
		'city'    => '',
		'zip'     => '',
		'state'   => '',
		'country' => '',
	);

	if ( is_user_logged_in() ) {
		$user_address = edd_get_customer_address();
		foreach ( $address as $key => $field ) {
			if ( empty( $field ) && ! empty( $user_address[ $key ] ) ) {
				$address[ $key ] = $user_address[ $key ];
			}
		}
	}

	/**
	 * Filter the billing address details that will be pre-populated on the checkout form.
	 *
	 * @since 2.8
	 *
	 * @param array $address The customer address.
	 * @param array $customer The customer data from the session
	 */
	return array_map( 'sanitize_text_field', apply_filters( 'edd_checkout_billing_details_address', $address, $customer ) );
}

add_action( 'edd_cc_address_fields', __NAMESPACE__ . '\do_address' );
/**
 * Renders the customer address fields for checkout.
 *
 * @since 2.0
 * @return void
 */
function do_address() {
	$customer            = get_customer();
	$customer['address'] = get_customer_address( $customer );

	include EDD_BLOCKS_DIR . 'views/checkout/purchase-form/address.php';
}

add_action( 'edd_cc_fields', __NAMESPACE__ . '\do_cc_fields' );
/**
 * Renders the default credit card fields on checkout.
 *
 * @since 2.0
 * @return void
 */
function do_cc_fields() {
	do_action( 'edd_before_cc_fields' );
	include EDD_BLOCKS_DIR . 'views/checkout/purchase-form/credit-card.php';
	do_action( 'edd_after_cc_fields' );
}

add_filter( 'edd_is_checkout', __NAMESPACE__ . '\is_checkout' );
/**
 * If the checkout block is on a page that isn't set as the checkout option, set edd_is_checkout to true.
 *
 * @since 2.0
 * @param bool $is_checkout
 * @return bool
 */
function is_checkout( $is_checkout ) {
	if ( $is_checkout ) {
		return $is_checkout;
	}

	if ( has_block( 'edd/checkout' ) ) {
		return true;
	}

	$current_page = ! empty( $_POST['current_page'] ) ? absint( $_POST['current_page'] ) : false;
	if ( $current_page && edd_doing_ajax() && has_block( 'edd/checkout', $current_page ) ) {
		return true;
	}

	return $is_checkout;
}
