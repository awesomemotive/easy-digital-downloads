<?php
/**
 * Form blocks for EDD.
 *
 * @package   edd-blocks
 * @copyright 2022 Easy Digital Downloads
 * @license   GPL2+
 * @since 2.0
 */

namespace EDD\Blocks\Forms;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

use EDD\Blocks\Functions;

require_once EDD_BLOCKS_DIR . 'includes/forms/recaptcha.php';

add_action( 'init', __NAMESPACE__ . '\register' );
/**
 * Registers all of the EDD core blocks.
 *
 * @since 2.0
 * @return void
 */
function register() {
	$blocks = array(
		'login'    => array(
			'render_callback' => __NAMESPACE__ . '\login',
		),
		'register' => array(
			'render_callback' => __NAMESPACE__ . '\registration',
		),
	);

	foreach ( $blocks as $block => $args ) {
		register_block_type( EDD_BLOCKS_DIR . 'build/' . $block, $args );
	}
}

/**
 * Renders the login form block.
 *
 * @since 2.0
 * @param array $block_attributes The block attributes.
 * @return string Login form HTML.
 */
function login( $block_attributes = array() ) {
	if ( is_user_logged_in() && empty( $_GET['reauth'] ) ) {
		return '';
	}
	$block_attributes = wp_parse_args(
		$block_attributes,
		array(
			'current'  => false,
			'redirect' => '',
		)
	);

	$action = ! empty( $_GET['action'] ) ? sanitize_text_field( $_GET['action'] ) : false;
	if ( 'rp' === $action ) {
		list( $rp_login, $rp_key ) = explode( ':', wp_unslash( $_COOKIE[ 'wp-resetpass-' . COOKIEHASH ] ), 2 );
		$user                      = check_password_reset_key( $rp_key, $rp_login );
		if ( ! $user || is_wp_error( $user ) ) {
			$action = 'lostpassword';
			edd_set_error( 'invalidkey', __( 'Your password reset link appears to be invalid. Please request a new link below.', 'easy-digital-downloads' ) );
		}
	}
	$block_classes = array( 'wp-block-edd-login' );
	if ( $action ) {
		$block_classes[] = "wp-block-edd-login__{$action}";
	}
	$classes = Functions\get_block_classes( $block_attributes, $block_classes );
	ob_start();

	?>
	<div class="<?php echo esc_attr( implode( ' ', $block_classes ) ); ?>">
		<?php
		// Show any error messages after form submission.
		edd_print_errors();

		if ( 'lostpassword' === $action ) {
			include EDD_BLOCKS_DIR . 'views/forms/lost-password.php';
		} elseif ( 'rp' === $action ) {
			include EDD_BLOCKS_DIR . 'views/forms/reset-password.php';
		} else {
			$redirect_url = get_redirect_url( $block_attributes, true );
			include EDD_BLOCKS_DIR . 'views/forms/login.php';
		}
		?>
	</div>
	<?php

	return ob_get_clean();
}

/**
 * Renders the registration form block.
 *
 * @since 2.0
 * @param array $block_attributes The block attributes.
 * @return string Registration from HTML.
 */
function registration( $block_attributes = array() ) {

	$block_attributes = wp_parse_args(
		$block_attributes,
		array(
			'current'  => true,
			'redirect' => '',
		)
	);
	ob_start();
	?>
	<div class="wp-block-edd-register">
		<?php
		edd_print_errors();
		if ( ! is_user_logged_in() ) {
			$redirect_url = get_redirect_url( $block_attributes );
			include EDD_BLOCKS_DIR . 'views/forms/registration.php';
		}
		?>
	</div>
	<?php

	return ob_get_clean();
}

/**
 * Gets the redirect URL from the block attributes.
 *
 * @since 2.0
 * @param array $block_attributes
 * @param bool  $is_login_form
 * @return string
 */
function get_redirect_url( $block_attributes, $is_login_form = false ) {
	// Check for the WordPress redirect URL.
	if ( $is_login_form && ! empty( $_GET['redirect_to'] ) && filter_var( $_GET['redirect_to'], FILTER_VALIDATE_URL ) ) {
		return $_GET['redirect_to'];
	}

	// Set the redirect to the current page by default.
	$redirect_url = edd_get_current_page_url();

	// If the block is set to redirect to the current page, return.
	if ( ! empty( $block_attributes['current'] ) ) {
		return $redirect_url;
	}

	// If a custom redirect URL is set for the block, use that.
	if ( ! empty( $block_attributes['redirect'] ) && filter_var( $block_attributes['redirect'], FILTER_VALIDATE_URL ) ) {
		return $block_attributes['redirect'];
	}

	// Otherwise, check for the EDD login redirect page.
	$login_redirect_page = $is_login_form ? edd_get_option( 'login_redirect_page', false ) : false;

	return $login_redirect_page ? get_permalink( $login_redirect_page ) : $redirect_url;
}
