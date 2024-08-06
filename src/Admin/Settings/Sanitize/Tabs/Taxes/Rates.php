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

			$adjustment_data = array(
				'name'        => $name,
				'type'        => 'tax_rate',
				'scope'       => $scope,
				'amount_type' => 'percent',
				'amount'      => floatval( $tax_rate['rate'] ),
				'description' => $region,
			);

			if ( ( empty( $adjustment_data['name'] ) && 'global' !== $adjustment_data['scope'] ) || $adjustment_data['amount'] < 0 ) {
				continue;
			}

			$existing_adjustment = edd_get_adjustments( $adjustment_data );

			if ( ! empty( $existing_adjustment ) ) {
				$adjustment                = $existing_adjustment[0];
				$adjustment_data['status'] = sanitize_text_field( $tax_rate['status'] );

				edd_update_adjustment( $adjustment->id, $adjustment_data );
			} else {
				$adjustment_data['status'] = 'active';

				edd_add_tax_rate( $adjustment_data );
			}
		}

		return $input;
	}
}
