<?php

namespace EDD\Admin\Extensions;

use EDD\Admin\Pass_Manager;
use EDD\Admin\Extensions\Card;
use EDD\EventManagement\SubscriberInterface;

class Extension_Manager implements SubscriberInterface {
	use \EDD\Admin\Extensions\Traits\Buttons;

	/**
	 * All of the installed plugins on the site.
	 *
	 * @since 2.11.4
	 * @var array
	 */
	public $all_plugins;

	/**
	 * The minimum pass ID required to install the extension.
	 *
	 * @since 3.1.1
	 */
	private $required_pass_id;

	/**
	 * Pass Manager class
	 *
	 * @var Pass_Manager
	 */
	protected $pass_manager;

	public function __construct( $required_pass_id = null ) {
		if ( $required_pass_id ) {
			$this->required_pass_id = $required_pass_id;
		}
		$this->pass_manager = new Pass_Manager();
	}

	/**
	 * Gets the subscribed events for this class.
	 *
	 * @since 3.1.1
	 * @return array
	 */
	public static function get_subscribed_events() {
		return array(
			'wp_ajax_edd_activate_extension'    => 'activate',
			'wp_ajax_edd_install_extension'     => 'install',
			'wp_ajax_edd_deactivate_extension'  => 'deactivate',
			'admin_enqueue_scripts'             => 'register_assets',
			'edd_after_ajax_activate_extension' => 'post_extension_activation',
		);
	}

	/**
	 * Registers the extension manager script and style.
	 *
	 * @since 2.11.4
	 * @return void
	 */
	public function register_assets() {
		if ( wp_script_is( 'edd-extension-manager', 'registered' ) ) {
			return;
		}
		$minify = defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ? '' : '.min';
		wp_register_style( 'edd-extension-manager', EDD_PLUGIN_URL . 'assets/css/edd-admin-extension-manager.min.css', array(), EDD_VERSION );
		wp_register_script( 'edd-extension-manager', EDD_PLUGIN_URL . 'assets/js/edd-admin-extension-manager.js', array( 'jquery' ), EDD_VERSION, true );
		wp_localize_script(
			'edd-extension-manager',
			'EDDExtensionManager',
			array(
				'activating'               => __( 'Activating', 'easy-digital-downloads' ),
				'installing'               => __( 'Installing', 'easy-digital-downloads' ),
				'plugin_install_failed'    => __( 'Could not install the plugin. Please download and install it manually via Plugins > Add New.', 'easy-digital-downloads' ),
				'extension_install_failed' => sprintf(
					/* translators: 1. opening anchor tag, do not translate; 2. closing anchor tag, do not translate */
					__( 'Could not install the extension. Please %1$sdownload it from your account%2$s and install it manually.', 'easy-digital-downloads' ),
					'<a href="https://easydigitaldownloads.com/your-account/" target="_blank" rel="noopener noreferrer">',
					'</a>'
				),
				'extension_manager_nonce'  => wp_create_nonce( 'edd_extensionmanager' ),
				'results'                  => __( 'extensions found', 'easy-digital-downloads' ),
				'deactivating'             => __( 'Deactivating', 'easy-digital-downloads' ),
				'debug'                    => edd_doing_script_debug(),
				'filter'                   => filter_input( INPUT_GET, 'filter', FILTER_SANITIZE_SPECIAL_CHARS ),
			)
		);
	}

	/**
	 * Enqueues the extension manager script/style.
	 *
	 * @since 2.11.4
	 * @return void
	 */
	public function enqueue() {
		wp_enqueue_style( 'edd-extension-manager' );
		wp_enqueue_script( 'edd-extension-manager' );
	}

	/**
	 * Outputs a standard extension card.
	 *
	 * @since 2.11.4
	 * @param ProductData $product             The product data object.
	 * @param array        $inactive_parameters The array of information to build the button for an inactive/not installed plugin.
	 * @param array        $active_parameters   The array of information needed to build the link to configure an active plugin.
	 * @param array        $configuration       The optional array of data to override the product data retrieved from the API.
	 * @return void
	 */
	public function do_extension_card( ProductData $product, $inactive_parameters, $active_parameters, $configuration = array() ) {
		$this->enqueue();

		$parameters = array(
			'inactive_parameters' => $inactive_parameters,
			'active_parameters'   => $active_parameters,
			'required_pass_id'    => ! empty( $product->pass_id ) ? $product->pass_id : $this->required_pass_id,
			'is_plugin_installed' => false,
			'is_plugin_active'    => false,
			'version'             => ! empty( $product->version ) ? $product->version : false,
		);
		if ( ! empty( $product->basename ) ) {
			$parameters['is_plugin_installed'] = $this->is_plugin_installed( $product->basename );
			$parameters['is_plugin_active']    = $this->is_plugin_active( $product->basename );
			$parameters['version']             = $this->get_plugin_version( $product->basename );
		}
		$card_class = edd_get_namespace( 'Admin\\Extensions\\Card' );
		$card       = new $card_class(
			$product,
			$parameters
		);
	}

	/**
	 * Installs and maybe activates a plugin or extension.
	 *
	 * @since 2.11.4
	 */
	public function install() {
		// Run a security check.
		check_ajax_referer( 'edd_extensionmanager', 'nonce', true );

		$generic_error = esc_html__( 'There was an error while performing your request.', 'easy-digital-downloads' );
		$type          = ! empty( $_POST['type'] ) ? sanitize_text_field( $_POST['type'] ) : '';
		$required_pass = ! empty( $_POST['pass'] ) ? sanitize_text_field( $_POST['pass'] ) : '';
		$plugin        = ! empty( $_POST['plugin'] ) ? esc_url( $_POST['plugin'] ) : '';
		$result        = array(
			'message'      => $generic_error,
			'is_activated' => false,
			'type'         => $type,
		);
		if ( ! $type ) {
			wp_send_json_error( $result );
		}

		// Check if new installations are allowed.
		if ( ! $this->can_install( $type, $required_pass ) ) {
			$result['message'] = __( 'Plugin installation is not available for you on this site.', 'easy-digital-downloads' );
			if ( 'extension' === $type ) {
				$result['highest_pass'] = $this->pass_manager->highest_pass_id;
			}
			wp_send_json_error( $result );
		}

		$result['message'] = 'plugin' === $type
			? sprintf(
				// translators: 1. opening anchor tag, do not translate; 2. closing anchor tag, do not translate.
				__( 'Could not install the plugin. Please %1$sdownload%2$s and install it manually via Plugins > Add New.', 'easy-digital-downloads' ),
				! empty( $plugin ) ? '<a href="' . $plugin . '" target="_blank" rel="noopener noreferrer">' : '',
				! empty( $plugin ) ? '</a>' : ''
			)
			: sprintf(
				// translators: 1. opening anchor tag, do not translate; 2. closing anchor tag, do not translate.
				__( 'Could not install the extension. Please %1$sdownload it from your account%2$s and install it manually.', 'easy-digital-downloads' ),
				'<a href="https://easydigitaldownloads.com/your-account/" target="_blank" rel="noopener noreferrer">',
				'</a>'
			);

		if ( 'plugin' !== $type ) {
			$download_url_classname = \edd_get_namespace( 'Admin\\Extensions\\DownloadURL' );
			$download_url_class     = new $download_url_classname( $this->pass_manager->highest_license_key );
			$plugin                 = $download_url_class->get_url();
		}

		if ( empty( $plugin ) ) {
			wp_send_json_error( $result );
		}

		// Set the current screen to avoid undefined notices.
		set_current_screen( 'download_page_edd-settings' );

		// Prepare variables.
		$url = esc_url_raw(
			edd_get_admin_url(
				array(
					'page' => 'edd-addons',
				)
			)
		);

		ob_start();
		$creds = request_filesystem_credentials( $url, '', false, false, null );

		// Hide the filesystem credentials form.
		ob_end_clean();

		// Check for file system permissions.
		if ( ! $creds ) {
			wp_send_json_error( $result );
		}

		if ( ! WP_Filesystem( $creds ) ) {
			wp_send_json_error( $result );
		}

		/*
		 * We do not need any extra credentials if we have gotten this far, so let's install the plugin.
		 */

		// Do not allow WordPress to search/download translations, as this will break JS output.
		remove_action( 'upgrader_process_complete', array( 'Language_Pack_Upgrader', 'async_upgrade' ), 20 );

		// Create the plugin upgrader with our custom skin.
		$installer = new \EDD\Admin\Installers\PluginSilentUpgrader( new \EDD\Admin\Installers\Install_Skin() );

		// Error check.
		if ( ! method_exists( $installer, 'install' ) || empty( $plugin ) ) {
			wp_send_json_error( $result );
		}

		$installer->install( $plugin ); // phpcs:ignore

		// Flush the cache and return the newly installed plugin basename.
		wp_cache_flush();

		$plugin_basename = $installer->plugin_info();

		// Check for permissions.
		if ( ! current_user_can( 'activate_plugins' ) ) {
			$result['message'] = 'plugin' === $type ? esc_html__( 'Plugin installed.', 'easy-digital-downloads' ) : esc_html__( 'Extension installed.', 'easy-digital-downloads' );

			wp_send_json_error( $result );
		}

		$this->activate( $plugin_basename );
	}

	/**
	 * Activates an existing extension.
	 *
	 * @since 2.11.4
	 * @param string $plugin_basename Optional: the plugin basename.
	 */
	public function activate( $plugin_basename = '' ) {
		$result = array(
			'message'      => __( 'There was an error while performing your request.', 'easy-digital-downloads' ),
			'is_activated' => false,
		);

		// Check for permissions.
		if ( ! check_ajax_referer( 'edd_extensionmanager', 'nonce', false ) || ! current_user_can( 'activate_plugins' ) ) {
			$result['message'] = __( 'Plugin activation is not available for you on this site.', 'easy-digital-downloads' );
			wp_send_json_error( $result );
		}

		$already_installed = false;
		if ( empty( $plugin_basename ) ) {
			$plugin_basename   = ! empty( $_POST['plugin'] ) ? sanitize_text_field( $_POST['plugin'] ) : '';
			$already_installed = true;
		}

		$plugin_basename = sanitize_text_field( wp_unslash( $plugin_basename ) );
		$type            = ! empty( $_POST['type'] ) ? sanitize_text_field( $_POST['type'] ) : '';
		if ( 'plugin' !== $type ) {
			$type = 'extension';
		}
		$result = array(
			/* translators: "extension" or "plugin" as defined by $type */
			'message'      => sprintf( __( 'Could not activate the %s.', 'easy-digital-downloads' ), esc_html( $type ) ),
			'is_activated' => false,
		);
		if ( empty( $plugin_basename ) || empty( $type ) ) {
			wp_send_json_error( $result );
		}

		$result['basename'] = $plugin_basename;

		// Set the GET variable for multi-plugin activation.
		$_GET['activate-multi'] = true;

		// Activate the plugin silently.
		$activated = activate_plugin( $plugin_basename );

		if ( is_wp_error( $activated ) ) {
			wp_send_json_error( $result );
		}

		do_action( 'edd_after_ajax_activate_extension', sanitize_text_field( $plugin_basename ) );

		// At this point we have successfully activated.
		if ( $already_installed ) {
			$message = 'plugin' === $type ? esc_html__( 'Plugin activated.', 'easy-digital-downloads' ) : esc_html__( 'Extension activated.', 'easy-digital-downloads' );
		} else {
			$message = 'plugin' === $type ? esc_html__( 'Plugin installed & activated.', 'easy-digital-downloads' ) : esc_html__( 'Extension installed & activated.', 'easy-digital-downloads' );
		}

		$success = array(
			'is_activated' => true,
			'message'      => $message,
		);
		if ( edd_is_pro() && class_exists( '\\EDD\\Pro\\Admin\\Extensions\\Buttons' ) ) {
			$buttons           = new \EDD\Pro\Admin\Extensions\Buttons();
			$success['status'] = __( 'Activated', 'easy-digital-downloads' );
			$success['button'] = $buttons->get_activate_deactivate_button(
				array(
					'type'    => $type,
					'id'      => filter_input( INPUT_POST, 'id', FILTER_SANITIZE_NUMBER_INT ),
					'product' => filter_input( INPUT_POST, 'product', FILTER_SANITIZE_NUMBER_INT ),
					'plugin'  => $plugin_basename,
					'action'  => 'deactivate',
				)
			);
		}

		wp_send_json_success( $success );
	}

	/**
	 * Deactivates a plugin.
	 *
	 * @since 3.1.1
	 * @return void
	 */
	public function deactivate() {
		$result = array(
			'message'        => __( 'There was an error while performing your request.', 'easy-digital-downloads' ),
			'is_deactivated' => false,
		);

		// Check for permissions.
		if ( ! check_ajax_referer( 'edd_extensionmanager', 'nonce', false ) || ! current_user_can( 'deactivate_plugins' ) ) {
			$result['message'] = __( 'Plugin deactivation is not available for you on this site.', 'easy-digital-downloads' );
			wp_send_json_error( $result );
		}

		$plugin = ! empty( $_POST['plugin'] ) ? sanitize_text_field( wp_unslash( $_POST['plugin'] ) ) : false;
		if ( empty( $plugin ) ) {
			wp_send_json_error( $result );
		}

		// At this point, we are allowed to deactivate the extension.
		$type = ! empty( $_POST['type'] ) ? sanitize_text_field( $_POST['type'] ) : '';
		if ( 'plugin' !== $type ) {
			$type = 'extension';
		}
		deactivate_plugins( $plugin );

		$this->update_wp_activation_data( $plugin );

		$success = array(
			'message'        => 'plugin' === $type ? esc_html__( 'Plugin deactivated.', 'easy-digital-downloads' ) : esc_html__( 'Extension deactivated.', 'easy-digital-downloads' ),
			'is_deactivated' => true,
		);
		if ( edd_is_pro() && class_exists( '\\EDD\\Pro\\Admin\\Extensions\\Buttons' ) ) {
			$buttons           = new \EDD\Pro\Admin\Extensions\Buttons();
			$success['button'] = $buttons->get_activate_deactivate_button(
				array(
					'type'    => $type,
					'id'      => filter_input( INPUT_POST, 'id', FILTER_SANITIZE_NUMBER_INT ),
					'product' => filter_input( INPUT_POST, 'product', FILTER_SANITIZE_NUMBER_INT ),
					'plugin'  => $plugin,
					'action'  => 'activate',
				)
			);
		}

		wp_send_json_success( $success );
	}

	/**
	 * Determine if the plugin/extension installations are allowed.
	 *
	 * @since 2.11.4
	 *
	 * @param string $type Should be `plugin` or `extension`.
	 *
	 * @return bool
	 */
	public function can_install( $type, $required_pass_id = false ) {

		if ( ! current_user_can( 'install_plugins' ) || ( is_multisite() && ! is_super_admin() ) ) {
			return false;
		}

		// Determine whether file modifications are allowed.
		if ( ! wp_is_file_mod_allowed( 'edd_can_install' ) ) {
			return false;
		}

		// All plugin checks are done.
		if ( 'plugin' === $type ) {
			return true;
		}

		return $this->pass_can_download( $required_pass_id );
	}

	/**
	 * Checks if a user's pass can download an extension.
	 *
	 * @since 2.11.4
	 * @return bool Returns true if the current site has an active pass and it is greater than or equal to the extension's minimum pass.
	 */
	public function pass_can_download( $required_pass_id = false ) {
		$highest_pass_id = $this->pass_manager->highest_pass_id;
		if ( ! $required_pass_id ) {
			$required_pass_id = $this->required_pass_id;
		}

		return ! empty( $highest_pass_id ) && ! empty( $required_pass_id ) && $this->pass_manager->pass_compare( $highest_pass_id, $required_pass_id, '>=' );
	}

	/**
	 * Get all installed plugins.
	 *
	 * @since 2.11.4
	 * @return array
	 */
	public function get_plugins() {
		if ( $this->all_plugins ) {
			return $this->all_plugins;
		}

		$this->all_plugins = get_plugins();

		return $this->all_plugins;
	}

	/**
	 * Check if a plugin is installed.
	 *
	 * @since 2.11.4
	 * @param  string $plugin The path to the main plugin file, eg 'my-plugin/my-plugin.php'.
	 * @return boolean
	 */
	public function is_plugin_installed( $plugin ) {
		return array_key_exists( $plugin, $this->get_plugins() );
	}

	/**
	 * Whether a given plugin is active or not.
	 *
	 * @since 2.11.4
	 * @param string|ProductData $basename_or_data The path to the main plugin file, eg 'my-plugin/my-plugin.php', or the product data object.
	 * @return boolean
	 */
	public function is_plugin_active( $basename_or_data ) {
		$basename = ! empty( $basename_or_data->basename ) ? $basename_or_data->basename : $basename_or_data;

		return ! empty( $basename ) && is_plugin_active( $basename );
	}

	/**
	 * Gets the plugin version.
	 *
	 * @since 3.1.1
	 * @param string $basename
	 * @return false|string
	 */
	protected function get_plugin_version( $basename ) {
		if ( empty( $basename ) ) {
			return false;
		}

		$plugins = $this->get_plugins();

		return array_key_exists( $basename, $plugins )
			? $plugins[ $basename ]['Version']
			: false;
	}

	/**
	 * When a plugin is deactivated, update the related WP options.
	 *
	 * @since 3.1.1
	 * @param string $plugin
	 * @return void
	 */
	private function update_wp_activation_data( $plugin ) {
		$deactivated = array(
			$plugin => time(),
		);

		if ( ! is_network_admin() ) {
			update_option( 'recently_activated', $deactivated + (array) get_option( 'recently_activated' ) );
		} else {
			update_site_option( 'recently_activated', $deactivated + (array) get_site_option( 'recently_activated' ) );
		}
	}

	/**
	 * When the extension manager activates a plugin, possibly modify behavior.
	 *
	 * @since 3.1.0.4
	 *
	 * @param string $plugin_basename The plugin basename being activated via the extension manager.
	 */
	public function post_extension_activation( $plugin_basename ) {
		if ( empty( $plugin_basename ) ) {
			return;
		}

		switch ( $plugin_basename ) {
			case 'wp-mail-smtp/wp_mail_smtp.php':
				update_option( 'wp_mail_smtp_activation_prevent_redirect', true );
				break;
			case 'all-in-one-seo-pack/all_in_one_seo_pack.php':
				update_option( 'aioseo_activation_redirect', true );
				break;
			case 'google-analytics-for-wordpress/googleanalytics.php':
				delete_transient( '_monsterinsights_activation_redirect' );
				break;
		}
	}
}
