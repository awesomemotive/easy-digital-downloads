<?php
/**
 * Admin Reports Page
 *
 * @package     EDD
 * @subpackage  Admin/Reports
 * @copyright   Copyright (c) 2013, Pippin Williamson
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
 * @global $edd_options Array of all the EDD Options
 * @return void
*/
function edd_reports_page() {
	global $edd_options;

	$current_page = admin_url( 'edit.php?post_type=download&page=edd-reports' );
	$active_tab = isset( $_GET[ 'tab' ] ) ? $_GET[ 'tab' ] : 'reports';
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
		'customers'	=> __( 'Customers', 'edd' ),
		'gateways'  => __( 'Payment Methods', 'edd' ),
		'taxes'		=> __( 'Taxes', 'edd' )
	);

	$views = apply_filters( 'edd_report_views', $views );

	return $views;
}

/**
 * Renders the Reports page
 *
 * @since 1.3
 * @return void
 */
function edd_reports_tab_reports() {
	$current_view = 'earnings';
	$views        = edd_reports_default_views();

	if ( isset( $_GET[ 'view' ] ) && array_key_exists( $_GET[ 'view' ], $views ) )
		$current_view = $_GET[ 'view' ];

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
	$views        = edd_reports_default_views();
	$current_view = isset( $_GET[ 'view' ] ) ? $_GET[ 'view' ] : 'earnings';
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
	include( dirname( __FILE__ ) . '/class-download-reports-table.php' );

	$downloads_table = new EDD_Download_Reports_Table();
	$downloads_table->prepare_items();
	$downloads_table->display();
}
add_action( 'edd_reports_view_downloads', 'edd_reports_downloads_table' );

/**
 * Renders the Reports Customers Table
 *
 * @since 1.3
 * @uses EDD_Customer_Reports_Table::prepare_items()
 * @uses EDD_Customer_Reports_Table::display()
 * @return void
 */
function edd_reports_customers_table() {
	include( dirname( __FILE__ ) . '/class-customer-reports-table.php' );

	$downloads_table = new EDD_Customer_Reports_Table();
	$downloads_table->prepare_items();
	$downloads_table->display();
}
add_action( 'edd_reports_view_customers', 'edd_reports_customers_table' );


/**
 * Renders the Gateways Table
 *
 * @since 1.3
 * @uses EDD_Gateawy_Reports_Table::prepare_items()
 * @uses EDD_Gateawy_Reports_Table::display()
 * @return void
 */
function edd_reports_gateways_table() {
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
	?>
	<div class="metabox-holder">
		<div id="post-body">
			<div id="post-body-content">

				<?php do_action( 'edd_reports_tab_export_content_top' ); ?>

				<div class="postbox">
					<h3><span><?php _e( 'Export PDF of Sales and Earnings', 'edd' ); ?></span></h3>
					<div class="inside">
						<p><?php _e( 'Download a PDF of Sales and Earnings reports for all products for the current year.', 'edd' ); ?> <?php _e( 'Date range reports will be coming soon.', 'edd' ); ?></p>
						<p><a class="button" href="<?php echo wp_nonce_url( add_query_arg( array( 'edd-action' => 'generate_pdf' ) ), 'edd_generate_pdf' ); ?>"><?php _e( 'Generate PDF', 'edd' ); ?></a></p>
					</div><!-- .inside -->
				</div><!-- .postbox -->

				<div class="postbox">
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

				<div class="postbox">
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

				<div class="postbox">
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
	require( EDD_PLUGIN_DIR . 'includes/admin/reporting/logs.php' );

	$current_view = 'file_downloads';
	$log_views    = edd_log_default_views();

	if ( isset( $_GET[ 'view' ] ) && array_key_exists( $_GET[ 'view' ], $log_views ) )
		$current_view = $_GET[ 'view' ];

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

		$products = get_posts( array( 'post_type' => 'download', 'posts_per_page' => -1, 'fields' => 'ids' ) );
		if ( $products ) {
			foreach ( $products as $download ) {
				$estimated['earnings'] += edd_get_average_monthly_download_earnings( $download );
				$estimated['sales']    += number_format( edd_get_average_monthly_download_sales( $download ), 0 );
			}
		}

		// Cache for one day
		set_transient( 'edd_estimated_monthly_stats', serialize( $estimated ), 86400 );
	}

	return maybe_unserialize( $estimated );
}