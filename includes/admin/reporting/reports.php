<?php
/**
 * Reports functions.
 *
 * @package     EDD\Admin\Reports
 * @copyright   Copyright (c) 2018, Sandhills Development, LLC
 * @license     https://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       1.0
 * @since       3.0 Full refactor of Reports.
 */

use EDD\Reports;

// Exit if accessed directly.
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

	// Redirect URL (on error).
	$redirect_url = edd_get_admin_url(
		array(
			'page' => 'edd-reports',
			'view' => 'overview',
		)
	);

	// Redirect if user cannot view reports.
	if ( ! current_user_can( 'view_shop_reports' ) ) {
		edd_redirect( $redirect_url );
	}

	// Start the Reports API.
	new Reports\Init();

	add_filter( 'edd_admin_is_single_view', '__return_false' );

	// Get the section and report.
	$section = Reports\get_current_report();
	$report  = Reports\get_report( $section );

	// Redirect if report is invalid.
	if ( empty( $report ) || is_wp_error( $report ) ) {
		edd_redirect( $redirect_url );
	}

	// Stash the report in the EDD global for future reference.
	EDD()->report = $report;
}
add_action( 'load-download_page_edd-reports', 'edd_admin_load_report' );

/**
 * Contains backwards compat code to shim tabs & views to EDD_Sections()
 *
 * @since 3.0
 */
function edd_reports_sections() {

	// Instantiate the Sections class and sections array.
	$sections   = new EDD\Admin\Reports_Sections();
	$c_sections = array();

	// Setup sections variables.
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
				'page' => 'edd-reports',
			),
			$persisted_filter_args
		)
	);

	// Get all registered tabs & views.
	$tabs = Reports\get_reports();

	// Loop through tabs & setup sections.
	if ( ! empty( $tabs ) ) {
		foreach ( $tabs as $id => $tab ) {

			// Add to sections array.
			$c_sections[] = array(
				'id'       => $id,
				'label'    => $tab['label'],
				'icon'     => $tab['icon'],
				'callback' => array( 'edd_output_report_callback', array( $id ) ),
			);
		}
	}

	// Set the customer sections.
	$sections->set_sections( $c_sections );

	// Display the sections.
	$sections->display();
}

/**
 * Output a report via a callback
 *
 * @since 3.0
 *
 * @param string $report_id The report ID.
 */
function edd_output_report_callback( $report_id = '' ) {

	// Maybe use the already loaded report.
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
		$tiles  = array(
			'overview_sales'               => 'EDD\Reports\Endpoints\Tiles\Sales',
			'overview_earnings'            => 'EDD\Reports\Endpoints\Tiles\Earnings',
			'overview_average_order_value' => 'EDD\Reports\Endpoints\Tiles\Average',
			'new_customer_growth'          => 'EDD\Reports\Endpoints\Tiles\CustomerGrowth',
			'overview_refunded_amount'     => 'EDD\Reports\Endpoints\Tiles\RefundedAmount',
		);
		$charts = array(
			'overview_earnings_chart' => 'EDD\Reports\Endpoints\Charts\Earnings',
			'overview_sales_chart'    => 'EDD\Reports\Endpoints\Charts\Sales',
		);
		$reports->add_report(
			'overview',
			array(
				'label'     => __( 'Overview', 'easy-digital-downloads' ),
				'icon'      => 'dashboard',
				'priority'  => 5,
				'endpoints' => array(
					'tiles'  => array_keys( $tiles ),
					'charts' => array_keys( $charts ),
				),
				'filters'   => array(
					'dates',
					'taxes',
					'currencies',
				),
			)
		);

		$endpoints = array_merge( $tiles, $charts );

		foreach ( $endpoints as $class ) {
			new $class( $reports );
		}
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
		$download_data = Reports\get_filter_value( 'products' );
		$download_data = ! empty( $download_data ) && 'all' !== Reports\get_filter_value( 'products' )
			? edd_parse_product_dropdown_value( Reports\get_filter_value( 'products' ) )
			: false;

		// Mock downloads and prices in case they cannot be found later.
		$download = edd_get_download();
		$prices   = array();

		$tiles = array_filter(
			array(
				'most_valuable_download'          => 'EDD\Reports\Endpoints\Tiles\MostValuableDownload',
				'average_download_sales_earnings' => 'EDD\Reports\Endpoints\Tiles\AverageSalesEarnings',
				'download_sales_earnings'         => 'EDD\Reports\Endpoints\Tiles\DownloadSalesEarnings',
			),
			function ( $endpoint ) use ( $download_data ) {
				switch ( $endpoint ) {
					case 'download_sales_earnings':
						return false !== $download_data;
					break;
					default:
						return false === $download_data;
				}
			},
			ARRAY_FILTER_USE_KEY
		);

		$charts = array_filter(
			array(
				'download_sales_by_variations'    => 'EDD\Reports\Endpoints\Pies\DownloadSalesByVariations',
				'download_earnings_by_variations' => 'EDD\Reports\Endpoints\Pies\DownloadEarningsByVariations',
				'download_earnings_chart'         => 'EDD\Reports\Endpoints\Charts\DownloadEarnings',
				'download_sales_chart'            => 'EDD\Reports\Endpoints\Charts\DownloadSales',
			),
			function ( $endpoint ) use ( $download_data, $download ) {
				if ( $download_data ) {
					$download = edd_get_download( absint( $download_data['download_id'] ) );
				}
				switch ( $endpoint ) {
					case 'download_sales_by_variations':
					case 'download_earnings_by_variations':
						return (
						$download &&
						null === $download_data['price_id'] &&
						true === $download->has_variable_prices()
						);

						break;

					default:
						return false !== $download_data;
				}
			},
			ARRAY_FILTER_USE_KEY
		);

		$tables = array_filter(
			array(
				'top_selling_downloads' => 'EDD\Reports\Endpoints\Tables\TopSellingDownloads',
			),
			function ( $endpoint ) use ( $download_data ) {
				return false === $download_data;
			},
			ARRAY_FILTER_USE_KEY
		);

		$reports->add_report(
			'downloads',
			array(
				'label'     => edd_get_label_plural(),
				'priority'  => 10,
				'icon'      => 'download',
				'endpoints' => array(
					'tiles'  => array_keys( $tiles ),
					'charts' => array_keys( $charts ),
					'tables' => array_keys( $tables ),
				),
				'filters'   => array( 'dates', 'products', 'taxes' ),
			)
		);

		$endpoints = array_merge( $tiles, $charts, $tables );

		foreach ( $endpoints as $class ) {
			new $class( $reports );
		}
	} catch ( \EDD_Exception $exception ) {
		edd_debug_log_exception( $exception );
	}
}
add_action( 'edd_reports_init', 'edd_register_downloads_report' );

/**
 * Register downloads taxonomy report and endpoints.
 *
 * @since 3.2.7
 * @param \EDD\Reports\Data\Report_Registry $reports Report registry.
 */
function edd_register_downloads_taxonomy_report( $reports ) {
	try {
		$tables = array(
			'earnings_by_taxonomy' => 'EDD\Reports\Endpoints\Tables\EarningsByTaxonomy',
		);

		$reports->add_report(
			'downloads_taxonomy',
			array(
				/* translators: %s: Downloads label */
				'label'     => sprintf( __( '%s Terms', 'easy-digital-downloads' ), edd_get_label_singular() ),
				'priority'  => 11,
				'icon'      => 'category',
				'endpoints' => array(
					'tables' => array_keys( $tables ),
				),
				'filters'   => array( 'dates' ),
			)
		);

		foreach ( $tables as $class ) {
			new $class( $reports );
		}
	} catch ( \EDD_Exception $exception ) {
		edd_debug_log_exception( $exception );
	}
}
add_action( 'edd_reports_init', 'edd_register_downloads_taxonomy_report' );

/**
 * Register refunds report and endpoints.
 *
 * @since 3.0
 *
 * @param \EDD\Reports\Data\Report_Registry $reports Report registry.
 */
function edd_register_refunds_report( $reports ) {
	try {

		$tiles  = array(
			'refund_count'                    => 'EDD\Reports\Endpoints\Tiles\RefundCount',
			'fully_refunded_order_count'      => 'EDD\Reports\Endpoints\Tiles\FullyRefundedOrderCount',
			'fully_refunded_order_item_count' => 'EDD\Reports\Endpoints\Tiles\FullyRefundedOrderItemCount',
			'refund_amount'                   => 'EDD\Reports\Endpoints\Tiles\RefundAmount',
			'average_refund_amount'           => 'EDD\Reports\Endpoints\Tiles\AverageRefundAmount',
			'average_time_to_refund'          => 'EDD\Reports\Endpoints\Tiles\AverageRefundTime',
			'refund_rate'                     => 'EDD\Reports\Endpoints\Tiles\RefundRate',
		);
		$charts = array(
			'refunds_earnings_chart' => 'EDD\Reports\Endpoints\Charts\RefundRevenue',
			'refunds_orders_chart'   => 'EDD\Reports\Endpoints\Charts\Refunds',
		);

		$reports->add_report(
			'refunds',
			array(
				'label'     => __( 'Refunds', 'easy-digital-downloads' ),
				'icon'      => 'image-rotate',
				'priority'  => 15,
				'endpoints' => array(
					'tiles'  => array_keys( $tiles ),
					'charts' => array_keys( $charts ),
				),
				'filters'   => array(
					'dates',
					'taxes',
					'currencies',
				),
			)
		);

		$endpoints = array_merge( $tiles, $charts );
		foreach ( $endpoints as $class ) {
			new $class( $reports );
		}
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
		$order_status  = Reports\get_filter_value( 'order_statuses' );

		$hbh   = Reports\get_dates_filter_hour_by_hour();
		$label = $options[ $dates['range'] ] . ( $hbh ? ' (' . edd_get_timezone_abbr() . ')' : '' );

		$tiles = array(
			'sales_per_gateway'         => 'EDD\Reports\Endpoints\Tiles\GatewaySales',
			'earnings_per_gateway'      => 'EDD\Reports\Endpoints\Tiles\GatewayEarnings',
			'refunds_per_gateway'       => 'EDD\Reports\Endpoints\Tiles\GatewayRefunds',
			'average_value_per_gateway' => 'EDD\Reports\Endpoints\Tiles\GatewayAverage',
		);

		$tables = array_filter(
			array(
				'gateway_stats' => 'EDD\Reports\Endpoints\Tables\GatewayStats',
			),
			function ( $endpoint ) use ( $gateway ) {
				return empty( $gateway ) || 'all' === $gateway;
			},
			ARRAY_FILTER_USE_KEY
		);

		if ( 'stripe' === $gateway && 'payment-elements' === edds_get_elements_mode() ) {
			$tables['stripe_payment_methods'] = 'EDD\Reports\Endpoints\Tables\StripePaymentMethods';
		}

		$charts = array_filter(
			array(
				'gateway_sales_breakdown'    => 'EDD\Reports\Endpoints\Charts\GatewaySalesBreakdown',
				'gateway_earnings_breakdown' => 'EDD\Reports\Endpoints\Charts\GatewayEarningsBreakdown',
				'gateway_earnings_chart'     => 'EDD\Reports\Endpoints\Charts\GatewayEarnings',
				'gateway_sales_chart'        => 'EDD\Reports\Endpoints\Charts\GatewaySales',
				'gateway_sales_breakdown'    => 'EDD\Reports\Endpoints\Pies\GatewaySales',
				'gateway_earnings_breakdown' => 'EDD\Reports\Endpoints\Pies\GatewayEarnings',
			),
			function ( $endpoint ) use ( $gateway ) {
				switch ( $endpoint ) {
					case 'gateway_earnings_chart':
					case 'gateway_sales_chart':
						return ! empty( $gateway ) && 'all' !== $gateway;
					break;
					default:
						return ( empty( $gateway ) || 'all' === $gateway );
				}
			},
			ARRAY_FILTER_USE_KEY
		);

		$reports->add_report(
			'gateways',
			array(
				'label'     => __( 'Payment Gateways', 'easy-digital-downloads' ),
				'icon'      => 'image-filter',
				'priority'  => 20,
				'endpoints' => array(
					'tiles'  => array_keys( $tiles ),
					'tables' => array_keys( $tables ),
					'charts' => array_keys( $charts ),
				),
				'filters'   => array( 'dates', 'gateways', 'order_statuses', 'taxes', 'currencies' ),
			)
		);

		$endpoints = array_merge( $tiles, $charts, $tables );
		foreach ( $endpoints as $class ) {
			new $class( $reports );
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

		$country = Reports\get_filter_value( 'countries' );

		$tiles = array_filter(
			array(
				'total_tax_collected'              => 'EDD\Reports\Endpoints\Tiles\TaxCollected',
				'total_tax_collected_for_location' => 'EDD\Reports\Endpoints\Tiles\TaxCollectedLocation',
			),
			function ( $tile ) use ( $country ) {
				switch ( $tile ) {
					case 'total_tax_collected_for_location':
						return ! in_array( $country, array( 'all', false, '' ), true );
						break;

					default:
						return true;
				}
			},
			ARRAY_FILTER_USE_KEY
		);

		$tables = array_filter(
			array(
				'tax_collected_by_location' => 'EDD\Reports\Endpoints\Tables\TaxCollectedByLocation',
			),
			function () {
				$download_data = Reports\get_filter_value( 'products' );

				return empty( $download_data ) || 'all' === $download_data;
			},
			ARRAY_FILTER_USE_KEY
		);

		$reports->add_report(
			'taxes',
			array(
				'label'     => __( 'Taxes', 'easy-digital-downloads' ),
				'priority'  => 25,
				'icon'      => 'editor-paste-text',
				'endpoints' => array(
					'tiles'  => array_keys( $tiles ),
					'tables' => array_keys( $tables ),
				),
				'filters'   => array( 'dates', 'products', 'countries', 'regions', 'currencies' ),
			)
		);

		$endpoints = array_merge( $tiles, $tables );
		foreach ( $endpoints as $class ) {
			new $class( $reports );
		}
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

		$tiles = array_filter(
			array(
				'number_of_file_downloads'            => 'EDD\Reports\Endpoints\Tiles\FileDownloads',
				'average_file_downloads_per_customer' => 'EDD\Reports\Endpoints\Tiles\FileDownloadsCustomer',
				'most_downloaded_product'             => 'EDD\Reports\Endpoints\Tiles\FileDownloadsProduct',
				'average_file_downloads_per_order'    => 'EDD\Reports\Endpoints\Tiles\FileDownloadsOrder',
			),
			function ( $endpoint ) use ( $download_data ) {
				switch ( $endpoint ) {
					case 'average_file_downloads_per_customer':
					case 'most_downloaded_product':
					case 'average_file_downloads_per_order':
						return false === $download_data;
					break;
					default:
						return true;
				}
			},
			ARRAY_FILTER_USE_KEY
		);

		$tables = array_filter(
			array(
				'top_five_most_downloaded_products' => 'EDD\Reports\Endpoints\Tables\TopFiveMostDownloaded',
			),
			function ( $endpoint ) use ( $download_data ) {
				return false === $download_data;
			},
			ARRAY_FILTER_USE_KEY
		);

		$charts = array(
			'file_downloads_chart' => 'EDD\Reports\Endpoints\Charts\FileDownloads',
		);

		$reports->add_report(
			'file_downloads',
			array(
				'label'     => __( 'File Downloads', 'easy-digital-downloads' ),
				'icon'      => 'download',
				'priority'  => 30,
				'endpoints' => array(
					'tiles'  => array_keys( $tiles ),
					'tables' => array_keys( $tables ),
					'charts' => array_keys( $charts ),
				),
				'filters'   => array( 'dates', 'products' ),
			)
		);

		$endpoints = array_merge( $tiles, $tables, $charts );
		foreach ( $endpoints as $class ) {
			new $class( $reports );
		}
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

		$tiles = array_filter(
			array(
				'number_of_discounts_used'   => 'EDD\Reports\Endpoints\Tiles\DiscountsUsed',
				'ratio_of_discounted_orders' => 'EDD\Reports\Endpoints\Tiles\DiscountRatio',
				'customer_savings'           => 'EDD\Reports\Endpoints\Tiles\DiscountSavings',
				'average_discount_amount'    => 'EDD\Reports\Endpoints\Tiles\DiscountAverage',
				'most_popular_discount'      => 'EDD\Reports\Endpoints\Tiles\DiscountPopular',
				'discount_usage_count'       => 'EDD\Reports\Endpoints\Tiles\DiscountUsage',
			),
			function ( $tile ) use ( $discount ) {
				switch ( $tile ) {
					case 'discount_usage_count':
						return 0 !== $discount;
					break;
					default:
						return 0 === $discount;
				}
			},
			ARRAY_FILTER_USE_KEY
		);

		$tables = array_filter(
			array(
				'top_five_discounts' => 'EDD\Reports\Endpoints\Tables\TopFiveDiscounts',
			),
			function ( $table ) use ( $discount ) {
				return 0 === $discount;
			},
			ARRAY_FILTER_USE_KEY
		);

		$charts = array(
			'discount_usage_chart' => 'EDD\Reports\Endpoints\Charts\Discounts',
		);

		$reports->add_report(
			'discounts',
			array(
				'label'     => __( 'Discounts', 'easy-digital-downloads' ),
				'icon'      => 'tickets-alt',
				'priority'  => 35,
				'endpoints' => array(
					'tiles'  => array_keys( $tiles ),
					'tables' => array_keys( $tables ),
					'charts' => array_keys( $charts ),
				),
				'filters'   => array( 'dates', 'discounts' ),
			)
		);

		$endpoints = array_merge( $tiles, $tables, $charts );
		foreach ( $endpoints as $class ) {
			new $class( $reports );
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
		$tiles  = array(
			'new_customer_growth'                   => 'EDD\Reports\Endpoints\Tiles\CustomerGrowth',
			'average_revenue_per_customer'          => 'EDD\Reports\Endpoints\Tiles\CustomerAverage',
			'average_number_of_orders_per_customer' => 'EDD\Reports\Endpoints\Tiles\CustomerAverageOrders',
		);
		$charts = array(
			'new_customers' => 'EDD\Reports\Endpoints\Charts\Customers',
		);
		$tables = array(
			'top_five_customers'      => 'EDD\Reports\Endpoints\Tables\TopFiveCustomers',
			'most_valuable_customers' => 'EDD\Reports\Endpoints\Tables\MostValuableCustomers',
		);

		$reports->add_report(
			'customers',
			array(
				'label'     => __( 'Customers', 'easy-digital-downloads' ),
				'icon'      => 'groups',
				'priority'  => 40,
				'endpoints' => array(
					'tiles'  => array_keys( $tiles ),
					'tables' => array_keys( $tables ),
					'charts' => array_keys( $charts ),
				),
			)
		);
		$endpoints = array_merge( $tiles, $charts, $tables );

		foreach ( $endpoints as $class ) {
			new $class( $reports );
		}
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
		$reports->add_report(
			'export',
			array(
				'label'            => __( 'Export', 'easy-digital-downloads' ),
				'icon'             => 'migrate',
				'priority'         => 1000,
				'capability'       => 'export_shop_reports',
				'display_callback' => 'display_export_report',
				'filters'          => false,
			)
		);
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
	wp_enqueue_script( 'edd-admin-tools-export' );
	?>
	<div id="edd-dashboard-widgets-wrap">
		<div class="metabox-holder">
			<div id="post-body">
				<div id="post-body-content" class="edd-reports-export edd-admin--has-grid">
					<?php
					\EDD\Admin\Exports\Loader::bootstrap();
					do_action( 'edd_reports_tab_export_content_top' );
					do_action( 'edd_reports_tab_export_content_bottom' );
					?>
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
 * @param bool $include_taxes If the estimated earnings should include taxes.
 * @return array
 */
function edd_estimated_monthly_stats( $include_taxes = true ) {

	$estimated = get_transient( 'edd_estimated_monthly_stats' . $include_taxes );

	if ( false === $estimated ) {

		$estimated = array(
			'earnings' => 0,
			'sales'    => 0,
		);

		$stats = new EDD_Payment_Stats();

		$to_date_earnings = $stats->get_earnings( 0, 'this_month', null, $include_taxes );
		$to_date_sales    = $stats->get_sales( 0, 'this_month' );

		$current_day   = date( 'd', current_time( 'timestamp' ) );
		$current_month = date( 'n', current_time( 'timestamp' ) );
		$current_year  = date( 'Y', current_time( 'timestamp' ) );
		$days_in_month = cal_days_in_month( CAL_GREGORIAN, $current_month, $current_year );

		$estimated['earnings'] = ( $to_date_earnings / $current_day ) * $days_in_month;
		$estimated['sales']    = ( $to_date_sales / $current_day ) * $days_in_month;

		// Cache for one day.
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
	wp_nonce_field( 'meta-box-order', 'meta-box-order-nonce', false );
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

/**
 * Will return HTML for relative date ranges dropdown.
 *
 * @since 3.1
 */
function edd_reports_get_relative_date_ranges() {
	require_once EDD_PLUGIN_DIR . 'includes/reports/reports-functions.php';
	$range = isset( $_REQUEST['range'] )
		? sanitize_text_field( $_REQUEST['range'] )
		: '';

	$relative_range = isset( $_REQUEST['relative_range'] )
		? sanitize_text_field( $_REQUEST['relative_range'] )
		: '';

	if ( empty( $range ) || empty( $relative_range ) ) {
		return;
	}

	echo Reports\display_relative_dates_dropdown_options( $range, $relative_range );

	edd_die();
}
add_action( 'wp_ajax_edd_reports_get_relative_date_ranges', 'edd_reports_get_relative_date_ranges' );

/**
 * Sets a transient to show the earnings by taxonomy report.
 *
 * @since 3.2.7
 * @return void
 */
function edd_show_earnings_by_taxonomy_report( $data ) {
	if ( empty( $data['edd-action'] ) || 'show_downloads_taxonomy_report' !== $data['edd-action'] ) {
		return;
	}
	if ( ! current_user_can( 'view_shop_reports' ) ) {
		return;
	}
	set_transient( 'edd_earnings_by_taxonomy_show_report', true, 7 * DAY_IN_SECONDS );
	edd_redirect(
		edd_get_admin_url(
			array(
				'page' => 'edd-reports',
				'view' => 'downloads_taxonomy',
			)
		)
	);
}
add_action( 'edd_show_downloads_taxonomy_report', 'edd_show_earnings_by_taxonomy_report' );
