<?php
/**
 * Email Functions
 *
 * @package     EDD
 * @subpackage  Emails
 * @copyright   Copyright (c) 2015, Pippin Williamson
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       1.0
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Email the download link(s) and payment confirmation to the buyer in a
 * customizable Purchase Receipt
 *
 * @since 1.0
 * @since 2.8 - Add parameters for EDD_Payment and EDD_Customer object.
 *
 * @param int          $payment_id   Payment ID
 * @param bool         $admin_notice Whether to send the admin email notification or not (default: true)
 * @param EDD_Payment  $payment      Payment object for payment ID.
 * @param EDD_Customer $customer     Customer object for associated payment.
 * @return void
 */
function edd_email_purchase_receipt( $payment_id, $admin_notice = true, $to_email = '', $payment = null, $customer = null ) {
	if ( is_null( $payment ) ) {
		$payment = edd_get_payment( $payment_id );
	}

	$payment_data = $payment->get_meta( '_edd_payment_meta', true );

	$from_name    = edd_get_option( 'from_name', wp_specialchars_decode( get_bloginfo( 'name' ), ENT_QUOTES ) );
	$from_name    = apply_filters( 'edd_purchase_from_name', $from_name, $payment_id, $payment_data );

	$from_email   = edd_get_option( 'from_email', get_bloginfo( 'admin_email' ) );
	$from_email   = apply_filters( 'edd_purchase_from_address', $from_email, $payment_id, $payment_data );

	if ( empty( $to_email ) ) {
		$to_email = $payment->email;
	}

	$subject      = edd_get_option( 'purchase_subject', __( 'Purchase Receipt', 'easy-digital-downloads' ) );
	$subject      = apply_filters( 'edd_purchase_subject', wp_strip_all_tags( $subject ), $payment_id );
	$subject      = wp_specialchars_decode( edd_do_email_tags( $subject, $payment_id ) );

	$heading      = edd_get_option( 'purchase_heading', __( 'Purchase Receipt', 'easy-digital-downloads' ) );
	$heading      = apply_filters( 'edd_purchase_heading', $heading, $payment_id, $payment_data );
	$heading      = edd_do_email_tags( $heading, $payment_id );

	$attachments  = apply_filters( 'edd_receipt_attachments', array(), $payment_id, $payment_data );

	$message      = edd_do_email_tags( edd_get_email_body_content( $payment_id, $payment_data ), $payment_id );

	$emails = EDD()->emails;

	$emails->__set( 'from_name', $from_name );
	$emails->__set( 'from_email', $from_email );
	$emails->__set( 'heading', $heading );

	$headers = apply_filters( 'edd_receipt_headers', $emails->get_headers(), $payment_id, $payment_data );
	$emails->__set( 'headers', $headers );

	$emails->send( $to_email, $subject, $message, $attachments );

	if ( $admin_notice && ! edd_admin_notices_disabled( $payment_id ) ) {
		do_action( 'edd_admin_sale_notice', $payment_id, $payment_data );
	}
}

/**
 * Email the download link(s) and payment confirmation to the admin accounts for testing.
 *
 * @since 1.5
 * @return void
 */
function edd_email_test_purchase_receipt() {

	$from_name   = edd_get_option( 'from_name', wp_specialchars_decode( get_bloginfo( 'name' ), ENT_QUOTES ) );
	$from_name   = apply_filters( 'edd_purchase_from_name', $from_name, 0, array() );

	$from_email  = edd_get_option( 'from_email', get_bloginfo( 'admin_email' ) );
	$from_email  = apply_filters( 'edd_test_purchase_from_address', $from_email, 0, array() );

	$subject     = edd_get_option( 'purchase_subject', __( 'Purchase Receipt', 'easy-digital-downloads' ) );
	$subject     = apply_filters( 'edd_purchase_subject', wp_strip_all_tags( $subject ), 0 );
	$subject     = wp_specialchars_decode( edd_do_email_tags( $subject, 0 ) );

	$heading     = edd_get_option( 'purchase_heading', __( 'Purchase Receipt', 'easy-digital-downloads' ) );
	$heading     = apply_filters( 'edd_purchase_heading', $heading, 0, array() );

	$attachments = apply_filters( 'edd_receipt_attachments', array(), 0, array() );

	$message     = edd_do_email_tags( edd_get_email_body_content( 0, array() ), 0 );

	$emails = EDD()->emails;
	$emails->__set( 'from_name' , $from_name );
	$emails->__set( 'from_email', $from_email );
	$emails->__set( 'heading'   , $heading );

	$headers = apply_filters( 'edd_receipt_headers', $emails->get_headers(), 0, array() );
	$emails->__set( 'headers', $headers );

	$emails->send( edd_get_admin_notice_emails(), $subject, $message, $attachments );

}

/**
 * Sends the Admin Sale Notification Email
 *
 * @since 1.4.2
 * @param int $payment_id Payment ID (default: 0)
 * @param array $payment_data Payment Meta and Data
 * @return void
 */
function edd_admin_email_notice( $payment_id = 0, $payment_data = array() ) {

	$payment_id = absint( $payment_id );

	if( empty( $payment_id ) ) {
		return;
	}

	if( ! edd_get_payment_by( 'id', $payment_id ) ) {
		return;
	}

	$from_name   = edd_get_option( 'from_name', wp_specialchars_decode( get_bloginfo( 'name' ), ENT_QUOTES ) );
	$from_name   = apply_filters( 'edd_purchase_from_name', $from_name, $payment_id, $payment_data );

	$from_email  = edd_get_option( 'from_email', get_bloginfo( 'admin_email' ) );
	$from_email  = apply_filters( 'edd_admin_sale_from_address', $from_email, $payment_id, $payment_data );

	$subject     = edd_get_option( 'sale_notification_subject', sprintf( __( 'New download purchase - Order #%1$s', 'easy-digital-downloads' ), $payment_id ) );
	$subject     = apply_filters( 'edd_admin_sale_notification_subject', wp_strip_all_tags( $subject ), $payment_id );
	$subject     = wp_specialchars_decode( edd_do_email_tags( $subject, $payment_id ) );

	$heading     = edd_get_option( 'sale_notification_heading', __( 'New Sale!', 'easy-digital-downloads' ) );
	$heading     = apply_filters( 'edd_admin_sale_notification_heading', $heading, $payment_id, $payment_data );
	$heading     = edd_do_email_tags( $heading, $payment_id );

	$attachments = apply_filters( 'edd_admin_sale_notification_attachments', array(), $payment_id, $payment_data );

	$message     = edd_get_sale_notification_body_content( $payment_id, $payment_data );

	$emails = EDD()->emails;

	$emails->__set( 'from_name', $from_name );
	$emails->__set( 'from_email', $from_email );
	$emails->__set( 'heading', $heading );

	$headers = apply_filters( 'edd_admin_sale_notification_headers', $emails->get_headers(), $payment_id, $payment_data );
	$emails->__set( 'headers', $headers );

	$emails->send( edd_get_admin_notice_emails(), $subject, $message, $attachments );

}
add_action( 'edd_admin_sale_notice', 'edd_admin_email_notice', 10, 2 );

/**
 * Retrieves the emails for which admin notifications are sent to (these can be
 * changed in the EDD Settings)
 *
 * @since 1.0
 * @return mixed
 */
function edd_get_admin_notice_emails() {
	$emails = edd_get_option( 'admin_notice_emails', false );
	$emails = strlen( trim( $emails ) ) > 0 ? $emails : get_bloginfo( 'admin_email' );
	$emails = array_map( 'trim', explode( "\n", $emails ) );

	return apply_filters( 'edd_admin_notice_emails', $emails );
}

/**
 * Checks whether admin sale notices are disabled
 *
 * @since 1.5.2
 *
 * @param int $payment_id
 * @return mixed
 */
function edd_admin_notices_disabled( $payment_id = 0 ) {
	$ret = edd_get_option( 'disable_admin_notices', false );
	return (bool) apply_filters( 'edd_admin_notices_disabled', $ret, $payment_id );
}

/**
 * Get sale notification email text
 *
 * Returns the stored email text if available, the standard email text if not
 *
 * @since 1.7
 * @author Daniel J Griffiths
 * @return string $message
 */
function edd_get_default_sale_notification_email() {
	$default_email_body = __( 'Hello', 'easy-digital-downloads' ) . "\n\n" . sprintf( __( 'A %s purchase has been made', 'easy-digital-downloads' ), edd_get_label_plural() ) . ".\n\n";
	$default_email_body .= sprintf( __( '%s sold:', 'easy-digital-downloads' ), edd_get_label_plural() ) . "\n\n";
	$default_email_body .= '{download_list}' . "\n\n";
	$default_email_body .= __( 'Purchased by: ', 'easy-digital-downloads' ) . ' {name}' . "\n";
	$default_email_body .= __( 'Amount: ', 'easy-digital-downloads' ) . ' {price}' . "\n";
	$default_email_body .= __( 'Payment Method: ', 'easy-digital-downloads' ) . ' {payment_method}' . "\n\n";
	$default_email_body .= __( 'Thank you', 'easy-digital-downloads' );

	$message = edd_get_option( 'sale_notification', false );
	$message = ! empty( $message ) ? $message : $default_email_body;

	return $message;
}

/**
 * Get various correctly formatted names used in emails
 *
 * @since 1.9
 * @param $user_info
 * @param $payment   EDD_Payment for getting the names
 *
 * @return array $email_names
 */
function edd_get_email_names( $user_info, $payment = false ) {
	$email_names = array();
	$email_names['fullname'] = '';

	if ( $payment instanceof EDD_Payment ) {

		if ( $payment->user_id > 0 ) {

			$user_data = get_userdata( $payment->user_id );
			$email_names['name']      = $payment->first_name;
			$email_names['fullname']  = trim( $payment->first_name . ' ' . $payment->last_name );
			$email_names['username']  = $user_data->user_login;

		} elseif ( ! empty( $payment->first_name ) ) {

			$email_names['name']     = $payment->first_name;
			$email_names['fullname'] = trim( $payment->first_name . ' ' . $payment->last_name );
			$email_names['username'] = $payment->first_name;

		} else {

			$email_names['name']     = $payment->email;
			$email_names['username'] = $payment->email;

		}

	} else {

		if ( is_serialized( $user_info ) ) {

			preg_match( '/[oO]\s*:\s*\d+\s*:\s*"\s*(?!(?i)(stdClass))/', $user_info, $matches );
			if ( ! empty( $matches ) ) {
				return array(
					'name'     => '',
					'fullname' => '',
					'username' => '',
				);
			} else {
				$user_info = maybe_unserialize( $user_info );
			}

		}

		if ( isset( $user_info['id'] ) && $user_info['id'] > 0 && isset( $user_info['first_name'] ) ) {
			$user_data = get_userdata( $user_info['id'] );
			$email_names['name']      = $user_info['first_name'];
			$email_names['fullname']  = $user_info['first_name'] . ' ' . $user_info['last_name'];
			$email_names['username']  = $user_data->user_login;
		} elseif ( isset( $user_info['first_name'] ) ) {
			$email_names['name']     = $user_info['first_name'];
			$email_names['fullname'] = $user_info['first_name'] . ' ' . $user_info['last_name'];
			$email_names['username'] = $user_info['first_name'];
		} else {
			$email_names['name']     = $user_info['email'];
			$email_names['username'] = $user_info['email'];
		}

	}

	return $email_names;
}

/**
 * Handle installation and connection for SendWP via ajax
 *
 * @since 2.9.15
 */
function edd_sendwp_remote_install_handler () {

	if ( ! current_user_can( 'manage_shop_settings' ) ) {
		wp_send_json_error( array(
			'error' => __( 'You do not have permission to do this.', 'easy-digital-downloads' )
		) );
	}

	include_once ABSPATH . 'wp-admin/includes/plugin-install.php';
	include_once ABSPATH . 'wp-admin/includes/file.php';
	include_once ABSPATH . 'wp-admin/includes/class-wp-upgrader.php';

	$plugins = get_plugins();

	if( ! array_key_exists( 'sendwp/sendwp.php', $plugins ) ) {

		/*
		* Use the WordPress Plugins API to get the plugin download link.
		*/
		$api = plugins_api( 'plugin_information', array(
			'slug' => 'sendwp',
		) );

		if ( is_wp_error( $api ) ) {
			wp_send_json_error( array(
				'error' => $api->get_error_message(),
				'debug' => $api
			) );
		}

		/*
		* Use the AJAX Upgrader skin to quietly install the plugin.
		*/
		$upgrader = new Plugin_Upgrader( new WP_Ajax_Upgrader_Skin() );
		$install = $upgrader->install( $api->download_link );
		if ( is_wp_error( $install ) ) {
			wp_send_json_error( array(
				'error' => $install->get_error_message(),
				'debug' => $api
			) );
		}

		$activated = activate_plugin( $upgrader->plugin_info() );

	} else {

		$activated = activate_plugin( 'sendwp/sendwp.php' );

	}

	/*
	* Final check to see if SendWP is available.
	*/
	if( ! function_exists('sendwp_get_server_url') ) {
		wp_send_json_error( array(
			'error' => __( 'Something went wrong. SendWP was not installed correctly.', 'easy-digital-downloads' )
		) );
	}

	wp_send_json_success( array(
		'partner_id' => 81,
		'register_url' => sendwp_get_server_url() . '_/signup',
		'client_name' => sendwp_get_client_name(),
		'client_secret' => sendwp_get_client_secret(),
		'client_redirect' => admin_url( '/edit.php?post_type=download&page=edd-settings&tab=emails&edd-message=sendwp-connected' ),
	) );
}
add_action( 'wp_ajax_edd_sendwp_remote_install', 'edd_sendwp_remote_install_handler' );

/**
 * Handle deactivation of SendWP via ajax
 *
 * @since 2.9.15
 */
function edd_sendwp_disconnect () {

	if ( ! current_user_can( 'manage_shop_settings' ) ) {
		wp_send_json_error( array(
			'error' => __( 'You do not have permission to do this.', 'easy-digital-downloads' )
		) );
	}

	sendwp_disconnect_client();

	deactivate_plugins( 'sendwp/sendwp.php' );

	wp_send_json_success();
}
add_action( 'wp_ajax_edd_sendwp_disconnect', 'edd_sendwp_disconnect' );

/**
 * Handle installation and activation for Jilt via AJAX
 *
 * @since n.n.n
 */
function edd_jilt_remote_install_handler() {

	if ( ! current_user_can( 'manage_shop_settings' ) || ! current_user_can( 'install_plugins' ) ) {
		wp_send_json_error(
			array(
				'error' => __( 'You do not have permission to do this.', 'easy-digital-downloads' ),
			)
		);
	}

	include_once ABSPATH . 'wp-admin/includes/plugin-install.php';
	include_once ABSPATH . 'wp-admin/includes/file.php';
	include_once ABSPATH . 'wp-admin/includes/class-wp-upgrader.php';

	$plugins = get_plugins();

	if ( ! array_key_exists( 'jilt-for-edd/jilt-for-edd.php', $plugins ) ) {
		/*
		* Use the WordPress Plugins API to get the plugin download link.
		*/
		$api = plugins_api(
			'plugin_information',
			array(
				'slug' => 'jilt-for-edd',
			)
		);

		if ( is_wp_error( $api ) ) {
			wp_send_json_error(
				array(
					'error' => $api->get_error_message(),
					'debug' => $api,
				)
			);
		}

		/*
		* Use the AJAX Upgrader skin to quietly install the plugin.
		*/
		$upgrader = new Plugin_Upgrader( new WP_Ajax_Upgrader_Skin() );
		$install  = $upgrader->install( $api->download_link );
		if ( is_wp_error( $install ) ) {
			wp_send_json_error(
				array(
					'error' => $install->get_error_message(),
					'debug' => $api,
				)
			);
		}

		activate_plugin( $upgrader->plugin_info() );

	} else {

		activate_plugin( 'jilt-for-edd/jilt-for-edd.php' );
	}

	/*
	* Final check to see if Jilt is available.
	*/
	if ( ! class_exists( 'EDD_Jilt_Loader' ) ) {
		wp_send_json_error(
			array(
				'error' => __( 'Something went wrong. Jilt was not installed correctly.', 'easy-digital-downloads' ),
			)
		);
	}

	wp_send_json_success();
}
add_action( 'wp_ajax_edd_jilt_remote_install', 'edd_jilt_remote_install_handler' );

/**
 * Handle connection for Jilt via AJAX
 *
 * @since n.n.n
 */
function edd_jilt_connect_handler() {

	if ( ! current_user_can( 'manage_shop_settings' ) ) {
		wp_send_json_error(
			array(
				'error' => __( 'You do not have permission to do this.', 'easy-digital-downloads' ),
			)
		);
	}

	if ( ! is_callable( 'edd_jilt' ) ) {
		wp_send_json_error(
			array(
				'error' => __( 'Something went wrong. Jilt was not installed correctly.', 'easy-digital-downloads' ),
			)
		);
	}

	wp_send_json_success( array( 'connect_url' => edd_jilt()->get_connect_url() ) );
}
add_action( 'wp_ajax_edd_jilt_connect', 'edd_jilt_connect_handler' );

/**
 * Handle disconnection and deactivation for Jilt via AJAX
 *
 * @since n.n.n
 */
function edd_jilt_disconnect_handler() {

	if ( ! current_user_can( 'manage_shop_settings' ) ) {
		wp_send_json_error(
			array(
				'error' => __( 'You do not have permission to do this.', 'easy-digital-downloads' ),
			)
		);
	}

	if ( is_callable( 'edd_jilt' ) ) {

		edd_jilt()->get_integration()->unlink_shop();
		edd_jilt()->get_integration()->revoke_authorization();
		edd_jilt()->get_integration()->clear_connection_data();
	}

	deactivate_plugins( 'jilt-for-edd/jilt-for-edd.php' );

	wp_send_json_success();
}
add_action( 'wp_ajax_edd_jilt_disconnect', 'edd_jilt_disconnect_handler' );

/**
 * Maybe adds a notice to abandoned payments if Jilt isn't installed.
 *
 * @since n.n.n
 *
 * @param int $payment_id The ID of the abandoned payment, for which a jilt notice is being thrown.
 */
function maybe_add_jilt_notice_to_abandoned_payment( $payment_id ) {

	if ( ! is_callable( 'edd_jilt' )
		&& ! is_plugin_active( 'recapture-for-edd/recapture.php' )
		&& 'abandoned' === edd_get_payment_status( $payment_id )
		&& ! get_user_meta( get_current_user_id(), '_edd_try_jilt_dismissed', true )
	) {
		?>
		<div class="notice notice-warning jilt-notice">
			<p>
				<?php
				echo wp_kses_post(
					sprintf(
						/* Translators: %1$s - <strong> tag, %2$s - </strong> tag, %3$s - <a> tag, %4$s - </a> tag */
						__( '%1$sRecover abandoned purchases like this one.%2$s %3$sTry Jilt for free%4$s.', 'easy-digital-downloads' ),
						'<strong>',
						'</strong>',
						'<a href="https://easydigitaldownloads.com/downloads/jilt" target="_blank">',
						'</a>'
					)
				);
				?>
			</p>
			<?php
			echo wp_kses_post(
				sprintf(
					/* Translators: %1$s - Opening anchor tag, %2$s - The url to dismiss the ajax notice, %3$s - Complete the opening of the anchor tag, %4$s - Open span tag, %4$s - Close span tag */
					__( '%1$s %2$s %3$s %4$s Dismiss this notice. %5$s', 'easy-digital-downloads' ),
					'<a href="',
					esc_url(
						add_query_arg(
							array(
								'edd_action' => 'dismiss_notices',
								'edd_notice' => 'try_jilt',
							)
						)
					),
					'" type="button" class="notice-dismiss">',
					'<span class="screen-reader-text">',
					'</span>
					</a>'
				)
			);
			?>
		</div>
		<?php
	}
}
add_action( 'edd_view_order_details_before', 'maybe_add_jilt_notice_to_abandoned_payment' );
