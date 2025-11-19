<?php
/**
 * Debug Log class
 *
 * @package EDD\Admin\Tools
 * @copyright Copyright (c) 2025, Sandhills Development, LLC
 * @license https://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since 3.6.1
 */

namespace EDD\Admin\Tools;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit; // @codeCoverageIgnore

use EDD\EventManagement\SubscriberInterface;

/**
 * Debug Log tab.
 *
 * @since 3.6.1
 */
class DebugLog implements SubscriberInterface {
	use \EDD\Admin\Settings\Traits\AjaxToggle;

	/**
	 * Get the subscribed events.
	 *
	 * @since 3.6.1
	 * @return array
	 */
	public static function get_subscribed_events(): array {
		return array(
			'edd_toggle_setting_handlers' => 'register_handler',
		);
	}

	/**
	 * Get the list of settings that this handler allows to be toggled via AJAX.
	 *
	 * @since 3.6.1
	 * @return array
	 */
	public static function get_allowed_ajax_settings(): array {
		return array( 'debug_mode' );
	}
}
