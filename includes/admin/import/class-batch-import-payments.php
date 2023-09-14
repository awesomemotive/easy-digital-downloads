<?php
/**
 * Payment Import Class
 *
 * This class handles importing payments with the batch processing API
 *
 * @package     EDD
 * @subpackage  Admin/Import
 * @copyright   Copyright (c) 2018, Easy Digital Downloads, LLC
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       2.6
 */

// Exit if accessed directly
defined( 'ABSPATH' ) || exit;

/**
 * EDD_Batch_Import Class
 *
 * @since 2.6
 */
class EDD_Batch_Payments_Import extends EDD_Batch_Import {

	/**
	 * Set up our import config.
	 *
	 * @since 2.6
	 * @return void
	 */
	public function init() {

		$this->per_step = 5;

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
			'name'              => '',
			'first_name'        => '',
			'last_name'         => '',
			'edd_customer_id'   => '',
			'user_id'           => '',
			'discounts'         => '',
			'key'               => '',
			'transaction_id'    => '',
			'ip'                => '',
			'currency'          => '',
			'parent_payment_id' => '',
			'downloads'         => '',
			'line1'             => '',
			'line2'             => '',
			'city'              => '',
			'state'             => '',
			'zip'               => '',
			'country'           => '',
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

		// Remove certain actions to ensure they don't fire when creating the payments
		remove_action( 'edd_complete_purchase', 'edd_trigger_purchase_receipt', 999 );
		remove_action( 'edd_admin_sale_notice', 'edd_admin_email_notice', 10 );

		$i      = 1;
		$offset = $this->step > 1 ? ( $this->per_step * ( $this->step - 1 ) ) : 0;

		if( $offset > $this->total ) {
			$this->done = true;

			// Clean up the temporary records in the payment import process
			global $wpdb;
			$sql = "DELETE FROM {$wpdb->prefix}edd_customermeta WHERE meta_key = '_canonical_import_id'";
			$wpdb->query( $sql );

			// Delete the uploaded CSV file.
			unlink( $this->file );
		}

		if( ! $this->done && $this->csv ) {

			$more = true;

			foreach( $this->csv as $key => $row ) {

				// Skip all rows until we pass our offset
				if( $key + 1 <= $offset ) {
					continue;
				}

				// Done with this batch
				if( $i > $this->per_step ) {
					break;
				}

				// Import payment
				$this->create_payment( $row );

				$i++;
			}

		}

		return $more;
	}

	/**
	 * Set up and store a payment record from a CSV row
	 *
	 * @since 2.6
	 * @return void
	 */
	public function create_payment( $row = array() ) {

		$payment = new EDD_Payment;
		$payment->status = 'pending';

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

				$date = date( 'Y-m-d H:i:s', current_time( 'timestamp' ) );

			} else {

				$date = date( 'Y-m-d H:i:s', strtotime( $date ) );

			}

			$payment->date = $date;

		}

		$payment->customer_id = $this->set_customer( $row );

		if( ! empty( $this->field_mapping['email'] ) && ! empty( $row[ $this->field_mapping['email'] ] ) ) {

			$payment->email = sanitize_text_field( $row[ $this->field_mapping['email'] ] );

		}

		if ( ! empty( $this->field_mapping['name'] ) && ! empty( $row[ $this->field_mapping['name'] ] ) ) {

			$payment->name = sanitize_text_field( $row[ $this->field_mapping['name'] ] );

		} else {

			if ( ! empty( $this->field_mapping['first_name'] ) && ! empty( $row[ $this->field_mapping['first_name'] ] ) ) {

				$payment->first_name = sanitize_text_field( $row[ $this->field_mapping['first_name'] ] );

			}

			if ( ! empty( $this->field_mapping['last_name'] ) && ! empty( $row[ $this->field_mapping['last_name'] ] ) ) {

				$payment->last_name = sanitize_text_field( $row[ $this->field_mapping['last_name'] ] );

			}
		}

		if( ! empty( $this->field_mapping['user_id'] ) && ! empty( $row[ $this->field_mapping['user_id'] ] ) ) {

			$user_id = sanitize_text_field( $row[ $this->field_mapping['user_id'] ] );

			if( is_numeric( $user_id ) ) {

				$user_id = absint( $row[ $this->field_mapping['user_id'] ] );
				$user    = get_userdata( $user_id );

			} elseif( is_email( $user_id ) ) {

				$user = get_user_by( 'email', $user_id );

			} else {

				$user = get_user_by( 'login', $user_id );

			}

			if( $user ) {

				$payment->user_id = $user->ID;

				$customer = new EDD_Customer( $payment->customer_id );

				if( empty( $customer->user_id ) ) {
					$customer->update( array( 'user_id' => $user->ID ) );
				}

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

			$gateways = edd_get_payment_gateways();
			$gateway  = strtolower( sanitize_text_field( $row[ $this->field_mapping['gateway'] ] ) );

			if( ! array_key_exists( $gateway, $gateways ) ) {

				foreach( $gateways as $key => $enabled_gateway ) {

					if( $enabled_gateway['checkout_label'] == $gateway ) {

						$gateway = $key;
						break;

					}

				}

			}

			$payment->gateway = $gateway;

		}

		if( ! empty( $this->field_mapping['currency'] ) && ! empty( $row[ $this->field_mapping['currency'] ] ) ) {

			$payment->currency = strtoupper( sanitize_text_field( $row[ $this->field_mapping['currency'] ] ) );

		}

		if( ! empty( $this->field_mapping['key'] ) && ! empty( $row[ $this->field_mapping['key'] ] ) ) {

			$payment->key = sanitize_text_field( $row[ $this->field_mapping['key'] ] );

		}

		if( ! empty( $this->field_mapping['parent_payment_id'] ) && ! empty( $row[ $this->field_mapping['parent_payment_id'] ] ) ) {

			$payment->parent_payment_id = absint( $row[ $this->field_mapping['parent_payment_id'] ] );

		}

		if( ! empty( $this->field_mapping['downloads'] ) && ! empty( $row[ $this->field_mapping['downloads'] ] ) ) {

			if( __( 'Products (Raw)', 'easy-digital-downloads' ) == $this->field_mapping['downloads'] ) {

				// This is an EDD export so we can extract prices
				$downloads = $this->get_downloads_from_edd( $row[ $this->field_mapping['downloads'] ] );

			} else {

				$downloads = $this->str_to_array( $row[ $this->field_mapping['downloads'] ] );

			}

			if( is_array( $downloads ) ) {

				$download_count = count( $downloads );

				foreach( $downloads as $download ) {

					if( is_array( $download ) ) {
						$download_name = $download['download'];
						$price         = $download['price'];
						$tax           = $download['tax'];
						$price_id      = $download['price_id'];
					} else {
						$download_name = $download;
					}

					$download_id = $this->maybe_create_download( $download_name );

					if( ! $download_id ) {
						continue;
					}

					$item_price = ! isset( $price ) ? edd_get_download_price( $download_id ) : $price;
					$item_tax   = ! isset( $tax ) ? ( $download_count > 1 ? 0.00 : $payment->tax ) : $tax;
					$price_id   = ! isset( $price_id ) ? false : $price_id;

					$args = array(
						'item_price' => $item_price,
						'tax'        => $item_tax,
						'price_id'   => $price_id,
					);

					$payment->add_download( $download_id, $args );

				}

			}

		}

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

		$address = array( 'line1' => '', 'line2' => '', 'city' => '', 'state' => '', 'zip' => '', 'country' => '' );

		foreach( $address as $key => $address_field ) {

			if( ! empty( $this->field_mapping[ $key ] ) && ! empty( $row[ $this->field_mapping[ $key ] ] ) ) {

				$address[ $key ] = sanitize_text_field( $row[ $this->field_mapping[ $key ] ] );

			}

		}

		$payment->address = $address;

		$payment->save();


		// The status has to be set after payment is created to ensure status update properly
		if( ! empty( $this->field_mapping['status'] ) && ! empty( $row[ $this->field_mapping['status'] ] ) ) {

			$payment->status = strtolower( sanitize_text_field( $row[ $this->field_mapping['status'] ] ) );

		} else {

			$payment->status = 'complete';

		}

		// Save a second time to update stats
		$payment->save();

	}

	private function set_customer( $row ) {

		global $wpdb;
		$customer = false;

		$customer = false;
		$email    = '';

		if( ! empty( $this->field_mapping['email'] ) && ! empty( $row[ $this->field_mapping['email'] ] ) ) {

			$email = sanitize_text_field( $row[ $this->field_mapping['email'] ] );

		}

		// Look for a customer from the canonical source, if any
		if( ! empty( $this->field_mapping['edd_customer_id'] ) && ! empty( $row[ $this->field_mapping['edd_customer_id'] ] ) ) {

			$canonical_id = absint( $row[ $this->field_mapping['edd_customer_id'] ] );
			$mapped_id    = $wpdb->get_var( $wpdb->prepare( "SELECT edd_customer_id FROM $wpdb->edd_customermeta WHERE meta_key = '_canonical_import_id' AND meta_value = %d LIMIT 1", $canonical_id ) );

		}

		if( ! empty( $mapped_id ) ) {

			$customer = new EDD_Customer( $mapped_id );

		}

		if( empty( $mapped_id ) || ! $customer->id > 0 ) {

			// Look for a customer based on provided ID, if any
			if ( ! empty( $this->field_mapping['edd_customer_id'] ) && ! empty( $row[ $this->field_mapping['edd_customer_id'] ] ) ) {

				$customer_id = absint( $row[ $this->field_mapping['edd_customer_id'] ] );

				$customer_by_id = new EDD_Customer( $customer_id );

			}

			// Now look for a customer based on provided email

			if( ! empty( $email ) ) {

				$customer_by_email = new EDD_Customer( $email );

			}

			// Now compare customer records. If they don't match, customer_id will be stored in meta and we will use the customer that matches the email

			if ( ! empty( $customer_by_email ) && ( empty( $customer_by_id ) || $customer_by_id->id !== $customer_by_email->id ) )  {

				$customer = $customer_by_email;

			} elseif ( ! empty( $customer_by_id ) ) {

				$customer = $customer_by_id;

				if( ! empty( $email ) ) {
					$customer->add_email( $email );
				}

			}

			// Make sure we found a customer. Create one if not.
			if ( empty( $customer->id ) ) {

				if ( ! $customer instanceof EDD_Customer ) {
					$customer = new EDD_Customer();
				}
			}

			if ( ! empty( $this->field_mapping['name'] ) && ! empty( $row[ $this->field_mapping['name'] ] ) ) {

				$name = $row[ $this->field_mapping['name'] ];

			} else {
				$first_name = '';
				$last_name  = '';
				if ( ! empty( $this->field_mapping['first_name'] ) && ! empty( $row[ $this->field_mapping['first_name'] ] ) ) {

					$first_name = $row[ $this->field_mapping['first_name'] ];

				}

				if ( ! empty( $this->field_mapping['last_name'] ) && ! empty( $row[ $this->field_mapping['last_name'] ] ) ) {

					$last_name = $row[ $this->field_mapping['last_name'] ];

				}
				$name = $first_name . ' ' . $last_name;
			}

			$customer->create(
				array(
					'name'  => sanitize_text_field( $name ),
					'email' => empty( $email ) ? '' : $email,
				)
			);

			if( ! empty( $canonical_id ) && (int) $canonical_id !== (int) $customer->id ) {
				$customer->update_meta( '_canonical_import_id', $canonical_id );
			}
		}

		if ( ! empty( $email ) && $email !== $customer->email ) {
			$customer->add_email( $email );
		}

		return $customer->id;

	}

	/**
	 * Look up Download by title and create one if none is found
	 *
	 * @since 2.6
	 * @return int|false Download ID or false if the download could not be created.
	 */
	private function maybe_create_download( $title = '' ) {

		if ( ! is_string( $title ) ) {
			return false;
		}

		$download = new WP_Query(
			array(
				'post_type'              => 'download',
				'title'                  => $title,
				'post_status'            => 'all',
				'posts_per_page'         => 1,
				'no_found_rows'          => true,
				'ignore_sticky_posts'    => true,
				'update_post_term_cache' => false,
				'update_post_meta_cache' => false,
				'orderby'                => 'post_date ID',
				'order'                  => 'ASC',
			)
		);

		if ( ! empty( $download->post ) ) {
			return $download->post->ID;
		}

		$args = array(
			'post_type'   => 'download',
			'post_title'  => $title,
			'post_author' => get_current_user_id(),
		);

		return wp_insert_post( $args );
	}

	/**
	 * Return the calculated completion percentage
	 *
	 * @since 2.6
	 * @return int
	 */
	public function get_downloads_from_edd( $data_str ) {

		// Break string into separate products

		$d_array   = array();
		$downloads = (array) explode( '/', $data_str );

		if( $downloads ) {

			foreach( $downloads as $key => $download ) {

				$d = (array) explode( '|', $download );
				if ( ! array_key_exists( 1, $d ) ) {
					continue;
				}
				preg_match_all( '/\{(\d|(\d+(\.\d+|\d+)))\}/', $d[1], $matches );

				if( false !== strpos( $d[1], '{' ) ) {

					$price = trim( substr( $d[1], 0, strpos( $d[1], '{' ) ) );

				} else {

					$price = trim( $d[1] );
				}

				$price    = floatval( $price );
				$tax      = isset( $matches[1][0] ) ? floatval( trim( $matches[1][0] ) ) : 0;
				$price_id = isset( $matches[1][1] ) ? trim( $matches[1][1] ) : false;

				$d_array[] = array(
					'download' => trim( $d[0] ),
					'price'    => $price - $tax,
					'tax'      => $tax,
					'price_id' => $price_id,
				);

			}

		}

		return $d_array;

	}

	/**
	 * Return the calculated completion percentage
	 *
	 * @since 2.6
	 * @return int
	 */
	public function get_percentage_complete() {

		$percentage = 0;
		$total      = count( $this->csv );

		if ( $total > 0 ) {
			$percentage = ( $this->step * $this->per_step / $total ) * 100;
		}

		if ( $percentage > 100 ) {
			$percentage = 100;
		}

		return $percentage;
	}

	/**
	 * Retrieve the URL to the payments list table
	 *
	 * @since 2.6
	 * @return string
	 */
	public function get_list_table_url() {
		return admin_url( 'edit.php?post_type=download&page=edd-payment-history' );
	}

	/**
	 * Retrieve the payments labels
	 *
	 * @since 2.6
	 * @return string
	 */
	public function get_import_type_label() {
		return __( 'payments', 'easy-digital-downloads' );
	}
}
