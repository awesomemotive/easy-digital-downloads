<?php
/**
 * Logs UI
 *
 * @package     EDD
 * @subpackage  Admin/Reports
 * @copyright   Copyright (c) 2015, Pippin Williamson
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       1.4
*/

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Sales Log View
 *
 * @since 1.4
 * @uses EDD_Sales_Log_Table::prepare_items()
 * @uses EDD_Sales_Log_Table::display()
 * @return void
 */
function edd_logs_view_sales() {

	if( ! current_user_can( 'view_shop_reports' ) ) {
		return;
	}

	include( dirname( __FILE__ ) . '/class-sales-logs-list-table.php' );

	$logs_table = new EDD_Sales_Log_Table();
	$logs_table->prepare_items();
	$logs_table->display();

}
add_action( 'edd_logs_view_sales', 'edd_logs_view_sales' );

/**
 * File Download Logs
 *
 * @since 1.4
 * @uses EDD_File_Downloads_Log_Table::prepare_items()
 * @uses EDD_File_Downloads_Log_Table::search_box()
 * @uses EDD_File_Downloads_Log_Table::display()
 * @return void
 */
function edd_logs_view_file_downloads() {

	if( ! current_user_can( 'view_shop_reports' ) ) {
		return;
	}

	include( dirname( __FILE__ ) . '/class-file-downloads-logs-list-table.php' );

	$logs_table = new EDD_File_Downloads_Log_Table();
	$logs_table->prepare_items();
	?>
	<div class="wrap">
		<?php do_action( 'edd_logs_file_downloads_top' ); ?>
		<form id="edd-logs-filter" method="get" action="<?php echo admin_url( 'edit.php?post_type=download&page=edd-reports&tab=logs' ); ?>">
			<?php
			$logs_table->search_box( __( 'Search', 'easy-digital-downloads' ), 'edd-payments' );
			$logs_table->display();
			?>
			<input type="hidden" name="post_type" value="download" />
			<input type="hidden" name="page" value="edd-reports" />
			<input type="hidden" name="tab" value="logs" />
		</form>
		<?php do_action( 'edd_logs_file_downloads_bottom' ); ?>
	</div>
<?php
}
add_action( 'edd_logs_view_file_downloads', 'edd_logs_view_file_downloads' );

/**
 * Gateway Error Logs
 *
 * @since 1.4
 * @uses EDD_File_Downloads_Log_Table::prepare_items()
 * @uses EDD_File_Downloads_Log_Table::display()
 * @return void
 */
function edd_logs_view_gateway_errors() {

	if( ! current_user_can( 'view_shop_reports' ) ) {
		return;
	}

	include( dirname( __FILE__ ) . '/class-gateway-error-logs-list-table.php' );

	$logs_table = new EDD_Gateway_Error_Log_Table();
	$logs_table->prepare_items();
	$logs_table->display();
}
add_action( 'edd_logs_view_gateway_errors', 'edd_logs_view_gateway_errors' );

/**
 * API Request Logs
 *
 * @since 1.5
 * @uses EDD_API_Request_Log_Table::prepare_items()
 * @uses EDD_API_Request_Log_Table::search_box()
 * @uses EDD_API_Request_Log_Table::display()
 * @return void
 */

function edd_logs_view_api_requests() {

	if( ! current_user_can( 'view_shop_reports' ) ) {
		return;
	}
	
	include( dirname( __FILE__ ) . '/class-api-requests-logs-list-table.php' );

	$logs_table = new EDD_API_Request_Log_Table();
	$logs_table->prepare_items();
	?>
	<div class="wrap">
		<?php do_action( 'edd_logs_api_requests_top' ); ?>
		<form id="edd-logs-filter" method="get" action="<?php echo admin_url( 'edit.php?post_type=download&page=edd-reports&tab=logs' ); ?>">
			<?php
			$logs_table->search_box( __( 'Search', 'easy-digital-downloads' ), 'edd-api-requests' );
			$logs_table->display();
			?>
			<input type="hidden" name="post_type" value="download" />
			<input type="hidden" name="page" value="edd-reports" />
			<input type="hidden" name="tab" value="logs" />
		</form>
		<?php do_action( 'edd_logs_api_requests_bottom' ); ?>
	</div>
<?php
}
add_action( 'edd_logs_view_api_requests', 'edd_logs_view_api_requests' );


/**
 * Default Log Views
 *
 * @since 1.4
 * @return array $views Log Views
 */
function edd_log_default_views() {
	$views = array(
		'file_downloads'  => __( 'File Downloads', 'easy-digital-downloads' ),
		'sales' 		  => __( 'Sales', 'easy-digital-downloads' ),
		'gateway_errors'  => __( 'Payment Errors', 'easy-digital-downloads' ),
		'api_requests'    => __( 'API Requests', 'easy-digital-downloads' )
	);

	$views = apply_filters( 'edd_log_views', $views );

	return $views;
}

/**
 * Renders the Reports page views drop down
 *
 * @since 1.3
 * @return void
*/
function edd_log_views() {
	$views        = edd_log_default_views();
	$current_view = isset( $_GET['view'] ) && array_key_exists( $_GET['view'], edd_log_default_views() ) ? sanitize_text_field( $_GET['view'] ) : 'file_downloads';
	?>
	<form id="edd-logs-filter" method="get" action="edit.php">
		<select id="edd-logs-view" name="view">
			<option value="-1"><?php _e( 'Log Type', 'easy-digital-downloads' ); ?></option>
			<?php foreach ( $views as $view_id => $label ): ?>
				<option value="<?php echo esc_attr( $view_id ); ?>" <?php selected( $view_id, $current_view ); ?>><?php echo $label; ?></option>
			<?php endforeach; ?>
		</select>

		<?php do_action( 'edd_log_view_actions' ); ?>

		<input type="hidden" name="post_type" value="download"/>
		<input type="hidden" name="page" value="edd-reports"/>
		<input type="hidden" name="tab" value="logs"/>

		<?php submit_button( __( 'Apply', 'easy-digital-downloads' ), 'secondary', 'submit', false ); ?>
	</form>
	<?php
}