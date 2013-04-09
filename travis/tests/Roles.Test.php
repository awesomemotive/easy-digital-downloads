<?php
/**
 * Test Roles
 */

class Test_Easy_Digital_Downloads_Roles extends WP_UnitTestCase {
	public function setUp() {
		parent::setUp();
	}

	public function testRoles() {
		global $wp_roles;

		$this->assertArrayHasKey('shop_manager', (array) $wp_roles->role_names);
		$this->assertArrayHasKey('shop_accountant', (array) $wp_roles->role_names);
		$this->assertArrayHasKey('shop_worker', (array) $wp_roles->role_names);
		$this->assertArrayHasKey('shop_vendor', (array) $wp_roles->role_names);
	}
}