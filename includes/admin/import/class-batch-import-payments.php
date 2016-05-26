<?php
/**
 * Batch Import Class
 *
 * This is the base class for all batch import methods. Each data import type (customers, payments, etc) extend this class
 *
 * @package     EDD
 * @subpackage  Admin/Import
 * @copyright   Copyright (c) 2015, Pippin Williamson
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       2.6
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * EDD_Batch_Import Class
 *
 * @since 2.6
 */
class EDD_Batch_Payments_Import extends EDD_Batch_Import {

	public function init() {

		// Set up default field map values
		$payment = new EDD_Payment;

		// Set up default field map values
		$this->field_mapping = array(
			'total'             => '',
			'subtotal'          => '',
			'tax'               => 'draft',
			'number'            => '',
			'mode'              => '',
			'gateway'           => '',
			'date'              => '',
			'status'            => '',
			'email'             => '',
			'first_name'        => '',
			'last_name'         => '',
			'customer_id'       => '',
			'user_id'           => '',
			'discounts'         => '',
			'transaction_id'    => '',
			'ip'                => '',
			'currency'          => '',
			'parent_payment_id' => '',
			'downloads'         => ''
		);
	}

	/**
	 * Process a step
	 *
	 * @since 2.6
	 * @return bool
	 */
	public function process_step() {

		$more = false;

		if ( ! $this->can_import() ) {
			wp_die( __( 'You do not have permission to import data.', 'easy-digital-downloads' ), __( 'Error', 'easy-digital-downloads' ), array( 'response' => 403 ) );
		}

		$i      = 1;
		$offset = $this->step > 1 ? ( $this->per_step * ( $this->step - 1 ) ) : 0;

		if( $offset > $this->total ) {
			$this->done = true;
		}

		if( ! $this->done && $this->csv->data ) {

			$more = true;

			foreach( $this->csv->data as $key => $row ) {

				// Skip all rows until we pass our offset
				if( $key + 1 < $offset ) {
					continue;
				}

				// Done with this batch
				if( $i >= $this->per_step ) {
					break;
				}

				// Import payment
				$this->create_payment( $row );

				$i++;
			}

		}

		return $more;
	}

	public function create_payment( $row = array() ) {

		$payment = new EDD_Payment;		

		if( ! empty( $this->field_mapping['total'] ) && ! empty( $row[ $this->field_mapping['total'] ] ) ) {

			$payment->total = edd_sanitize_amount( $row[ $this->field_mapping['total'] ] );

		}

		if( ! empty( $this->field_mapping['tax'] ) && ! empty( $row[ $this->field_mapping['tax'] ] ) ) {

			$payment->tax = edd_sanitize_amount( $row[ $this->field_mapping['tax'] ] );

		}

		if( ! empty( $this->field_mapping['subtotal'] ) && ! empty( $row[ $this->field_mapping['subtotal'] ] ) ) {

			$payment->subtotal = edd_sanitize_amount( $row[ $this->field_mapping['subtotal'] ] );

		} else {

			$payment->subtotal = $payment->total - $payment->tax;

		}

		if( ! empty( $this->field_mapping['number'] ) && ! empty( $row[ $this->field_mapping['number'] ] ) ) {

			$payment->number = sanitize_text_field( $row[ $this->field_mapping['number'] ] );

		}

		if( ! empty( $this->field_mapping['mode'] ) && ! empty( $row[ $this->field_mapping['mode'] ] ) ) {

			$mode = strtolower( sanitize_text_field( $row[ $this->field_mapping['mode'] ] ) );
			$mode = 'test' != $mode && 'live' != $mode ? false : $mode;
			if( ! $mode ) {
				$mode = edd_is_test_mode() ? 'test' : 'live';
			}

			$payment->mode = $mode;

		}

		if( ! empty( $this->field_mapping['date'] ) && ! empty( $row[ $this->field_mapping['date'] ] ) ) {

			$date = sanitize_text_field( $row[ $this->field_mapping['date'] ] );

			if( ! strtotime( $date ) ) {

				$date = date( 'Y-n-d H:i:s', current_time( 'timestamp' ) );

			}

			$payment->date = $date;

		}

		if( ! empty( $this->field_mapping['status'] ) && ! empty( $row[ $this->field_mapping['status'] ] ) ) {

			$payment->status = strtolower( sanitize_text_field( $row[ $this->field_mapping['status'] ] ) );

		}

		if( ! empty( $this->field_mapping['customer_id'] ) && ! empty( $row[ $this->field_mapping['customer_id'] ] ) ) {

			$customer_id = absint( $row[ $this->field_mapping['customer_id'] ] );

			$customer = new EDD_Customer( $customer_id );

			if( $customer->id > 0 ) {

				$payment->customer_id = $customer_id;
	
			}

		}

		if( ! empty( $this->field_mapping['email'] ) && ! empty( $row[ $this->field_mapping['email'] ] ) ) {

			$payment->email = sanitize_text_field( $row[ $this->field_mapping['email'] ] );

		}

		if( ! empty( $this->field_mapping['first_name'] ) && ! empty( $row[ $this->field_mapping['first_name'] ] ) ) {

			$payment->first_name = sanitize_text_field( $row[ $this->field_mapping['first_name'] ] );

		}

		if( ! empty( $this->field_mapping['last_name'] ) && ! empty( $row[ $this->field_mapping['last_name'] ] ) ) {

			$payment->last_name = sanitize_text_field( $row[ $this->field_mapping['last_name'] ] );

		}

		if( ! empty( $this->field_mapping['user_id'] ) && ! empty( $row[ $this->field_mapping['user_id'] ] ) ) {

			$user_id = absint( $row[ $this->field_mapping['user_id'] ] );

			$user = get_userdata( $user_id );

			if( $user ) {

				$payment->user_id = $user->ID;
	
			}

		}

		if( ! empty( $this->field_mapping['discounts'] ) && ! empty( $row[ $this->field_mapping['discounts'] ] ) ) {

			$payment->discounts = sanitize_text_field( $row[ $this->field_mapping['discounts'] ] );

		}

		if( ! empty( $this->field_mapping['transaction_id'] ) && ! empty( $row[ $this->field_mapping['transaction_id'] ] ) ) {

			$payment->transaction_id = sanitize_text_field( $row[ $this->field_mapping['transaction_id'] ] );

		}

		if( ! empty( $this->field_mapping['ip'] ) && ! empty( $row[ $this->field_mapping['ip'] ] ) ) {

			$payment->ip = sanitize_text_field( $row[ $this->field_mapping['ip'] ] );

		}

		if( ! empty( $this->field_mapping['gateway'] ) && ! empty( $row[ $this->field_mapping['gateway'] ] ) ) {

			$payment->gateway = sanitize_text_field( $row[ $this->field_mapping['gateway'] ] );

		}

		if( ! empty( $this->field_mapping['currency'] ) && ! empty( $row[ $this->field_mapping['currency'] ] ) ) {

			$payment->currency = strtoupper( sanitize_text_field( $row[ $this->field_mapping['currency'] ] ) );

		}

		if( ! empty( $this->field_mapping['parent_payment_id'] ) && ! empty( $row[ $this->field_mapping['parent_payment_id'] ] ) ) {

			$payment->parent_payment_id = absint( $row[ $this->field_mapping['parent_payment_id'] ] );

		}

		if( ! empty( $this->field_mapping['downloads'] ) && ! empty( $row[ $this->field_mapping['downloads'] ] ) ) {

			$downloads = $this->str_to_array( $row[ $this->field_mapping['downloads'] ] );

			if( is_array( $downloads ) ) {

				$download_count = count( $downloads );

				foreach( $downloads as $download ) {

					$download_id = $this->maybe_create_download( $download ); 

					if( ! $download_id ) {
						continue;
					}

					$item_price = $download_count > 1 ? 0.00 : $payment->subtotal;
					$item_tax   = $download_count > 1 ? 0.00 : $payment->tax;

					$payment->add_download( $download_id, array(
						'item_price' => $item_price,
						'tax'        => $item_tax
					) );

				}
				
			}

		}

		$payment->save();

	}

	private function maybe_create_download( $title = '' ) {

		if( ! is_string( $title ) ) {
			return false;
		}

		$download = get_page_by_title( $title, OBJECT, 'download' );

		if( $download ) {

			$download_id = $download->ID;

		} else {

			$args = array(
				'post_type'   => 'download',
				'post_title'  => $title,
				'post_author' => get_current_user_id()
			);

			$download_id = wp_insert_post( $args );

		}

		return $download_id;
	}

	/**
	 * Return the calculated completion percentage
	 *
	 * @since 2.6
	 * @return int
	 */
	public function get_percentage_complete() {

		$total = 20;

		if( $total > 0 ) {
			$percentage = ( $this->step / $total ) * 100;
		}

		if( $percentage > 100 ) {
			$percentage = 100;
		}

		return $percentage;
	}

	public function get_list_table_url() {
		return admin_url( 'edit.php?post_type=download&page=edd-payment-history' );
	}

	public function get_import_type_label() {
		return __( 'payments', 'easy-digital-downloads' );
	}
}