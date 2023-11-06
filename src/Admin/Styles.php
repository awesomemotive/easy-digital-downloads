<?php
/**
 * Loads the EDD admin styles.
 *
 * @package     EDD
 * @subpackage  Admin/Styles
 *
 * @since 3.2.4
 */

namespace EDD\Admin;

use EDD\EventManagement\SubscriberInterface;

/**
 * Styles Loader Class.
 *
 * @since 3.2.4
 */
class Styles implements SubscriberInterface {

	/**
	 * Gets the subscribed events.
	 *
	 * @since 3.2.4
	 */
	public static function get_subscribed_events() {
		return array(
			'admin_body_class' => 'add_body_class',
		);
	}

	/**
	 * Adds the body class for our admin pages.
	 *
	 * @since 3.2.4
	 *
	 * @param string $classes The current body classes.
	 * @return string
	 */
	public function add_body_class( $classes ) {
		if ( ! edd_is_admin_page() ) {
			return $classes;
		}

		$classes .= ' edd-admin-page ';

		return $classes;
	}
}
