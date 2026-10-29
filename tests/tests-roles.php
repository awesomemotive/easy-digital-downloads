<?php
namespace EDD\Tests;

use EDD\Tests\PHPUnit\EDD_UnitTestCase;

/**
 * @group edd_roles
 */
class Tests_Roles extends EDD_UnitTestCase {

	/**
	 * A real user of each role the absence assertions run against.
	 *
	 * @var array
	 */
	protected static $users = array();

	/**
	 * The current user before each test.
	 *
	 * @var int
	 */
	private $original_user_id = 0;

	/**
	 * Create one user per role under test.
	 */
	public static function wpSetUpBeforeClass() {
		EDD()->roles->add_roles();
		EDD()->roles->add_caps();

		foreach ( array( 'shop_accountant', 'shop_worker', 'shop_vendor', 'subscriber' ) as $role ) {
			self::$users[ $role ] = self::factory()->user->create( array( 'role' => $role ) );
		}
	}

	/**
	 * Remember the current user, which the same-request tests replace.
	 */
	public function set_up() {
		parent::set_up();

		$this->original_user_id = get_current_user_id();
	}

	/**
	 * Restore the shop roles and the current user the same-request tests change.
	 */
	public function tear_down() {
		EDD()->roles->add_roles();
		EDD()->roles->add_caps();
		wp_set_current_user( $this->original_user_id );

		parent::tear_down();
	}

	public function test_roles() {

		global $wp_roles;

		$this->assertArrayHasKey( 'shop_manager', (array) $wp_roles->role_names );
		$this->assertArrayHasKey( 'shop_accountant', (array) $wp_roles->role_names );
		$this->assertArrayHasKey( 'shop_worker', (array) $wp_roles->role_names );
		$this->assertArrayHasKey( 'shop_vendor', (array) $wp_roles->role_names );
	}

	public function test_shop_manager_caps() {
		global $wp_roles;

		if ( class_exists( 'WP_Roles' ) ) {
			if ( ! isset( $wp_roles ) ) {
				$wp_roles = new \WP_Roles();
			}
		}

		$this->assertArrayHasKey( 'read', (array) $wp_roles->roles['shop_manager']['capabilities'] );
		$this->assertArrayHasKey( 'edit_posts', (array) $wp_roles->roles['shop_manager']['capabilities'] );
		$this->assertArrayHasKey( 'delete_posts', (array) $wp_roles->roles['shop_manager']['capabilities'] );
		$this->assertArrayHasKey( 'unfiltered_html', (array) $wp_roles->roles['shop_manager']['capabilities'] );
		$this->assertArrayHasKey( 'upload_files', (array) $wp_roles->roles['shop_manager']['capabilities'] );
		$this->assertArrayHasKey( 'export', (array) $wp_roles->roles['shop_manager']['capabilities'] );
		$this->assertArrayHasKey( 'import', (array) $wp_roles->roles['shop_manager']['capabilities'] );
		$this->assertArrayHasKey( 'delete_others_pages', (array) $wp_roles->roles['shop_manager']['capabilities'] );
		$this->assertArrayHasKey( 'delete_others_posts', (array) $wp_roles->roles['shop_manager']['capabilities'] );
		$this->assertArrayHasKey( 'delete_pages', (array) $wp_roles->roles['shop_manager']['capabilities'] );
		$this->assertArrayHasKey( 'delete_private_pages', (array) $wp_roles->roles['shop_manager']['capabilities'] );
		$this->assertArrayHasKey( 'delete_private_posts', (array) $wp_roles->roles['shop_manager']['capabilities'] );
		$this->assertArrayHasKey( 'delete_published_pages', (array) $wp_roles->roles['shop_manager']['capabilities'] );
		$this->assertArrayHasKey( 'delete_published_posts', (array) $wp_roles->roles['shop_manager']['capabilities'] );
		$this->assertArrayHasKey( 'edit_others_pages', (array) $wp_roles->roles['shop_manager']['capabilities'] );
		$this->assertArrayHasKey( 'edit_others_posts', (array) $wp_roles->roles['shop_manager']['capabilities'] );
		$this->assertArrayHasKey( 'edit_pages', (array) $wp_roles->roles['shop_manager']['capabilities'] );
		$this->assertArrayHasKey( 'edit_posts', (array) $wp_roles->roles['shop_manager']['capabilities'] );
		$this->assertArrayHasKey( 'edit_private_pages', (array) $wp_roles->roles['shop_manager']['capabilities'] );
		$this->assertArrayHasKey( 'edit_private_posts', (array) $wp_roles->roles['shop_manager']['capabilities'] );
		$this->assertArrayHasKey( 'edit_published_pages', (array) $wp_roles->roles['shop_manager']['capabilities'] );
		$this->assertArrayHasKey( 'edit_published_posts', (array) $wp_roles->roles['shop_manager']['capabilities'] );
		$this->assertArrayHasKey( 'manage_categories', (array) $wp_roles->roles['shop_manager']['capabilities'] );
		$this->assertArrayHasKey( 'manage_links', (array) $wp_roles->roles['shop_manager']['capabilities'] );
		$this->assertArrayHasKey( 'moderate_comments', (array) $wp_roles->roles['shop_manager']['capabilities'] );
		$this->assertArrayHasKey( 'publish_pages', (array) $wp_roles->roles['shop_manager']['capabilities'] );
		$this->assertArrayHasKey( 'publish_posts', (array) $wp_roles->roles['shop_manager']['capabilities'] );
		$this->assertArrayHasKey( 'read_private_pages', (array) $wp_roles->roles['shop_manager']['capabilities'] );
		$this->assertArrayHasKey( 'view_shop_sensitive_data', (array) $wp_roles->roles['shop_manager']['capabilities'] );
		$this->assertArrayHasKey( 'export_shop_reports', (array) $wp_roles->roles['shop_manager']['capabilities'] );
		$this->assertArrayHasKey( 'manage_shop_settings', (array) $wp_roles->roles['shop_manager']['capabilities'] );
		$this->assertArrayHasKey( 'manage_shop_discounts', (array) $wp_roles->roles['shop_manager']['capabilities'] );
	}

	public function test_administrator_caps() {
		global $wp_roles;

		if ( class_exists( 'WP_Roles' ) ) {
			if ( ! isset( $wp_roles ) ) {
				$wp_roles = new \WP_Roles();
			}
		}

		$this->assertArrayHasKey( 'view_shop_sensitive_data', (array) $wp_roles->roles['administrator']['capabilities'] );
		$this->assertArrayHasKey( 'export_shop_reports', (array) $wp_roles->roles['administrator']['capabilities'] );
		$this->assertArrayHasKey( 'manage_shop_settings', (array) $wp_roles->roles['administrator']['capabilities'] );
		$this->assertArrayHasKey( 'manage_shop_discounts', (array) $wp_roles->roles['administrator']['capabilities'] );
	}

	public function test_shop_accountant_caps() {
		global $wp_roles;

		if ( class_exists( 'WP_Roles' ) ) {
			if ( ! isset( $wp_roles ) ) {
				$wp_roles = new \WP_Roles();
			}
		}

		$this->assertArrayHasKey( 'read', (array) $wp_roles->roles['shop_accountant']['capabilities'] );
		$this->assertArrayHasKey( 'edit_posts', (array) $wp_roles->roles['shop_accountant']['capabilities'] );
		$this->assertArrayHasKey( 'delete_posts', (array) $wp_roles->roles['shop_accountant']['capabilities'] );
		$this->assertArrayHasKey( 'read_private_products', (array) $wp_roles->roles['shop_accountant']['capabilities'] );
		$this->assertArrayHasKey( 'view_shop_reports', (array) $wp_roles->roles['shop_accountant']['capabilities'] );
		$this->assertArrayHasKey( 'export_shop_reports', (array) $wp_roles->roles['shop_accountant']['capabilities'] );
		$this->assertArrayHasKey( 'edit_shop_payments', (array) $wp_roles->roles['shop_accountant']['capabilities'] );
	}

	public function test_shop_vendor_caps() {
		global $wp_roles;

		if ( class_exists( 'WP_Roles' ) ) {
			if ( ! isset( $wp_roles ) ) {
				$wp_roles = new \WP_Roles();
			}
		}

		$this->assertArrayHasKey( 'edit_product', (array) $wp_roles->roles['shop_vendor']['capabilities'] );
		$this->assertArrayHasKey( 'delete_product', (array) $wp_roles->roles['shop_vendor']['capabilities'] );
		$this->assertArrayHasKey( 'delete_products', (array) $wp_roles->roles['shop_vendor']['capabilities'] );
		$this->assertArrayHasKey( 'publish_products', (array) $wp_roles->roles['shop_vendor']['capabilities'] );
		$this->assertArrayHasKey( 'edit_published_products', (array) $wp_roles->roles['shop_vendor']['capabilities'] );
		$this->assertArrayHasKey( 'upload_files', (array) $wp_roles->roles['shop_vendor']['capabilities'] );
		$this->assertArrayHasKey( 'assign_product_terms', (array) $wp_roles->roles['shop_vendor']['capabilities'] );
	}

	/*
	 * Capability absences.
	 *
	 * These assert through user_can() against a real user of each role rather than
	 * through $wp_roles->roles[...], because EDD_Roles::meta_caps() can grant a
	 * capability through map_meta_cap that never appears in the stored role array.
	 * An absence from that array is not an absence for the user.
	 */

	public function test_shop_accountant_does_not_hold_delete_shop_payments() {
		$this->assertTrue( user_can( self::$users['shop_accountant'], 'edit_shop_payments' ) );
		$this->assertFalse( user_can( self::$users['shop_accountant'], 'delete_shop_payments' ) );
	}

	public function test_shop_accountant_does_not_hold_publish_shop_payments() {
		$this->assertFalse( user_can( self::$users['shop_accountant'], 'publish_shop_payments' ) );
	}

	public function test_shop_accountant_does_not_hold_manage_shop_settings() {
		$this->assertFalse( user_can( self::$users['shop_accountant'], 'manage_shop_settings' ) );
	}

	public function test_shop_worker_holds_delete_shop_payments() {
		$this->assertTrue( user_can( self::$users['shop_worker'], 'delete_shop_payments' ) );
	}

	public function test_shop_worker_does_not_hold_view_shop_reports() {
		$this->assertFalse( user_can( self::$users['shop_worker'], 'view_shop_reports' ) );
	}

	public function test_shop_worker_does_not_hold_manage_shop_settings() {
		$this->assertFalse( user_can( self::$users['shop_worker'], 'manage_shop_settings' ) );
	}

	public function test_shop_vendor_holds_no_payment_capabilities() {
		$user_id = self::$users['shop_vendor'];

		$this->assertTrue(
			user_can( $user_id, 'edit_products' ),
			'Fixture: the Shop Vendor must hold its own product capabilities, or these absences are vacuous.'
		);

		$payment_caps = EDD()->roles->get_core_caps()['shop_payment'];
		$this->assertNotEmpty( $payment_caps );

		foreach ( $payment_caps as $cap ) {
			$this->assertFalse(
				user_can( $user_id, $cap ),
				sprintf( 'The Shop Vendor unexpectedly holds %s.', $cap )
			);
		}
	}

	public function test_subscriber_holds_no_edd_capabilities() {
		global $post_type_meta_caps;

		$user_id = self::$users['subscriber'];

		$this->assertTrue( user_can( $user_id, 'read' ), 'Fixture: the Subscriber must exist and hold its own role.' );

		$caps = array(
			'view_shop_reports',
			'view_shop_sensitive_data',
			'export_shop_reports',
			'manage_shop_settings',
			'manage_shop_discounts',
		);

		foreach ( EDD()->roles->get_core_caps() as $cap_group ) {
			$caps = array_merge( $caps, $cap_group );
		}

		/*
		 * edit_product, read_product and delete_product are registered as post type
		 * meta capabilities, so WordPress resolves them against one download rather
		 * than against a role. Checking them with no object only measures
		 * map_meta_cap()'s missing-object path, which says nothing about the role.
		 */
		$caps = array_diff( $caps, array_keys( (array) $post_type_meta_caps ) );

		$this->assertGreaterThan( 50, count( $caps ) );

		foreach ( $caps as $cap ) {
			$this->assertFalse(
				user_can( $user_id, $cap ),
				sprintf( 'The Subscriber unexpectedly holds %s.', $cap )
			);
		}
	}

	/*
	 * Same-request effect.
	 *
	 * Activation and the reset tool check capabilities in the request that granted
	 * them, so a grant must reach the role objects and the loaded current user, not
	 * only the stored option.
	 */

	public function test_add_caps_is_effective_for_new_user_object_in_same_request() {
		$shop_manager_id = self::factory()->user->create( array( 'role' => 'shop_manager' ) );
		$this->load_roles_without_shop_caps();

		EDD()->roles->add_caps();

		$this->assertTrue( user_can( new \WP_User( $shop_manager_id ), 'manage_shop_settings' ) );
	}

	public function test_add_caps_is_effective_for_current_user_in_same_request() {
		$administrator_id = self::factory()->user->create( array( 'role' => 'administrator' ) );
		$this->load_roles_without_shop_caps();
		wp_set_current_user( $administrator_id );

		EDD()->roles->add_caps();

		$this->assertTrue( current_user_can( 'manage_shop_settings' ) );
	}

	public function test_remove_caps_is_effective_for_current_user_in_same_request() {
		$administrator_id = self::factory()->user->create( array( 'role' => 'administrator' ) );
		wp_roles()->for_site();
		wp_set_current_user( $administrator_id );
		$this->assertTrue( current_user_can( 'manage_shop_settings' ), 'Fixture: the administrator must start with the cap.' );

		EDD()->roles->remove_caps();

		$this->assertFalse( current_user_can( 'manage_shop_settings' ) );
	}

	public function test_role_objects_match_stored_roles_after_add_caps() {
		$this->load_roles_without_shop_caps();

		EDD()->roles->add_caps();

		$stored_roles = get_option( wp_roles()->role_key );
		foreach ( array( 'administrator', 'shop_manager', 'shop_accountant', 'shop_worker', 'shop_vendor' ) as $role ) {
			$this->assertSame( $stored_roles[ $role ]['capabilities'], get_role( $role )->capabilities, $role );
		}
	}

	public function test_add_caps_writes_roles_option_once() {
		$this->load_roles_without_shop_caps();
		$write_count = 0;
		add_action(
			'update_option_' . wp_roles()->role_key,
			function () use ( &$write_count ) {
				++$write_count;
			}
		);

		EDD()->roles->add_caps();

		$this->assertSame( 1, $write_count );
	}

	public function test_remove_caps_revokes_every_cap_add_caps_grants() {
		$shop_roles = array( 'shop_manager', 'shop_accountant', 'shop_worker', 'shop_vendor' );
		foreach ( $shop_roles as $role ) {
			remove_role( $role );
		}
		EDD()->roles->add_roles();
		$base_roles = get_option( wp_roles()->role_key );
		EDD()->roles->add_caps();
		$granted_roles = get_option( wp_roles()->role_key );

		EDD()->roles->remove_caps();

		$revoked_roles = get_option( wp_roles()->role_key );
		foreach ( $shop_roles as $role ) {
			$granted_caps = array_diff_key( $granted_roles[ $role ]['capabilities'], $base_roles[ $role ]['capabilities'] );
			$this->assertNotEmpty( $granted_caps, "Fixture: add_caps() must grant {$role} something, or this is vacuous." );
			$this->assertSame(
				array(),
				array_keys( array_intersect_key( $revoked_roles[ $role ]['capabilities'], $granted_caps ) ),
				$role
			);
		}
	}

	/**
	 * Rebuilds the role objects from roles stored without EDD's capabilities,
	 * the state an activation request starts from.
	 */
	private function load_roles_without_shop_caps() {
		EDD()->roles->remove_caps();
		wp_roles()->for_site();
	}
}
