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