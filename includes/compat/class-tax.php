<?php
/**
 * Backwards Compatibility Handler for Taxes.
 *
 * @package     EDD
 * @subpackage  Compat
 * @copyright   Copyright (c) 2018, Easy Digital Downloads, LLC
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       3.0
 */
namespace EDD\Compat;

// Exit if accessed directly
defined( 'ABSPATH' ) || exit;

/**
 * Tax Class.
 *
 * EDD 3.0 moves away from storing tax rates in wp_options. This class handles all the backwards compatibility for the
 * transition to custom tables.
 *
 * @since 3.0
 */
class Tax extends Base {

	/**
	 * Holds the component for which we are handling back-compat. There is a chance that two methods have the same name
	 * and need to be dispatched to completely other methods. When a new instance of Back_Compat is created, a component
	 * can be passed to the constructor which will allow __call() to dispatch to the correct methods.
	 *
	 * @since 3.0
	 * @access protected
	 * @var string
	 */
	protected $component = 'tax';

	/**
	 * Backwards compatibility hooks for payments.
	 *
	 * @since 3.0
	 * @access protected
	 */
	protected function hooks() {

		/* Filters ************************************************************/

		add_filter( 'pre_update_option', array( $this, 'update_option' ), 10, 3 );
	}

	/**
	 * Backwards compatibility layer for update_option().
	 *
	 * This is here for backwards compatibility purposes with the migration to custom tables in EDD 3.0.
	 *
	 * @since 3.0
	 *
	 * @param mixed  $value     The new, unserialized option value.
	 * @param string $option    Name of the option.
	 * @param mixed  $old_value The old option value.
	 *
	 * @return string $value Option value.
	 */
	public function update_option( $value, $option, $old_value ) {

		// Bail if tax rates are not being updated.
		if ( 'edd_tax_rates' !== $option ) {
			return $value;
		}

		$value = (array) $value;

		foreach ( $value as $tax_rate ) {
			if ( empty( $tax_rate ) ) {
				continue;
			}
			$scope = isset( $tax_rate['global'] )
				? 'country'
				: 'region';

			$region = isset( $tax_rate['state'] )
				? sanitize_text_field( $tax_rate['state'] )
				: '';

			$adjustment_data = array(
				'name'        => $tax_rate['country'],
				'status'      => 'active',
				'type'        => 'tax_rate',
				'scope'       => $scope,
				'amount_type' => 'percent',
				'amount'      => floatval( $tax_rate['rate'] ),
				'description' => $region,
			);

			// Update database if adjustment ID was supplied.
			if ( isset( $tax_rate['edd_adjustment_id'] ) ) {
				edd_update_adjustment( $tax_rate['edd_adjustment_id'], $adjustment_data );

			// Check if the tax rate exists.
			} else {
				$rate = edd_get_adjustments( array(
					'type'        => 'tax_rate',
					'fields'      => 'ids',
					'name'        => $tax_rate['country'],
					'description' => $region,
					'scope'       => $scope,
				) );

				// Tax rate exists.
				if ( 1 === count( $rate ) ) {
					$adjustment_id = absint( $rate[0] );

					edd_update_adjustment( $adjustment_id, $adjustment_data );

				// Add the tax rate to the database.
				} else {
					edd_add_adjustment( $adjustment_data );
				}
			}
		}

		// Return the value so it is stored for backwards compatibility purposes.
		return $value;
	}
}
