<?php
/**
 * Pass related cron events.
 *
 * @package EDD
 * @subpackage Cron/Components
 */

namespace EDD\Cron\Components;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/**
 * Passes Class
 *
 * @since 3.3.0
 */
class Passes extends Component {

	/**
	 * The unique identifier for this component.
	 *
	 * @var string
	 */
	protected static $id = 'passes';

	/**
	 * Gets the array of subscribed events.
	 */
	public static function get_subscribed_events(): array {
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
	 * @return void|bool
	 */
	public function weekly_license_check() {
		if ( ! edd_doing_cron() ) {
			return;
		}

		$handler = new \EDD\Admin\PassHandler\Handler();
		$license = $handler->get_pro_license();
		if ( empty( $license->key ) ) {
			return false;
		}

		// data to send in our API request.
		$api_params = array(
			'edd_action' => 'check_license',
			'license'    => $license->key,
			'item_id'    => $license->item_id,
			'item_name'  => $license->item_name,
			'pass_id'    => $license->item_id,
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
