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

	// Get the section and report
	$section = Reports\get_current_report();
	$report  = Reports\get_report( $section );

	// Redirect if report is invalid
	if ( empty( $report ) || is_wp_error( $report ) ) {
		edd_redirect( $redirect_url );
	}

	// Enqueue the postbox JS
	wp_enqueue_script( 'postbox' );

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
	$sections->base_url = edd_get_admin_url( array(
		'page'             => 'edd-reports',
		'settings-updated' => false
	) );

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
        <h1><?php _e( 'Reports', 'easy-digital-downloads' ); ?></h1>

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
					'overview_busiest_day',
				),
				'charts' => array(
					'overview_sales_earnings_chart',
					'refunds_chart'
				),
			),
		) );

		$reports->register_endpoint( 'overview_time_period_data', array(
			'label' => __( 'Sales / Earnings', 'easy-digital-downloads' ),
			'views' => array(
				'tile' => array(
					'data_callback' => function () use ( $filter ) {
						$stats = new EDD\Stats( array(
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
						$stats = new EDD\Stats( array(
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
						$stats = new EDD\Stats();
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
						$stats = new EDD\Stats();
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
					'data_callback' => function () use ( $filter ) {
						$stats = new EDD\Stats();
						return apply_filters( 'edd_reports_overview_refunds', $stats->get_order_refund_count( array(
							'range' => $filter['range'],
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

		$reports->register_endpoint( 'overview_average_customer_revenue', array(
			'label' => __( 'Average Revenue per Customer', 'easy-digital-downloads' ),
			'views' => array(
				'tile' => array(
					'data_callback' => function () use ( $filter ) {
						$stats = new EDD\Stats();
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
						$stats = new EDD\Stats();
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
			'label' => __( 'Customer Growth', 'easy-digital-downloads' ),
			'views' => array(
				'tile' => array(
					'data_callback' => function () use ( $filter ) {
						$stats = new EDD\Stats();
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
						$stats = new EDD\Stats();
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
						$stats = new EDD\Stats();
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

		$reports->register_endpoint( 'overview_busiest_day', array(
			'label' => __( 'Busiest Day', 'easy-digital-downloads' ),
			'views' => array(
				'tile' => array(
					'data_callback' => function () use ( $filter ) {
						$stats = new EDD\Stats();
						return apply_filters( 'edd_reports_overview_busiest_day', $stats->get_busiest_day( array(
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

		$reports->register_endpoint( 'overview_sales_earnings_chart', array(
			'label' => __( 'Sales and Earnings', 'easy-digital-downloads' ) . ' &mdash; ' . $label,
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

		// Variables to hold date filter values.
		$options = Reports\get_dates_filter_options();
		$filter  = Reports\get_filter_value( 'dates' );
		$label   = $options[ $filter['range'] ];

		$download_data = Reports\get_filter_value( 'products' );
		$download_data = ! empty( $download_data ) && 'all' !== Reports\get_filter_value( 'products' )
			? edd_parse_product_dropdown_value( Reports\get_filter_value( 'products' ) )
			: false;

		$download_label = '';

		$country = Reports\get_filter_value( 'countries' );
		$region  = Reports\get_filter_value( 'regions' );

		if ( $download_data ) {
			$download = edd_get_download( $download_data['download_id'] );

			if ( $download_data['price_id'] ) {
				$prices = array_values( wp_filter_object_list( $download->get_prices(), array( 'index' => absint( $download_data['price_id'] ) ) ) );

				$download_label = esc_html( ' (' . $download->post_title . ': ' . $prices[0]['name'] . ')' );
			} else {
				$download_label = esc_html( ' (' . $download->post_title . ')' );
			}
		}

		$reports->add_report( 'downloads', array(
			'label'     => edd_get_label_plural(),
			'priority'  => 10,
			'icon'      => 'download',
			'endpoints' => array(
				'tiles'  => array(
					'most_valuable_download',
					'average_download_sales_earnings',
					'download_sales_earnings',
				),
				'charts' => array(
					'download_sales_by_variations',
					'download_earnings_by_variations',
                    'download_sales_earnings_chart'
				),
				'tables' => array(
					'top_selling_downloads',
					'earnings_by_taxonomy',
				),
			),
            'filters'   => array( 'products', 'countries', 'regions' )
		) );

		$reports->register_endpoint( 'most_valuable_download', array(
			'label' => sprintf( __( 'Most Valuable %s', 'easy-digital-downloads' ), edd_get_label_singular() ),
			'views' => array(
				'tile' => array(
					'data_callback' => function () use ( $filter ) {
						$stats = new EDD\Stats();
						$d = $stats->get_most_valuable_order_items( array(
							'range' => $filter['range'],
						) );

						if ( ! empty( $d ) && isset( $d[0] ) ) {
							$d = $d[0];

							if ( $d->object instanceof EDD_Download ) {
								$title = $d->object->post_title;

								if ( $d->object->has_variable_prices() ) {
									$prices = array_values( wp_filter_object_list( $d->object->get_prices(), array( 'index' => absint( $d->price_id ) ) ) );

									$title .= is_array( $prices )
										? ': ' . $prices[0]['name']
										: '';
								}

								return esc_html( $title );
							}
                        }
					},
					'display_args'  => array(
						'context'          => 'primary',
						'comparison_label' => $label,
					),
				),
			),
		) );

		$reports->register_endpoint( 'average_download_sales_earnings', array(
			'label' => __( 'Average Sales / Earnings', 'easy-digital-downloads' ),
			'views' => array(
				'tile' => array(
					'data_callback' => function () use ( $filter ) {
						$stats = new EDD\Stats( array(
							'function' => 'AVG',
							'range'    => $filter['range'],
							'output'   => 'formatted',
						) );

						return $stats->get_order_item_count() . ' / ' . $stats->get_order_item_earnings();
					},
					'display_args'  => array(
						'context'          => 'secondary',
						'comparison_label' => $label,
					),
				),
			),
		) );

		if ( ! empty( $download_label ) ) {
			$endpoint_label = __( 'Sales / Earnings', 'easy-digital-downloads' );

			$location = '';

			if ( ! empty( $country ) && 'all' !== $country ) {
				$location = ' ' . __( 'for', 'easy-digital-downloads' ) . ' ';

				if ( ! empty( $region ) && 'all' !== $region ) {
					$location .= edd_get_state_name( $country, $region ) . ', ';
				}

				$location .= edd_get_country_name( $country );
			}

			$country = 'all' !== $country
				? $country
				: '';

			$region = 'all' !== $region
				? $region
				: '';

			$endpoint_label .= $location;

		    $reports->register_endpoint( 'download_sales_earnings', array(
			    'label' => $endpoint_label,
			    'views' => array(
				    'tile' => array(
					    'data_callback' => function () use ( $filter, $download_data, $country, $region ) {
						    $stats = new EDD\Stats( array(
							    'product_id' => absint( $download_data['download_id'] ),
							    'price_id'   => absint( $download_data['price_id'] ),
							    'range'      => $filter['range'],
							    'output'     => 'formatted',
							    'country'    => $country,
							    'region'     => $region
						    ) );

						    $earnings = $stats->get_order_item_earnings();
						    $sales    = $stats->get_order_item_count();

						    return apply_filters( 'edd_reports_downloads_sales_earnings', esc_html( $sales . ' / ' . $earnings ) );
					    },
					    'display_args'  => array(
						    'context'          => 'tertiary',
						    'comparison_label' => $label . $download_label,
					    ),
				    ),
			    ),
            ) );
        }

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

        if ( $download_data && $download->has_variable_prices() ) {
	        $prices = $download->get_prices();

	        $reports->register_endpoint( 'download_sales_by_variations', array(
		        'label' => __( 'Sales by Variation for ', 'easy-digital-downloads' ) . esc_html( $download->post_title ) . ' &mdash; ' . $label,
		        'views' => array(
			        'chart' => array(
				        'data_callback' => function() use ( $filter, $download_data, $prices ) {
					        $stats = new EDD\Stats();
					        $sales = $stats->get_order_item_count( array(
						        'product_id' => absint( $download_data['download_id'] ),
						        'range'      => $filter['range'],
						        'grouped'    => true,
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
		        'label' => __( 'Earnings by Variation for ', 'easy-digital-downloads' ) . esc_html( $download->post_title ) . ' &mdash; ' . $label,
		        'views' => array(
			        'chart' => array(
				        'data_callback' => function() use ( $filter, $download_data, $prices ) {
					        $stats = new EDD\Stats();
					        $earnings = $stats->get_order_item_earnings( array(
						        'product_id' => absint( $download_data['download_id'] ),
						        'range'      => $filter['range'],
						        'grouped'    => true,
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
        }

        if ( $download_data ) {
            $download_label = $download->post_title;

            if ( ! empty( $download_data['price_id'] ) ) {
	            $prices = array_values( wp_filter_object_list( $download->get_prices(), array( 'index' => absint( $download_data['price_id'] ) ) ) );

	            $download_label .= ': ' . $prices[0]['name'];
            }

	        $reports->register_endpoint( 'download_sales_earnings_chart', array(
		        'label' => __( 'Sales and Earnings for ', 'easy-digital-downloads' ) . esc_html( $download_label ),
		        'views' => array(
			        'chart' => array(
				        'data_callback' => function () use ( $filter, $download_data ) {
					        global $wpdb;

					        $dates        = Reports\get_dates_filter( 'objects' );
					        $day_by_day   = Reports\get_dates_filter_day_by_day();
					        $hour_by_hour = Reports\get_dates_filter_hour_by_hour();

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
					        } elseif ( $hour_by_hour ) {
						        $sql_clauses = array(
							        'select'  => 'YEAR(date_created) AS year, MONTH(date_created) AS month, DAY(date_created) AS day, HOUR(date_created) AS hour',
							        'groupby' => 'YEAR(date_created), MONTH(date_created), DAY(date_created), HOUR(date_created)',
							        'orderby' => 'YEAR(date_created), MONTH(date_created), DAY(date_created), HOUR(date_created)',
						        );
					        }

					        $price_id = ! empty( $download_data['price_id'] )
						        ? $wpdb->prepare( 'AND price_id = %d', absint( $download_data['price_id'] ) )
						        : '';

					        $results = $wpdb->get_results( $wpdb->prepare(
						        "SELECT COUNT(total) AS sales, SUM(total) AS earnings, {$sql_clauses['select']}
                                 FROM {$wpdb->edd_order_items} edd_oi
                                 WHERE product_id = %d {$price_id} AND date_created >= %s AND date_created <= %s
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

						        $sales[ $timestamp ][1]    = $result->sales;
						        $earnings[ $timestamp ][1] = floatval( $result->earnings );
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
        }
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
		$options = Reports\get_dates_filter_options();
		$filter  = Reports\get_filter_value( 'dates' );
		$label   = $options[ $filter['range'] ];

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
			'filters'   => array( 'products' ),
		) );

		$reports->register_endpoint( 'refund_count', array(
			'label' => __( 'Number of Refunds', 'easy-digital-downloads' ),
			'views' => array(
				'tile' => array(
					'data_callback' => function () use ( $filter ) {
						$stats  = new EDD\Stats();
						$number = $stats->get_order_refund_count( array(
							'range' => $filter['range'],
						) );
						return apply_filters( 'edd_reports_refunds_refund_count', esc_html( $number ) );
					},
					'display_args'  => array(
						'context'          => 'primary',
						'comparison_label' => $label,
					),
				),
			),
		) );

		$reports->register_endpoint( 'fully_refunded_order_count', array(
			'label' => __( 'Number of Fully Refunded Orders', 'easy-digital-downloads' ),
			'views' => array(
				'tile' => array(
					'data_callback' => function () use ( $filter ) {
						$stats  = new EDD\Stats();
						$number = $stats->get_order_refund_count( array(
							'range'  => $filter['range'],
							'status' => array( 'complete' ),
						) );
						return apply_filters( 'edd_reports_refunds_fully_refunded_order_count', esc_html( $number ) );
					},
					'display_args'  => array(
						'context'          => 'secondary',
						'comparison_label' => $label,
					),
				),
			),
		) );

		$reports->register_endpoint( 'fully_refunded_order_item_count', array(
			'label' => __( 'Number of Fully Refunded Items', 'easy-digital-downloads' ),
			'views' => array(
				'tile' => array(
					'data_callback' => function () use ( $filter ) {
						$stats  = new EDD\Stats();
						$number = $stats->get_order_item_refund_count( array(
							'range'  => $filter['range'],
							'status' => array( 'refunded' ),
						) );
						return apply_filters( 'edd_reports_refunds_fully_refunded_order_item_count', esc_html( $number ) );
					},
					'display_args'  => array(
						'context'          => 'tertiary',
						'comparison_label' => $label,
					),
				),
			),
		) );

		$reports->register_endpoint( 'refund_amount', array(
			'label' => __( 'Total Refund Amount', 'easy-digital-downloads' ),
			'views' => array(
				'tile' => array(
					'data_callback' => function () use ( $filter ) {
						$stats  = new EDD\Stats();
						$amount = $stats->get_order_refund_amount( array(
							'range'  => $filter['range'],
							'output' => 'formatted',
						) );

						return apply_filters( 'edd_reports_refunds_refund_amount', esc_html( $amount ) );
					},
					'display_args'  => array(
						'context'          => 'primary',
						'comparison_label' => $label,
					),
				),
			),
		) );

		$reports->register_endpoint( 'average_refund_amount', array(
			'label' => __( 'Average Refund Amount', 'easy-digital-downloads' ),
			'views' => array(
				'tile' => array(
					'data_callback' => function () use ( $filter ) {
						$stats = new EDD\Stats();
						return apply_filters( 'edd_reports_refunds_average_refund_amount', $stats->get_order_refund_amount( array(
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

		$reports->register_endpoint( 'average_time_to_refund', array(
			'label' => __( 'Average Time to Refund', 'easy-digital-downloads' ),
			'views' => array(
				'tile' => array(
					'data_callback' => function () use ( $filter ) {
						$stats = new EDD\Stats();
						return $stats->get_average_refund_time( array(
							'range' => $filter['range'],
						) );
					},
					'display_args'  => array(
						'context'          => 'tertiary',
						'comparison_label' => $label,
					),
				),
			),
		) );

		$reports->register_endpoint( 'refund_rate', array(
			'label' => __( 'Refund Rate', 'easy-digital-downloads' ),
			'views' => array(
				'tile' => array(
					'data_callback' => function () use ( $filter ) {
						$stats = new EDD\Stats();
						return apply_filters( 'edd_reports_refunds_refund_rate', $stats->get_refund_rate( array(
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
				'charts' => array(
					'gateway_sales_breakdown',
					'gateway_earnings_breakdown',
					'gateway_sales_earnings_chart',
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

						$stats = new EDD\Stats();

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

						$stats = new EDD\Stats();

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

						$stats = new EDD\Stats();

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

						$stats = new EDD\Stats();

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
			'label' => __( 'Gateway Stats', 'easy-digital-downloads' ) . ' &mdash; ' . $options[ $filter['range'] ],
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
			'label' => __( 'Gateway Sales', 'easy-digital-downloads' ) . ' &mdash; ' . $options[ $filter['range'] ],
			'views' => array(
				'chart' => array(
					'data_callback' => function() use ( $filter ) {
						$stats = new EDD\Stats();
						$g = $stats->get_gateway_sales( array(
							'range'    => $filter['range'],
							'grouped'  => true,
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
			'label' => __( 'Gateway Earnings', 'easy-digital-downloads' ) . ' &mdash; ' . $options[ $filter['range'] ],
			'views' => array(
				'chart' => array(
					'data_callback' => function() use ( $filter ) {
						$stats = new EDD\Stats();
						$g = $stats->get_gateway_earnings( array(
							'grouped' => true,
							'range'   => $filter['range'],
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

		if ( ! empty( $gateway ) ) {
			$reports->register_endpoint( 'gateway_sales_earnings_chart', array(
				'label' => __( 'Sales and Earnings', 'easy-digital-downloads' ) . ' &mdash; ' . $label,
				'views' => array(
					'chart' => array(
						'data_callback' => function () use ( $filter ) {
							global $wpdb;

							$dates        = Reports\get_dates_filter( 'objects' );
							$day_by_day   = Reports\get_dates_filter_day_by_day();
							$hour_by_hour = Reports\get_dates_filter_hour_by_hour();

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
							} elseif ( $hour_by_hour ) {
								$sql_clauses = array(
									'select'  => 'YEAR(date_created) AS year, MONTH(date_created) AS month, DAY(date_created) AS day, HOUR(date_created) AS hour',
									'groupby' => 'YEAR(date_created), MONTH(date_created), DAY(date_created), HOUR(date_created)',
									'orderby' => 'YEAR(date_created), MONTH(date_created), DAY(date_created), HOUR(date_created)',
								);
							}

							$gateway = Reports\get_filter_value( 'gateways' );

							$results = $wpdb->get_results( $wpdb->prepare(
								"SELECT COUNT(total) AS sales, SUM(total) AS earnings, {$sql_clauses['select']}
								 FROM {$wpdb->edd_orders} o
								 WHERE gateway = %s AND status IN ('complete', 'revoked') AND date_created >= %s AND date_created <= %s
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

								$sales[ $timestamp ][1] = $result->sales;
								$earnings[ $timestamp ][1] = floatval( $result->earnings );
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
        }
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
		$options = Reports\get_dates_filter_options();
		$filter  = Reports\get_filter_value( 'dates' );
		$label   = $options[ $filter['range'] ];

		$download_data = Reports\get_filter_value( 'products' );
		$download_data = ! empty( $download_data ) && 'all' !== Reports\get_filter_value( 'products' )
			? edd_parse_product_dropdown_value( Reports\get_filter_value( 'products' ) )
			: false;

		$download_label = '';

		if ( $download_data ) {
			$download = edd_get_download( $download_data['download_id'] );

			if ( $download_data['price_id'] ) {
				$prices = array_values( wp_filter_object_list( $download->get_prices(), array( 'index' => absint( $download_data['price_id'] ) ) ) );

				$download_label = esc_html( ' (' . $download->post_title . ': ' . $prices[0]['name'] . ')' );
			} else {
				$download_label = esc_html( ' (' . $download->post_title . ')' );
			}
		}

		$country = Reports\get_filter_value( 'countries' );
		$region  = Reports\get_filter_value( 'regions' );

		$reports->add_report( 'taxes', array(
			'label'     => __( 'Taxes', 'easy-digital-downloads' ),
			'priority'  => 25,
			'icon'      => 'editor-paste-text',
			'endpoints' => array(
				'tiles' => array(
					'total_tax_collected',
					'total_tax_collected_for_location',
				),
				'tables' => array(
					'tax_collected_by_location',
				),
			),
			'filters'   => array( 'products', 'countries', 'regions' ),
		) );

		$reports->register_endpoint( 'total_tax_collected', array(
			'label' => __( 'Total Tax Collected', 'easy-digital-downloads' ),
			'views' => array(
				'tile' => array(
					'data_callback' => function () use ( $filter ) {
						$download = Reports\get_filter_value( 'products' );
						$download = ! empty( $download ) && 'all' !== Reports\get_filter_value( 'products' )
							? edd_parse_product_dropdown_value( Reports\get_filter_value( 'products' ) )
							: array( 'download_id' => '', 'price_id' => '' );

						$stats = new EDD\Stats();
						return $stats->get_tax( array(
							'output'      => 'formatted',
							'range'       => $filter['range'],
							'download_id' => $download['download_id'],
							'price_id'    => (string) $download['price_id'],
						) );
					},
					'display_args'  => array(
						'context'          => 'primary',
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
						'data_callback' => function () use ( $filter, $country, $region ) {
							$download = Reports\get_filter_value( 'products' );
							$download = ! empty( $download ) && 'all' !== Reports\get_filter_value( 'products' )
								? edd_parse_product_dropdown_value( Reports\get_filter_value( 'products' ) )
								: array( 'download_id' => '', 'price_id' => '' );

							$stats = new EDD\Stats();

							return $stats->get_tax_by_location( array(
								'output'      => 'formatted',
								'range'       => $filter['range'],
								'download_id' => $download['download_id'],
								'price_id'    => (string) $download['price_id'],
								'country'     => $country,
								'region'      => $region
							) );
						},
						'display_args'  => array(
							'context'          => 'secondary',
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
		$label   = $options[ $filter['range'] ];

		$download_data = Reports\get_filter_value( 'products' );
		$download_data = ! empty( $download_data ) && 'all' !== Reports\get_filter_value( 'products' )
			? edd_parse_product_dropdown_value( Reports\get_filter_value( 'products' ) )
			: false;

		$download_label = '';

		if ( $download_data ) {
			$download = edd_get_download( $download_data['download_id'] );

			if ( $download_data['price_id'] ) {
				$prices = array_values( wp_filter_object_list( $download->get_prices(), array( 'index' => absint( $download_data['price_id'] ) ) ) );

				$download_label = esc_html( ' (' . $download->post_title . ': ' . $prices[0]['name'] . ')' );
			} else {
				$download_label = esc_html( ' (' . $download->post_title . ')' );
			}
		}

		$reports->add_report( 'file_downloads', array(
			'label'     => __( 'File Downloads', 'easy-digital-downloads' ),
			'icon'      => 'download',
			'priority'  => 30,
			'endpoints' => array(
				'tiles'  => array(
					'number_of_file_downloads',
					'average_file_downloads_per_customer',
					'most_downloaded_product',
					'average_file_downloads_per_order',
				),
				'tables' => array(
					'top_five_most_downloaded_products',
				),
				'charts' => array(
					'file_downloads_chart',
				),
			),
			'filters'   => array( 'products' ),
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
						return apply_filters( 'edd_reports_file_downloads_number_of_file_downloads', $stats->get_file_download_count( array(
							'range'       => $filter['range'],
							'download_id' => $download['download_id'],
							'price_id'    => (string) $download['price_id'],
						) ) );
					},
					'display_args'  => array(
						'context'          => 'primary',
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
						return apply_filters( 'edd_reports_file_downloads_average_per_customer', $stats->get_average_file_download_count( array(
							'range'  => $filter['range'],
							'column' => 'customer_id',
						) ) );
					},
					'display_args'  => array(
						'context'          => 'secondary',
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
                        return apply_filters( 'edd_reports_file_downloads_average_per_order', $stats->get_average_file_download_count( array(
	                        'range'  => $filter['range'],
	                        'column' => 'order_id',
                        ) ) );
					},
					'display_args'  => array(
						'context'          => 'primary',
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
							return apply_filters( 'edd_reports_file_downloads_most_downloaded_product', esc_html( $d[0]->object->post_title ) );
						}
					},
					'display_args'  => array(
						'context'          => 'tertiary',
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
						$day_by_day   = Reports\get_dates_filter_day_by_day();
						$hour_by_hour = Reports\get_dates_filter_hour_by_hour();

						$sql_clauses = array(
							'select'  => 'YEAR(edd_lfd.date_created) AS year, MONTH(edd_lfd.date_created) AS month, DAY(edd_lfd.date_created) AS day',
							'groupby' => 'YEAR(edd_lfd.date_created), MONTH(edd_lfd.date_created), DAY(edd_lfd.date_created)',
							'orderby' => 'YEAR(edd_lfd.date_created), MONTH(edd_lfd.date_created), DAY(edd_lfd.date_created)',
						);

						if ( ! $day_by_day ) {
							$sql_clauses = array(
								'select'  => 'YEAR(date_created) AS year, MONTH(date_created) AS month',
								'groupby' => 'YEAR(date_created), MONTH(date_created)',
								'orderby' => 'YEAR(date_created), MONTH(date_created)',
							);
						} elseif ( $hour_by_hour ) {
							$sql_clauses = array(
								'select'  => 'YEAR(date_created) AS year, MONTH(date_created) AS month, DAY(date_created) AS day, HOUR(date_created) AS hour',
								'groupby' => 'YEAR(date_created), MONTH(date_created), DAY(date_created), HOUR(date_created)',
								'orderby' => 'YEAR(date_created), MONTH(date_created), DAY(date_created), HOUR(date_created)',
							);
						}

						$product_id = '';
						$price_id   = '';

						if ( is_array( $download_data ) ) {
							$product_id = $wpdb->prepare( 'AND product_id = %d', absint( $download_data['download_id'] ) );

							$price_id = ! empty( $download_data['price_id'] )
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
							if ( $hour_by_hour ) {
								$timestamp = \Carbon\Carbon::create( $dates['start']->year, $dates['start']->month, $dates['start']->day, $dates['start']->hour, 0, 0, 'UTC' )->setTimezone( edd_get_timezone_id() )->timestamp;

								$file_downloads[ $timestamp ][] = $timestamp;
								$file_downloads[ $timestamp ][] = 0;

								$earnings[ $timestamp ][] = $timestamp;
								$earnings[ $timestamp ][] = 0.00;

								$dates['start']->addHour( 1 );
							} else {
								$day = ( true === $day_by_day )
									? $dates['start']->day
									: 1;

								$timestamp = \Carbon\Carbon::create( $dates['start']->year, $dates['start']->month, $day, 0, 0, 0, 'UTC' )->setTimezone( edd_get_timezone_id() )->timestamp;

								$file_downloads[ $timestamp ][] = $timestamp;
								$file_downloads[ $timestamp ][] = 0;

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

							$file_downloads[ $timestamp ][1] = $result->total;
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
				'charts' => array(
					'discount_usage_chart',
				),
			),
			'filters'   => array( 'discounts' ),
		) );

		$reports->register_endpoint( 'number_of_discounts_used', array(
			'label' => __( 'Number of Discounts Used', 'easy-digital-downloads' ),
			'views' => array(
				'tile' => array(
					'data_callback' => function () use ( $filter ) {
						$stats = new EDD\Stats();
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
			'label' => __( 'Discount Ratio', 'easy-digital-downloads' ),
			'views' => array(
				'tile' => array(
					'data_callback' => function () use ( $filter ) {
						$stats = new EDD\Stats();
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
						$stats = new EDD\Stats();
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
						$stats = new EDD\Stats();
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
						$stats = new EDD\Stats();

						$r = apply_filters( 'edd_reports_discounts_most_popular_discount', $stats->get_most_popular_discounts( array(
							'range'  => $filter['range'],
							'number' => 1,
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
							$stats = new EDD\Stats();
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

							if ( ! $day_by_day ) {
								$sql_clauses = array(
									'select'  => 'YEAR(edd_oa.date_created) AS year, MONTH(edd_oa.date_created) AS month',
									'groupby' => 'YEAR(edd_oa.date_created), MONTH(edd_oa.date_created)',
									'orderby' => 'YEAR(edd_oa.date_created), MONTH(edd_oa.date_created)',
								);
							} elseif ( $hour_by_hour ) {
								$sql_clauses = array(
									'select'  => 'YEAR(edd_oa.date_created) AS year, MONTH(edd_oa.date_created) AS month, DAY(edd_oa.date_created) AS day, HOUR(edd_oa.date_created) AS hour',
									'groupby' => 'YEAR(edd_oa.date_created), MONTH(edd_oa.date_created), DAY(edd_oa.date_created), HOUR(edd_oa.date_created)',
									'orderby' => 'YEAR(edd_oa.date_created), MONTH(edd_oa.date_created), DAY(edd_oa.date_created), HOUR(edd_oa.date_created)',
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

								$discount_usage[ $timestamp ][1] = $result->total;
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
						$stats = new EDD\Stats();
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
						$stats = new EDD\Stats();
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
						$stats = new EDD\Stats();
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
					'data_callback' => function () use ( $filter ) {
						global $wpdb;

						$dates        = Reports\get_dates_filter( 'objects' );
						$day_by_day   = Reports\get_dates_filter_day_by_day();
						$hour_by_hour = Reports\get_dates_filter_hour_by_hour();

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
						} elseif ( $hour_by_hour ) {
							$sql_clauses = array(
								'select'  => 'YEAR(date_created) AS year, MONTH(date_created) AS month, DAY(date_created) AS day, HOUR(date_created) AS hour',
								'groupby' => 'YEAR(date_created), MONTH(date_created), DAY(date_created), HOUR(date_created)',
								'orderby' => 'YEAR(date_created), MONTH(date_created), DAY(date_created), HOUR(date_created)',
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

							$customers[ $timestamp ][1] = $result->total;
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
    ?>
    <div id="edd-dashboard-widgets-wrap">
        <div class="metabox-holder">
            <div id="post-body">
                <div id="post-body-content">
					<?php do_action( 'edd_reports_tab_export_content_top' ); ?>

                    <div class="postbox edd-export-earnings-report">
                        <h3 class="hndle"><span><?php esc_html_e( 'Export Earnings Report', 'easy-digital-downloads' ); ?></span></h3>
                        <div class="inside">
                            <p><?php esc_html_e( 'Download a CSV giving a detailed look into earnings over time.', 'easy-digital-downloads' ); ?></p>
                            <form id="edd-export-earnings" class="edd-export-form edd-import-export-form" method="post">
								<?php echo EDD()->html->month_dropdown( 'start_month' ); ?>
								<?php echo EDD()->html->year_dropdown( 'start_year' ); ?>
								<span class="edd-label"><?php echo _x( 'to', 'Date one to date two', 'easy-digital-downloads' ); ?></span>
								<?php echo EDD()->html->month_dropdown( 'end_month' ); ?>
								<?php echo EDD()->html->year_dropdown( 'end_year' ); ?>
								<?php wp_nonce_field( 'edd_ajax_export', 'edd_ajax_export' ); ?>
                                <input type="hidden" name="edd-export-class" value="EDD_Batch_Earnings_Report_Export"/>
                                <span>
									<input type="submit" value="<?php esc_html_e( 'Generate CSV', 'easy-digital-downloads' ); ?>" class="button-secondary"/>
									<span class="spinner"></span>
								</span>
                            </form>
                        </div>
                    </div>

                    <div class="postbox edd-export-sales-earnings">
                        <h3 class="hndle"><span><?php esc_html_e( 'Export Sales and Earnings', 'easy-digital-downloads' ); ?></span></h3>
                        <div class="inside">
                            <p><?php esc_html_e( 'Download a CSV of all sales or earnings on a day-by-day basis.', 'easy-digital-downloads' ); ?></p>

                            <form id="edd-export-sales-earnings" class="edd-export-form edd-import-export-form" method="post">
								<span class="edd-from-to-wrapper">
									<?php

									echo EDD()->html->date_field( array(
										'id'          => 'edd-order-export-start',
										'name'        => 'start',
										'placeholder' => _x( 'From', 'date filter', 'easy-digital-downloads' ),
									) );

									echo EDD()->html->date_field( array(
										'id'          => 'edd-order-export-end',
										'name'        => 'end',
										'placeholder' => _x( 'To', 'date filter', 'easy-digital-downloads' ),
									) );

								?></span><?php

				                echo EDD()->html->product_dropdown( array(
					                'name'   => 'download_id',
					                'id'     => 'edd_orders_export_download',
					                'chosen' => true,
				                ) );

				                echo EDD()->html->customer_dropdown( array(
					                'name'          => 'customer_id',
					                'id'            => 'edd_order_export_customer',
					                'chosen'        => true,
					                'none_selected' => '',
				                ) );

				                wp_nonce_field( 'edd_ajax_export', 'edd_ajax_export' ); ?>

                                <input type="hidden" name="edd-export-class" value="EDD_Batch_Sales_And_Earnings_Export"/>

                                <span>
									<input type="submit" value="<?php esc_html_e( 'Export', 'easy-digital-downloads' ); ?>" class="button-secondary"/>
									<span class="spinner"></span>
								</span>
                            </form>
                        </div>
                    </div>

                    <div class="postbox edd-export-orders">
                        <h3 class="hndle"><span><?php esc_html_e( 'Export Orders', 'easy-digital-downloads' ); ?></span></h3>
                        <div class="inside">
                            <p><?php esc_html_e( 'Download a CSV of all orders.', 'easy-digital-downloads' ); ?></p>
                            <form id="edd-export-orders" class="edd-export-form edd-import-export-form" method="post">
								<span class="edd-from-to-wrapper">
									<?php

									echo EDD()->html->date_field( array(
										'id'          => 'edd-orders-export-start',
										'name'        => 'start',
										'placeholder' => _x( 'From', 'date filter', 'easy-digital-downloads' ),
									) );

									echo EDD()->html->date_field( array(
										'id'          => 'edd-orders-export-end',
										'name'        => 'end',
										'placeholder' => _x( 'To', 'date filter', 'easy-digital-downloads' ),
									) );

								?></span><?php

								echo EDD()->html->select( array(
									'id'              => 'edd-orders-export-status',
									'name'            => 'status',
									'show_option_all' => __( 'All Statuses', 'easy-digital-downloads' ),
									'selected'        => false,
									'chosen'          => true,
									'options'         => edd_get_payment_statuses(),
								) );

								wp_nonce_field( 'edd_ajax_export', 'edd_ajax_export' );
								?>
                                <input type="hidden" name="edd-export-class" value="EDD_Batch_Payments_Export"/>
                                <span>
									<input type="submit" value="<?php esc_html_e( 'Generate CSV', 'easy-digital-downloads' ); ?>" class="button-secondary"/>
									<span class="spinner"></span>
								</span>
                            </form>
                        </div>
                    </div>

                    <div class="postbox edd-export-taxed-orders">
                        <h3 class="hndle"><span><?php esc_html_e( 'Export Taxed Orders', 'easy-digital-downloads' ); ?></span></h3>
                        <div class="inside">
                            <p><?php esc_html_e( 'Download a CSV of all orders, taxed by Country and/or Region.', 'easy-digital-downloads' ); ?></p>
                            <form id="edd-export-taxed-orders" class="edd-export-form edd-import-export-form" method="post">
								<span class="edd-from-to-wrapper">
									<?php

									echo EDD()->html->date_field( array(
										'id'          => 'edd-taxed-orders-export-start',
										'name'        => 'start',
										'placeholder' => _x( 'From', 'date filter', 'easy-digital-downloads' )
									) );

									echo EDD()->html->date_field( array(
										'id'          => 'edd-taxed-orders-export-end',
										'name'        => 'end',
										'placeholder' => _x( 'To', 'date filter', 'easy-digital-downloads' )
									) );

								?></span><?php

								echo EDD()->html->select( array(
									'id'              => 'edd-taxed-orders-export-status',
									'name'            => 'status',
									'show_option_all' => __( 'All Statuses', 'easy-digital-downloads' ),
									'selected'        => false,
									'chosen'          => true,
									'options'         => edd_get_payment_statuses(),
								) );

		                        echo EDD()->html->select( array(
			                        'name'             => 'country',
			                        'id'               => 'edd_reports_filter_countries',
			                        'options'          => edd_get_country_list(),
			                        'chosen'           => true,
			                        'selected'         => false,
			                        'show_option_none' => false,
			                        'placeholder'      => __( 'Choose a Country', 'easy-digital-downloads' ),
			                        'show_option_all'  => __( 'All Countries', 'easy-digital-downloads' ),
		                        ) );

		                        echo EDD()->html->select( array(
			                        'name'             => 'region',
			                        'id'               => 'edd_reports_filter_regions',
			                        'options'          => edd_get_shop_states(),
			                        'chosen'           => true,
			                        'selected'         => false,
			                        'show_option_none' => false,
			                        'placeholder'      => __( 'Choose a Region', 'easy-digital-downloads' ),
			                        'show_option_all'  => __( 'All Regions', 'easy-digital-downloads' ),
		                        ) );

		                        wp_nonce_field( 'edd_ajax_export', 'edd_ajax_export' );
								?>
                                <input type="hidden" name="edd-export-class" value="EDD_Batch_Taxed_Orders_Export"/>
                                <span>
									<input type="submit" value="<?php esc_html_e( 'Generate CSV', 'easy-digital-downloads' ); ?>" class="button-secondary"/>
									<span class="spinner"></span>
								</span>
                            </form>
                        </div>
                    </div>

                    <div class="postbox edd-export-customers">
                        <h3 class="hndle"><span><?php esc_html_e( 'Export Customers','easy-digital-downloads' ); ?></span></h3>
                        <div class="inside">
                            <p><?php printf( esc_html__( 'Download a CSV of customers. Select a taxonomy to see all the customers who purchased %s in that taxonomy.', 'easy-digital-downloads' ), edd_get_label_plural( true ) ); ?></p>
                            <form id="edd-export-customers" class="edd-export-form edd-import-export-form" method="post">
				                <?php
				                $taxonomies = get_object_taxonomies( 'download', 'names' );
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

				                echo EDD()->html->select( array(
					                'name'             => 'taxonomy',
					                'options'          => $taxonomies,
					                'chosen'           => true,
					                'selected'         => false,
					                'show_option_none' => false,
					                'placeholder'      => __( 'Select a Taxonomy', 'easy-digital-downloads' ),
					                'show_option_all'  => __( 'All Taxonomies', 'easy-digital-downloads' ),
				                ) );

				                echo EDD()->html->product_dropdown( array(
					                'name'   => 'download',
					                'id'     => 'edd_customer_export_download',
					                'chosen' => true,
				                ) );

				                wp_nonce_field( 'edd_ajax_export', 'edd_ajax_export' );
				                ?>
                                <input type="hidden" name="edd-export-class" value="EDD_Batch_Customers_Export"/>
                                <input type="submit" value="<?php esc_html_e( 'Generate CSV', 'easy-digital-downloads' ); ?>" class="button-secondary"/>
                            </form>
                        </div>
                    </div>

                    <div class="postbox edd-export-taxed-customers">
                        <h3 class="hndle"><span><?php esc_html_e( 'Export Taxed Customers', 'easy-digital-downloads' ); ?></span></h3>
                        <div class="inside">
                            <p><?php esc_html_e( 'Download a CSV of all customers that were taxed.', 'easy-digital-downloads' ); ?></p>
                            <form id="edd-export-taxed-customers" class="edd-export-form edd-import-export-form" method="post">
								<span class="edd-from-to-wrapper">
									<?php

									echo EDD()->html->date_field( array(
										'id'          => 'edd-taxed-orders-export-start',
										'name'        => 'start',
										'placeholder' => _x( 'From', 'date filter', 'easy-digital-downloads' )
									) );

									echo EDD()->html->date_field( array(
										'id'          => 'edd-taxed-orders-export-end',
										'name'        => 'end',
										'placeholder' => _x( 'To', 'date filter', 'easy-digital-downloads' )
									) );

								?></span><?php

				                wp_nonce_field( 'edd_ajax_export', 'edd_ajax_export' );

								?>
                                <input type="hidden" name="edd-export-class" value="EDD_Batch_Taxed_Customers_Export"/>
                                <span>
									<input type="submit" value="<?php esc_html_e( 'Generate CSV', 'easy-digital-downloads' ); ?>" class="button-secondary"/>
									<span class="spinner"></span>
								</span>
                            </form>
                        </div>
                    </div>

                    <div class="postbox edd-export-downloads">
                        <h3 class="hndle"><span><?php esc_html_e( sprintf( __( 'Export %s','easy-digital-downloads' ), edd_get_label_plural() ) ); ?></span></h3>
                        <div class="inside">
                            <p><?php esc_html_e( sprintf( __( 'Download a CSV of %1$s. To download a CSV for all %1$s, leave "Choose a %2$s" as it is.', 'easy-digital-downloads' ), edd_get_label_plural( true ), edd_get_label_singular() ) ); ?></p>
                            <form id="edd-export-file-downloads" class="edd-export-form edd-import-export-form" method="post">
								<?php echo EDD()->html->product_dropdown( array( 'name' => 'download_id', 'id' => 'edd_download_export_download', 'chosen' => true ) ); ?>
								<?php wp_nonce_field( 'edd_ajax_export', 'edd_ajax_export' ); ?>
                                <input type="hidden" name="edd-export-class" value="EDD_Batch_Downloads_Export"/>
                                <input type="submit" value="<?php esc_html_e( 'Generate CSV', 'easy-digital-downloads' ); ?>" class="button-secondary"/>
                            </form>
                        </div>
                    </div>

                    <div class="postbox edd-export-api-requests">
                        <h3 class="hndle"><span><?php esc_html_e('Export API Request Logs','easy-digital-downloads' ); ?></span></h3>
                        <div class="inside">
                            <p><?php esc_html_e( 'Download a CSV of API request logs.', 'easy-digital-downloads' ); ?></p>
                            <form id="edd-export-api-requests" class="edd-export-form edd-import-export-form" method="post">
								<span class="edd-from-to-wrapper">
					                <?php

									echo EDD()->html->date_field( array(
										'id'          => 'edd-api-requests-export-start',
										'name'        => 'start',
										'placeholder' => _x( 'From', 'date filter', 'easy-digital-downloads' )
									) );

									echo EDD()->html->date_field( array(
										'id'          => 'edd-api-requests-export-end',
										'name'        => 'end',
										'placeholder' => _x( 'To', 'date filter', 'easy-digital-downloads' )
									) );

								?></span><?php

				                wp_nonce_field( 'edd_ajax_export', 'edd_ajax_export' );

								?>
                                <input type="hidden" name="edd-export-class" value="EDD_Batch_API_Requests_Export"/>
                                <input type="submit" value="<?php esc_html_e( 'Generate CSV', 'easy-digital-downloads' ); ?>" class="button-secondary"/>
                            </form>
                        </div>
                    </div>

                    <div class="postbox edd-export-download-history">
                        <h3 class="hndle"><span><?php esc_html_e('Export File Download Logs','easy-digital-downloads' ); ?></span></h3>
                        <div class="inside">
                            <p><?php esc_html_e( 'Download a CSV of file downloads. To download a CSV for all file downloads, leave "Choose a Download" as it is.', 'easy-digital-downloads' ); ?></p>
                            <form id="edd-export-file-downloads" class="edd-export-form edd-import-export-form" method="post">
								<?php echo EDD()->html->product_dropdown( array( 'name' => 'download_id', 'id' => 'edd_file_download_export_download', 'chosen' => true ) ); ?>
								<span class="edd-from-to-wrapper">
									<?php

									echo EDD()->html->date_field( array(
										'id'          => 'edd-file-download-export-start',
										'name'        => 'start',
										'placeholder' => _x( 'From', 'date filter', 'easy-digital-downloads' )
									) );

									echo EDD()->html->date_field( array(
										'id'          => 'edd-file-download-export-end',
										'name'        => 'end',
										'placeholder' => _x( 'To', 'date filter', 'easy-digital-downloads' )
									) );

								?></span><?php

								wp_nonce_field( 'edd_ajax_export', 'edd_ajax_export' );

								?>
                                <input type="hidden" name="edd-export-class" value="EDD_Batch_File_Downloads_Export"/>
                                <input type="submit" value="<?php esc_html_e( 'Generate CSV', 'easy-digital-downloads' ); ?>" class="button-secondary"/>
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
