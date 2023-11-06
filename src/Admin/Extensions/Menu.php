<?php
/**
 * Adds the Extensions submenu page under the "Downloads" menu
 *
 * @package     EDD
 * @subpackage  Admin/Extensions
 */

namespace EDD\Admin\Extensions;

use EDD\EventManagement\SubscriberInterface;

/**
 * Extensions menu class.
 *
 * @since 3.1.1
 */
class Menu implements SubscriberInterface {

	/**
	 * Returns an array of events that this subscriber wants to listen to.
	 *
	 * @return array
	 */
	public static function get_subscribed_events() {
		$events = array();
		if ( current_user_can( 'manage_shop_settings' ) ) {
			$events['admin_menu'] = array( 'menu', 99999 );
		}

		return $events;
	}

	/**
	 * Create the Extensions submenu page under the "Downloads" menu
	 *
	 * @since 3.1.1
	 * @global $edd_add_ons_page
	 */
	public function menu() {
		global $edd_add_ons_page;
		$extensions_class = edd_get_namespace( 'Admin\\Extensions\\ExtensionPage' );
		$extensions       = new $extensions_class();
		$edd_add_ons_page = add_submenu_page(
			'edit.php?post_type=download',
			__( 'EDD Extensions', 'easy-digital-downloads' ),
			__( 'Extensions', 'easy-digital-downloads' ),
			'manage_shop_settings',
			'edd-addons',
			array( $extensions, 'init' )
		);
	}
}
