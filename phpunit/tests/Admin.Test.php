<?php

require_once( './phpunit/vendor/wordpress/wp-admin/includes/plugin.php' );

class Test_Easy_Digital_Downloads_Admin extends WP_UnitTestCase {
	private $object;

	public function setUp() {
		parent::setUp();
		$this->object = EDD();
	}

	public function tearDown() {
		parent::tearDown();
	}

	public function testPluginPages() {
		$edd_payments_page   	= add_submenu_page( 'edit.php?post_type=download', __( 'Payment History', 'edd' ), __( 'Payment History', 'edd' ), 'edit_shop_payments', 'edd-payment-history', 'edd_payment_history_page' );
		$edd_discounts_page     = add_submenu_page( 'edit.php?post_type=download', __( 'Discount Codes', 'edd' ), __( 'Discount Codes', 'edd' ), 'manage_shop_discounts', 'edd-discounts', 'edd_discounts_page' );
		$edd_reports_page 	    = add_submenu_page( 'edit.php?post_type=download', __( 'Earnings and Sales Reports', 'edd' ), __( 'Reports', 'edd' ), 'view_shop_reports', 'edd-reports', 'edd_reports_page' );
		$edd_settings_page 	    = add_submenu_page( 'edit.php?post_type=download', __( 'Easy Digital Download Settings', 'edd' ), __( 'Settings', 'edd' ), 'manage_shop_settings', 'edd-settings', 'edd_options_page' );
		$edd_system_info_page 	= add_submenu_page( 'edit.php?post_type=download', __( 'Easy Digital Download System Info', 'edd' ), __( 'System Info', 'edd' ), 'manage_shop_settings', 'edd-system-info', 'edd_system_info' );
		$edd_add_ons_page 	    = add_submenu_page( 'edit.php?post_type=download', __( 'Easy Digital Download Add Ons', 'edd' ), __( 'Add Ons', 'edd' ), 'manage_shop_settings', 'edd-addons', 'edd_add_ons_page' );
		$edd_upgrades_screen    = add_submenu_page( null, __( 'EDD Upgrades', 'edd' ), __( 'EDD Upgrades', 'edd' ), 'manage_shop_settings', 'edd-upgrades', 'edd_upgrades_screen' );

		
	}
}