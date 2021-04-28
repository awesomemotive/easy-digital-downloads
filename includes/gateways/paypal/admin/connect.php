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
	define( 'EDD_PAYPAL_PARTNER_CONNECT_URL', 'https://easydigitaldownloads.com/wp-json/paypal-connect/v1/' );
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

	$mode = edd_is_test_mode() ? PayPal\API::MODE_SANDBOX : PayPal\API::MODE_LIVE;

	try {
		/**
		 * First get account information.
		 */
		$api      = new PayPal\API();
		$response = $api->make_request( 'v1/identity/oauth2/userinfo?schema=paypalv1.1', array(), array(), 'GET' );

		if ( empty( $response->user_id ) ) {
			wp_send_json_error( wpautop( __( 'Unable to retrieve account information from PayPal. You may wish to try reconnecting.', 'easy-digital-downloads' ) ) );
		}

		$status = 'success';

		/**
		 * Note: Despite the docs saying you'll get a full profile back, including name, email, etc.
		 * we're only actually getting the `user_id` back, which isn't super helpful. So although this
		 * is coded to *potentially* support showing the email address of the account, in most (if not
		 * all) cases it will just say "yes you're connected".
		 *
		 * @link https://developer.paypal.com/docs/api/identity/v1/#userinfo
		 */

		/* Translators: %s - the connected mode, either `sandbox` or `live` */
		$mode_string    = edd_is_test_mode() ? __( 'sandbox', 'easy-digital-downloads' ) : __( 'live', 'easy-digital-downloads' );
		$account_status = sprintf( __( 'Your PayPal account is successfully connected in %s mode.', 'easy-digital-downloads' ), $mode_string );

		if ( ! empty( $response->emails ) && is_array( $response->emails ) ) {
			foreach ( $response->emails as $email ) {
				if ( ! empty( $email->value ) && ! empty( $email->primary ) ) {
					$account_status = sprintf(
					/* Translators: %1$s - PayPal account email; %2$s - the connected mode, either `sandbox` or `live` */
						__( 'You are successfully connected to the account <strong>%1$s</strong> in %2$s mode.', 'easy-digital-downloads' ),
						esc_html( $email->value ),
						$mode_string
					);
				}
			}
		}

		/**
		 * Now check the webhook connection.
		 */
		$webhook_status = wpautop( esc_html__( 'Webhook successfully configured for the following events:', 'easy-digital-downloads' ) );
		$actions        = array(
			'<button type="button" class="button edd-paypal-connect-action" data-nonce="' . esc_attr( wp_create_nonce( 'edd_update_paypal_webhook' ) ) . '" data-action="edd_paypal_commerce_update_webhook">' . esc_html__( 'Sync Webhook', 'easy-digital-downloads' ) . '</button>'
		);
		try {
			$webhook = PayPal\Webhooks\get_webhook_details( $mode );
			if ( empty( $webhook->id ) ) {
				throw new \Exception();
			}

			// Now compare the events to make sure we have them all.
			$expected_events = PayPal\Webhooks\get_webhook_events( $mode );
			$actual_events   = array();

			if ( ! empty( $webhook->event_types ) && is_array( $webhook->event_types ) ) {
				foreach ( $webhook->event_types as $event_type ) {
					if ( ! empty( $event_type->name ) && ! empty( $event_type->status ) && 'ENABLED' === strtoupper( $event_type->status ) ) {
						$actual_events[] = $event_type->name;
					}
				}
			}

			$missing_events = array_diff( $expected_events, $actual_events );
			$number_missing = count( $missing_events );
			if ( $number_missing ) {
				$status         = 'warning';
				$webhook_status = wpautop( _n(
					'<strong>Warning:</strong> Webhook is configured but is missing an event. Click "Sync Webhook" to correct this.',
					'<strong>Warning:</strong> Webhook is configured but is missing events. Click "Sync Webhook" to correct this.',
					$number_missing,
					'easy-digital-downloads'
				) );
			}

			ob_start();
			?>
			<ul class="edd-paypal-webhook-events">
				<?php foreach ( $expected_events as $event_name ) : ?>
					<li>
						<span class="dashicons dashicons-<?php echo in_array( $event_name, $actual_events ) ? 'yes' : 'no'; ?>"></span>
						<span class="edd-paypal-webhook-event-name"><?php echo esc_html( $event_name ); ?></span>
					</li>
				<?php endforeach; ?>
			</ul>
			<?php
			$webhook_status .= ob_get_clean();
		} catch ( \Exception $e ) {
			$status         = 'warning';
			$webhook_status = wpautop( __( '<strong>Warning:</strong> Webhook not configured. Some actions may not be working properly.', 'easy-digital-downloads' ) );
			$actions        = array(
				'<button type="button" class="button edd-paypal-connect-action" data-nonce="' . esc_attr( wp_create_nonce( 'edd_create_paypal_webhook' ) ) . '" data-action="edd_paypal_commerce_create_webhook">' . esc_html__( 'Create Webhook', 'easy-digital-downloads' ) . '</button>'
			);
		}

		wp_send_json_success( array(
			'status'         => $status,
			'account_status' => wpautop( $account_status ),
			'webhook_status' => $webhook_status,
			'actions'        => $actions
		) );
	} catch ( \Exception $e ) {
		wp_send_json_error( wpautop( $e->getMessage() ) );
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
