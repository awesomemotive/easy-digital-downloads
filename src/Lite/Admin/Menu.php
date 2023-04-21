<?php
/**
 * Admin menu functionality.
 *
 * @package EDD
 */

namespace EDD\Lite\Admin;

use \EDD\EventManagement\SubscriberInterface;

class Menu implements SubscriberInterface {

	/**
	 * Returns an array of events that this subscriber wants to listen to.
	 *
	 * @return array
	 */
	public static function get_subscribed_events() {
		$events = array();
		if ( current_user_can( 'manage_shop_settings' ) ) {
			$events['admin_menu'] = array( 'pro_link', 9000 );
		}

		return $events;
	}

	public function pro_link() {
		$pass_manager         = new \EDD\Admin\Pass_Manager();
		$onboarding_completed = get_option( 'edd_onboarding_completed', false );

		if ( $onboarding_completed && ! $pass_manager->has_pass() ) {

			add_submenu_page(
				'edit.php?post_type=download',
				esc_html__( 'Upgrade to Pro', 'easy-digital-downloads' ),
				esc_html__( 'Upgrade to Pro', 'easy-digital-downloads' ),
				'manage_shop_settings',
				edd_link_helper(
					'https://easydigitaldownloads.com/lite-upgrade',
					array(
						'utm_medium'  => 'admin-menu',
						'utm_content' => 'upgrade-to-pro',
					)
				)
			);
			add_action( 'admin_head', array( $this, 'adjust_pro_menu_item_class' ) );
		}
	}

	/**
	 * Adds the custom pro menu item class.
	 *
	 * @since 3.1.1
	 * @return void
	 */
	public function adjust_pro_menu_item_class() {
		new \EDD\Admin\Menu\LinkClass( 'https://easydigitaldownloads.com/lite-upgrade', 'edd-sidebar__upgrade-pro' );
	}
}
