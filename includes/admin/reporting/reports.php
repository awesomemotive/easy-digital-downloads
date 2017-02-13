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
if ( ! defined( 'ABSPATH' ) ) exit;

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
	$active_tab = isset( $_GET['tab'] ) ? $_GET['tab'] : 'reports';
	?>
	<div class="wrap">
		<h2><?php _e( 'Easy Digital Downloads Reports', 'easy-digital-downloads' ); ?></h2>
		<h2 class="nav-tab-wrapper">
			<a href="<?php echo add_query_arg( array( 'tab' => 'reports', 'settings-updated' => false ), $current_page ); ?>" class="nav-tab <?php echo $active_tab == 'reports' ? 'nav-tab-active' : ''; ?>"><?php _e( 'Reports', 'easy-digital-downloads' ); ?></a>
			<?php if ( current_user_can( 'export_shop_reports' ) ) { ?>
				<a href="<?php echo add_query_arg( array( 'tab' => 'export', 'settings-updated' => false ), $current_page ); ?>" class="nav-tab <?php echo $active_tab == 'export' ? 'nav-tab-active' : ''; ?>"><?php _e( 'Export', 'easy-digital-downloads' ); ?></a>
			<?php } ?>
			<a href="<?php echo add_query_arg( array( 'tab' => 'logs', 'settings-updated' => false ), $current_page ); ?>" class="nav-tab <?php echo $active_tab == 'logs' ? 'nav-tab-active' : ''; ?>"><?php _e( 'Logs', 'easy-digital-downloads' ); ?></a>
			<?php do_action( 'edd_reports_tabs' ); ?>
		</h2>

		<?php
		do_action( 'edd_reports_page_top' );
		do_action( 'edd_reports_tab_' . $active_tab );
		do_action( 'edd_reports_page_bottom' );
		?>
	</div><!-- .wrap -->
	<?php
}

/**
 * Default Report Views
 *
 * @since 1.4
 * @return array $views Report Views
 */
function edd_reports_default_views() {
	$views = array(
		'earnings'   => __( 'Earnings', 'easy-digital-downloads' ),
		'categories' => __( 'Earnings by Category', 'easy-digital-downloads' ),
		'downloads'  => edd_get_label_plural(),
		'gateways'   => __( 'Payment Methods', 'easy-digital-downloads' ),
		'taxes'      => __( 'Taxes', 'easy-digital-downloads' ),
	);

	$views = apply_filters( 'edd_report_views', $views );

	return $views;
}

/**
 * Default Report Views
 *
 * Checks the $_GET['view'] parameter to ensure it exists within the default allowed views.
 *
 * @param string $default Default view to use.
 *
 * @since 1.9.6
 * @return string $view Report View
 *
 */
function edd_get_reporting_view( $default = 'earnings' ) {

	if ( ! isset( $_GET['view'] ) || ! in_array( $_GET['view'], array_keys( edd_reports_default_views() ) ) ) {
		$view = $default;
	} else {
		$view = $_GET['view'];
	}

	return apply_filters( 'edd_get_reporting_view', $view );
}

/**
 * Renders the Reports page
 *
 * @since 1.3
 * @return void
 */
function edd_reports_tab_reports() {

	if( ! current_user_can( 'view_shop_reports' ) ) {
		wp_die( __( 'You do not have permission to access this report', 'easy-digital-downloads' ), __( 'Error', 'easy-digital-downloads' ), array( 'response' => 403 ) );
	}

	$current_view = 'earnings';
	$views        = edd_reports_default_views();

	if ( isset( $_GET['view'] ) && array_key_exists( $_GET['view'], $views ) )
		$current_view = $_GET['view'];

	do_action( 'edd_reports_view_' . $current_view );

}
add_action( 'edd_reports_tab_reports', 'edd_reports_tab_reports' );

/**
 * Renders the Reports Page Views Drop Downs
 *
 * @since 1.3
 * @return void
 */
function edd_report_views() {

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
?>
	<div class="tablenav top">
		<div class="actions bulkactions">
			<div class="alignleft">
				<?php edd_report_views(); ?>
			</div>&nbsp;
			<button onclick="history.go(-1);" class="button-secondary"><?php _e( 'Go Back', 'easy-digital-downloads' ); ?></button>
		</div>
	</div>
<?php
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
	?>
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
	<div class="tablenav top">
		<div class="alignleft actions"><?php edd_report_views(); ?></div>
	</div>

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
 * Renders the Reports page
 *
 * @since 1.3
 * @return void
 */
function edd_reports_tab_logs() {

	if( ! current_user_can( 'view_shop_reports' ) ) {
		return;
	}

	require( EDD_PLUGIN_DIR . 'includes/admin/reporting/logs.php' );

	$current_view = 'file_downloads';
	$log_views    = edd_log_default_views();

	if ( isset( $_GET['view'] ) && array_key_exists( $_GET['view'], $log_views ) )
		$current_view = $_GET['view'];

	do_action( 'edd_logs_view_' . $current_view );
}
add_action( 'edd_reports_tab_logs', 'edd_reports_tab_logs' );

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
