<?php
/**
 * Email Functions
 *
 * @package     EDD
 * @subpackage  Emails
 * @copyright   Copyright (c) 2018, Easy Digital Downloads, LLC
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       1.0
 */

// Exit if accessed directly
defined( 'ABSPATH' ) || exit;

/**
 * Retrieves the emails for which admin notifications are sent to (these can be
 * changed in the EDD Settings)
 *
 * @since 1.0
 * @param null|EDD\Orders\Order $order Optional. The order object. Default: null. Added in 3.2.3.
 * @return array $emails The emails to send admin notices to
 */
function edd_get_admin_notice_emails( $order = null ) {
	$emails = edd_get_option( 'admin_notice_emails', false );
	$emails = strlen( trim( $emails ) ) > 0 ? $emails : get_bloginfo( 'admin_email' );
	$emails = array_map( 'trim', explode( "\n", $emails ) );

	/**
	 * Filters the emails for which admin notifications are sent.
	 *
	 * @param array $emails The emails to send admin notices to.
	 * @param null|EDD\Orders\Order $order Optional. The order object. Default: null. Added in 3.2.3.
	 */
	return apply_filters( 'edd_admin_notice_emails', $emails, $order );
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
 * Handle installation and connection for Recapture via ajax
 *
 * @since 2.10.2
 */
function edd_recapture_remote_install_handler () {

	if ( ! current_user_can( 'manage_shop_settings' ) || ! current_user_can( 'install_plugins' ) ) {
		wp_send_json_error( array(
			'error' => __( 'You do not have permission to do this.', 'easy-digital-downloads' )
		) );
	}

	include_once ABSPATH . 'wp-admin/includes/plugin-install.php';
	include_once ABSPATH . 'wp-admin/includes/file.php';
	include_once ABSPATH . 'wp-admin/includes/class-wp-upgrader.php';

	$plugins = get_plugins();

	if( ! array_key_exists( 'recapture-for-edd/recapture.php', $plugins ) ) {

		/*
		* Use the WordPress Plugins API to get the plugin download link.
		*/
		$api = plugins_api( 'plugin_information', array(
			'slug' => 'recapture-for-edd',
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

		$activated = activate_plugin( 'recapture-for-edd/recapture.php' );

	}

	/*
	* Final check to see if Recapture is available.
	*/
	if ( is_wp_error( $activated ) ) {
		wp_send_json_error( array(
			'error' => __( 'Something went wrong. Recapture for EDD was not installed correctly.', 'easy-digital-downloads' )
		) );
	}

	wp_send_json_success();
}
add_action( 'wp_ajax_edd_recapture_remote_install', 'edd_recapture_remote_install_handler' );

/**
 * Maybe adds a notice to abandoned payments if Recapture isn't installed.
 *
 * @since 2.10.2
 *
 * @param int $payment_id The ID of the abandoned payment, for which a Recapture notice is being thrown.
 */
function maybe_add_recapture_notice_to_abandoned_payment( $payment_id ) {

	if ( ! class_exists( 'Recapture' )
		&& 'abandoned' === edd_get_payment_status( $payment_id )
		&& ! get_user_meta( get_current_user_id(), '_edd_try_recapture_dismissed', true )
	) {
		?>
		<div class="notice notice-warning recapture-notice">
			<p>
				<?php
				echo wp_kses_post(
					sprintf(
						/* Translators: %1$s - <strong> tag, %2$s - </strong> tag, %3$s - <a> tag, %4$s - </a> tag */
						__( '%1$sRecover abandoned purchases like this one.%2$s %3$sTry Recapture for free%4$s.', 'easy-digital-downloads' ),
						'<strong>',
						'</strong>',
						'<a href="https://recapture.io/abandoned-carts-easy-digital-downloads" rel="noopener" target="_blank">',
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
						wp_nonce_url(
							add_query_arg(
								array(
									'edd_action' => 'dismiss_notices',
									'edd_notice' => 'try_recapture',
								)
							),
							'edd_notice_nonce'
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
add_action( 'edd_view_order_details_before', 'maybe_add_recapture_notice_to_abandoned_payment' );
