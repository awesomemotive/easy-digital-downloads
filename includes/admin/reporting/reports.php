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
		<h2 class="nav-tab-wrapper">
			<a href="<?php echo add_query_arg( array( 'tab' => 'reports', 'settings-updated' => false ), $current_page ); ?>" class="nav-tab <?php echo $active_tab == 'reports' ? 'nav-tab-active' : ''; ?>"><?php _e( 'Reports', 'edd' ); ?></a>
			<?php if ( current_user_can( 'export_shop_reports' ) ) { ?>
				<a href="<?php echo add_query_arg( array( 'tab' => 'export', 'settings-updated' => false ), $current_page ); ?>" class="nav-tab <?php echo $active_tab == 'export' ? 'nav-tab-active' : ''; ?>"><?php _e( 'Export', 'edd' ); ?></a>
			<?php } ?>
			<a href="<?php echo add_query_arg( array( 'tab' => 'logs', 'settings-updated' => false ), $current_page ); ?>" class="nav-tab <?php echo $active_tab == 'logs' ? 'nav-tab-active' : ''; ?>"><?php _e( 'Logs', 'edd' ); ?></a>
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
		'earnings'	=> __( 'Earnings', 'edd' ),
		'downloads' => edd_get_label_plural(),
		'gateways'  => __( 'Payment Methods', 'edd' ),
		'taxes'		=> __( 'Taxes', 'edd' )
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
		wp_die( __( 'You do not have permission to access this report', 'edd'  ), __( 'Error', 'edd' ), array( 'response' => 403 ) );
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
			<option value="-1"><?php _e( 'Report Type', 'edd' ); ?></option>
			<?php foreach ( $views as $view_id => $label ) : ?>
				<option value="<?php echo esc_attr( $view_id ); ?>" <?php selected( $view_id, $current_view ); ?>><?php echo $label; ?></option>
			<?php endforeach; ?>
		</select>

		<?php do_action( 'edd_report_view_actions' ); ?>

		<input type="hidden" name="post_type" value="download"/>
		<input type="hidden" name="page" value="edd-reports"/>
		<?php submit_button( __( 'Show', 'edd' ), 'secondary', 'submit', false ); ?>
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
			<button onclick="history.go(-1);" class="button-secondary"><?php _e( 'Go Back', 'edd' ); ?></button>
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
			<h3><span><?php _e('Tax Report', 'edd'); ?></span></h3>
			<div class="inside">
				<p><?php _e( 'This report shows the total amount collected in sales tax for the given year.', 'edd' ); ?></p>
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
					<?php submit_button( __( 'Submit', 'edd' ), 'secondary', 'submit', false ); ?>
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

					<div class="postbox edd-export-pdf-sales-earnings">
						<h3><span><?php _e( 'Export PDF of Sales and Earnings', 'edd' ); ?></span></h3>
						<div class="inside">
							<p><?php _e( 'Download a PDF of Sales and Earnings reports for all products for the current year.', 'edd' ); ?> <?php _e( 'Date range reports will be coming soon.', 'edd' ); ?></p>
							<p><a class="button" href="<?php echo wp_nonce_url( add_query_arg( array( 'edd-action' => 'generate_pdf' ) ), 'edd_generate_pdf' ); ?>"><?php _e( 'Generate PDF', 'edd' ); ?></a></p>
						</div><!-- .inside -->
					</div><!-- .postbox -->

					<div class="postbox edd-export-sales-earnings">
						<h3><span><?php _e( 'Export Earnings and Sales Stats', 'edd' ); ?></span></h3>
						<div class="inside">
							<p><?php _e( 'Download a CSV of earnings and sales over time.', 'edd' ); ?></p>
							<p>
								<form method="post">
									<?php echo EDD()->html->year_dropdown( 'start_year' ); ?>
									<?php echo EDD()->html->month_dropdown( 'start_month' ); ?>
									<?php echo _x( 'to', 'Date one to date two', 'edd' ); ?>
									<?php echo EDD()->html->year_dropdown( 'end_year' ); ?>
									<?php echo EDD()->html->month_dropdown( 'end_month' ); ?>
									<input type="hidden" name="edd-action" value="earnings_export"/>
									<input type="submit" value="<?php _e( 'Generate CSV', 'edd' ); ?>" class="button-secondary"/>
								</form>
							</p>
						</div><!-- .inside -->
					</div><!-- .postbox -->

					<div class="postbox edd-export-payment-history">
						<h3><span><?php _e('Export Payment History', 'edd'); ?></span></h3>
						<div class="inside">
							<p><?php _e( 'Download a CSV of all payments recorded.', 'edd' ); ?></p>
							<p>
								<form method="post">
									<?php echo EDD()->html->year_dropdown(); ?>
									<?php echo EDD()->html->month_dropdown(); ?>
									<select name="edd_export_payment_status">
										<option value="0"><?php _e( 'All Statuses', 'edd' ); ?></option>
										<?php
										$statuses = edd_get_payment_statuses();
										foreach( $statuses as $status => $label ) {
											echo '<option value="' . $status . '">' . $label . '</option>';
										}
										?>
									</select>
									<input type="hidden" name="edd-action" value="payment_export"/>
									<input type="submit" value="<?php _e( 'Generate CSV', 'edd' ); ?>" class="button-secondary"/>
								</form>
							</p>
						</div><!-- .inside -->
					</div><!-- .postbox -->

					<div class="postbox edd-export-customers">
						<h3><span><?php _e('Export Customers in CSV', 'edd'); ?></span></h3>
						<div class="inside">
							<p><?php _e( 'Download a CSV of all customer emails. Optionally export only customers that have purchased a particular product. Note, if you have a large number of customers, exporting the purchase stats may fail.', 'edd' ); ?></p>
							<p>
								<form method="post" id="edd_customer_export">
									<select name="edd_export_download" id="edd_customer_export_download">
										<option value="0"><?php printf( __( 'All %s', 'edd' ), edd_get_label_plural() ); ?></option>
										<?php
										$downloads = get_posts( array( 'post_type' => 'download', 'posts_per_page' => -1 ) );
										if( $downloads ) {
											foreach( $downloads as $download ) {
												echo '<option value="' . $download->ID . '">' . get_the_title( $download->ID ) . '</option>';
											}
										}
										?>
									</select>
									<select name="edd_export_option" id="edd_customer_export_option">
										<option value="emails"><?php _e( 'Emails', 'edd' ); ?></option>
										<option value="emails_and_names"><?php _e( 'Emails and Names', 'edd' ); ?></option>
										<option value="full"><?php _e( 'Emails, Names, and Purchase Stats', 'edd' ); ?></option>
									</select>
									<input type="hidden" name="edd-action" value="email_export"/>
									<input type="submit" value="<?php _e( 'Generate CSV', 'edd' ); ?>" class="button-secondary"/>
								</form>
							</p>
						</div><!-- .inside -->
					</div><!-- .postbox -->

					<div class="postbox edd-export-download-history">
						<h3><span><?php _e('Export Download History in CSV', 'edd'); ?></span></h3>
						<div class="inside">
							<p><?php _e( 'Download a CSV of all file downloads for a specific month and year.', 'edd' ); ?></p>
							<p>
								<form method="post">
									<?php echo EDD()->html->year_dropdown(); ?>
									<?php echo EDD()->html->month_dropdown(); ?>
									<input type="hidden" name="edd-action" value="downloads_history_export"/>
									<input type="submit" value="<?php _e( 'Generate CSV', 'edd' ); ?>" class="button-secondary"/>
								</form>
							</p>
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
 * @return array
 */
function edd_estimated_monthly_stats() {

	$estimated = get_transient( 'edd_estimated_monthly_stats' );

	if ( false === $estimated ) {

		$estimated = array(
			'earnings' => 0,
			'sales'    => 0
		);

		$stats = new EDD_Payment_Stats;

		$to_date_earnings = $stats->get_earnings( 0, 'this_month' );
		$to_date_sales    = $stats->get_sales( 0, 'this_month' );

		$current_day      = date( 'd', current_time( 'timestamp' ) );
		$current_month    = date( 'n', current_time( 'timestamp' ) );
		$current_year     = date( 'Y', current_time( 'timestamp' ) );
		$days_in_month    = cal_days_in_month( CAL_GREGORIAN, $current_month, $current_year );

		$estimated['earnings'] = ( $to_date_earnings / $current_day ) * $days_in_month;
		$estimated['sales']    = ( $to_date_sales / $current_day ) * $days_in_month;

		// Cache for one day
		set_transient( 'edd_estimated_monthly_stats', $estimated, 86400 );
	}

	return maybe_unserialize( $estimated );
}
