<?php
/**
 * Checkout form functions for EDD checkout blocks.
 *
 * @package   edd-blocks
 * @copyright 2022 Easy Digital Downloads
 * @license   GPL2+
 * @since 2.0
 */

namespace EDD\Blocks\Checkout\Forms;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/**
 * Outputs the purchase form for checkout.
 *
 * @since 2.0
 * @deprecated 3.6.0
 * @param array $block_attributes The block attributes.
 * @return void
 */
function do_purchase_form( $block_attributes ) {
	\EDD\Blocks\Checkout\Elements\PurchaseForm::render( $block_attributes );
}

add_action( 'enqueue_block_assets', __NAMESPACE__ . '\add_user_script' );
/**
 * Adds the guest checkout button switcher script to the EDD global checkout script.
 *
 * @since 2.0
 * @return void
 */
function add_user_script() {
	if ( ! edd_is_checkout() ) {
		return;
	}

	// Manually trigger a blur event on the hidden email input for logged in users.
	if ( is_user_logged_in() ) {
		$script = "jQuery(document).ready(function($) {
			$( '#edd-email[type=\"hidden\"]' ).trigger( 'blur' );
		} );";

		wp_add_inline_script( 'edd-checkout-global', $script );
		return;
	}

	wp_register_script( 'edd-blocks-checkout-forms', EDD_BLOCKS_URL . 'assets/js/checkout-forms.js', array( 'edd-checkout-global' ), EDD_VERSION, true );
}

add_action( 'wp_ajax_nopriv_edd_blocks_swap_personal_info', __NAMESPACE__ . '\swap_form' );
add_action( 'wp_ajax_edd_blocks_swap_personal_info', __NAMESPACE__ . '\swap_form' );
/**
 * Swaps out the currently displayed form for the selected one.
 *
 * @since 2.0
 * @return void
 */
function swap_form() {
	if ( empty( $_GET['form_id'] ) ) {
		return;
	}
	$form_id = sanitize_text_field( $_GET['form_id'] );
	$forms   = get_forms();
	if ( empty( $forms[ $form_id ] ) ) {
		return;
	}
	$form = $forms[ $form_id ];
	if ( empty( $form['view'] ) ) {
		wp_send_json_error( __( 'Unable to load form.', 'easy-digital-downloads' ) );
	}

	$customer = \EDD\Blocks\Checkout\get_customer();
	if ( is_callable( $form['view'] ) ) {
		wp_send_json_success( call_user_func( $form['view'], array( 'current' => true ) ) );
	}

	if ( \EDD\Utils\FileSystem::file_exists( $form['view'] ) ) {
		ob_start();
		include $form['view'];
		wp_send_json_success( ob_get_clean() );
	}

	wp_send_json_error( __( 'Unable to load form.', 'easy-digital-downloads' ) );
}

/**
 * Gets the array of forms for the checkout form.
 *
 * @since 3.2.4
 * @return array
 */
function get_forms() {
	return array(
		'login'    => array(
			'label' => __( 'Log in', 'easy-digital-downloads' ),
			'view'  => EDD_BLOCKS_DIR . 'views/checkout/purchase-form/login.php',
		),
		'register' => array(
			'label' => __( 'Register for a new account', 'easy-digital-downloads' ),
			'view'  => EDD_BLOCKS_DIR . 'views/checkout/purchase-form/register.php',
		),
		'guest'    => array(
			'label' => __( 'Checkout as a guest', 'easy-digital-downloads' ),
			'view'  => EDD_BLOCKS_DIR . 'views/checkout/purchase-form/personal-info.php',
		),
	);
}

/**
 * Gets the array of personal info forms for the checkout form.
 *
 * @since 2.0
 * @deprecated 3.6.0 in favor of \EDD\Blocks\Checkout\Elements\PersonalInfo::get_personal_info_forms()
 * @param array $block_attributes       The block attributes.
 * @param bool  $customer_info_complete Whether the logged in customer information is complete.
 * @return array
 */
function get_personal_info_forms( $block_attributes, $customer_info_complete = true ) {
	return \EDD\Blocks\Checkout\Elements\PersonalInfo::get_personal_info_forms( $block_attributes, $customer_info_complete );
}

/**
 * Shows the login and/or registration form for guest users in checkout.
 *
 * @since 2.0
 * @deprecated 3.6.0 in favor of \EDD\Blocks\Checkout\Elements\PersonalInfo::render()
 * @param array $block_attributes The block attributes.
 * @return void
 */
function do_personal_info_forms( $block_attributes ) {
	\EDD\Blocks\Checkout\Elements\PersonalInfo::render( $block_attributes );
}
