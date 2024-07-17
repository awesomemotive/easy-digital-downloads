<?php

namespace EDD\Admin\Menu;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit; // @codeCoverageIgnore

/**
 * Class Pages
 *
 * @since 3.3.0
 * @package EDD\Admin\Menu
 */
class Pages {

	/**
	 * Registers the EDD admin pages.
	 *
	 * @since 3.3.0
	 */
	public static function register() {
		$pages = self::define_pages();
		foreach ( $pages as $slug => $page ) {
			add_submenu_page(
				self::get_parent_slug(),
				$page['page_title'],
				$page['menu_title'],
				$page['capability'],
				$slug,
				$page['callback']
			);
		}

		self::register_upgrade_page();
		self::add_to_dashboard();
	}

	/**
	 * Gets the list of EDD admin page slugs.
	 *
	 * @since 3.3.0
	 * @return array
	 */
	public static function get_pages() {
		return array_keys( self::define_pages() );
	}

	/**
	 * Defines the EDD admin pages.
	 *
	 * @since 3.3.0
	 * @return array
	 */
	private static function define_pages() {
		return array(
			'edd-payment-history' => array(
				'page_title' => __( 'Orders', 'easy-digital-downloads' ),
				'menu_title' => __( 'Orders', 'easy-digital-downloads' ),
				'capability' => 'edit_shop_payments',
				'callback'   => 'edd_payment_history_page',
			),
			'edd-customers'       => array(
				'page_title' => __( 'Customers', 'easy-digital-downloads' ),
				'menu_title' => __( 'Customers', 'easy-digital-downloads' ),
				'capability' => apply_filters( 'edd_view_customers_role', 'view_shop_reports' ),
				'callback'   => 'edd_customers_page',
			),
			'edd-discounts'       => array(
				'page_title' => __( 'Discounts', 'easy-digital-downloads' ),
				'menu_title' => __( 'Discounts', 'easy-digital-downloads' ),
				'capability' => 'manage_shop_discounts',
				'callback'   => 'edd_discounts_page',
			),
			'edd-reports'         => array(
				'page_title' => __( 'Reports', 'easy-digital-downloads' ),
				'menu_title' => __( 'Reports', 'easy-digital-downloads' ),
				'capability' => 'view_shop_reports',
				'callback'   => 'edd_reports_page',
			),
			'edd-settings'        => array(
				'page_title' => __( 'EDD Settings', 'easy-digital-downloads' ),
				'menu_title' => __( 'Settings', 'easy-digital-downloads' ),
				'capability' => 'manage_shop_settings',
				'callback'   => array( '\\EDD\\Admin\\Settings\\Screen', 'render' ),
			),
			'edd-emails'          => array(
				'page_title' => __( 'EDD Emails', 'easy-digital-downloads' ),
				'menu_title' => self::mark_new( __( 'Emails', 'easy-digital-downloads' ) ),
				'capability' => 'manage_shop_settings',
				'callback'   => array( '\\EDD\\Admin\\Emails\\Screen', 'render' ),
			),
			'edd-tools'           => array(
				'page_title' => __( 'EDD Tools', 'easy-digital-downloads' ),
				'menu_title' => __( 'Tools', 'easy-digital-downloads' ),
				'capability' => 'manage_shop_settings',
				'callback'   => array( '\\EDD\\Admin\\Tools\\Screen', 'render' ),
			),
		);
	}

	/**
	 * Registers the hidden upgrades page.
	 *
	 * @since 3.3.0
	 */
	private static function register_upgrade_page() {
		add_submenu_page(
			'index.php',
			__( 'EDD Upgrades', 'easy-digital-downloads' ),
			__( 'EDD Upgrades', 'easy-digital-downloads' ),
			'manage_shop_settings',
			'edd-upgrades',
			'edd_upgrades_screen'
		);
		add_action(
			'admin_head',
			function () {
				remove_submenu_page( 'index.php', 'edd-upgrades' );
			}
		);
	}

	/**
	 * Add our reports link in the main Dashboard menu.
	 *
	 * @since 3.3.0
	 */
	private static function add_to_dashboard() {
		global $submenu;

		$submenu['index.php'][] = array( // phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited
			__( 'Store Reports', 'easy-digital-downloads' ),
			'view_shop_reports',
			self::get_parent_slug() . '&page=edd-reports',
		);
	}

	/**
	 * Get the parent slug for the EDD submenu pages.
	 *
	 * @since 3.3.0
	 * @return string
	 */
	private static function get_parent_slug() {
		return 'edit.php?post_type=download';
	}

	/**
	 * Adds an indicator to mark a new menu item.
	 *
	 * @since 3.3.0
	 * @return string
	 */
	private static function mark_new( $title ) {
		return sprintf(
			'%s<span class="edd-admin-menu__new">&nbsp;%s</span>',
			$title,
			__( 'NEW!', 'easy-digital-downloads' )
		);
	}
}
