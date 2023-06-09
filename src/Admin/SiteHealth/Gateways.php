<?php
/**
 * Gets the gateways data for the Site Health report.
 *
 * @since 3.1.2
 * @package EDD\Admin\SiteHealth
 */

namespace EDD\Admin\SiteHealth;

/**
 * Loads gateways data into Site Health.
 *
 * @since 3.1.2
 */
class Gateways {

	/**
	 * Gets the gateways data array.
	 *
	 * @since 3.1.2
	 */
	public function get() {
		return array(
			'label'  => __( 'Easy Digital Downloads &mdash; Gateways', 'easy-digital-downloads' ),
			'fields' => $this->get_gateways(),
		);
	}

	/**
	 * Gets the gateways data.
	 *
	 * @since 3.1.2
	 */
	private function get_gateways() {
		$all_gateways = edd_get_payment_gateways();
		$gateways     = array();

		if ( ! empty( $all_gateways ) ) {

			$default_gateway = edd_get_default_gateway();

			foreach ( $all_gateways as $key => $gateway ) {
				$gateways[ $key ] = array(
					'label' => $gateway['admin_label'],
					'value' => edd_is_gateway_active( $key ) ? 'Active' : 'Inactive',
				);

				if ( $default_gateway === $key ) {
					$gateways[ $key ]['value'] .= ' (Default)';
				}
			}
		}

		return $gateways;
	}
}
