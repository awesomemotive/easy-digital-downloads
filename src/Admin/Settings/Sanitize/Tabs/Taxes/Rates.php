<?php
/**
 * Sanitizes the Tax Rates tab.
 *
 * @since 3.3.3
 * @package EDD\Admin\Settings\Sanitize\Tabs\Taxes
 */

namespace EDD\Admin\Settings\Sanitize\Tabs\Taxes;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

use EDD\Admin\Settings\Sanitize\Tabs\Section;

/**
 * Sanitizes the Tax Rates tab.
 *
 * @since 3.3.3
 */
class Rates extends Section {
	/**
	 * Handle the changes to the tax rates section.
	 *
	 * @since 3.3.3
	 * @param array $input The array of settings for the settings tab.
	 * @return array
	 */
	protected static function additional_processing( $input ) {
		if ( ! isset( $_POST['tax_rates'] ) ) {
			return $input;
		}

		$tax_rates = ! empty( $_POST['tax_rates'] )
			? $_POST['tax_rates']
			: array();

		foreach ( $tax_rates as $tax_rate ) {

			$scope = isset( $tax_rate['global'] )
				? 'country'
				: 'region';

			$region = isset( $tax_rate['state'] )
				? sanitize_text_field( $tax_rate['state'] )
				: '';

			$name = '*' === $tax_rate['country']
				? ''
				: sanitize_text_field( $tax_rate['country'] );

			if ( empty( $name ) ) {
				$scope = 'global';
			}

			$tax_rate_data = array(
				'country' => $name,
				'amount'  => floatval( $tax_rate['rate'] ),
				'state'   => $region,
				'scope'   => $scope,
				'status'  => $tax_rate['status'] ?? 'active',
			);

			if ( ( empty( $tax_rate_data['country'] ) && 'global' !== $tax_rate_data['scope'] ) || $tax_rate_data['amount'] < 0 ) {
				continue;
			}

			if ( ! empty( $tax_rate['id'] ) && edd_get_tax_rate_by( $tax_rate['id'] ) ) {
				$tax_rate_data['status'] = sanitize_text_field( $tax_rate['status'] );

				edd_update_tax_rate( $tax_rate['id'], $tax_rate_data );
			} else {
				$tax_rate_data['status'] = 'active';

				edd_add_tax_rate( $tax_rate_data );
			}
		}

		return $input;
	}
}
