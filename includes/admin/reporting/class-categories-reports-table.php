<?php
/**
 * Earnings by Category Reports Table Class
 *
 * @package     EDD
 * @subpackage  Admin/Reports
 * @copyright   Copyright (c) 2018, Easy Digital Downloads, LLC
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       2.4
 */

// Exit if accessed directly
defined( 'ABSPATH' ) || exit;

use EDD\Admin\List_Table;

/**
 * EDD_Categories_Reports_Table Class
 *
 * Renders the Download Reports table
 *
 * @since 2.4
 */
class EDD_Categories_Reports_Table extends List_Table {

	/**
	 * Get things started
	 *
	 * @since 2.4
	 * @see WP_List_Table::__construct()
	 */
	public function __construct() {

		// Set parent defaults
		parent::__construct( array(
			'singular'  => 'report-earning',
			'plural'    => 'report-earnings',
			'ajax'      => false
		) );
	}

	/**
	 * Gets the name of the primary column.
	 *
	 * @since 2.5
	 * @access protected
	 *
	 * @return string Name of the primary column.
	 */
	protected function get_primary_column_name() {
		return 'label';
	}

	/**
	 * This function renders most of the columns in the list table.
	 *
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
	 * @since  2.4
	 * @return array $columns Array of all the list table columns
	 */
	public function get_columns() {
		return array(
			'label'          => __( 'Category',             'easy-digital-downloads' ),
			'total_sales'    => __( 'Total Sales',          'easy-digital-downloads' ),
			'total_earnings' => __( 'Total Earnings',       'easy-digital-downloads' ),
			'avg_sales'      => __( 'Monthly Sales Avg',    'easy-digital-downloads' ),
			'avg_earnings'   => __( 'Monthly Earnings Avg', 'easy-digital-downloads' )
		);
	}

	/**
	 * Outputs the reporting views
	 *
	 * @since 1.5
	 * @return void
	 */
	public function display_tablenav( $which = '' ) {
		?>
		<div class="tablenav <?php echo esc_attr( $which ); ?>">
			<div class="alignleft actions bulkactions">
				<?php
				if ( 'top' === $which ) {
					edd_report_views();
				}
				?>
			</div>
		</div>
	<?php
	}

	/**
	 * Builds and retrieves of all the categories reports data.
	 *
	 * @since 2.4
	 * @edeprecated 3.0 Use get_data()
	 *
	 * @return array All the data for customer reports.
	 */
	public function reports_data() {
		_edd_deprecated_function( __METHOD__, '3.0', 'EDD_Categories_Reports_Table::get_data()' );

		return $this->get_data();
	}

	/**
	 * Builds and retrieves all of the categories reports data.
	 *
	 * @since 3.0
	 *
	 * @return array Categories reports table data.
	 */
	public function get_data() {

		/*
		 * Date filtering
		 */
		$dates = edd_get_report_dates();

		$include_taxes = empty( $_GET['exclude_taxes'] ) ? true : false;

		if ( ! empty( $dates[ 'year' ] ) ) {
			$date = new DateTime();
			$date->setDate( $dates[ 'year' ], $dates[ 'm_start' ], $dates[ 'day' ] );
			$start_date = $date->format( 'Y-m-d' );

			$date->setDate( $dates[ 'year_end' ], $dates[ 'm_end' ], $dates[ 'day_end' ] );
			$end_date          = $date->format( 'Y-m-d' );
			$cached_report_key = 'edd_earnings_by_category_data' . $start_date . '_' . $end_date;
		} else {
			$start_date        = false;
			$end_date          = false;
			$cached_report_key = 'edd_earnings_by_category_data';
		}

		$cached_reports = get_transient( $cached_report_key );

		if ( false !== $cached_reports ) {
			$reports_data = $cached_reports;

		} else {
			$reports_data = array();
			$categories   = get_terms( 'download_category', array(
				'parent'       => 0,
				'hierarchical' => 0,
				'hide_empty'   => false
			) );

			foreach ( $categories as $category ) {

				$category_slugs = array( $category->slug );
				$child_terms    = get_terms( 'download_category', array(
					'parent'       => $category->term_id,
					'hierarchical' => 0
				) );

				if ( ! empty( $child_terms ) ) {
					foreach ( $child_terms as $child_term ) {
						$category_slugs[] = $child_term->slug;
					}
				}

				$downloads = get_posts( array(
					'post_type'      => 'download',
					'posts_per_page' => -1,
					'fields'         => 'ids',
					'tax_query'      => array(
						array(
							'taxonomy' => 'download_category',
							'field'    => 'slug',
							'terms'    => $category_slugs
						)
					)
				) );

				$sales     = $avg_sales    = 0;
				$earnings  = $avg_earnings = 0.00;

				foreach ( $downloads as $download ) {
					$current_sales    = EDD()->payment_stats->get_sales( $download, $start_date, $end_date );
					$current_earnings = EDD()->payment_stats->get_earnings( $download, $start_date, $end_date, $include_taxes );

					$current_average_sales = edd_get_average_monthly_download_sales( $download );
					$current_average_earnings = edd_get_average_monthly_download_earnings( $download );

					$sales        += $current_sales;
					$earnings     += $current_earnings;
					$avg_sales    += $current_average_sales;
					$avg_earnings += $current_average_earnings;
				}

				$avg_earnings = round( $avg_earnings, edd_currency_decimal_filter() );
				if ( ! empty( $avg_earnings ) && $avg_sales < 1 ) {
					$avg_sales = __( 'Less than 1', 'easy-digital-downloads' );
				} else {
					$avg_sales = round( edd_format_amount( $avg_sales, false ) );
				}

				$reports_data[] = array(
					'ID'                 => $category->term_id,
					'label'              => $category->name,
					'total_sales'        => edd_format_amount( $sales, false ),
					'total_sales_raw'    => $sales,
					'total_earnings'     => edd_currency_filter( edd_format_amount( $earnings ) ),
					'total_earnings_raw' => $earnings,
					'avg_sales'          => $avg_sales,
					'avg_earnings'       => edd_currency_filter( edd_format_amount( $avg_earnings ) ),
					'is_child'           => false,
				);

				if ( ! empty( $child_terms ) ) {
					foreach ( $child_terms as $child_term ) {
						$child_downloads = get_posts( array(
							'post_type'      => 'download',
							'posts_per_page' => -1,
							'fields'         => 'ids',
							'tax_query'      => array(
								array(
									'taxonomy' => 'download_category',
									'field'    => 'slug',
									'terms'    => $child_term->slug
								)
							)
						) );

						$child_sales     = $child_avg_sales    = 0;
						$child_earnings  = $child_avg_earnings = 0.00;

						foreach ( $child_downloads as $child_download ) {
							$current_average_sales    = $current_sales    = EDD()->payment_stats->get_sales( $child_download, $start_date, $end_date );
							$current_average_earnings = $current_earnings = EDD()->payment_stats->get_earnings( $child_download, $start_date, $end_date );

							$release_date = get_post_field( 'post_date', $child_download );
							$diff         = abs( current_time( 'timestamp' ) - strtotime( $release_date ) );
							$months       = floor( $diff / ( 30 * 60 * 60 * 24 ) ); // Number of months since publication

							if ( $months > 0 ) {
								$current_average_sales    = ( $current_sales / $months );
								$current_average_earnings = ( $current_earnings / $months );
							}

							$child_sales        += $current_sales;
							$child_earnings     += $current_earnings;
							$child_avg_sales    += $current_average_sales;
							$child_avg_earnings += $current_average_earnings;
						}

						$child_avg_sales    = round( $child_avg_sales / count( $child_downloads ) );
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
							'is_child'           => true
						);
					}
				}
			}
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

		$data        = array();
		$total_sales = 0;

		foreach ( $this->items as $item ) {
			$total_sales += $item['total_sales_raw'];

			if ( ! empty( $item[ 'is_child' ] ) || empty( $item[ 'total_sales_raw' ] ) ) {
				continue;
			}

			$data[ $item[ 'label' ] ] = $item[ 'total_sales_raw' ];
		}


		if ( empty( $total_sales ) ) {
			echo '<p><em>' . __( 'No sales for dates provided.', 'easy-digital-downloads' ) . '</em></p>';
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

		$data           = array();
		$total_earnings = 0;

		foreach ( $this->items as $item ) {
			$total_earnings += $item['total_earnings_raw'];

			if ( ! empty( $item[ 'is_child' ] ) || empty( $item[ 'total_earnings_raw' ] ) ) {
				continue;
			}

			$data[ $item[ 'label' ] ] = $item[ 'total_earnings_raw' ];

		}

		if ( empty( $total_earnings ) ) {
			echo '<p><em>' . __( 'No earnings for dates provided.', 'easy-digital-downloads' ) . '</em></p>';
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
	 * @since 2.4
	 * @uses EDD_Categories_Reports_Table::get_columns()
	 * @uses EDD_Categories_Reports_Table::get_sortable_columns()
	 * @uses EDD_Categories_Reports_Table::reports_data()
	 * @return void
	 */
	public function prepare_items() {
		$columns               = $this->get_columns();
		$hidden                = array(); // No hidden columns
		$sortable              = $this->get_sortable_columns();
		$this->_column_headers = array( $columns, $hidden, $sortable );
		$this->items           = $this->get_data();
	}
}
