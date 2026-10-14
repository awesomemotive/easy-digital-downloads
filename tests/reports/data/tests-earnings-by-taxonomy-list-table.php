<?php
/**
 * Tests for the Earnings by Taxonomy list table.
 *
 * @package     EDD\Tests\Reports\Data
 * @copyright   Copyright (c) 2026, Sandhills Development, LLC
 * @license     https://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       3.7.2
 */

namespace EDD\Tests\Reports\Data;

defined( 'ABSPATH' ) || exit; // @codeCoverageIgnore

use EDD\Tests\PHPUnit\EDD_UnitTestCase;
use EDD\Reports\Data\Downloads\Earnings_By_Taxonomy_List_Table;

/**
 * Earnings by Taxonomy list table tests.
 *
 * @since 3.7.2
 *
 * @group edd_reports
 *
 * @coversDefaultClass \EDD\Reports\Data\Downloads\Earnings_By_Taxonomy_List_Table
 */
class EarningsByTaxonomyListTable extends EDD_UnitTestCase {

	/**
	 * Top-level category assigned to both downloads.
	 *
	 * @var array
	 */
	private $parent_category;

	/**
	 * Child category assigned to one download.
	 *
	 * @var array
	 */
	private $child_category;

	/**
	 * The screen set before the test ran.
	 *
	 * @var \WP_Screen|null
	 */
	private $original_screen;

	/**
	 * Loads the reports functions the table reads its filters through.
	 */
	public static function wpSetUpBeforeClass() {
		\EDD\Reports\Init::bootstrap();
	}

	/**
	 * Creates two downloads with one sale each, filed under a parent and a child category.
	 */
	public function set_up() {
		parent::set_up();

		$this->original_screen = $GLOBALS['current_screen'] ?? null;
		set_current_screen( 'edit.php' );
		set_transient( 'edd_earnings_by_taxonomy_show_report', true );

		$this->parent_category = wp_insert_term( 'Parent Category', 'download_category', array( 'description' => 'Top-level category.' ) );
		$this->child_category  = wp_insert_term( 'Child Category', 'download_category', array( 'parent' => $this->parent_category['term_id'] ) );

		$categorized_download = self::factory()->post->create( array( 'post_type' => 'download' ) );
		$parent_only_download = self::factory()->post->create( array( 'post_type' => 'download' ) );
		wp_set_object_terms( $categorized_download, array( $this->parent_category['term_id'], $this->child_category['term_id'] ), 'download_category' );
		wp_set_object_terms( $parent_only_download, array( $this->parent_category['term_id'] ), 'download_category' );

		foreach ( array( $categorized_download, $parent_only_download ) as $download_id ) {
			parent::edd()->order_item->create(
				array(
					'order_id'   => parent::edd()->order->create(),
					'product_id' => $download_id,
					'status'     => 'complete',
				)
			);
		}
	}

	/**
	 * Restores the screen and removes the report transient.
	 */
	public function tear_down() {
		delete_transient( 'edd_earnings_by_taxonomy_show_report' );
		if ( is_null( $this->original_screen ) ) {
			unset( $GLOBALS['current_screen'] );
		} else {
			$GLOBALS['current_screen'] = $this->original_screen;
		}

		parent::tear_down();
	}

	/**
	 * Term descriptions are never displayed, and loading one per assignment exhausts memory on large stores.
	 *
	 * @covers ::get_data()
	 */
	public function test_taxonomy_query_does_not_load_term_descriptions() {
		$taxonomy_query = '';
		$capture_query  = function ( $query ) use ( &$taxonomy_query ) {
			if ( false !== strpos( $query, 'term_relationships' ) ) {
				$taxonomy_query = $query;
			}

			return $query;
		};

		add_filter( 'query', $capture_query );
		( new Earnings_By_Taxonomy_List_Table() )->get_data();
		remove_filter( 'query', $capture_query );

		global $wpdb;
		$rows = $wpdb->get_results( $taxonomy_query );

		$this->assertNotEmpty( $rows );
		$this->assertFalse( property_exists( $rows[0], 'description' ) );
	}

	/**
	 * A term's sales are the sum of the sales of every download assigned to it.
	 *
	 * @covers ::get_data()
	 */
	public function test_term_sales_sum_every_assigned_download() {
		$rows = $this->get_rows_by_term_id();

		$this->assertSame( 2, $rows[ $this->parent_category['term_id'] ]->sales );
		$this->assertSame( 1, $rows[ $this->child_category['term_id'] ]->sales );
	}

	/**
	 * Child terms carry their parent's ID, which the Name column indents by.
	 *
	 * @covers ::get_data()
	 */
	public function test_child_term_row_references_its_parent() {
		$rows = $this->get_rows_by_term_id();

		$this->assertSame( 'Child Category', $rows[ $this->child_category['term_id'] ]->name );
		$this->assertSame( $this->parent_category['term_id'], $rows[ $this->child_category['term_id'] ]->parent );
		$this->assertNull( $rows[ $this->parent_category['term_id'] ]->parent );
	}

	/**
	 * Gets the table rows keyed by term ID.
	 *
	 * @return \stdClass[]
	 */
	private function get_rows_by_term_id() {
		$rows = array();
		foreach ( ( new Earnings_By_Taxonomy_List_Table() )->get_data() as $row ) {
			$rows[ $row->id ] = $row;
		}

		return $rows;
	}
}
