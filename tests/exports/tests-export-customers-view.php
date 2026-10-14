<?php
/**
 * Tests for the Export Customers view.
 *
 * @package   EDD\Tests\Exports
 * @copyright Copyright (c) 2026, Sandhills Development, LLC
 * @license https://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since 3.7.2
 */

namespace EDD\Tests\Exports;

defined( 'ABSPATH' ) || exit; // @codeCoverageIgnore

use EDD\Tests\PHPUnit\EDD_UnitTestCase;

/**
 * Export Customers view tests.
 *
 * @since 3.7.2
 *
 * @group edd_exports
 */
class ExportCustomersView extends EDD_UnitTestCase {

	/**
	 * Category assigned to several published downloads.
	 *
	 * @var array
	 */
	private $shared_category;

	/**
	 * Category assigned only to a draft download.
	 *
	 * @var array
	 */
	private $draft_only_category;

	/**
	 * Category with no assignments.
	 *
	 * @var array
	 */
	private $unused_category;

	/**
	 * Tag assigned to a published download.
	 *
	 * @var array
	 */
	private $tag;

	/**
	 * Creates the terms and the downloads they are assigned to.
	 */
	public function set_up() {
		parent::set_up();

		$this->shared_category     = wp_insert_term( 'Shared Category', 'download_category', array( 'description' => 'Assigned to several downloads.' ) );
		$this->draft_only_category = wp_insert_term( 'Draft Only Category', 'download_category' );
		$this->unused_category     = wp_insert_term( 'Unused Category', 'download_category' );
		$this->tag                 = wp_insert_term( 'Some Tag', 'download_tag' );

		$published_downloads = self::factory()->post->create_many( 3, array( 'post_type' => 'download' ) );
		foreach ( $published_downloads as $download_id ) {
			wp_set_object_terms( $download_id, array( $this->shared_category['term_id'] ), 'download_category' );
		}
		wp_set_object_terms( $published_downloads[0], array( $this->tag['term_id'] ), 'download_tag' );

		$draft_download = self::factory()->post->create(
			array(
				'post_type'   => 'download',
				'post_status' => 'draft',
			)
		);
		wp_set_object_terms( $draft_download, array( $this->draft_only_category['term_id'] ), 'download_category' );
	}

	/**
	 * Rolls back the per-test transaction.
	 */
	public function tear_down() {
		parent::tear_down();
	}

	/**
	 * The dropdown needs one row per term, so its cost must not grow with the number of assignments.
	 */
	public function test_taxonomy_query_returns_one_row_per_term() {
		$taxonomy_query = '';
		$capture_query  = function ( $query ) use ( &$taxonomy_query ) {
			// Keep the view's query: the product dropdown rendered after it can prime the term cache with its own join.
			if ( '' === $taxonomy_query && false !== strpos( $query, 'term_relationships' ) ) {
				$taxonomy_query = $query;
			}

			return $query;
		};

		add_filter( 'query', $capture_query );
		$this->render_view();
		remove_filter( 'query', $capture_query );

		global $wpdb;
		$shared_category_rows = array_filter(
			$wpdb->get_results( $taxonomy_query ),
			function ( $row ) {
				return absint( $row->term_id ) === absint( $this->shared_category['term_id'] );
			}
		);

		$this->assertCount( 1, $shared_category_rows );
	}

	/**
	 * Options are keyed by term ID and labeled with the taxonomy name.
	 */
	public function test_term_assigned_to_several_downloads_is_listed_once() {
		$options = $this->get_taxonomy_options();

		$this->assertSame( 1, count( array_keys( array_column( $options, 'value' ), (string) $this->shared_category['term_id'], true ) ) );
		$this->assertContains(
			array(
				'value' => (string) $this->shared_category['term_id'],
				'label' => get_taxonomy( 'download_category' )->labels->singular_name . ': Shared Category',
			),
			$options
		);
	}

	/**
	 * Term counts include published downloads only, so the dropdown must not rely on them.
	 */
	public function test_term_assigned_only_to_draft_download_is_listed() {
		$this->assertContains( (string) $this->draft_only_category['term_id'], array_column( $this->get_taxonomy_options(), 'value' ) );
	}

	/**
	 * The dropdown spans every download taxonomy, not only categories.
	 */
	public function test_download_tag_is_listed() {
		$this->assertContains(
			array(
				'value' => (string) $this->tag['term_id'],
				'label' => get_taxonomy( 'download_tag' )->labels->singular_name . ': Some Tag',
			),
			$this->get_taxonomy_options()
		);
	}

	/**
	 * Only terms assigned to a download are offered.
	 */
	public function test_unassigned_term_is_not_listed() {
		$this->assertNotContains( (string) $this->unused_category['term_id'], array_column( $this->get_taxonomy_options(), 'value' ) );
	}

	/**
	 * Renders the Export Customers view.
	 *
	 * @return string
	 */
	private function render_view() {
		ob_start();
		include EDD_PLUGIN_DIR . 'includes/admin/reporting/views/export-customers.php';

		return ob_get_clean();
	}

	/**
	 * Gets the options of the rendered "Select Taxonomy" dropdown, skipping "All Taxonomies".
	 *
	 * @return array[] Each option's value and label.
	 */
	private function get_taxonomy_options() {
		$document = new \DOMDocument();
		libxml_use_internal_errors( true );
		$document->loadHTML( '<?xml encoding="utf-8"?>' . $this->render_view() );
		libxml_clear_errors();

		$options = array();
		$select  = $document->getElementById( 'edd_export_taxonomy' );
		foreach ( $select->getElementsByTagName( 'option' ) as $option ) {
			if ( 'all' === $option->getAttribute( 'value' ) ) {
				continue;
			}
			$options[] = array(
				'value' => $option->getAttribute( 'value' ),
				'label' => trim( $option->textContent ),
			);
		}

		return $options;
	}
}
