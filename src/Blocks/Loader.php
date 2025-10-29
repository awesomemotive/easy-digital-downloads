<?php
/**
 * Blocks loader.
 *
 * @package     EDD\Blocks
 * @copyright   Copyright (c) 2025, Sandhills Development, LLC
 * @license     https://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       3.6.0
 */

namespace EDD\Blocks;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit; // @codeCoverageIgnore

use EDD\EventManagement\MiniManager;

/**
 * Class Loader
 *
 * @since 3.6.0
 */
class Loader extends MiniManager {
	/**
	 * Get the event classes.
	 *
	 * @since 3.6.0
	 * @return array
	 */
	protected function get_event_classes(): array {
		return array(
			new Checkout\Elements\UserDetails(),
		);
	}
}
