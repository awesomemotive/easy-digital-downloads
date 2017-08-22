<?php
/**
 * Recount all cutomer stats
 *
 * This class handles batch processing of recounting all customer stats
 *
 * @subpackage  Admin/Tools/EDD_Tools_Recount_Customer_Stats
 * @copyright   Copyright (c) 2015, Chris Klosowski
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       2.5
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * EDD_Tools_Recount_Stats Class
 *
 * @since 2.5
 */
class EDD_Tools_Recount_Customer_Stats extends EDD_Batch_Export {

	/**
	 * Our export type. Used for export-type specific filters/actions
	 * @var string
	 * @since 2.5
	 */
	public $export_type = '';

	/**
	 * Allows for a non-download batch processing to be run.
	 * @since  2.5
	 * @var boolean
	 */
	public $is_void = true;

	/**
	 * Sets the number of items to pull on each step
	 * @since  2.5
	 * @var integer
	 */
	public $per_step = 5;

	/**
	 * Get the Export Data
	 *
	 * @access public
	 * @since 2.5
	 * @global object $wpdb Used to query the database using the WordPress
	 *   Database API
	 * @return array $data The data for the CSV file
	 */
	public function get_data() {

		$args = array(
			'number'       => $this->per_step,
			'offset'       => $this->per_step * ( $this->step - 1 ),
			'orderby'      => 'id',
			'order'        => 'DESC',
		);

		$customers = EDD()->customers->get_customers( $args );

		if ( $customers ) {

			$allowed_payment_status = apply_filters( 'edd_recount_customer_payment_statuses', edd_get_payment_status_keys() );

			foreach ( $customers as $customer ) {

				$attached_payment_ids = explode( ',', $customer->payment_ids );

				$attached_args = array(
					'post__in' => $attached_payment_ids,
					'number'   => -1,
					'status'   => $allowed_payment_status,
				);

				$attached_payments = edd_get_payments( $attached_args );

				$unattached_args = array(
					'post__not_in' => $attached_payment_ids,
					'number'       => -1,
					'status'       => $allowed_payment_status,
					'meta_query'   => array(
						array(
							'key'     => '_edd_payment_user_email',
							'value'   => $customer->email,
							'compare' => '=',
						)
					),
				);

				$unattached_payments = edd_get_payments( $unattached_args );

				$payments = array_merge( $attached_payments, $unattached_payments );

				$purchase_value = 0.00;
				$purchase_count = 0;
				$payment_ids    = array();

				if( $payments ) {

					foreach ( $payments as $payment ) {

						$should_process_payment = 'publish' == $payment->post_status || 'revoked' == $payment->post_status ? true : false;
						$should_process_payment = apply_filters( 'edd_customer_recount_should_process_payment', $should_process_payment, $payment );

						if( true === $should_process_payment ) {

							if ( apply_filters( 'edd_customer_recount_sholud_increase_value', true, $payment ) ) {
								$purchase_value += edd_get_payment_amount( $payment->ID );
							}

							if ( apply_filters( 'edd_customer_recount_sholud_increase_count', true, $payment ) ) {
								$purchase_count++;
							}

						}

						$payment_ids[] = $payment->ID;
					}

				}

				$payment_ids = implode( ',', $payment_ids );

				$customer_update_data = array(
					'purchase_count' => $purchase_count,
					'purchase_value' => $purchase_value,
					'payment_ids'    => $payment_ids,
				);

				$customer_instance = new EDD_Customer( $customer->id );
				$customer_instance->update( $customer_update_data );

			}

			return true;
		}

		return false;

	}

	/**
	 * Return the calculated completion percentage
	 *
	 * @since 2.5
	 * @return int
	 */
	public function get_percentage_complete() {

		$args = array(
			'number'       => -1,
			'orderby'      => 'id',
			'order'        => 'DESC',
		);

		$customers = EDD()->customers->get_customers( $args );
		$total     = count( $customers );

		$percentage = 100;

		if( $total > 0 ) {
			$percentage = ( ( $this->per_step * $this->step ) / $total ) * 100;
		}

		if( $percentage > 100 ) {
			$percentage = 100;
		}

		return $percentage;
	}

	/**
	 * Set the properties specific to the payments export
	 *
	 * @since 2.5
	 * @param array $request The Form Data passed into the batch processing
	 */
	public function set_properties( $request ) { }

	/**
	 * Process a step
	 *
	 * @since 2.5
	 * @return bool
	 */
	public function process_step() {

		if ( ! $this->can_export() ) {
			wp_die( __( 'You do not have permission to export data.', 'easy-digital-downloads' ), __( 'Error', 'easy-digital-downloads' ), array( 'response' => 403 ) );
		}

		$had_data = $this->get_data();

		if( $had_data ) {
			$this->done = false;
			return true;
		} else {
			$this->done    = true;
			$this->message = __( 'Customer stats successfully recounted.', 'easy-digital-downloads' );
			return false;
		}
	}

	public function headers() {
		ignore_user_abort( true );

		if ( ! edd_is_func_disabled( 'set_time_limit' ) ) {
			set_time_limit( 0 );
		}
	}

	/**
	 * Perform the export
	 *
	 * @access public
	 * @since 2.5
	 * @return void
	 */
	public function export() {

		// Set headers
		$this->headers();

		edd_die();
	}

}
