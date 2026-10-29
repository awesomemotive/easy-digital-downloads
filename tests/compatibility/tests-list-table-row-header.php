<?php
/**
 * Tests for WordPress 7.1 list table row header compatibility.
 *
 * WordPress 7.1 moved the row header in `WP_List_Table::single_row_columns()` from
 * the checkbox column to the primary column: the checkbox cell became a `td` and the
 * primary column became a `th scope="row"`. EDD inherits that markup, so the only
 * things that can break are selectors which assumed the checkbox lived in a `th`.
 *
 * These tests guard two things:
 *
 * 1. EDD list tables still inherit core's row markup, so the assumption above holds.
 * 2. The refund modal styles use selectors that work on WordPress 6.7 to 7.1+, in both the
 *    source and the built stylesheets.
 *
 * @package     EDD\Tests\Compatibility
 * @copyright   Copyright (c) 2026, Sandhills Development, LLC
 * @license     https://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       3.7.0
 */

namespace EDD\Tests\Compatibility;

use EDD\Tests\PHPUnit\EDD_UnitTestCase;

/**
 * @group compatibility
 * @group edd_list_tables
 */
class ListTableRowHeader extends EDD_UnitTestCase {

	/**
	 * EDD list tables which render rows through `WP_List_Table::single_row_columns()`.
	 *
	 * Keyed by class name, valued by the file which declares it, relative to the plugin
	 * directory. PSR-4 autoloaded classes use an empty path.
	 *
	 * @var array
	 */
	private static $tables = array(
		'EDD_Payment_History_Table'          => 'includes/admin/payments/class-payments-table.php',
		'EDD_Customer_Reports_Table'         => 'includes/admin/customers/class-customer-table.php',
		'EDD_Customer_Addresses_Table'       => 'includes/admin/customers/class-customer-addresses-table.php',
		'EDD_Customer_Email_Addresses_Table' => 'includes/admin/customers/class-customer-email-addresses-table.php',
		'EDD\Admin\Discounts\ListTable'      => '',
		'EDD\Admin\Emails\ListTable'         => '',
		'EDD\Admin\Refund_Items_Table'       => '',
	);

	/**
	 * Loads the list table classes once for the whole class.
	 */
	public static function setUpBeforeClass(): void {
		parent::setUpBeforeClass();

		if ( ! class_exists( 'WP_List_Table' ) ) {
			require_once ABSPATH . 'wp-admin/includes/class-wp-list-table.php';
		}

		foreach ( self::$tables as $class => $file ) {
			if ( class_exists( $class ) || empty( $file ) ) {
				continue;
			}

			$path = EDD_PLUGIN_DIR . $file;
			if ( file_exists( $path ) ) {
				require_once $path;
			}
		}
	}

	/**
	 * No EDD list table may override `single_row_columns()`, otherwise it would keep
	 * emitting the pre-7.1 row structure.
	 *
	 * @dataProvider list_table_provider
	 * @param string $class The list table class name.
	 */
	public function test_list_table_inherits_single_row_columns( $class ) {
		if ( ! class_exists( $class ) ) {
			$this->markTestSkipped( sprintf( '%s is not available in this test run.', $class ) );
		}

		$method = new \ReflectionMethod( $class, 'single_row_columns' );

		$this->assertSame(
			'WP_List_Table',
			$method->getDeclaringClass()->getName(),
			sprintf( '%s must inherit single_row_columns() from WP_List_Table so it picks up the 7.1 row header.', $class )
		);
	}

	/**
	 * No EDD list table may define a `_column_*` method, which would bypass the
	 * primary column handling in `single_row_columns()`.
	 *
	 * @dataProvider list_table_provider
	 * @param string $class The list table class name.
	 */
	public function test_list_table_defines_no_underscore_column_methods( $class ) {
		if ( ! class_exists( $class ) ) {
			$this->markTestSkipped( sprintf( '%s is not available in this test run.', $class ) );
		}

		$reflection = new \ReflectionClass( $class );
		$found      = array();

		foreach ( $reflection->getMethods() as $method ) {
			if ( 0 === strpos( $method->getName(), '_column_' ) ) {
				$found[] = $method->getName();
			}
		}

		$this->assertSame( array(), $found, sprintf( '%s should not define _column_* methods.', $class ) );
	}

	/**
	 * Every EDD list table declares an explicit primary column, so the cell which
	 * becomes the row header does not depend on core's fallback detection.
	 *
	 * @dataProvider list_table_provider
	 * @param string $class The list table class name.
	 */
	public function test_list_table_declares_a_primary_column( $class ) {
		if ( ! class_exists( $class ) ) {
			$this->markTestSkipped( sprintf( '%s is not available in this test run.', $class ) );
		}

		$method = new \ReflectionMethod( $class, 'get_primary_column_name' );

		$this->assertNotSame(
			'WP_List_Table',
			$method->getDeclaringClass()->getName(),
			sprintf( '%s should declare its own primary column.', $class )
		);
	}

	/**
	 * The refund modal must align the primary column, which is the row header on 7.1,
	 * while keeping the checkbox column rule for older versions.
	 */
	public function test_refund_modal_source_aligns_both_row_header_cells() {
		$source = $this->get_file_contents( 'assets/src/scss/admin/orders/_refunds-modal.scss' );

		// The checkbox cell needs both elements: core's `.widefat .check-column` sets
		// `vertical-align: top` at a higher specificity than a bare `td` selector.
		$this->assertStringContainsString( 'td.check-column', $source );
		$this->assertStringContainsString( 'th.check-column', $source );
		$this->assertStringContainsString( 'th.column-primary', $source );
	}

	/**
	 * The compiled admin stylesheet must carry the refund modal rule for both cells.
	 */
	public function test_refund_modal_built_css_aligns_both_row_header_cells() {
		foreach ( array( 'assets/build/css/admin/admin.min.css', 'assets/build/css/admin/admin-rtl.min.css' ) as $file ) {
			$css = $this->get_file_contents( $file );

			$this->assertStringContainsString( '.refund-items td.check-column', $css, sprintf( '%s should out-specify core when the checkbox column is a td.', $file ) );
			$this->assertStringContainsString( '.refund-items th.check-column', $css, sprintf( '%s should still align the checkbox column.', $file ) );
			$this->assertStringContainsString( '.refund-items th.column-primary', $css, sprintf( '%s should align the primary column.', $file ) );
		}
	}

	/**
	 * Data provider for the list table classes.
	 *
	 * @return array
	 */
	public function list_table_provider() {
		$data = array();
		foreach ( array_keys( self::$tables ) as $class ) {
			$data[ $class ] = array( $class );
		}

		return $data;
	}

	/**
	 * Reads a file from the plugin directory.
	 *
	 * @param string $file Path relative to the plugin directory.
	 * @return string
	 */
	private function get_file_contents( $file ) {
		$path = EDD_PLUGIN_DIR . $file;

		$this->assertFileExists( $path, sprintf( '%s should exist. Has the asset been renamed or the build not been run?', $file ) );

		return (string) file_get_contents( $path ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents
	}
}
