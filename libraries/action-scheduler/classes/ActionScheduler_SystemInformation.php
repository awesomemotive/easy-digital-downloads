<?php

/**
 * Provides information about active and registered instances of Action Scheduler.
 */
class ActionScheduler_SystemInformation {
	/**
	 * Returns information about the plugin or theme which contains the current active version
	 * of Action Scheduler.
	 *
	 * If this cannot be determined, or if Action Scheduler is being loaded via some other
	 * method, then it will return an empty array. Otherwise, if populated, the array will
	 * look like the following:
	 *
	 *     [
	 *         'type' => 'plugin', # or 'theme'
	 *         'name' => 'Name',
	 *     ]
	 *
	 * @return array
	 */
	public static function active_source(): array {
		$plugins      = get_plugins();
		$plugin_files = array_keys( $plugins );

		foreach ( $plugin_files as $plugin_file ) {
			$plugin_path = trailingslashit( WP_PLUGIN_DIR ) . dirname( $plugin_file );
			$plugin_file = trailingslashit( WP_PLUGIN_DIR ) . $plugin_file;

			if ( 0 !== strpos( dirname( __DIR__ ), $plugin_path ) ) {
				continue;
			}

			$plugin_data = get_plugin_data( $plugin_file );

			if ( ! is_array( $plugin_data ) || empty( $plugin_data['Name'] ) ) {
				continue;
			}

			return array(
				'type' => 'plugin',
				'name' => $plugin_data['Name'],
			);
		}

		$themes = (array) search_theme_directories();

		foreach ( $themes as $slug => $data ) {
			$needle = trailingslashit( $data['theme_root'] ) . $slug . '/';

			if ( 0 !== strpos( __FILE__, $needle ) ) {
				continue;
			}

			$theme = wp_get_theme( $slug );

			if ( ! is_object( $theme ) || ! is_a( $theme, \WP_Theme::class ) ) {
				continue;
			}

			return array(
				'type' => 'theme',
				// phpcs:ignore WordPress.NamingConventions.ValidVariableName.UsedPropertyNotSnakeCase
				'name' => $theme->Name,
			);
		}

		return array();
	}

	/**
	 * Returns the directory path for the currently active installation of Action Scheduler.
	 *
	 * @return string
	 */
	public static function active_source_path(): string {
		return trailingslashit( dirname( __DIR__ ) );
	}

	/**
	 * Get registered sources.
	 *
	 * It is not always possible to obtain this information. For instance, if earlier versions (<=3.9.0) of
	 * Action Scheduler register themselves first, then the necessary data about registered sources will
	 * not be available.
	 *
	 * @return array<string, string>
	 */
	public static function get_sources() {
		$versions = ActionScheduler_Versions::instance();
		return method_exists( $versions, 'get_sources' ) ? $versions->get_sources() : array();
	}
}
