<?php
/**
 * Batch Sales Logs Export Class
 *
 * This class handles Sales logs export
 *
 * @package     EDD
 * @subpackage  Admin/Reports
 * @copyright   Copyright (c) 2016, Sunny Ratilal
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       2.7
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * EDD_Batch_Sales_Export Class
 *
 * @since 2.7
 */
class EDD_Batch_Sales_Export extends EDD_Batch_Export {
	/**
	 * Our export type. Used for export-type specific filters/actions
	 *
	 * @var string
	 * @since 2.7
	 */
	public $export_type = 'sales';

	/**
	 * Set the CSV columns
	 *
	 * @access public
	 * @since 2.7
	 * @return array $cols All the columns
	 */
	public function csv_cols() {
		$cols = array(
			'ID'         => __( 'Log ID', 'easy-digital-downloads' ),
			'user_id'    => __( 'User', 'easy-digital-downloads' ),
			'download'   => edd_get_label_singular(),
			'amount'     => __( 'Item Amount', 'easy-digital-downloads' ),
			'payment_id' => __( 'Payment ID', 'easy-digital-downloads' ),
			'date'       => __( 'Date', 'easy-digital-downloads' ),
		);

		return $cols;
	}

	/**
	 * Get the Export Data
	 *
	 * @access public
	 * @since 2.7
 	 * @global object $edd_logs EDD Logs Object
	 * @return array $data The data for the CSV file
	 */
	public function get_data() {
		global $edd_logs;

		$data = array();

		$args = array(
			'log_type'       => 'sale',
			'posts_per_page' => 30,
			'paged'          => $this->step
		);

		if ( ! empty( $this->start ) || ! empty( $this->end ) ) {
			$args['date_query'] = array(
				array(
					'after'     => date( 'Y-n-d H:i:s', strtotime( $this->start ) ),
					'before'    => date( 'Y-n-d H:i:s', strtotime( $this->end ) ),
					'inclusive' => true
				)
			);
		}

		if ( 0 !== $this->download_id ) {
			$args['post_parent'] = $this->download_id;
		}

		$logs = $edd_logs->get_connected_logs( $args );

		if ( $logs ) {
			foreach ( $logs as $log ) {
				$payment_id = get_post_meta( $log->ID, '_edd_log_payment_id', true );
				$payment    = new EDD_Payment( $payment_id );
				$download    = new EDD_Download( $log->post_parent );

				if ( ! empty( $payment->ID ) ) {
					$customer   = new EDD_Customer( $payment->customer_id );
					$cart_items = $payment->cart_details;
					$amount     = 0;

					if ( is_array( $cart_items ) ) {
						foreach ( $cart_items as $item ) {
							if ( $item['id'] == $log->post_parent ) {
								if ( isset( $item['item_number']['options']['price_id'] ) ) {
									$log_price_id = get_post_meta( $log->ID, '_edd_log_price_id', true );

									if ( (int) $item['item_number']['options']['price_id'] !== (int) $log_price_id ) {
										continue;
									}
								}

								$amount = isset( $item['price'] ) ? $item['price'] : $item['item_price'];
								break;
							}
						}
					}
				}
				$data[] = array(
					'ID'         => $log->ID,
					'user_id'    => $customer->user_id,
					'download'   => $download->post_title,
					'amount'     => $amount,
					'payment_id' => $payment->ID,
					'date'       => get_post_field( 'post_date', $payment_id ),
				);
			}

			$data = apply_filters( 'edd_export_get_data', $data );
			$data = apply_filters( 'edd_export_get_data_' . $this->export_type, $data );

			return $data;
		}

		return false;
	}

	/**
	 * Return the calculated completion percentage
	 *
	 * @since 2.7
	 * @return int
	 */
	public function get_percentage_complete() {
		global $edd_logs;

		$args = array(
			'post_type'		   => 'edd_log',
			'posts_per_page'   => -1,
			'post_status'	   => 'publish',
			'fields'           => 'ids',
			'post_parent'      => $this->download_id,
			'tax_query'        => array(
				array(
					'taxonomy' 	=> 'edd_log_type',
					'field'		=> 'slug',
					'terms'		=> 'sale'
				)
			),
			'date_query'        => array(
				array(
					'after'     => date( 'Y-n-d H:i:s', strtotime( $this->start ) ),
					'before'    => date( 'Y-n-d H:i:s', strtotime( $this->end ) ),
					'inclusive' => true
				)
			)
		);

		$logs       = new WP_Query( $args );
		$total      = (int) $logs->post_count;
		$percentage = 100;

		if ( $total > 0 ) {
			$percentage = ( ( 30 * $this->step ) / $total ) * 100;
		}

		if ( $percentage > 100 ) {
			$percentage = 100;
		}

		return $percentage;
	}

	public function set_properties( $request ) {
		$this->start       = isset( $request['start'] ) ? sanitize_text_field( $request['start'] ) : '';
		$this->end         = isset( $request['end'] )   ? sanitize_text_field( $request['end'] )   : '';
		$this->download_id = isset( $request['download_id'] )   ? absint( $request['download_id'] )        : 0;
	}
}