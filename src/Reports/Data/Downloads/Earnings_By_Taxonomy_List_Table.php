<?php
/**
 * Earnings by Taxonomy list table.
 *
 * @package     EDD
 * @subpackage  Reports/Data/File_Downloads
 * @copyright   Copyright (c) 2018, Easy Digital Downloads, LLC
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       3.0
 */

namespace EDD\Reports\Data\Downloads;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

use EDD\Reports;
use EDD\Admin\List_Table;
use EDD\Stats;

/**
 * Earnings_By_Taxonomy_List_Table class.
 *
 * @since 3.0
 */
class Earnings_By_Taxonomy_List_Table extends List_Table {

	/**
	 * Whether or not to show the warning.
	 *
	 * @since 3.2.7
	 * @var   bool
	 */
	private $show_warning;

	/**
	 * Query the database and fetch the top five most downloaded products.
	 *
	 * @since 3.0
	 *
	 * @return array Taxonomies.
	 */
	public function get_data() {
		if ( $this->show_warning() ) {
			return array();
		}
		global $wpdb;

		$dates    = Reports\get_filter_value( 'dates' );
		$currency = Reports\get_filter_value( 'currencies' );

		$taxonomies = edd_get_download_taxonomies();
		$taxonomies = array_map( 'sanitize_text_field', $taxonomies );

		$placeholders = implode( ', ', array_fill( 0, count( $taxonomies ), '%s' ) );

		$taxonomy__in = $wpdb->prepare( "tt.taxonomy IN ({$placeholders})", $taxonomies );

		$sql = "SELECT t.*, tt.*, tr.object_id
				FROM {$wpdb->terms} AS t
				INNER JOIN {$wpdb->term_taxonomy} AS tt ON t.term_id = tt.term_id
				INNER JOIN {$wpdb->term_relationships} AS tr ON tr.term_taxonomy_id = tt.term_taxonomy_id
				WHERE {$taxonomy__in}";

		$results = $wpdb->get_results( $sql );

		// Build intermediate array to allow for better data processing.
		$taxonomies = array();
		foreach ( $results as $r ) {
			if ( isset( $taxonomies[ absint( $r->term_id ) ] ) ) {
				$taxonomies[ absint( $r->term_id ) ]['object_ids'][] = absint( $r->object_id );
				continue;
			}
			$taxonomies[ absint( $r->term_id ) ]['name']         = esc_html( $r->name );
			$taxonomies[ absint( $r->term_id ) ]['object_ids'][] = absint( $r->object_id );
			$taxonomies[ absint( $r->term_id ) ]['parent']       = absint( $r->parent );
		}

		// Setup an empty array for the final returned data.
		$data = array();

		// Store each download's stats during the loop to avoid double queries.
		$download_stats = array();

		foreach ( $taxonomies as $k => $t ) {
			$c       = new \stdClass();
			$c->id   = $k;
			$c->name = $taxonomies[ $k ]['name'];

			$earnings = 0.00;
			$sales    = 0;

			$average_earnings = 0.00;
			$average_sales    = 0;

			foreach ( $taxonomies[ $k ]['object_ids'] as $download_id ) {
				if ( ! isset( $download_stats[ $download_id ] ) ) {
					$stats = new Stats(
						array(
							'product_id' => absint( $download_id ),
							'currency'   => $currency,
							'range'      => $dates['range'],
							'output'     => 'typed',
						)
					);

					$download_stats[ $download_id ]['earnings'] = $stats->get_order_item_earnings(
						array(
							'function' => 'SUM',
						)
					);

					$download_stats[ $download_id ]['sales'] = $stats->get_order_item_count(
						array(
							'function' => 'COUNT',
						)
					);

					$download_stats[ $download_id ]['average_earnings'] = edd_get_average_monthly_download_earnings( $download_id );
					$download_stats[ $download_id ]['average_sales']    = edd_get_average_monthly_download_sales( $download_id );
				}

				$earnings += $download_stats[ $download_id ]['earnings'];
				$sales    += $download_stats[ $download_id ]['sales'];

				$average_earnings += $download_stats[ $download_id ]['average_earnings'];
				$average_sales    += $download_stats[ $download_id ]['average_sales'];
			}

			$c->sales    = $sales;
			$c->earnings = $earnings;
			$c->parent   = 0 === $t['parent']
				? null
				: $t['parent'];

			$c->average_sales    = $average_sales;
			$c->average_earnings = $average_earnings;

			$data[] = $c;
		}

		$sorted_data = array();

		foreach ( $data as $d ) {

			// Get parent level elements.
			if ( null === $d->parent ) {
				$sorted_data[] = $d;

				$objects = array_values( wp_filter_object_list( $data, array( 'parent' => $d->id ) ) );

				foreach ( $objects as $o ) {
					$sorted_data[] = $o;
				}
			}
		}

		// Sort by total earnings.
		usort(
			$sorted_data,
			function ( $a, $b ) {
				return ( $a->earnings < $b->earnings ) ? -1 : 1;
			}
		);

		return $sorted_data;
	}

	/**
	 * Retrieve the table columns.
	 *
	 * @since 3.0
	 *
	 * @return array $columns Array of all the list table columns
	 */
	public function get_columns() {
		return array(
			'name'             => __( 'Name', 'easy-digital-downloads' ),
			'sales'            => __( 'Total Sales', 'easy-digital-downloads' ),
			'earnings'         => __( 'Total Earnings', 'easy-digital-downloads' ),
			'average_sales'    => __( 'Monthly Sales Average', 'easy-digital-downloads' ),
			'average_earnings' => __( 'Monthly Earnings Average', 'easy-digital-downloads' ),
		);
	}

	/**
	 * Render the Name Column.
	 *
	 * @since 3.0
	 *
	 * @param \stdClass $taxonomy Taxonomy object.
	 * @return string Data shown in the Name column.
	 */
	public function column_name( $taxonomy ) {
		return 0 < $taxonomy->parent
			? '&#8212; ' . $taxonomy->name
			: $taxonomy->name;
	}

	/**
	 * Render the Sales Column.
	 *
	 * @since 3.0
	 *
	 * @param \stdClass $taxonomy Taxonomy object.
	 * @return string Data shown in the Sales column.
	 */
	public function column_sales( $taxonomy ) {
		return $taxonomy->sales;
	}

	/**
	 * Render the Earnings Column.
	 *
	 * @since 3.0
	 *
	 * @param \stdClass $taxonomy Taxonomy object.
	 * @return string Data shown in the Earnings column.
	 */
	public function column_earnings( $taxonomy ) {
		return edd_currency_filter( edd_format_amount( $taxonomy->earnings ) );
	}

	/**
	 * Render the Average Sales Column.
	 *
	 * @since 3.0
	 *
	 * @param \stdClass $taxonomy Taxonomy object.
	 * @return int Data shown in the Average Sales column.
	 */
	public function column_average_sales( $taxonomy ) {
		return (int) round( $taxonomy->average_sales );
	}

	/**
	 * Render the Average Earnings Column.
	 *
	 * @since 3.0
	 *
	 * @param \stdClass $taxonomy Taxonomy object.
	 * @return string Data shown in the Average Earnings column.
	 */
	public function column_average_earnings( $taxonomy ) {
		return edd_currency_filter( edd_format_amount( $taxonomy->average_earnings ) );
	}

	/**
	 * Setup the final data for the table.
	 *
	 * @since 3.0
	 */
	public function prepare_items() {
		$columns  = $this->get_columns();
		$hidden   = array();
		$sortable = $this->get_sortable_columns();

		$this->_column_headers = array( $columns, $hidden, $sortable );
		$this->items           = $this->get_data();
	}

	/**
	 * Message to be displayed when there are no items
	 *
	 * @since 3.0
	 */
	public function no_items() {
		if ( $this->show_warning() ) {
			?>
			<p style="text-align:center;">
				<?php
				esc_html_e(
					'Due to the large number of products or terms on your site, this report may be slow or sometimes fail to load.',
					'easy-digital-downloads'
				);
				?>
			</p>
			<p style="text-align:center;">
				<a href="
				<?php
					echo esc_url(
						add_query_arg(
							'edd-action',
							'show_downloads_taxonomy_report'
						)
					);
				?>
				" class="button button-primary"
				>
					<?php esc_html_e( 'Continue to Report', 'easy-digital-downloads' ); ?>
				</a>
			</p>
			<?php
			return;
		}

		esc_html_e( 'No taxonomies found.', 'easy-digital-downloads' );
	}

	/**
	 * Gets the name of the primary column.
	 *
	 * @since 3.0
	 * @access protected
	 *
	 * @return string Name of the primary column.
	 */
	protected function get_primary_column_name() {
		return 'name';
	}

	/**
	 * Return empty array to disable sorting.
	 *
	 * @since 3.0
	 *
	 * @return array
	 */
	public function get_sortable_columns() {
		return array();
	}

	/**
	 * Return empty array to remove bulk actions.
	 *
	 * @since 3.0
	 *
	 * @return array
	 */
	public function get_bulk_actions() {
		return array();
	}

	/**
	 * Hide pagination.
	 *
	 * @since 3.0
	 *
	 * @param string $which
	 */
	protected function pagination( $which ) {
	}

	/**
	 * Hide table navigation.
	 *
	 * @since 3.0
	 *
	 * @param string $which
	 */
	protected function display_tablenav( $which ) {
	}

	/**
	 * Show the button to allow the user to display the report.
	 *
	 * @since 3.2.7
	 * @return bool
	 */
	private function show_warning() {
		if ( ! is_null( $this->show_warning ) ) {
			return $this->show_warning;
		}

		// If the user has already dismissed this warning, we don't need to show it again.
		if ( get_transient( 'edd_earnings_by_taxonomy_show_report' ) ) {
			$this->show_warning = false;
			return $this->show_warning;
		}

		// We only want to show this warning if there are more than 200 products or terms.
		// This is, admittedly, an arbitrary number, but it's a good starting point.
		$threshold = 200;

		if (
			wp_count_posts( 'download' )->publish > $threshold
			|| wp_count_terms( array( 'taxonomy' => edd_get_download_taxonomies() ) ) > $threshold
		) {
			$this->show_warning = true;
		} else {
			$this->show_warning = false;
		}

		return $this->show_warning;
	}
}
