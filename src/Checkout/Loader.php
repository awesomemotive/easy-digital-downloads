<?php
/**
 * Handles loading the checkout classes.
 *
 * @package   easy-digital-downloads
 * @copyright Copyright (c) 2023, Easy Digital Downloads
 * @license   GPL2+
 * @since     3.3.5
 */

namespace EDD\Checkout;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit; // @codeCoverageIgnore

use EDD\EventManagement\MiniManager;

/**
 * Class Loader
 *
 * @since 3.3.5
 * @package EDD\Checkout
 */
class Loader extends MiniManager {

	/**
	 * Get the event classes.
	 *
	 * @since 3.3.5
	 * @return array
	 */
	protected function get_event_classes(): array {
		return array(
			AutoRegister::get_instance(),
			new Errors(),
		);
	}
}
