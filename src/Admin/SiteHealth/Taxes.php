<?php
/**
 * Gets the tax information and rates for the site.
 *
 * @since 3.1.2
 * @package EDD\Admin\SiteHealth
 */

namespace EDD\Admin\SiteHealth;

/**
 * Loads tax information into Site Health.
 *
 * @since 3.1.2
 */
class Taxes {

	/**
	 * Gets the data.
	 *
	 * @since 3.1.2
	 * @return array
	 */
	public function get() {
		return array(
			'label'  => __( 'Easy Digital Downloads &mdash; Taxes', 'easy-digital-downloads' ),
			'fields' => $this->get_taxes(),
		);
	}

	/**
	 * Gets the tax information.
	 *
	 * @since 3.1.2
	 * @return array
	 */
	private function get_taxes() {
		$taxes = array(
			'taxes_enabled'       => array(
				'label' => 'Taxes',
				'value' => edd_use_taxes() ? 'Enabled' : 'Disabled',
			),
			'default_rate'        => array(
				'label' => 'Default Tax Rate',
				'value' => edd_get_formatted_tax_rate(),
			),
			'display_on_checkout' => array(
				'label' => 'Display on Checkout',
				'value' => edd_get_option( 'checkout_include_tax', false ) ? 'Displayed' : 'Not Displayed',
			),
			'prices_include_tax'  => array(
				'label' => 'Prices Include Tax',
				'value' => edd_prices_include_tax() ? 'Yes' : 'No',
			),
		);
		$rates = edd_get_tax_rates( array(), OBJECT );
		if ( ! empty( $rates ) ) {
			foreach ( $rates as $rate ) {
				if ( 'global' === $rate->scope ) {
					continue;
				}
				$tax_rate = $rate->name;
				if ( ! empty( $rate->description ) ) {
					$tax_rate .= ' / ' . $rate->description;
				}
				$taxes[ $rate->id ] = array(
					'label' => $tax_rate,
					'value' => edd_get_formatted_tax_rate( $rate->name, $rate->description ),
				);
			}
		}

		return $taxes;
	}
}
