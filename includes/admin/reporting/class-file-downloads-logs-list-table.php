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
	 * @param array  $item Contains all the data of the log item
	 * @param string $column_name The name of the column
	 *
	 * @return string Column Name
	 */
	public function column_default( $item, $column_name ) {
		$base_url = remove_query_arg( 'paged' );
		switch ( $column_name ) {
			case 'download':
				$download     = new EDD_Download( $item[ $column_name ] );
				$column_value = $download->get_name();

				if ( ! empty( $item['price_id'] ) ) {
					$column_value .= ' &mdash; ' . edd_get_price_option_name( $download->ID, $item['price_id'] );
				}

				return '<a href="' . add_query_arg( 'download', $download->ID, $base_url ) . '" >' . $column_value . '</a>';
			case 'customer':
				return ! empty( $item['customer']->id )
					? '<a href="' . add_query_arg( 'customer', $item['customer']->id, $base_url ) . '">' . $item['customer']->name . '</a>'
					: '&mdash;';

			case 'payment_id':
				$number = edd_get_payment_number( $item['payment_id'] );
				return ! empty( $number )
					? '<a href="' . admin_url( 'edit.php?post_type=download&page=edd-payment-history&view=view-order-details&id=' . $item['payment_id'] ) . '">' . esc_html( $number ) . '</a>'
					: '&mdash;';
			case 'ip':
				return '<a href="https://ipinfo.io/' . $item['ip'] . '" target="_blank" rel="noopener noreferrer">' . $item['ip'] . '</a>';
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
			'ID'         => __( 'Log ID', 'easy-digital-downloads' ),
			'download'   => edd_get_label_singular(),
			'customer'   => __( 'Customer', 'easy-digital-downloads' ),
			'payment_id' => __( 'Order Number', 'easy-digital-downloads' ),
			'file'       => __( 'File', 'easy-digital-downloads' ),
			'ip'         => __( 'IP Address', 'easy-digital-downloads' ),
			'user_agent' => __( 'User Agent', 'easy-digital-downloads' ),
			'date'       => __( 'Date', 'easy-digital-downloads' ),
		);
	}

	/**
	 * Gets the log entries for the current view
	 *
	 * @since 1.4
	 *
	 * @return array $logs_data Array of all the logs.
	 */
	function get_logs( $log_query = array() ) {
		$logs_data = array();

		$logs = edd_get_file_download_logs( $log_query );

		if ( $logs ) {
			foreach ( $logs as $log ) {
				/** @var $log EDD\Logs\File_Download_Log */

				$meta        = get_post_custom( $log->order_id );
				$customer_id = (int) isset( $meta['_edd_log_customer_id'] )
					? $meta['_edd_log_customer_id'][0]
					: edd_get_payment_customer_id( $log->order_id );

				if ( ! array_key_exists( $log->download_id, $this->queried_files ) ) {
					$files                                    = get_post_meta( $log->download_id, 'edd_download_files', true );
					$this->queried_files[ $log->download_id ] = $files;
				} else {
					$files = $this->queried_files[ $log->download_id ];
				}

				// For backwards compatibility purposes
				$user = edd_get_customer( $log->user_id );

				$user_info = ! empty( $user )
					? array(
						'id'    => $user->ID,
						'email' => $user->user_email,
						'name'  => $user->display_name,
					)
					: array();

				$meta = array(
					'_edd_log_user_info'  => $user_info,
					'_edd_log_user_id'    => $log->user_id,
					'_edd_log_file_id'    => $log->file_id,
					'_edd_log_ip'         => $log->id,
					'_edd_log_payment_id' => $log->order_id,
					'_edd_log_price_id'   => $log->price_id,
				);

				// Filter the download files
				$files = apply_filters( 'edd_log_file_download_download_files', $files, $log, $meta );

				$file_id = $log->file_id;

				// Filter the $file_id
				$file_id = apply_filters( 'edd_log_file_download_file_id', $file_id, $log );

				$file_name = isset( $files[ $file_id ]['name'] )
					? $files[ $file_id ]['name']
					: null;

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
	 * Setup the final data for the table.
	 *
	 * @since 1.5
	 */
	public function get_total( $log_query = array() ) {
		return edd_count_file_download_logs( $log_query );
	}
}
