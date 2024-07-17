<?php
/**
 * Manages legacy extensions which have been merged into EDD.
 *
 * @since 3.1.1
 * @package EDD\Admin\Extensions
 */

namespace EDD\Admin\Extensions;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit; // @codeCoverageIgnore

/**
 * Class Legacy
 *
 * @since 3.1.1
 */
class Legacy implements \EDD\EventManagement\SubscriberInterface {

	/**
	 * Returns an array of events that this subscriber wants to listen to.
	 *
	 * @since 3.1.1
	 * @return array
	 */
	public static function get_subscribed_events() {
		return array(
			'admin_init' => 'manage_legacy_extensions',
		);
	}

	/**
	 * Deactivates the legacy extension.
	 *
	 * @since 3.1.1
	 * @return void
	 */
	public function manage_legacy_extensions() {
		foreach ( $this->get_extensions() as $extension ) {
			add_action( "plugin_action_links_{$extension['basename']}", array( $this, 'update_plugin_links' ), 10, 2 );
			if ( ! is_plugin_active( $extension['basename'] ) ) {
				continue;
			}
			if ( $this->should_deactivate( $extension['basename'] ) ) {

				$this->maybe_do_notification( $extension );

				if ( ! empty( $extension['on_deactivate'] ) && is_callable( $extension['on_deactivate'] ) ) {
					add_action( "deactivate_{$extension['basename']}", $extension['on_deactivate'] );
				}
				deactivate_plugins( $extension['basename'] );
			}
			if ( ! empty( $extension['option'] ) ) {
				delete_option( $extension['option'] );
			}
		}
	}

	/**
	 * Removes the activation link from the plugins table.
	 *
	 * @since 3.1.1
	 * @param array  $links       The plugin links.
	 * @param string $plugin_file The plugin file.
	 * @return array
	 */
	public function update_plugin_links( $links, $plugin_file ) {
		$links['activate'] = __( 'Inactive &mdash; Part of EDD', 'easy-digital-downloads' );

		return $links;
	}

	/**
	 * Updates the auto register option and emails when Auto Register is deactivated.
	 *
	 * @since 3.3.0
	 * @return void
	 */
	public function deactivate_auto_register() {
		$auto_register = new Legacy\AutoRegister();
		$auto_register->update();
	}

	/**
	 * Gets the array of extensions which have been merged into EDD.
	 *
	 * @sice 3.1.1
	 * @return array
	 */
	protected function get_extensions() {
		return array(
			'edd-manual-purchases'         => array(
				'notification-id' => 'mp-legacy-notice',
				'name'            => 'Manual Purchases',
				'basename'        => 'edd-manual-purchases/edd-manual-purchases.php',
				'option'          => 'edd_manual_purchases_license_active',
			),
			'edd-downloads-as-services'    => array(
				'notification-id' => 'das-legacy-notice',
				'name'            => 'Downloads as Services',
				'basename'        => 'edd-downloads-as-services/edd-downloads-as-services.php',
			),
			'edd-disable-purchase-receipt' => array(
				'notification-id' => 'dpr-legacy-notice',
				'name'            => 'Disable Purchase Receipt',
				'basename'        => 'edd-disable-purchase-receipt/edd-disable-purchase-receipt.php',
				'on_deactivate'   => array( $this, 'disable_order_receipt' ),
			),
			'edd-auto-register'            => array(
				'notification-id' => 'ar-legacy-notice',
				'name'            => 'Auto Register',
				'basename'        => 'edd-auto-register/edd-auto-register.php',
				'on_deactivate'   => array( $this, 'deactivate_auto_register' ),
				'content'         => __( 'Auto Register has been merged into Easy Digital Downloads. It has been deactivated and you can safely delete the Auto Register plugin. Please review your new user emails to ensure that any customizations were retained during the migration.', 'easy-digital-downloads' ),
				'custom_buttons'  => array(
					array(
						'text' => __( 'View Emails', 'easy-digital-downloads' ),
						'url'  => edd_get_admin_url(
							array(
								'page' => 'edd-emails',
							)
						),
					),
				),
			),
		);
	}

	/**
	 * Updates the order receipt email when "Disable Purchase Receipt" is deactivated automatically.
	 *
	 * @since 3.3.0
	 * @return void
	 */
	public function disable_order_receipt() {
		$email = edd_get_email_by( 'email_id', 'order_receipt' );
		edd_update_email(
			$email->id,
			array(
				'status' => 0,
			)
		);
	}

	/**
	 * Whether the plugin should be deactivated.
	 *
	 * @since 3.1.1
	 * @param string $basename The plugin basename.
	 * @return bool
	 */
	protected function should_deactivate( $basename ) {
		return true;
	}

	/**
	 * If set, adds an EDD notification.
	 *
	 * @since 3.3.0
	 * @param array $extension The array of extension data.
	 * @return void
	 */
	private function maybe_do_notification( $extension ) {
		// If a legacy extension has a notification ID, then add a local notification.
		if ( empty( $extension['notification-id'] ) ) {
			return;
		}
		EDD()->notifications->maybe_add_local_notification( $this->get_notification_args( $extension ) );
	}

	/**
	 * Retrieves the notification arguments for a given extension.
	 *
	 * @since 3.3.0
	 * @param array $extension The array of extension data.
	 * @return array The notification arguments for the extension.
	 */
	private function get_notification_args( $extension ) {
		return wp_parse_args(
			$extension,
			array(
				'remote_id' => $extension['notification-id'],
				'type'      => 'info',
				'title'     => sprintf(
					/* translators: %s: name of the extension. */
					__( '%s is now part of EDD!', 'easy-digital-downloads' ),
					$extension['name']
				),
				'content'   => sprintf(
					/* translators: %s: name of the extension. */
					__( 'The functionality of %1$s has been merged into Easy Digital Downloads. It has been deactivated and you can safely delete the %2$s plugin.', 'easy-digital-downloads' ),
					$extension['name'],
					$extension['name'] // This is the same as the previous placeholder, but it's necessary because the original string has two placeholders.
				),
				'buttons'   => $this->get_buttons( $extension ),
			)
		);
	}

	/**
	 * Retrieves the buttons for a given extension.
	 *
	 * @since 3.3.0
	 * @param array $extension The array of extension data.
	 * @return array The buttons for the extension.
	 */
	private function get_buttons( $extension ) {
		$buttons = array(
			array(
				'text' => __( 'View Plugins', 'easy-digital-downloads' ),
				'url'  => add_query_arg(
					array(
						's' => urlencode( $extension['name'] ),
					),
					admin_url( 'plugins.php' )
				),
			),
		);

		if ( empty( $extension['custom_buttons'] ) ) {
			return $buttons;
		}

		return array_merge( $buttons, $extension['custom_buttons'] );
	}
}
