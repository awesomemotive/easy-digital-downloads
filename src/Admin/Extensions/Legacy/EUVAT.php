<?php
/**
 * Legacy EU VAT Rates.
 *
 * @package     EDD\Admin\Extensions\Legacy
 * @copyright   Copyright (c) 2025, Sandhills Development, LLC
 * @license     https://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       3.5.0
 */

namespace EDD\Admin\Extensions\Legacy;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit; // @codeCoverageIgnore

/**
 * Class EUVAT
 *
 * @since 3.5.0
 */
class EUVAT {

	/**
	 * Register the legacy EU VAT rates.
	 *
	 * @since 3.5.0
	 */
	public function register_rates() {
		if ( edd_has_upgrade_completed( 'eu_vat_legacy_rates' ) ) {
			return;
		}

		foreach ( $this->get_rates() as $country_code => $rate ) {
			$tax_rates = edd_get_tax_rates(
				array(
					'status'  => 'active',
					'country' => $country_code,
					'scope'   => 'country',
				),
				OBJECT
			);
			if ( ! empty( $tax_rates ) ) {
				continue;
			}

			$tax_rate_id = edd_add_tax_rate(
				array(
					'country' => $country_code,
					'scope'   => 'country',
					'amount'  => $rate,
					'status'  => 'active',
					'source'  => 'legacy',
				)
			);
			if ( $tax_rate_id ) {
				edd_debug_log( 'Added legacy tax rate for ' . $country_code . ': ' . $rate );
			} else {
				edd_debug_log( 'Failed to add legacy tax rate for ' . $country_code . ': ' . $rate );
			}
		}

		edd_set_upgrade_complete( 'eu_vat_legacy_rates' );
	}

	/**
	 * Get the legacy EU VAT rates.
	 *
	 * @since 3.5.0
	 * @return array
	 */
	private function get_rates() {
		return apply_filters(
			'edd_vat_current_eu_vat_rates',
			array(
				'AT' => 20.0,
				'BE' => 21.0,
				'BG' => 20.0,
				'CY' => 19.0,
				'CZ' => 21.0,
				'DE' => 19.0,
				'DK' => 25.0,
				'EE' => 22.0,
				'ES' => 21.0,
				'FI' => 25.5,
				'FR' => 20.0,
				'GB' => 20.0,
				'GR' => 24.0,
				'HR' => 25.0,
				'HU' => 27.0,
				'IE' => 23.0,
				'IT' => 22.0,
				'LT' => 21.0,
				'LU' => 17.0,
				'LV' => 21.0,
				'MT' => 18.0,
				'NL' => 21.0,
				'PL' => 23.0,
				'PT' => 23.0,
				'RO' => 19.0,
				'SI' => 22.0,
				'SK' => 20.0,
				'SE' => 25.0,
			)
		);
	}
}
