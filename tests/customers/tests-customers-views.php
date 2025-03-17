<?php
/**
 * Customers Admin View Tests.
 *
 * @group edd_customers
 */

namespace EDD\Tests\Customers\Admin;

use EDD\Tests\PHPUnit\EDD_UnitTestCase;

class Views extends EDD_UnitTestCase {

	public static function wpSetUpBeforeClass() {
		require_once EDD_PLUGIN_DIR . 'includes/admin/customers/customers.php';
		require_once EDD_PLUGIN_DIR . 'includes/admin/customers/customer-functions.php';
	}

	public function test_registered_pages() {
		$this->assertSame(
			array(
				'customers' => esc_html__( 'Customers', 'easy-digital-downloads' ),
				'emails'    => esc_html__( 'Email Addresses', 'easy-digital-downloads' ),
				'physical'  => esc_html__( 'Physical Addresses', 'easy-digital-downloads' ),
			),
			\edd_get_customer_pages()
		);
	}

	/**
	 * @covers ::edd_customer_views
	 */
	public function test_registered_views() {
		$this->assertSame(
			array(
				'overview'  => 'edd_customers_view',
				'emails'    => 'edd_customers_emails_view',
				'addresses' => 'edd_customers_addresses_view',
				'delete'    => 'edd_customers_delete_view',
				'notes'     => 'edd_customer_notes_view',
				'tools'     => 'edd_customer_tools_view',
			),
			\edd_customer_views()
		);
	}

	/**
	 * @covers ::edd_customer_tabs
	 */
	public function test_registered_tabs() {
		$this->assertSame(
			array(
				'overview'  => array( 'dashicon' => 'dashicons-admin-users',    'title' => _x( 'Profile', 'Customer Details tab title', 'easy-digital-downloads' ) ),
				'emails'    => array( 'dashicon' => 'dashicons-email', 'title' => _x( 'Emails', 'Customer Emails tab title', 'easy-digital-downloads' ) ),
				'addresses' => array( 'dashicon' => 'dashicons-admin-home', 'title' => _x( 'Addresses', 'Customer Addresses tab title', 'easy-digital-downloads' ) ),
				'notes'     => array( 'dashicon' => 'dashicons-admin-comments', 'title' => _x( 'Notes',   'Customer Notes tab title',   'easy-digital-downloads' ) ),
				'tools'     => array( 'dashicon' => 'dashicons-admin-tools',    'title' => _x( 'Tools',   'Customer Tools tab title',   'easy-digital-downloads' ) ),
				'delete'    => array( 'dashicon' => 'dashicons-trash', 'title' => _x( 'Delete', 'Delete Customer tab title', 'easy-digital-downloads' ) ),
			),
			\edd_customer_tabs()
		);
	}
}
