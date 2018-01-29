<?php
/**
 * Admin Reports Page
 *
 * @package     EDD
 * @subpackage  Admin/Reports
 * @copyright   Copyright (c) 2015, Pippin Williamson
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       1.0
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
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
	$current_page = admin_url( 'edit.php?post_type=download&page=edd-reports' );

	wp_enqueue_script( 'postbox' );

	// Start the Reports API.
	new EDD\Admin\Reports();

	$active_tab = edd_get_active_report_tab();
	?>
	<style>
		#edd-item-wrapper {
			max-width: 100%;
		}
		#edd-item-tab-wrapper {
			width: 15%;
		}
		.edd-item-has-tabs #edd-item-card-wrapper {
			width: 85%;
		}
		#edd-item-card-wrapper h3 {
			margin-top: 0;
		}
	</style>
	<div class="wrap">
		<h2><?php _e( 'Easy Digital Downloads Reports', 'easy-digital-downloads' ); ?></h2>

		<div id="edd-item-wrapper" class="edd-item-has-tabs edd-clearfix">
			<div id="edd-item-tab-wrapper" class="report-tab-wrapper">
				<ul id="edd-item-tab-wrapper-list" class="report-tab-wrapper-list">
					<?php
					$tabs = edd_get_report_tabs();

					if ( current_user_can( 'export_shop_reports' ) ) :
						$tabs['export'] = __( 'Export', 'easy-digital-downloads' );
					endif;

					foreach ( $tabs as $slug => $label ) :
						$active = $slug === $active_tab ? true : false;
						$class  = $active ? 'active' : 'inactive';
						?>

						<li class="<?php echo sanitize_html_class( $class ); ?>">

							<?php
							$link = add_query_arg( array(
								'tab'              => $slug,
								'settings-updated' => false ),
							$current_page );
							?>

							<?php if ( ! $active ) : ?>
								<a href="<?php echo esc_url( $link ); ?>">
							<?php endif; ?>

								<span class="edd-item-tab-label-wrap">
									<span class="edd-item-tab-label"><?php echo esc_attr( $label ); ?></span>
								</span>

							<?php if ( ! $active ) : ?>
								</a>
							<?php endif; ?>
						</li>

					<?php endforeach; ?>
				</ul>
			</div>

			<div id="edd-item-card-wrapper" class="edd-report-card-wrapper" style="float: left">
				<?php do_action( 'edd_reports_tabs' ); ?>

				<?php
				$report = edd_reports_get_report( $active_tab );

				if ( ! is_wp_error( $report ) ) :

					do_action( 'edd_reports_page_top' );

					if ( $report->has_endpoints( 'tiles' ) ) : ?>

						<div id="edd-reports-tiles-wrap">
							<div id="dashboard-widgets" class="metabox-holder">

								<div class="postbox-container">
									<?php do_meta_boxes( 'download_page_edd-reports', 'primary', null ); ?>
								</div>

								<div class="postbox-container">
									<?php do_meta_boxes( 'download_page_edd-reports', 'secondary', null ); ?>
								</div>

								<div class="postbox-container">
									<?php do_meta_boxes( 'download_page_edd-reports', 'tertiary', null ); ?>
								</div>

							</div>
						</div>
					<?php endif; // Has endpoints.
				endif; // WP_Error.

				if ( has_action( "edd_reports_tab_{$active_tab}" ) ) {
					do_action( "edd_reports_tab_{$active_tab}" );
				} elseif ( has_action( "edd_reports_view_{$active_tab}" ) ) {
					do_action( "edd_reports_view_{$active_tab}" );
				}

				do_action( 'edd_reports_page_bottom' );
				?>
			</div>
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
 * @param \EDD\Admin\Reports\Data\Reports_Registry $reports Reports registry.
 */
function edd_register_core_reports( $reports ) {

	try {

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

		$reports->add_report( 'earnings', array(
			'label'     => __( 'Earnings', 'easy-digital-downloads' ),
			'priority'  => 5,
			'endpoints' => array(
				'tiles' => array( 'test_tile', 'another_test_tile' )
			),
		) );

		$reports->add_report( 'categories', array(
			'label'     => __( 'Earnings by Category', 'easy-digital-downloads' ),
			'priority'  => 10,
			'endpoints' => array(
				'tiles' => array( 'test_tile' )
			),
		) );

		$reports->add_report( 'downloads', array(
			'label'     => edd_get_label_plural(),
			'priority'  => 15,
			'endpoints' => array(
				'tiles' => array( 'test_tile' )
			),
		) );

		$reports->add_report( 'gateways', array(
			'label'     => __( 'Payment Methods', 'easy-digital-downloads' ),
			'priority'  => 20,
			'endpoints' => array(
				'tiles' => array( 'test_tile' )
			),
		) );

		$reports->add_report( 'taxes', array(
			'label'     => __( 'Taxes', 'easy-digital-downloads' ),
			'priority'  => 25,
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
 * Retrieves the list of slug/label report tab pairs.
 *
 * @since 3.0
 *
 * @return array List of report tabs, otherwise an empty array.
 */
function edd_get_report_tabs() {
	/** @var \EDD\Admin\Reports\Data\Reports_Registry|\WP_Error $registry */
	$registry = EDD()->utils->get_registry( 'reports' );

	if ( is_wp_error( $registry ) ) {
		return array();
	} else {
		$registered_reports = $registry->get_reports( 'priority' );
	}

	$reports = array();

	foreach ( $registered_reports as $report_id => $attributes ) {
		$reports[ $report_id ] = $attributes['label'];
	}

	if ( has_filter( 'edd_report_views' ) ) {
		/**
		 * Filters legacy 'Reports' tab views.
		 *
		 * @since 1.4
		 * @deprecated 3.0 Use {@see 'edd_get_report_tabs'}
		 * @see 'edd_get_report_tabs'
		 *
		 * @param array $views 'Reports' tab views.
		 */
		$legacy_views = apply_filters_deprecated( 'edd_report_views', array( array() ), '3.0', 'edd_report_tabs' );

		$reports = array_merge( $reports, $legacy_views );
	}

	/**
	 * Filters the list of report tab slug/label pairs.
	 *
	 * @since 3.0
	 *
	 * @param array $reports List of slug/label pairs as representative of report tabs.
	 */
	return apply_filters( 'edd_get_report_tabs', $reports );
}

/**
 * Retrieves the slug for the active report tab.
 *
 * @since 3.0
 *
 * @return string The active report tab, or the first tab if the 'tab' var is not defined.
 */
function edd_get_active_report_tab() {

	$tabs = edd_get_report_tabs();

	// If not set, default the active tab to the first one.
	return isset( $_GET['tab'] ) ? sanitize_key( $_GET['tab'] ) : key( $tabs );
}

/**
 * Renders the Reports Downloads Table
 *
 * @since 1.3
 * @uses EDD_Download_Reports_Table::prepare_items()
 * @uses EDD_Download_Reports_Table::display()
 * @return void
 */
function edd_reports_downloads_table() {

	if( ! current_user_can( 'view_shop_reports' ) ) {
		return;
	}

	if( isset( $_GET['download-id'] ) )
		return;

	include( dirname( __FILE__ ) . '/class-download-reports-table.php' );

	$downloads_table = new EDD_Download_Reports_Table();
	$downloads_table->prepare_items();
	$downloads_table->display();
}
add_action( 'edd_reports_view_downloads', 'edd_reports_downloads_table' );

/**
 * Renders the detailed report for a specific product
 *
 * @since 1.9
 * @return void
 */
function edd_reports_download_details() {

	if( ! current_user_can( 'view_shop_reports' ) ) {
		return;
	}

	if( ! isset( $_GET['download-id'] ) )
		return;

	edd_reports_graph_of_download( absint( $_GET['download-id'] ) );
}
add_action( 'edd_reports_view_downloads', 'edd_reports_download_details' );


/**
 * Renders the Gateways Table
 *
 * @since 1.3
 * @uses EDD_Gateawy_Reports_Table::prepare_items()
 * @uses EDD_Gateawy_Reports_Table::display()
 * @return void
 */
function edd_reports_gateways_table() {

	if( ! current_user_can( 'view_shop_reports' ) ) {
		return;
	}

	include( dirname( __FILE__ ) . '/class-gateways-reports-table.php' );

	$downloads_table = new EDD_Gateawy_Reports_Table();
	$downloads_table->prepare_items();
	$downloads_table->display();
}
add_action( 'edd_reports_view_gateways', 'edd_reports_gateways_table' );


/**
 * Renders the Reports Earnings Graphs
 *
 * @since 1.3
 * @return void
 */
function edd_reports_earnings() {

	if( ! current_user_can( 'view_shop_reports' ) ) {
		return;
	}

	edd_reports_graph();
}
add_action( 'edd_reports_view_earnings', 'edd_reports_earnings' );


/**
 * Renders the Reports Earnings By Category Table & Graphs
 *
 * @since  2.4
 */
function edd_reports_categories() {
	if( ! current_user_can( 'view_shop_reports' ) ) {
		return;
	}

	include( dirname( __FILE__ ) . '/class-categories-reports-table.php' );
	?>
			<div class="inside">
				<?php

				$categories_table = new EDD_Categories_Reports_Table();
				$categories_table->prepare_items();
				$categories_table->display();
				?>

				<?php echo $categories_table->load_scripts(); ?>

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

	if( ! current_user_can( 'view_shop_reports' ) ) {
		return;
	}

	$year = isset( $_GET['year'] ) ? absint( $_GET['year'] ) : date( 'Y' );
	?>
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

	if( ! current_user_can( 'view_shop_reports' ) ) {
		return;
	}
	?>
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
							<p><?php _e( 'Download a CSV of download products.', 'easy-digital-downloads' ); ?></p>
							<form id="edd-export-file-downloads" class="edd-export-form edd-import-export-form" method="post">
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
		$estimated['sales']    = ( $to_date_sales / $current_day ) * $days_in_month;

		// Cache for one day
		set_transient( 'edd_estimated_monthly_stats' . $include_taxes, $estimated, 86400 );
	}

	return maybe_unserialize( $estimated );
}

/**
 * Retrieves the list of supported endpoint view types and their attributes.
 *
 * @since 3.0
 *
 * @return array List of supported endpoint types.
 */
function edd_reports_get_endpoint_views() {
	return array(
		'tile' => array(
			'group'       => 'tiles',
			'handler'     => 'EDD\Admin\Reports\Data\Tile_Endpoint',
			'fields'      => array(
				'data_callback'    => '',
				'display_callback' => 'edd_reports_display_tile',
				'display_args'     => array(
					'type'             => '' ,
					'context'          => 'primary',
					'comparison_label' => __( 'All time', 'easy-digital-downloads' ),
				),
			),
		),
		'chart' => array(
			'group' => 'charts',
		),
		'table' => array(
			'group' => 'tables',
		),
		'graph' => array(
			'group' => 'graphs',
		),
	);
}

/**
 * Registers a new data endpoint to the master registry.
 *
 * @since 3.0
 *
 * @see \EDD\Admin\Reports\Data\Endpoint_Registry::register_endpoint()
 *
 * @param string $endpoint_id Reports data endpoint ID.
 * @param array  $attributes  {
 *     Endpoint attributes. All arguments are required unless otherwise noted.
 *
 *     @type string $label    Endpoint label.
 *     @type int    $priority Optional. Priority by which to retrieve the endpoint. Default 10.
 *     @type array  $views {
 *         Array of view handlers by type.
 *
 *         @type array $view_type {
 *             View type slug, with array beneath it.
 *
 *             @type callable $data_callback    Callback used to retrieve data for the view.
 *             @type callable $display_callback Callback used to render the view.
 *             @type array    $display_args     Optional. Array of arguments to pass to the
 *                                              display_callback (if any). Default empty array.
 *         }
 *     }
 * }
 * @return bool True if the endpoint was successfully registered, otherwise false.
 */
function edd_reports_register_endpoint( $endpoint_id, $attributes ) {
	/** @var \EDD\Admin\Reports\Data\Endpoint_Registry|\WP_Error $registry */
	$registry = EDD()->utils->get_registry( 'reports:endpoints' );

	if ( is_wp_error( $registry ) ) {
		return false;
	}

	try {

		$added = $registry->register_endpoint( $endpoint_id, $attributes );

	} catch ( \EDD_Exception $exception ) {

		$added = false;

	}

	return $added;
}

/**
 * Retrieves and builds an endpoint object.
 *
 * @since 3.0
 *
 * @see \EDD\Admin\Reports\Data\Endpoint_Registry::build_endpoint()
 *
 * @param string $endpoint_id Endpoint ID.
 * @param string $view_type   View type to use when building the object.
 * @return \EDD\Admin\Reports\Data\Endpoint|\WP_Error Endpoint object on success, otherwise a WP_Error object.
 */
function edd_reports_get_endpoint( $endpoint_id, $view_type ) {
	/** @var \EDD\Admin\Reports\Data\Endpoint_Registry|\WP_Error $registry */
	$registry = EDD()->utils->get_registry( 'reports:endpoints' );

	if ( is_wp_error( $registry ) ) {
		return $registry;
	}

	return $registry->build_endpoint( $endpoint_id, $view_type );
}

/**
 * Retrieves and builds a report object.
 *
 * @since 3.0
 *
 * @see \EDD\Admin\Reports\Data\Reports_Registry::build_report()
 *
 * @param string $report_id       Report ID.
 * @param bool   $build_endpoints Optional. Whether to build the endpoints (includes registering
 *                                any endpoint dependencies, such as registering meta boxes).
 *                                Default true.
 * @return \EDD\Admin\Reports\Data\Report|\WP_Error Report object on success, otherwise a WP_Error object.
 */
function edd_reports_get_report( $report_id, $build_endpoints = true ) {
	/** @var \EDD\Admin\Reports\Data\Reports_Registry|\WP_Error $registry */
	$registry = EDD()->utils->get_registry( 'reports' );

	if ( is_wp_error( $registry ) ) {
		return $registry;
	}

	return $registry->build_report( $report_id, $build_endpoints );
}

/**
 * Parses views for an incoming endpoint.
 *
 * @since 3.0
 *
 * @see edd_reports_get_endpoint_views()
 *
 * @param array  $views View slugs and attributes as dictated by edd_reports_get_endpoint_views().
 * @return array (Maybe) adjusted views slugs and attributes array.
 */
function edd_reports_parse_endpoint_views( $views ) {
	$valid_views = edd_reports_get_endpoint_views();

	foreach ( $views as $view => $attributes ) {
		if ( ! empty( $valid_views[ $view ]['fields'] ) ) {
			$fields = $valid_views[ $view ]['fields'];

			// Merge the incoming args with the field defaults.
			$view_args = wp_parse_args( $attributes, $fields );

			// Overwrite the view attributes, keeping only the valid fields.
			$views[ $view ] = array_intersect_key( $view_args, $fields );

			if ( $views[ $view ]['display_callback'] === $fields['display_callback'] ) {
				$views[ $view ]['display_args'] = wp_parse_args( $views[ $view ]['display_args'], $fields['display_args'] );
			}
		}
	}

	return $views;
}

/**
 * Determines whether an endpoint view is valid.
 *
 * @since 3.0
 *
 * @param string $view Endpoint view slug.
 * @return bool True if the view is valid, otherwise false.
 */
function edd_reports_is_view_valid( $view ) {
	return array_key_exists( $view, edd_reports_get_endpoint_views() );
}

/**
 * Displays the default content for a tile endpoint.
 *
 * @since 3.0
 *
 * @param \EDD\Admin\Reports\Data\Report $report Report object the tile endpoint is being rendered in.
 *                                               Not always set.
 * @param array                          $args   Tile display arguments.
 * @return void Meta box display callbacks only echo output.
 */
function edd_reports_display_tile( $object, $tile ) {
	if ( ! isset( $tile['args'] ) ) {
		return;
	}

	if ( empty( $tile['args']['data'] ) ) {
		echo '<span class="tile-no-data tile-value">' . __( 'No data for the current date range.', 'easy-digital-downloads' ) . '</span>';
	} else {
		switch( $tile['args']['display_args']['type'] ) {
			case 'number':
				echo '<span class="tile-number tile-value">' . edd_format_amount( $tile['args']['data'] ) . '</span>';
				break;

			case 'split-number':
				printf( '<span class="tile-amount tile-value">%1$d / %2$d</span>',
					edd_format_amount( $tile['args']['data']['first_value'] ),
					edd_format_amount( $tile['args']['data']['second_value'] )
				);
				break;

			case 'split-amount':
				printf( '<span class="tile-amount tile-value">%1$d / %2$d</span>',
					edd_currency_filter( edd_format_amount( $tile['args']['data']['first_value'] ) ),
					edd_currency_filter( edd_format_amount( $tile['args']['data']['second_value'] ) )
				);
				break;

			case 'amount':
				echo '<span class="tile-amount tile-value">' . edd_currency_filter( edd_format_amount( $tile['args']['data'] ) ) . '</span>';
				break;

			case 'url':
				echo '<span class="tile-url tile-value">' . esc_url( $tile['args']['data'] ) . '</span>';
				break;

			default:
				echo '<span class="tile-value">' . esc_html( $tile['args']['data'] ) . '</span>';
				break;
		}
	}

	if ( ! empty( $tile['args']['display_args']['comparison_label'] ) ) {
		echo '<span class="tile-compare">' . $tile['args']['display_args']['comparison_label'] . '</span>';
	}
}

/**
 * Retrieves the name of the handler class for a given endpoint view.
 *
 * @since 3.0
 *
 * @param string $view Endpoint view.
 * @return string Handler class name if set and the view exists, otherwise an empty string.
 */
function edd_reports_get_endpoint_handler( $view ) {
	$handler = '';

	$views = edd_reports_get_endpoint_views();

	if ( isset( $views[ $view ]['handler'] ) ) {
		$handler = $views[ $view ]['handler'];
	}

	return $handler;
}

/**
 * Adds postbox nonces, which are used to save the position of tile endpoint meta boxes.
 *
 * @since 3.0
 */
function edd_add_screen_options_nonces() {
	wp_nonce_field( 'closedpostboxes', 'closedpostboxesnonce' , false );
	wp_nonce_field( 'meta-box-order', 'meta-box-order-nonce' , false );
}
add_action( 'admin_footer', 'edd_add_screen_options_nonces' );

/**
 * Renders the Logs tab in the Reports screen.
 *
 * @since 1.3
 * @deprecated 3.0 Use edd_tools_tab_logs() instead.
 * @see edd_tools_tab_logs()
 * @return void
 */
function edd_reports_tab_logs() {
	_edd_deprecated_function( __FUNCTION__, '3.0', 'edd_tools_tab_logs' );

	if ( ! function_exists( 'edd_tools_tab_logs' ) ) {
		require_once EDD_PLUGIN_DIR . 'includes/admin/tools/logs.php';
	}

	edd_tools_tab_logs();
}

/**
 * Defines views for the legacy 'Reports' tab.
 *
 * @since 1.4
 * @deprecated 3.0 Use edd_get_report_tabs()
 * @see edd_get_report_tabs
 *
 * @return array $views Report Views
 */
function edd_reports_default_views() {
	_edd_deprecated_function( __FUNCTION__, '3.0', 'edd_get_report_tabs' );

	return edd_get_report_tabs();
}

/**
 * Renders the Reports page
 *
 * @since 1.3
 * @deprecated 3.0 Unused.
 */
function edd_reports_tab_reports() {

	_edd_deprecated_function( __FUNCTION__, '3.0' );

	if( ! current_user_can( 'view_shop_reports' ) ) {
		wp_die( __( 'You do not have permission to access this report', 'easy-digital-downloads' ), __( 'Error', 'easy-digital-downloads' ), array( 'response' => 403 ) );
	}

	$current_view = 'earnings';
	$views        = edd_reports_default_views();

	if ( isset( $_GET['view'] ) && array_key_exists( $_GET['view'], $views ) )
		$current_view = $_GET['view'];

	do_action( 'edd_reports_view_' . $current_view );

}

/**
 * Default Report Views
 *
 * Checks the $_GET['view'] parameter to ensure it exists within the default allowed views.
 *
 * @param string $default Default view to use.
 *
 * @since 1.9.6
 * @deprecated 3.0 Unused.
 *
 * @return string $view Report View
 */
function edd_get_reporting_view( $default = 'earnings' ) {

	_edd_deprecated_function( __FUNCTION__, '3.0' );

	if ( ! isset( $_GET['view'] ) || ! in_array( $_GET['view'], array_keys( edd_reports_default_views() ) ) ) {
		$view = $default;
	} else {
		$view = $_GET['view'];
	}

	/**
	 * Legacy: filters the current reporting view (now implemented solely via the 'tab' var).
	 *
	 * @since 1.9.6
	 * @deprecated 3.0 Unused.
	 *
	 * @param string $view View slug.
	 */
	return apply_filters_deprecated( 'edd_get_reporting_view', array( $view ), '3.0' );
}

/**
 * Renders the Reports Page Views Drop Downs
 *
 * @since 1.3
 * @deprecated 3.0 Unused.
 *
 * @return void
 */
function edd_report_views() {

	_edd_deprecated_function( __FUNCTION__, '3.0' );

	if( ! current_user_can( 'view_shop_reports' ) ) {
		return;
	}

	$views        = edd_reports_default_views();
	$current_view = isset( $_GET['view'] ) ? $_GET['view'] : 'earnings';
	?>
	<form id="edd-reports-filter" method="get">
		<select id="edd-reports-view" name="view">
			<option value="-1"><?php _e( 'Report Type', 'easy-digital-downloads' ); ?></option>
			<?php foreach ( $views as $view_id => $label ) : ?>
				<option value="<?php echo esc_attr( $view_id ); ?>" <?php selected( $view_id, $current_view ); ?>><?php echo $label; ?></option>
			<?php endforeach; ?>
		</select>

		<?php do_action( 'edd_report_view_actions' ); ?>

		<input type="hidden" name="post_type" value="download"/>
		<input type="hidden" name="page" value="edd-reports"/>
		<?php submit_button( __( 'Show', 'easy-digital-downloads' ), 'secondary', 'submit', false ); ?>
	</form>
	<?php
	do_action( 'edd_report_view_actions_after' );
}
