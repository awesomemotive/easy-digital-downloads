<?php
/**
 * Extends the core telemetry behavior.
 *
 * @package   easy-digital-downloads
 * @copyright Copyright (c) 2023, Easy Digital Downloads
 * @license   GPL2+
 * @since     3.1.1
 */

namespace EDD\Pro\Telemetry;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/**
 * Class Tracking
 *
 * @package EDD\Pro\Telemetry
 */
class Tracking extends \EDD\Telemetry\Tracking {

	/**
	 * Pro users should never see the telemetry setting.
	 *
	 * @param array $settings
	 * @return array
	 */
	public function register_setting( $settings ) {
		return $settings;
	}

	/**
	 * All pro users opt into tracking.
	 *
	 * @return true
	 */
	protected function tracking_allowed() {
		return true;
	}

	/**
	 * Clears out the admin notice for Pro.
	 *
	 * @return void
	 */
	public function admin_notice() {}
}
