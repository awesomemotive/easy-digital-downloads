<?php

namespace EDD\Admin\Extensions;

use \EDD\Admin\Pass_Manager;

class Extension_Manager {

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
	 * @since 2.11.4
	 * @var int
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

		add_action( 'wp_ajax_edd_activate_extension', array( $this, 'activate' ) );
		add_action( 'wp_ajax_edd_install_extension', array( $this, 'install' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'register_assets' ) );
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
		wp_register_style( 'edd-extension-manager', EDD_PLUGIN_URL . "assets/css/edd-admin-extension-manager.min.css", array(), EDD_VERSION );
		wp_register_script( 'edd-extension-manager', EDD_PLUGIN_URL . "assets/js/edd-admin-extension-manager.js", array( 'jquery' ), EDD_VERSION, true );
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
		if ( ! empty( $configuration ) ) {
			$product = $product->mergeConfig( $configuration );
		}
		?>
		<div class="<?php echo esc_attr( implode( ' ', array_map( 'sanitize_html_class', $this->get_card_classes( $product ) ) ) ); ?>">
			<h3 class="edd-extension-manager__title"><?php echo esc_html( $product->title ); ?></h3>
			<div class="edd-extension-manager__body">
				<?php if ( ! empty( $product->image ) ) : ?>
					<div class="edd-extension-manager__image">
						<img alt="" src="<?php echo esc_url( $product->image ); ?>" />
					</div>
				<?php endif; ?>
				<?php if ( ! empty( $product->description ) ) : ?>
					<div class="edd-extension-manager__description"><?php echo wp_kses_post( wpautop( $product->description ) ); ?></div>
				<?php endif; ?>
				<?php if ( ! empty( $product->features ) && is_array( $product->features ) ) : ?>
					<div class="edd-extension-manager__features">
						<ul>
						<?php foreach ( $product->features as $feature ) : ?>
							<li><span class="dashicons dashicons-yes"></span><?php echo esc_html( $feature ); ?></li>
						<?php endforeach; ?>
						</ul>
					</div>
				<?php endif; ?>
				<div class="edd-extension-manager__group">
					<?php
					if ( ! empty( $product->basename ) && ! $this->is_plugin_active( $product->basename ) ) {
						?>
						<div class="edd-extension-manager__step">
							<?php $this->button( $inactive_parameters ); ?>
						</div>
						<?php
					}
					?>
					<div class="edd-extension-manager__step">
						<?php $this->link( $active_parameters ); ?>
					</div>
				</div>
			</div>
		</div>
		<?php
	}

	/**
	 * Gets the CSS classes for the single extension card.
	 *
	 * @since 2.11.4
	 * @param ProductData $product The product data object.
	 * @return array The array of CSS classes.
	 */
	private function get_card_classes( $product ) {
		$base_class   = 'edd-extension-manager__card';
		$card_classes = array(
			$base_class,
		);
		$variation    = 'stacked';
		if ( ! empty( $product->style ) ) {
			$variation = $product->style;
		}
		if ( 'detailed-2col' === $variation && ( empty( $product->features ) || ! is_array( $product->features ) ) ) {
			$variation = 'detailed';
		}
		$card_classes[] = "{$base_class}--{$variation}";

		return $card_classes;
	}

	/**
	 * Outputs the button to activate/install a plugin/extension.
	 * If a link is passed in the args, create a button style link instead (@uses $this->link()).
	 *
	 * @since 2.11.4
	 * @param array $args The array of parameters for the button.
	 * @return void
	 */
	public function button( $args ) {
		if ( ! empty( $args['href'] ) ) {
			$this->link( $args );
			return;
		}
		$defaults = array(
			'button_class' => 'button-primary',
			'plugin'       => '',
			'action'       => '',
			'button_text'  => '',
			'type'         => 'plugin',
			'id'           => '',
			'product'      => '',
			'pass'         => $this->required_pass_id,
		);
		$args     = wp_parse_args( $args, $defaults );
		if ( empty( $args['button_text'] ) ) {
			return;
		}
		?>
		<button
			class="button <?php echo esc_attr( $args['button_class'] ); ?> edd-extension-manager__action"
			<?php
			foreach ( $args as $key => $attribute ) {
				if ( empty( $attribute ) || in_array( $key, array( 'button_class', 'button_text' ), true ) ) {
					continue;
				}
				printf(
					' data-%s="%s"',
					esc_attr( $key ),
					esc_attr( $attribute )
				);
			}
			?>
		>
			<?php echo esc_html( $args['button_text'] ); ?>
		</button>
		<?php
	}

	/**
	 * Outputs the link, if it should be a link.
	 *
	 * @param array $args
	 * @return void
	 */
	public function link( $args ) {
		$defaults = array(
			'button_class' => 'button-primary',
			'button_text'  => '',
		);
		$args     = wp_parse_args( $args, $defaults );
		if ( empty( $args['button_text'] ) ) {
			return;
		}
		?>
		<a
			class="button <?php echo esc_attr( $args['button_class'] ); ?>"
			href="<?php echo esc_url( $args['href'] ); ?>"
			<?php echo ! empty( $args['new_tab'] ) ? ' target="_blank" rel="noopener noreferrer"' : ''; ?>
		>
			<?php echo esc_html( $args['button_text'] ); ?>
		</a>
		<?php
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
		$result        = array(
			'message'      => $generic_error,
			'is_activated' => false,
		);
		if ( ! $type ) {
			wp_send_json_error( $result );
		}

		// Check if new installations are allowed.
		if ( ! $this->can_install( $type, $required_pass ) ) {
			wp_send_json_error( $result );
		}

		$result['message'] = 'plugin' === $type
			? __( 'Could not install the plugin. Please download and install it manually via Plugins > Add New.', 'easy-digital-downloads' )
			: sprintf(
				/* translators: 1. opening anchor tag, do not translate; 2. closing anchor tag, do not translate */
				__( 'Could not install the extension. Please %1$sdownload it from your account%2$s and install it manually.', 'easy-digital-downloads' ),
				'<a href="https://easydigitaldownloads.com/your-account/" target="_blank" rel="noopener noreferrer">',
				'</a>'
			);

		$plugin = ! empty( $_POST['plugin'] ) ? sanitize_text_field( $_POST['plugin'] ) : '';
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
		require_once EDD_PLUGIN_DIR . 'includes/admin/installers/class-plugin-silent-upgrader.php';
		require_once EDD_PLUGIN_DIR . 'includes/admin/installers/class-plugin-silent-upgrader-skin.php';
		require_once EDD_PLUGIN_DIR . 'includes/admin/installers/class-install-skin.php';

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

		// Activate the plugin silently.
		$activated = activate_plugin( $plugin_basename );

		if ( ! is_wp_error( $activated ) ) {

			if ( $already_installed ) {
				$message = 'plugin' === $type ? esc_html__( 'Plugin activated.', 'easy-digital-downloads' ) : esc_html__( 'Extension activated.', 'easy-digital-downloads' );
			} else {
				$message = 'plugin' === $type ? esc_html__( 'Plugin installed & activated.', 'easy-digital-downloads' ) : esc_html__( 'Extension installed & activated.', 'easy-digital-downloads' );
			}
			$result['is_activated'] = true;
			$result['message']      = $message;

			wp_send_json_success( $result );
		}

		// Fallback error just in case.
		wp_send_json_error( $result );
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

		if ( ! current_user_can( 'install_plugins' ) ) {
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
}
