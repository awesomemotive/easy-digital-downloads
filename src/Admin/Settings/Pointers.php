<?php

namespace EDD\Admin\Settings;

defined( 'ABSPATH' ) || exit;

use EDD\EventManagement\SubscriberInterface;

/**
 * Class Pointers
 *
 * @since 3.2.4
 * @package EDD\Admin\Settings
 */
class Pointers implements SubscriberInterface {

	/**
	 * Get the events to subscribe to.
	 *
	 * @since 3.2.4
	 * @return array
	 */
	public static function get_subscribed_events() {
		$events = array();

		if ( empty( edd_get_option( 'base_country' ) ) ) {
			$events['edd_pointers'] = 'pointers';
		}

		return $events;
	}

	/**
	 * Maybe show an admin pointer showing a message about the store's base country.
	 *
	 * @since 3.2.4
	 * @return array
	 */
	public function pointers( $pointers ) {
		if ( ! edd_is_admin_page() ) {
			return $pointers;
		}

		if ( edd_is_admin_page( 'settings', 'general' ) ) {
			$pointers[] = array(
				'pointer_id' => 'edd_base_country_input',
				'target'     => '#edd_settings_base_country__chosen',
				'options'    => array(
					'content'  => sprintf(
						'<h3>%s</h3><p>%s</p>',
						__( 'Update your Store\'s Country', 'easy-digital-downloads' ),
						__( 'Your store does not have the Business Country set. Easy Digital Downloads uses this setting to properly configure settings for your store. Please update your country to get the best experience.', 'easy-digital-downloads' )
					),
					'position' => array(
						'edge'  => 'left',
						'align' => 'center',
					),
					'pointerClass' => 'edd-pointer warning',
				),
			);
		}

		return $pointers;
	}
}
