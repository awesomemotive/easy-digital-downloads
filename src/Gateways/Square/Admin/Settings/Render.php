<?php
/**
 * Render the Square settings.
 *
 * @package     EDD\Gateways\Square\Admin\Settings
 * @copyright   Copyright (c) 2025, Sandhills Development, LLC
 * @license     https://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       3.4.0
 */

namespace EDD\Gateways\Square\Admin\Settings;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit; // @codeCoverageIgnore

use EDD\Gateways\Square\Connection;
use EDD\Gateways\Square\Gateway;
use EDD\Gateways\Square\Helpers\Currency;
use EDD\Gateways\Square\Helpers\Setting;
use EDD\Gateways\Square\ApplicationFee;

/**
 * Settings class.
 *
 * @since 3.4.0
 */
class Render {
	/**
	 * Gets the settings.
	 *
	 * @since 3.4.0
	 * @return array
	 */
	public function get() {
		if ( ! Gateway::is_store_country_supported() ) {
			return array();
		}

		$mode = edd_is_test_mode() ? 'sandbox' : 'live';

		$square_settings = array(
			'square_connect_button' => array(
				'id'    => 'square_connect_button',
				'name'  => __( 'Connection Status', 'easy-digital-downloads' ),
				'desc'  => $this->get_connect_setting_field(),
				'type'  => 'descriptive_text',
				'class' => 'edd-square-connect-row',
			),
			'square_location_id'    => array(
				'id'      => 'square_' . $mode . '_location_id',
				'name'    => __( 'Location ID', 'easy-digital-downloads' ),
				'desc'    => __( 'Select the location ID where payments will be processed.', 'easy-digital-downloads' ),
				'type'    => 'select',
				'size'    => 'regular',
				'current' => Setting::get( 'location_id' ),
				'options' => Setting::get( 'locations' ),
				'class'   => $this->get_connected_class(),
			),
		);

		return $square_settings;
	}

	/**
	 * Gets the connect setting field HTML.
	 *
	 * @since 3.4.0
	 * @return string
	 */
	private function get_connect_setting_field() {
		if ( ! edd_is_admin_page( 'settings', 'gateways' ) ) {
			return '';
		}

		$section = filter_input( INPUT_GET, 'section', FILTER_SANITIZE_SPECIAL_CHARS );
		if ( 'square' !== $section ) {
			return '';
		}

		if ( $this->is_connected() ) {
			return $this->get_connected_status_html();
		}

		return $this->get_disconnected_status_html();
	}

	/**
	 * Gets the HTML for connected status.
	 *
	 * @since 3.4.0
	 * @return string
	 */
	private function get_connected_status_html() {
		$connection      = new Connection();
		$status          = $connection->get_connection_status();
		$mode            = $status['mode'];
		$merchant_name   = $status['merchant_name'];
		$webhook         = Setting::get( 'webhook_subscription_id' );
		$application_fee = new ApplicationFee();

		// Account info box.
		$notice_class = $this->get_connection_status_class( $webhook, $application_fee );
		ob_start();
		?>
		<div class="edd-square-connect-status-wrapper notice inline <?php echo $notice_class; ?>">
		<?php

		// Show merchant business name.
		if ( $merchant_name && __( 'Unknown', 'easy-digital-downloads' ) !== $merchant_name ) {
			printf(
				/* translators: 1: Connected Account label, 2: Merchant name */
				'<p><strong>%1$s</strong> %2$s</p>',
				__( 'Connected Account:', 'easy-digital-downloads' ),
				$merchant_name
			);
		} else {
			printf(
				'<p><strong>%s</strong></p>',
				__( 'Square Account', 'easy-digital-downloads' )
			);
		}

		$message = $application_fee->get_fee_message();
		if ( ! empty( $message ) ) {
			printf(
				'<p>%s</p>',
				$message
			);
		}
		?>

		<p>
			<strong><?php _e( 'Webhooks Status:', 'easy-digital-downloads' ); ?></strong>
			<?php
			echo $webhook
				? esc_html_x( 'Registered', 'Webhooks status when registered', 'easy-digital-downloads' )
				: esc_html_x( 'Not registered', 'Webhooks status when not registered', 'easy-digital-downloads' );
			?>
		</p>
		<?php
		if ( ! $webhook ) {
			?>
			<p><?php _e( 'Square uses webhooks to notify Easy Digital Downloads when an event has occurred in your Square account.', 'easy-digital-downloads' ); ?></p>
			<p><?php _e( 'Please see our documentation on Square webhooks for full details.', 'easy-digital-downloads' ); ?></p>
			<p>
				<button
					class="button button-primary edd-promo-notice__trigger edd-promo-notice__trigger--ajax"
					data-id="squarewebhooksmodal"
					data-product-id="0"
					data-value="connect"
				>
					<?php _e( 'Connect Webhooks', 'easy-digital-downloads' ); ?>
				</button>
			</p>
			<?php
		} else {
			?>
			<p>
				<button
					class="button button-secondary edd-promo-notice__trigger edd-promo-notice__trigger--ajax"
					data-id="squarewebhooksmodal"
					data-product-id="0"
					data-value="refresh"
				>
					<?php _e( 'Refresh Webhooks', 'easy-digital-downloads' ); ?>
				</button>
			</p>
			<?php
		}

		?>
		</div>
		<?php

		/* translators: %s is the mode of the connection. */
		$reconnect_text = sprintf( __( 'Reconnect in %s mode', 'easy-digital-downloads' ), $mode );

		// The mode attribute for the oauth process is 'test' not 'sandbox', so adjust it if needed.
		$mode_attribute = 'sandbox' === $mode ? 'test' : $mode;
		printf(
			/* translators: 1: Mode label, 2: Reconnect button, 3: Disconnect button */
			'Your Square account is connected in %1$s. %2$s, or %3$s.',
			'<strong>' . $mode . ' mode</strong>',
			'<button class="button-link" id="edd-square-reconnect" data-mode="' . esc_attr( $mode_attribute ) . '">' . $reconnect_text . '</button>',
			'<button class="button-link" id="edd-square-disconnect">' . __( 'disconnect this account', 'easy-digital-downloads' ) . '</button>',
		);

		return ob_get_clean();
	}

	/**
	 * Gets the HTML for disconnected status.
	 *
	 * @since 3.4.0
	 * @return string
	 */
	private function get_disconnected_status_html() {
		$html = '';
		// If we are in test mode, inform the user that they need to use one of their 'Sandbox Test Accounts', and link to the documentation.
		if ( edd_is_test_mode() ) {
			$html .= '<div class="notice notice-warning inline">';
			$html .= '<p>' . __( 'Your store is currently in test mode. The process of connecting with Square in test mode is slightly different than in live mode.', 'easy-digital-downloads' ) . '</p>';
			$html .= '<p>' . sprintf(
				/* translators: %s: Documentation link */
				__( 'Please %1$sread these special instructions%2$s to connect with Square in test mode.', 'easy-digital-downloads' ),
				sprintf(
					'<a href="%s" target="_blank">',
					'https://easydigitaldownloads.com/docs/setting-up-square-payments/#test-mode',
				),
				'</a>'
			);
			$html .= '</p></div>';
		}

		$html .= sprintf(
			'<p><button type="button" class="button button-primary edd-square-connect-button" id="edd-square-connect">%s</button></p>',
			__( 'Connect with Square', 'easy-digital-downloads' )
		);

		$application_fee = new ApplicationFee();
		$message         = $application_fee->get_fee_message();
		if ( ! empty( $message ) ) {
			$html .= '<p>' . $message . '</p>';
		}

		if ( ! edd_is_test_mode() ) {
			$html .= '<p>';
			$html .= sprintf(
				/* translators: %s: Documentation link */
				__( 'Have questions about connecting with Square? See the %s.', 'easy-digital-downloads' ),
				sprintf(
					'<a href="%s" target="_blank">%s</a>',
					'https://easydigitaldownloads.com/docs/setting-up-square-payments/',
					__( 'documentation', 'easy-digital-downloads' )
				)
			);
			$html .= '</p>';
		}

		return $html;
	}

	/**
	 * Gets the connected class.
	 *
	 * @since 3.4.0
	 * @return string
	 */
	private function get_connected_class() {
		return $this->is_connected() ? '' : 'edd-hidden';
	}

	/**
	 * Checks if Square is connected.
	 *
	 * @since 3.4.0
	 * @return bool
	 */
	private function is_connected(): bool {
		return Connection::is_connected();
	}

	/**
	 * Enqueues admin scripts and styles.
	 *
	 * @since 3.4.0
	 * @param string $hook The current admin page hook.
	 * @return void
	 */
	public static function enqueue_admin_assets( $hook ) {
		// Only load on EDD settings page.
		if ( 'download_page_edd-settings' !== $hook ) {
			return;
		}

		// Only load on the EDD settings page.
		if ( 'gateways' !== filter_input( INPUT_GET, 'tab', FILTER_SANITIZE_SPECIAL_CHARS ) ) {
			return;
		}

		// Only load on the Square settings page.
		if ( 'square' !== filter_input( INPUT_GET, 'section', FILTER_SANITIZE_SPECIAL_CHARS ) ) {
			return;
		}

		wp_enqueue_script(
			'edd-square-admin',
			EDD_PLUGIN_URL . 'assets/js/square-admin.js',
			array( 'jquery' ),
			EDD_VERSION,
			true
		);

		wp_enqueue_style(
			'edd-square-admin',
			EDD_PLUGIN_URL . 'assets/css/square-admin.min.css',
			array(),
			EDD_VERSION
		);

		wp_localize_script(
			'edd-square-admin',
			'edd_square_admin',
			array(
				'nonce'          => wp_create_nonce( 'edd_square_admin_nonce' ),
				'ajax_url'       => admin_url( 'admin-ajax.php' ),
				'is_test_mode'   => edd_is_test_mode() ? 1 : 0,
				'square_enabled' => array_key_exists( 'square', edd_get_enabled_payment_gateways() ),
				'is_connected'   => Connection::is_connected(),
				'i18n'           => array(
					'testing_connection'    => __( 'Testing connection...', 'easy-digital-downloads' ),
					'connection_successful' => __( 'Square is connected and ready to accept payments.', 'easy-digital-downloads' ),
					'connection_failed'     => __( 'Connection failed.', 'easy-digital-downloads' ),
					'invalid_location_id'   => __( 'Invalid location ID format.', 'easy-digital-downloads' ),
					'loading'               => __( 'Loading...', 'easy-digital-downloads' ),
					'connecting'            => __( 'Connecting to Square...', 'easy-digital-downloads' ),
					'disconnecting'         => __( 'Disconnecting...', 'easy-digital-downloads' ),
					'reconnecting'          => __( 'Reconnecting...', 'easy-digital-downloads' ),
					'switching_mode'        => __( 'Switching mode...', 'easy-digital-downloads' ),
					'connection_error'      => __( 'Connection error. Please try again.', 'easy-digital-downloads' ),
					'disconnect_confirm'    => __( 'Are you sure you want to disconnect Square? This will disable Square payments.', 'easy-digital-downloads' ),
				),
			)
		);
	}

	/**
	 * Handles AJAX request to initiate Square OAuth connection.
	 *
	 * @since 3.4.0
	 * @return void
	 */
	public static function ajax_initiate_connection() {
		// Verify nonce.
		if ( ! wp_verify_nonce( $_POST['nonce'], 'edd_square_admin_nonce' ) ) {
			wp_send_json_error( array( 'message' => __( 'Security verification failed.', 'easy-digital-downloads' ) ) );
		}

		// Check user capabilities.
		if ( ! current_user_can( 'manage_shop_settings' ) ) {
			wp_send_json_error( array( 'message' => __( 'Insufficient permissions.', 'easy-digital-downloads' ) ) );
		}

		$mode = sanitize_text_field( $_POST['mode'] ?? 'test' );
		if ( ! in_array( $mode, array( 'test', 'live' ), true ) ) {
			wp_send_json_error( array( 'message' => __( 'Invalid connection mode.', 'easy-digital-downloads' ) ) );
		}

		try {
			$oauth_url = Connection::initiate_connection( $mode );

			if ( is_wp_error( $oauth_url ) ) {
				$error_message = $oauth_url->get_error_message();
				$error_code    = $oauth_url->get_error_code();

				// Provide more specific error messages.
				if ( 'proxy_error' === $error_code ) {
					$error_message = sprintf(
						/* translators: %s: Error message from proxy */
						__( 'Square connection failed: %s', 'easy-digital-downloads' ),
						$error_message
					);
				} elseif ( 'connection_failed' === $error_code ) {
					$error_message = __( 'Unable to connect to Square. Please check your internet connection and try again.', 'easy-digital-downloads' );
				}

				wp_send_json_error( array( 'message' => $error_message ) );
			}

			wp_send_json_success( array( 'oauth_url' => $oauth_url ) );
		} catch ( \Exception $e ) {
			wp_send_json_error(
				array(
					'message' => sprintf(
						/* translators: %s: Exception message */
						__( 'Connection error: %s', 'easy-digital-downloads' ),
						$e->getMessage()
					),
				)
			);
		}
	}

	/**
	 * Display admin notices.
	 *
	 * @since 3.4.0
	 * @return void
	 */
	public static function admin_notices() {
		if ( ! edd_is_admin_page() ) {
			return;
		}

		if ( ! Connection::is_connected() ) {
			return;
		}

		if ( ! Currency::is_currency_supported() ) {
			?>
			<div class="notice edd-notice notice-warning">
				<p>
					<?php
					printf(
						/* translators: %s: currency */
						__( '%1$sImportant Notice:%2$s The Square payment integration is not currently able to process payments in %3$s.', 'easy-digital-downloads' ),
						'<strong>',
						'</strong>',
						edd_get_currency()
					);
					?>
				</p>

				<p>
					<?php
					printf(
						/* translators: %s: currency */
						__( 'Square requires that payments be processed in %1$s, the currency of your merchant account for %2$s. Please %3$supdate the EDD currency%4$s to match.', 'easy-digital-downloads' ),
						Setting::get( 'currency' ),
						Setting::get( 'business_name' ),
						'<a href="' . admin_url( 'admin.php?page=edd-settings&tab=general&section=currency' ) . '">',
						'</a>'
					);
					?>
				</p>
			</div>
			<?php
		}
	}

	/**
	 * Get settings URL
	 *
	 * @since 3.4.0
	 * @return string Settings URL.
	 */
	public static function get_settings_url() {
		return edd_get_admin_url(
			array(
				'page'    => 'edd-settings',
				'tab'     => 'gateways',
				'section' => 'square',
			)
		);
	}

	/**
	 * Gets the connection status class.
	 *
	 * @since 3.4.0
	 * @param bool           $webhook The webhook status.
	 * @param ApplicationFee $application_fee The application fee status.
	 *
	 * @return string The connection status class.
	 */
	private function get_connection_status_class( $webhook, $application_fee ) {
		if ( true === $application_fee->has_application_fee() ) {
			return 'notice-warning';
		}

		if ( empty( $webhook ) ) {
			return 'notice-warning';
		}

		return 'notice-success';
	}
}
