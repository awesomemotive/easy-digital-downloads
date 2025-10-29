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
add_action( 'init', __NAMESPACE__ . '\register' );

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
 * @param array $block_attributes The block attributes.
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
		<p class="edd-blocks-form__cart"><?php echo wp_kses_post( edd_empty_cart_message() ); ?></p>
		<?php
		return;
	}
	\EDD\Blocks\Checkout\Elements\Cart::render(
		array(
			'block_attributes' => $block_attributes,
			'is_cart_widget'   => true,
			'cart_items'       => $cart_items,
		)
	);
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
 * @param array $block_attributes The block attributes.
 * @return string Checkout HTML.
 */
function checkout( $block_attributes = array() ) {
	$block_attributes = wp_parse_args(
		$block_attributes,
		array(
			'show_register_form' => edd_get_option( 'show_register_form' ),
			'layout'             => '',
			'show_discount_form' => true,
			'thumbnail_width'    => 25,
			'logged_in'          => is_user_logged_in() && ! \EDD\Blocks\Utility::doing_guest_preview(),
		)
	);

	$layout  = $block_attributes['layout'] ? sanitize_text_field( $block_attributes['layout'] ) : 'full';
	$classes = array(
		'wp-block-edd-checkout',
		'edd-blocks__checkout',
		"edd-checkout__layout--{$layout}",
	);
	if ( $block_attributes['logged_in'] ) {
		$classes[] = 'edd-blocks__checkout--logged-in';
	}
	$classes = Helpers\get_block_classes( $block_attributes, $classes );

	$cart_items = get_cart_contents();

	if ( ! $cart_items && ! edd_cart_has_fees() ) {
		return '<p>' . edd_empty_cart_message() . '</p>';
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
		if ( $block_attributes['logged_in'] ) {
			$customer = get_customer();
			include EDD_BLOCKS_DIR . 'views/checkout/logged-in.php';
		}
		\EDD\Blocks\Checkout\Elements\Cart::render(
			array(
				'block_attributes' => $block_attributes,
				'cart_items'       => $cart_items,
				'doing_ajax'       => false,
			)
		);
		\EDD\Blocks\Checkout\Elements\PurchaseForm::render( $block_attributes );
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
	if ( ! \EDD\Blocks\Utility::is_block_editor() ) {
		return edd_get_cart_contents();
	}

	if ( ! empty( $_GET['cart_item'] ) ) {
		$download = edd_get_download( absint( $_GET['cart_item'] ) );
		if ( $download ) {
			$cart_item = array(
				array(
					'id'       => $download->ID,
					'quantity' => 1,
					'options'  => array(),
				),
			);
			if ( $download->has_variable_prices() ) {
				$price_ids = $download->get_prices();
				if ( ! empty( $price_ids ) ) {
					$cart_item[0]['options']['price_id'] = array_rand( array_flip( array_keys( $price_ids ) ) );
				}
			}
			return $cart_item;
		}
	}

	$downloads = new \WP_Query(
		array(
			'post_type'      => 'download',
			'fields'         => 'ids',
			'posts_per_page' => 10,
			'no_found_rows'  => true,
			'post_status'    => 'publish',
		)
	);
	if ( empty( $downloads->posts ) ) {
		return false;
	}

	$download_id = array_rand( array_flip( $downloads->posts ) );
	$download    = edd_get_download( $download_id );
	$cart_item   = array(
		'id'       => $download_id,
		'quantity' => 1,
		'options'  => array(),
	);

	if ( $download->has_variable_prices() ) {
		$price_ids = $download->get_prices();
		if ( empty( $price_ids ) ) {
			return false;
		}
		$cart_item['options']['price_id'] = array_rand( array_flip( array_keys( $price_ids ) ) );
	}

	return array( $cart_item );
}

/**
 * Remove some of the default EDD fields from the purchase form.
 * Loaded in this hook because we have to account for ajax.
 *
 * @since 2.0
 * @return void
 */
function remove_default_purchase_fields() {
	if ( ! \EDD\Checkout\Validator::has_block() ) {
		return;
	}
	remove_action( 'edd_purchase_form_after_user_info', 'edd_user_info_fields' );
	remove_action( 'edd_register_fields_before', 'edd_user_info_fields' );
	remove_action( 'edd_purchase_form_before_submit', 'edd_checkout_final_total', 999 );
	add_filter(
		'edd_get_option_show_register_form',
		function () {
			return 'none';
		}
	);
	add_filter( 'edd_pre_cc_address_fields', '__return_true' );
	add_filter( 'edd_pre_cc_fields', '__return_true' );
}
add_action( 'edd_purchase_form_top', __NAMESPACE__ . '\remove_default_purchase_fields' );

/**
 * Gets the array of customer information from the session and potentially the logged in user information.
 *
 * @since 2.0
 * @return array
 */
function get_customer() {
	return \EDD\Sessions\Customer::get();
}

/**
 * Renders the customer address fields for checkout.
 *
 * @since 2.0
 * @deprecated 3.6.0
 * @return void
 */
function do_address() {
	$address_class = edd_get_namespace( 'Checkout\\Address' );
	$address       = new $address_class();
	$address->render();
}

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
add_action( 'edd_cc_fields', __NAMESPACE__ . '\do_cc_fields' );

/**
 * Disables the Checkout Fields Manager captcha if reCAPTCHA is enabled.
 *
 * @since 3.5.3
 * @param bool $has_captcha Whether the checkout has a captcha.
 * @return bool
 */
function disable_cfm_captcha( $has_captcha ) {
	return \EDD\Captcha\Utility::can_do_captcha() ? false : $has_captcha;
}
add_filter( 'edd_cfm_checkout_has_captcha', __NAMESPACE__ . '\disable_cfm_captcha' );

/**
 * If the checkout block is on a page that isn't set as the checkout option, set edd_is_checkout to true.
 *
 * @since 2.0
 * @deprecated 3.3.0
 * @param bool $is_checkout Whether we are currently on the checkout page.
 * @return bool
 */
function is_checkout( $is_checkout ) {
	_edd_deprecated_function( __FUNCTION__, '3.3.0' );

	return $is_checkout;
}

/**
 * Gets the customer address for checkout.
 *
 * @since 2.0
 * @deprecated 3.3.8
 * @param array $customer The customer data from the session.
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

/**
 * Gets the checkout cart markup when EDD recalculates taxes.
 *
 * @deprecated 3.6.0
 * @param string $cart The cart HTML markup.
 * @return string
 */
function do_checkout_cart( $cart ) {
	return $cart;
}
