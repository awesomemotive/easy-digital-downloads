<?php
/**
 * Admin Reports Page
 *
 * @package     Easy Digital Downloads
 * @subpackage  Admin Reports Page
 * @copyright   Copyright (c) 2012, Pippin Williamson
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       1.0
*/

// Exit if accessed directly
if ( !defined( 'ABSPATH' ) ) exit;

/**
 * Reports Page
 *
 * Renders the reports page contents.
 *
 * @access      private
 * @since       1.0
 * @return      void
*/

function edd_reports_page() {
	global $edd_options;

	$current_page = admin_url( 'edit.php?post_type=download&page=edd-reports' );

	$active_tab = isset( $_GET[ 'tab' ] ) ? $_GET[ 'tab' ] : 'reports';

	?>
	<div class="wrap">

		<h2 class="nav-tab-wrapper">
			<a href="<?php echo add_query_arg( array( 'tab' => 'reports', 'settings-updated' => false ) ); ?>" class="nav-tab <?php echo $active_tab == 'reports' ? 'nav-tab-active' : ''; ?>">
				<?php _e( 'Reports', 'edd' ); ?>
			</a>
			<a href="<?php echo add_query_arg( array( 'tab' => 'export', 'settings-updated' => false ) ); ?>" class="nav-tab <?php echo $active_tab == 'export' ? 'nav-tab-active' : ''; ?>">
				<?php _e( 'Export', 'edd' ); ?>
			</a>
			<?php do_action( 'edd_reports_tabs' ); ?>
		</h2>

		<?php

		do_action( 'edd_reports_page_top' );

		if( $active_tab == 'reports' ) {
			do_action( 'edd_reports_tab_reports' );
		} elseif ( $active_tab == 'export' ) {
			do_action( 'edd_reports_tab_export' );
		}

		do_action( 'edd_reports_page_bottom' );

		?>

	</div><!--end wrap-->
	<?php
}


/**
 * Renders the Reports page
 *
 * @access      public
 * @since       1.3
 * @return      void
*/

function edd_reports_tab_reports() {

	// current view
	$current_view = isset( $_GET['view'] ) ? $_GET['view'] : 'earnings';

	do_action( 'edd_reports_view_' . $current_view );

}
add_action( 'edd_reports_tab_reports', 'edd_reports_tab_reports' );


/**
 * Renders the Reports page views drop down
 *
 * @access      public
 * @since       1.3
 * @return      void
*/

function edd_report_views() {
	// default reporting views
	$views = array(
		'earnings'	=> __( 'Earnings', 'edd' ),
		'downloads' => edd_get_label_plural(),
		'customers'	=> __( 'Customers', 'edd' ),
		'taxes'		=> __( 'Taxes', 'edd' )
	);

	$views = apply_filters( 'edd_report_views', $views );

	// current view
	$current_view = isset( $_GET['view'] ) ? $_GET['view'] : 'earnings';

	?>
	<form id="edd-reports-filter" method="get">
		<div class="tablenav top">
			<div class="alignleft actions">
				<span><?php _e( 'Reporting Views', 'edd' ); ?></span>
				<input type="hidden" name="post_type" value="download"/>
				<input type="hidden" name="page" value="edd-reports"/>
				<select id="edd-reports-view" name="view">
					<?php foreach( $views as $view_id => $label ): ?>
						<option value="<?php echo esc_attr( $view_id ); ?>" <?php selected( $view_id, $current_view ); ?>><?php echo $label; ?></option>
					<?php endforeach; ?>
				</select>
				<?php do_action( 'edd_report_view_actions' ); ?>
				<input type="submit" class="button-secondary" value="<?php _e( 'Apply', 'edd' ); ?>"/>
			</div>
		</div>
	</form>
	<?php
}


/**
 * Renders the Reports Downloads table
 *
 * @access      public
 * @since       1.3
 * @return      void
*/

function edd_reports_downloads_table() {
	include( dirname( __FILE__ ) . '/class-download-reports-table.php' );

	$downloads_table = new EDD_Download_Reports_Table();
	$downloads_table->prepare_items();
	$downloads_table->display();
}
add_action( 'edd_reports_view_downloads', 'edd_reports_downloads_table' );


/**
 * Renders the Reports Customers table
 *
 * @access      public
 * @since       1.3
 * @return      void
*/

function edd_reports_customers_table() {
	include( dirname( __FILE__ ) . '/class-customer-reports-table.php' );

	$downloads_table = new EDD_Customer_Reports_Table();
	$downloads_table->prepare_items();
	$downloads_table->display();
}
add_action( 'edd_reports_view_customers', 'edd_reports_customers_table' );


/**
 * Renders the Reports earnings graphs
 *
 * @access      public
 * @since       1.3
 * @return      void
*/

function edd_reports_earnings() {
	edd_report_views();
	edd_reports_graph();
}
add_action( 'edd_reports_view_earnings', 'edd_reports_earnings' );


/**
 * Renders the tax reports
 *
 * @access      public
 * @since       1.3.3
 * @return      void
*/

function edd_reports_taxes() {

	edd_report_views();

	$year = isset( $_GET['year'] ) ? absint( $_GET['year'] ) : date( 'Y' );

	?>
	<div class="metabox-holder">
		<div id="post-body">
			<div id="post-body-content">

				<div class="postbox">
					<h3><span><?php _e('Tax Report', 'edd'); ?></span></h3>
					<div class="inside">

						<p><?php _e( 'This report shows the total amount collected in sales tax for the given year.', 'edd' ); ?></p>


						<form method="get" action="<?php echo admin_url( 'edit.php' ); ?>">
							<span><?php echo $year; ?></span>: <strong><?php edd_sales_tax_for_year( $year ); ?></strong>&nbsp;&mdash;&nbsp;
							<select name="year">
								<?php for( $i = 2009; $i <= date( 'Y' ); $i++ ) : ?>
								<option value="<?php echo $i; ?>"<?php selected( $year, $i ); ?>><?php echo $i; ?></option>
								<?php endfor; ?>
							</select>
							<input type="hidden" name="view" value="taxes" />
							<input type="hidden" name="post_type" value="download" />
							<input type="hidden" name="page" value="edd-reports" />
							<input type="submit" class="button-secondary" value="<?php _e( 'Submit', 'edd' ); ?>"/>
						</form>

					</div><!--end inside-->
				</div><!--end postbox-->

			</div><!--endpost-body-content-->
		</div><!--end post-body-->
	</div><!--end metabox-holder-->
	<?php
}
add_action( 'edd_reports_view_taxes', 'edd_reports_taxes' );


/**
 * Renders the Reports page Export tab
 *
 * @access      public
 * @since       1.3
 * @return      void
*/

function edd_reports_tab_export() {
	?>
	<div class="metabox-holder">
		<div id="post-body">
			<div id="post-body-content">

				<div class="postbox">
					<h3><span><?php _e( 'Export PDF of Sales and Earnings', 'edd' ); ?></span></h3>
					<div class="inside">
						<p><?php _e( 'Download a PDF of Sales and Earnings reports for all products for the current year.', 'edd' ); ?> <?php _e( 'Date range reports will be coming soon.', 'edd' ); ?></p>
						<p>
							<a class="button" href="<?php echo wp_nonce_url( add_query_arg( array( 'edd-action' => 'generate_pdf' ) ), 'edd_generate_pdf' ); ?>">
								<?php _e( 'Generate PDF', 'edd' ); ?>
							</a>
						</p>
					</div><!--end inside-->
				</div><!--end postbox-->

				<div class="postbox">
					<h3><span><?php _e('Export Payment History', 'edd'); ?></span></h3>
					<div class="inside">
						<p><?php _e( 'Download a CSV of all payments recorded.', 'edd' ); ?></p>
						<p>
							<a class="button" href="<?php echo wp_nonce_url( add_query_arg( array( 'edd-action' => 'payment_export' ) ), 'edd_payments_export' ); ?>">
								<?php _e( 'Generate CSV', 'edd' ) ; ?>
							</a>
						</p>
					</div><!--end inside-->
				</div><!--end postbox-->

				<div class="postbox">
					<h3><span><?php _e('Export Customers in CSV', 'edd'); ?></span></h3>
					<div class="inside">
						<p><?php _e( 'Download a CSV of all customer emails. This export includes purchase numbers and amounts for each customer.', 'edd' ); ?></p>
						<p>
							<a class="button" href="<?php echo wp_nonce_url( add_query_arg( array( 'edd-action' => 'email_export' ) ), 'edd_email_export' ); ?>">
								<?php _e( 'Generate CSV', 'edd' ) ; ?>
							</a>
						</p>
					</div><!--end inside-->
				</div><!--end postbox-->

				<div class="postbox">
					<h3><span><?php _e('Export Download History in CSV', 'edd'); ?></span></h3>
					<div class="inside">
						<p><?php _e( 'Download a CSV of all file downloads for the current month.', 'edd' ); ?></p>
						<p>
							<a class="button" href="<?php echo wp_nonce_url( add_query_arg( array( 'edd-action' => 'downloads_history_export' ) ), 'edd_export_all_downloads_history' ); ?>">
								<?php _e( 'Generate CSV', 'edd' ) ; ?>
							</a>
						</p>
					</div><!--end inside-->
				</div><!--end postbox-->

			</div><!--endpost-body-content-->
		</div><!--end post-body-->
	</div><!--end metabox-holder-->
	<?php
}
add_action( 'edd_reports_tab_export', 'edd_reports_tab_export' );