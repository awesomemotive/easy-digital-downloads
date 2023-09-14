<?php
/**
 * Manages legacy extensions which have been merged into EDD.
 *
 * @since 3.1.1
 */
namespace EDD\Admin\Extensions;

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

				// If a legacy extension has a notification ID, then add a local notification.
				if ( ! empty( $extension['notification-id'] ) ) {
					EDD()->notifications->maybe_add_local_notification(
						array(
							'remote_id' => $extension['notification-id'],
							'type'      => 'info',
							// translators: %s is the name of the extension.
							'title'     => sprintf(
								__( '%s is now part of EDD!', 'easy-digital-downloads' ),
								$extension['name']
							),
							// translators: %s is the name of the extension.
							'content'   => sprintf(
								__( 'The functionality of %s has been merged into Easy Digital Downloads. It has been deactivated and you can safely delete the %s plugin.', 'easy-digital-downloads' ),
								$extension['name'],
								$extension['name']
							),
							'buttons'   => array(
								array(
									'text' => __( 'View Plugins', 'easy-digital-downloads' ),
									'url'  => add_query_arg(
										array(
											's' => urlencode( $extension['name'] ),
										),
										admin_url( 'plugins.php' )
									)
								),
							),
						)
					);
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
	 * @param array  $links
	 * @param string $plugin_file
	 * @return array
	 */
	public function update_plugin_links( $links, $plugin_file ) {
		$links['activate'] = __( 'Inactive &mdash; Part of EDD', 'easy-digital-downloads' );

		return $links;
	}

	/**
	 * Gets the array of extensions which have been merged into EDD.
	 *
	 * @sice 3.1.1
	 * @return array
	 */
	protected function get_extensions() {
		return array(
			'edd-manual-purchases' => array(
				'notification-id' => 'mp-legacy-notice',
				'name'            => 'Manual Purchases',
				'basename'        => 'edd-manual-purchases/edd-manual-purchases.php',
				'option'          => 'edd_manual_purchases_license_active',
			),
			'edd-downloads-as-services' => array(
				'notification-id' => 'das-legacy-notice',
				'name'            => 'Downloads as Services',
				'basename'        => 'edd-downloads-as-services/edd-downloads-as-services.php',
			),
		);
	}

	/**
	 * Whether the plugin should be deactivated.
	 *
	 * @since 3.1.1
	 * @param string $basename
	 * @return bool
	 */
	protected function should_deactivate( $basename ) {
		return true;
	}
}
