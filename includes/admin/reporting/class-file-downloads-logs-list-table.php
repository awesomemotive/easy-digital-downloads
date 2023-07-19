<?php
/**
 * File Downloads Log List Table.
 *
 * @package     EDD
 * @subpackage  Admin/Reports
 * @copyright   Copyright (c) 2018, Easy Digital Downloads, LLC
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       1.4.4
 * @since       3.0 Updated to use the custom tables.
 */

use EDD\Logs\File_Download_Log;

// Exit if accessed directly
defined( 'ABSPATH' ) || exit;

/**
 * EDD_File_Downloads_Log_Table Class
 *
 * @since 1.4
 * @since 3.0 Updated to use the custom tables and new query classes.
 */
class EDD_File_Downloads_Log_Table extends EDD_Base_Log_List_Table {

	/**
	 * Log type
	 *
	 * @var string
	 */
	protected $log_type = 'file_downloads';

	/**
	 * Are we searching for files?
	 *
	 * @var bool
	 * @since 1.4
	 */
	public $file_search = false;

	/**
	 * Store each unique product's files so they only need to be queried once
	 *
	 * @var array
	 * @since 1.9
	 */
	private $queried_files = array();

	/**
	 * Get things started
	 *
	 * @since 1.4
	 * @see WP_List_Table::__construct()
	 */
	public function __construct() {
		parent::__construct();
	}

	/**
	 * This function renders most of the columns in the list table.
	 *
	 * @since 1.4
	 *
	 * @param array  $item Contains all the data of the log item.
	 * @param string $column_name The name of the column.
	 *
	 * @return string Column Name
	 */
	public function column_default( $item, $column_name ) {
		$base_url = remove_query_arg( 'paged' );
		switch ( $column_name ) {
			case 'download' :
				$download     = new EDD_Download( $item[ $column_name ] );
				$column_value = ! empty( $item['price_id'] )
					? edd_get_download_name( $download->ID, $item['price_id'] )
					: edd_get_download_name( $download->ID );

				return '<a href="' . esc_url( add_query_arg( 'download', absint( $download->ID ), $base_url ) ) . '" >' . esc_html( $column_value ) . '</a>';
			case 'customer' :
				return ! empty( $item[ 'customer' ]->id )
					? '<a href="' . esc_url( add_query_arg( 'customer', absint( $item['customer']->id ), $base_url ) ) . '">' . esc_html( $item['customer']->name ) . '</a>'
					: '&mdash;';

			case 'payment_id' :
				$number = edd_get_payment_number( $item['payment_id'] );
				return ! empty( $number )
					? '<a href="' . esc_url( edd_get_admin_url( array( 'page' => 'edd-payment-history', 'view' => 'view-order-details', 'id' => absint( $item['payment_id'] ) ) ) ) . '">' . esc_html( $number ) . '</a>'
					: '&mdash;';
			case 'ip' :
				return '<a href="' . esc_url( 'https://ipinfo.io/' . esc_attr( $item['ip'] ) )  . '" target="_blank" rel="noopener noreferrer">' . esc_html( $item['ip'] )  . '</a>';
			case 'file':
				return ! empty( $item['file'] )
					? esc_html( $item['file'] )
					: '&mdash;';
			default:
				return $item[ $column_name ];
		}
	}

	/**
	 * Set the table columns.
	 *
	 * @since 1.4
     *
	 * @return array $columns Array of all the list table columns
	 */
	public function get_columns() {
		return array(
			'ID'         => __( 'Log ID',       'easy-digital-downloads' ),
			'download'   => edd_get_label_singular(),
			'customer'   => __( 'Customer',     'easy-digital-downloads' ),
			'payment_id' => __( 'Order Number', 'easy-digital-downloads' ),
			'file'       => __( 'File',         'easy-digital-downloads' ),
			'ip'         => __( 'IP Address',   'easy-digital-downloads' ),
			'user_agent' => __( 'User Agent',   'easy-digital-downloads' ),
			'date'       => __( 'Date',         'easy-digital-downloads' )
		);
	}

	/**
	 * Gets the log entries for the current view
	 *
	 * @since 1.4
	 *
	 * @param $log_query array Arguments for getting logs.
     *
	 * @return array $logs_data Array of all the logs.
	 */
	function get_logs( $log_query = array() ) {
		$logs_data = array();

		$logs = edd_get_file_download_logs( $log_query );

		if ( $logs ) {
			foreach ( $logs as $log ) {
				/** @var $log File_Download_Log */

				$customer_id = ! empty( $log->customer_id ) ? (int) $log->customer_id : edd_get_payment_customer_id( $log->order_id );

				$files = $this->get_log_files( $log, $customer_id );

				$file_id = $log->file_id;

				/**
				 * Filters the ID of the file that was actually downloaded from this log.
				 *
				 * @param int               $file_id
				 * @param File_Download_Log $log
				 */
				$file_id = apply_filters( 'edd_log_file_download_file_id', $file_id, $log );

				$file_name = edd_get_file_download_log_meta( $log->id, 'file_name', true );

				if ( empty( $file_name ) && is_array( $files ) && isset( $files[ $file_id ] ) ) {
					$file_name = ! empty( $files[ $file_id ]['name'] )
						? $files[ $file_id ]['name']
						: edd_get_file_name( $files[ $file_id ] );
				}

				if ( empty( $this->file_search ) || ( ! empty( $this->file_search ) && strpos( strtolower( $file_name ), strtolower( $this->get_search() ) ) !== false ) ) {
					$logs_data[] = array(
						'ID'         => $log->id,
						'download'   => $log->product_id,
						'customer'   => new EDD_Customer( $customer_id ),
						'payment_id' => $log->order_id,
						'price_id'   => $log->price_id,
						'file'       => $file_name,
						'ip'         => $log->ip,
						'user_agent' => $log->user_agent,
						'date'       => $log->date_created,
					);
				}
			}
		}

		return $logs_data;
	}

	/**
	 * Retrieves the array of files associated with the log.
	 *
	 * This method contains a lot of logic to ensure backwards compatibility with how
	 * the `edd_log_file_download_download_files` filter was formatted in EDD 2.x.
	 *
	 * @since 3.0
	 *
	 * @param File_Download_Log $log
	 * @param int               $customer_id
	 *
	 * @return array
	 */
	protected function get_log_files( File_Download_Log $log, $customer_id ) {
		$customer = ! empty( $customer_id ) ? edd_get_customer( $customer_id ) : false;

		/*
		 * Get the files associated with this download and store them in a property to prevent
		 * multiple queries for the same download.
		 * This is needed for backwards compatibility in the `edd_log_file_download_download_files` filter.
		 */
		if ( ! array_key_exists( $log->product_id, $this->queried_files ) ) {
			$files = get_post_meta( $log->product_id, 'edd_download_files', true );
			$this->queried_files[ $log->product_id ] = $files;
		} else {
			$files = $this->queried_files[ $log->product_id ];
		}

		/*
		 * User info is needed for backwards compatibility in the `edd_log_file_download_download_files` filter.
		 */
		$user = ! empty( $customer->user_id ) ? get_userdata( $customer->user_id ) : false;

		$user_info = ! empty( $user )
			? array(
				'id'    => $user->ID,
				'email' => $user->user_email,
				'name'  => $user->display_name,
			)
			: array();

		/*
		 * Build up an array of meta.
		 */
		$meta = edd_get_file_download_log_meta( $log->id );

		// These meta keys no longer exist, but we need to create them for use in the filter.
		$backwards_compat_meta = array(
			'_edd_log_user_info'   => $user_info,
			'_edd_log_user_id'     => ! empty( $customer->user_id ) ? $customer->user_id : false,
			'_edd_log_customer_id' => $customer_id,
			'_edd_log_file_id'     => $log->file_id,
			'_edd_log_ip'          => $log->ip,
			'_edd_log_payment_id'  => $log->order_id,
			'_edd_log_price_id'    => $log->price_id,
		);

		foreach( $backwards_compat_meta as $meta_key => $meta_value ) {
			$meta[ $meta_key ] = array( $meta_value );
		}

		/**
		 * Filters the array of all files linked to the product.
		 *
		 * @param array             $files Files linked to the product.
		 * @param File_Download_Log $log   Log record from the database.
		 * @param array             $meta  What used to be the meta array in EDD 2.9 and lower.
		 */
		return apply_filters( 'edd_log_file_download_download_files', $files, $log, $meta );
	}

	/**
	 * Setup the final data for the table.
	 *
	 * @since 1.5
	 */
	public function get_total( $log_query = array() ) {
		return edd_count_file_download_logs( $log_query );
	}
}
