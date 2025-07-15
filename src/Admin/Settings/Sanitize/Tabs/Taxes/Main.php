<?php
/**
 * Sanitizes the main taxes settings.
 *
 * @package     EDD\Admin\Settings\Sanitize\Tabs\Taxes
 * @copyright   Copyright (c) 2025, Sandhills Development, LLC
 * @license     https://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       3.5.0
 */

namespace EDD\Admin\Settings\Sanitize\Tabs\Taxes;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

use EDD\Admin\Settings\Sanitize\Tabs\Section;

/**
 * Sanitizes the main taxes settings.
 *
 * @since 3.5.0
 */
class Main extends Section {
	/**
	 * Handle the changes to the tax rates section.
	 *
	 * @since 3.5.0
	 * @param array $input The array of settings for the settings tab.
	 * @return array
	 */
	protected static function additional_processing( $input ) {
		if ( empty( $input['enable_taxes'] ) ) {
			$input['vat_enable'] = false;
		}

		if ( ! empty( $input['vat_enable'] ) ) {
			self::handle_legacy_vat();
		}

		return $input;
	}

	/**
	 * Registers the legacy EU VAT rates.
	 *
	 * @since 3.5.0
	 * @return void
	 */
	private static function handle_legacy_vat() {
		\EDD\Admin\Extensions\Legacy::deactivate(
			array(
				'notification-id' => 'eu-vat-legacy-notice',
				'name'            => 'EU VAT',
				'basename'        => 'edd-eu-vat/edd-eu-vat.php',
			)
		);

		$legacy_rates = new \EDD\Admin\Extensions\Legacy\EUVAT();
		$legacy_rates->register_rates();
	}
}
