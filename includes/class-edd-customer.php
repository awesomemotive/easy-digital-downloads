<?php
/**
 * Customer Object
 *
 * @package     EDD
 * @subpackage  Customers
 * @copyright   Copyright (c) 2018, Easy Digital Downloads, LLC
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       2.3
 */

// Exit if accessed directly
defined( 'ABSPATH' ) || exit;

/**
 * EDD_Customer Class.
 *
 * @since 2.3
 * @since 3.0 No longer extends EDD_DB_Customer.
 *
 * @property int $id
 * @property int $purchase_count
 * @property float $purchase_value
 * @property array $emails
 * @property string $name
 * @property string $status
 * @property string $date_created
 * @property string $payment_ids
 * @property int $user_id
 * @property string $notes
 */
class EDD_Customer extends \EDD\Database\Rows\Customer {

	/**
	 * Customer ID.
	 *
	 * @since 2.3
	 * @var int
	 */
	public $id = 0;

	/**
	 * The customer's purchase count
	 *
	 * @since 2.3
	 * @var int
	 */
	public $purchase_count = 0;

	/**
	 * Lifetime value of a customer.
	 *
	 * @since 2.3
	 * @var float
	 */
	public $purchase_value = 0;

	/**
	 * Customer's primary email.
	 *
	 * @since 2.3
	 * @var string
	 */
	public $email;

	/**
	 * Email addresses associated with customer.
	 *
	 * @since 2.6
	 * @var array
	 */
	protected $emails;

	/**
	 * Customer's name.
	 *
	 * @since 2.3
	 * @since 3.0 Visibility set to `protected`.
	 * @var string
	 */
	public $name;

	/**
	 * The customer's status
	 *
	 * @since 3.0
	 * @since 3.0 Visibility set to `protected`.
	 * @var string
	 */
	public $status;

	/**
	 * The customer's creation date
	 *
	 * @since 2.3
	 * @since 3.0 Visibility set to `protected`.
	 * @var string
	 */
	public $date_created;

	/**
	 * The payment IDs associated with the customer
	 *
	 * @since 2.3
	 * @var string
	 */
	protected $payment_ids;

	/**
	 * The user ID associated with the customer
	 *
	 * @since 2.3
	 * @since 3.0 Visibility set to `protected`.
	 * @var int
	 */
	public $user_id;

	/**
	 * Notes attached to the customer record.
	 *
	 * @since 2.3
	 * @var string
	 */
	protected $notes;

	/**
	 * Get things going
	 *
	 * @since 2.3
	 */
	public function __construct( $_id_or_email = false, $by_user_id = false ) {
		if ( false === $_id_or_email || ( is_numeric( $_id_or_email ) && absint( $_id_or_email ) !== (int) $_id_or_email ) ) {
			return false;
		}

		$by_user_id = is_bool( $by_user_id ) ? $by_user_id : false;

		if ( is_object( $_id_or_email ) ) {
			$customer = $_id_or_email;
		} else {
			if ( is_numeric( $_id_or_email ) ) {
				$field = $by_user_id ? 'user_id' : 'id';
			} else {
				$field = 'email';
			}

			$customer = edd_get_customer_by( $field, $_id_or_email );
		}

		if ( empty( $customer ) || ! is_object( $customer ) ) {
			return false;
		}

		$this->setup_customer( $customer );
	}

	/**
	 * Given the customer data, let's set the variables
	 *
	 * @since  2.3
	 *
	 * @param  object $customer Customer object.
	 * @return bool True if the object was setup correctly, false otherwise.
	 */
	private function setup_customer( $customer ) {
		if ( ! is_object( $customer ) ) {
			return false;
		}

		foreach ( $customer as $key => $value ) {
			switch ( $key ) {
				case 'purchase_value':
					$this->$key = floatval( $value );
					break;
				case 'purchase_count':
					$this->$key = absint( $value );
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
	 * Magic __get method to dispatch a call to retrieve a protected property.
	 *
	 * @since 3.0
	 *
	 * @param string $key
	 * @return mixed
	 */
	public function __get( $key = '' ) {
		switch ( $key ) {
			case 'emails':
				return $this->get_emails();
			case 'payment_ids':
				$payment_ids = $this->get_order_ids();
				return implode( ',', $payment_ids );
			case 'order_ids':
				return $this->get_order_ids();
			default:
				return isset( $this->{$key} )
					? $this->{$key}
					: edd_get_customer_meta( $this->id, $key );
		}
	}

	/**
	 * Magic __set method to dispatch a call to update a protected property.
	 *
	 * @since 3.0
	 *
	 * @param string $key   Property name.
	 * @param mixed  $value Property value.
	 *
	 * @return mixed Return value of setter being dispatched to.
	 */
	public function __set( $key, $value ) {
		$key = sanitize_key( $key );

		// Only real properties can be saved.
		$keys = array_keys( get_class_vars( get_called_class() ) );

		if ( ! in_array( $key, $keys, true ) ) {
			return false;
		}

		// Dispatch to setter method if value needs to be sanitized.
		if ( method_exists( $this, 'set_' . $key ) ) {
			return call_user_func( array( $this, 'set_' . $key ), $key, $value );
		} else {
			$this->{$key} = $value;
		}
	}

	/**
	 * Creates a customer based on class vars.
	 *
	 * @since 2.3
	 *
	 * @param  array  $data Array of attributes for a customer
	 * @return mixed        False if not a valid creation, Customer ID if user is found or valid creation
	 */
	public function create( $data = array() ) {
		if ( 0 !== $this->id || empty( $data ) ) {
			return false;
		}

		$defaults = array(
			'payment_ids' => '',
		);

		$args = wp_parse_args( $data, $defaults );
		$args = $this->sanitize_columns( $args );

		if ( empty( $args['email'] ) || ! is_email( $args['email'] ) ) {
			return false;
		}

		/**
		 * Fires before a customer is created
		 *
		 * @param array $args Contains customer information such as payment ID, name, and email.
		 */
		do_action( 'edd_customer_pre_create', $args );

		$created = false;

		// Add the customer
		$customer_id = edd_add_customer( $args );

		if ( ! empty( $customer_id ) ) {

			// Add the primary email address for this customer
			edd_add_customer_email_address( array(
				'customer_id' => $customer_id,
				'email'       => $args['email'],
				'type'        => 'primary'
			) );

			// Maybe add payments
			if ( ! empty( $args['payment_ids'] ) && is_array( $args['payment_ids'] ) ) {
				$payment_ids = array_unique( array_values( $args['payment_ids'] ) );

				foreach ( $payment_ids as $payment_id ) {
					edd_update_order( $payment_id, array(
						'customer_id' => $customer_id
					) );
				}
			}

			// We've successfully added/updated the customer, reset the class vars with the new data
			$customer = edd_get_customer( $customer_id );

			// Setup the customer data with the values from DB
			$this->setup_customer( $customer );

			$created = $this->id;
		}

		/**
		 * Fires after a customer is created
		 *
		 * @param int   $created If created successfully, the customer ID.  Defaults to false.
		 * @param array $args Contains customer information such as payment ID, name, and email.
		 */
		do_action( 'edd_customer_post_create', $created, $args );

		return $created;
	}

	/**
	 * Update a customer record.
	 *
	 * @since 2.3
	 *
	 * @param array $data Array of data attributes for a customer (checked via whitelist)
	 * @return bool True if update was successful, false otherwise.
	 */
	public function update( $data = array() ) {
		if ( empty( $data ) ) {
			return false;
		}

		$data = $this->sanitize_columns( $data );

		do_action( 'edd_customer_pre_update', $this->id, $data );

		$updated = false;

		if ( edd_update_customer( $this->id, $data ) ) {
			$customer = edd_get_customer( $this->id );
			$this->setup_customer( $customer );

			$updated = true;
		}

		do_action( 'edd_customer_post_update', $updated, $this->id, $data );

		return $updated;
	}

	/**
	 * Attach an email address to the customer.
	 *
	 * @since 2.6
	 * @since 3.0.1 This method will return customer email ID or false, instead of bool
	 *
	 * @param string $email The email address to remove from the customer.
	 * @param bool   $primary Allows setting the email added as the primary.
	 *
	 * @return int|false ID of newly created customer email address, false on error.
	 */
	public function add_email( $email = '', $primary = false ) {
		if ( ! is_email( $email ) ) {
			return false;
		}

		// Bail if email exists in the universe.
		if ( $this->email_exists( $email ) ) {
			return false;
		}

		do_action( 'edd_customer_pre_add_email', $email, $this->id, $this );

		// Primary or secondary
		$type = ( true === $primary )
			? 'primary'
			: 'secondary';

		// Update is used to ensure duplicate emails are not added.
		$ret = edd_add_customer_email_address(
			array(
				'customer_id' => $this->id,
				'email'       => $email,
				'type'        => $type,
			)
		);

		do_action( 'edd_customer_post_add_email', $email, $this->id, $this );

		if ( $ret && true === $primary ) {
			$this->set_primary_email( $email );
		}

		return $ret;
	}

	/**
	 * Remove an email address from the customer.
	 *
	 * @since 2.6
	 * @since 3.0 Updated to use custom table.
	 *
	 * @param string $email The email address to remove from the customer.
	 * @return bool True if the email was removed successfully, false otherwise.
	 */
	public function remove_email( $email = '' ) {
		if ( ! is_email( $email ) ) {
			return false;
		}

		do_action( 'edd_customer_pre_remove_email', $email, $this->id, $this );

		$email_address = edd_get_customer_email_address_by( 'email', $email );

		$ret = $email_address
			? (bool) edd_delete_customer_email_address( $email_address->id )
			: false;

		do_action( 'edd_customer_post_remove_email', $email, $this->id, $this );

		return $ret;
	}

	/**
	 * Check if an email address already exists somewhere in the known universe
	 * of WordPress Users, or EDD customer email addresses.
	 *
	 * We intentionally skip the edd_customers table, to avoid race conditions
	 * when adding new customers and their email addresses at the same time.
	 *
	 * @since 3.0
	 *
	 * @param string $email Email address to check.
	 * @return boolean True if assigned to existing customer, false otherwise.
	 */
	public function email_exists( $email = '' ) {

		// Bail if not an email address
		if ( ! is_email( $email ) ) {
			return false;
		}

		// Return true if found in users table
		if ( email_exists( $email ) ) {
			return true;
		}

		// Query email addresses table for this address
		$exists = edd_get_customer_email_address_by( 'email' , $email );

		// Return true if found in email addresses table
		if ( ! empty( $exists ) ) {
			return true;
		}

		// Not found
		return false;
	}

	/**
	 * Set an email address as the customer's primary email.
	 *
	 * This will move the customer's previous primary email to an additional email.
	 *
	 * @since 2.6
	 * @param string $new_primary_email The email address to remove from the customer.
	 * @return bool True if the email was set as primary successfully, false otherwise.
	 */
	public function set_primary_email( $new_primary_email = '' ) {

		// Default return value
		$retval = false;

		// Bail if not an email
		if ( ! is_email( $new_primary_email ) ) {
			return $retval;
		}

		do_action( 'edd_customer_pre_set_primary_email', $new_primary_email, $this->id, $this );

		// Bail if already primary
		if ( $new_primary_email === $this->email ) {
			return true;
		}

		// Get customer emails
		$emails = edd_get_customer_email_addresses( array(
			'customer_id' => $this->id
		) );

		// Pluck addresses, to help with in_array() calls
		$plucked = wp_list_pluck( $emails, 'email' );

		// Maybe fix a missing primary email address in the new table
		if ( ! in_array( $this->email, $plucked, true ) ) {

			// Attempt to add the current primary if it's missing
			$added = edd_add_customer_email_address( array(
				'customer_id' => $this->id,
				'email'       => $this->email,
				'type'        => 'primary'
			) );

			// Maybe re-get all customer emails and re-pluck them
			if ( ! empty( $added ) ) {

				// Get customer emails
				$emails = edd_get_customer_email_addresses( array(
					'customer_id' => $this->id
				) );

				// Pluck addresses, and look for the new one
				$plucked = wp_list_pluck( $emails, 'email' );
			}
		}

		// Bail if not an address for this customer
		if ( ! in_array( $new_primary_email, $plucked, true ) ) {
			return $retval;
		}

		// Loop through addresses and juggle them
		foreach ( $emails as $email ) {

			// Make old primary a secondary
			if ( ( 'primary' === $email->type ) && ( $new_primary_email !== $email->email ) ) {
				edd_update_customer_email_address( $email->id, array(
					'type' => 'secondary'
				) );
			}

			// Make new address primary
			if ( ( 'secondary' === $email->type ) && ( $new_primary_email === $email->email ) ) {
				edd_update_customer_email_address( $email->id, array(
					'type' => 'primary'
				) );
			}
		}

		// Mismatch, so update the customer column
		if ( $this->email !== $new_primary_email ) {

			// Update the email column on the customer row
			$this->update( array( 'email' => $new_primary_email ) );

			// Reload the customer emails for this object
			$this->email  = $new_primary_email;
			$this->emails = $this->get_emails();
			$retval       = true;
		}

		do_action( 'edd_customer_post_set_primary_email', $new_primary_email, $this->id, $this );

		return (bool) $retval;
	}

	/**
	 * Before 3.0, when the primary email address was changed, it would cascade
	 * through all previous purchases and update the email address associated
	 * with it. Since 3.0, that is no longer the case.
	 *
	 * This method contains code that is no longer used, and is provided here as
	 * a convenience function if needed.
	 *
	 * @since 3.0
	 */
	public function update_order_email_addresses( $email = '' ) {

		// Get the order IDs.
		$order_ids = $this->get_order_ids();

		// Bail if no orders.
		if ( empty( $payment_ids ) ) {
			return;
		}

		// Update order emails to primary email.
		foreach ( $order_ids as $order_id ) {
			edd_update_order(
				$order_id,
				array(
					'email' => $email,
				)
			);
		}
	}

	/**
	 * Get the payment ids of the customer in an array.
	 *
	 * @since 2.6
	 * @deprecated 3.2 Use the get_order_ids method of the EDD_Customer object instead.
	 *
	 * @return array An array of payment IDs for the customer, or an empty array if none exist.
	 */
	public function get_payment_ids() {
		_edd_deprecated_function( __METHOD__, '3.2', 'EDD_Customer::get_order_ids' );

		return $this->get_order_ids();
	}

	/**
	 * Get an array of EDD_Payment objects from the payment_ids attached to the customer.
	 *
	 * @since 2.6
	 * @deprecated 3.2 Use the get_orders method of the EDD_Customer object instead.
	 *
	 * @param array|string  $status A single status as a string or an array of statuses.
	 *
	 * @return array An array of EDD_Payment objects or an empty array.
	 */
	public function get_payments( $status = array() ) {
		_edd_deprecated_function( __METHOD__, '3.2', 'EDD_Customer::get_orders' );

		// Get payment IDs.
		$payment_ids = $this->get_order_ids( $status );
		$payments    = array();

		// Bail if no IDs.
		if ( empty( $payment_ids ) ) {
			return $payments;
		}

		// Get payments one at a time.
		$payments = edd_get_payments(
			array(
				'number'        => count( $payment_ids ),
				'no_found_rows' => true,
				'id__in'        => $payment_ids,
			)
		);

		return $payments;
	}

	/**
	 * Get the customer's orders
	 *
	 * @since 3.2
	 *
	 * @param array $status The status or statuses of the orders to get.
	 *
	 * @return array An array of EDD\Orders\Order objects.
	 */
	public function get_orders( $status = array() ) {
		$order_args = array(
			'customer_id' => $this->id,
			'type'        => 'sale',
		);

		if ( ! empty( $status ) ) {
			$order_args['status__in'] = $status;
		}

		// Get the order IDs for the user and the total number of orders.
		$order_ids            = $this->get_order_ids( $status );

		// Since the `edd_get_orders` function limits to 30 by default, we need to override that.
		$order_args['number'] = count( $order_ids );

		// Get the customer's orders.
		$orders = edd_get_orders( $order_args );

		return $orders;
	}

	/**
	 * Get the customer's order IDs.
	 *
	 * @since 3.2
	 *
	 * @param array $status The status or statuses of the orders to get.
	 *
	 * @return array An array of order IDs.
	 */
	public function get_order_ids( $status = array() ) {
		// Bail if no customer.
		if ( empty( $this->id ) ) {
			return array();
		}

		// Previously, a string was allowed in other methods, so let's ensure we have an array.
		if ( is_string( $status ) ) {
			$status = (array) $status;
		}

		$count_args = array(
			'customer_id' => $this->id,
			'type'        => 'sale',
		);

		if ( ! empty( $status ) ) {
			$count_args['status__in'] = $status;
		}

		// Get total orders.
		$count = edd_count_orders( $count_args );


		$order_args = array(
			'customer_id'   => $this->id,
			'number'        => $count,
			'fields'        => 'ids',
			'no_found_rows' => true,
			'type'          => 'sale',
		);

		if ( ! empty( $status ) ) {
			$order_args['status__in'] = $status;
		}

		// Get order IDs.
		$ids = edd_get_orders( $order_args );

		// Cast IDs to ints.
		return ! empty( $ids ) ? array_map( 'absint', $ids ) : array();
	}

	/**
	 * Attach payment to the customer then triggers increasing statistics.
	 *
	 * @since 2.3
	 *
	 * @param int  $order_id     The Order ID to attach to the customer.
	 * @param bool $update_stats For backwards compatibility, if we should increase the stats or not.
	 *
	 * @return bool True if the attachment was successfully, false otherwise.
	 */
	public function attach_payment( $order_id = 0, $update_stats = true ) {

		// Bail if no payment ID.
		if ( empty( $order_id ) ) {
			return false;
		}

		// Get order.
		$order = edd_get_order( $order_id );

		// Bail if payment does not exist.
		if ( empty( $order ) ) {
			return false;
		}

		do_action( 'edd_customer_pre_attach_payment', $order->id, $this->id, $this );

		$success = (int) $order->customer_id === (int) $this->id;

		// Update the order if it isn't already attached.
		if ( ! $success ) {
			// Update the order.
			$success = (bool) edd_update_order(
				$order_id,
				array(
					'customer_id' => $this->id,
					'email'       => $this->email,
				)
			);
		}

		// Maybe update stats.
		if ( ! empty( $success ) && ! empty( $update_stats ) ) {
			$this->recalculate_stats();
		}

		do_action( 'edd_customer_post_attach_payment', $success, $order->id, $this->id, $this );

		return $success;
	}

	/**
	 * Remove a payment from this customer, then triggers reducing stats
	 *
	 * @since 2.3
	 * @since 3.2 Updated to use order objects.
	 *
	 * @param integer $order_id     The Order ID to remove.
	 * @param bool    $update_stats For backwards compatibility, if we should increase the stats or not.
	 *
	 * @return bool $detached True if removed successfully, false otherwise.
	 */
	public function remove_payment( $order_id = 0, $update_stats = true ) {

		// Bail if no payment ID.
		if ( empty( $order_id ) ) {
			return false;
		}

		// Get payment.
		$order = edd_get_order( $order_id );

		// Bail if payment does not exist.
		if ( empty( $order ) ) {
			return false;
		}

		// Get all previous payment IDs
		$order_ids = $this->get_order_ids();

		// Bail if already detached.
		if ( ! in_array( $order_id, $order_ids, true ) ) {
			return true;
		}

		// Only update stats when in a completed state.
		if ( ! in_array( $order->status, edd_get_complete_order_statuses(), true ) ) {
			$update_stats = false;
		}

		do_action( 'edd_customer_pre_remove_payment', $order_id, $this->id, $this );

		// Update the order.
		$success = (bool) edd_update_order(
			$order_id,
			array(
				'customer_id' => 0,
				'email'       => '',
			)
		);

		// Maybe update stats.
		if ( ! empty( $success ) && ! empty( $update_stats ) ) {
			$this->recalculate_stats();
		}

		do_action( 'edd_customer_post_remove_payment', $success, $order_id, $this->id, $this );

		return $success;
	}

	/**
	 * Recalculate stats for this customer.
	 *
	 * This replaces the older, less accurate increase/decrease methods.
	 *
	 * @since 3.0
	 */
	public function recalculate_stats() {
		$this->purchase_count = edd_count_orders(
			array(
				'customer_id' => $this->id,
				'status'      => edd_get_net_order_statuses(),
				'type'        => 'sale',
			)
		);

		global $wpdb;
		$statuses      = edd_get_gross_order_statuses();
		$status_string = implode(', ', array_fill( 0, count( $statuses ), '%s' ) );

		$this->purchase_value = (float) $wpdb->get_var( $wpdb->prepare(
			"SELECT SUM(total / rate)
			FROM {$wpdb->edd_orders}
			WHERE customer_id = %d
			AND status IN({$status_string})",
			$this->id,
			...$statuses
		) );

		// Update the customer purchase count & value
		return $this->update(
			array(
				'purchase_count' => $this->purchase_count,
				'purchase_value' => $this->purchase_value,
			)
		);
	}

	/** Notes *****************************************************************/

	/**
	 * Get the parsed notes for a customer as an array.
	 *
	 * @since 2.3
	 * @since 3.0 Use the new Notes component & API.
	 *
	 * @param integer $length The number of notes to get.
	 * @param integer $paged What note to start at.
	 *
	 * @return array The notes requested.
	 */
	public function get_notes( $length = 20, $paged = 1 ) {

		// Number
		$length = is_numeric( $length )
			? absint( $length )
			: 20;

		// Offset
		$offset = is_numeric( $paged ) && ( 1 !== $paged )
			? ( ( absint( $paged ) - 1 ) * $length )
			: 0;

		// Return the paginated notes for back-compat
		return edd_get_notes( array(
			'object_id'   => $this->id,
			'object_type' => 'customer',
			'number'      => $length,
			'offset'      => $offset,
			'order'       => 'desc',
		) );
	}

	/**
	 * Get the total number of notes we have after parsing.
	 *
	 * @since 2.3
	 * @since 3.0 Use the new Notes component & API.
	 *
	 * @return int The number of notes for the customer.
	 */
	public function get_notes_count() {
		return edd_count_notes( array(
			'object_id'   => $this->id,
			'object_type' => 'customer',
		) );
	}

	/**
	 * Add a customer note.
	 *
	 * @since 2.3
	 * @since 3.0 Use the new Notes component & API
	 *
	 * @param string $note The note to add
	 * @return string|boolean The new note if added successfully, false otherwise
	 */
	public function add_note( $note = '' ) {

		// Bail if note content is empty
		$note = trim( $note );
		if ( empty( $note ) ) {
			return false;
		}

		/**
		 * Filter the note of a customer before it's added
		 *
		 * @since 2.3
		 * @since 3.0 No longer includes the datetime stamp
		 *
		 * @param string $note The content of the note to add
		 * @return string
		 */
		$note = apply_filters( 'edd_customer_add_note_string', $note );

		/**
		 * Allow actions before a note is added
		 *
		 * @since 2.3
		 */
		do_action( 'edd_customer_pre_add_note', $note, $this->id, $this );

		// Sanitize note
		$note = trim( wp_kses( stripslashes( $note ), edd_get_allowed_tags() ) );

		// Try to add the note
		edd_add_note( array(
			'user_id'     => 0, // Authored by System/Bot
			'object_id'   => $this->id,
			'object_type' => 'customer',
			'content'     => $note,
		) );

		/**
		 * Allow actions after a note is added
		 *
		 * @since 3.0 Changed to an empty string since notes were moved out
		 */
		do_action( 'edd_customer_post_add_note', '', $note, $this->id, $this );

		// Return the formatted note, so we can test, as well as update any displays
		return $note;
	}

	/** Meta ******************************************************************/

	/**
	 * Retrieve customer meta field for a customer.
	 *
	 * @since 2.6
	 *
	 * @param string  $key    Optional. The meta key to retrieve. By default, returns data for all keys. Default empty.
	 * @param bool    $single Optional, default is false. If true, return only the first value of the specified meta_key.
	 *                        This parameter has no effect if meta_key is not specified.
	 *
	 * @return mixed Will be an array if $single is false. Will be value of meta data field if $single is true.
	 */
	public function get_meta( $key = '', $single = true ) {
		return edd_get_customer_meta( $this->id, $key, $single );
	}

	/**
	 * Add meta data field to a customer.
	 *
	 * @since 2.6
	 *
	 * @param string $meta_key   Meta data name.
	 * @param mixed  $meta_value Meta data value. Must be serializable if non-scalar.
	 * @param bool   $unique     Optional. Whether the same key should not be added. Default false.
	 *
	 * @return int|false Meta ID on success, false on failure.
	 */
	public function add_meta( $meta_key = '', $meta_value = '', $unique = false ) {
		return edd_add_customer_meta( $this->id, $meta_key, $meta_value, $unique );
	}

	/**
	 * Update customer meta field based on customer ID.
	 *
	 * Use the $prev_value parameter to differentiate between meta fields with the
	 * same key and order ID.
	 *
	 * If the meta field for the order does not exist, it will be added.
	 *
	 * @since 2.6
	 *
	 * @param string $meta_key   Meta data key.
	 * @param mixed  $meta_value Meta data value. Must be serializable if non-scalar.
	 * @param mixed  $prev_value Optional. Previous value to check before removing. Default empty.
	 *
	 * @return int|bool Meta ID if the key didn't exist, true on successful update, false on failure.
	 */
	public function update_meta( $meta_key = '', $meta_value = '', $prev_value = '' ) {
		return edd_update_customer_meta( $this->id, $meta_key, $meta_value, $prev_value );
	}

	/**
	 * Remove meta data matching criteria from a customer.
	 *
	 * You can match based on the key, or key and value. Removing based on key and value, will keep from removing duplicate
	 * meta data with the same key. It also allows removing all meta data matching key, if needed.
	 *
	 * @since 2.6
	 *
	 * @param string $meta_key   Meta data name.
	 * @param mixed  $meta_value Optional. Meta data value. Must be serializable if non-scalar. Default empty.
	 *
	 * @return bool True on success, false on failure.
	 */
	public function delete_meta( $meta_key = '', $meta_value = '' ) {
		return edd_delete_customer_meta( $this->id, $meta_key, $meta_value );
	}

	/** Private ***************************************************************/

	/**
	 * Sanitize the data for update/create.
	 *
	 * @since 2.3
	 *
	 * @param array $data The data to sanitize.
	 * @return array The sanitized data, based off column defaults.
	 */
	private function sanitize_columns( $data = array() ) {
		$default_values = array();

		foreach ( $data as $key => $type ) {

			// Only sanitize data that we were provided
			if ( ! array_key_exists( $key, $data ) ) {
				continue;
			}

			switch ( $type ) {
				case '%s':
					if ( 'email' === $key ) {
						$data[ $key ] = sanitize_email( $data[ $key ] );
					} else {
						$data[ $key ] = sanitize_text_field( $data[ $key ] );
					}
					break;

				case '%d':
					if ( ! is_numeric( $data[ $key ] ) || absint( $data[ $key ] ) !== (int) $data[ $key ] ) {
						$data[ $key ] = $default_values[ $key ];
					} else {
						$data[ $key ] = absint( $data[ $key ] );
					}
					break;

				case '%f':
					// Convert what was given to a float
					$value = floatval( $data[ $key ] );

					if ( ! is_float( $value ) ) {
						$data[ $key ] = $default_values[ $key ];
					} else {
						$data[ $key ] = $value;
					}
					break;

				default:
					$data[ $key ] = sanitize_text_field( $data[ $key ] );
					break;
			}
		}

		return $data;
	}

	/** Helpers ***************************************************************/

	/**
	 * Retrieve all of the IP addresses used by the customer.
	 *
	 * @since 3.0
	 *
	 * @return array Array of objects containing IP address.
	 */
	public function get_ips() {
		return edd_get_orders( array(
			'customer_id' => $this->id,
			'fields'      => 'ip',
			'groupby'     => 'ip',
		) );
	}

	/**
	 * Retrieve all the email addresses associated with this customer.
	 *
	 * @since 3.0
	 *
	 * @return array
	 */
	public function get_emails() {

		// Add primary email.
		$retval = array( $this->email );

		// Fetch email addresses from the database.
		$emails = edd_get_customer_email_addresses( array(
			'customer_id' => $this->id
		) );

		// Pluck addresses and merg them
		if ( ! empty( $emails ) ) {

			// We only want the email addresses
			$emails = wp_list_pluck( $emails, 'email' );

			// Merge with primary email
			$retval = array_merge( $retval, $emails );
		}

		// Return unique results (to avoid duplicates)
		return array_unique( $retval );
	}

	/**
	 * Retrieve an address.
	 *
	 * @since 3.0
	 *
	 * @param boolean $is_primary Whether the address is the primary address. Default true.
	 *
	 * @return array|\EDD\Customers\Customer_Address|null Object if primary address requested, array otherwise. Null if no result for primary address.
	 */
	public function get_address( $is_primary = true ) {
		$args = array(
			'customer_id' => $this->id,
			'is_primary'  => $is_primary,
		);
		if ( $is_primary ) {
			$args['number']  = 1;
			$args['orderby'] = 'date_created';
			$args['order']   = 'desc';
		}
		$address = edd_get_customer_addresses( $args );
		if ( ! $is_primary ) {
			return $address;
		}
		if ( is_array( $address ) && ! empty( $address[0] ) ) {
			return $address[0];
		}

		return null;
	}

	/**
	 * Retrieve all addresses.
	 *
	 * @since 3.0
	 *
	 * @param string $type Address type. Default empty.
	 *
	 * @return \EDD\Customers\Customer_Address[] Array of addresses.
	 */
	public function get_addresses( $type = '' ) {
		$addresses = edd_get_customer_addresses( array(
			'customer_id' => $this->id,
		) );

		if ( ! empty( $type ) ) {
			$addresses = wp_filter_object_list( $addresses, array( 'type' => $type ) );
		}

		return $addresses;
	}

	/** Deprecated ************************************************************/

	/**
	 * Increase the purchase count of a customer.
	 *
	 * @since 2.3
	 * @deprecated 3.0 Use recalculate_stats()
	 *
	 * @param int $count The number to increment purchase count by. Default 1.
	 * @return int New purchase count.
	 */
	public function increase_purchase_count( $count = 1 ) {

		_edd_deprecated_function( __METHOD__, '3.0', 'EDD_Customer::recalculate_stats()' );

		// Make sure it's numeric and not negative
		if ( ! is_numeric( $count ) || absint( $count ) !== $count ) {
			return false;
		}

		$new_total = (int) $this->purchase_count + (int) $count;

		do_action( 'edd_customer_pre_increase_purchase_count', $count, $this->id, $this );

		if ( $this->update( array( 'purchase_count' => $new_total ) ) ) {
			$this->purchase_count = $new_total;
		}

		do_action( 'edd_customer_post_increase_purchase_count', $this->purchase_count, $count, $this->id, $this );

		return $this->purchase_count;
	}

	/**
	 * Decrease the customer's purchase count.
	 *
	 * @since 2.3
	 * @deprecated 3.0 Use recalculate_stats()
	 *
	 * @param int $count The number to decrement purchase count by. Default 1.
	 * @return mixed New purchase count if successful, false otherwise.
	 */
	public function decrease_purchase_count( $count = 1 ) {

		_edd_deprecated_function( __METHOD__, '3.0', 'EDD_Customer::recalculate_stats()' );

		// Make sure it's numeric and not negative
		if ( ! is_numeric( $count ) || absint( $count ) !== $count ) {
			return false;
		}

		$new_total = (int) $this->purchase_count - (int) $count;

		if ( $new_total < 0 ) {
			$new_total = 0;
		}

		do_action( 'edd_customer_pre_decrease_purchase_count', $count, $this->id, $this );

		if ( $this->update( array( 'purchase_count' => $new_total ) ) ) {
			$this->purchase_count = $new_total;
		}

		do_action( 'edd_customer_post_decrease_purchase_count', $this->purchase_count, $count, $this->id, $this );

		return $this->purchase_count;
	}

	/**
	 * Increase the customer's lifetime value.
	 *
	 * @since 2.3
	 * @deprecated 3.0 Use recalculate_stats()
	 *
	 * @param float $value The value to increase by.
	 * @return mixed New lifetime value if successful, false otherwise.
	 */
	public function increase_value( $value = 0.00 ) {

		_edd_deprecated_function( __METHOD__, '3.0', 'EDD_Customer::recalculate_stats()' );

		$value     = floatval( apply_filters( 'edd_customer_increase_value', $value, $this ) );
		$new_value = floatval( $this->purchase_value ) + $value;

		do_action( 'edd_customer_pre_increase_value', $value, $this->id, $this );

		if ( $this->update( array( 'purchase_value' => $new_value ) ) ) {
			$this->purchase_value = $new_value;
		}

		do_action( 'edd_customer_post_increase_value', $this->purchase_value, $value, $this->id, $this );

		return $this->purchase_value;
	}

	/**
	 * Decrease a customer's lifetime value.
	 *
	 * @since 2.3
	 * @deprecated 3.0 Use recalculate_stats()
	 *
	 * @param float $value The value to decrease by.
	 * @return mixed New lifetime value if successful, false otherwise.
	 */
	public function decrease_value( $value = 0.00 ) {

		_edd_deprecated_function( __METHOD__, '3.0', 'EDD_Customer::recalculate_stats()' );

		$value     = floatval( apply_filters( 'edd_customer_decrease_value', $value, $this ) );
		$new_value = floatval( $this->purchase_value ) - $value;

		if ( $new_value < 0 ) {
			$new_value = 0.00;
		}

		do_action( 'edd_customer_pre_decrease_value', $value, $this->id, $this );

		if ( $this->update( array( 'purchase_value' => $new_value ) ) ) {
			$this->purchase_value = $new_value;
		}

		do_action( 'edd_customer_post_decrease_value', $this->purchase_value, $value, $this->id, $this );

		return $this->purchase_value;
	}
}
