<?php
/**
 * Gateways Reports Table Class
 *
 * @package     EDD
 * @subpackage  Admin/Reports
 * @copyright   Copyright (c) 2015, Pippin Williamson
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       2.4
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

// Load WP_List_Table if not loaded
if ( ! class_exists( 'WP_List_Table' ) ) {
	require_once ABSPATH . 'wp-admin/includes/class-wp-list-table.php';
}

/**
 * EDD_Categories_Reports_Table Class
 *
 * Renders the Download Reports table
 *
 * @since 2.4
 */
class EDD_Categories_Reports_Table extends WP_List_Table {

	/**
	 * Get things started
	 *
	 * @since 2.4
	 * @see WP_List_Table::__construct()
	 */
	public function __construct() {
		global $status, $page;
	}

	/**
	 * This function renders most of the columns in the list table.
	 *
	 * @access public
	 * @since 2.4
	 *
	 * @param array $item Contains all the data of the downloads
	 * @param string $column_name The name of the column
	 *
	 * @return string Column Name
	 */
	public function column_default( $item, $column_name ) {
		return $item[ $column_name ];
	}

	/**
	 * Retrieve the table columns
	 *
	 * @access public
	 * @since  2.4
	 * @return array $columns Array of all the list table columns
	 */
	public function get_columns() {
		$columns = array(
			'label'          => __( 'Category', 'easy-digital-downloads' ),
			'total_sales'    => __( 'Total Sales', 'easy-digital-downloads' ),
			'total_earnings' => __( 'Total Earnings', 'easy-digital-downloads' ),
			'avg_sales'      => __( 'Monthly Sales Avg', 'easy-digital-downloads' ),
			'avg_earnings'   => __( 'Monthly Earnings Avg', 'easy-digital-downloads' ),
		);

		return $columns;
	}

	/**
	 * Retrieve the current page number
	 *
	 * @access public
	 * @since  2.4
	 * @return int Current page number
	 */
	public function get_paged() {
		return isset( $_GET['paged'] ) ? absint( $_GET['paged'] ) : 1;
	}


	/**
	 * Outputs the reporting views
	 *
	 * @access public
	 * @since  2.4
	 * @return void
	 */
	public function bulk_actions( $which = '' ) {
		// These aren't really bulk actions but this outputs the markup in the right place
		edd_report_views();
	}


	/**
	 * Build all the reports data
	 *
	 * @access public
	 * @since  2.4
	 * @return array $reports_data All the data for customer reports
	 */
	public function reports_data() {

		$cached_reports = get_transient( 'edd_earnings_by_category_data' );
		if ( false !== $cached_reports ) {
			$reports_data = $cached_reports;
		} else {
			$reports_data = array();
			$term_args    = array(
				'parent'       => 0,
				'hierarchical' => 0,
			);

			$categories   = get_terms( 'download_category', $term_args );

			foreach ( $categories as $category_id => $category ) {

				$category_slugs = array( $category->slug );

				$child_args  = array(
					'parent'       => $category->term_id,
					'hierarchical' => 0,
				);

				$child_terms = get_terms( 'download_category', $child_args );
				if ( ! empty( $child_terms ) ) {

					foreach ( $child_terms as $child_term ) {
						$category_slugs[] = $child_term->slug;
					}

				}

				$download_args = array(
					'post_type'      => 'download',
					'posts_per_page' => -1,
					'fields'         => 'ids',
					'tax_query'      => array(
						array(
							'taxonomy' => 'download_category',
							'field'    => 'slug',
							'terms'    => $category_slugs,
						),
					),
				);

				$downloads = get_posts( $download_args );

				$sales        = 0;
				$earnings     = 0.00;
				$avg_sales    = 0;
				$avg_earnings = 0.00;

				foreach ( $downloads as $download ) {
					$sales        += edd_get_download_sales_stats( $download );
					$earnings     += edd_get_download_earnings_stats( $download );
					$avg_sales    += edd_get_average_monthly_download_sales( $download );
					$avg_earnings += edd_get_average_monthly_download_earnings( $download );
				}

				$avg_sales    = round( $avg_sales    / count( $downloads ) );
				$avg_earnings = round( $avg_earnings / count( $downloads ), edd_currency_decimal_filter() );

				$reports_data[] = array(
					'ID'                 => $category->term_id,
					'label'              => $category->name,
					'total_sales'        => edd_format_amount( $sales, false ),
					'total_sales_raw'    => $sales,
					'total_earnings'     => edd_currency_filter( edd_format_amount( $earnings ) ),
					'total_earnings_raw' => $earnings,
					'avg_sales'          => edd_format_amount( $avg_sales, false ),
					'avg_earnings'       => edd_currency_filter( edd_format_amount( $avg_earnings ) ),
					'is_child'           => false,
				);

				if ( ! empty( $child_terms ) ) {

					foreach ( $child_terms as $child_term ) {
						$child_args = array(
							'post_type'      => 'download',
							'posts_per_page' => -1,
							'fields'         => 'ids',
							'tax_query'      => array(
								array(
									'taxonomy' => 'download_category',
									'field'    => 'slug',
									'terms'    => $child_term->slug,
								),
							),
						);

						$child_downloads = get_posts( $child_args );

						$child_sales        = 0;
						$child_earnings     = 0.00;
						$child_avg_sales    = 0;
						$child_avg_earnings = 0.00;

						foreach ( $child_downloads as $child_download ) {
							$child_sales        += edd_get_download_sales_stats( $child_download );
							$child_earnings     += edd_get_download_earnings_stats( $child_download );
							$child_avg_sales    += edd_get_average_monthly_download_sales( $child_download );
							$child_avg_earnings += edd_get_average_monthly_download_earnings( $child_download );
						}

						$child_avg_sales    = round( $child_avg_sales    / count( $child_downloads ) );
						$child_avg_earnings = round( $child_avg_earnings / count( $child_downloads ), edd_currency_decimal_filter() );

						$reports_data[] = array(
							'ID'                 => $child_term->term_id,
							'label'              => '&#8212; ' . $child_term->name,
							'total_sales'        => edd_format_amount( $child_sales, false ),
							'total_sales_raw'    => $child_sales,
							'total_earnings'     => edd_currency_filter( edd_format_amount( $child_earnings ) ),
							'total_earnings_raw' => $child_earnings,
							'avg_sales'          => edd_format_amount( $child_avg_sales, false ),
							'avg_earnings'       => edd_currency_filter( edd_format_amount( $child_avg_earnings ) ),
							'is_child'           => true,
						);

					}
				}

			}

			set_transient( 'edd_earnings_by_category_data', $reports_data, ( HOUR_IN_SECONDS / 4 ) );
		}

		return $reports_data;
	}

	/**
	 * Output the Category Sales Mix Pie Chart
	 *
	 * @since  2.4
	 * @return string The HTML for the outputted graph
	 */
	public function output_sales_graph() {
		if ( empty( $this->items ) ) {
			return;
		}

		$data = array();
		foreach ( $this->items as $item ) {
			if ( ! empty( $item['is_child'] ) || empty( $item['total_sales_raw'] ) ) {
				continue;
			}

			$data[ $item['label'] ] = $item['total_sales_raw'];
		}

		// Sort High to Low, prior to filter so people can reorder if they please
		arsort( $data );
		$data = apply_filters( 'edd_category_sales_graph_data', $data );

		$options = apply_filters( 'edd_category_sales_graph_options', array(
			'legend_formatter' => 'eddLegendFormatterSales',
		), $data );

		$pie_graph = new EDD_Pie_Graph( $data, $options );
		$pie_graph->display();
	}

	/**
	 * Output the Category Earnings Mix Pie Chart
	 *
	 * @since  2.4
	 * @return string The HTML for the outputted graph
	 */
	public function output_earnings_graph() {
		if ( empty( $this->items ) ) {
			return;
		}

		$data = array();
		foreach ( $this->items as $item ) {
			if ( ! empty( $item['is_child'] ) || empty( $item['total_earnings_raw'] ) ) {
				continue;
			}

			$data[ $item['label'] ] = $item['total_earnings_raw'];
		}

		// Sort High to Low, prior to filter so people can reorder if they please
		arsort( $data );
		$data = apply_filters( 'edd_category_earnings_graph_data', $data );

		$options = apply_filters( 'edd_category_earnings_graph_options', array(
			'legend_formatter' => 'eddLegendFormatterEarnings',
		), $data );

		$pie_graph = new EDD_Pie_Graph( $data, $options );
		$pie_graph->display();
	}

	/**
	 * Setup the final data for the table
	 *
	 * @access public
	 * @since 2.4
	 * @uses EDD_Gateawy_Reports_Table::get_columns()
	 * @uses EDD_Gateawy_Reports_Table::get_sortable_columns()
	 * @uses EDD_Gateawy_Reports_Table::reports_data()
	 * @return void
	 */
	public function prepare_items() {
		$columns               = $this->get_columns();
		$hidden                = array(); // No hidden columns
		$sortable              = $this->get_sortable_columns();
		$this->_column_headers = array( $columns, $hidden, $sortable );
		$this->items           = $this->reports_data();

	}
}
