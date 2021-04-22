<?php
/**
 * PayPal Commerce Connect
 *
 * @package   easy-digital-downloads
 * @copyright Copyright (c) 2021, Sandhills Development, LLC
 * @license   GPL2+
 * @since     2.11
 */

namespace EDD\PayPal\Admin;

use EDD\PayPal;

if ( ! defined( 'EDD_PAYPAL_PARTNER_CONNECT_URL' ) ) {
	// @todo
	define( 'EDD_PAYPAL_PARTNER_CONNECT_URL', 'https://develop/edd/wp-json/paypal-connect/v1/' );
}

function connect_settings_field() {
	$is_connected = PayPal\has_rest_api_connection();

	ob_start();

	if ( ! $is_connected ) {
		/**
		 * Show Connect
		 */
		?>
		<a href="#" class="edd-paypal-connect" data-nonce="<?php echo esc_attr( wp_create_nonce( 'edd_process_paypal_connect' ) ); ?>">
			<?php esc_html_e( 'Connect with PayPal', 'easy-digital-downloads' ); ?>
		</a>
		<?php
	} else {
		/**
		 * Show Account Info & Disconnect
		 */

	}
	?>

	<?php
	return ob_get_clean();
}

/**
 * AJAX handler for processing the PayPal Connection.
 *
 * @since 2.11
 * @return void
 */
function process_connect() {
	// This validates the nonce.
	check_ajax_referer( 'edd_process_paypal_connect' );

	if ( ! current_user_can( 'manage_options' ) ) {
		wp_send_json_error( __( 'You do not have permission to perform this action.', 'easy-digital-downloads' ) );
	}

	$response = wp_remote_post( EDD_PAYPAL_PARTNER_CONNECT_URL . 'signup-link', array(
		'body' => json_encode( array(
			'mode'       => edd_is_test_mode() ? 'sandbox' : 'live',
			'return_url' => admin_url() // @todo
		) )
	) );

	if ( is_wp_error( $response ) ) {
		wp_send_json_error( $response->get_error_message() );
	}

	$code = wp_remote_retrieve_response_code( $response );
	$body = json_decode( wp_remote_retrieve_body( $response ) );

	if ( 200 !== intval( $code ) ) {
		wp_send_json_error( sprintf(
		/* Translators: %d - HTTP response code */
			__( 'Unexpected response code: %d', 'easy-digital-downloads' ),
			$code
		) );
	}

	if ( empty( $body->signupLink ) || empty( $body->nonce ) ) {
		wp_send_json_error( __( 'An unexpected error occurred.', 'easy-digital-downloads' ) );
	}

	update_option( 'edd_paypal_commerce_connect_details', json_encode( $body ) );

	wp_send_json_success( $body );
}

add_action( 'wp_ajax_edd_paypal_commerce_connect', __NAMESPACE__ . '\process_connect' );
