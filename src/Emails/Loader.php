<?php
/**
 * Handles loading the email classes.
 *
 * @package   easy-digital-downloads
 * @copyright Copyright (c) 2023, Easy Digital Downloads
 * @license   GPL2+
 * @since     3.3.0
 */

namespace EDD\Emails;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit; // @codeCoverageIgnore

use EDD\EventManagement\MiniManager;

/**
 * Class Loader
 *
 * @since 3.3.0
 * @package EDD\Emails
 */
class Loader extends MiniManager {

	/**
	 * Get the event classes.
	 *
	 * @since 3.3.9
	 * @return array
	 */
	protected function get_event_classes(): array {
		$classes = array(
			new Handler(),
			new Triggers(),
			new Legacy(),
		);
		if ( is_admin() ) {
			$classes[] = new \EDD\Admin\Emails\Manager();
			$classes[] = new \EDD\Admin\Emails\Messages();
		}

		return $classes;
	}
}
