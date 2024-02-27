<?php
/**
 * Admin Settings: Stripe Connect
 *
 * @package EDD_Stripe\Admin\Settings\Stripe_Connect
 * @copyright Copyright (c) 2019, Sandhills Development, LLC
 * @license http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since 2.8.0
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Retrieves a URL to allow Stripe Connect via oAuth.
 *
 * @since 2.8.0
 *
 * @return string
 */
function edds_stripe_connect_url() {
	$return_url = add_query_arg(
		array(
			'post_type' => 'download',
			'page'      => 'edd-settings',
			'tab'       => 'gateways',
			'section'   => 'edd-stripe',
		),
		admin_url( 'edit.php' )
	);

	/**
	 * Filters the URL users are returned to after using Stripe Connect oAuth.
	 *
	 * @since 2.8.0
	 *
	 * @param $return_url URL to return to.
	 */
	$return_url = apply_filters( 'edds_stripe_connect_return_url', $return_url );

	$stripe_connect_url = add_query_arg(
		array(
			'live_mode'         => (int) ! edd_is_test_mode(),
			'state'             => str_pad( wp_rand( wp_rand(), PHP_INT_MAX ), 100, wp_rand(), STR_PAD_BOTH ),
			'customer_site_url' => esc_url_raw( $return_url ),
		),
		'https://easydigitaldownloads.com/?edd_gateway_connect_init=stripe_connect'
	);

	/**
	 * Filters the URL to start the Stripe Connect oAuth flow.
	 *
	 * @since 2.8.0
	 *
	 * @param $stripe_connect_url URL to oAuth proxy.
	 */
	return apply_filters( 'edds_stripe_connect_url', $stripe_connect_url );
}

/**
 * Listens for Stripe Connect completion requests and saves the Stripe API keys.
 *
 * @since 2.6.14
 */
function edds_process_gateway_connect_completion() {

	$redirect_screen = ! empty( $_GET['redirect_screen'] ) ? sanitize_text_field( $_GET['redirect_screen'] ) : '';

	// A cancelled connection doesn't contain the completion or state values, but we do need to listen for the redirect_screen for the wizard.
	if (
		isset( $_GET['edd_gateway_connect_error'] ) &&
		filter_var( $_GET['edd_gateway_connect_error'], FILTER_VALIDATE_BOOLEAN ) &&
		! empty( $redirect_screen )
	) {
		$error_redirect = '';

		switch ( $redirect_screen ) {
			case 'onboarding-wizard':
				$error_redirect = edd_get_admin_url(
					array(
						'page'         => 'edd-onboarding-wizard',
						'current_step' => 'payment_methods',
					)
				);
				break;
		}

		if ( ! empty( $error_redirect ) ) {
			edd_redirect( $error_redirect );
		}
	}

	if ( ! isset( $_GET['edd_gateway_connect_completion'] ) || 'stripe_connect' !== $_GET['edd_gateway_connect_completion'] || ! isset( $_GET['state'] ) ) {
		return;
	}

	if ( ! current_user_can( 'manage_shop_settings' ) ) {
		return;
	}

	if ( headers_sent() ) {
		return;
	}

	$customer_site_url = edd_get_admin_url();
	if ( ! empty( $redirect_screen ) ) {
		$customer_site_url = add_query_arg( 'redirect_screen', $redirect_screen, $customer_site_url );
	}

	$edd_credentials_url = add_query_arg(
		array(
			'live_mode'         => (int) ! edd_is_test_mode(),
			'state'             => sanitize_text_field( $_GET['state'] ),
			'customer_site_url' => urlencode( $customer_site_url ),
		),
		'https://easydigitaldownloads.com/?edd_gateway_connect_credentials=stripe_connect'
	);

	$response = wp_remote_get( esc_url_raw( $edd_credentials_url ) );

	if ( is_wp_error( $response ) || 200 !== wp_remote_retrieve_response_code( $response ) ) {
		$message = '<p>' . sprintf(
			/* translators: %1$s Opening anchor tag, do not translate. %2$s Closing anchor tag, do not translate. */
			__( 'There was an error getting your Stripe credentials. Please %1$stry again%2$s. If you continue to have this problem, please contact support.', 'easy-digital-downloads' ),
			'<a href="' . esc_url( admin_url( 'edit.php?post_type=download&page=edd-settings&tab=gateways&section=edd-stripe' ) ) . '" target="_blank" rel="noopener noreferrer">',
			'</a>'
		) . '</p>';
		wp_die( $message );
	}

	$data = json_decode( $response['body'], true );
	$data = $data['data'];

	if ( edd_is_test_mode() ) {
		edd_update_option( 'test_publishable_key', sanitize_text_field( $data['publishable_key'] ) );
		edd_update_option( 'test_secret_key', sanitize_text_field( $data['secret_key'] ) );
	} else {
		$previously_connected = (bool) edd_get_option( 'live_publishable_key', false );
		if ( ! $previously_connected && ( edd_is_pro() || edds_is_pro() ) ) {
			set_transient( 'edd_stripe_new_install', time(), HOUR_IN_SECONDS * 72 );
		}
		edd_update_option( 'live_publishable_key', sanitize_text_field( $data['publishable_key'] ) );
		edd_update_option( 'live_secret_key', sanitize_text_field( $data['secret_key'] ) );
	}

	edd_update_option( 'stripe_connect_account_id', sanitize_text_field( $data['stripe_user_id'] ) );

	$redirect_url = edd_get_admin_url(
		array(
			'page'    => 'edd-settings',
			'tab'     => 'gateways',
			'section' => 'edd-stripe',
		)
	);

	if ( ! empty( $redirect_screen ) && 'onboarding-wizard' === $redirect_screen ) {
		$redirect_url       = edd_get_admin_url(
			array(
				'page'         => 'edd-onboarding-wizard',
				'current_step' => 'payment_methods',
			)
		);
		$gateways           = edd_get_option( 'gateways', array() );
		$gateways['stripe'] = true;
		edd_update_option( 'gateways', $gateways );
	}

	edd_redirect( $redirect_url );
}
add_action( 'admin_init', 'edds_process_gateway_connect_completion' );

/**
 * Returns a URL to disconnect the current Stripe Connect account ID and keys.
 *
 * @since 2.8.0
 *
 * @return string $stripe_connect_disconnect_url URL to disconnect an account ID and keys.
 */
function edds_stripe_connect_disconnect_url() {
	$stripe_connect_disconnect_url = add_query_arg(
		array(
			'post_type'              => 'download',
			'page'                   => 'edd-settings',
			'tab'                    => 'gateways',
			'section'                => 'edd-stripe',
			'edds-stripe-disconnect' => true,
		),
		admin_url( 'edit.php' )
	);

	/**
	 * Filters the URL to "disconnect" the Stripe Account.
	 *
	 * @since 2.8.0
	 *
	 * @param $stripe_connect_disconnect_url URL to remove the associated Account ID.
	 */
	$stripe_connect_disconnect_url = apply_filters(
		'edds_stripe_connect_disconnect_url',
		$stripe_connect_disconnect_url
	);

	$stripe_connect_disconnect_url = wp_nonce_url( $stripe_connect_disconnect_url, 'edds-stripe-connect-disconnect' );

	return $stripe_connect_disconnect_url;
}

/**
 * Removes the associated Stripe Connect Account ID and keys.
 *
 * This does not revoke application permissions from the Stripe Dashboard,
 * it simply allows the "Connect with Stripe" flow to run again for a different account.
 *
 * @since 2.8.0
 */
function edds_stripe_connect_process_disconnect() {
	// Do not need to handle this request, bail.
	if (
		! ( isset( $_GET['page'] ) && ( 'edd-settings' === $_GET['page'] || 'edd-onboarding-wizard' === $_GET['page'] ) ) ||
		! isset( $_GET['edds-stripe-disconnect'] )
	) {
		return;
	}

	// Current user cannot handle this request, bail.
	if ( ! current_user_can( 'manage_options' ) ) {
		return;
	}

	// No nonce, bail.
	if ( ! isset( $_GET['_wpnonce'] ) ) {
		return;
	}

	// Invalid nonce, bail.
	if ( ! wp_verify_nonce( $_GET['_wpnonce'], 'edds-stripe-connect-disconnect' ) ) {
		return;
	}

	$options = array(
		'stripe_connect_account_id',
		'stripe_connect_account_country',
		'test_publishable_key',
		'test_secret_key',
		'live_publishable_key',
		'live_secret_key',
		'stripe_statement_descriptor_prefix',
	);

	foreach ( $options as $option ) {
		edd_delete_option( $option );
	}

	// Remove Stripe from the enabled gateways.
	$gateways = edd_get_option( 'gateways', array() );
	unset( $gateways['stripe'] );
	edd_update_option( 'gateways', $gateways );

	$redirect = remove_query_arg(
		array(
			'_wpnonce',
			'edds-stripe-disconnect',
		)
	);

	return wp_redirect( esc_url_raw( $redirect ) );
}
add_action( 'admin_init', 'edds_stripe_connect_process_disconnect' );

/**
 * Updates the `stripe_connect_account_country` setting if using Stripe Connect
 * and no country information is available.
 *
 * @since 2.8.7
 */
function edds_stripe_connect_maybe_refresh_account_country() {
	// Current user cannot modify options, bail.
	if ( false === current_user_can( 'manage_options' ) ) {
		return;
	}

	$account_id = edd_stripe()->connect()->get_connect_id();

	// Stripe Connect has not been used, bail.
	if ( empty( $account_id ) ) {
		return;
	}

	// Account country is already set, bail.
	$account_country = edd_get_option( 'stripe_connect_account_country', '' );

	if ( ! empty( $account_country ) ) {
		return;
	}

	try {
		$account = edds_api_request( 'Account', 'retrieve', $account_id );

		if ( isset( $account->country ) ) {
			$account_country = sanitize_text_field(
				strtolower( $account->country )
			);

			edd_update_option(
				'stripe_connect_account_country',
				$account_country
			);
		}
	} catch ( \Exception $e ) {
		// Do nothing.
	}
}
add_action( 'admin_init', 'edds_stripe_connect_maybe_refresh_account_country' );

/**
 * Renders custom HTML for the "Stripe Connect" setting field in the Stripe Payment Gateway
 * settings subtab.
 *
 * Provides a way to use Stripe Connect and manually manage API keys.
 *
 * @since 2.8.0
 */
function edds_stripe_connect_setting_field() {
	$stripe_connect_url    = edds_stripe_connect_url();
	$stripe_disconnect_url = edds_stripe_connect_disconnect_url();

	$stripe_connect_account_id = edd_stripe()->connect()->get_connect_id();

	$api_key = edd_is_test_mode()
		? edd_get_option( 'test_publishable_key' )
		: edd_get_option( 'live_publishable_key' );

	ob_start();
	?>

	<?php if ( empty( $api_key ) ) : ?>

	<a href="<?php echo esc_url( $stripe_connect_url ); ?>" class="edd-stripe-connect">
		<span><?php esc_html_e( 'Connect with Stripe', 'easy-digital-downloads' ); ?></span>
	</a>

	<p>
		<?php
		echo wp_kses_post( edd_stripe()->application_fee->get_fee_message() );
		echo wp_kses(
			sprintf(
				/* translators: %1$s Opening anchor tag, do not translate. %2$s Closing anchor tag, do not translate. */
				__( 'Have questions about connecting with Stripe? See the %1$sdocumentation%2$s.', 'easy-digital-downloads' ),
				'<a href="' . esc_url( edds_documentation_route( 'stripe' ) ) . '" target="_blank" rel="noopener noreferrer">',
				'</a>'
			),
			array(
				'a' => array(
					'href'   => true,
					'target' => true,
					'rel'    => true,
				),
			)
		);
		?>
	</p>

	<?php endif; ?>

	<?php if ( ! empty( $api_key ) ) : ?>

	<div
		id="edds-stripe-connect-account"
		class="edds-stripe-connect-acount-info notice inline loading"
		data-account-id="<?php echo esc_attr( $stripe_connect_account_id ); ?>"
		data-nonce="<?php echo wp_create_nonce( 'edds-stripe-connect-account-information' ); ?>"
		<?php echo ( ! empty( $_GET['page'] ) && 'edd-onboarding-wizard' === $_GET['page'] ) ? ' data-onboarding-wizard="true"' : ''; ?>>
		<p>
			<span class="account-name"></span>
			<span class="info"></span>
		</p>
	</div>
	<div id="edds-stripe-disconnect-reconnect" class="loading">
	</div>

	<?php endif; ?>

	<?php if ( true === edds_stripe_connect_can_manage_keys() ) : ?>

	<div class="edds-api-key-toggle">
		<p>
			<button type="button" class="button-link">
				<small>
					<?php esc_html_e( 'Manage API keys manually', 'easy-digital-downloads' ); ?>
				</small>
			</button>
		</p>
	</div>

	<div class="edds-api-key-toggle edd-hidden">
		<p>
			<button type="button" class="button-link">
				<small>
					<?php esc_html_e( 'Hide API keys', 'easy-digital-downloads' ); ?>
				</small>
			</button>
		</p>

		<div class="notice inline notice-warning" style="margin: 15px 0 -10px;">
			<?php echo wpautop( esc_html__( 'Although you can add your API keys manually, we recommend using Stripe Connect: an easier and more secure way of connecting your Stripe account to your website. Stripe Connect prevents issues that can arise when copying and pasting account details from Stripe into your Easy Digital Downloads payment gateway settings. With Stripe Connect you\'ll be ready to go with just a few clicks.', 'easy-digital-downloads' ) ); ?>
		</div>
	</div>

	<?php endif; ?>

	<?php
	return ob_get_clean();
}

/**
 * Responds to an AJAX request about the current Stripe connection status.
 *
 * @since 2.8.0
 */
function edds_stripe_connect_account_info_ajax_response() {
	// Generic error.
	$unknown_error = array(
		'message' => wpautop( esc_html__( 'Unable to retrieve account information.', 'easy-digital-downloads' ) ),
	);

	// Current user can't manage settings.
	if ( ! current_user_can( 'manage_shop_settings' ) ) {
		return wp_send_json_error( $unknown_error );
	}

	// Nonce validation, show error on fail.
	if ( ! isset( $_POST['nonce'] ) || ! wp_verify_nonce( $_POST['nonce'], 'edds-stripe-connect-account-information' ) ) {
		return wp_send_json_error( $unknown_error );
	}

	$account_id = isset( $_POST['accountId'] )
		? sanitize_text_field( $_POST['accountId'] )
		: '';

	$mode = edd_is_test_mode()
		? _x( 'test', 'Stripe Connect mode', 'easy-digital-downloads' )
		: _x( 'live', 'Stripe Connect mode', 'easy-digital-downloads' );

	// Provides general reconnect and disconnect action URLs.
	$reconnect_disconnect_actions = wp_kses(
		sprintf(
			/* translators: %1$s Stripe payment mode. %2$s Opening anchor tag for reconnecting to Stripe, do not translate. %3$s Opening anchor tag for disconnecting Stripe, do not translate. %4$s Closing anchor tag, do not translate. */
			__( 'Your Stripe account is connected in %1$s mode. %2$sReconnect in %1$s mode%4$s, or %3$sdisconnect this account%4$s.', 'easy-digital-downloads' ),
			'<strong>' . $mode . '</strong>',
			'<a href="' . esc_url( edds_stripe_connect_url() ) . '" rel="noopener noreferrer">',
			'<a href="' . esc_url( edds_stripe_connect_disconnect_url() ) . '">',
			'</a>'
		),
		array(
			'strong' => true,
			'a'      => array(
				'href' => true,
				'rel'  => true,
			),
		)
	);

	// If connecting in Test Mode Stripe gives you the opportunity to create a
	// temporary account. Alert the user of the limitations associated with
	// this type of account.
	$dev_account_error = array(
		'message' => wp_kses(
			wpautop(
				sprintf(
					/* translators: %1$s Opening bold tag, do not translate. %2$s Closing bold tag, do not translate. */
					__( 'You are currently connected to a %1$stemporary%2$s Stripe test account, which can only be used for testing purposes. You cannot manage this account in Stripe.', 'easy-digital-downloads' ),
					'<strong>',
					'</strong>'
				) . ' ' .
				(
					class_exists( 'EDD_Recurring' )
						? __(
							'Webhooks cannot be configured for recurring purchases with this account.',
							'easy-digital-downloads'
						)
						: ''
				) . ' ' .
				sprintf(
					/* translators: %1$s Opening link tag, do not translate. %2$s Closing link tag, do not translate. */
					__( '%1$sRegister a Stripe account%2$s for full access.', 'easy-digital-downloads' ),
					'<a href="https://dashboard.stripe.com/register" target="_blank" rel="noopener noreferrer">',
					'</a>'
				) . ' ' .
				'<br /><br />' .
				sprintf(
					/* translators: %1$s Opening anchor tag for disconnecting Stripe, do not translate. %2$s Closing anchor tag, do not translate. */
					__( '%1$sDisconnect this account%2$s.', 'easy-digital-downloads' ),
					'<a href="' . esc_url( edds_stripe_connect_disconnect_url() ) . '">',
					'</a>'
				)
			),
			array(
				'p'      => true,
				'strong' => true,
				'a'      => array(
					'href'   => true,
					'rel'    => true,
					'target' => true,
				),
			)
		),
		'status'  => 'warning',
	);

	// Attempt to show account information from Stripe Connect account.
	if ( ! empty( $account_id ) ) {
		try {
			$account = edds_api_request( 'Account', 'retrieve', $account_id );

			// Find the email.
			$email = isset( $account->email )
				? esc_html( $account->email )
				: '';

			// Find a Display Name.
			$display_name = isset( $account->display_name )
				? esc_html( $account->display_name )
				: '';

			if (
				empty( $display_name ) &&
				isset( $account->settings ) &&
				isset( $account->settings->dashboard ) &&
				isset( $account->settings->dashboard->display_name )
			) {
				$display_name = esc_html( $account->settings->dashboard->display_name );
			}

			// Unsaved/unactivated accounts do not have an email or display name.
			if ( empty( $email ) && empty( $display_name ) ) {
				return wp_send_json_success( $dev_account_error );
			}

			if ( ! empty( $display_name ) ) {
				$display_name = '<span class="display-name">' . $display_name . '</span>';
			}

			if ( ! empty( $email ) ) {
				$email = $email . ' &mdash; ';
			}

			/**
			 * Filters if the Stripe Connect fee messaging should show.
			 *
			 * @since 2.8.1
			 *
			 * @param bool $show_fee_message Show fee message, or not.
			 */
			$show_fee_message = edd_stripe()->application_fee->get_fee_message();

			$fee_message = ! empty( $show_fee_message )
				? wpautop( $show_fee_message )
				: '';

			$message = sprintf(
				'<span class="display-name">%1$s</span><span class="info">%2$s %3$s %4$s</span>',
				$display_name,
				$email,
				esc_html__( 'Administrator (Owner)', 'easy-digital-downloads' ),
				$fee_message
			);

			/**
			 * If we have a statement descriptor prefix in the account settings, save it so we can use it later.
			 *
			 * Saving it now ensures that if someone visits the Stripe settings page, it is updated.
			 */
			if ( isset( $account->settings->card_payments->statement_descriptor_prefix ) ) {
				edd_update_option( 'stripe_statement_descriptor_prefix', sanitize_text_field( $account->settings->card_payments->statement_descriptor_prefix ) );
			}

			// Return a message with name, email, and reconnect/disconnect actions.
			return wp_send_json_success(
				array(
					'message' => wpautop(
						$message
					),
					'actions' => $reconnect_disconnect_actions,
					'status'  => ! empty( $show_fee_message ) ? 'warning' : 'success',
					'account' => $account,
				)
			);
		} catch ( \Stripe\Exception\AuthenticationException $e ) {
			// API keys were changed after using Stripe Connect.
			return wp_send_json_error(
				array(
					'message' => wpautop(
						esc_html__( 'The API keys provided do not match the Stripe Connect account associated with this installation. If you have manually modified these values after connecting your account, please reconnect below or update your API keys.', 'easy-digital-downloads' ) .
						'<br /><br />' .
						$reconnect_disconnect_actions
					),
				)
			);
		} catch ( \EDD_Stripe_Utils_Exceptions_Stripe_API_Unmet_Requirements $e ) {
			return wp_send_json_error(
				array(
					'message' => wpautop(
						$e->getMessage()
					),
				)
			);
		} catch ( \Exception $e ) {
			// General error.
			return wp_send_json_error( $unknown_error );
		}
		// Manual API key management.
	} else {
		$connect_button = sprintf(
			'<a href="%s" class="edd-stripe-connect"><span>%s</span></a>',
			esc_url( edds_stripe_connect_url() ),
			esc_html__( 'Connect with Stripe', 'easy-digital-downloads' )
		);

		$connect = esc_html__( 'It is highly recommended to Connect with Stripe for easier setup and improved security.', 'easy-digital-downloads' );

		// See if the keys are valid.
		try {
			// While we could show similar account information, leave it blank to help
			// push people towards Stripe Connect.
			$account = edds_api_request( 'Account', 'retrieve' );

			return wp_send_json_success(
				array(
					'message' => wpautop(
						sprintf(
							/* translators: %1$s Stripe payment mode.*/
							__( 'Your manually managed %1$s mode API keys are valid.', 'easy-digital-downloads' ),
							'<strong>' . $mode . '</strong>'
						) .
						'<br /><br />' .
						$connect . '<br /><br />' . $connect_button
					),
					'status'  => 'success',
				)
			);
			// Show invalid keys.
		} catch ( \Exception $e ) {
			return wp_send_json_error(
				array(
					'message' => wpautop(
						sprintf(
							/* translators: %1$s Stripe payment mode.*/
							__( 'Your manually managed %1$s mode API keys are invalid.', 'easy-digital-downloads' ),
							'<strong>' . $mode . '</strong>'
						) .
						'<br /><br />' .
						$connect . '<br /><br />' . $connect_button
					),
				)
			);
		}
	}
}
add_action( 'wp_ajax_edds_stripe_connect_account_info', 'edds_stripe_connect_account_info_ajax_response' );

/**
 * Registers admin notices for Stripe Connect.
 *
 * @since 2.8.0
 *
 * @return true|WP_Error True if all notices are registered, otherwise WP_Error.
 */
function edds_stripe_connect_admin_notices_register() {
	$registry = edds_get_registry( 'admin-notices' );

	if ( ! $registry ) {
		return new WP_Error( 'edds-invalid-registry', esc_html__( 'Unable to locate registry', 'easy-digital-downloads' ) );
	}

	$connect_button = sprintf(
		'<a href="%s" class="edd-stripe-connect"><span>%s</span></a>',
		esc_url( edds_stripe_connect_url() ),
		esc_html__( 'Connect with Stripe', 'easy-digital-downloads' )
	);

	try {
		// Stripe Connect.
		$registry->add(
			'stripe-connect',
			array(
				'message'     => sprintf(
					'<p>%s</p><p>%s</p>',
					esc_html__( 'Start accepting payments with Stripe by connecting your account. Stripe Connect helps ensure easier setup and improved security.', 'easy-digital-downloads' ),
					$connect_button
				),
				'type'        => 'info',
				'dismissible' => true,
			)
		);

		// Stripe Connect reconnect.
		/** translators: %s Test mode status. */
		$test_mode_status = edd_is_test_mode()
			? _x( 'enabled', 'gateway test mode status', 'easy-digital-downloads' )
			: _x( 'disabled', 'gateway test mode status', 'easy-digital-downloads' );

		$registry->add(
			'stripe-connect-reconnect',
			array(
				'message'     => sprintf(
					'<p>%s</p><p>%s</p>',
					sprintf(
						/* translators: %s Test mode status. Enabled or disabled. */
						__( '"Test Mode" has been %s. Please verify your Stripe connection status.', 'easy-digital-downloads' ),
						$test_mode_status
					),
					$connect_button
				),
				'type'        => 'warning',
				'dismissible' => true,
			)
		);

	} catch ( Exception $e ) {
		return new WP_Error( 'edds-invalid-notices-registration', $e->getMessage() );
	}

	return true;
}
add_action( 'admin_init', 'edds_stripe_connect_admin_notices_register' );

/**
 * Conditionally prints registered notices.
 *
 * @since 2.6.19
 */
function edds_stripe_connect_admin_notices_print() {
	// Current user needs capability to dismiss notices.
	if ( ! current_user_can( 'manage_options' ) ) {
		return;
	}

	$screen  = get_current_screen();
	$section = filter_input( INPUT_GET, 'section', FILTER_SANITIZE_SPECIAL_CHARS );
	if ( $screen && 'download_page_edd-settings' === $screen->id && 'edd-stripe' === $section ) {
		return;
	}
	$registry = edds_get_registry( 'admin-notices' );

	if ( ! $registry ) {
		return;
	}

	$notices = new EDD_Stripe_Admin_Notices( $registry );

	wp_enqueue_script( 'edds-admin-notices' );

	try {
		$enabled_gateways = edd_get_enabled_payment_gateways();

		$api_key = true === edd_is_test_mode()
			? edd_get_option( 'test_secret_key' )
			: edd_get_option( 'live_secret_key' );

		$mode_toggle = isset( $_GET['edd-message'] ) && 'connect-to-stripe' === $_GET['edd-message'];

		if ( array_key_exists( 'stripe', $enabled_gateways ) && empty( $api_key ) ) {
			edd_stripe_connect_admin_style();

			// Stripe Connect.
			if ( false === $mode_toggle ) {
				$notices->output( 'stripe-connect' );
				// Stripe Connect reconnect.
			} else {
				$notices->output( 'stripe-connect-reconnect' );
			}
		}
	} catch ( Exception $e ) {
		// Do nothing.
	}
}
add_action( 'admin_notices', 'edds_stripe_connect_admin_notices_print' );
