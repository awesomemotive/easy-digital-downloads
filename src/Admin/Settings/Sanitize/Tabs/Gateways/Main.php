<?php
/**
 * Sanitizes the Main section of the Gateways tab.
 *
 * @since 3.3.3
 * @package EDD\Admin\Settings\Sanitize\Tabs\Gateways
 */

namespace EDD\Admin\Settings\Sanitize\Tabs\Gateways;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

use EDD\Admin\Settings\Sanitize\Tabs\Section;

/**
 * Sanitizes the Gateways tab Main section.
 *
 * @since 3.3.3
 */
class Main extends Section {
	/**
	 * Sanitize the gateways tab main section.
	 *
	 * @since 3.3.3
	 *
	 * @param array $input The array of settings for the settings tab.
	 * @return array
	 */
	protected static function additional_processing( $input ) {
		if ( empty( $input['default_gateway'] ) ) {
			return $input;
		}

		// Unset the default gateway if there are no `gateways` enabled.
		if ( empty( $input['gateways'] ) || '-1' === $input['gateways'] ) {
			unset( $input['default_gateway'] );
		} elseif ( ! array_key_exists( $input['default_gateway'], $input['gateways'] ) ) {
			// Current gateway is no longer enabled.
			$enabled_gateways = $input['gateways'];

			reset( $enabled_gateways );

			$first_gateway = key( $enabled_gateways );

			if ( $first_gateway ) {
				$input['default_gateway'] = $first_gateway;
			}
		}

		return $input;
	}
}
