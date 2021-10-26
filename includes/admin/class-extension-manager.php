<?php

namespace EDD\Admin;

class Extension_Manager {

	public function __construct() {
		add_action( 'wp_ajax_edd_activate_extension', array( $this, 'activate' ) );
		add_action( 'wp_ajax_edd_install_extension', array( $this, 'install' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'scripts' ) );
	}

	/**
	 * Enqueues the extension manager script.
	 *
	 * @since 2.11.x
	 * @return void
	 */
	public function scripts() {
		$minify = defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ? '' : '.min';
		\wp_register_script( 'edd-extension-manager', EDD_PLUGIN_URL . "assets/js/extension-manager{$minify}.js", array( 'jquery' ), EDD_VERSION, true );
		\wp_localize_script(
			'edd-extension-manager',
			'EDDExtensionManager',
			array(
				'activating'              => __( 'Activating', 'easy-digital-downloads' ),
				'installing'              => __( 'Installing', 'easy-digital-downloads' ),
				'ajaxurl'                 => edd_get_ajax_url(),
				'extension_manager_nonce' => wp_create_nonce( 'edd_extensionmanager' ),
			)
		);
	}

	/**
	 * Output the button to activate/insall a plugin/extension.
	 *
	 * @since 2.11.x
	 * @param array $button The array of parameters for the button.
	 * @return void
	 */
	public function button( $button ) {
		?>
		<p>
			<button
				class="<?php echo esc_attr( $button['button_class'] ); ?> edd-extension-manager"
				data-plugin="<?php echo wp_http_validate_url( $button['data-plugin'] ) ? esc_url( $button['data-plugin'] ) : esc_attr( $button['data-plugin'] ); ?>"
				data-action="<?php echo esc_attr( $button['data-action'] ); ?>"
			>
				<?php echo esc_html( $button['button_text'] ); ?>
			</button>
		</p>
		<?php
		\wp_print_scripts( 'edd-extension-manager' );
	}

	/**
	 * Activate extension.
	 *
	 * @since 2.11.x
	 */
	public function activate() {

		// Check for permissions.
		if ( ! check_ajax_referer( 'edd_extensionmanager', 'nonce', false ) || ! current_user_can( 'activate_plugins' ) ) {
			wp_send_json_error( esc_html__( 'Plugin activation is disabled for you on this site.', 'easy-digital-downloads' ) );
		}

		$plugin = filter_input( INPUT_POST, 'plugin', FILTER_SANITIZE_STRING );
		$type   = filter_input( INPUT_POST, 'type', FILTER_SANITIZE_STRING );

		if ( $plugin ) {

			if ( empty( $type ) ) {
				$type = 'extension';
			}

			$plugin   = sanitize_text_field( wp_unslash( $plugin ) );
			$activate = activate_plugins( $plugin );

			/**
			 * Fire after plugin activating via the EDD installer.
			 *
			 * @since 2.11.x
			 *
			 * @param string $plugin Path to the plugin file relative to the plugins directory.
			 */
			do_action( 'edd_plugin_activated', $plugin );

			if ( ! is_wp_error( $activate ) ) {
				if ( 'plugin' === $type ) {
					wp_send_json_success(
						array(
							'msg' => esc_html__( 'Plugin activated.', 'easy-digital-downloads' ),
						)
					);
				} else {
					wp_send_json_success(
						array(
							'msg' => esc_html__( 'Extension activated.', 'easy-digital-downloads' ),
						)
					);
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
		check_ajax_referer( 'edd_extensionmanager', 'nonce', true );

		$generic_error = esc_html__( 'There was an error while performing your request.', 'easy-digital-downloads' );
		$type          = filter_input( INPUT_POST, 'type', FILTER_SANITIZE_STRING );
		if ( ! $type ) {
			$type = 'extension';
		}

		// Check if new installations are allowed.
		if ( ! $this->can_install( $type ) ) {
			wp_send_json_error( $generic_error );
		}

		$error = 'plugin' === $type
			? esc_html__( 'Could not install the plugin. Please download and install it manually.', 'easy-digital-downloads' )
			: esc_html__( 'Could not install the extension. Please download it from edd.com and install it manually.', 'easy-digital-downloads' );

		$plugin = filter_input( INPUT_POST, 'plugin', FILTER_SANITIZE_STRING );
		if ( empty( $plugin ) ) {
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
		require_once EDD_PLUGIN_DIR . 'includes/libraries/class-plugin-silent-upgrader.php';
		require_once EDD_PLUGIN_DIR . 'includes/libraries/class-plugin-silent-upgrader-skin.php';
		require_once EDD_PLUGIN_DIR . 'includes/libraries/class-install-skin.php';

		// Do not allow WordPress to search/download translations, as this will break JS output.
		remove_action( 'upgrader_process_complete', array( 'Language_Pack_Upgrader', 'async_upgrade' ), 20 );

		// Create the plugin upgrader with our custom skin.
		$installer = new \EDD\Admin\PluginSilentUpgrader( new \EDD\Admin\Install_Skin() );

		// Error check.
		if ( ! method_exists( $installer, 'install' ) || empty( $plugin ) ) {
			wp_send_json_error( $error );
		}

		$installer->install( $plugin ); // phpcs:ignore

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

	/**
	 * Determine if the plugin/extension installations are allowed.
	 *
	 * @since 2.11.x
	 *
	 * @param string $type Should be `plugin` or `extension`.
	 *
	 * @return bool
	 */
	public function can_install( $type ) {

		if ( ! in_array( $type, array( 'plugin', 'extension' ), true ) ) {
			return false;
		}

		if ( ! current_user_can( 'install_plugins' ) ) {
			return false;
		}

		// // Determine whether file modifications are allowed.
		// if ( ! wp_is_file_mod_allowed( 'wpforms_can_install' ) ) {
		// 	return false;
		// }

		// All plugin checks are done.
		if ( 'plugin' === $type ) {
			return true;
		}

		// Addons require additional license checks.
		$pass_data = get_option( 'edd_pass_licenses' );

		// Allow addons installation if license is not expired, enabled and valid.
		return empty( $license['is_expired'] ) && empty( $license['is_disabled'] ) && empty( $license['is_invalid'] );
	}
}
