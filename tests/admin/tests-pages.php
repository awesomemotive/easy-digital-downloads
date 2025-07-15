<?php

namespace EDD\Tests\Admin;

use EDD\Tests\PHPUnit\EDD_UnitTestCase;
use EDD\Admin\Utils\Page;

/**
 * Tests for the Page utility class and edd_is_admin_page function.
 *
 * These tests validate that the new Page utility class is a drop-in replacement
 * for the legacy edd_is_admin_page function, ensuring both implementations return
 * identical results for all scenarios.
 *
 * Each test validates:
 * 1. The Page class behavior through $admin_page->is_admin()
 * 2. The edd_is_admin_page function behavior with identical parameters
 * 3. Both implementations return the same result (assertSame)
 */
class Pages extends EDD_UnitTestCase {

	/**
	 * Clean up after each test.
	 */
	public function tearDown(): void {
		parent::tearDown();

		// Clean up $_GET
		$_GET = array();

		// Reset globals
		global $pagenow, $typenow;
		$pagenow = null;
		$typenow = null;

		// Clear the static cache to prevent test interference
		Page::clear_cache();
	}

	/**
	 * Test basic Page construction and is_admin method when not in admin.
	 */
	public function test_is_admin_returns_false_when_not_in_admin() {
		// Mock not being in admin
		$this->assertFalse( is_admin() );

		$result = Page::is_admin();
		$this->assertFalse( $result );

		// Ensure edd_is_admin_page() matches Page behavior
		$this->assertSame( $result, edd_is_admin_page() );
	}

	/**
	 * Test download page detection with list-table view.
	 */
	public function test_download_list_table_page() {
		$this->set_admin_context();
		$this->set_global_pagenow( 'edit.php' );
		$this->set_global_typenow( 'download' );

		$result = Page::is_admin( 'download', 'list-table' );
		$this->assertTrue( $result );

		// Ensure edd_is_admin_page() matches Page behavior
		$this->assertSame( $result, edd_is_admin_page( 'download', 'list-table' ) );
	}

	/**
	 * Test download page detection with edit view.
	 */
	public function test_download_edit_page() {
		$this->set_admin_context();
		$this->set_global_pagenow( 'post.php' );
		$this->set_global_typenow( 'download' );

		$result = Page::is_admin( 'download', 'edit' );
		$this->assertTrue( $result );

		// Ensure edd_is_admin_page() matches Page behavior
		$this->assertSame( $result, edd_is_admin_page( 'download', 'edit' ) );
	}

	/**
	 * Test download page detection with new view.
	 */
	public function test_download_new_page() {
		$this->set_admin_context();
		$this->set_global_pagenow( 'post-new.php' );
		$this->set_global_typenow( 'download' );

		$result = Page::is_admin( 'download', 'new' );
		$this->assertTrue( $result );

		// Ensure edd_is_admin_page() matches Page behavior
		$this->assertSame( $result, edd_is_admin_page( 'download', 'new' ) );
	}

	/**
	 * Test categories list-table view.
	 */
	public function test_categories_list_table_page() {
		$this->set_admin_context();
		$this->set_global_pagenow( 'edit-tags.php' );
		$this->set_global_typenow( 'download' );
		$_GET['taxonomy'] = 'download_category';

		$result = Page::is_admin( 'categories', 'list-table' );
		$this->assertTrue( $result );

		// Ensure edd_is_admin_page() matches Page behavior
		$this->assertSame( $result, edd_is_admin_page( 'categories', 'list-table' ) );

		unset( $_GET['taxonomy'] );
	}

	/**
	 * Test categories edit view.
	 */
	public function test_categories_edit_page() {
		$this->set_admin_context();
		$this->set_global_pagenow( 'edit-tags.php' );
		$this->set_global_typenow( 'download' );
		$_GET['taxonomy'] = 'download_category';
		$_GET['action'] = 'edit';

		$result = Page::is_admin( 'categories', 'edit' );
		$this->assertTrue( $result );

		// Ensure edd_is_admin_page() matches Page behavior
		$this->assertSame( $result, edd_is_admin_page( 'categories', 'edit' ) );

		unset( $_GET['taxonomy'], $_GET['action'] );
	}

	/**
	 * Test tags list-table view.
	 */
	public function test_tags_list_table_page() {
		$this->set_admin_context();
		$this->set_global_pagenow( 'edit-tags.php' );
		$this->set_global_typenow( 'download' );
		$_GET['taxonomy'] = 'download_tag';

		$result = Page::is_admin( 'tags', 'list-table' );
		$this->assertTrue( $result );

		// Ensure edd_is_admin_page() matches Page behavior
		$this->assertSame( $result, edd_is_admin_page( 'tags', 'list-table' ) );

		unset( $_GET['taxonomy'] );
	}

	/**
	 * Test tags edit view.
	 */
	public function test_tags_edit_page() {
		$this->set_admin_context();
		$this->set_global_pagenow( 'edit-tags.php' );
		$this->set_global_typenow( 'download' );
		$_GET['taxonomy'] = 'download_tag';
		$_GET['action'] = 'edit';

		$result = Page::is_admin( 'tags', 'edit' );
		$this->assertTrue( $result );

		// Ensure edd_is_admin_page() matches Page behavior
		$this->assertSame( $result, edd_is_admin_page( 'tags', 'edit' ) );

		unset( $_GET['taxonomy'], $_GET['action'] );
	}

	/**
	 * Test payments list-table view.
	 */
	public function test_payments_list_table_page() {
		$this->set_admin_context();
		$this->set_global_pagenow( 'edit.php' );
		$this->set_global_typenow( 'download' );
		$_GET['page'] = 'edd-payment-history';

		$result = Page::is_admin( 'payments', 'list-table', true, 'edd-payment-history', false );
		$this->assertTrue( $result );

		// Ensure edd_is_admin_page() matches Page behavior
		$this->assertSame( $result, edd_is_admin_page( 'payments', 'list-table' ) );

		unset( $_GET['page'] );
	}

	/**
	 * Test payments edit view.
	 */
	public function test_payments_edit_page() {
		$this->set_admin_context();
		$this->set_global_pagenow( 'edit.php' );
		$this->set_global_typenow( 'download' );
		$_GET['page'] = 'edd-payment-history';
		$_GET['view'] = 'view-order-details';

		$result = Page::is_admin( 'payments', 'edit', true, 'edd-payment-history', 'view-order-details' );
		$this->assertTrue( $result );

		// Ensure edd_is_admin_page() matches Page behavior
		$this->assertSame( $result, edd_is_admin_page( 'payments', 'edit' ) );

		unset( $_GET['page'], $_GET['view'] );
	}

	/**
	 * Test discounts list-table view.
	 */
	public function test_discounts_list_table_page() {
		$this->set_admin_context();
		$this->set_global_pagenow( 'edit.php' );
		$this->set_global_typenow( 'download' );
		$_GET['page'] = 'edd-discounts';

		$result = Page::is_admin( 'discounts', 'list-table', true, 'edd-discounts', false );
		$this->assertTrue( $result );

		// Ensure edd_is_admin_page() matches Page behavior
		$this->assertSame( $result, edd_is_admin_page( 'discounts', 'list-table' ) );

		unset( $_GET['page'] );
	}

	/**
	 * Test discounts edit view.
	 */
	public function test_discounts_edit_page() {
		$this->set_admin_context();
		$this->set_global_pagenow( 'edit.php' );
		$this->set_global_typenow( 'download' );
		$_GET['page'] = 'edd-discounts';
		$_GET['edd-action'] = 'edit_discount';

		$result = Page::is_admin( 'discounts', 'edit', true, 'edd-discounts', false );
		$this->assertTrue( $result );

		// Ensure edd_is_admin_page() matches Page behavior
		$this->assertSame( $result, edd_is_admin_page( 'discounts', 'edit' ) );

		unset( $_GET['page'], $_GET['edd-action'] );
	}

	/**
	 * Test discounts new view.
	 */
	public function test_discounts_new_page() {
		$this->set_admin_context();
		$this->set_global_pagenow( 'edit.php' );
		$this->set_global_typenow( 'download' );
		$_GET['page'] = 'edd-discounts';
		$_GET['edd-action'] = 'add_discount';

		$result = Page::is_admin( 'discounts', 'new', true, 'edd-discounts', false );
		$this->assertTrue( $result );

		// Ensure edd_is_admin_page() matches Page behavior
		$this->assertSame( $result, edd_is_admin_page( 'discounts', 'new' ) );

		unset( $_GET['page'], $_GET['edd-action'] );
	}

	/**
	 * Test reports earnings view.
	 */
	public function test_reports_earnings_page() {
		$this->set_admin_context();
		$this->set_global_pagenow( 'edit.php' );
		$this->set_global_typenow( 'download' );
		$_GET['page'] = 'edd-reports';
		$_GET['view'] = 'earnings';

		$result = Page::is_admin( 'reports', 'earnings', true, 'edd-reports', 'earnings' );
		$this->assertTrue( $result );

		// Ensure edd_is_admin_page() matches Page behavior
		$this->assertSame( $result, edd_is_admin_page( 'reports', 'earnings' ) );

		unset( $_GET['page'], $_GET['view'] );
	}

	/**
	 * Test reports downloads view.
	 */
	public function test_reports_downloads_page() {
		$this->set_admin_context();
		$this->set_global_pagenow( 'edit.php' );
		$this->set_global_typenow( 'download' );
		$_GET['page'] = 'edd-reports';
		$_GET['view'] = 'downloads';

		$result = Page::is_admin( 'reports', 'downloads', true, 'edd-reports', 'downloads' );
		$this->assertTrue( $result );

		// Ensure edd_is_admin_page() matches Page behavior
		$this->assertSame( $result, edd_is_admin_page( 'reports', 'downloads' ) );

		unset( $_GET['page'], $_GET['view'] );
	}

	/**
	 * Test settings general tab.
	 */
	public function test_settings_general_page() {
		$this->set_admin_context();
		$this->set_global_pagenow( 'edit.php' );
		$this->set_global_typenow( 'download' );
		$_GET['page'] = 'edd-settings';

		$result = Page::is_admin( 'settings', 'general', true, 'edd-settings', false );
		$this->assertTrue( $result );

		// Ensure edd_is_admin_page() matches Page behavior
		$this->assertSame( $result, edd_is_admin_page( 'settings', 'general' ) );

		unset( $_GET['page'] );
	}

	/**
	 * Test settings specific tab.
	 */
	public function test_settings_specific_tab() {
		$this->set_admin_context();
		$this->set_global_pagenow( 'edit.php' );
		$this->set_global_typenow( 'download' );
		$_GET['page'] = 'edd-settings';
		$_GET['tab'] = 'gateways';

		// Mock the edd_get_settings_tabs function
		if ( ! function_exists( 'edd_get_settings_tabs' ) ) {
			function edd_get_settings_tabs() {
				return array(
					'general'  => 'General',
					'gateways' => 'Payment Gateways',
					'emails'   => 'Emails',
					'styles'   => 'Styles',
				);
			}
		}

		$result = Page::is_admin( 'settings', 'gateways', true, 'edd-settings', false );
		$this->assertTrue( $result );

		// Ensure edd_is_admin_page() matches Page behavior
		$this->assertSame( $result, edd_is_admin_page( 'settings', 'gateways' ) );

		unset( $_GET['page'], $_GET['tab'] );
	}

	/**
	 * Test tools general tab.
	 */
	public function test_tools_general_page() {
		$this->set_admin_context();
		$this->set_global_pagenow( 'edit.php' );
		$this->set_global_typenow( 'download' );
		$_GET['page'] = 'edd-tools';

		$result = Page::is_admin( 'tools', 'general', true, 'edd-tools', false );
		$this->assertTrue( $result );

		// Ensure edd_is_admin_page() matches Page behavior
		$this->assertSame( $result, edd_is_admin_page( 'tools', 'general' ) );

		unset( $_GET['page'] );
	}

	/**
	 * Test tools api_keys tab.
	 */
	public function test_tools_api_keys_page() {
		$this->set_admin_context();
		$this->set_global_pagenow( 'edit.php' );
		$this->set_global_typenow( 'download' );
		$_GET['page'] = 'edd-tools';
		$_GET['tab'] = 'api_keys';

		$result = Page::is_admin( 'tools', 'api_keys', true, 'edd-tools', false );
		$this->assertTrue( $result );

		// Ensure edd_is_admin_page() matches Page behavior
		$this->assertSame( $result, edd_is_admin_page( 'tools', 'api_keys' ) );

		unset( $_GET['page'], $_GET['tab'] );
	}

	/**
	 * Test customers list-table view.
	 */
	public function test_customers_list_table_page() {
		$this->set_admin_context();
		$this->set_global_pagenow( 'edit.php' );
		$this->set_global_typenow( 'download' );
		$_GET['page'] = 'edd-customers';

		$result = Page::is_admin( 'customers', 'list-table', true, 'edd-customers', false );
		$this->assertTrue( $result );

		// Ensure edd_is_admin_page() matches Page behavior
		$this->assertSame( $result, edd_is_admin_page( 'customers', 'list-table' ) );

		unset( $_GET['page'] );
	}

	/**
	 * Test customers overview view.
	 */
	public function test_customers_overview_page() {
		$this->set_admin_context();
		$this->set_global_pagenow( 'edit.php' );
		$this->set_global_typenow( 'download' );
		$_GET['page'] = 'edd-customers';
		$_GET['view'] = 'overview';

		$result = Page::is_admin( 'customers', 'overview', true, 'edd-customers', 'overview' );
		$this->assertTrue( $result );

		// Ensure edd_is_admin_page() matches Page behavior
		$this->assertSame( $result, edd_is_admin_page( 'customers', 'overview' ) );

		unset( $_GET['page'], $_GET['view'] );
	}

	/**
	 * Test addons page.
	 */
	public function test_addons_page() {
		$this->set_admin_context();
		$this->set_global_pagenow( 'edit.php' );
		$this->set_global_typenow( 'download' );
		$_GET['page'] = 'edd-addons';

		$result = Page::is_admin( 'addons', '', true, 'edd-addons', false );
		$this->assertTrue( $result );

		// Ensure edd_is_admin_page() matches Page behavior
		$this->assertSame( $result, edd_is_admin_page( 'addons', '' ) );

		unset( $_GET['page'] );
	}

	/**
	 * Test index.php page.
	 */
	public function test_index_page() {
		$this->set_admin_context();
		$this->set_global_pagenow( 'index.php' );

		$result = Page::is_admin( 'index.php' );
		$this->assertTrue( $result );

		// Ensure edd_is_admin_page() matches Page behavior
		$this->assertSame( $result, edd_is_admin_page( 'index.php' ) );
	}

	/**
	 * Test default case with no specific page.
	 */
	public function test_default_case_with_download_typenow() {
		$this->set_admin_context();
		$this->set_global_typenow( 'download' );

		$result = Page::is_admin();
		$this->assertTrue( $result );

		// Ensure edd_is_admin_page() matches Page behavior
		$this->assertSame( $result, edd_is_admin_page() );
	}

	/**
	 * Test default case with include_non_exclusive false.
	 */
	public function test_default_case_non_exclusive_false() {
		// Set admin context with users screen (not download-related)
		$this->set_admin_context( 'users' );

		$this->set_global_pagenow( 'users.php' ); // Use a core WP admin page that's not an EDD page
		// Explicitly ensure no download-related globals are set
		$this->set_global_typenow( null );

		// Ensure $_GET is clean
		$_GET = array();

		$result = Page::is_admin( '', '', false );
		$this->assertFalse( $result );

		// Ensure edd_is_admin_page() matches Page behavior
		$this->assertSame( $result, edd_is_admin_page( '', '', false ) );
	}

	/**
	 * Test post_type parameter instead of typenow.
	 */
	public function test_download_post_type_parameter() {
		$this->set_admin_context();
		$this->set_global_pagenow( 'edit.php' );
		$_GET['post_type'] = 'download';

		$result = Page::is_admin( 'download', 'list-table' );
		$this->assertTrue( $result );

		// Ensure edd_is_admin_page() matches Page behavior
		$this->assertSame( $result, edd_is_admin_page( 'download', 'list-table' ) );

		unset( $_GET['post_type'] );
	}

	/**
	 * Test invalid taxonomy for categories.
	 */
	public function test_categories_invalid_taxonomy() {
		$this->set_admin_context();
		$this->set_global_pagenow( 'edit-tags.php' );
		$this->set_global_typenow( 'download' );
		$_GET['taxonomy'] = 'invalid_taxonomy';

		$result = Page::is_admin( 'categories', 'list-table' );
		$this->assertFalse( $result );

		// Ensure edd_is_admin_page() matches Page behavior
		$this->assertSame( $result, edd_is_admin_page( 'categories', 'list-table' ) );

		unset( $_GET['taxonomy'] );
	}

	/**
	 * Test parameter sanitization.
	 */
	public function test_parameter_sanitization() {
		$this->set_admin_context();
		$this->set_global_pagenow( 'edit.php' );
		$this->set_global_typenow( 'download' );
		$_GET['page'] = 'EDD-SETTINGS'; // Uppercase to test lowercasing
		$_GET['tab'] = 'GENERAL';

		$result = Page::is_admin( 'settings', 'general', true, 'edd-settings', false );
		$this->assertTrue( $result );

		// Ensure edd_is_admin_page() matches Page behavior
		$this->assertSame( $result, edd_is_admin_page( 'settings', 'general' ) );

		unset( $_GET['page'], $_GET['tab'] );
	}

	/**
	 * Test non-string parameters are ignored.
	 */
	public function test_non_string_parameters_ignored() {
		$this->set_admin_context();
		$this->set_global_pagenow( 'admin.php' ); // Use a different pagenow that won't trigger edit.php logic
		// Don't set typenow to 'download' to avoid default detection
		$_GET['page'] = array( 'edd-settings' ); // Array instead of string

		// Test with a specific page that should fail due to the array parameter
		$result = Page::is_admin( 'settings', 'general', false, 'edd-settings', false );

		// The cast_value() method should return false for the array,
		// and since we're not on a download screen and include_non_exclusive is false,
		// this should return false
		$this->assertFalse( $result );

		// Ensure edd_is_admin_page() matches Page behavior
		$this->assertSame( $result, edd_is_admin_page( 'settings', 'general', false ) );

		unset( $_GET['page'] );
	}

	/**
	 * Helper method to set admin context.
	 */
	private function set_admin_context( $screen = 'edit-download' ) {
		set_current_screen( $screen );
	}

	/**
	 * Helper method to set global $pagenow.
	 */
	private function set_global_pagenow( $pagenow ) {
		global $GLOBALS;
		$GLOBALS['pagenow'] = $pagenow;
	}

	/**
	 * Helper method to set global $typenow.
	 */
	private function set_global_typenow( $typenow ) {
		global $GLOBALS;
		$GLOBALS['typenow'] = $typenow;
	}

	/**
	 * Test the static caching method directly.
	 */
	public function test_static_caching_method() {
		$this->set_admin_context();
		$this->set_global_pagenow( 'edit.php' );
		$this->set_global_typenow( 'download' );
		$_GET['page'] = 'edd-settings';

		// Clear cache to start fresh
		Page::clear_cache();

		// First call should create cache entry
		$result1 = Page::is_admin( 'settings', 'general' );
		$this->assertTrue( $result1 );

		// Second call should use cache
		$result2 = Page::is_admin( 'settings', 'general' );
		$this->assertTrue( $result2 );
		$this->assertSame( $result1, $result2 );

		// Ensure edd_is_admin_page() still works and returns same result
		$this->assertSame( $result1, edd_is_admin_page( 'settings', 'general' ) );

		unset( $_GET['page'] );
	}

	/**
	 * Test cache invalidation when state changes.
	 */
	public function test_cache_invalidation_on_state_change() {
		$this->set_admin_context();
		$this->set_global_pagenow( 'edit.php' );
		$this->set_global_typenow( 'download' );

		// Clear cache
		Page::clear_cache();

		// First call without $_GET['page']
		$result1 = Page::is_admin( 'settings', 'general' );
		$this->assertFalse( $result1 );

		// Add $_GET['page'] to simulate state change during request
		$_GET['page'] = 'edd-settings';

		// Second call should detect the change and return different result
		$result2 = Page::is_admin( 'settings', 'general' );
		$this->assertTrue( $result2 );
		$this->assertNotSame( $result1, $result2 );

		// Ensure edd_is_admin_page() behaves the same way
		$this->assertSame( $result2, edd_is_admin_page( 'settings', 'general' ) );

		unset( $_GET['page'] );
	}

	/**
	 * Test that cache handles different parameter combinations correctly.
	 */
	public function test_cache_parameter_combinations() {
		$this->set_admin_context();
		$this->set_global_pagenow( 'edit.php' );
		$this->set_global_typenow( 'download' );
		$_GET['page'] = 'edd-settings';

		// Clear cache
		Page::clear_cache();

		// Different parameter combinations should have different cache entries
		$result1 = Page::is_admin( 'settings', 'general' );
		$result2 = Page::is_admin( 'settings', 'gateways' );
		$result3 = Page::is_admin( 'reports', 'earnings' );

		$this->assertTrue( $result1 );
		$this->assertTrue( $result2 );
		$this->assertFalse( $result3 ); // Should be false since page is edd-settings, not edd-reports

		// Ensure edd_is_admin_page() returns same results
		$this->assertSame( $result1, edd_is_admin_page( 'settings', 'general' ) );
		$this->assertSame( $result2, edd_is_admin_page( 'settings', 'gateways' ) );
		$this->assertSame( $result3, edd_is_admin_page( 'reports', 'earnings' ) );

		unset( $_GET['page'] );
	}
}
