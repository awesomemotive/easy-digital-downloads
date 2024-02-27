<?php
/**
 * Registers legacy extensions that have been merged into EDD (Pro).
 *
 * @since 3.1.1
 */
namespace EDD\Pro\Admin\Extensions;

class Legacy extends \EDD\Admin\Extensions\Legacy {

	/**
	 * Deactivates the legacy extension.
	 *
	 * @since 3.1.1
	 * @return void
	 */
	public function manage_legacy_extensions() {
		remove_filter( 'plugin_action_links_easy-digital-downloads/easy-digital-downloads.php', 'edd_plugin_action_links' );
		add_filter( 'plugin_action_links_easy-digital-downloads-pro/easy-digital-downloads.php', 'edd_plugin_action_links', 10, 2 );
		parent::manage_legacy_extensions();
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
		$plugin_file = explode( '/', $plugin_file );
		$slug        = reset( $plugin_file );
		if ( ! array_key_exists( $slug, $this->get_pro_extensions() ) ) {
			return parent::update_plugin_links( $links, $plugin_file );
		}

		$links['activate'] = __( 'Inactive &mdash; Part of EDD (Pro)', 'easy-digital-downloads' );
		if ( 'easy-digital-downloads' === $slug ) {
			$links['activate'] = __( 'Inactive &mdash; You\'ve got EDD (Pro)!', 'easy-digital-downloads' );
		}

		return $links;
	}

	/**
	 * Gets the array of extensions which have been merged into EDD.
	 *
	 * @sice 3.1.1
	 * @return array
	 */
	protected function get_extensions() {
		return array_merge( parent::get_extensions(), $this->get_pro_extensions() );
	}

	/**
	 * Whether the plugin should be deactivated.
	 *
	 * @since 3.1.1
	 * @param string $basename
	 * @return bool
	 */
	protected function should_deactivate( $basename ) {
		return 'easy-digital-downloads/easy-digital-downloads.php' === $basename ? false : parent::should_deactivate( $basename );
	}

	/**
	 * Gets extensions replaced by pro functionality.
	 *
	 * @since 3.1.1
	 * @return array
	 */
	private function get_pro_extensions() {
		return array(
			'edd-duplicate-downloads' => array(
				'notification-id' => 'dd-legacy-notice',
				'name'            => 'Duplicate Downloads',
				'basename'        => 'edd-duplicate-downloads/easy-digital-downloads-duplicate-downloads.php',
				'option'          => 'edd_duplicate_downloads_license_active',
			),
			'easy-digital-downloads'  => array(
				'basename' => 'easy-digital-downloads/easy-digital-downloads.php',
			),
		);
	}
}
