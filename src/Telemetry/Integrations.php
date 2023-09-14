<?php
/**
 * Gets the store settings data to send to our telemetry server.
 *
 * @package   easy-digital-downloads
 * @copyright Copyright (c) 2023, Easy Digital Downloads
 * @license   GPL2+
 * @since     3.1.1
 */
namespace EDD\Telemetry;

class Integrations {

	/**
	 * Gets the integrations data.
	 *
	 * @todo currently returning two different kinds of data.
	 * @since 3.1.1
	 * @return array
	 */
	public function get() {
		$data = array();
		foreach ( $this->get_all_plugins() as $basename => $details ) {
			if ( ! $this->should_log_integration( $basename, $details ) ) {
				continue;
			}
			$data[] = array(
				'name'    => $details['Name'],
				'type'    => $this->is_core_integration( $basename, $details ) ? 'core' : 'external',
				'version' => $details['Version'],
			);
		}

		return $data;
	}

	/**
	 * Gets all plugins on the site.
	 *
	 * @since 3.1.1
	 * @return array
	 */
	private function get_all_plugins() {
		if ( ! function_exists( 'get_plugins' ) ) {
			require_once ABSPATH . 'wp-admin/includes/plugin.php';
		}

		return get_plugins();
	}

	/**
	 * Whether the integration should be included in the data.
	 *
	 * @since 3.1.1
	 * @param string $basename
	 * @param array $details
	 * @return bool
	 */
	private function should_log_integration( $basename, $details ) {
		if ( ! is_plugin_active( $basename ) ) {
			return false;
		}

		return true;
	}

	/**
	 * Whether the integration is an EDD or third party integration.
	 *
	 * @since 3.1.1
	 * @param string $basename
	 * @param array $details
	 * @return bool
	 */
	private function is_core_integration( $basename, $details ) {
		if ( 'Easy Digital Downloads' === $details['Author'] ) {
			return true;
		}
		if ( in_array( untrailingslashit( $details['AuthorURI'] ), array( 'https://easydigitaldownloads.com', 'https://sandhillsdev.com' ), true ) ) {
			return false !== strpos( $details['PluginURI'], 'easydigitaldownloads.com' );
		}

		return false;
	}
}
