<?php

namespace EDD\Upgrades;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit; // @codeCoverageIgnore

use EDD\EventManagement\MiniManager;

/**
 * Class Loader
 *
 * @since 3.2.10
 * @package EDD\Upgrades
 */
class Loader extends MiniManager {

	/**
	 * Get the event classes.
	 *
	 * @since 3.2.10
	 * @return array
	 */
	protected function get_event_classes(): array {
		return array(
			new Orders\MigrateAfterActionsDate(),
			new Adjustments\DiscountsStartEnd(),
			new Emails\Registration(),
		);
	}
}
