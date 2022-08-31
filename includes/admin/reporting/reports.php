<?php
/**
 * Reports functions.
 *
 * @package     EDD
 * @subpackage  Admin/Reports
 * @copyright   Copyright (c) 2018, Easy Digital Downloads, LLC
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       1.0
 * @since       3.0 Full refactor of Reports.
 */

use EDD\Reports;

// Exit if accessed directly
defined( 'ABSPATH' ) || exit;

/**
 * Load a report early in the admin-area.
 *
 * This action-function loads the Report early, so that a redirect can occur in
 * the event that the report is not valid, registered, or the user cannot view
 * it.
 *
 * Note that pre-3.0 reports are shimmed via EDD\Reports::legacy_reports()
 *
 * @since 3.0
 */
function edd_admin_load_report() {

	// Redirect URL (on error)
	$redirect_url = edd_get_admin_url( array(
		'page' => 'edd-reports'
	) );

	// Redirect if user cannot view reports
	if ( ! current_user_can( 'view_shop_reports' ) ) {
		edd_redirect( $redirect_url );
	}

	// Start the Reports API.
	new Reports\Init();

	add_filter( 'edd_admin_is_single_view', '__return_false' );

	// Get the section and report
	$section = Reports\get_current_report();
	$report  = Reports\get_report( $section );

	// Redirect if report is invalid
	if ( empty( $report ) || is_wp_error( $report ) ) {
		edd_redirect( $redirect_url );
	}

	// Stash the report in the EDD global for future reference
	EDD()->report = $report;
}
add_action( 'load-download_page_edd-reports', 'edd_admin_load_report' );

/**
 * Contains backwards compat code to shim tabs & views to EDD_Sections()
 *
 * @since 3.0
 */
function edd_reports_sections() {

	// Instantiate the Sections class and sections array
	$sections   = new EDD\Admin\Reports_Sections();
	$c_sections = array();

	// Setup sections variables
	$sections->use_js          = false;
	$sections->current_section = Reports\get_current_report();
	$sections->item            = null;

	// Find persisted filters to append to the base URL.
	$persisted_filters     = Reports\get_persisted_filters();
	$persisted_filter_args = array();

	foreach ( $persisted_filters as $filter ) {
		if ( isset( $_GET[ $filter ] ) ) {
			$persisted_filter_args[ $filter ] = sanitize_text_field( $_GET[ $filter ] );
		}
	}

	// Build the section base URL.
	$sections->base_url = edd_get_admin_url(
		array_merge(
			array(
				'page'  => 'edd-reports',
			),
			$persisted_filter_args
		)
	);

	// Get all registered tabs & views
	$tabs = Reports\get_reports();

	// Loop through tabs & setup sections
	if ( ! empty( $tabs ) ) {
		foreach ( $tabs as $id => $tab ) {

			// Add to sections array
			$c_sections[] = array(
				'id'       => $id,
				'label'    => $tab['label'],
				'icon'     => $tab['icon'],
				'callback' => array( 'edd_output_report_callback', array( $id ) )
			);
		}
	}

	// Set the customer sections
	$sections->set_sections( $c_sections );

	// Display the sections
	$sections->display();
}

/**
 * Output a report via a callback
 *
 * @since 3.0
 *
 * @param string $report_id
 */
function edd_output_report_callback( $report_id = '' ) {

	// Maybe use the already loaded report
	$report = EDD()->report
		? EDD()->report
		: EDD\Reports\get_report( $report_id );

	/**
	 * Fires at the top of the content area of a Reports tab.
	 *
	 * @since 1.0
	 * @since 3.0 Added the `$report` parameter.
	 *
	 * @param \EDD\Reports\Data\Report|\WP_Error $report The current report object,
	 *                                                   or WP_Error if invalid.
	 */
	do_action( 'edd_reports_page_top', $report );

	if ( ! is_wp_error( $report ) ) {
		printf( '<h2 class="edd-reports__heading">%s</h2>', esc_html( $report->label ) );
		$report->display();
	} else {
		Reports\default_display_report( $report );
	}

	/**
	 * Fires at the bottom of the content area of a Reports tab.
	 *
	 * @since 1.0
	 * @since 3.0 Added the `$report` parameter.
	 *
	 * @param \EDD\Reports\Data\Report|\WP_Error $report The current report object,
	 *                                                   or WP_Error if invalid.
	 */
	do_action( 'edd_reports_page_bottom', $report );
}

/**
 * Reports Page
 *
 * Renders the reports page contents.
 *
 * @since 1.0
 * @return void
 */
function edd_reports_page() {
	// Enqueue scripts.
	wp_enqueue_script( 'edd-admin-reports' );
	?>

	<div class="wrap">
		<h1><?php esc_html_e( 'Reports', 'easy-digital-downloads' ); ?></h1>

		<?php Reports\display_filters( EDD()->report ); ?>

		<div id="edd-item-wrapper" class="full-width edd-clearfix">
			<?php edd_reports_sections(); ?>
		</div>
	</div><!-- .wrap -->

	<?php
}

/**
 * Register overview report and endpoints.
 *
 * @since 3.0
 *
 * @param \EDD\Reports\Data\Report_Registry $reports Report registry.
 */
function edd_register_overview_report( $reports ) {
	try {
		// Variables to hold date filter values.
		$options       = Reports\get_dates_filter_options();
		$dates         = Reports\get_filter_value( 'dates' );
		$exclude_taxes = Reports\get_taxes_excluded_filter();
		$currency      = Reports\get_filter_value( 'currencies' );

		$hbh   = Reports\get_dates_filter_hour_by_hour();
		$label = $options[ $dates['range'] ] . ( $hbh ? ' (' . edd_get_timezone_abbr() . ')' : '' );

		$reports->add_report( 'overview', array(
			'label'     => __( 'Overview', 'easy-digital-downloads' ),
			'icon'      => 'dashboard',
			'priority'  => 5,
			'endpoints' => array(
				'tiles'  => array(
					'overview_sales',
					'overview_earnings',
					'overview_average_order_value',
					'overview_new_customers',
					'overview_refunded_amount',
				),
				'charts' => array(
					'overview_sales_earnings_chart',
				),
			),
			'filters' => array(
				'dates',
				'taxes',
				'currencies',
			)
		) );

		$reports->register_endpoint( 'overview_sales', array(
			'label' => __( 'Sales', 'easy-digital-downloads' ),
			'views' => array(
				'tile' => array(
					'data_callback' => function () use ( $dates, $currency ) {
						$stats = new EDD\Stats(
							array(
								'range'        => $dates['range'],
								'relative'     => true,
								'currency'     => $currency,
								'revenue_type' => 'net',
							)
						);
						return $stats->get_order_count();
					},
					'display_args'  => array(
						'comparison_label' => $label . ' &mdash; ' . __( 'Net', 'easy-digital-downloads' ),
					),
				),
			),
		) );

		$reports->register_endpoint( 'overview_earnings', array(
			'label' => __( 'Earnings', 'easy-digital-downloads' ),
			'views' => array(
				'tile' => array(
					'data_callback' => function () use ( $dates, $exclude_taxes, $currency ) {
						$stats = new EDD\Stats(
							array(
								'range'         => $dates['range'],
								'function'      => 'SUM',
								'exclude_taxes' => $exclude_taxes,
								'currency'      => $currency,
								'relative'      => true,
								'output'        => 'formatted',
								'revenue_type'  => 'net',
							)
						);
						return $stats->get_order_earnings();
					},
					'display_args'  => array(
						'comparison_label' => $label . ' &mdash; ' . __( 'Net', 'easy-digital-downloads' ),
					),
				),
			),
		) );

		$reports->register_endpoint( 'overview_average_order_value', array(
			'label' => __( 'Average Order Value', 'easy-digital-downloads' ),
			'views' => array(
				'tile' => array(
					'data_callback' => function () use ( $dates, $exclude_taxes, $currency ) {
						$stats = new EDD\Stats(
							array(
								'function'      => 'AVG',
								'output'        => 'formatted',
								'relative'      => true,
								'range'         => $dates['range'],
								'exclude_taxes' => $exclude_taxes,
								'currency'      => $currency,
							)
						);
						return $stats->get_order_earnings();
					},
					'display_args'  => array(
						'comparison_label' => $label,
					),
				),
			),
		) );

		$reports->register_endpoint( 'overview_new_customers', array(
			'label' => __( 'New Customers', 'easy-digital-downloads' ),
			'views' => array(
				'tile' => array(
					'data_callback' => function () use ( $dates ) {
						$stats = new EDD\Stats();
						return $stats->get_customer_count( array(
							'range'    => $dates['range'],
							'relative' => true,
						) );
					},
					'display_args'  => array(
						'comparison_label' => $label,
					),
				),
			),
		) );

		$reports->register_endpoint( 'overview_refunded_amount', array(
			'label' => __( 'Total Refund Amount', 'easy-digital-downloads' ),
			'views' => array(
				'tile' => array(
					'data_callback' => function () use ( $dates, $exclude_taxes, $currency ) {
						$stats  = new EDD\Stats();
						return $stats->get_order_refund_amount( array(
							'range'         => $dates['range'],
							'function'      => 'SUM',
							'exclude_taxes' => $exclude_taxes,
							'currency'      => $currency,
							'relative'      => true,
							'output'        => 'formatted',
						) );

					},
					'display_args'  => array(
						'comparison_label' => $label,
					),
				),
			),
		) );

		$reports->register_endpoint( 'overview_sales_earnings_chart', array(
			'label' => __( 'Sales and Earnings', 'easy-digital-downloads' ) . ' &mdash; ' . $label . ' &mdash; ' . __( 'Net', 'easy-digital-downloads' ),
			'views' => array(
				'chart' => array(
					'data_callback' => 'edd_overview_sales_earnings_chart',
					'type'          => 'line',
					'options'       => array(
						'datasets' => array(
							'sales'    => array(
								'label'                => __( 'Sales', 'easy-digital-downloads' ),
								'borderColor'          => 'rgb(252,108,18)',
								'backgroundColor'      => 'rgba(252,108,18,0.2)',
								'fill'                 => true,
								'borderDash'           => array( 2, 6 ),
								'borderCapStyle'       => 'round',
								'borderJoinStyle'      => 'round',
								'pointRadius'          => 4,
								'pointHoverRadius'     => 6,
								'pointBackgroundColor' => 'rgb(255,255,255)',
							),
							'earnings' => array(
								'label'                => __( 'Earnings', 'easy-digital-downloads' ),
								'borderColor'          => 'rgb(24,126,244)',
								'backgroundColor'      => 'rgba(24,126,244,0.05)',
								'fill'                 => true,
								'borderWidth'          => 2,
								'type'                 => 'currency',
								'pointRadius'          => 4,
								'pointHoverRadius'     => 6,
								'pointBackgroundColor' => 'rgb(255,255,255)',
							),
						),
					),
				),
			),
		) );
	} catch ( \EDD_Exception $exception ) {
		edd_debug_log_exception( $exception );
	}
}
add_action( 'edd_reports_init', 'edd_register_overview_report' );

/**
 * Register downloads report and endpoints.
 *
 * @since 3.0
 *
 * @param \EDD\Reports\Data\Report_Registry $reports Report registry.
 */
function edd_register_downloads_report( $reports ) {
	try {
		$options       = Reports\get_dates_filter_options();
		$dates         = Reports\get_filter_value( 'dates' );
		$exclude_taxes = Reports\get_taxes_excluded_filter();
		$currency      = '';

		$hbh   = Reports\get_dates_filter_hour_by_hour();
		$label = $options[ $dates['range'] ] . ( $hbh ? ' (' . edd_get_timezone_abbr() . ')' : '' );

		$download_data = Reports\get_filter_value( 'products' );
		$download_data = ! empty( $download_data ) && 'all' !== Reports\get_filter_value( 'products' )
			? edd_parse_product_dropdown_value( Reports\get_filter_value( 'products' ) )
			: false;

		$endpoint_label = __( 'Sales / Earnings', 'easy-digital-downloads' );

		// Mock downloads and prices in case they cannot be found later.
		$download       = edd_get_download();
		$prices         = array();
		$download_label = '';
		if ( $download_data ) {
			$download = edd_get_download( $download_data['download_id'] );
			$prices   = $download->get_prices();
			if ( isset( $download_data['price_id'] ) && is_numeric( $download_data['price_id'] ) ) {
				$args       = array( 'price_id' => $download_data['price_id'] );
				$price_name = edd_get_price_name( $download->ID, $args );
				if ( $price_name ) {
					$download->post_title .= ': ' . $price_name;
				}
			}
			$download_label = esc_html( ' (' . $download->post_title . ')' );
		}

		$tiles = array_filter( array(
			'most_valuable_download',
			'average_download_sales_earnings',
			'download_sales_earnings',
		), function( $endpoint ) use ( $download_data ) {
			switch( $endpoint ) {
				case 'download_sales_earnings':
					return false !== $download_data;
					break;
				default:
					return false === $download_data;
			}
		} );

		$charts = array_filter( array(
			'download_sales_by_variations',
			'download_earnings_by_variations',
			'download_sales_earnings_chart'
		), function( $endpoint ) use ( $download_data, $download ) {
			switch( $endpoint ) {
				case 'download_sales_by_variations':
				case 'download_earnings_by_variations':
					return (
						false !== $download_data &&
						false === $download_data['price_id'] &&
						true === $download->has_variable_prices()
					);

					break;

				default:
					return false !== $download_data;
			}
		} );

		$tables = array_filter( array(
			'top_selling_downloads',
			'earnings_by_taxonomy',
		), function( $endpoint ) use ( $download_data ) {
			return false === $download_data;
		} );

		$reports->add_report( 'downloads', array(
			'label'     => edd_get_label_plural(),
			'priority'  => 10,
			'icon'      => 'download',
			'endpoints' => array(
				'tiles'  => $tiles,
				'charts' => $charts,
				'tables' => $tables,
			),
			'filters'   => array( 'dates', 'products', 'taxes' ),
		) );

		$reports->register_endpoint( 'most_valuable_download', array(
			'label' => sprintf( __( 'Most Valuable %s', 'easy-digital-downloads' ), edd_get_label_singular() ),
			'views' => array(
				'tile' => array(
					'data_callback' => function () use ( $dates, $currency ) {
						$stats = new EDD\Stats();
						$d     = $stats->get_most_valuable_order_items( array(
							'range'    => $dates['range'],
							'currency' => $currency,
							'function' => 'SUM'
						) );

						if ( ! empty( $d ) && isset( $d[0] ) ) {
							$d = $d[0];

							if ( $d->object instanceof EDD_Download ) {
								$title = $d->object->post_title;

								if ( $d->object->has_variable_prices() ) {
									$prices = array_values( wp_filter_object_list( $d->object->get_prices(), array( 'index' => absint( $d->price_id ) ) ) );

									$title .= ( is_array( $prices ) && isset( $prices[0] ) )
										? ': ' . $prices[0]['name']
										: '';
								}

								return esc_html( $title );
							}
						}
					},
					'display_args'  => array(
						'comparison_label' => $label,
					),
				),
			),
		) );

		$reports->register_endpoint( 'average_download_sales_earnings', array(
			'label' => __( 'Average Sales / Earnings', 'easy-digital-downloads' ),
			'views' => array(
				'tile' => array(
					'data_callback' => function () use ( $dates, $exclude_taxes, $currency ) {
						$stats = new EDD\Stats( array(
							'function'      => 'AVG',
							'range'         => $dates['range'],
							'exclude_taxes' => $exclude_taxes,
							'currency'      => $currency,
							'output'        => 'formatted',
						) );

						return $stats->get_order_item_count() . ' / ' . $stats->get_order_item_earnings();
					},
					'display_args'  => array(
						'comparison_label' => $label . ' &mdash; ' . __( 'Net	', 'easy-digital-downloads' ),
					),
				),
			),
		) );

		$reports->register_endpoint( 'download_sales_earnings', array(
			'label' => $endpoint_label,
			'views' => array(
				'tile' => array(
					'data_callback' => function () use ( $download_data, $dates, $currency ) {
						$price_id = isset( $download_data['price_id'] ) && is_numeric( $download_data['price_id'] ) ? absint( $download_data['price_id'] ) : null;
						$stats    = new EDD\Stats( array(
							'product_id' => absint( $download_data['download_id'] ),
							'price_id'   => $price_id,
							'currency'   => $currency,
							'range'      => $dates['range'],
							'output'     => 'formatted',
						) );

						$earnings = $stats->get_order_item_earnings( array(
							'function' => 'SUM'
						) );
						$sales    = $stats->get_order_item_count( array(
							'function' => 'COUNT'
						) );

						return esc_html( $sales . ' / ' . $earnings );
					},
					'display_args'  => array(
						'comparison_label' => $label . $download_label,
					),
				),
			),
		) );

		$reports->register_endpoint( 'earnings_by_taxonomy', array(
			'label' => __( 'Earnings By Taxonomy', 'easy-digital-downloads' ) . ' &mdash; ' . $label,
			'views' => array(
				'table' => array(
					'display_args' => array(
						'class_name' => '\\EDD\\Reports\\Data\\Downloads\\Earnings_By_Taxonomy_List_Table',
						'class_file' => EDD_PLUGIN_DIR . 'includes/reports/data/downloads/class-earnings-by-taxonomy-list-table.php',
					),
				),
			),
		) );

		$reports->register_endpoint( 'top_selling_downloads', array(
			'label' => sprintf( __( 'Top Selling %s', 'easy-digital-downloads' ), edd_get_label_plural() ) . ' &mdash; ' . $label,
			'views' => array(
				'table' => array(
					'display_args' => array(
						'class_name' => '\\EDD\\Reports\\Data\\Downloads\\Top_Selling_Downloads_List_Table',
						'class_file' => EDD_PLUGIN_DIR . 'includes/reports/data/downloads/class-top-selling-downloads-list-table.php',
					),
				),
			),
		) );

		$reports->register_endpoint( 'download_sales_by_variations', array(
			'label' => __( 'Sales by Variation', 'easy-digital-downloads' ) . $download_label,
			'views' => array(
				'chart' => array(
					'data_callback' => function() use ( $download_data, $download, $dates, $prices, $currency ) {
						$stats = new EDD\Stats();
						$sales = $stats->get_order_item_count( array(
							'product_id' => absint( $download_data['download_id'] ),
							'range'      => $dates['range'],
							'grouped'    => true,
							'currency'   => $currency
						) );

						// Set all values to 0.
						foreach ( $prices as $key => $price ) {
							$prices[ $key ]['sales'] = 0;
						}

						// Parse results from the database.
						foreach ( $sales as $data ) {
							$prices[ $data->price_id ]['sales'] = absint( $data->total );
						}

						$sales = array_values( wp_list_pluck( $prices, 'sales' ) );

						return array(
							'sales' => $sales,
						);
					},
					'type' => 'pie',
					'options' => array(
						'cutoutPercentage' => 0,
						'datasets'         => array(
							'sales' => array(
								'label'           => __( 'Sales', 'easy-digital-downloads' ),
								'backgroundColor' => array(
									'rgb(133,175,91)',
									'rgb(9,149,199)',
									'rgb(8,189,231)',
									'rgb(137,163,87)',
									'rgb(27,98,122)',
								),
							),
						),
						'labels' => array_values( wp_list_pluck( $prices, 'name' ) )
					),
				),
			)
		) );

		$reports->register_endpoint( 'download_earnings_by_variations', array(
			'label' => __( 'Earnings by Variation', 'easy-digital-downloads' ) . $download_label,
			'views' => array(
				'chart' => array(
					'data_callback' => function() use ( $download_data, $prices, $dates, $currency ) {
						$stats = new EDD\Stats();
						$earnings = $stats->get_order_item_earnings( array(
							'product_id' => absint( $download_data['download_id'] ),
							'range'      => $dates['range'],
							'grouped'    => true,
							'currency'   => $currency
						) );

						// Set all values to 0.
						foreach ( $prices as $key => $price ) {
							$prices[ $key ]['earnings'] = floatval( 0 );
						}

						// Parse results from the database.
						foreach ( $earnings as $data ) {
							$prices[ $data->price_id ]['earnings'] = floatval( $data->total );
						}

						$earnings = array_values( wp_list_pluck( $prices, 'earnings' ) );

						return array(
							'earnings' => $earnings,
						);
					},
					'type' => 'pie',
					'options' => array(
						'cutoutPercentage' => 0,
						'datasets'         => array(
							'earnings' => array(
								'label'           => __( 'Earnings', 'easy-digital-downloads' ),
								'type'            => 'currency',
								'backgroundColor' => array(
									'rgb(133,175,91)',
									'rgb(9,149,199)',
									'rgb(8,189,231)',
									'rgb(137,163,87)',
									'rgb(27,98,122)',
								),
							),
						),
						'labels' => array_values( wp_list_pluck( $prices, 'name' ) )
					),
				),
			)
		) );

		$reports->register_endpoint( 'download_sales_earnings_chart', array(
			'label' => __( 'Sales and Earnings', 'easy-digital-downloads' ) . esc_html( $download_label ),
			'views' => array(
				'chart' => array(
					'data_callback' => function () use ( $download_data, $currency ) {
						global $wpdb;

						$dates        = Reports\get_dates_filter( 'objects' );
						$day_by_day   = Reports\get_dates_filter_day_by_day();
						$hour_by_hour = Reports\get_dates_filter_hour_by_hour();

						$sql_clauses = array(
							'select'  => 'YEAR(date_created) AS year, MONTH(date_created) AS month, DAY(date_created) AS day',
							'groupby' => 'YEAR(date_created), MONTH(date_created), DAY(date_created)',
							'orderby' => 'YEAR(date_created), MONTH(date_created), DAY(date_created)',
						);

						if ( $hour_by_hour ) {
							$sql_clauses = array(
								'select'  => 'YEAR(date_created) AS year, MONTH(date_created) AS month, DAY(date_created) AS day, HOUR(date_created) AS hour',
								'groupby' => 'YEAR(date_created), MONTH(date_created), DAY(date_created), HOUR(date_created)',
								'orderby' => 'YEAR(date_created), MONTH(date_created), DAY(date_created), HOUR(date_created)',
							);
						} elseif ( ! $day_by_day ) {
							$sql_clauses = array(
								'select'  => 'YEAR(date_created) AS year, MONTH(date_created) AS month',
								'groupby' => 'YEAR(date_created), MONTH(date_created)',
								'orderby' => 'YEAR(date_created), MONTH(date_created)',
							);
						}

						$price_id = isset( $download_data['price_id'] ) && is_numeric( $download_data['price_id'] )
							? $wpdb->prepare( 'AND price_id = %d', absint( $download_data['price_id'] ) )
							: '';

						$results = $wpdb->get_results( $wpdb->prepare(
							"SELECT COUNT(total) AS sales, SUM(total / rate) AS earnings, {$sql_clauses['select']}
							FROM {$wpdb->edd_order_items} edd_oi
							WHERE product_id = %d {$price_id} AND date_created >= %s AND date_created <= %s AND status = 'complete'
							GROUP BY {$sql_clauses['groupby']}
							ORDER BY {$sql_clauses['orderby']} ASC",
							$download_data['download_id'], $dates['start']->copy()->format( 'mysql' ), $dates['end']->copy()->format( 'mysql' ) ) );

						$sales    = array();
						$earnings = array();

						// Initialise all arrays with timestamps and set values to 0.
						while ( strtotime( $dates['start']->copy()->format( 'mysql' ) ) <= strtotime( $dates['end']->copy()->format( 'mysql' ) ) ) {
							if ( $hour_by_hour ) {
								$timestamp = \Carbon\Carbon::create( $dates['start']->year, $dates['start']->month, $dates['start']->day, $dates['start']->hour, 0, 0, 'UTC' )->setTimezone( edd_get_timezone_id() )->timestamp;

								$sales[ $timestamp ][] = $timestamp;
								$sales[ $timestamp ][] = 0;

								$earnings[ $timestamp ][] = $timestamp;
								$earnings[ $timestamp ][] = 0.00;

								$dates['start']->addHour( 1 );
							} else {
								$day = ( true === $day_by_day )
									? $dates['start']->day
									: 1;

								$timestamp = \Carbon\Carbon::create( $dates['start']->year, $dates['start']->month, $day, 0, 0, 0, 'UTC' )->setTimezone( edd_get_timezone_id() )->timestamp;

								$sales[ $timestamp ][] = $timestamp;
								$sales[ $timestamp ][] = 0;

								$earnings[ $timestamp ][] = $timestamp;
								$earnings[ $timestamp ][] = 0.00;

								$dates['start'] = ( true === $day_by_day )
									? $dates['start']->addDays( 1 )
									: $dates['start']->addMonth( 1 );
							}
						}

						foreach ( $results as $result ) {
							if ( $hour_by_hour ) {

								/**
								 * If this is hour by hour, the database returns the timestamps in UTC and an offset
								 * needs to be applied to that.
								 */
								$timestamp = \Carbon\Carbon::create( $result->year, $result->month, $result->day, $result->hour, 0, 0, 'UTC' )->setTimezone( edd_get_timezone_id() )->timestamp;
							} else {
								$day = ( true === $day_by_day )
									? $result->day
									: 1;

								$timestamp = \Carbon\Carbon::create( $result->year, $result->month, $day, 0, 0, 0, 'UTC' )->setTimezone( edd_get_timezone_id() )->timestamp;
							}

							$sales[ $timestamp ][1]    += $result->sales;
							$earnings[ $timestamp ][1] += floatval( $result->earnings );
						}

						$sales    = array_values( $sales );
						$earnings = array_values( $earnings );

						return array(
							'sales'    => $sales,
							'earnings' => $earnings,
						);
					},
					'type'          => 'line',
					'options'       => array(
						'datasets' => array(
							'sales'    => array(
								'label'                => __( 'Sales', 'easy-digital-downloads' ),
								'borderColor'          => 'rgb(252,108,18)',
								'backgroundColor'      => 'rgba(252,108,18,0.2)',
								'fill'                 => true,
								'borderDash'           => array( 2, 6 ),
								'borderCapStyle'       => 'round',
								'borderJoinStyle'      => 'round',
								'pointRadius'          => 4,
								'pointHoverRadius'     => 6,
								'pointBackgroundColor' => 'rgb(255,255,255)',
							),
							'earnings' => array(
								'label'                => __( 'Earnings', 'easy-digital-downloads' ),
								'borderColor'          => 'rgb(24,126,244)',
								'backgroundColor'      => 'rgba(24,126,244,0.05)',
								'fill'                 => true,
								'borderWidth'          => 2,
								'type'                 => 'currency',
								'pointRadius'          => 4,
								'pointHoverRadius'     => 6,
								'pointBackgroundColor' => 'rgb(255,255,255)',
							),
						),
					),
				),
			),
		) );
	} catch ( \EDD_Exception $exception ) {
		edd_debug_log_exception( $exception );
	}
}
add_action( 'edd_reports_init', 'edd_register_downloads_report' );


/**
 * Register refunds report and endpoints.
 *
 * @since 3.0
 *
 * @param \EDD\Reports\Data\Report_Registry $reports Report registry.
 */
function edd_register_refunds_report( $reports ) {
	try {

		// Variables to hold date filter values.
		$options       = Reports\get_dates_filter_options();
		$dates         = Reports\get_filter_value( 'dates' );
		$exclude_taxes = Reports\get_taxes_excluded_filter();
		$currency      = Reports\get_filter_value( 'currencies' );

		$hbh   = Reports\get_dates_filter_hour_by_hour();
		$label = $options[ $dates['range'] ] . ( $hbh ? ' (' . edd_get_timezone_abbr() . ')' : '' );

		$reports->add_report( 'refunds', array(
			'label'     => __( 'Refunds', 'easy-digital-downloads' ),
			'icon'      => 'image-rotate',
			'priority'  => 15,
			'endpoints' => array(
				'tiles'  => array(
					'refund_count',
					'fully_refunded_order_count',
					'fully_refunded_order_item_count',
					'refund_amount',
					'average_refund_amount',
					'average_time_to_refund',
					'refund_rate',
				),
				'charts' => array(
					'refunds_chart',
				),
			),
			'filters' => array(
				'dates',
				'taxes',
				'currencies'
			)
		) );

		$reports->register_endpoint( 'refund_count', array(
			'label' => __( 'Number of Refunds', 'easy-digital-downloads' ),
			'views' => array(
				'tile' => array(
					'data_callback' => function () use ( $dates, $currency ) {
						$stats  = new EDD\Stats();
						$number = $stats->get_order_refund_count( array(
							'range'    => $dates['range'],
							'currency' => $currency
						) );
						return esc_html( $number );
					},
					'display_args'  => array(
						'comparison_label' => $label,
					),
				),
			),
		) );

		$reports->register_endpoint( 'fully_refunded_order_count', array(
			'label' => __( 'Number of Fully Refunded Orders', 'easy-digital-downloads' ),
			'views' => array(
				'tile' => array(
					'data_callback' => function () use ( $dates, $currency ) {
						$stats  = new EDD\Stats();
						$number = $stats->get_order_refund_count( array(
							'range'    => $dates['range'],
							'status'   => array( 'complete' ),
							'currency' => $currency
						) );
						return esc_html( $number );
					},
					'display_args'  => array(
						'comparison_label' => $label,
					),
				),
			),
		) );

		$reports->register_endpoint( 'fully_refunded_order_item_count', array(
			'label' => __( 'Number of Fully Refunded Items', 'easy-digital-downloads' ),
			'views' => array(
				'tile' => array(
					'data_callback' => function () use ( $dates, $currency ) {
						$stats  = new EDD\Stats();
						$number = $stats->get_order_item_refund_count( array(
							'range'    => $dates['range'],
							'status'   => array( 'refunded' ),
							'currency' => $currency
						) );
						return esc_html( $number );
					},
					'display_args'  => array(
						'comparison_label' => $label,
					),
				),
			),
		) );

		$reports->register_endpoint( 'refund_amount', array(
			'label' => __( 'Total Refund Amount', 'easy-digital-downloads' ),
			'views' => array(
				'tile' => array(
					'data_callback' => function () use ( $dates, $exclude_taxes, $currency ) {
						$stats  = new EDD\Stats();
						return $stats->get_order_refund_amount( array(
							'range'         => $dates['range'],
							'exclude_taxes' => $exclude_taxes,
							'output'        => 'formatted',
							'currency'      => $currency
						) );
					},
					'display_args'  => array(
						'comparison_label' => $label,
					),
				),
			),
		) );

		$reports->register_endpoint( 'average_refund_amount', array(
			'label' => __( 'Average Refund Amount', 'easy-digital-downloads' ),
			'views' => array(
				'tile' => array(
					'data_callback' => function () use ( $dates, $exclude_taxes, $currency ) {
						$stats = new EDD\Stats();
						return $stats->get_order_refund_amount( array(
							'function'      => 'AVG',
							'range'         => $dates['range'],
							'exclude_taxes' => $exclude_taxes,
							'output'        => 'formatted',
							'currency'      => $currency
						) );
					},
					'display_args'  => array(
						'comparison_label' => $label,
					),
				),
			),
		) );

		$reports->register_endpoint( 'average_time_to_refund', array(
			'label' => __( 'Average Time to Refund', 'easy-digital-downloads' ),
			'views' => array(
				'tile' => array(
					'data_callback' => function () use ( $dates, $currency ) {
						$stats = new EDD\Stats();
						return $stats->get_average_refund_time( array(
							'range'    => $dates['range'],
							'currency' => $currency
						) );
					},
					'display_args'  => array(
						'comparison_label' => $label,
					),
				),
			),
		) );

		$reports->register_endpoint( 'refund_rate', array(
			'label' => __( 'Refund Rate', 'easy-digital-downloads' ),
			'views' => array(
				'tile' => array(
					'data_callback' => function () use ( $dates, $currency ) {
						$stats = new EDD\Stats();
						return $stats->get_refund_rate( array(
							'range'    => $dates['range'],
							'output'   => 'formatted',
							'status'   => edd_get_gross_order_statuses(),
							'currency' => $currency
						) );
					},
					'display_args'  => array(
						'comparison_label' => $label,
					),
				),
			),
		) );

		$reports->register_endpoint( 'refunds_chart', array(
			'label' => __( 'Refunds', 'easy-digital-downloads' ) . ' &mdash; ' . $label,
			'views' => array(
				'chart' => array(
					'data_callback' => 'edd_overview_refunds_chart',
					'type'          => 'line',
					'options'       => array(
						'datasets' => array(
							'number' => array(
								'label'                => __( 'Number', 'easy-digital-downloads' ),
								'borderColor'          => 'rgb(252,108,18)',
								'backgroundColor'      => 'rgba(252,108,18,0.2)',
								'fill'                 => true,
								'borderDash'           => array( 2, 6 ),
								'borderCapStyle'       => 'round',
								'borderJoinStyle'      => 'round',
								'pointRadius'          => 4,
								'pointHoverRadius'     => 6,
								'pointBackgroundColor' => 'rgb(255,255,255)',
							),
							'amount' => array(
								'label'                => __( 'Amount', 'easy-digital-downloads' ),
								'borderColor'          => 'rgb(24,126,244)',
								'backgroundColor'      => 'rgba(24,126,244,0.05)',
								'fill'                 => true,
								'borderWidth'          => 2,
								'type'                 => 'currency',
								'pointRadius'          => 4,
								'pointHoverRadius'     => 6,
								'pointBackgroundColor' => 'rgb(255,255,255)',
							),
						),
					),
				),
			),
		) );
	} catch ( \EDD_Exception $exception ) {
		edd_debug_log_exception( $exception );
	}
}
add_action( 'edd_reports_init', 'edd_register_refunds_report' );

/**
 * Register payment gateways report and endpoints.
 *
 * @since 3.0
 *
 * @param \EDD\Reports\Data\Report_Registry $reports Report registry.
 */
function edd_register_payment_gateways_report( $reports ) {
	try {

		// Variables to hold date filter values.
		$options       = Reports\get_dates_filter_options();
		$dates         = Reports\get_filter_value( 'dates' );
		$exclude_taxes = Reports\get_taxes_excluded_filter();
		$currency      = Reports\get_filter_value( 'currencies' );
		$gateway       = Reports\get_filter_value( 'gateways' );

		$hbh   = Reports\get_dates_filter_hour_by_hour();
		$label = $options[ $dates['range'] ] . ( $hbh ? ' (' . edd_get_timezone_abbr() . ')' : '' );

		$tiles = array(
			'sales_per_gateway',
			'earnings_per_gateway',
			'refunds_per_gateway',
			'average_value_per_gateway',
		);

		$tables = array_filter( array(
			'gateway_stats',
		), function( $endpoint ) use ( $gateway ) {
			return empty( $gateway ) || 'all' === $gateway;
		} );

		$charts = array_filter( array(
			'gateway_sales_breakdown',
			'gateway_earnings_breakdown',
			'gateway_sales_earnings_chart',
		), function( $endpoint ) use ( $gateway ) {
			switch( $endpoint ) {
				case 'gateway_sales_earnings_chart':
					return ! empty( $gateway ) && 'all' !== $gateway;
					break;
				default:
					return ( empty( $gateway ) || 'all' === $gateway );
			}
		} );

		$reports->add_report( 'gateways', array(
			'label'     => __( 'Payment Gateways', 'easy-digital-downloads' ),
			'icon'      => 'image-filter',
			'priority'  => 20,
			'endpoints' => array(
				'tiles'  => $tiles,
				'tables' => $tables,
				'charts' => $charts,
			),
			'filters'   => array( 'dates', 'gateways', 'taxes', 'currencies' ),
		) );

		$reports->register_endpoint( 'sales_per_gateway', array(
			'label' => __( 'Sales', 'easy-digital-downloads' ),
			'views' => array(
				'tile' => array(
					'data_callback' => function () use ( $dates, $currency ) {
						$gateway = 'all' !== Reports\get_filter_value( 'gateways' )
							? Reports\get_filter_value( 'gateways' )
							: '';

						$stats = new EDD\Stats();

						return $stats->get_gateway_sales( array(
							'range'    => $dates['range'],
							'gateway'  => $gateway,
							'currency' => $currency,
						) );
					},
					'display_args'  => array(
						'comparison_label' => $label,
					),
				),
			),
		) );

		$reports->register_endpoint( 'earnings_per_gateway', array(
			'label' => __( 'Earnings', 'easy-digital-downloads' ),
			'views' => array(
				'tile' => array(
					'data_callback' => function () use ( $dates, $exclude_taxes, $currency ) {
						$gateway = 'all' !== Reports\get_filter_value( 'gateways' )
							? Reports\get_filter_value( 'gateways' )
							: '';

						$stats = new EDD\Stats();

						return $stats->get_gateway_earnings( array(
							'range'         => $dates['range'],
							'exclude_taxes' => $exclude_taxes,
							'gateway'       => $gateway,
							'output'        => 'formatted',
							'currency'      => $currency
						) );
					},
					'display_args'  => array(
						'comparison_label' => $label,
					),
				),
			),
		) );

		$reports->register_endpoint( 'refunds_per_gateway', array(
			'label' => __( 'Refunds', 'easy-digital-downloads' ),
			'views' => array(
				'tile' => array(
					'data_callback' => function () use ( $dates, $currency ) {
						$gateway = 'all' !== Reports\get_filter_value( 'gateways' )
							? Reports\get_filter_value( 'gateways' )
							: '';

						$stats = new EDD\Stats();

						return $stats->get_gateway_earnings( array(
							'range'    => $dates['range'],
							'gateway'  => $gateway,
							'output'   => 'formatted',
							'type'    => 'refund',
							'status'   => array( 'complete' ),
							'currency' => $currency
						) );
					},
					'display_args'  => array(
						'comparison_label' => $label,
					),
				),
			),
		) );

		$reports->register_endpoint( 'average_value_per_gateway', array(
			'label' => __( 'Average Order Value', 'easy-digital-downloads' ),
			'views' => array(
				'tile' => array(
					'data_callback' => function () use ( $dates, $exclude_taxes, $currency ) {
						$gateway = 'all' !== Reports\get_filter_value( 'gateways' )
							? Reports\get_filter_value( 'gateways' )
							: '';

						$stats = new EDD\Stats();

						if ( empty( $gateway ) ) {
							return $stats->get_order_earnings( array(
								'range'         => $dates['range'],
								'exclude_taxes' => $exclude_taxes,
								'function'      => 'AVG',
								'output'        => 'formatted',
								'currency'      => $currency
							) );
						} else {
							return $stats->get_gateway_earnings( array(
								'range'         => $dates['range'],
								'exclude_taxes' => $exclude_taxes,
								'gateway'       => $gateway,
								'function'      => 'AVG',
								'output'        => 'formatted',
								'currency'      => $currency
							) );
						}
					},
					'display_args'  => array(
						'comparison_label' => $label,
					),
				),
			),
		) );

		$reports->register_endpoint( 'gateway_stats', array(
			'label' => __( 'Gateway Stats', 'easy-digital-downloads' ) . ' &mdash; ' . $options[ $dates['range'] ],
			'views' => array(
				'table' => array(
					'display_args' => array(
						'class_name' => '\\EDD\\Reports\\Data\\Payment_Gateways\\Gateway_Stats',
						'class_file' => EDD_PLUGIN_DIR . 'includes/reports/data/payment-gateways/class-gateway-stats-list-table.php',
					),
				),
			),
		) );

		$gateway_list = array_map( 'edd_get_gateway_admin_label', array_keys( edd_get_payment_gateways() ) );

		$reports->register_endpoint( 'gateway_sales_breakdown', array(
			'label' => __( 'Gateway Sales', 'easy-digital-downloads' ) . ' &mdash; ' . $options[ $dates['range'] ],
			'views' => array(
				'chart' => array(
					'data_callback' => function() use ( $dates, $currency ) {
						$stats = new EDD\Stats();
						$g = $stats->get_gateway_sales( array(
							'range'    => $dates['range'],
							'grouped'  => true,
							'currency' => $currency
						) );

						$gateways = array_flip( array_keys( edd_get_payment_gateways() ) );

						foreach ( $g as $data ) {
							$gateways[ $data->gateway ] = $data->total;
						}

						$gateways = array_map( function( $v ) {
							return null === $v
								? 0
								: $v;
						}, $gateways );

						return array(
							'sales' => array_values( $gateways ),
						);
					},
					'type' => 'pie',
					'options' => array(
						'cutoutPercentage' => 0,
						'datasets'         => array(
							'sales' => array(
								'label'           => __( 'Sales', 'easy-digital-downloads' ),
								'backgroundColor' => array(
									'rgb(133,175,91)',
									'rgb(9,149,199)',
									'rgb(8,189,231)',
									'rgb(137,163,87)',
									'rgb(27,98,122)',
								),
							),
						),
						'labels' => $gateway_list,
					),
				),
			)
		) );

		$reports->register_endpoint( 'gateway_earnings_breakdown', array(
			'label' => __( 'Gateway Earnings', 'easy-digital-downloads' ) . ' &mdash; ' . $options[ $dates['range'] ],
			'views' => array(
				'chart' => array(
					'data_callback' => function() use ( $dates, $exclude_taxes, $currency ) {
						$stats = new EDD\Stats();
						$g = $stats->get_gateway_earnings( array(
							'grouped'       => true,
							'range'         => $dates['range'],
							'exclude_taxes' => $exclude_taxes,
							'currency'      => $currency
						) );

						$gateways = array_flip( array_keys( edd_get_payment_gateways() ) );

						foreach ( $g as $data ) {
							$gateways[ $data->gateway ] = $data->earnings;
						}

						$gateways = array_values( array_map( function( $v ) {
							return null === $v
								? 0.00
								: $v;
						}, $gateways ) );

						return array(
							'earnings' => $gateways,
						);
					},
					'type' => 'pie',
					'options' => array(
						'cutoutPercentage' => 0,
						'datasets'         => array(
							'earnings' => array(
								'label'           => __( 'Earnings', 'easy-digital-downloads' ),
								'backgroundColor' => array(
									'rgb(133,175,91)',
									'rgb(9,149,199)',
									'rgb(8,189,231)',
									'rgb(137,163,87)',
									'rgb(27,98,122)',
								),
								'type'            => 'currency',
							),
						),
						'labels' => $gateway_list,
					),
				),
			)
		) );

		$reports->register_endpoint( 'gateway_sales_earnings_chart', array(
			'label' => __( 'Sales and Earnings', 'easy-digital-downloads' ) . ' &mdash; ' . $label,
			'views' => array(
				'chart' => array(
					'data_callback' => function () use ( $dates, $exclude_taxes, $currency ) {
						global $wpdb;

						$dates        = Reports\get_dates_filter( 'objects' );
						$day_by_day   = Reports\get_dates_filter_day_by_day();
						$hour_by_hour = Reports\get_dates_filter_hour_by_hour();

						$sql_clauses = array(
							'select'  => 'YEAR(date_created) AS year, MONTH(date_created) AS month, DAY(date_created) AS day',
							'groupby' => 'YEAR(date_created), MONTH(date_created), DAY(date_created)',
							'orderby' => 'YEAR(date_created), MONTH(date_created), DAY(date_created)',
						);

						if ( $hour_by_hour ) {
							$sql_clauses = array(
								'select'  => 'YEAR(date_created) AS year, MONTH(date_created) AS month, DAY(date_created) AS day, HOUR(date_created) AS hour',
								'groupby' => 'YEAR(date_created), MONTH(date_created), DAY(date_created), HOUR(date_created)',
								'orderby' => 'YEAR(date_created), MONTH(date_created), DAY(date_created), HOUR(date_created)',
							);
						} elseif ( ! $day_by_day ) {
							$sql_clauses = array(
								'select'  => 'YEAR(date_created) AS year, MONTH(date_created) AS month',
								'groupby' => 'YEAR(date_created), MONTH(date_created)',
								'orderby' => 'YEAR(date_created), MONTH(date_created)',
							);
						}

						$gateway = Reports\get_filter_value( 'gateways' );
						$column  = $exclude_taxes
							? '( total - tax ) / rate'
							: 'total / rate';

						$currency_sql = '';
						if ( ! empty( $currency ) && array_key_exists( strtoupper( $currency ), edd_get_currencies() ) ) {
							$currency_sql = $wpdb->prepare(
								" AND currency = %s ",
								strtoupper( $currency )
							);
						}

						$results = $wpdb->get_results( $wpdb->prepare(
							"SELECT COUNT({$column}) AS sales, SUM({$column}) AS earnings, {$sql_clauses['select']}
							FROM {$wpdb->edd_orders} o
							WHERE gateway = %s AND status IN ('complete', 'revoked') {$currency_sql} AND date_created >= %s AND date_created <= %s
							GROUP BY {$sql_clauses['groupby']}
							ORDER BY {$sql_clauses['orderby']} ASC",
							esc_sql( $gateway ), $dates['start']->copy()->format( 'mysql' ), $dates['end']->copy()->format( 'mysql' ) ) );

						$sales = array();
						$earnings = array();

						// Initialise all arrays with timestamps and set values to 0.
						while ( strtotime( $dates['start']->copy()->format( 'mysql' ) ) <= strtotime( $dates['end']->copy()->format( 'mysql' ) ) ) {
							if ( $hour_by_hour ) {
								$timestamp = \Carbon\Carbon::create( $dates['start']->year, $dates['start']->month, $dates['start']->day, $dates['start']->hour, 0, 0, 'UTC' )->setTimezone( edd_get_timezone_id() )->timestamp;

								$sales[ $timestamp ][] = $timestamp;
								$sales[ $timestamp ][] = 0;

								$earnings[ $timestamp ][] = $timestamp;
								$earnings[ $timestamp ][] = 0.00;

								$dates['start']->addHour( 1 );
							} else {
								$day = ( true === $day_by_day )
									? $dates['start']->day
									: 1;

								$timestamp = \Carbon\Carbon::create( $dates['start']->year, $dates['start']->month, $day, 0, 0, 0, 'UTC' )->setTimezone( edd_get_timezone_id() )->timestamp;

								$sales[ $timestamp ][] = $timestamp;
								$sales[ $timestamp ][] = 0;

								$earnings[ $timestamp ][] = $timestamp;
								$earnings[ $timestamp ][] = 0.00;

								$dates['start'] = ( true === $day_by_day )
									? $dates['start']->addDays( 1 )
									: $dates['start']->addMonth( 1 );
							}
						}

						foreach ( $results as $result ) {
							if ( $hour_by_hour ) {

								/**
								 * If this is hour by hour, the database returns the timestamps in UTC and an offset
								 * needs to be applied to that.
								 */
								$timestamp = \Carbon\Carbon::create( $result->year, $result->month, $result->day, $result->hour, 0, 0, 'UTC' )->setTimezone( edd_get_timezone_id() )->timestamp;
							} else {
								$day = ( true === $day_by_day )
									? $result->day
									: 1;

								$timestamp = \Carbon\Carbon::create( $result->year, $result->month, $day, 0, 0, 0, 'UTC' )->setTimezone( edd_get_timezone_id() )->timestamp;
							}

							$sales[ $timestamp ][1]    += $result->sales;
							$earnings[ $timestamp ][1] += floatval( $result->earnings );
						}

						$sales    = array_values( $sales );
						$earnings = array_values( $earnings );

						return array(
							'sales'    => $sales,
							'earnings' => $earnings,
						);
					},
					'type'          => 'line',
					'options'       => array(
						'datasets' => array(
							'sales'    => array(
								'label'                => __( 'Sales', 'easy-digital-downloads' ),
								'borderColor'          => 'rgb(252,108,18)',
								'backgroundColor'      => 'rgba(252,108,18,0.2)',
								'fill'                 => true,
								'borderDash'           => array( 2, 6 ),
								'borderCapStyle'       => 'round',
								'borderJoinStyle'      => 'round',
								'pointRadius'          => 4,
								'pointHoverRadius'     => 6,
								'pointBackgroundColor' => 'rgb(255,255,255)',
							),
							'earnings' => array(
								'label'                => __( 'Earnings', 'easy-digital-downloads' ),
								'borderColor'          => 'rgb(24,126,244)',
								'backgroundColor'      => 'rgba(24,126,244,0.05)',
								'fill'                 => true,
								'borderWidth'          => 2,
								'type'                 => 'currency',
								'pointRadius'          => 4,
								'pointHoverRadius'     => 6,
								'pointBackgroundColor' => 'rgb(255,255,255)',
							),
						),
					),
				),
			),
		) );
	} catch ( \EDD_Exception $exception ) {
		edd_debug_log_exception( $exception );
	}
}
add_action( 'edd_reports_init', 'edd_register_payment_gateways_report' );

/**
 * Register taxes report and endpoints.
 *
 * @since 3.0
 *
 * @param \EDD\Reports\Data\Report_Registry $reports Report registry.
 */
function edd_register_taxes_report( $reports ) {
	try {

		// Variables to hold date filter values.
		$options  = Reports\get_dates_filter_options();
		$dates    = Reports\get_filter_value( 'dates' );
		$currency = Reports\get_filter_value( 'currencies' );

		$hbh   = Reports\get_dates_filter_hour_by_hour();
		$label = $options[ $dates['range'] ] . ( $hbh ? ' (' . edd_get_timezone_abbr() . ')' : '' );

		$download_data = Reports\get_filter_value( 'products' );
		$download_data = ! empty( $download_data ) && 'all' !== Reports\get_filter_value( 'products' )
			? edd_parse_product_dropdown_value( Reports\get_filter_value( 'products' ) )
			: false;

		$download_label = '';
		if ( $download_data ) {
			$download = edd_get_download( $download_data['download_id'] );
			if ( isset( $download_data['price_id'] ) && is_numeric( $download_data['price_id'] ) ) {
				$args       = array( 'price_id' => $download_data['price_id'] );
				$price_name = edd_get_price_name( $download->ID, $args );
				if ( $price_name ) {
					$download->post_title .= ': ' . $price_name;
				}
			}
			$download_label = esc_html( ' (' . $download->post_title . ')' );
		}

		$country = Reports\get_filter_value( 'countries' );
		$region  = Reports\get_filter_value( 'regions' );

		$tiles = array(
			'total_tax_collected',
			'total_tax_collected_for_location',
		);

		$tables = array_filter( array(
			'tax_collected_by_location',
		), function( $table ) use ( $download_data ) {
			return false === $download_data;
		} );

		$reports->add_report( 'taxes', array(
			'label'     => __( 'Taxes', 'easy-digital-downloads' ),
			'priority'  => 25,
			'icon'      => 'editor-paste-text',
			'endpoints' => array(
				'tiles'  => $tiles,
				'tables' => $tables,
			),
			'filters'   => array( 'dates', 'products', 'countries', 'regions', 'currencies' ),
		) );

		$reports->register_endpoint( 'total_tax_collected', array(
			'label' => __( 'Total Tax Collected', 'easy-digital-downloads' ),
			'views' => array(
				'tile' => array(
					'data_callback' => function () use ( $dates, $currency ) {
						$download = Reports\get_filter_value( 'products' );
						$download = ! empty( $download ) && 'all' !== Reports\get_filter_value( 'products' )
							? edd_parse_product_dropdown_value( Reports\get_filter_value( 'products' ) )
							: array( 'download_id' => '', 'price_id' => '' );

						$stats = new EDD\Stats();
						return $stats->get_tax( array(
							'output'      => 'formatted',
							'range'       => $dates['range'],
							'download_id' => $download['download_id'],
							'price_id'    => (string) $download['price_id'],
							'currency'    => $currency
						) );
					},
					'display_args'  => array(
						'comparison_label' => $label . $download_label,
					),
				),
			),
		) );

		if ( ! empty( $country ) && 'all' !== $country ) {
			$location = '';

			if ( ! empty( $region ) && 'all' !== $region ) {
				$location = edd_get_state_name( $country, $region ) . ', ';
			}

			$location .= edd_get_country_name( $country );

			$reports->register_endpoint( 'total_tax_collected_for_location', array(
				'label' => __( 'Total Tax Collected for ', 'easy-digital-downloads' ) . $location,
				'views' => array(
					'tile' => array(
						'data_callback' => function () use ( $dates, $country, $region, $currency ) {
							$download = Reports\get_filter_value( 'products' );
							$download = ! empty( $download ) && 'all' !== Reports\get_filter_value( 'products' )
								? edd_parse_product_dropdown_value( Reports\get_filter_value( 'products' ) )
								: array( 'download_id' => '', 'price_id' => '' );

							$stats = new EDD\Stats();

							return $stats->get_tax_by_location( array(
								'output'      => 'formatted',
								'range'       => $dates['range'],
								'download_id' => $download['download_id'],
								'price_id'    => (string) $download['price_id'],
								'country'     => $country,
								'region'      => $region,
								'currency'    => $currency
							) );
						},
						'display_args'  => array(
							'comparison_label' => $label . $download_label,
						),
					),
				),
			) );
		}

		$reports->register_endpoint( 'tax_collected_by_location', array(
			'label' => __( 'Tax Collected by Location', 'easy-digital-downloads' ),
			'views' => array(
				'table' => array(
					'display_args' => array(
						'class_name' => '\\EDD\\Reports\\Data\\Taxes\\Tax_Collected_By_Location',
						'class_file' => EDD_PLUGIN_DIR . 'includes/reports/data/taxes/class-tax-collected-by-location-list-table.php',
					),
				),
			),
		) );
	} catch ( \EDD_Exception $exception ) {
		edd_debug_log_exception( $exception );
	}
}
add_action( 'edd_reports_init', 'edd_register_taxes_report' );

/**
 * Register file downloads report and endpoints.
 *
 * @since 3.0
 *
 * @param \EDD\Reports\Data\Report_Registry $reports Report registry.
 */
function edd_register_file_downloads_report( $reports ) {
	try {

		// Variables to hold date filter values.
		$options = Reports\get_dates_filter_options();
		$filter  = Reports\get_filter_value( 'dates' );

		$hbh   = Reports\get_dates_filter_hour_by_hour();
		$label = $options[ $filter['range'] ] . ( $hbh ? ' (' . edd_get_timezone_abbr() . ')' : '' );

		$download_data = Reports\get_filter_value( 'products' );
		$download_data = ! empty( $download_data ) && 'all' !== Reports\get_filter_value( 'products' )
			? edd_parse_product_dropdown_value( Reports\get_filter_value( 'products' ) )
			: false;

		$download_label = '';
		if ( $download_data ) {
			$download = edd_get_download( $download_data['download_id'] );
			if ( isset( $download_data['price_id'] ) && is_numeric( $download_data['price_id'] ) ) {
				$args       = array( 'price_id' => $download_data['price_id'] );
				$price_name = edd_get_price_name( $download->ID, $args );
				if ( $price_name ) {
					$download->post_title .= ': ' . $price_name;
				}
			}
			$download_label = esc_html( ' (' . $download->post_title . ')' );
		}

		$tiles = array_filter( array(
			'number_of_file_downloads',
			'average_file_downloads_per_customer',
			'most_downloaded_product',
			'average_file_downloads_per_order',
		), function( $endpoint ) use ( $download_data ) {
			switch( $endpoint ) {
				case 'average_file_downloads_per_customer':
				case 'most_downloaded_product':
				case 'average_file_downloads_per_order':
					return false === $download_data;
					break;
				default:
					return true;
			}
		} );

		$tables = array_filter( array(
			'top_five_most_downloaded_products',
		), function( $endpoint ) use ( $download_data ) {
			return false === $download_data;
		} );

		$charts = array(
			'file_downloads_chart',
		);

		$reports->add_report( 'file_downloads', array(
			'label'     => __( 'File Downloads', 'easy-digital-downloads' ),
			'icon'      => 'download',
			'priority'  => 30,
			'endpoints' => array(
				'tiles'  => $tiles,
				'tables' => $tables,
				'charts' => $charts,
			),
			'filters'   => array( 'dates', 'products' ),
		) );

		$reports->register_endpoint( 'number_of_file_downloads', array(
			'label' => __( 'Number of File Downloads', 'easy-digital-downloads' ),
			'views' => array(
				'tile' => array(
					'data_callback' => function () use ( $filter ) {
						$download = Reports\get_filter_value( 'products' );
						$download = ! empty( $download ) && 'all' !== Reports\get_filter_value( 'products' )
							? edd_parse_product_dropdown_value( Reports\get_filter_value( 'products' ) )
							: array( 'download_id' => '', 'price_id' => '' );

						$stats = new EDD\Stats();
						return $stats->get_file_download_count( array(
							'range'       => $filter['range'],
							'download_id' => $download['download_id'],
							'price_id'    => (string) $download['price_id'],
						) );
					},
					'display_args'  => array(
						'comparison_label' => $label . $download_label,
					),
				),
			),
		) );

		$reports->register_endpoint( 'average_file_downloads_per_customer', array(
			'label' => __( 'Average per Customer', 'easy-digital-downloads' ),
			'views' => array(
				'tile' => array(
					'data_callback' => function () use ( $filter ) {
						$stats = new EDD\Stats();
						return $stats->get_average_file_download_count( array(
							'range'  => $filter['range'],
							'column' => 'customer_id',
						) );
					},
					'display_args'  => array(
						'comparison_label' => $label,
					),
				),
			),
		) );

		$reports->register_endpoint( 'average_file_downloads_per_order', array(
			'label' => __( 'Average per Order', 'easy-digital-downloads' ),
			'views' => array(
				'tile' => array(
					'data_callback' => function () use ( $filter ) {
                        $stats = new EDD\Stats();
                        return $stats->get_average_file_download_count( array(
	                        'range'  => $filter['range'],
	                        'column' => 'order_id',
                        ) );
					},
					'display_args'  => array(
						'comparison_label' => $label,
					),
				),
			),
		) );

		$reports->register_endpoint( 'most_downloaded_product', array(
			'label' => __( 'Most Downloaded Product', 'easy-digital-downloads' ),
			'views' => array(
				'tile' => array(
					'data_callback' => function () use ( $filter ) {
						$stats = new EDD\Stats();
						$d = $stats->get_most_downloaded_products( array( 'range' => $filter['range'] ) );
						if ( $d ) {
							return esc_html( $d[0]->object->post_title );
						}
					},
					'display_args'  => array(
						'comparison_label' => $label,
					),
				),
			),
		) );

		$reports->register_endpoint( 'top_five_most_downloaded_products', array(
			'label' => __( 'Top Five Most Downloaded Products', 'easy-digital-downloads' ) . ' – ' . $label,
			'views' => array(
				'table' => array(
					'display_args' => array(
						'class_name' => '\\EDD\\Reports\\Data\\File_Downloads\\Top_Five_Most_Downloaded_List_Table',
						'class_file' => EDD_PLUGIN_DIR . 'includes/reports/data/file-downloads/class-top-five-most-downloaded-list-table.php',
					),
				),
			),
		) );

		$reports->register_endpoint( 'file_downloads_chart', array(
			'label' => __( 'Number of File Downloads', 'easy-digital-downloads' ) . $download_label,
			'views' => array(
				'chart' => array(
					'data_callback' => function () use ( $filter, $download_data ) {
						global $wpdb;

						$dates        = Reports\get_dates_filter( 'objects' );
						$chart_dates  = Reports\parse_dates_for_range( null, 'now', false );
						$day_by_day   = Reports\get_dates_filter_day_by_day();
						$hour_by_hour = Reports\get_dates_filter_hour_by_hour();

						$sql_clauses = array(
							'select' => 'date_created AS date',
							'where'  => '',
						);

						// Default to 'monthly'.
						$sql_clauses['groupby'] = 'MONTH(date_created)';
						$sql_clauses['orderby'] = 'MONTH(date_created)';

						// Now drill down to the smallest unit.
						if ( $hour_by_hour ) {
							$sql_clauses['groupby'] = 'HOUR(date_created)';
							$sql_clauses['orderby'] = 'HOUR(date_created)';
						} elseif ( $day_by_day ) {
							$sql_clauses['groupby'] = 'DATE(date_created)';
							$sql_clauses['orderby'] = 'DATE(date_created)';
						}

						$product_id = '';
						$price_id   = '';

						if ( is_array( $download_data ) ) {
							$product_id = $wpdb->prepare( 'AND product_id = %d', absint( $download_data['download_id'] ) );

							$price_id = isset( $download_data['price_id'] ) && is_numeric( $download_data['price_id'] )
								? $wpdb->prepare( 'AND price_id = %d', absint( $download_data['price_id'] ) )
								: '';
						}

						$results = $wpdb->get_results( $wpdb->prepare(
							"SELECT COUNT(id) AS total, {$sql_clauses['select']}
							FROM {$wpdb->edd_logs_file_downloads} edd_lfd
							WHERE edd_lfd.date_created >= %s AND edd_lfd.date_created <= %s {$product_id} {$price_id}
							GROUP BY {$sql_clauses['groupby']}
							ORDER BY {$sql_clauses['orderby']} ASC",
							$dates['start']->copy()->format( 'mysql' ), $dates['end']->copy()->format( 'mysql' ) ) );

						$file_downloads = array();

						// Initialise all arrays with timestamps and set values to 0.
						while ( strtotime( $dates['start']->copy()->format( 'mysql' ) ) <= strtotime( $dates['end']->copy()->format( 'mysql' ) ) ) {
							$utc_timezone    = new DateTimeZone( 'UTC' );
							$timezone        = new DateTimeZone( edd_get_timezone_id() );

							$timestamp       = $dates['start']->copy()->format( 'U' );
							$date_on_chart   = new DateTime( $chart_dates['start'], $timezone );

							$file_downloads[ $timestamp ][0] = $timestamp;
							$file_downloads[ $timestamp ][1] = 0;

							foreach ( $results as $result ) {
								$date_of_db_value = new DateTime( $result->date, $utc_timezone );
								$date_of_db_value = $date_of_db_value->setTimeZone( $timezone );

								// Add any file downloads that happened during this hour.
								if ( $hour_by_hour ) {
									// If the date of this db value matches the date on this line graph/chart, set the y axis value for the chart to the number in the DB result.
									if ( $date_of_db_value->format( 'Y-m-d H' ) === $date_on_chart->format( 'Y-m-d H' ) ) {
										$file_downloads[ $timestamp ][1] += absint( $result->total );
									}
									// Add any file downloads that happened during this day.
								} elseif ( $day_by_day ) {
									// If the date of this db value matches the date on this line graph/chart, set the y axis value for the chart to the number in the DB result.
									if ( $date_of_db_value->format( 'Y-m-d' ) === $date_on_chart->format( 'Y-m-d' ) ) {
										$file_downloads[ $timestamp ][1] += absint( $result->total );
									}
									// Add any file downloads that happened during this month.
								} else {
									// If the date of this db value matches the date on this line graph/chart, set the y axis value for the chart to the number in the DB result.
									if ( $date_of_db_value->format( 'Y-m' ) === $date_on_chart->format( 'Y-m' ) ) {
										$file_downloads[ $timestamp ][1] += absint( $result->total );
									}
								}
							}

							// Move the chart along to the next hour/day/month to get ready for the next loop.
							if ( $hour_by_hour ) {
								$dates['start']->addHour( 1 );
								$chart_dates['start']->addHour( 1 );
							} elseif ( $day_by_day ) {
								$dates['start']->addDays( 1 );
								$chart_dates['start']->addDays( 1 );
							} else {
								$dates['start']->addMonth( 1 );
								$chart_dates['start']->addMonth( 1 );
							}
						}

						$file_downloads = array_values( $file_downloads );

						return array( 'file_downloads' => $file_downloads );
					},
					'type'          => 'line',
					'options'       => array(
						'datasets' => array(
							'file_downloads' => array(
								'label'                => __( 'File Downloads', 'easy-digital-downloads' ),
								'borderColor'          => 'rgb(24,126,244)',
								'backgroundColor'      => 'rgba(24,126,244,0.05)',
								'fill'                 => true,
								'borderWidth'          => 2,
								'pointRadius'          => 4,
								'pointHoverRadius'     => 6,
								'pointBackgroundColor' => 'rgb(255,255,255)',
							),
						),
					),
				),
			),
		) );
	} catch ( \EDD_Exception $exception ) {
		edd_debug_log_exception( $exception );
	}
}
add_action( 'edd_reports_init', 'edd_register_file_downloads_report' );

/**
 * Register discounts report and endpoints.
 *
 * @since 3.0
 *
 * @param \EDD\Reports\Data\Report_Registry $reports Report registry.
 */
function edd_register_discounts_report( $reports ) {
	try {

		// Variables to hold date filter values.
		$options  = Reports\get_dates_filter_options();
		$filter   = Reports\get_filter_value( 'dates' );
		$currency = Reports\get_filter_value( 'currencies' );

		$hbh   = Reports\get_dates_filter_hour_by_hour();
		$label = $options[ $filter['range'] ] . ( $hbh ? ' (' . edd_get_timezone_abbr() . ')' : '' );

		$discount = Reports\get_filter_value( 'discounts' );
		$discount = ! empty( $discount ) && 'all' !== $discount
			? $discount
			: 0;

		$d = edd_get_discount( $discount );

		$discount_label = false !== $d
			? esc_html( ' (' . $d->name . ')' )
			: '';

		$tiles = array_filter( array(
			'number_of_discounts_used',
			'ratio_of_discounted_orders',
			'customer_savings',
			'average_discount_amount',
			'most_popular_discount',
			'discount_usage_count',
		), function( $tile ) use ( $discount ) {
			switch ( $tile ) {
				case 'discount_usage_count':
					return 0 !== $discount;
					break;
				default:
					return 0 === $discount;
			}
		} );

		$tables = array_filter( array(
			'top_five_discounts',
		), function( $table ) use ( $discount ) {
			return 0 === $discount;
		} );

		$charts = array(
			'discount_usage_chart',
		);

		$reports->add_report( 'discounts', array(
			'label'     => __( 'Discounts', 'easy-digital-downloads' ),
			'icon'      => 'tickets-alt',
			'priority'  => 35,
			'endpoints' => array(
				'tiles'  => $tiles,
				'tables' => $tables,
				'charts' => $charts,
			),
			'filters'   => array( 'dates', 'discounts' ),
		) );

		$reports->register_endpoint( 'number_of_discounts_used', array(
			'label' => __( 'Number of Discounts Used', 'easy-digital-downloads' ),
			'views' => array(
				'tile' => array(
					'data_callback' => function () use ( $filter ) {
						$stats = new EDD\Stats();
						return $stats->get_discount_usage_count( array(
							'range'    => $filter['range'],
						) );
					},
					'display_args'  => array(
						'comparison_label' => $label,
					),
				),
			),
		) );

		$reports->register_endpoint( 'ratio_of_discounted_orders', array(
			'label' => __( 'Discount Ratio', 'easy-digital-downloads' ),
			'views' => array(
				'tile' => array(
					'data_callback' => function () use ( $filter ) {
						$stats = new EDD\Stats();
						return $stats->get_ratio_of_discounted_orders( array(
							'range'    => $filter['range'],
						) );
					},
					'display_args'  => array(
						'context'          => 'secondary',
						'comparison_label' => $label,
					),
				),
			),
		) );

		$reports->register_endpoint( 'customer_savings', array(
			'label' => __( 'Customer Savings', 'easy-digital-downloads' ),
			'views' => array(
				'tile' => array(
					'data_callback' => function () use ( $filter, $d ) {
						$stats = new EDD\Stats();
						return $stats->get_discount_savings( array(
							'range'         => $filter['range'],
							'output'        => 'formatted',
							'discount_code' => isset( $d->code )
								? $d->code
								: '',
						) );
					},
					'display_args'  => array(
						'comparison_label' => $label . $discount_label,
					),
				),
			),
		) );

		$reports->register_endpoint( 'average_discount_amount', array(
			'label' => __( 'Average Discount Amount', 'easy-digital-downloads' ),
			'views' => array(
				'tile' => array(
					'data_callback' => function () use ( $filter ) {
						$stats = new EDD\Stats();
						return $stats->get_average_discount_amount( array(
							'range'    => $filter['range'],
							'output'   => 'formatted',
						) );
					},
					'display_args'  => array(
						'comparison_label' => $label,
					),
				),
			),
		) );

		$reports->register_endpoint( 'most_popular_discount', array(
			'label' => __( 'Most Popular Discount', 'easy-digital-downloads' ),
			'views' => array(
				'tile' => array(
					'data_callback' => function () use ( $filter ) {
						$stats = new EDD\Stats();

						$r = $stats->get_most_popular_discounts( array(
							'range'    => $filter['range'],
							'number'   => 1,
						) );

						if ( ! empty( $r ) ) {
							$r = $r[0];
							return esc_html( $r->code . ' (' . $r->count . ')' );
						}
					},
					'display_args'  => array(
						'comparison_label' => $label,
					),
				),
			),
		) );

		if ( $d ) {
			$reports->register_endpoint( 'discount_usage_count', array(
				'label' => __( 'Discount Usage Count', 'easy-digital-downloads' ),
				'views' => array(
					'tile' => array(
						'data_callback' => function () use ( $filter, $d ) {
							$stats = new EDD\Stats();
							return $stats->get_discount_usage_count( array(
								'range'         => $filter['range'],
								'discount_code' => $d->code,
							) );
						},
						'display_args'  => array(
							'comparison_label' => $label . $discount_label,
						),
					),
				),
			) );
		}

		$reports->register_endpoint( 'top_five_discounts', array(
			'label' => __( 'Top Five Discounts', 'easy-digital-downloads' ) . ' – ' . $label,
			'views' => array(
				'table' => array(
					'display_args' => array(
						'class_name' => '\\EDD\\Reports\\Data\\Discounts\\Top_Five_Discounts_List_Table',
						'class_file' => EDD_PLUGIN_DIR . 'includes/reports/data/discounts/class-top-five-discounts-list-table.php',
					),
				),
			),
		) );

		if ( $d ) {
			$reports->register_endpoint( 'discount_usage_chart', array(
				'label' => __( 'Discount Usage', 'easy-digital-downloads' ),
				'views' => array(
					'chart' => array(
						'data_callback' => function () use ( $filter, $d ) {
							global $wpdb;

							$dates        = Reports\get_dates_filter( 'objects' );
							$day_by_day   = Reports\get_dates_filter_day_by_day();
							$hour_by_hour = Reports\get_dates_filter_hour_by_hour();

							$sql_clauses = array(
								'select'  => 'YEAR(edd_oa.date_created) AS year, MONTH(edd_oa.date_created) AS month, DAY(edd_oa.date_created) AS day',
								'groupby' => 'YEAR(edd_oa.date_created), MONTH(edd_oa.date_created), DAY(edd_oa.date_created)',
								'orderby' => 'YEAR(edd_oa.date_created), MONTH(edd_oa.date_created), DAY(edd_oa.date_created)',
							);

							if ( $hour_by_hour ) {
								$sql_clauses = array(
									'select'  => 'YEAR(edd_oa.date_created) AS year, MONTH(edd_oa.date_created) AS month, DAY(edd_oa.date_created) AS day, HOUR(edd_oa.date_created) AS hour',
									'groupby' => 'YEAR(edd_oa.date_created), MONTH(edd_oa.date_created), DAY(edd_oa.date_created), HOUR(edd_oa.date_created)',
									'orderby' => 'YEAR(edd_oa.date_created), MONTH(edd_oa.date_created), DAY(edd_oa.date_created), HOUR(edd_oa.date_created)',
								);
							} elseif ( ! $day_by_day ) {
								$sql_clauses = array(
									'select'  => 'YEAR(edd_oa.date_created) AS year, MONTH(edd_oa.date_created) AS month',
									'groupby' => 'YEAR(edd_oa.date_created), MONTH(edd_oa.date_created)',
									'orderby' => 'YEAR(edd_oa.date_created), MONTH(edd_oa.date_created)',
								);
							}

							$discount_code = ! empty( $d->code )
								? $wpdb->prepare( 'AND type = %s AND description = %s', 'discount', esc_sql( $d->code ) )
								: $wpdb->prepare( 'AND type = %s', 'discount' );

							$results = $wpdb->get_results( $wpdb->prepare(
								"SELECT COUNT(id) AS total, {$sql_clauses['select']}
								 FROM {$wpdb->edd_order_adjustments} edd_oa
								 WHERE 1=1 {$discount_code} AND edd_oa.date_created >= %s AND edd_oa.date_created <= %s
								 GROUP BY {$sql_clauses['groupby']}
								 ORDER BY {$sql_clauses['orderby']} ASC",
								$dates['start']->copy()->format( 'mysql' ), $dates['end']->copy()->format( 'mysql' ) ) );

							$discount_usage = array();

							// Initialise all arrays with timestamps and set values to 0.
							while ( strtotime( $dates['start']->copy()->format( 'mysql' ) ) <= strtotime( $dates['end']->copy()->format( 'mysql' ) ) ) {
								if ( $hour_by_hour ) {
									$timestamp = \Carbon\Carbon::create( $dates['start']->year, $dates['start']->month, $dates['start']->day, $dates['start']->hour, 0, 0, 'UTC' )->setTimezone( edd_get_timezone_id() )->timestamp;

									$discount_usage[ $timestamp ][] = $timestamp;
									$discount_usage[ $timestamp ][] = 0;

									$dates['start']->addHour( 1 );
								} else {
									$day = ( true === $day_by_day )
										? $dates['start']->day
										: 1;

									$timestamp = \Carbon\Carbon::create( $dates['start']->year, $dates['start']->month, $day, 0, 0, 0, 'UTC' )->setTimezone( edd_get_timezone_id() )->timestamp;

									$discount_usage[ $timestamp ][] = $timestamp;
									$discount_usage[ $timestamp ][] = 0;

									$dates['start'] = ( true === $day_by_day )
										? $dates['start']->addDays( 1 )
										: $dates['start']->addMonth( 1 );
								}
							}

							foreach ( $results as $result ) {
								if ( $hour_by_hour ) {

									/**
									 * If this is hour by hour, the database returns the timestamps in UTC and an offset
									 * needs to be applied to that.
									 */
									$timestamp = \Carbon\Carbon::create( $result->year, $result->month, $result->day, $result->hour, 0, 0, 'UTC' )->setTimezone( edd_get_timezone_id() )->timestamp;
								} else {
									$day = ( true === $day_by_day )
										? $result->day
										: 1;

									$timestamp = \Carbon\Carbon::create( $result->year, $result->month, $day, 0, 0, 0, 'UTC' )->setTimezone( edd_get_timezone_id() )->timestamp;
								}

								if ( array_key_exists( $timestamp, $discount_usage ) ) {
									$discount_usage[ $timestamp ][1] += $result->total;
								}
							}

							$discount_usage = array_values( $discount_usage );

							return array( 'discount_usage' => $discount_usage );
						},
						'type'          => 'line',
						'options'       => array(
							'datasets' => array(
								'discount_usage' => array(
									'label'                => __( 'Discount Usage', 'easy-digital-downloads' ),
									'borderColor'          => 'rgb(24,126,244)',
									'backgroundColor'      => 'rgba(24,126,244,0.05)',
									'fill'                 => true,
									'borderWidth'          => 2,
									'pointRadius'          => 4,
									'pointHoverRadius'     => 6,
									'pointBackgroundColor' => 'rgb(255,255,255)',
								),
							),
						),
					),
				),
			) );
		}
	} catch ( \EDD_Exception $exception ) {
		edd_debug_log_exception( $exception );
	}
}
add_action( 'edd_reports_init', 'edd_register_discounts_report' );

/**
 * Register customer report and endpoints.
 *
 * @since 3.0
 *
 * @param \EDD\Reports\Data\Report_Registry $reports Report registry.
 */
function edd_register_customer_report( $reports ) {
	try {

		// Variables to hold date filter values.
		$options       = Reports\get_dates_filter_options();
		$dates         = Reports\get_filter_value( 'dates' );
		$exclude_taxes = Reports\get_taxes_excluded_filter();

		$hbh   = Reports\get_dates_filter_hour_by_hour();
		$label = $options[ $dates['range'] ] . ( $hbh ? ' (' . edd_get_timezone_abbr() . ')' : '' );

		$reports->add_report( 'customers', array(
			'label'     => __( 'Customers', 'easy-digital-downloads' ),
			'icon'      => 'groups',
			'priority'  => 40,
			'endpoints' => array(
				'tiles'  => array(
					'new_customer_growth',
					'average_revenue_per_customer',
					'average_number_of_orders_per_customer',
				),
				'tables' => array(
					'top_five_customers',
					'most_valuable_customers',
				),
				'charts' => array(
					'new_customers',
				),
			),
		) );

		$reports->register_endpoint( 'new_customer_growth', array(
			'label' => __( 'New Customers', 'easy-digital-downloads' ),
			'views' => array(
				'tile' => array(
					'data_callback' => function () use ( $dates ) {
						$stats = new EDD\Stats();
						return $stats->get_customer_count( array(
							'range'    => $dates['range'],
							'relative' => true,
						) );
					},
					'display_args'  => array(
						'comparison_label' => $label,
					),
				),
			),
		) );

		$reports->register_endpoint( 'average_revenue_per_customer', array(
			'label' => __( 'Average Revenue per Customer', 'easy-digital-downloads' ),
			'views' => array(
				'tile' => array(
					'data_callback' => function () {
						$stats = new EDD\Stats();
						return $stats->get_customer_lifetime_value( array(
							'function' => 'AVG',
							'output'   => 'formatted',
						) );
					},
				),
			),
		) );

		$reports->register_endpoint( 'average_number_of_orders_per_customer', array(
			'label' => __( 'Average Orders per Customer', 'easy-digital-downloads' ),
			'views' => array(
				'tile' => array(
					'data_callback' => function () use ( $dates ) {
						$stats = new EDD\Stats();
						return $stats->get_customer_order_count( array(
							'range'    => $dates['range'],
							'function' => 'AVG',
							'relative' => true,
						) );
					},
				),
			),
		) );

		$reports->register_endpoint( 'top_five_customers', array(
			'label' => __( 'Top Five Customers &mdash; All Time', 'easy-digital-downloads' ),
			'views' => array(
				'table' => array(
					'display_args' => array(
						'class_name' => '\\EDD\\Reports\\Data\\Customers\\Top_Five_Customers_List_Table',
						'class_file' => EDD_PLUGIN_DIR . 'includes/reports/data/customers/class-top-five-customers-list-table.php',
					),
				),
			),
		) );

		$reports->register_endpoint( 'most_valuable_customers', array(
			'label' => __( 'Most Valuable Customers', 'easy-digital-downloads' ) . ' &mdash; '. $label,
			'views' => array(
				'table' => array(
					'display_args' => array(
						'class_name' => '\\EDD\\Reports\\Data\\Customers\\Most_Valuable_Customers_List_Table',
						'class_file' => EDD_PLUGIN_DIR . 'includes/reports/data/customers/class-most-valuable-customers-list-table.php',
					),
				),
			),
		) );

		$reports->register_endpoint( 'new_customers', array(
			'label' => __( 'New Customers', 'easy-digital-downloads' ) . ' &mdash; ' . $label,
			'views' => array(
				'chart' => array(
					'data_callback' => function () {
						global $wpdb;

						$dates        = Reports\get_dates_filter( 'objects' );
						$day_by_day   = Reports\get_dates_filter_day_by_day();
						$hour_by_hour = Reports\get_dates_filter_hour_by_hour();

						$sql_clauses = array(
							'select'  => 'YEAR(date_created) AS year, MONTH(date_created) AS month, DAY(date_created) AS day',
							'groupby' => 'YEAR(date_created), MONTH(date_created), DAY(date_created)',
							'orderby' => 'YEAR(date_created), MONTH(date_created), DAY(date_created)',
						);

						if ( $hour_by_hour ) {
							$sql_clauses = array(
								'select'  => 'YEAR(date_created) AS year, MONTH(date_created) AS month, DAY(date_created) AS day, HOUR(date_created) AS hour',
								'groupby' => 'YEAR(date_created), MONTH(date_created), DAY(date_created), HOUR(date_created)',
								'orderby' => 'YEAR(date_created), MONTH(date_created), DAY(date_created), HOUR(date_created)',
							);
						} elseif ( ! $day_by_day ) {
							$sql_clauses = array(
								'select'  => 'YEAR(date_created) AS year, MONTH(date_created) AS month',
								'groupby' => 'YEAR(date_created), MONTH(date_created)',
								'orderby' => 'YEAR(date_created), MONTH(date_created)',
							);
						}

						$results = $wpdb->get_results( $wpdb->prepare(
							"SELECT COUNT(c.id) AS total, {$sql_clauses['select']}
					         FROM {$wpdb->edd_customers} c
					         WHERE c.date_created >= %s AND c.date_created <= %s
					         GROUP BY {$sql_clauses['groupby']}
					         ORDER BY {$sql_clauses['orderby']} ASC",
							$dates['start']->copy()->format( 'mysql' ), $dates['end']->copy()->format( 'mysql' ) ) );

						$customers = array();

						// Initialise all arrays with timestamps and set values to 0.
						while ( strtotime( $dates['start']->copy()->format( 'mysql' ) ) <= strtotime( $dates['end']->copy()->format( 'mysql' ) ) ) {
							if ( $hour_by_hour ) {
								$timestamp = \Carbon\Carbon::create( $dates['start']->year, $dates['start']->month, $dates['start']->day, $dates['start']->hour, 0, 0, 'UTC' )->setTimezone( edd_get_timezone_id() )->timestamp;

								$customers[ $timestamp ][] = $timestamp;
								$customers[ $timestamp ][] = 0;

								$earnings[ $timestamp ][] = $timestamp;
								$earnings[ $timestamp ][] = 0.00;

								$dates['start']->addHour( 1 );
							} else {
								$day = ( true === $day_by_day )
									? $dates['start']->day
									: 1;

								$timestamp = \Carbon\Carbon::create( $dates['start']->year, $dates['start']->month, $day, 0, 0, 0, 'UTC' )->setTimezone( edd_get_timezone_id() )->timestamp;

								$customers[ $timestamp ][] = $timestamp;
								$customers[ $timestamp ][] = 0;

								$dates['start'] = ( true === $day_by_day )
									? $dates['start']->addDays( 1 )
									: $dates['start']->addMonth( 1 );
							}
						}

						foreach ( $results as $result ) {
							if ( $hour_by_hour ) {

								/**
								 * If this is hour by hour, the database returns the timestamps in UTC and an offset
								 * needs to be applied to that.
								 */
								$timestamp = \Carbon\Carbon::create( $result->year, $result->month, $result->day, $result->hour, 0, 0, 'UTC' )->setTimezone( edd_get_timezone_id() )->timestamp;
							} else {
								$day = ( true === $day_by_day )
									? $result->day
									: 1;

								$timestamp = \Carbon\Carbon::create( $result->year, $result->month, $day, 0, 0, 0, 'UTC' )->setTimezone( edd_get_timezone_id() )->timestamp;
							}

							$customers[ $timestamp ][1] += $result->total;
						}

						$customers = array_values( $customers );

						return array(
							'customers' => $customers,
						);
					},
					'type'          => 'line',
					'options'       => array(
						'datasets' => array(
							'customers' => array(
								'label'                => __( 'New Customers', 'easy-digital-downloads' ),
								'borderColor'          => 'rgb(24,126,244)',
								'backgroundColor'      => 'rgba(24,126,244,0.05)',
								'fill'                 => true,
								'borderWidth'          => 2,
								'pointRadius'          => 4,
								'pointHoverRadius'     => 6,
								'pointBackgroundColor' => 'rgb(255,255,255)',
							),
						),
					),
				),
			),
		) );
	} catch ( \EDD_Exception $exception ) {
		edd_debug_log_exception( $exception );
	}
}
add_action( 'edd_reports_init', 'edd_register_customer_report' );

/**
 * Register export report and endpoints.
 *
 * @since 3.0
 *
 * @param \EDD\Reports\Data\Report_Registry $reports Report registry.
 */
function edd_register_export_report( $reports ) {
	try {
		$reports->add_report( 'export', array(
			'label'            => __( 'Export', 'easy-digital-downloads' ),
			'icon'             => 'migrate',
			'priority'         => 1000,
			'capability'       => 'export_shop_reports',
			'display_callback' => 'display_export_report',
			'filters'          => false,
		) );
	} catch ( \EDD_Exception $exception ) {
		edd_debug_log_exception( $exception );
	}
}
add_action( 'edd_reports_init', 'edd_register_export_report' );
/**
 * Render the `Export` report.
 *
 * @since 3.0
 */
function display_export_report() {
	global $wpdb;

	wp_enqueue_script( 'edd-admin-tools-export' );
	?>
	<div id="edd-dashboard-widgets-wrap">
		<div class="metabox-holder">
			<div id="post-body">
				<div id="post-body-content" class="edd-reports-export edd-admin--has-grid">

				<?php do_action( 'edd_reports_tab_export_content_top' ); ?>

				<div class="postbox edd-export-earnings-report">
					<h2 class="hndle"><span><?php esc_html_e( 'Export Earnings Report', 'easy-digital-downloads' ); ?></span></h2>
					<div class="inside">
						<p><?php esc_html_e( 'Download a CSV giving a detailed look into earnings over time.', 'easy-digital-downloads' ); ?></p>
						<form id="edd-export-earnings-report" class="edd-export-form edd-import-export-form" method="post">
							<fieldset class="edd-to-and-from-container">
							<legend class="screen-reader-text">
								<?php esc_html_e( 'Export Earnings Start', 'easy-digital-downloads' ); ?>
							</legend>
								<label for="edd_export_earnings_start_month" class="screen-reader-text"><?php esc_html_e( 'Select start month', 'easy-digital-downloads' ); ?></label>
									<?php echo EDD()->html->month_dropdown( 'start_month', 0, 'edd_export_earnings', true ); ?>
								<label for="edd_export_earnings_start_year" class="screen-reader-text"><?php esc_html_e( 'Select start year', 'easy-digital-downloads' ); ?></label>
									<?php echo EDD()->html->year_dropdown( 'start_year', 0, 5, 0, 'edd_export_earnings' ); ?>
							</fieldset>

							<span class="edd-to-and-from--separator"><?php echo _x( '&mdash; to &mdash;', 'Date one to date two', 'easy-digital-downloads' ); ?></span>

							<fieldset class="edd-to-and-from-container">
							<legend class="screen-reader-text">
								<?php esc_html_e( 'Export Earnings End', 'easy-digital-downloads' ); ?>
							</legend>
								<label for="edd_export_earnings_end_month" class="screen-reader-text"><?php esc_html_e( 'Select end month', 'easy-digital-downloads' ); ?></label>
									<?php echo EDD()->html->month_dropdown( 'end_month', 0, 'edd_export_earnings', true ); ?>
								<label for="edd_export_earnings_end_year" class="screen-reader-text"><?php esc_html_e( 'Select end year', 'easy-digital-downloads' ); ?></label>
									<?php echo EDD()->html->year_dropdown( 'end_year', 0, 5, 0, 'edd_export_earnings' ); ?>
							</fieldset>
							<?php wp_nonce_field( 'edd_ajax_export', 'edd_ajax_export' ); ?>
							<input type="hidden" name="edd-export-class" value="EDD_Batch_Earnings_Report_Export"/>
							<button type="submit" class="button button-secondary"><?php esc_html_e( 'Generate CSV', 'easy-digital-downloads' ); ?></button>
						</form>
					</div>
				</div>

				<div class="postbox edd-export-sales-earnings">
					<h2 class="hndle"><span><?php esc_html_e( 'Export Sales and Earnings', 'easy-digital-downloads' ); ?></span></h2>
					<div class="inside">
						<p><?php esc_html_e( 'Download a CSV of all sales or earnings on a day-by-day basis.', 'easy-digital-downloads' ); ?></p>
						<form id="edd-export-sales-earnings" class="edd-export-form edd-import-export-form" method="post">
							<fieldset class="edd-from-to-wrapper">
							<legend class="screen-reader-text">
								<?php esc_html_e( 'Export Sales and Earnings Dates', 'easy-digital-downloads' ); ?>
							</legend>
								<label for="edd-order-export-start" class="screen-reader-text"><?php esc_html_e( 'Set start date', 'easy-digital-downloads' ); ?></label>
									<?php
									echo EDD()->html->date_field(
										array(
											'id'          => 'edd-order-export-start',
											'class'       => 'edd-export-start',
											'name'        => 'order-export-start',
											'placeholder' => _x( 'From', 'date filter', 'easy-digital-downloads' ),
										)
									);
									?>
								<label for="edd-order-export-end" class="screen-reader-text"><?php esc_html_e( 'Set end date', 'easy-digital-downloads' ); ?></label>
									<?php
									echo EDD()->html->date_field(
										array(
											'id'          => 'edd-order-export-end',
											'class'       => 'edd-export-end',
											'name'        => 'order-export-end',
											'placeholder' => _x( 'To', 'date filter', 'easy-digital-downloads' ),
										)
									);

									?>
							</fieldset>
							<label for="edd_orders_export_download" class="screen-reader-text"><?php esc_html_e( 'Select Download', 'easy-digital-downloads' ); ?></label>
								<?php
								echo EDD()->html->product_dropdown(
									array(
										'name'        => 'download_id',
										'id'          => 'edd_orders_export_download',
										'chosen'      => true,
										/* translators: the plural post type label */
										'placeholder' => sprintf( __( 'All %s', 'easy-digital-downloads' ), edd_get_label_plural() ),
									)
								);
								?>
							<label for="edd_order_export_customer" class="screen-reader-text"><?php esc_html_e( 'Select Customer', 'easy-digital-downloads' ); ?></label>
							<?php
								echo EDD()->html->customer_dropdown(
									array(
										'name'          => 'customer_id',
										'id'            => 'edd_order_export_customer',
										'chosen'        => true,
										'none_selected' => '',
										'placeholder'   => __( 'All Customers', 'easy-digital-downloads' ),
									)
								);

								wp_nonce_field( 'edd_ajax_export', 'edd_ajax_export' ); ?>

								<input type="hidden" name="edd-export-class" value="EDD_Batch_Sales_And_Earnings_Export"/>
								<button type="submit" class="button button-secondary"><?php esc_html_e( 'Export', 'easy-digital-downloads' ); ?></button>
							</form>
						</div>
				</div>

				<div class="postbox edd-export-orders">
					<h2 class="hndle"><span><?php esc_html_e( 'Export Orders', 'easy-digital-downloads' ); ?></span></h2>
					<div class="inside">
						<p><?php esc_html_e( 'Download a CSV of all orders.', 'easy-digital-downloads' ); ?></p>
						<form id="edd-export-orders" class="edd-export-form edd-import-export-form" method="post">
							<fieldset class="edd-from-to-wrapper">
							<legend class="screen-reader-text">
								<?php esc_html_e( 'Export Order Dates', 'easy-digital-downloads' ); ?>
							</legend>
								<label for="edd-orders-export-start" class="screen-reader-text"><?php esc_html_e( 'Set start date', 'easy-digital-downloads' ); ?></label>
									<?php
									echo EDD()->html->date_field(
										array(
											'id'          => 'edd-orders-export-start',
											'class'       => 'edd-export-start',
											'name'        => 'orders-export-start',
											'placeholder' => _x( 'From', 'date filter', 'easy-digital-downloads' ),
										)
									);
									?>
								<label for="edd-orders-export-end" class="screen-reader-text"><?php esc_html_e( 'Set end date', 'easy-digital-downloads' ); ?></label>
									<?php
									echo EDD()->html->date_field(
										array(
											'id'          => 'edd-orders-export-end',
											'class'       => 'edd-export-end',
											'name'        => 'orders-export-end',
											'placeholder' => _x( 'To', 'date filter', 'easy-digital-downloads' ),
										)
									);
									?>
							</fieldset>
							<label for="edd_orders_export_status" class="screen-reader-text"><?php esc_html_e( 'Select Status', 'easy-digital-downloads' ); ?></label>
								<?php
									echo EDD()->html->select(
										array(
											'id'               => 'edd_orders_export_status',
											'name'             => 'status',
											'show_option_all'  => __( 'All Statuses', 'easy-digital-downloads' ),
											'show_option_none' => false,
											'selected'         => false,
											'options'          => edd_get_payment_statuses(),
										)
									);

								wp_nonce_field( 'edd_ajax_export', 'edd_ajax_export' );
								?>
							<input type="hidden" name="edd-export-class" value="EDD_Batch_Payments_Export"/>
							<button type="submit" class="button button-secondary"><?php esc_html_e( 'Generate CSV', 'easy-digital-downloads' ); ?></button>
						</form>
					</div>
				</div>

				<div class="postbox edd-export-taxed-orders">
					<h2 class="hndle"><span><?php esc_html_e( 'Export Taxed Orders', 'easy-digital-downloads' ); ?></span></h2>
					<div class="inside">
						<p><?php esc_html_e( 'Download a CSV of all orders, taxed by Country and/or Region.', 'easy-digital-downloads' ); ?></p>
						<form id="edd-export-taxed-orders" class="edd-export-form edd-import-export-form" method="post">
							<fieldset class="edd-from-to-wrapper">
							<legend class="screen-reader-text">
								<?php esc_html_e( 'Export Taxed Order Dates', 'easy-digital-downloads' ); ?>
							</legend>
								<label for="edd-taxed-orders-export-start" class="screen-reader-text"><?php esc_html_e( 'Set start date', 'easy-digital-downloads' ); ?></label>
									<?php
									echo EDD()->html->date_field(
										array(
											'id'          => 'edd-taxed-orders-export-start',
											'class'       => 'edd-export-start',
											'name'        => 'taxed-orders-export-start',
											'placeholder' => _x( 'From', 'date filter', 'easy-digital-downloads' )
										)
									);
									?>
								<label for="edd-taxed-orders-export-end" class="screen-reader-text"><?php esc_html_e( 'Set end date', 'easy-digital-downloads' ); ?></label>
									<?php
									echo EDD()->html->date_field(
										array(
											'id'          => 'edd-taxed-orders-export-end',
											'class'       => 'edd-export-end',
											'name'        => 'taxed-orders-export-end',
											'placeholder' => _x( 'To', 'date filter', 'easy-digital-downloads' )
										)
									);
									?>
							</fieldset>
							<label for="edd_taxed_orders_export_status" class="screen-reader-text"><?php esc_html_e( 'Select Status', 'easy-digital-downloads' ); ?></label>
								<?php
								echo EDD()->html->select(
									array(
										'id'               => 'edd_taxed_orders_export_status',
										'name'             => 'status',
										'show_option_all'  => __( 'All Statuses', 'easy-digital-downloads' ),
										'show_option_none' => false,
										'selected'         => false,
										'options'          => edd_get_payment_statuses(),
									)
								);
								?>
							<label for="edd_reports_filter_taxed_countries" class="screen-reader-text"><?php esc_html_e( 'Select Country', 'easy-digital-downloads' ); ?></label>
								<?php
								echo EDD()->html->country_select(
									array(
										'name'            => 'country',
										'id'              => 'edd_reports_filter_taxed_countries',
										'selected'        => false,
										'show_option_all' => false,
									)
								);
								?>
							<label for="edd_reports_filter_regions" class="screen-reader-text"><?php esc_html_e( 'Select Region', 'easy-digital-downloads' ); ?></label>
								<?php
								echo EDD()->html->region_select(
									array(
										'id'          => 'edd_reports_filter_regions',
										'placeholder' => __( 'All Regions', 'easy-digital-downloads' ),
									)
								);

								wp_nonce_field( 'edd_ajax_export', 'edd_ajax_export' );
								?>
							<input type="hidden" name="edd-export-class" value="EDD_Batch_Taxed_Orders_Export"/>
							<button type="submit" class="button button-secondary"><?php esc_html_e( 'Generate CSV', 'easy-digital-downloads' ); ?></button>
						</form>
					</div>
				</div>

				<div class="postbox edd-export-customers">
					<h2 class="hndle"><span><?php esc_html_e( 'Export Customers', 'easy-digital-downloads' ); ?></span></h2>
					<div class="inside">
						<p><?php printf( esc_html__( 'Download a CSV of customers. Select a taxonomy to see all the customers who purchased %s in that taxonomy.', 'easy-digital-downloads' ), edd_get_label_plural( true ) ); ?></p>
						<form id="edd-export-customers" class="edd-export-form edd-import-export-form" method="post">
							<?php
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

							$taxonomies = array();

							if ( $results ) {
								foreach ( $results as $r ) {
									$t = get_taxonomy( $r->taxonomy );
									$taxonomies[ absint( $r->term_id ) ] = $t->labels->singular_name . ': ' . esc_html( $r->name );
								}
							}
							?>
							<label for="edd_export_taxonomy" class="screen-reader-text"><?php esc_html_e( 'Select Taxonomy', 'easy-digital-downloads' ); ?></label>
								<?php
								echo EDD()->html->select(
									array(
										'name'             => 'taxonomy',
										'id'               => 'edd_export_taxonomy',
										'options'          => $taxonomies,
										'selected'         => false,
										'show_option_none' => false,
										'show_option_all'  => __( 'All Taxonomies', 'easy-digital-downloads' ),
									)
								);
								?>
							<label for="edd_customer_export_download" class="screen-reader-text"><?php esc_html_e( 'Select Download', 'easy-digital-downloads' ); ?></label>
								<?php
								echo EDD()->html->product_dropdown(
									array(
										'name'        => 'download',
										'id'          => 'edd_customer_export_download',
										'chosen'      => true,
										/* translators: the plural post type label */
										'placeholder' => sprintf( __( 'All %s', 'easy-digital-downloads' ), edd_get_label_plural() ),
									)
								);

								wp_nonce_field( 'edd_ajax_export', 'edd_ajax_export' );
								?>
							<input type="hidden" name="edd-export-class" value="EDD_Batch_Customers_Export"/>
							<button type="submit" class="button button-secondary"><?php esc_html_e( 'Generate CSV', 'easy-digital-downloads' ); ?></button>
						</form>
					</div>
				</div>

				<div class="postbox edd-export-taxed-customers">
					<h2 class="hndle"><span><?php esc_html_e( 'Export Taxed Customers', 'easy-digital-downloads' ); ?></span></h2>
					<div class="inside">
						<p><?php esc_html_e( 'Download a CSV of all customers that were taxed.', 'easy-digital-downloads' ); ?></p>
						<form id="edd-export-taxed-customers" class="edd-export-form edd-import-export-form" method="post">
							<fieldset class="edd-from-to-wrapper">
							<legend class="screen-reader-text">
								<?php esc_html_e( 'Export Taxed Customer Dates', 'easy-digital-downloads' ); ?></legend>
							<label for="edd-taxed-customers-export-start" class="screen-reader-text"><?php esc_html_e( 'Set start date', 'easy-digital-downloads' ); ?></label>
								<?php
									echo EDD()->html->date_field(
										array(
											'id'          => 'edd-taxed-customers-export-start',
											'class'       => 'edd-export-start',
											'name'        => 'taxed-customers-export-start',
											'placeholder' => _x( 'From', 'date filter', 'easy-digital-downloads' )
										)
									);
								?>
							<label for="edd-taxed-customers-export-end" class="screen-reader-text"><?php esc_html_e( 'Set end date', 'easy-digital-downloads' ); ?></label>
								<?php
								echo EDD()->html->date_field(
									array(
										'id'          => 'edd-taxed-customers-export-end',
										'class'       => 'edd-export-end',
										'name'        => 'taxed-customers-export-end',
										'placeholder' => _x( 'To', 'date filter', 'easy-digital-downloads' )
									)
								);
								?>
							</fieldset>
							<?php
							wp_nonce_field( 'edd_ajax_export', 'edd_ajax_export' );

							?>
							<input type="hidden" name="edd-export-class" value="EDD_Batch_Taxed_Customers_Export"/>
							<button type="submit" class="button button-secondary"><?php esc_html_e( 'Generate CSV', 'easy-digital-downloads' ); ?></button>
						</form>
					</div>
				</div>

				<div class="postbox edd-export-downloads">
					<h2 class="hndle"><span><?php esc_html_e(
						/* translators: the singular post type label */
						sprintf( __( 'Export %s Products', 'easy-digital-downloads' ), edd_get_label_singular() ) ); ?></span></h2>
					<div class="inside">
						<p><?php esc_html_e(
							/* translators: the plural post type label */
							sprintf( __( 'Download a CSV of product %1$s.', 'easy-digital-downloads' ), edd_get_label_plural( true ) ) ); ?></p>
						<form id="edd-export-downloads" class="edd-export-form edd-import-export-form" method="post">
						<label for="edd_download_export_download" class="screen-reader-text"><?php esc_html_e( 'Select Download', 'easy-digital-downloads' ); ?></label>
							<?php echo EDD()->html->product_dropdown(
								array(
									'name'        => 'download_id',
									'id'          => 'edd_download_export_download',
									'chosen'      => true,
									/* translators: the plural post type label */
									'placeholder' => sprintf( __( 'All %s', 'easy-digital-downloads' ), edd_get_label_plural() ),
								)
							);
							?>
							<?php wp_nonce_field( 'edd_ajax_export', 'edd_ajax_export' ); ?>
							<input type="hidden" name="edd-export-class" value="EDD_Batch_Downloads_Export"/>
							<button type="submit" class="button button-secondary"><?php esc_html_e( 'Generate CSV', 'easy-digital-downloads' ); ?></button>
						</form>
					</div>
				</div>

				<div class="postbox edd-export-api-requests">
					<h2 class="hndle"><span><?php esc_html_e( 'Export API Request Logs', 'easy-digital-downloads' ); ?></span></h2>
					<div class="inside">
						<p><?php esc_html_e( 'Download a CSV of API request logs.', 'easy-digital-downloads' ); ?></p>
						<form id="edd-export-api-requests" class="edd-export-form edd-import-export-form" method="post">
							<fieldset class="edd-from-to-wrapper">
							<legend class="screen-reader-text">
								<?php esc_html_e( 'Export API Request Log Dates', 'easy-digital-downloads' ); ?>
							</legend>
								<label for="edd-api-requests-export-start" class="screen-reader-text"><?php esc_html_e( 'Set start date', 'easy-digital-downloads' ); ?></label>
									<?php
									echo EDD()->html->date_field(
										array(
											'id'          => 'edd-api-requests-export-start',
											'class'       => 'edd-export-start',
											'name'        => 'api-requests-export-start',
											'placeholder' => _x( 'From', 'date filter', 'easy-digital-downloads' )
										)
									);
									?>
								<label for="edd-api-requests-export-end" class="screen-reader-text"><?php esc_html_e( 'Set end date', 'easy-digital-downloads' ); ?></label>
									<?php
									echo EDD()->html->date_field(
										array(
											'id'          => 'edd-api-requests-export-end',
											'class'       => 'edd-export-end',
											'name'        => 'api-requests-export-end',
											'placeholder' => _x( 'To', 'date filter', 'easy-digital-downloads' )
										)
									);

									?>
							</fieldset>
							<?php
							wp_nonce_field( 'edd_ajax_export', 'edd_ajax_export' );

							?>
							<input type="hidden" name="edd-export-class" value="EDD_Batch_API_Requests_Export"/>
							<button type="submit" class="button button-secondary"><?php esc_html_e( 'Generate CSV', 'easy-digital-downloads' ); ?></button>
						</form>
					</div>
				</div>

				<div class="postbox edd-export-download-history">
					<h2 class="hndle"><span><?php esc_html_e( 'Export File Download Logs', 'easy-digital-downloads' ); ?></span></h2>
					<div class="inside">
						<p><?php esc_html_e( 'Download a CSV of file download logs.', 'easy-digital-downloads' ); ?></p>
						<form id="edd-export-download-history" class="edd-export-form edd-import-export-form" method="post">
							<label for="edd_file_download_export_download" class="screen-reader-text"><?php esc_html_e( 'Select Download', 'easy-digital-downloads' ); ?></label>
								<?php echo EDD()->html->product_dropdown(
									array(
										'name'        => 'download_id',
										'id'          => 'edd_file_download_export_download',
										'chosen'      => true,
										/* translators: the plural post type label */
										'placeholder' => sprintf( __( 'All %s', 'easy-digital-downloads' ), edd_get_label_plural() ),
									)
								);
							?>
							<fieldset class="edd-from-to-wrapper">
							<legend class="screen-reader-text">
								<?php esc_html_e( 'Export File Download Log Dates', 'easy-digital-downloads' ); ?>
							</legend>
								<label for="edd-file-download-export-start" class="screen-reader-text"><?php esc_html_e( 'Set start date', 'easy-digital-downloads' ); ?></label>
								<?php
								echo EDD()->html->date_field(
									array(
										'id'          => 'edd-file-download-export-start',
										'class'       => 'edd-export-start',
										'name'        => 'file-download-export-start',
										'placeholder' => _x( 'From', 'date filter', 'easy-digital-downloads' )
									)
								);
								?>
								<label for="edd-file-download-export-end" class="screen-reader-text"><?php esc_html_e( 'Set end date', 'easy-digital-downloads' ); ?></label>
									<?php
									echo EDD()->html->date_field(
										array(
											'id'          => 'edd-file-download-export-end',
											'class'       => 'edd-export-end',
											'name'        => 'file-download-export-end',
											'placeholder' => _x( 'To', 'date filter', 'easy-digital-downloads' )
										)
									);

									?>
							</fieldset>
							<?php
							wp_nonce_field( 'edd_ajax_export', 'edd_ajax_export' );

							?>
							<input type="hidden" name="edd-export-class" value="EDD_Batch_File_Downloads_Export"/>
							<button type="submit" class="button button-secondary"><?php esc_html_e( 'Generate CSV', 'easy-digital-downloads' ); ?></button>
						</form>
					</div>
				</div>

				<?php do_action( 'edd_reports_tab_export_content_bottom' ); ?>
				</div>
			</div>
		</div>
	</div>
	<?php
}

/**
 * Retrieves estimated monthly earnings and sales
 *
 * @since 1.5
 *
 * @param bool  $include_taxes If the estimated earnings should include taxes
 * @return array
 */
function edd_estimated_monthly_stats( $include_taxes = true ) {

	$estimated = get_transient( 'edd_estimated_monthly_stats' . $include_taxes );

	if ( false === $estimated ) {

		$estimated = array(
			'earnings' => 0,
			'sales'    => 0
		);

		$stats = new EDD_Payment_Stats;

		$to_date_earnings = $stats->get_earnings( 0, 'this_month', null, $include_taxes );
		$to_date_sales    = $stats->get_sales( 0, 'this_month' );

		$current_day      = date( 'd', current_time( 'timestamp' ) );
		$current_month    = date( 'n', current_time( 'timestamp' ) );
		$current_year     = date( 'Y', current_time( 'timestamp' ) );
		$days_in_month    = cal_days_in_month( CAL_GREGORIAN, $current_month, $current_year );

		$estimated['earnings'] = ( $to_date_earnings / $current_day ) * $days_in_month;
		$estimated['sales']    = ( $to_date_sales    / $current_day ) * $days_in_month;

		// Cache for one day
		set_transient( 'edd_estimated_monthly_stats' . $include_taxes, $estimated, 86400 );
	}

	return maybe_unserialize( $estimated );
}

/**
 * Adds postbox nonces, which are used to save the position of tile endpoint meta boxes.
 *
 * @since 3.0
 */
function edd_add_screen_options_nonces() {
	wp_nonce_field( 'closedpostboxes', 'closedpostboxesnonce', false );
	wp_nonce_field( 'meta-box-order',  'meta-box-order-nonce', false );
}
add_action( 'admin_footer', 'edd_add_screen_options_nonces' );

/**
 * This function adds a notice to the bottom of the Tax reports screen if a default tax rate is detected, stating
 * that we cannot report on the default tax rate.
 *
 * @since 3.0
 * @param \EDD\Reports\Data\Report|\WP_Error $report The current report object, or WP_Error if invalid.
 */
function edd_tax_report_notice( $report ) {
	if ( 'taxes' === $report->object_id && false !== edd_get_option( 'tax_rate' ) ) {
		?>
		<p class="description">
			<strong><?php esc_html_e( 'Notice', 'easy-digital-downloads' ); ?>: </strong>
			<?php esc_html_e( 'Tax reports are only generated for taxes associated with a location. The legacy default tax rate is unable to be reported on.', 'easy-digital-downloads' ); ?>
		</p>
		<?php
	}
}
add_action( 'edd_reports_page_bottom', 'edd_tax_report_notice', 10, 1 );
