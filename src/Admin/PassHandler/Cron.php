<?php
/**
 * Pass Handler related cron events.
 *
 * @package EDD
 */

namespace EDD\Admin\PassHandler;

use \EDD\EventManagement\SubscriberInterface;

class Cron implements SubscriberInterface {

	/**
	 * Gets the array of subscribed events.
	 *
	 * @return array
	 */
	public static function get_subscribed_events() {
		if ( is_multisite() && ! is_main_site() ) {
			return array();
		}

		return array(
			'edd_weekly_scheduled_events' => 'weekly_license_check',
		);
	}

	/**
	 * Check if license key is valid once per week
	 *
	 * @since 3.1.1
	 * @return void
	 */
	public function weekly_license_check() {
		if ( ! edd_doing_cron() ) {
			return;
		}

		$handler = new \EDD\Admin\PassHandler\Handler();
		$license = $handler->get_pro_license();
		if ( empty( $license->key ) ) {
			return;
		}

		// data to send in our API request
		$api_params = array(
			'edd_action' => 'check_license',
			'license'    => $license->key,
			'item_id'    => $license->item_id,
			'item_name'  => $license->item_name,
			'pass_id'    => $license->item_id,
			'url'        => network_home_url(),
		);

		$api_handler  = new \EDD\Licensing\API();
		$license_data = $api_handler->make_request( $api_params );
		if ( ! $license_data ) {
			return false;
		}

		$pass_manager = new \EDD\Admin\Pass_Manager();
		$pass_manager->maybe_set_pass_flag( $license->key, $license_data );
		$handler->update_pro_license( $license_data );
	}
}
