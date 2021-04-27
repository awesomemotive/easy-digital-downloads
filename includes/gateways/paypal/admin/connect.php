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

/**
 * Returns the content for the PayPal Commerce Connect fields.
 *
 * If the account is not yet connected, the user is shown a "Connect with PayPal" button.
 * If they are connected, their account details are show instead.
 *
 * @since 2.11
 * @return string
 */
function connect_settings_field() {
	$is_connected = PayPal\has_rest_api_connection();
	$mode         = edd_is_test_mode() ? __( 'sandbox', 'easy-digital-downloads' ) : __( 'live', 'easy-digital-downloads' );

	ob_start();

	if ( ! $is_connected ) {
		/**
		 * Show Connect
		 */
		?>
		<button type="button" id="edd-paypal-commerce-connect" class="button" data-nonce="<?php echo esc_attr( wp_create_nonce( 'edd_process_paypal_connect' ) ); ?>">
			<?php
			/* Translators: %s - the store mode, either `sandbox` or `test */
			printf( esc_html__( 'Connect with PayPal in %s mode', 'easy-digital-downloads' ), esc_html( $mode ) );
			?>
		</button>
		<a href="#" target="_blank" id="edd-paypal-commerce-link" class="edd-hidden" data-paypal-onboard-complete="eddPayPalOnboardingCallback" data-paypal-button="true">
			<?php esc_html_e( 'Sign up for PayPal', 'easy-digital-downloads' ); ?>
		</a>
		<div id="edd-paypal-commerce-errors"></div>
		<?php
	} else {
		/**
		 * Show Account Info & Disconnect
		 */
		?>
		<div id="edd-paypal-commerce-connect-wrap" class="edd-paypal-connect-account-info notice inline" data-nonce="<?php echo esc_attr( wp_create_nonce( 'edd_paypal_account_information' ) ); ?>">
			<p>
				<em><?php esc_html_e( 'Retrieving account information...', 'easy-digital-downloads' ); ?></em>
				<span class="spinner is-active"></span>
			</p>
		</div>
		<div id="edd-paypal-disconnect">
			<a href="<?php echo esc_url( get_disconnect_url() ); ?>"><?php esc_html_e( 'Disconnect from PayPal', 'easy-digital-downloads' ); ?></a>
		</div>
		<?php
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
		'headers' => array(
			'Content-Type' => 'application/json',
		),
		'body'    => json_encode( array(
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
		/* Translators: %d - HTTP response code; %s - Response from the API */
			__( 'Unexpected response code: %d. Error: %s', 'easy-digital-downloads' ),
			$code,
			json_encode( $body )
		) );
	}

	if ( empty( $body->signupLink ) || empty( $body->nonce ) ) {
		wp_send_json_error( __( 'An unexpected error occurred.', 'easy-digital-downloads' ) );
	}

	/**
	 * We need to store this temporarily so we can use the nonce again in the next request.
	 * @see get_access_token()
	 */
	update_option( 'edd_paypal_commerce_connect_details', json_encode( $body ) );

	wp_send_json_success( $body );
}

add_action( 'wp_ajax_edd_paypal_commerce_connect', __NAMESPACE__ . '\process_connect' );

/**
 * AJAX handler for retrieving a one-time access token, then used to retrieve
 * the seller's API credentials.
 *
 * @since 2.11
 * @return void
 */
function get_and_save_credentials() {
	// This validates the nonce.
	check_ajax_referer( 'edd_process_paypal_connect' );

	if ( ! current_user_can( 'manage_options' ) ) {
		wp_send_json_error( __( 'You do not have permission to perform this action.', 'easy-digital-downloads' ) );
	}

	if ( empty( $_POST['auth_code'] ) || empty( $_POST['share_id'] ) ) {
		wp_send_json_error( __( 'Missing PayPal authentication information. Please try again.', 'easy-digital-downloads' ) );
	}

	$partner_details = json_decode( get_option( 'edd_paypal_commerce_connect_details' ) );
	if ( empty( $partner_details->nonce ) ) {
		wp_send_json_error( __( 'Missing nonce. Please refresh the page and try again.', 'easy-digital-downloads' ) );
	}

	$mode             = edd_is_test_mode() ? 'sandbox' : 'live';
	$paypal_subdomain = edd_is_test_mode() ? '.sandbox' : '';
	$api_url          = 'https://api-m' . $paypal_subdomain . '.paypal.com/';

	/*
	 * First get a temporary access token from PayPal.
	 */
	$response = wp_remote_post( $api_url . 'v1/oauth2/token', array(
		'headers' => array(
			'Content-Type'  => 'application/x-www-form-urlencoded',
			'Authorization' => sprintf( 'Basic %s', base64_encode( $_POST['share_id'] ) ),
			'timeout'       => 15
		),
		'body'    => array(
			'grant_type'    => 'authorization_code',
			'code'          => $_POST['auth_code'],
			'code_verifier' => $partner_details->nonce
		)
	) );

	if ( is_wp_error( $response ) ) {
		wp_send_json_error( $response->get_error_message() );
	}

	$code = wp_remote_retrieve_response_code( $response );
	$body = json_decode( wp_remote_retrieve_body( $response ) );

	if ( empty( $body->access_token ) ) {
		wp_send_json_error( sprintf(
		/* Translators: %d - HTTP response code */
			__( 'Unexpected response from PayPal while generating token. Response code: %d. Please try again.', 'easy-digital-downloads' ),
			$code
		) );
	}

	/*
	 * Now we can use this access token to fetch the seller's credentials for all future
	 * API requests.
	 */
	$response = wp_remote_get( $api_url . 'v1/customer/partners/' . urlencode( \EDD\PayPal\get_partner_merchant_id( $mode ) ) . '/merchant-integrations/credentials/', array(
		'headers' => array(
			'Authorization' => sprintf( 'Bearer %s', $body->access_token ),
			'Content-Type'  => 'application/json',
			'timeout'       => 15
		)
	) );

	if ( is_wp_error( $response ) ) {
		wp_send_json_error( $response->get_error_message() );
	}

	$code = wp_remote_retrieve_response_code( $response );
	$body = json_decode( wp_remote_retrieve_body( $response ) );

	if ( empty( $body->client_id ) || empty( $body->client_secret ) ) {
		wp_send_json_error( sprintf(
		/* Translators: %d - HTTP response code */
			__( 'Unexpected response from PayPal. Response code: %d. Please try again.', 'easy-digital-downloads' ),
			$code
		) );
	}

	edd_update_option( 'paypal_' . $mode . '_client_id', sanitize_text_field( $body->client_id ) );
	edd_update_option( 'paypal_' . $mode . '_client_secret', sanitize_text_field( $body->client_secret ) );

	// We don't need the nonce anymore so we can delete that.
	delete_option( 'edd_paypal_commerce_connect_details' );

	wp_send_json_success( esc_html__( 'Successfully connected.', 'easy-digital-downloads' ) );
}

add_action( 'wp_ajax_edd_paypal_commerce_get_access_token', __NAMESPACE__ . '\get_and_save_credentials' );

/**
 * Verifies the connected account.
 *
 * @since 2.11
 * @return void
 */
function get_account_info() {
	check_ajax_referer( 'edd_paypal_account_information' );

	if ( ! current_user_can( 'manage_shop_settings' ) ) {
		wp_send_json_error( __( 'You do not have permission to perform this action.', 'easy-digital-downloads' ) );
	}

	try {
		$api      = new PayPal\API();
		$response = $api->make_request( 'v1/identity/oauth2/userinfo?schema=paypalv1.1', array(), array(), 'GET' );

		if ( empty( $response->user_id ) ) {
			wp_send_json_error( __( 'Unable to retrieve account information from PayPal. You may wish to try reconnecting.', 'easy-digital-downloads' ) );
		}

		/**
		 * Note: Despite the docs saying you'll get a full profile back, including name, email, etc.
		 * we're only actually getting the `user_id` back, which isn't super helpful. So although this
		 * is coded to *potentially* support showing the email address of the account, in most (if not
		 * all) cases it will just say "yes you're connected".
		 *
		 * @link https://developer.paypal.com/docs/api/identity/v1/#userinfo
		 */

		/* Translators: %s - the connected mode, either `sandbox` or `live` */
		$mode    = edd_is_test_mode() ? __( 'sandbox', 'easy-digital-downloads' ) : __( 'live', 'easy-digital-downloads' );
		$message = sprintf( __( 'Your PayPal account is successfully connected in %s mode.', 'easy-digital-downloads' ), $mode );

		if ( ! empty( $response->emails ) && is_array( $response->emails ) ) {
			foreach ( $response->emails as $email ) {
				if ( ! empty( $email->value ) && ! empty( $email->primary ) ) {
					$message = sprintf(
					/* Translators: %1$s - PayPal account email; %2$s - the connected mode, either `sandbox` or `live` */
						__( 'You are successfully connected to the account <strong>%1$s</strong> in %2$s mode.', 'easy-digital-downloads' ),
						esc_html( $email->value ),
						$mode
					);
				}
			}
		}

		wp_send_json_success( $message );
	} catch ( \Exception $e ) {
		wp_send_json_error( $e->getMessage() );
	}
}

add_action( 'wp_ajax_edd_paypal_commerce_get_account_info', __NAMESPACE__ . '\get_account_info' );

/**
 * Returns the URL for disconnecting from PayPal Commerce.
 *
 * @since 2.11
 * @return string
 */
function get_disconnect_url() {
	return wp_nonce_url(
		add_query_arg(
			array(
				'edd_action' => 'disconnect_paypal_commerce'
			),
			admin_url()
		),
		'edd_disconnect_paypal_commerce'
	);
}

/**
 * Disconnects from PayPal in the current mode.
 *
 * @since 2.11
 * @return void
 */
function process_disconnect() {
	if ( ! current_user_can( 'manage_options' ) ) {
		wp_die( __( 'You do not have permission to perform this action.', 'easy-digital-downloads' ), __( 'Error', 'easy-digital-downloads' ), array( 'response' => 403 ) );
	}

	if ( empty( $_GET['_wpnonce'] ) || ! wp_verify_nonce( $_GET['_wpnonce'], 'edd_disconnect_paypal_commerce' ) ) {
		wp_die( __( 'You do not have permission to perform this action.', 'easy-digital-downloads' ), __( 'Error', 'easy-digital-downloads' ), array( 'response' => 403 ) );
	}

	$mode = edd_is_test_mode() ? 'sandbox' : 'live';

	$options_to_delete = array(
		'paypal_' . $mode . '_client_id',
		'paypal_' . $mode . '_client_secret'
	);

	foreach ( $options_to_delete as $option_name ) {
		edd_delete_option( $option_name );
	}

	wp_safe_redirect( esc_url_raw( admin_url( 'edit.php?post_type=download&page=edd-settings&tab=gateways&section=paypal_commerce' ) ) );
	exit;
}

add_action( 'edd_disconnect_paypal_commerce', __NAMESPACE__ . '\process_disconnect' );
