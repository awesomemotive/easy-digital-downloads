<?php

namespace EDD\Pro\Admin\Settings;

defined( 'ABSPATH' ) || exit;

use EDD\EventManagement\SubscriberInterface;

class Handler implements SubscriberInterface {

	/**
	 * Returns an array of events that this subscriber wants to listen to.
	 *
	 * @return array
	 */
	public static function get_subscribed_events() {
		return array(
			'edd_settings_gateways' => 'add_geoip_settings',
		);
	}

	/**
	 * Update the GeoIP settings in the settings array.
	 *
	 * @param array $settings The settings array.
	 * @return array
	 */
	public function add_geoip_settings( $settings ) {
		if ( edd_is_inactive_pro() || empty( $settings['checkout']['geolocation'] ) ) {
			return $settings;
		}

		$settings['checkout']['geolocation']['options']  = array(
			'disabled'   => __( 'Disabled', 'easy-digital-downloads' ),
			'enabled'    => __( 'Always Enabled', 'easy-digital-downloads' ),
			'logged_out' => __( 'Enabled for logged out users only', 'easy-digital-downloads' ),
		);
		$settings['checkout']['geolocation']['desc']     = __( 'Increase conversions by auto-detecting the country and region for customers at checkout.', 'easy-digital-downloads' );
		$settings['checkout']['geolocation']['std']      = 'enabled';
		$settings['checkout']['geolocation']['disabled'] = false;

		return $settings;
	}
}
