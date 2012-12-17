<?php

function edd_logs_view_sales() {

	include( dirname( __FILE__ ) . '/class-sales-logs-list-table.php' );

	$logs_table = new EDD_Sales_Log_Table();
	$logs_table->prepare_items();
	$logs_table->display();

}
add_action( 'edd_logs_view_sales', 'edd_logs_view_sales' );


function edd_logs_view_file_downloads() {

	include( dirname( __FILE__ ) . '/class-file-downloads-logs-list-table.php' );

	$logs_table = new EDD_File_Downloads_Log_Table();
	$logs_table->prepare_items();
	$logs_table->display();

}
add_action( 'edd_logs_view_file_downloads', 'edd_logs_view_file_downloads' );


function edd_logs_view_gateway_errors() {

	include( dirname( __FILE__ ) . '/class-gateway-error-logs-list-table.php' );

	$logs_table = new EDD_Gateway_Error_Log_Table();
	$logs_table->prepare_items();
	$logs_table->display();

}
add_action( 'edd_logs_view_gateway_errors', 'edd_logs_view_gateway_errors' );

/**
 * Default Log Views
 *
 * @access      public
 * @since       1.4
 * @return      void
 */
function edd_log_default_views() {
	$views = array(
		'sales' 			=> __( 'Sales', 'edd' ),
		'file_downloads'	=> __( 'File Downloads', 'edd' ),
		'gateway_errors'	=> __( 'Payment Errors', 'edd' )
	);

	$views = apply_filters( 'edd_log_views', $views );

	return $views;
}

/**
 * Renders the Reports page views drop down
 *
 * @access      public
 * @since       1.3
 * @return      void
*/

function edd_log_views() {
	$views        = edd_log_default_views();
	$current_view = isset( $_GET['view'] ) ? $_GET['view'] : 'sales';

	?>
	<form id="edd-sales-filter" method="get">
		<select id="edd-logs-view" name="view">
			<option value="-1"><?php _e( 'Log Type', 'edd' ); ?></option>
			<?php foreach( $views as $view_id => $label ): ?>
				<option value="<?php echo esc_attr( $view_id ); ?>" <?php selected( $view_id, $current_view ); ?>><?php echo $label; ?></option>
			<?php endforeach; ?>
		</select>
		
		<?php do_action( 'edd_log_view_actions' ); ?>
		<input type="submit" class="button-secondary" value="<?php _e( 'Apply', 'edd' ); ?>"/>

		<input type="hidden" name="post_type" value="download"/>
		<input type="hidden" name="page" value="edd-reports"/>
		<input type="hidden" name="tab" value="logs"/>
	</form>
	<?php
}