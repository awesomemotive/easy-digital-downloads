<?php

namespace EDD\Admin;

class Extension_Manager {

	public function __construct() {
		add_action( 'wp_ajax_edd_activate_extension', array( $this, 'activate' ) );
		add_action( 'wp_ajax_edd_install_extension', array( $this, 'install' ) );
	}

	/**
	 * Activate extension.
	 *
	 * @since 2.11.x
	 */
	public function activate() {

		// Run a security check.
		check_ajax_referer( 'edd-admin', 'nonce' );

		// Check for permissions.
		if ( ! current_user_can( 'activate_plugins' ) ) {
			wp_send_json_error( esc_html__( 'Plugin activation is disabled for you on this site.', 'easy-digital-downloads' ) );
		}

		$type = 'extension';

		if ( isset( $_POST['plugin'] ) ) {

			if ( ! empty( $_POST['type'] ) ) {
				$type = sanitize_key( $_POST['type'] );
			}

			$plugin   = sanitize_text_field( wp_unslash( $_POST['plugin'] ) );
			$activate = activate_plugins( $plugin );

			/**
			 * Fire after plugin activating via the EDD installer.
			 *
			 * @since 1.6.3.1
			 *
			 * @param string $plugin Path to the plugin file relative to the plugins directory.
			 */
			do_action( 'edd_plugin_activated', $plugin );

			if ( ! is_wp_error( $activate ) ) {
				if ( 'plugin' === $type ) {
					wp_send_json_success( esc_html__( 'Plugin activated.', 'easy-digital-downloads' ) );
				} else {
					wp_send_json_success( esc_html__( 'Addon activated.', 'easy-digital-downloads' ) );
				}
			}
		}

		if ( 'plugin' === $type ) {
			wp_send_json_error( esc_html__( 'Could not activate the plugin. Please activate it on the Plugins page.', 'easy-digital-downloads' ) );
		}

		wp_send_json_error( esc_html__( 'Could not activate the extension. Please activate it on the Plugins page.', 'easy-digital-downloads' ) );
	}

	/**
	 * Install extension.
	 *
	 * @since 2.11.x
	 */
	public function install() {

		// Run a security check.
		check_ajax_referer( 'edd-admin', 'nonce' );

		$generic_error = esc_html__( 'There was an error while performing your request.', 'easy-digital-downloads' );
		$type          = ! empty( $_POST['type'] ) ? sanitize_key( $_POST['type'] ) : 'extension';

		// Check if new installations are allowed.
		if ( ! edd_can_install( $type ) ) {
			wp_send_json_error( $generic_error );
		}

		$error = 'plugin' === $type
			? esc_html__( 'Could not install the plugin. Please download and install it manually.', 'easy-digital-downloads' )
			: esc_html__( 'Could not install the extension. Please download it from edd.com and install it manually.', 'easy-digital-downloads' );

		if ( empty( $_POST['plugin'] ) ) {
			wp_send_json_error( $error );
		}

		// Set the current screen to avoid undefined notices.
		set_current_screen( 'edd_page_edd-settings' );

		// Prepare variables.
		$url = esc_url_raw(
			add_query_arg(
				array(
					'post_type' => 'download',
					'page'      => 'edd-addons',
				),
				admin_url( 'edit.php' )
			)
		);

		ob_start();
		$creds = request_filesystem_credentials( $url, '', false, false, null );

		// Hide the filesystem credentials form.
		ob_end_clean();

		// Check for file system permissions.
		if ( ! $creds ) {
			wp_send_json_error( $error );
		}

		if ( ! WP_Filesystem( $creds ) ) {
			wp_send_json_error( $error );
		}

		/*
		 * We do not need any extra credentials if we have gotten this far, so let's install the plugin.
		 */

		require_once EDD_PLUGIN_DIR . 'includes/admin/class-install-skin.php';

		// Do not allow WordPress to search/download translations, as this will break JS output.
		remove_action( 'upgrader_process_complete', array( 'Language_Pack_Upgrader', 'async_upgrade' ), 20 );

		// Create the plugin upgrader with our custom skin.
		$installer = new \EDD\Admin\PluginSilentUpgrader( new \EDD\Admin\Install_Skin() );

		// Error check.
		if ( ! method_exists( $installer, 'install' ) || empty( $_POST['plugin'] ) ) {
			wp_send_json_error( $error );
		}

		$installer->install( $_POST['plugin'] ); // phpcs:ignore

		// Flush the cache and return the newly installed plugin basename.
		wp_cache_flush();

		$plugin_basename = $installer->plugin_info();

		if ( empty( $plugin_basename ) ) {
			wp_send_json_error( $error );
		}

		$result = array(
			'msg'          => $generic_error,
			'is_activated' => false,
			'basename'     => $plugin_basename,
		);

		// Check for permissions.
		if ( ! current_user_can( 'activate_plugins' ) ) {
			$result['msg'] = 'plugin' === $type ? esc_html__( 'Plugin installed.', 'easy-digital-downloads' ) : esc_html__( 'Extension installed.', 'easy-digital-downloads' );

			wp_send_json_success( $result );
		}

		// Activate the plugin silently.
		$activated = activate_plugin( $plugin_basename );

		if ( ! is_wp_error( $activated ) ) {

			/**
			 * Fire after plugin activating via the EDD installer.
			 *
			 * @since 2.11.x
			 *
			 * @param string $plugin_basename Path to the plugin file relative to the plugins directory.
			 */
			do_action( 'edd_plugin_activated', $plugin_basename );

			$result['is_activated'] = true;
			$result['msg']          = 'plugin' === $type ? esc_html__( 'Plugin installed & activated.', 'easy-digital-downloads' ) : esc_html__( 'Addon installed & activated.', 'easy-digital-downloads' );

			wp_send_json_success( $result );
		}

		// Fallback error just in case.
		wp_send_json_error( $result );
	}
}
