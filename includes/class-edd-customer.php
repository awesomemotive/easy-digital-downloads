<?php
/**
 * Customer Object
 *
 * @package     EDD
 * @subpackage  Classes/Customer
 * @copyright   Copyright (c) 2012, Chris Klosowski
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       2.3
*/

/**
 * EDD_Customer Class
 *
 * @since 2.3
 */
class EDD_Customer {

	/**
	 * The customer ID
	 *
	 * @since 2.3
	 */
	public $id = 0;

	/**
	 * The customer's purchase count
	 *
	 * @since 2.3
	 */
	public $purchase_count = 0;

	/**
	 * The customer's lifetime value
	 *
	 * @since 2.3
	 */
	public $purchase_value = 0;

	/**
	 * The customer's email
	 *
	 * @since 2.3
	 */
	public $email;

	/**
	 * The customer's name
	 *
	 * @since 2.3
	 */
	public $name;

	/**
	 * The customer's creation date
	 *
	 * @since 2.3
	 */
	public $date_created;

	/**
	 * The payment IDs associated with the customer
	 *
	 * @since  2.3
	 */
	public $payment_ids;

	/**
	 * The user ID associated with the customer
	 *
	 * @since  2.3
	 */
	public $user_id;

	/**
	 * Customer Notes
	 *
	 * @since  2.3
	 */
	public $notes;

	/**
	 * Get things going
	 *
	 * @since 2.3
	 */
	public function __construct( $_id_or_email ) {

		global $edd_customers_db;
		$edd_customers_db = new EDD_DB_Customers;

		if ( false === $_id_or_email || ( is_numeric( $_id_or_email ) && (int) $_id_or_email !== absint( $_id_or_email ) ) ) {
			return false;
		}

		$field       = is_numeric( $_id_or_email ) ? 'id' : 'email';
		$customer    = $edd_customers_db->get_customer_by( $field, $_id_or_email );

		if ( empty( $customer ) || ! is_object( $customer ) ) {
			return false;
		}

		foreach ( $customer as $key => $value ) {
			$this->$key = $value;
		}

	}

	/**
	 * Magic __get function to dispatch a call to retrieve a private property
	 *
	 * @since 2.3
	 */
	public function __get( $key ) {

		if( method_exists( $this, 'get_' . $key ) ) {

			return call_user_func( array( $this, 'get_' . $key ) );

		} else {

			throw new Exception( 'Can\'t get property ' . $key );

		}

	}


	/**
	 * Attach payment to the customer then triggers increasing stats
	 *
	 * @since  2.3
	 * @param  int $payment_id The payment ID to attach to the customer
	 * @param  bool $update_stats For backwards compatibility, if we should increase the stats or not
	 * @return bool            If the attachment was successfuly
	 */
	public function attach_payment( $payment_id = 0, $update_stats = true ) {

		if( empty( $payment_id ) ) {
			return false;
		}

		global $edd_customers_db;

		if( empty( $this->payment_ids ) ) {

			$new_payment_ids = $payment_id;

		} else {

			$payment_ids   = array_map( 'absint', explode( ',', $this->payment_ids ) );
			$payment_ids[] = $payment_id;
			$new_payment_ids = implode( ',', array_unique( array_values( $payment_ids ) ) );

		}

		$payment_added = $edd_customers_db->update( $this->id, array( 'payment_ids' => $new_payment_ids ) );

		if ( $payment_added ) {

			$this->payment_ids = $new_payment_ids;

			// We added this payment successfully, increment the stats
			if ( $update_stats ) {
				$payment_amount = edd_get_payment_amount( $payment_id );

				if ( ! empty( $payment_amount ) ) {
					$this->increase_value( $payment_amount );
				}

				$this->increase_purchase_count();
			}

		}

		return $payment_added;
	}


	/**
	 * Remove a payment from this customer, then triggers reducing stats
	 *
	 * @since  2.3
	 * @param  integer $payment_id The Payment ID to remove
	 * @param  bool $update_stats For backwards compatibility, if we should increase the stats or not
	 * @return boolean             If the removal was successful
	 */
	public function remove_payment( $payment_id = 0, $update_stats = true ) {

		if( empty( $payment_id ) ) {
			return false;
		}

		global $edd_customers_db;

		$new_payment_ids = '';

		if( ! empty( $this->payment_ids ) ) {

			$payment_ids = array_map( 'absint', explode( ',', $this->payment_ids ) );

			$pos = array_search( $payment_id, $payment_ids );
			if ( false === $pos ) {
				return false;
			}

			unset( $payment_ids[$pos] );
			$payment_ids = array_filter( $payment_ids );

			$new_payment_ids = implode( ',', array_unique( array_values( $payment_ids ) ) );

		}

		$payment_removed = $edd_customers_db->update( $this->id, array( 'payment_ids' => $new_payment_ids ) );

		if ( $payment_removed ) {

			$this->payment_ids = $new_payment_ids;

			if ( $update_stats ) {
				// We removed this payment successfully, decrement the stats
				$payment_amount = edd_get_payment_amount( $payment_id );

				if ( ! empty( $payment_amount ) ) {
					$this->decrease_value( $payment_amount );
				}

				$this->decrease_purchase_count();
			}

		}

		return $payment_removed;

	}

	/**
	 * Increase the purcahse count of a customer
	 *
	 * @since  2.3
	 * @param  integer $count The number to imcrement by
	 * @return mixed          If successful, the new count, otherwise false
	 */
	public function increase_purchase_count( $count = 1 ) {

		// Make sure it's numeric and not negative
		if ( ! is_numeric( $count ) || $count != absint( $count ) ) {
			return false;
		}

		global $edd_customers_db;
		$new_total = (int) $this->purchase_count + (int) $count;

		if ( $edd_customers_db->update( $this->id, array( 'purchase_count' => $new_total ) ) ) {
			$this->purchase_count = $new_total;
			return $new_total;
		}

		return false;
	}

	/**
	 * Decrease the customer purchase count
	 *
	 * @since  2.3
	 * @param  integer $count The amount to decrease by
	 * @return mixed          If successful, the new count, otherwise false
	 */
	public function decrease_purchase_count( $count = 1 ) {

		// Make sure it's numeric and not negative
		if ( ! is_numeric( $count ) || $count != absint( $count ) ) {
			return false;
		}

		global $edd_customers_db;
		$new_total = (int) $this->purchase_count - (int) $count;

		if ( $edd_customers_db->update( $this->id, array( 'purchase_count' => $new_total ) ) ) {
			$this->purchase_count = $new_total;
			return (string) $new_total;
		}

		return false;
	}

	/**
	 * Increase the customer's lifetime value
	 *
	 * @since  2.3
	 * @param  float  $value The value to increase by
	 * @return mixed         If successful, the new value, otherwise false
	 */
	public function increase_value( $value = 0.00 ) {

		global $edd_customers_db;
		$new_value = floatval( $this->purchase_value ) + $value;

		if ( $edd_customers_db->update( $this->id, array( 'purchase_value' => $new_value ) ) ) {
			$this->purchase_value = $new_value;
			return (string) $new_value;
		}

		return false;
	}

	/**
	 * Decrease a customer's lifetime value
	 *
	 * @since  2.3
	 * @param  float  $value The value to decrease by
	 * @return mixed         If successful, the new value, otherwise false
	 */
	public function decrease_value( $value = 0.00 ) {

		global $edd_customers_db;
		$new_value = floatval( $this->purchase_value ) - $value;

		if ( $edd_customers_db->update( $this->id, array( 'purchase_value' => $new_value ) ) ) {
			$this->purchase_value = $new_value;
			return (string) $new_value;
		}

		return false;
	}

}
