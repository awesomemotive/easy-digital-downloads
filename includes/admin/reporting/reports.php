<?php
/**
 * Admin Reports Page
 *
 * @package     EDD
 * @subpackage  Admin/Reports
 * @copyright   Copyright (c) 2018, Easy Digital Downloads, LLC
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       1.0
 */

use EDD\Reports;

// Exit if accessed directly
defined( 'ABSPATH' ) || exit;

/**
 * Contains backwards compat code to shim tabs & views to EDD_Sections()
 *
 * @since 3.0
 */
function edd_reports_sections() {

	// Instantiate the Sections class and sections array
	$sections   = new EDD_Sections();
	$c_sections = array();

	// Setup sections variables
	$sections->use_js          = false;
	$sections->current_section = Reports\get_active_tab();
	$sections->item            = null;
	$sections->base_url = add_query_arg( array(
		'post_type'        => 'download',
		'page'             => 'edd-reports',
		'settings-updated' => false
	), admin_url( 'edit.php' ) );

	// Get all registered tabs & views
	$tabs = Reports\get_tabs();

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
	$report = EDD\Reports\get_report( $report_id );
	
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

	wp_enqueue_script( 'postbox' );

	// Start the Reports API.
	new Reports\Init(); ?>

    <div class="wrap">
        <h1><?php _e( 'Easy Digital Downloads Reports', 'easy-digital-downloads' ); ?></h1>

        <div id="edd-item-wrapper" class="full-width edd-clearfix">
			<?php edd_reports_sections(); ?>
        </div>
    </div><!-- .wrap -->

	<?php
}

/**
 * Placeholder code to re-register former reports views as report tabs.
 *
 * This code will be replaced before release with individual tab registration logic.
 *
 * @since 3.0
 *
 * @param \EDD\Reports\Data\Report_Registry $reports Report registry.
 */
function edd_register_core_reports( $reports ) {
	try {

		// Endpoint whitelisted for display testing purposes pre-implementation.
		$reports->register_endpoint( 'test_tile', array(
			'label' => 'Test Tile',
			'views' => array(
				'tile' => array(
					'data_callback' => function() {
						return 'Some Tile Data';
					}
				)
			)
		) );

		$reports->register_endpoint( 'another_test_tile', array(
			'label'   => 'Another Test Tile',
			'views'   => array(
				'tile' => array(
					'data_callback' => function() {
						return 'Some Tile Data';
					},
					'display_args' => array(
						'context' => 'tertiary',
					),
				),
			),
		) );

		$reports->add_report( 'downloads', array(
			'label'     => edd_get_label_plural(),
			'priority'  => 10,
			'icon'      => 'download',
			'endpoints' => array(
				'tiles' => array( 'test_tile', 'another_test_tile' )
			),
		) );

		$reports->add_report( 'refunds', array(
			'label'     => __( 'Refunds', 'easy-digital-downloads' ),
			'icon'      => 'image-rotate',
			'priority'  => 15,
			'endpoints' => array(
				'tiles' => array( 'test_tile', 'another_test_tile' )
			),
		) );

		$reports->add_report( 'taxes', array(
			'label'     => __( 'Taxes', 'easy-digital-downloads' ),
			'priority'  => 25,
			'icon'      => 'editor-paste-text',
			'endpoints' => array(
				'tiles' => array( 'test_tile' )
			),
		) );

		$reports->add_report( 'file_downloads', array(
			'label'     => __( 'File Downloads', 'easy-digital-downloads' ),
			'icon'      => 'download',
			'priority'  => 30,
			'endpoints' => array(
				'tiles' => array( 'test_tile' )
			),
		) );

		$reports->add_report( 'categories', array(
			'label'     => __( 'Earnings by Category', 'easy-digital-downloads' ),
			'icon'      => 'category',
			'priority'  => 45,
			'endpoints' => array(
				'tiles' => array( 'test_tile' )
			),
		) );
	} catch ( \EDD_Exception $exception ) {
		edd_debug_log_exception( $exception );
	}
}
add_action( 'edd_reports_init', 'edd_register_core_reports' );

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
		$options = Reports\get_dates_filter_options();
		$filter  = Reports\get_filter_value( 'dates' );
		$label   = $options[ $filter['range'] ];

		$reports->add_report( 'overview', array(
			'label'     => __( 'Overview', 'easy-digital-downloads' ),
			'icon'      => 'dashboard',
			'priority'  => 5,
			'endpoints' => array(
				'tiles'  => array(
					'overview_time_period_data',
					'overview_all_time_data',
					'overview_sales',
					'overview_earnings',
					'overview_refunds',
					'overview_average_customer_revenue',
					'overview_average_order_value',
					'overview_new_customers',
					'overview_file_downloads',
					'overview_taxes',
				),
				'charts' => array(
					'overview_sales_chart',
					'overview_earnings_chart',
				),
			),
		) );

		$reports->register_endpoint( 'overview_time_period_data', array(
			'label' => __( 'Sales / Earnings', 'easy-digital-downloads' ),
			'views' => array(
				'tile' => array(
					'data_callback' => function () use ( $filter ) {
						$stats = new EDD\Orders\Stats( array(
							'range'  => $filter['range'],
							'output' => 'formatted',
						) );

						return $stats->get_order_count() . ' / ' . $stats->get_order_earnings();
					},
					'display_args'  => array(
						'context'          => 'primary',
						'comparison_label' => $label,
					),
				),
			),
		) );

		$reports->register_endpoint( 'overview_all_time_data', array(
			'label' => __( 'Sales / Earnings', 'easy-digital-downloads' ),
			'views' => array(
				'tile' => array(
					'data_callback' => function () {
						$stats = new EDD\Orders\Stats( array(
							'output' => 'formatted',
						) );

						return $stats->get_order_count() . ' / ' . $stats->get_order_earnings();
					},
					'display_args'  => array(
						'context'          => 'secondary',
						'comparison_label' => __( 'All Time', 'easy-digital-downloads' ),
					),
				),
			),
		) );

		$reports->register_endpoint( 'overview_earnings', array(
			'label' => __( 'Earnings', 'easy-digital-downloads' ),
			'views' => array(
				'tile' => array(
					'data_callback' => function () use ( $filter ) {
						$stats = new EDD\Orders\Stats();
						return apply_filters( 'edd_reports_overview_earnings', $stats->get_order_earnings( array(
							'range'    => $filter['range'],
							'relative' => true,
						) ) );
					},
					'display_args'  => array(
						'context'          => 'tertiary',
						'comparison_label' => $label,
					),
				),
			),
		) );

		$reports->register_endpoint( 'overview_sales', array(
			'label' => __( 'Sales', 'easy-digital-downloads' ),
			'views' => array(
				'tile' => array(
					'data_callback' => function () use ( $filter ) {
						$stats = new EDD\Orders\Stats();
						return apply_filters( 'edd_reports_overview_sales', $stats->get_order_count( array(
							'range'    => $filter['range'],
							'relative' => true,
						) ) );
					},
					'display_args'  => array(
						'context'          => 'primary',
						'comparison_label' => $label,
					),
				),
			),
		) );

		$reports->register_endpoint( 'overview_refunds', array(
			'label' => __( 'Refunds', 'easy-digital-downloads' ),
			'views' => array(
				'tile' => array(
					'data_callback' => function () {

					},
					'display_args'  => array(
						'context'          => 'secondary',
						'comparison_label' => $label,
					),
				),
			),
		) );

		$reports->register_endpoint( 'overview_average_customer_revenue', array(
			'label' => __( 'Average Revenue per Customer', 'easy-digital-downloads' ),
			'views' => array(
				'tile' => array(
					'data_callback' => function () use ( $filter ) {
						$stats = new EDD\Orders\Stats();
						return apply_filters( 'edd_reports_overview_average_customer_revenue', $stats->get_customer_lifetime_value( array(
							'function' => 'AVG',
							'range'    => $filter['range'],
							'output'   => 'formatted',
							'relative' => true,
						) ) );
					},
					'display_args'  => array(
						'context'          => 'tertiary',
						'comparison_label' => $label,
					),
				),
			),
		) );

		$reports->register_endpoint( 'overview_average_order_value', array(
			'label' => __( 'Average Order Value', 'easy-digital-downloads' ),
			'views' => array(
				'tile' => array(
					'data_callback' => function () use ( $filter ) {
						$stats = new EDD\Orders\Stats();
						return apply_filters( 'edd_reports_overview_average_order_value', $stats->get_order_earnings( array(
							'function' => 'AVG',
							'output'   => 'formatted',
							'relative' => true,
							'range'    => $filter['range'],
						) ) );
					},
					'display_args'  => array(
						'context'          => 'primary',
						'comparison_label' => $label,
					),
				),
			),
		) );

		$reports->register_endpoint( 'overview_new_customers', array(
			'label' => __( 'New Customers', 'easy-digital-downloads' ),
			'views' => array(
				'tile' => array(
					'data_callback' => function () use ( $filter ) {
						$stats = new EDD\Orders\Stats();
						return apply_filters( 'edd_reports_overview_new_customers', $stats->get_customer_count( array(
							'range'    => $filter['range'],
							'relative' => true,
						) ) );
					},
					'display_args'  => array(
						'context'          => 'secondary',
						'comparison_label' => $label,
					),
				),
			),
		) );

		$reports->register_endpoint( 'overview_file_downloads', array(
			'label' => __( 'File Downloads', 'easy-digital-downloads' ),
			'views' => array(
				'tile' => array(
					'data_callback' => function () use ( $filter ) {
						$stats = new EDD\Orders\Stats();
						return apply_filters( 'edd_reports_overview_new_customers', $stats->get_file_download_count( array(
							'range'    => $filter['range'],
							'relative' => true,
						) ) );
					},
					'display_args'  => array(
						'context'          => 'tertiary',
						'comparison_label' => $label,
					),
				),
			),
		) );

		$reports->register_endpoint( 'overview_taxes', array(
			'label' => __( 'Taxes', 'easy-digital-downloads' ),
			'views' => array(
				'tile' => array(
					'data_callback' => function () use ( $filter ) {
						$stats = new EDD\Orders\Stats();
						return apply_filters( 'edd_reports_overview_taxes', $stats->get_tax( array(
							'range'    => $filter['range'],
							'relative' => true,
						) ) );
					},
					'display_args'  => array(
						'context'          => 'primary',
						'comparison_label' => $label,
					),
				),
			),
		) );

		$reports->register_endpoint( 'overview_sales_chart', array(
			'label' => __( 'Sales', 'easy-digital-downloads' ),
			'views' => array(
				'chart' => array(
					'data_callback' => function () use ( $filter ) {
						global $wpdb;

						$dates      = Reports\get_dates_filter( 'objects' );
						$day_by_day = Reports\get_dates_filter_day_by_day();

						$sql_clauses = array(
							'select'  => 'YEAR(date_created) AS year, MONTH(date_created) AS month, DAY(date_created) AS day',
							'groupby' => 'YEAR(date_created), MONTH(date_created), DAY(date_created)',
							'orderby' => 'YEAR(date_created), MONTH(date_created), DAY(date_created)',
						);

						if ( ! $day_by_day ) {
							$sql_clauses = array(
								'select'  => 'YEAR(date_created) AS year, MONTH(date_created) AS month',
								'groupby' => 'YEAR(date_created), MONTH(date_created)',
								'orderby' => 'YEAR(date_created), MONTH(date_created)',
							);
						}

						$start = $dates['start']->format( 'Y-m-d' );
						$end   = $dates['end']->format( 'Y-m-d' );

						$results = $wpdb->get_results( $wpdb->prepare(
							"SELECT COUNT(id) AS total, {$sql_clauses['select']}
					         FROM {$wpdb->edd_orders} edd_o
					         WHERE date_created >= %s AND date_created <= %s 
                             GROUP BY {$sql_clauses['groupby']}
                             ORDER BY {$sql_clauses['orderby']} ASC",
							$start, $end ) );

						$sales = array();

						while ( strtotime( $start ) <= strtotime( $end ) ) {
							$day = ( true === $day_by_day )
								? $dates['start']->day
								: 1;

							$timestamp = \Carbon\Carbon::create( $dates['start']->year, $dates['start']->month, $day, 0, 0, 0 )->timestamp;

							$sales[ $timestamp ][] = $timestamp;
							$sales[ $timestamp ][] = 0;

							$start = ( true === $day_by_day )
								? $dates['start']->addDays( 1 )->format( 'Y-m-d' )
								: $dates['start']->addMonth( 1 )->format( 'Y-m' );
						}

						foreach ( $results as $result ) {
							$day = ( true === $day_by_day )
								? $result->day
								: 1;

							$timestamp = \Carbon\Carbon::create( $result->year, $result->month, $day, 0, 0, 0 )->timestamp;

							$sales[ $timestamp ][1] = $result->total;
						}

						$sales = array_values( $sales );

						return array( 'sales' => $sales );
					},
					'type'          => 'line',
					'options'       => array(
						'datasets' => array(
							'sales' => array(
								'label'           => __( 'Sales', 'easy-digital-downloads' ),
								'borderColor'     => 'rgb(39,148,218)',
								'backgroundColor' => 'rgb(39,148,218)',
								'fill'            => false,
							),
						),
					),
				),
			),
		) );

		$reports->register_endpoint( 'overview_earnings_chart', array(
			'label' => __( 'Earnings', 'easy-digital-downloads' ),
			'views' => array(
				'chart' => array(
					'data_callback' => function () use ( $filter ) {
						global $wpdb;

						$dates      = Reports\get_dates_filter( 'objects' );
						$day_by_day = Reports\get_dates_filter_day_by_day();

						$sql_clauses = array(
							'select'  => 'YEAR(date_created) AS year, MONTH(date_created) AS month, DAY(date_created) AS day',
							'groupby' => 'YEAR(date_created), MONTH(date_created), DAY(date_created)',
							'orderby' => 'YEAR(date_created), MONTH(date_created), DAY(date_created)',
						);

						if ( ! $day_by_day ) {
							$sql_clauses = array(
								'select'  => 'YEAR(date_created) AS year, MONTH(date_created) AS month',
								'groupby' => 'YEAR(date_created), MONTH(date_created)',
								'orderby' => 'YEAR(date_created), MONTH(date_created)',
							);
						}

						$start = $dates['start']->format( 'Y-m-d' );
						$end   = $dates['end']->format( 'Y-m-d' );

						$results = $wpdb->get_results( $wpdb->prepare(
							"SELECT SUM(total) AS total, {$sql_clauses['select']}
					         FROM {$wpdb->edd_orders} edd_o
					         WHERE date_created >= %s AND date_created <= %s 
                             GROUP BY {$sql_clauses['groupby']}
                             ORDER BY {$sql_clauses['orderby']} ASC",
							$start, $end ) );

						$earnings = array();

						while ( strtotime( $start ) <= strtotime( $end ) ) {
							$day = ( true === $day_by_day )
								? $dates['start']->day
								: 1;

							$timestamp = \Carbon\Carbon::create( $dates['start']->year, $dates['start']->month, $day, 0, 0, 0 )->timestamp;

							$earnings[ $timestamp ][] = $timestamp;
							$earnings[ $timestamp ][] = 0;

							$start = ( true === $day_by_day )
								? $dates['start']->addDays( 1 )->format( 'Y-m-d' )
								: $dates['start']->addMonth( 1 )->format( 'Y-m' );
						}

						foreach ( $results as $result ) {
							$day = ( true === $day_by_day )
								? $result->day
								: 1;

							$timestamp = \Carbon\Carbon::create( $result->year, $result->month, $day, 0, 0, 0 )->timestamp;

							$earnings[ $timestamp ][1] = $result->total;
						}

						$earnings = array_values( $earnings );

						return array( 'earnings' => $earnings );
					},
					'type'          => 'line',
					'options'       => array(
						'datasets' => array(
							'earnings' => array(
								'label'           => __( 'Earnings', 'easy-digital-downloads' ),
								'borderColor'     => 'rgb(39,148,218)',
								'backgroundColor' => 'rgb(39,148,218)',
								'fill'            => false,
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
 * Register payment gateways report and endpoints.
 *
 * @since 3.0
 *
 * @param \EDD\Reports\Data\Report_Registry $reports Report registry.
 */
function edd_register_payment_gateways_report( $reports ) {
	try {

		// Variables to hold date filter values.
		$options = Reports\get_dates_filter_options();
		$filter  = Reports\get_filter_value( 'dates' );
		$gateway = Reports\get_filter_value( 'gateways' );

		$gateway = ! empty( $gateway ) && 'all' !== $gateway
			? ' (' . esc_html( edd_get_gateway_admin_label( $gateway ) ) . ')'
			: '';

		$label = $options[ $filter['range'] ] . $gateway;

		$reports->add_report( 'gateways', array(
			'label'     => __( 'Payment Gateways', 'easy-digital-downloads' ),
			'icon'      => 'image-filter',
			'priority'  => 20,
			'endpoints' => array(
				'tiles'  => array(
					'sales_per_gateway',
					'earnings_per_gateway',
					'refunds_per_gateway',
					'average_value_per_gateway',
				),
				'tables' => array(
					'gateway_stats',
				),
			),
			'filters'   => array( 'gateways' ),
		) );

		$reports->register_endpoint( 'sales_per_gateway', array(
			'label' => __( 'Sales', 'easy-digital-downloads' ),
			'views' => array(
				'tile' => array(
					'data_callback' => function () use ( $filter ) {
						$gateway = 'all' !== Reports\get_filter_value( 'gateways' )
							? Reports\get_filter_value( 'gateways' )
							: '';

						$stats = new EDD\Orders\Stats();

						return apply_filters( 'edd_reports_gateways_sales', $stats->get_gateway_sales( array(
							'range'   => $filter['range'],
							'gateway' => $gateway,
						) ) );
					},
					'display_args'  => array(
						'context'          => 'primary',
						'comparison_label' => $label,
					),
				),
			),
		) );

		$reports->register_endpoint( 'earnings_per_gateway', array(
			'label' => __( 'Earnings', 'easy-digital-downloads' ),
			'views' => array(
				'tile' => array(
					'data_callback' => function () use ( $filter ) {
						$gateway = 'all' !== Reports\get_filter_value( 'gateways' )
							? Reports\get_filter_value( 'gateways' )
							: '';

						$stats = new EDD\Orders\Stats();

						return apply_filters( 'edd_reports_gateways_earnings', $stats->get_gateway_earnings( array(
							'range'   => $filter['range'],
							'gateway' => $gateway,
							'output'  => 'formatted',
						) ) );
					},
					'display_args'  => array(
						'context'          => 'secondary',
						'comparison_label' => $label,
					),
				),
			),
		) );

		$reports->register_endpoint( 'refunds_per_gateway', array(
			'label' => __( 'Refunds', 'easy-digital-downloads' ),
			'views' => array(
				'tile' => array(
					'data_callback' => function () use ( $filter ) {
						$gateway = 'all' !== Reports\get_filter_value( 'gateways' )
							? Reports\get_filter_value( 'gateways' )
							: '';

						$stats = new EDD\Orders\Stats();

						return apply_filters( 'edd_reports_gateways_refunds', $stats->get_gateway_earnings( array(
							'range'   => $filter['range'],
							'gateway' => $gateway,
							'output'  => 'formatted',
							'status'  => array( 'refunded' ),
						) ) );
					},
					'display_args'  => array(
						'context'          => 'tertiary',
						'comparison_label' => $label,
					),
				),
			),
		) );

		$reports->register_endpoint( 'average_value_per_gateway', array(
			'label' => __( 'Average Order Value', 'easy-digital-downloads' ),
			'views' => array(
				'tile' => array(
					'data_callback' => function () use ( $filter ) {
						$gateway = 'all' !== Reports\get_filter_value( 'gateways' )
							? Reports\get_filter_value( 'gateways' )
							: '';

						$stats = new EDD\Orders\Stats();

						if ( empty( $gateway ) ) {
							return apply_filters( 'edd_reports_gateways_average_order_value', $stats->get_order_earnings( array(
								'range'    => $filter['range'],
								'function' => 'AVG',
								'output'   => 'formatted',
							) ) );
						} else {
							return apply_filters( 'edd_reports_gateways_average_order_value', $stats->get_gateway_earnings( array(
								'range'    => $filter['range'],
								'gateway'  => $gateway,
								'function' => 'AVG',
								'output'   => 'formatted',
							) ) );
						}
					},
					'display_args'  => array(
						'context'          => 'primary',
						'comparison_label' => $label,
					),
				),
			),
		) );

		$reports->register_endpoint( 'gateway_stats', array(
			'label' => __( 'Gateway Stats', 'easy-digital-downloads' ),
			'views' => array(
				'table' => array(
					'display_args' => array(
						'class_name' => '\\EDD\\Reports\\Data\\Payment_Gateways\\Gateway_Stats',
						'class_file' => EDD_PLUGIN_DIR . 'includes/reports/data/payment-gateways/class-gateway-stats-list-table.php',
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
 * Register discounts report and endpoints.
 *
 * @since 3.0
 *
 * @param \EDD\Reports\Data\Report_Registry $reports Report registry.
 */
function edd_register_discounts_report( $reports ) {
	try {

		// Variables to hold date filter values.
		$options = Reports\get_dates_filter_options();
		$filter  = Reports\get_filter_value( 'dates' );
		$label   = $options[ $filter['range'] ];

		$discount = Reports\get_filter_value( 'discounts' );
		$discount = ! empty( $discount ) && 'all' !== $discount
			? $discount
			: 0;

		$d = edd_get_discount( $discount );

		$discount_label = false !== $d
			? esc_html( ' (' . $d->name . ')' )
			: '';

		$reports->add_report( 'discounts', array(
			'label'     => __( 'Discounts', 'easy-digital-downloads' ),
			'icon'      => 'tickets-alt',
			'priority'  => 35,
			'endpoints' => array(
				'tiles'  => array(
					'number_of_discounts_used',
					'ratio_of_discounted_orders',
					'customer_savings',
					'average_discount_amount',
					'most_popular_discount',
					'discount_usage_count',
				),
				'tables' => array(
					'top_five_discounts',
				),
			),
			'filters'   => array( 'discounts' ),
		) );

		$reports->register_endpoint( 'number_of_discounts_used', array(
			'label' => __( 'Number of Discounts Used', 'easy-digital-downloads' ),
			'views' => array(
				'tile' => array(
					'data_callback' => function () use ( $filter ) {
						$stats = new EDD\Orders\Stats();
						return apply_filters( 'edd_reports_discounts_number_of_discounts_used', $stats->get_discount_usage_count( array(
							'range' => $filter['range'],
						) ) );
					},
					'display_args'  => array(
						'context'          => 'primary',
						'comparison_label' => $label,
					),
				),
			),
		) );

		$reports->register_endpoint( 'ratio_of_discounted_orders', array(
			'label' => __( 'Ratio of Discounted/Non-Discounted Orders', 'easy-digital-downloads' ),
			'views' => array(
				'tile' => array(
					'data_callback' => function () use ( $filter ) {
						$stats = new EDD\Orders\Stats();
						return apply_filters( 'edd_reports_discounts_ratio_of_discounted_orders', $stats->get_ratio_of_discounted_orders( array(
							'range' => $filter['range'],
						) ) );
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
						$stats = new EDD\Orders\Stats();
						return apply_filters( 'edd_reports_discounts_customer_savings', $stats->get_discount_savings( array(
							'range'         => $filter['range'],
							'output'        => 'formatted',
							'discount_code' => isset( $d->code )
								? $d->code
								: '',
						) ) );
					},
					'display_args'  => array(
						'context'          => 'tertiary',
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
						$stats = new EDD\Orders\Stats();
						return apply_filters( 'edd_reports_discounts_average_discount_amount', $stats->get_average_discount_amount( array(
							'range'  => $filter['range'],
							'output' => 'formatted',
						) ) );
					},
					'display_args'  => array(
						'context'          => 'primary',
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
						$stats = new EDD\Orders\Stats();

						$r = apply_filters( 'edd_reports_discounts_most_popular_discount', $stats->get_most_popular_discounts( array(
							'range' => $filter['range'],
						) ) );

						if ( ! empty( $r ) ) {
							$r = $r[0];
							return apply_filters( 'edd_reports_discounts_most_popular_discount', esc_html( $r->code . ' (' . $r->count . ')' ) );
						}
					},
					'display_args'  => array(
						'context'          => 'secondary',
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
							$stats = new EDD\Orders\Stats();
							return apply_filters( 'edd_reports_discounts_most_popular_discount', $stats->get_discount_usage_count( array(
								'range'         => $filter['range'],
								'discount_code' => $d->code,
							) ) );
						},
						'display_args'  => array(
							'context'          => 'tertiary',
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
		$options = Reports\get_dates_filter_options();
		$filter  = Reports\get_filter_value( 'dates' );
		$label   = $options[ $filter['range'] ];

		$reports->add_report( 'customers', array(
			'label'     => __( 'Customers', 'easy-digital-downloads' ),
			'icon'      => 'groups',
			'priority'  => 40,
			'endpoints' => array(
				'tiles'  => array(
					'lifetime_value_of_customer',
					'average_customer_value',
					'average_number_of_orders_per_customer',
					'customer_average_age',
					'most_valuable_customer',
				),
				'tables' => array(
					'top_five_customers',
					'most_valuable_customers',
				),
				'charts' => array(
					'new_customers',
				),
			),
			'filters'   => array( 'dates' ),
		) );

		$reports->register_endpoint( 'lifetime_value_of_customer', array(
			'label' => __( 'Average Lifetime Value', 'easy-digital-downloads' ),
			'views' => array(
				'tile' => array(
					'data_callback' => function () {
						$stats = new EDD\Orders\Stats();
						return $stats->get_customer_lifetime_value( array(
							'function' => 'AVG',
							'output'   => 'formatted',
						) );
					},
				),
			),
		) );

		$reports->register_endpoint( 'average_customer_value', array(
			'label' => __( 'Average Value', 'easy-digital-downloads' ),
			'views' => array(
				'tile' => array(
					'data_callback' => function () use ( $filter ) {
						$stats = new EDD\Orders\Stats();
						return apply_filters( 'edd_reports_customers_average_customer_value', $stats->get_customer_lifetime_value( array(
							'function' => 'AVG',
							'range'    => $filter['range'],
							'output'   => 'formatted',
						) ) );
					},
					'display_args'  => array(
						'context'          => 'secondary',
						'comparison_label' => $label,
					),
				),
			),
		) );

		$reports->register_endpoint( 'average_number_of_orders_per_customer', array(
			'label' => __( 'Average Number of Orders', 'easy-digital-downloads' ),
			'views' => array(
				'tile' => array(
					'data_callback' => function () {
						$stats = new EDD\Orders\Stats();
						return apply_filters( 'edd_reports_customers_average_order_count', $stats->get_customer_order_count( array(
							'function' => 'AVG',
						) ) );
					},
					'display_args'  => array(
						'context' => 'tertiary',
					),
				),
			),
		) );

		$reports->register_endpoint( 'customer_average_age', array(
			'label' => __( 'Average Age', 'easy-digital-downloads' ),
			'views' => array(
				'tile' => array(
					'data_callback' => function () {
						global $wpdb;
						$average_value = (int) $wpdb->get_var( "SELECT AVG(DATEDIFF(NOW(), date_created)) AS average FROM {$wpdb->edd_customers}" );

						return apply_filters( 'edd_reports_customers_average_age', $average_value . ' ' . __( 'days', 'easy-digital-downloads' ) );
					},
					'display_args'  => array(
						'context' => 'primary',
					),
				),
			),
		) );

		$reports->register_endpoint( 'top_five_customers', array(
			'label' => __( 'Top Five Customers of All Time', 'easy-digital-downloads' ),
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
			'label' => __( 'Most Valuable Customers of ', 'easy-digital-downloads' ) . $label,
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
			'label' => __( 'New Customers', 'easy-digital-downloads' ),
			'views' => array(
				'chart' => array(
					'data_callback' => function () use ( $filter ) {
						global $wpdb;

						$start_date = date( 'Y-m-d 00:00:00', strtotime( $filter['from'] ) );
						$end_date   = date( 'Y-m-d 23:59:59', strtotime( $filter['to'] ) );

						$results = $wpdb->get_results( $wpdb->prepare(
							"SELECT YEAR(date_created) AS year, MONTH(date_created) AS month, DAY(date_created) AS day, COUNT(edd_c.id) AS new_customers
					         FROM {$wpdb->edd_customers} edd_c
					         WHERE ( ( date_created >= %s
                             AND date_created <= %s ) ) 
                             GROUP BY YEAR(date_created), MONTH(date_created), DAY(date_created)
                             ORDER BY YEAR(date_created), MONTH(date_created), DAY(date_created) ASC",
							$start_date, $end_date ) );

						$new_customers = array();

						$i = 0;
						foreach ( $results as $result ) {
							$new_customers[ $i ][] = \Carbon\Carbon::create( $result->year, $result->month, $result->day, 0, 0, 0 )->timestamp;
							$new_customers[ $i ][] = $result->new_customers;

							$i++;
						}

						return array( 'customers' => $new_customers );
					},
					'type'          => 'line',
					'options'       => array(
						'datasets' => array(
							'customers' => array(
								'label'           => __( 'New Customers', 'easy-digital-downloads' ),
								'borderColor'     => 'rgb(39,148,218)',
								'backgroundColor' => 'rgb(39,148,218)',
								'fill'            => false,
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
 * Renders the Reports Downloads Table
 *
 * @since 1.3
 * @uses EDD_Download_Reports_Table::prepare_items()
 * @uses EDD_Download_Reports_Table::display()
 * @return void
 */
function edd_reports_downloads_table() {

	if ( ! current_user_can( 'view_shop_reports' ) ) {
		return;
	}

	if ( isset( $_GET['download-id'] ) ) {
		return;
	}

	include dirname( __FILE__ ) . '/class-download-reports-table.php'; ?>

	<div class="inside">
		<?php
		$downloads_table = new EDD_Download_Reports_Table();
		$downloads_table->prepare_items();
		$downloads_table->display();
		?>
	</div>

	<?php
}
add_action( 'edd_reports_view_downloads', 'edd_reports_downloads_table' );

/**
 * Renders the detailed report for a specific product
 *
 * @since 1.9
 * @return void
 */
function edd_reports_download_details() {

	if ( ! current_user_can( 'view_shop_reports' ) ) {
		return;
	}

	if ( ! isset( $_GET['download-id'] ) ) {
		return;
	}

	edd_reports_graph_of_download( absint( $_GET['download-id'] ) );
}
add_action( 'edd_reports_view_downloads', 'edd_reports_download_details' );


/**
 * Renders the Gateways Table
 *
 * @since 1.3
 * @deprecated
 * @uses EDD_Gateway_Reports_Table::prepare_items()
 * @uses EDD_Gateway_Reports_Table::display()
 * @return void
 */
function edd_reports_gateways_table() {

	if ( ! current_user_can( 'view_shop_reports' ) ) {
		return;
	}

	include dirname( __FILE__ ) . '/class-gateways-reports-table.php'; ?>

	<div class="inside">
		<?php
		$downloads_table = new EDD_Gateway_Reports_Table();
		$downloads_table->prepare_items();
		$downloads_table->display();
		?>
	</div>

	<?php
}

/**
 * Renders the Reports Earnings Graphs
 *
 * @since 1.3
 * @return void
 */
function edd_reports_earnings() {

	if ( ! current_user_can( 'view_shop_reports' ) ) {
		return;
	} ?>

    <div class="tablenav top">
        <div class="alignleft actions"><?php edd_report_views(); ?></div>
    </div>

	<?php

	edd_reports_graph();
}
add_action( 'edd_reports_view_earnings', 'edd_reports_earnings' );

/**
 * Renders the Reports Earnings By Category Table & Graphs
 *
 * @since  2.4
 */
function edd_reports_categories() {

	if ( ! current_user_can( 'view_shop_reports' ) ) {
		return;
	} ?>

    <div class="inside">
		<?php

		include dirname( __FILE__ ) . '/class-categories-reports-table.php';
		$categories_table = new EDD_Categories_Reports_Table();
		$categories_table->prepare_items();
		$categories_table->display();

		echo $categories_table->load_scripts(); ?>

        <div class="edd-mix-totals">
            <div class="edd-mix-chart">
                <strong><?php _e( 'Category Sales Mix: ', 'easy-digital-downloads' ); ?></strong>
				<?php $categories_table->output_sales_graph(); ?>
            </div>
            <div class="edd-mix-chart">
                <strong><?php _e( 'Category Earnings Mix: ', 'easy-digital-downloads' ); ?></strong>
				<?php $categories_table->output_earnings_graph(); ?>
            </div>
        </div>

		<?php do_action( 'edd_reports_graph_additional_stats' ); ?>

        <p class="edd-graph-notes">
			<span>
				<em><sup>&dagger;</sup> <?php _e( 'All Parent categories include sales and earnings stats from child categories.', 'easy-digital-downloads' ); ?></em>
			</span>
            <span>
				<em><?php _e( 'Stats include all sales and earnings for the lifetime of the store.', 'easy-digital-downloads' ); ?></em>
			</span>
        </p>
    </div>

	<?php
}
add_action( 'edd_reports_view_categories', 'edd_reports_categories' );

/**
 * Renders the Tax Reports
 *
 * @since 1.3.3
 * @return void
 */
function edd_reports_taxes() {

	if ( ! current_user_can( 'view_shop_reports' ) ) {
		return;
	}

	$year = isset( $_GET['year'] )
		? absint( $_GET['year'] )
		: date( 'Y' ); ?>

    <div class="metabox-holder" style="padding-top: 0;">
        <div class="postbox">
            <h3><span><?php _e('Tax Report','easy-digital-downloads' ); ?></span></h3>
            <div class="inside">
                <p><?php _e( 'This report shows the total amount collected in sales tax for the given year.', 'easy-digital-downloads' ); ?></p>
                <form method="get" action="<?php echo admin_url( 'edit.php' ); ?>">
                    <span><?php echo $year; ?></span>: <strong><?php edd_sales_tax_for_year( $year ); ?></strong>&nbsp;&mdash;&nbsp;
                    <select name="year">
						<?php for ( $i = 2009; $i <= date( 'Y' ); $i++ ) : ?>
                            <option value="<?php echo $i; ?>"<?php selected( $year, $i ); ?>><?php echo $i; ?></option>
						<?php endfor; ?>
                    </select>
                    <input type="hidden" name="view" value="taxes" />
                    <input type="hidden" name="post_type" value="download" />
                    <input type="hidden" name="page" value="edd-reports" />
					<?php submit_button( __( 'Submit', 'easy-digital-downloads' ), 'secondary', 'submit', false ); ?>
                </form>
            </div><!-- .inside -->
        </div><!-- .postbox -->
    </div><!-- .metabox-holder -->

	<?php
}
add_action( 'edd_reports_view_taxes', 'edd_reports_taxes' );

/**
 * Renders the 'Export' tab on the Reports Page
 *
 * @since 1.3
 * @return void
 */
function edd_reports_tab_export() {

	if ( ! current_user_can( 'view_shop_reports' ) ) {
		return;
	} ?>

    <div id="edd-dashboard-widgets-wrap">
        <div class="metabox-holder">
            <div id="post-body">
                <div id="post-body-content">

					<?php do_action( 'edd_reports_tab_export_content_top' ); ?>

                    <div class="postbox edd-export-earnings-report">
                        <h3><span><?php _e( 'Export Earnings Report', 'easy-digital-downloads' ); ?></span></h3>
                        <div class="inside">
                            <p><?php _e( 'Download a CSV giving a detailed look into earnings over time.', 'easy-digital-downloads' ); ?></p>
                            <form id="edd-export-earnings" class="edd-export-form edd-import-export-form" method="post">
								<?php echo EDD()->html->month_dropdown( 'start_month' ); ?>
								<?php echo EDD()->html->year_dropdown( 'start_year' ); ?>
								<?php echo _x( 'to', 'Date one to date two', 'easy-digital-downloads' ); ?>
								<?php echo EDD()->html->month_dropdown( 'end_month' ); ?>
								<?php echo EDD()->html->year_dropdown( 'end_year' ); ?>
								<?php wp_nonce_field( 'edd_ajax_export', 'edd_ajax_export' ); ?>
                                <input type="hidden" name="edd-export-class" value="EDD_Batch_Earnings_Report_Export"/>
                                <span>
									<input type="submit" value="<?php _e( 'Generate CSV', 'easy-digital-downloads' ); ?>" class="button-secondary"/>
									<span class="spinner"></span>
								</span>
                            </form>
                        </div><!-- .inside -->
                    </div><!-- .postbox -->

                    <div class="postbox edd-export-payment-history">
                        <h3><span><?php _e('Export Payment History','easy-digital-downloads' ); ?></span></h3>
                        <div class="inside">
                            <p><?php _e( 'Download a CSV of all payments recorded.', 'easy-digital-downloads' ); ?></p>

                            <form id="edd-export-payments" class="edd-export-form edd-import-export-form" method="post">
								<?php echo EDD()->html->date_field( array( 'id' => 'edd-payment-export-start', 'name' => 'start', 'placeholder' => __( 'Choose start date', 'easy-digital-downloads' ) )); ?>
								<?php echo EDD()->html->date_field( array( 'id' => 'edd-payment-export-end','name' => 'end', 'placeholder' => __( 'Choose end date', 'easy-digital-downloads' ) )); ?>
                                <select name="status">
                                    <option value="any"><?php _e( 'All Statuses', 'easy-digital-downloads' ); ?></option>
									<?php
									$statuses = edd_get_payment_statuses();
									foreach( $statuses as $status => $label ) {
										echo '<option value="' . $status . '">' . $label . '</option>';
									}
									?>
                                </select>
								<?php wp_nonce_field( 'edd_ajax_export', 'edd_ajax_export' ); ?>
                                <input type="hidden" name="edd-export-class" value="EDD_Batch_Payments_Export"/>
                                <span>
									<input type="submit" value="<?php _e( 'Generate CSV', 'easy-digital-downloads' ); ?>" class="button-secondary"/>
									<span class="spinner"></span>
								</span>
                            </form>

                        </div><!-- .inside -->
                    </div><!-- .postbox -->

                    <div class="postbox edd-export-customers">
                        <h3><span><?php _e('Export Customers in CSV','easy-digital-downloads' ); ?></span></h3>
                        <div class="inside">
                            <p><?php _e( 'Download a CSV of customers.', 'easy-digital-downloads' ); ?></p>
                            <form id="edd-export-customers" class="edd-export-form edd-import-export-form" method="post">
								<?php echo EDD()->html->product_dropdown( array( 'name' => 'download', 'id' => 'edd_customer_export_download', 'chosen' => true ) ); ?>
								<?php wp_nonce_field( 'edd_ajax_export', 'edd_ajax_export' ); ?>
                                <input type="hidden" name="edd-export-class" value="EDD_Batch_Customers_Export"/>
                                <input type="submit" value="<?php _e( 'Generate CSV', 'easy-digital-downloads' ); ?>" class="button-secondary"/>
                            </form>
                        </div><!-- .inside -->
                    </div><!-- .postbox -->

                    <div class="postbox edd-export-downloads">
                        <h3><span><?php _e('Export Download Products in CSV','easy-digital-downloads' ); ?></span></h3>
                        <div class="inside">
                            <p><?php _e( 'Download a CSV of download products. To download a CSV for all download products, leave "Choose a Download" as it is.', 'easy-digital-downloads' ); ?></p>
                            <form id="edd-export-file-downloads" class="edd-export-form edd-import-export-form" method="post">
								<?php echo EDD()->html->product_dropdown( array( 'name' => 'download_id', 'id' => 'edd_download_export_download', 'chosen' => true ) ); ?>
								<?php wp_nonce_field( 'edd_ajax_export', 'edd_ajax_export' ); ?>
                                <input type="hidden" name="edd-export-class" value="EDD_Batch_Downloads_Export"/>
                                <input type="submit" value="<?php _e( 'Generate CSV', 'easy-digital-downloads' ); ?>" class="button-secondary"/>
                            </form>
                        </div><!-- .inside -->
                    </div><!-- .postbox -->

                    <div class="postbox edd-export-download-history">
                        <h3><span><?php _e('Export Download History in CSV','easy-digital-downloads' ); ?></span></h3>
                        <div class="inside">
                            <p><?php _e( 'Download a CSV of file downloads. To download a CSV for all file downloads, leave "Choose a Download" as it is.', 'easy-digital-downloads' ); ?></p>
                            <form id="edd-export-file-downloads" class="edd-export-form edd-import-export-form" method="post">
								<?php echo EDD()->html->product_dropdown( array( 'name' => 'download_id', 'id' => 'edd_file_download_export_download', 'chosen' => true ) ); ?>
								<?php echo EDD()->html->date_field( array( 'id' => 'edd-file-download-export-start', 'name' => 'start', 'placeholder' => __( 'Choose start date', 'easy-digital-downloads' ) )); ?>
								<?php echo EDD()->html->date_field( array( 'id' => 'edd-file-download-export-end', 'name' => 'end', 'placeholder' => __( 'Choose end date', 'easy-digital-downloads' ) )); ?>
								<?php wp_nonce_field( 'edd_ajax_export', 'edd_ajax_export' ); ?>
                                <input type="hidden" name="edd-export-class" value="EDD_Batch_File_Downloads_Export"/>
                                <input type="submit" value="<?php _e( 'Generate CSV', 'easy-digital-downloads' ); ?>" class="button-secondary"/>
                            </form>
                        </div><!-- .inside -->
                    </div><!-- .postbox -->

                    <div class="postbox edd-export-api-requests">
                        <h3><span><?php _e('Export API Requests in CSV','easy-digital-downloads' ); ?></span></h3>
                        <div class="inside">
                            <p><?php _e( 'Download a CSV of API request logs.', 'easy-digital-downloads' ); ?></p>
                            <form id="edd-export-api-requests" class="edd-export-form edd-import-export-form" method="post">
								<?php echo EDD()->html->date_field( array( 'id' => 'edd-api-requests-export-start', 'name' => 'start', 'placeholder' => __( 'Choose start date', 'easy-digital-downloads' ) )); ?>
								<?php echo EDD()->html->date_field( array( 'id' => 'edd-api-requests-export-end', 'name' => 'end', 'placeholder' => __( 'Choose end date', 'easy-digital-downloads' ) )); ?>
								<?php wp_nonce_field( 'edd_ajax_export', 'edd_ajax_export' ); ?>
                                <input type="hidden" name="edd-export-class" value="EDD_Batch_API_Requests_Export"/>
                                <input type="submit" value="<?php _e( 'Generate CSV', 'easy-digital-downloads' ); ?>" class="button-secondary"/>
                            </form>
                        </div><!-- .inside -->
                    </div><!-- .postbox -->

                    <div class="postbox edd-export-payment-history">
                        <h3><span><?php _e('Export Sales', 'easy-digital-downloads' ); ?></span></h3>
                        <div class="inside">
                            <p><?php _e( 'Download a CSV of all sales.', 'easy-digital-downloads' ); ?></p>

                            <form id="edd-export-sales" class="edd-export-form edd-import-export-form" method="post">
								<?php echo EDD()->html->product_dropdown( array( 'name' => 'download_id', 'id' => 'edd_sales_export_download', 'chosen' => true ) ); ?>
								<?php echo EDD()->html->date_field( array( 'id' => 'edd-sales-export-start', 'name' => 'start', 'placeholder' => __( 'Choose start date', 'easy-digital-downloads' ) )); ?>
								<?php echo EDD()->html->date_field( array( 'id' => 'edd-sales-export-end','name' => 'end', 'placeholder' => __( 'Choose end date', 'easy-digital-downloads' ) )); ?>
								<?php wp_nonce_field( 'edd_ajax_export', 'edd_ajax_export' ); ?>
                                <input type="hidden" name="edd-export-class" value="EDD_Batch_Sales_Export"/>
                                <span>
									<input type="submit" value="<?php _e( 'Generate CSV', 'easy-digital-downloads' ); ?>" class="button-secondary"/>
									<span class="spinner"></span>
								</span>
                            </form>
                        </div><!-- .inside -->
                    </div><!-- .postbox -->

					<?php do_action( 'edd_reports_tab_export_content_bottom' ); ?>

                </div><!-- .post-body-content -->
            </div><!-- .post-body -->
        </div><!-- .metabox-holder -->
    </div><!-- #edd-dashboard-widgets-wrap -->
	<?php
}
add_action( 'edd_reports_tab_export', 'edd_reports_tab_export' );

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