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
		$links['activate'] = __( 'Inactive &mdash; part of EDD', 'easy-digital-downloads' );

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
				'basename' => 'edd-manual-purchases/edd-manual-purchases.php',
				'option'   => 'edd_manual_purchases_license_active',
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
