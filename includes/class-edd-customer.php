<?php
/**
 * Customer Object
 *
 * @package     EDD
 * @subpackage  Classes/Customer
 * @copyright   Copyright (c) 2015, Chris Klosowski
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       2.3
*/

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

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
	 * The Database Abstraction
	 *
	 * @since  2.3
	 */
	protected $db;

	/**
	 * Get things going
	 *
	 * @since 2.3
	 */
	public function __construct( $_id_or_email = false, $by_user_id = false ) {

		$this->db = new EDD_DB_Customers;

		if ( false === $_id_or_email || ( is_numeric( $_id_or_email ) && (int) $_id_or_email !== absint( $_id_or_email ) ) ) {
			return false;
		}

		$by_user_id = is_bool( $by_user_id ) ? $by_user_id : false;

		if ( is_numeric( $_id_or_email ) ) {
			$field = $by_user_id ? 'user_id' : 'id';
		} else {
			$field = 'email';
		}

		$customer = $this->db->get_customer_by( $field, $_id_or_email );

		if ( empty( $customer ) || ! is_object( $customer ) ) {
			return false;
		}

		$this->setup_customer( $customer );

	}

	/**
	 * Given the customer data, let's set the variables
	 *
	 * @since  2.3
	 * @param  object $customer The Customer Object
	 * @return bool             If the setup was successful or not
	 */
	private function setup_customer( $customer ) {

		if ( ! is_object( $customer ) ) {
			return false;
		}

		foreach ( $customer as $key => $value ) {

			switch ( $key ) {

				case 'notes':
					$this->$key = $this->get_notes();
					break;

				default:
					$this->$key = $value;
					break;

			}

		}

		// Customer ID and email are the only things that are necessary, make sure they exist
		if ( ! empty( $this->id ) && ! empty( $this->email ) ) {
			return true;
		}

		return false;

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

			return new WP_Error( 'edd-customer-invalid-property', sprintf( __( 'Can\'t get property %s', 'easy-digital-downloads' ), $key ) );

		}

	}

	/**
	 * Creates a customer
	 *
	 * @since  2.3
	 * @param  array  $data Array of attributes for a customer
	 * @return mixed        False if not a valid creation, Customer ID if user is found or valid creation
	 */
	public function create( $data = array() ) {

		if ( $this->id != 0 || empty( $data ) ) {
			return false;
		}

		$defaults = array(
			'payment_ids' => ''
		);

		$args = wp_parse_args( $data, $defaults );
		$args = $this->sanitize_columns( $args );

		if ( empty( $args['email'] ) || ! is_email( $args['email'] ) ) {
			return false;
		}

		if ( ! empty( $args['payment_ids'] ) && is_array( $args['payment_ids'] ) ) {
			$args['payment_ids'] = implode( ',', array_unique( array_values( $args['payment_ids'] ) ) );
		}

		do_action( 'edd_customer_pre_create', $args );

		$created = false;

		// The DB class 'add' implies an update if the customer being asked to be created already exists
		if ( $this->db->add( $data ) ) {

			// We've successfully added/updated the customer, reset the class vars with the new data
			$customer = $this->db->get_customer_by( 'email', $args['email'] );

			// Setup the customer data with the values from DB
			$this->setup_customer( $customer );

			$created = $this->id;
		}

		do_action( 'edd_customer_post_create', $created, $args );

		return $created;

	}

	/**
	 * Update a customer record
	 *
	 * @since  2.3
	 * @param  array  $data Array of data attributes for a customer (checked via whitelist)
	 * @return bool         If the update was successful or not
	 */
	public function update( $data = array() ) {

		if ( empty( $data ) ) {
			return false;
		}

		$data = $this->sanitize_columns( $data );

		do_action( 'edd_customer_pre_update', $this->id, $data );

		$updated = false;

		if ( $this->db->update( $this->id, $data ) ) {

			$customer = $this->db->get_customer_by( 'id', $this->id );
			$this->setup_customer( $customer);

			$updated = true;
		}

		do_action( 'edd_customer_post_update', $updated, $this->id, $data );

		return $updated;
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

		if( empty( $this->payment_ids ) ) {

			$new_payment_ids = $payment_id;

		} else {

			$payment_ids = array_map( 'absint', explode( ',', $this->payment_ids ) );

			if ( in_array( $payment_id, $payment_ids ) ) {
				$update_stats = false;
			}

			$payment_ids[] = $payment_id;

			$new_payment_ids = implode( ',', array_unique( array_values( $payment_ids ) ) );

		}

		do_action( 'edd_customer_pre_attach_payment', $payment_id, $this->id );

		$payment_added = $this->update( array( 'payment_ids' => $new_payment_ids ) );

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

		do_action( 'edd_customer_post_attach_payment', $payment_added, $payment_id, $this->id );

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

		do_action( 'edd_customer_pre_remove_payment', $payment_id, $this->id );

		$payment_removed = $this->update( array( 'payment_ids' => $new_payment_ids ) );

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

		do_action( 'edd_customer_post_remove_payment', $payment_removed, $payment_id, $this->id );

		return $payment_removed;

	}

	/**
	 * Increase the purchase count of a customer
	 *
	 * @since  2.3
	 * @param  integer $count The number to imcrement by
	 * @return int            The purchase count
	 */
	public function increase_purchase_count( $count = 1 ) {

		// Make sure it's numeric and not negative
		if ( ! is_numeric( $count ) || $count != absint( $count ) ) {
			return false;
		}

		$new_total = (int) $this->purchase_count + (int) $count;

		do_action( 'edd_customer_pre_increase_purchase_count', $count, $this->id );

		if ( $this->update( array( 'purchase_count' => $new_total ) ) ) {
			$this->purchase_count = $new_total;
		}

		do_action( 'edd_customer_post_increase_purchase_count', $this->purchase_count, $count, $this->id );

		return $this->purchase_count;
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

		$new_total = (int) $this->purchase_count - (int) $count;

		if( $new_total < 0 ) {
			$new_total = 0;
		}

		do_action( 'edd_customer_pre_decrease_purchase_count', $count, $this->id );

		if ( $this->update( array( 'purchase_count' => $new_total ) ) ) {
			$this->purchase_count = $new_total;
		}

		do_action( 'edd_customer_post_decrease_purchase_count', $this->purchase_count, $count, $this->id );

		return $this->purchase_count;
	}

	/**
	 * Increase the customer's lifetime value
	 *
	 * @since  2.3
	 * @param  float  $value The value to increase by
	 * @return mixed         If successful, the new value, otherwise false
	 */
	public function increase_value( $value = 0.00 ) {

		$new_value = floatval( $this->purchase_value ) + $value;

		do_action( 'edd_customer_pre_increase_value', $value, $this->id );

		if ( $this->update( array( 'purchase_value' => $new_value ) ) ) {
			$this->purchase_value = $new_value;
		}

		do_action( 'edd_customer_post_increase_value', $this->purchase_value, $value, $this->id );

		return $this->purchase_value;
	}

	/**
	 * Decrease a customer's lifetime value
	 *
	 * @since  2.3
	 * @param  float  $value The value to decrease by
	 * @return mixed         If successful, the new value, otherwise false
	 */
	public function decrease_value( $value = 0.00 ) {

		$new_value = floatval( $this->purchase_value ) - $value;

		if( $new_value < 0 ) {
			$new_value = 0.00;
		}

		do_action( 'edd_customer_pre_decrease_value', $value, $this->id );

		if ( $this->update( array( 'purchase_value' => $new_value ) ) ) {
			$this->purchase_value = $new_value;
		}

		do_action( 'edd_customer_post_decrease_value', $this->purchase_value, $value, $this->id );

		return $this->purchase_value;
	}

	/**
	 * Get the parsed notes for a customer as an array
	 *
	 * @since  2.3
	 * @param  integer $length The number of notes to get
	 * @param  integer $paged What note to start at
	 * @return array           The notes requsted
	 */
	public function get_notes( $length = 20, $paged = 1 ) {

		$length = is_numeric( $length ) ? $length : 20;
		$offset = is_numeric( $paged ) && $paged != 1 ? ( ( absint( $paged ) - 1 ) * $length ) : 0;

		$all_notes   = $this->get_raw_notes();
		$notes_array = array_reverse( array_filter( explode( "\n\n", $all_notes ) ) );

		$desired_notes = array_slice( $notes_array, $offset, $length );

		return $desired_notes;

	}

	/**
	 * Get the total number of notes we have after parsing
	 *
	 * @since  2.3
	 * @return int The number of notes for the customer
	 */
	public function get_notes_count() {

		$all_notes = $this->get_raw_notes();
		$notes_array = array_reverse( array_filter( explode( "\n\n", $all_notes ) ) );

		return count( $notes_array );

	}

	/**
	 * Add a note for the customer
	 *
	 * @since  2.3
	 * @param string $note The note to add
	 * @return string|boolean The new note if added succesfully, false otherwise
	 */
	public function add_note( $note = '' ) {

		$note = trim( $note );
		if ( empty( $note ) ) {
			return false;
		}

		$notes = $this->get_raw_notes();

		if( empty( $notes ) ) {
			$notes = '';
		}

		$note_string = date_i18n( 'F j, Y H:i:s', current_time( 'timestamp' ) ) . ' - ' . $note;
		$new_note    = apply_filters( 'edd_customer_add_note_string', $note_string );
		$notes      .= "\n\n" . $new_note;

		do_action( 'edd_customer_pre_add_note', $new_note, $this->id );

		$updated = $this->update( array( 'notes' => $notes ) );

		if ( $updated ) {
			$this->notes = $this->get_notes();
		}

		do_action( 'edd_customer_post_add_note', $this->notes, $new_note, $this->id );

		// Return the formatted note, so we can test, as well as update any displays
		return $new_note;

	}

	/**
	 * Get the notes column for the customer
	 *
	 * @since  2.3
	 * @return string The Notes for the customer, non-parsed
	 */
	private function get_raw_notes() {

		$all_notes = $this->db->get_column( 'notes', $this->id );

		return $all_notes;

	}

	/**
	 * Sanitize the data for update/create
	 *
	 * @since  2.3
	 * @param  array $data The data to sanitize
	 * @return array       The sanitized data, based off column defaults
	 */
	private function sanitize_columns( $data ) {

		$columns        = $this->db->get_columns();
		$default_values = $this->db->get_column_defaults();

		foreach ( $columns as $key => $type ) {

			// Only sanitize data that we were provided
			if ( ! array_key_exists( $key, $data ) ) {
				continue;
			}

			switch( $type ) {

				case '%s':
					if ( 'email' == $key ) {
						$data[$key] = sanitize_email( $data[$key] );
					} elseif ( 'notes' == $key ) {
						$data[$key] = strip_tags( $data[$key] );
					} else {
						$data[$key] = sanitize_text_field( $data[$key] );
					}
					break;

				case '%d':
					if ( ! is_numeric( $data[$key] ) || (int) $data[$key] !== absint( $data[$key] ) ) {
						$data[$key] = $default_values[$key];
					} else {
						$data[$key] = absint( $data[$key] );
					}
					break;

				case '%f':
					// Convert what was given to a float
					$value = floatval( $data[$key] );

					if ( ! is_float( $value ) ) {
						$data[$key] = $default_values[$key];
					} else {
						$data[$key] = $value;
					}
					break;

				default:
					$data[$key] = sanitize_text_field( $data[$key] );
					break;

			}

		}

		return $data;
	}

}
