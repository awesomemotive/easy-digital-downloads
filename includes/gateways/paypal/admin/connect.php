<?php
/**
 * PayPal Commerce Connect
 *
 * @package   easy-digital-downloads
 * @copyright Copyright (c) 2021, Sandhills Development, LLC
 * @license   GPL2+
 * @since     2.11
 */

namespace EDD\Gateways\PayPal\Admin;

use EDD\Gateways\PayPal;
use EDD\Gateways\PayPal\AccountStatusValidator;
use EDD\Gateways\PayPal\API;

if ( ! defined( 'EDD_PAYPAL_PARTNER_CONNECT_URL' ) ) {
	define( 'EDD_PAYPAL_PARTNER_CONNECT_URL', 'https://easydigitaldownloads.com/wp-json/paypal-connect/v1/' );
}

/**
 * Returns the content for the PayPal Commerce Connect fields.
 *
 * If the account is not yet connected, the user is shown a "Connect with PayPal" button.
 * If they are connected, their account details are shown instead.
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
			/* Translators: %s - the store mode, either `sandbox` or `live` */
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
			'return_url' => get_settings_url()
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
	 *
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

	$mode             = edd_is_test_mode() ? PayPal\API::MODE_SANDBOX : PayPal\API::MODE_LIVE;
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
	$response = wp_remote_get( $api_url . 'v1/customer/partners/' . urlencode( \EDD\Gateways\PayPal\get_partner_merchant_id( $mode ) ) . '/merchant-integrations/credentials/', array(
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

	$message = esc_html__( 'Successfully connected.', 'easy-digital-downloads' );

	try {
		PayPal\Webhooks\create_webhook( $mode );
	} catch ( \Exception $e ) {
		$message = esc_html__( 'Your account has been successfully connected, but an error occurred while creating a webhook.', 'easy-digital-downloads' );
	}

	/**
	 * Triggers when an account is successfully connected to PayPal.
	 *
	 * @param string $mode The mode that the account was connected in. Either `sandbox` or `live`.
	 *
	 * @since 2.11
	 */
	do_action( 'edd_paypal_commerce_connected', $mode );

	wp_send_json_success( $message );
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
		wp_send_json_error( wpautop( __( 'You do not have permission to perform this action.', 'easy-digital-downloads' ) ) );
	}

	try {
		$status         = 'success';
		$account_status = '';
		$actions        = array();

		$validator = new AccountStatusValidator();
		$validator->check();

		/*
		 * 1. Check REST API credentials
		 */
		$rest_api_message = '<strong>' . __( 'API:', 'easy-digital-downloads' ) . '</strong>' . ' ';
		if ( $validator->errors_for_credentials->errors ) {
			$rest_api_dashicon = 'no';
			$status            = 'error';
			$rest_api_message  .= $validator->errors_for_credentials->get_error_message();
		} else {
			$rest_api_dashicon = 'yes';
			$mode_string       = edd_is_test_mode() ? __( 'sandbox', 'easy-digital-downloads' ) : __( 'live', 'easy-digital-downloads' );

			/* Translators: %s - the connected mode, either `sandbox` or `live` */
			$rest_api_message .= sprintf( __( 'Your PayPal account is successfully connected in %s mode.', 'easy-digital-downloads' ), $mode_string );
		}

		ob_start();
		?>
		<li>
			<span class="dashicons dashicons-<?php echo esc_attr( $rest_api_dashicon ); ?>"></span>
			<span><?php echo wp_kses( $rest_api_message, array( 'strong' => array() ) ); ?></span>
		</li>
		<?php
		$account_status .= ob_get_clean();

		/*
		 * 2. Check merchant account
		 */
		$merchant_account_message = '<strong>' . __( 'Payment Status:', 'easy-digital-downloads' ) . '</strong>' . ' ';
		if ( $validator->errors_for_merchant_account->errors ) {
			$merchant_dashicon        = 'no';
			$status                   = 'error';
			$merchant_account_message .= __( 'You need to address the following issues before you can start receiving payments:', 'easy-digital-downloads' );
		} else {
			$merchant_dashicon        = 'yes';
			$merchant_account_message .= __( 'Ready to accept payments.', 'easy-digital-downloads' );

			$actions = array(
				'<button type="button" class="button edd-paypal-connect-action" data-nonce="' . esc_attr( wp_create_nonce( 'edd_update_paypal_webhook' ) ) . '" data-action="edd_paypal_commerce_update_webhook">' . esc_html__( 'Sync Webhook', 'easy-digital-downloads' ) . '</button>'
			);
		}

		ob_start();
		?>
		<li>
			<span class="dashicons dashicons-<?php echo esc_attr( $merchant_dashicon ); ?>"></span>
			<span><?php echo wp_kses_post( $merchant_account_message ); ?></span>
			<?php if ( $validator->errors_for_merchant_account->errors ) : ?>
				<ul>
					<?php foreach ( $validator->errors_for_merchant_account->get_error_codes() as $code ) : ?>
						<li><?php echo wp_kses( $validator->errors_for_merchant_account->get_error_message( $code ), array( 'strong' => array() ) ); ?></li>
					<?php endforeach; ?>
				</ul>
			<?php endif; ?>
		</li>
		<?php
		$account_status .= ob_get_clean();

		/*
		 * 3. Webhooks
		 */
		$webhook_message = '<strong>' . __( 'Webhook:', 'easy-digital-downloads' ) . '</strong>' . ' ';
		if ( $validator->errors_for_webhook->errors ) {
			$webhook_dashicon = 'no';
			$status           = ( 'success' === $status ) ? 'warning' : $status;
			$webhook_message  .= $validator->errors_for_webhook->get_error_message();

			if ( in_array( 'webhook_missing', $validator->errors_for_webhook->get_error_codes() ) ) {
				$actions = array(
					'<button type="button" class="button edd-paypal-connect-action" data-nonce="' . esc_attr( wp_create_nonce( 'edd_create_paypal_webhook' ) ) . '" data-action="edd_paypal_commerce_create_webhook">' . esc_html__( 'Create Webhook', 'easy-digital-downloads' ) . '</button>'
				);
			}
		} else {
			$webhook_dashicon = 'yes';
			$webhook_message  .= __( 'Webhook successfully configured for the following events:', 'easy-digital-downloads' );
		}

		ob_start();
		?>
		<li>
			<span class="dashicons dashicons-<?php echo esc_attr( $webhook_dashicon ); ?>"></span>
			<span><?php echo wp_kses( $webhook_message, array( 'strong' => array() ) ); ?></span>
			<?php if ( $validator->webhook ) : ?>
				<ul class="edd-paypal-webhook-events">
					<?php foreach ( array_keys( PayPal\Webhooks\get_webhook_events() ) as $event_name ) : ?>
						<li>
							<span class="dashicons dashicons-<?php echo in_array( $event_name, $validator->enabled_webhook_events ) ? 'yes' : 'no'; ?>"></span>
							<span class="edd-paypal-webhook-event-name"><?php echo esc_html( $event_name ); ?></span>
						</li>
					<?php endforeach; ?>
				</ul>
			<?php endif; ?>
		</li>
		<?php
		$account_status .= ob_get_clean();

		if ( ! edd_is_gateway_active( 'paypal_commerce' ) ) {
			$account_status .= ' ' . sprintf(
				/* Translators: %1$s opening anchor tag; %2$s closing anchor tag */
					__( 'To start using PayPal, be sure to %1$senable it%2$s in the general gateway settings.', 'easy-digital-downloads' ),
					'<a href="' . esc_url( admin_url( 'edit.php?post_type=download&page=edd-settings&tab=gateways&section=main' ) ) . '">',
					'</a>'
				);
		}

		wp_send_json_success( array(
			'status'         => $status,
			'account_status' => '<ul class="edd-paypal-account-status">' . $account_status . '</ul>',
			'webhook_object' => isset( $validator ) ? $validator->webhook : null,
			'actions'        => $actions
		) );
	} catch ( \Exception $e ) {
		wp_send_json_error( array(
			'status'  => isset( $status ) ? $status : 'error',
			'message' => wpautop( $e->getMessage() )
		) );
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
		wp_die( esc_html__( 'You do not have permission to perform this action.', 'easy-digital-downloads' ), esc_html__( 'Error', 'easy-digital-downloads' ), array( 'response' => 403 ) );
	}

	if ( empty( $_GET['_wpnonce'] ) || ! wp_verify_nonce( $_GET['_wpnonce'], 'edd_disconnect_paypal_commerce' ) ) {
		wp_die( esc_html__( 'You do not have permission to perform this action.', 'easy-digital-downloads' ), esc_html__( 'Error', 'easy-digital-downloads' ), array( 'response' => 403 ) );
	}

	$mode = edd_is_test_mode() ? PayPal\API::MODE_SANDBOX : PayPal\API::MODE_LIVE;

	$edd_settings_to_delete = array(
		'paypal_' . $mode . '_client_id',
		'paypal_' . $mode . '_client_secret'
	);

	delete_option( 'edd_paypal_' . $mode . '_merchant_details' );

	delete_connection_errors();

	try {
		// Also delete the token cache key, to ensure we fetch a fresh one if they connect to a different account later.
		$api                 = new PayPal\API();
		$edd_settings_to_delete[] = $api->token_cache_key;

		// Disconnect the webhook.
		PayPal\Webhooks\delete_webhook( $mode );
	} catch ( \Exception $e ) {

	}

	foreach ( $edd_settings_to_delete as $option_name ) {
		edd_delete_option( $option_name );
	}

	wp_safe_redirect( esc_url_raw( get_settings_url() ) );
	exit;
}

add_action( 'edd_disconnect_paypal_commerce', __NAMESPACE__ . '\process_disconnect' );

/**
 * AJAX callback for creating a webhook.
 *
 * @since 2.11
 */
function create_webhook() {
	check_ajax_referer( 'edd_create_paypal_webhook' );

	if ( ! current_user_can( 'manage_options' ) ) {
		wp_send_json_error( esc_html__( 'You do not have permission to perform this action.', 'easy-digital-downloads' ) );
	}

	try {
		PayPal\Webhooks\create_webhook();

		wp_send_json_success();
	} catch ( \Exception $e ) {
		wp_send_json_error( esc_html( $e->getMessage() ) );
	}
}

add_action( 'wp_ajax_edd_paypal_commerce_create_webhook', __NAMESPACE__ . '\create_webhook' );

/**
 * AJAX callback for syncing a webhook. This is used to fix issues with missing events.
 *
 * @since 2.11
 */
function update_webhook() {
	check_ajax_referer( 'edd_update_paypal_webhook' );

	if ( ! current_user_can( 'manage_options' ) ) {
		wp_send_json_error( esc_html__( 'You do not have permission to perform this action.', 'easy-digital-downloads' ) );
	}

	try {
		PayPal\Webhooks\sync_webhook();

		wp_send_json_success();
	} catch ( \Exception $e ) {
		wp_send_json_error( esc_html( $e->getMessage() ) );
	}
}

add_action( 'wp_ajax_edd_paypal_commerce_update_webhook', __NAMESPACE__ . '\update_webhook' );

/**
 * PayPal Redirect Callback
 *
 * This processes after the merchant is redirected from PayPal. We immediately
 * check their seller status via partner connect, save any errors, and save
 * their merchant status if available. The user is then redirected back to
 * the settings page.
 *
 * @since 2.11
 */
add_action( 'admin_init', function () {
	if ( ! isset( $_GET['merchantIdInPayPal'] ) || ! edd_is_admin_page( 'settings' ) ) {
		return;
	}

	if ( ! current_user_can( 'manage_options' ) ) {
		wp_die( __( 'You do not have permission to perform this action.', 'easy-digital-downloads' ), __( 'Error', 'easy-digital-downloads' ), array( 'response' => 403 ) );
	}

	edd_debug_log( 'PayPal Connect - Checking merchant status.' );

	delete_connection_errors();

	$response = wp_remote_post( EDD_PAYPAL_PARTNER_CONNECT_URL . 'status', array(
		'headers' => array(
			'Content-Type' => 'application/json',
		),
		'body'    => json_encode( array(
			'mode'        => edd_is_test_mode() ? 'sandbox' : 'live',
			'merchant_id' => sanitize_text_field( urldecode( $_GET['merchantIdInPayPal'] ) )
		) )
	) );

	try {
		if ( is_wp_error( $response ) ) {
			throw new \Exception( $response->get_error_message() );
		}

		$code = wp_remote_retrieve_response_code( $response );
		$body = json_decode( wp_remote_retrieve_body( $response ), true );

		if ( 200 !== intval( $code ) ) {
			$error_message = ! empty( $body->message ) ? $body->message : sprintf(
			/* Translators: %d - HTTP response code; %s - Response from the API */
				__( 'Unexpected response code: %d. Error: %s', 'easy-digital-downloads' ),
				$code,
				json_encode( $body )
			);

			throw new \Exception( $error_message );
		}

		$merchant_account = new PayPal\MerchantAccount( $body );
		$merchant_account->save();

		edd_debug_log( 'PayPal Connect - Successfully saved merchant details.' );
	} catch ( \Exception $e ) {
		edd_debug_log( sprintf(
			'PayPal Connect - Exception while checking account status. Response Code: %d; Message: %s',
			isset( $code ) ? $code : 0,
			$e->getMessage()
		) );

		save_connection_errors( array( $e->getMessage() ) );
	}

	wp_safe_redirect( esc_url_raw( get_settings_url() ) );
	exit;
} );

/**
 * Retrieves connection errors.
 *
 * @since 2.11
 *
 * @return array
 */
function get_connection_errors() {
	return get_option( sprintf(
		'edd_paypal_connect_errors_%s',
		edd_is_test_mode() ? API::MODE_SANDBOX : API::MODE_LIVE
	), array() );
}

/**
 * Saves new connection errors.
 *
 * @since 2.11
 *
 * @param string[] $errors
 */
function save_connection_errors( $errors ) {
	update_option( sprintf(
		'edd_paypal_connect_errors_%s',
		edd_is_test_mode() ? API::MODE_SANDBOX : API::MODE_LIVE
	), $errors );
}

/**
 * Deletes connection errors.
 */
function delete_connection_errors() {
	delete_option( sprintf(
		'edd_paypal_connect_errors_%s',
		edd_is_test_mode() ? API::MODE_SANDBOX : API::MODE_LIVE
	) );
}
