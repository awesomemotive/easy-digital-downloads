<?php
/**
 * Adds Software Licensing update support for the pro product.
 *
 * @package   easy-digital-downloads
 * @copyright Copyright (c) 2023, Easy Digital Downloads
 * @license   GPL2+
 * @since     3.1.1
 */
namespace EDD\Pro\Licensing;

class Update implements \EDD\EventManagement\SubscriberInterface {

	/**
	 * Returns an array of events that this subscriber wants to listen to.
	 *
	 * @return array
	 */
	public static function get_subscribed_events() {
		return array(
			'init' => 'check_for_updates',
		);
	}

	/**
	 * Queries the EDD server for pro updates.
	 * This is managed separately from the main license handler class because the license is managed differently.
	 *
	 * @since 3.1.1
	 * @return void
	 */
	public function check_for_updates() {

		// To support auto-updates, this needs to run during the wp_version_check cron job for privileged users.
		$doing_cron = defined( 'DOING_CRON' ) && DOING_CRON;
		if ( ! current_user_can( 'manage_options' ) && ! $doing_cron ) {
			return;
		}

		// Gets the license key from the database.
		$license_key = trim( get_site_option( 'edd_pro_license_key' ) );

		// Don't check for updates if there isn't a license key.
		if ( empty( $license_key ) ) {
			return;
		}

		// Instantiate the updater class.
		new \EDD\Extensions\Updater(
			EDD_PLUGIN_FILE,
			array(
				'version'   => EDD_VERSION,
				'license'   => $license_key,
				'item_name' => 'Easy Digital Downloads (Pro)',
				'item_id'   => 1783595,
				'beta'      => false,
			)
		);
	}
}
