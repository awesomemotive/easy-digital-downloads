<?php

namespace EDD\Lite\Admin\PassHandler;

use \EDD\EventManagement\SubscriberInterface;

/**
 * Easy Digital Downloads Connect.
 *
 * EDD Connect is our service that makes it easy for non-techy users to
 * upgrade to EDD (Pro) without having to manually install EDD (Pro) plugin.
 *
 * @since 3.1.1
 */

use \EDD\Admin\Pass_Manager;

class Connect implements SubscriberInterface {

	/**
	 * The EDD Pass Manager class.
	 *
	 * @var \EDD\Admin\Pass_Manager
	 */
	protected $pass_manager;

	/**
	 * The pass handler.
	 *
	 * @var \EDD\Admin\PassHandler\Handler;
	 */
	protected $handler;

	public function __construct( \EDD\Admin\PassHandler\Handler $handler ) {
		$this->handler      = $handler;
		$this->pass_manager = new Pass_Manager();
	}

	public static function get_subscribed_events() {
		return array(
			'wp_ajax_nopriv_easydigitaldownloads_connect_process' => 'process',
		);
	}

	/**
	 * Process EDD Connect.
	 *
	 * @since 3.1.1
	 */
	public function process() {

		$error = esc_html__( 'There was an error while installing an upgrade. Please download the plugin from easydigitaldownloads.com and install it manually.', 'easy-digital-downloads' );

		// Verify params present (oth & download link).
		$post_oth = ! empty( $_REQUEST['oth'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['oth'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification
		$post_url = ! empty( $_REQUEST['file'] ) ? esc_url_raw( wp_unslash( $_REQUEST['file'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification

		if ( empty( $post_oth ) || empty( $post_url ) ) {
			wp_send_json_error( $error );
		}

		// Verify oth.
		$oth = get_option( 'edd_connect_token' );

		if ( empty( $post_oth ) || hash_hmac( 'sha512', $oth, wp_salt() ) !== $post_oth ) {
			wp_send_json_error( $error );
		}

		// Delete so cannot replay.
		delete_option( 'edd_connect_token' );

		// Check license key.
		$license_key = ! empty( $_REQUEST['key'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['key'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification
		if ( empty( $license_key ) ) {
			wp_send_json_error( __( 'No key provided.', 'easy-digital-downloads' ) );
		}

		if ( ! empty( $_REQUEST['license'] ) ) {
			update_site_option( 'edd_pro_license_key', $license_key );
			$license_data = (object) $_REQUEST['license'];
			$this->handler->update_pro_license( $license_data );
			$this->pass_manager->maybe_set_pass_flag( $license_key, $license_data );
		}

		if ( ! get_option( 'edd_pro_activation_date', false ) ) {
			update_option( 'edd_pro_activation_date', time() );
		}

		// If pro is already active, return a success message.
		if ( edd_is_pro() ) {
			wp_send_json_success( esc_html__( 'Plugin installed & activated.', 'easy-digital-downloads' ) );
		}

		// Set the current screen to avoid undefined notices.
		set_current_screen( 'download_page_edd-settings' );

		// Verify pro not installed.
		$active = activate_plugin( 'easy-digital-downloads-pro/easy-digital-downloads.php', '', false, true );
		if ( ! is_wp_error( $active ) ) {
			wp_send_json_success( esc_html__( 'Plugin installed & activated.', 'easy-digital-downloads' ) );
		}

		// Prepare variables.
		$url   = esc_url_raw(
			edd_get_admin_url(
				array( 'page' => 'edd-settings' )
			)
		);
		$creds = request_filesystem_credentials( $url, '', false, false, null );

		// Check for file system permissions.
		if ( false === $creds || ! WP_Filesystem( $creds ) ) {
			wp_send_json_error(
				esc_html__( 'There was an error while installing an upgrade. Please check file system permissions and try again. Also, you can download the plugin from easydigitaldownloads.com and install it manually.', 'easy-digital-downloads' )
			);
		}

		/*
		 * We do not need any extra credentials if we have gotten this far, so let's install the plugin.
		 */

		// Do not allow WordPress to search/download translations, as this will break JS output.
		remove_action( 'upgrader_process_complete', array( 'Language_Pack_Upgrader', 'async_upgrade' ), 20 );

		// Create the plugin upgrader with our custom skin.
		$installer = new \EDD\Admin\Installers\PluginSilentUpgrader( new \EDD\Admin\Installers\Install_Skin() );

		// Error check.
		if ( ! method_exists( $installer, 'install' ) ) {
			wp_send_json_error( $error );
		}

		$installer->install( $post_url ); // phpcs:ignore

		// Flush the cache and return the newly installed plugin basename.
		wp_cache_flush();

		$plugin_basename = $installer->plugin_info();

		if ( $plugin_basename ) {

			// Activate the plugin silently.
			$activated = activate_plugin( $plugin_basename, '', false, true );

			if ( ! is_wp_error( $activated ) ) {
				wp_send_json_success( esc_html__( 'Plugin installed & activated.', 'easy-digital-downloads' ) );
			}

			$error = esc_html__( 'Easy Digital Downloads (Pro) was installed, but needs to be activated on the Plugins page inside your WordPress admin.', 'easy-digital-downloads' );
		}

		wp_send_json_error( $error );
	}
}
