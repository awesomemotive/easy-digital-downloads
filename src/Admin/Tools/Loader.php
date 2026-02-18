<?php
/**
 * Loader for Tools.
 *
 * @package EDD\Admin\Tools
 * @copyright Copyright (c) 2025, Sandhills Development, LLC
 * @license https://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since 3.6.1
 */

namespace EDD\Admin\Tools;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit; // @codeCoverageIgnore

use EDD\EventManagement\MiniManager;

/**
 * Loader for Tools.
 *
 * @since 3.6.1
 */
class Loader extends MiniManager {

	/**
	 * Get the event classes.
	 *
	 * @since 3.6.1
	 * @return array
	 */
	protected function get_event_classes(): array {
		return array(
			new Labs(),
			new DebugLog(),
			new Logs(),
			new LogSettings(),
			new ScheduledActions(),
		);
	}
}
