<?php
/**
 * Customers DB class
 *
 * This class is for interacting with the customers' database table
 *
 * @package     EDD
 * @subpackage  Classes/DB Customers
 * @copyright   Copyright (c) 2015, Pippin Williamson
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       2.1
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * EDD_DB_Customers Class
 *
 * @since 2.1
 */
class EDD_DB_Customers extends EDD_DB  {

	/**
	 * The metadata type.
	 *
	 * @since  2.8
	 * @var string
	 */
	public $meta_type = 'customer';

	/**
	 * The name of the date column.
	 *
	 * @since  2.8
	 * @var string
	 */
	public $date_key = 'date_created';

	/**
	 * The name of the cache group.
	 *
	 * @since  2.8
	 * @var string
	 */
	public $cache_group = 'customers';

	/**
	 * Get things started
	 *
	 * @since   2.1
	*/
	public function __construct() {

		global $wpdb;

		$this->table_name  = $wpdb->prefix . 'edd_customers';
		$this->primary_key = 'id';
		$this->version     = '1.0';

		add_action( 'profile_update', array( $this, 'update_customer_email_on_user_update' ), 10, 2 );

	}

	/**
	 * Get columns and formats
	 *
	 * @since   2.1
	*/
	public function get_columns() {
		return array(
			'id'             => '%d',
			'user_id'        => '%d',
			'name'           => '%s',
			'email'          => '%s',
			'payment_ids'    => '%s',
			'purchase_value' => '%f',
			'purchase_count' => '%d',
			'notes'          => '%s',
			'date_created'   => '%s',
		);
	}

	/**
	 * Get default column values
	 *
	 * @since   2.1
	*/
	public function get_column_defaults() {
		return array(
			'user_id'        => 0,
			'email'          => '',
			'name'           => '',
			'payment_ids'    => '',
			'purchase_value' => 0.00,
			'purchase_count' => 0,
			'notes'          => '',
			'date_created'   => date( 'Y-m-d H:i:s' ),
		);
	}

	/**
	 * Add a customer
	 *
	 * @since   2.1
	*/
	public function add( $data = array() ) {

		$defaults = array(
			'payment_ids' => ''
		);

		$args = wp_parse_args( $data, $defaults );

		if( empty( $args['email'] ) ) {
			return false;
		}

		if( ! empty( $args['payment_ids'] ) && is_array( $args['payment_ids'] ) ) {
			$args['payment_ids'] = implode( ',', array_unique( array_values( $args['payment_ids'] ) ) );
		}

		$customer = $this->get_customer_by( 'email', $args['email'] );

		if( $customer ) {
			// update an existing customer

			// Update the payment IDs attached to the customer
			if( ! empty( $args['payment_ids'] ) ) {

				if( empty( $customer->payment_ids ) ) {

					$customer->payment_ids = $args['payment_ids'];

				} else {

					$existing_ids = array_map( 'absint', explode( ',', $customer->payment_ids ) );
					$payment_ids  = array_map( 'absint', explode( ',', $args['payment_ids'] ) );
					$payment_ids  = array_merge( $payment_ids, $existing_ids );
					$customer->payment_ids = implode( ',', array_unique( array_values( $payment_ids ) ) );

				}

				$args['payment_ids'] = $customer->payment_ids;

			}

			$this->update( $customer->id, $args );

			return $customer->id;

		} else {

			return $this->insert( $args, 'customer' );

		}

	}

	/**
	 * Insert a new customer
	 *
	 * @since   2.1
	 * @return  int
	 */
	public function insert( $data, $type = '' ) {
		$result = parent::insert( $data, $type );

		if ( $result ) {
			$this->set_last_changed();
		}

		return $result;
	}

	/**
	 * Update a customer
	 *
	 * @since   2.1
	 * @return  bool
	 */
	public function update( $row_id, $data = array(), $where = '' ) {
		$result = parent::update( $row_id, $data, $where );

		if ( $result ) {
			$this->set_last_changed();
		}

		return $result;
	}

	/**
	 * Delete a customer
	 *
	 * NOTE: This should not be called directly as it does not make necessary changes to
	 * the payment meta and logs. Use edd_customer_delete() instead
	 *
	 * @since   2.3.1
	*/
	public function delete( $_id_or_email = false ) {

		if ( empty( $_id_or_email ) ) {
			return false;
		}

		$column   = is_email( $_id_or_email ) ? 'email' : 'id';
		$customer = $this->get_customer_by( $column, $_id_or_email );

		if ( $customer->id > 0 ) {

			global $wpdb;

			$result = $wpdb->delete( $this->table_name, array( 'id' => $customer->id ), array( '%d' ) );

			if ( $result ) {
				$this->set_last_changed();
			}

			return $result;

		} else {
			return false;
		}

	}

	/**
	 * Checks if a customer exists
	 *
	 * @since   2.1
	*/
	public function exists( $value = '', $field = 'email' ) {

		$columns = $this->get_columns();
		if ( ! array_key_exists( $field, $columns ) ) {
			return false;
		}

		return (bool) $this->get_column_by( 'id', $field, $value );

	}

	/**
	 * Attaches a payment ID to a customer
	 *
	 * @since   2.1
	*/
	public function attach_payment( $customer_id = 0, $payment_id = 0 ) {

		$customer = new EDD_Customer( $customer_id );

		if( empty( $customer->id ) ) {
			return false;
		}

		// Attach the payment, but don't increment stats, as this function previously did not
		return $customer->attach_payment( $payment_id, false );

	}

	/**
	 * Removes a payment ID from a customer
	 *
	 * @since   2.1
	*/
	public function remove_payment( $customer_id = 0, $payment_id = 0 ) {

		$customer = new EDD_Customer( $customer_id );

		if( ! $customer ) {
			return false;
		}

		// Remove the payment, but don't decrease stats, as this function previously did not
		return $customer->remove_payment( $payment_id, false );

	}

	/**
	 * Increments customer purchase stats
	 *
	 * @since   2.1
	*/
	public function increment_stats( $customer_id = 0, $amount = 0.00 ) {

		$customer = new EDD_Customer( $customer_id );

		if( empty( $customer->id ) ) {
			return false;
		}

		$increased_count = $customer->increase_purchase_count();
		$increased_value = $customer->increase_value( $amount );

		return ( $increased_count && $increased_value ) ? true : false;

	}

	/**
	 * Decrements customer purchase stats
	 *
	 * @since   2.1
	*/
	public function decrement_stats( $customer_id = 0, $amount = 0.00 ) {

		$customer = new EDD_Customer( $customer_id );

		if( ! $customer ) {
			return false;
		}

		$decreased_count = $customer->decrease_purchase_count();
		$decreased_value = $customer->decrease_value( $amount );

		return ( $decreased_count && $decreased_value ) ? true : false;

	}

	/**
	 * Updates the email address of a customer record when the email on a user is updated
	 *
	 * @since   2.4
	*/
	public function update_customer_email_on_user_update( $user_id = 0, $old_user_data ) {

		$customer = new EDD_Customer( $user_id, true );

		if( ! $customer ) {
			return false;
		}

		$user = get_userdata( $user_id );

		if( ! empty( $user ) && $user->user_email !== $customer->email ) {

			if( ! $this->get_customer_by( 'email', $user->user_email ) ) {

				$success = $this->update( $customer->id, array( 'email' => $user->user_email ) );

				if( $success ) {
					// Update some payment meta if we need to
					$payments_array = explode( ',', $customer->payment_ids );

					if( ! empty( $payments_array ) ) {

						foreach ( $payments_array as $payment_id ) {

							edd_update_payment_meta( $payment_id, 'email', $user->user_email );

						}

					}

					do_action( 'edd_update_customer_email_on_user_update', $user, $customer );

				}

			}

		}

	}

	/**
	 * Retrieves a single customer from the database
	 *
	 * @since  2.3
	 * @param  string $column id or email
	 * @param  mixed  $value  The Customer ID or email to search
	 * @return mixed          Upon success, an object of the customer. Upon failure, NULL
	 */
	public function get_customer_by( $field = 'id', $value = 0 ) {
		if ( empty( $field ) || empty( $value ) ) {
			return NULL;
		}

		/**
		 * Filters the Customer before querying the database.
		 *
		 * Return a non-null value to bypass the default query and return early.
		 *
		 * @since 2.9.23
		 *
		 * @param mixed|null $customer               Customer to return instead. Default null to use default method.
		 * @param string     $field                  The field to retrieve by.
		 * @param mixed      $value                  The value to search by.
		 * @param EDD_DB_Customers $edd_customers_db Customer database class.
		 */
		$found = apply_filters( 'edd_pre_get_customer', null, $field, $value, $this );

		if ( null !== $found ) {
			return $found;
		}

		if ( 'id' == $field || 'user_id' == $field ) {
			// Make sure the value is numeric to avoid casting objects, for example,
			// to int 1.
			if ( ! is_numeric( $value ) ) {
				return false;
			}

			$value = intval( $value );

			if ( $value < 1 ) {
				return false;
			}

		} elseif ( 'email' === $field ) {

			if ( ! is_email( $value ) ) {
				return false;
			}

			$value = trim( $value );
		}

		if ( ! $value ) {
			return false;
		}

		$args = array( 'number' => 1 );

		switch ( $field ) {
			case 'id':
				$db_field = 'id';
				$args['include'] = array( $value );
				break;
			case 'email':
				$args['email'] = sanitize_text_field( $value );
				break;
			case 'user_id':
				$args['users_include'] = array( $value );
				break;
			default:
				return false;
		}

		$query = new EDD_Customer_Query( '', $this );

		$results = $query->query( $args );

		$customer = ! empty( $results ) ? array_shift( $results ) : false;

		/**
		 * Filters the single Customer retrieved from the database based on field.
		 *
		 * @since 2.9.23
		 *
		 * @param object|false     $customer         Customer query result. False if no Customer is found.
		 * @param array            $args             Arguments used to query the Customer.
		 * @param EDD_DB_Customers $edd_customers_db Customer database class.
		 */
		$customer = apply_filters( "edd_get_customer_by_{$field}", $customer, $args, $this );

		/**
		 * Filters the single Customer retrieved from the database.
		 *
		 * @since 2.9.23
		 *
		 * @param object|false     $customer         Customer query result. False if no Customer is found.
		 * @param array            $args             Arguments used to query the Customer.
		 * @param EDD_DB_Customers $edd_customers_db Customer database class.
		 */
		$customer = apply_filters( 'edd_get_customer', $customer, $args, $this );

		return $customer;
	}

	/**
	 * Retrieve customers from the database
	 *
	 * @since   2.1
	*/
	public function get_customers( $args = array() ) {
		$args = $this->prepare_customer_query_args( $args );
		$args['count'] = false;

		$query = new EDD_Customer_Query( '', $this );

		return $query->query( $args );
	}


	/**
	 * Count the total number of customers in the database
	 *
	 * @since   2.1
	*/
	public function count( $args = array() ) {
		$args = $this->prepare_customer_query_args( $args );
		$args['count'] = true;
		$args['offset'] = 0;

		$query   = new EDD_Customer_Query( '', $this );
		$results = $query->query( $args );

		return $results;
	}

	/**
	 * Prepare query arguments for `EDD_Customer_Query`.
	 *
	 * This method ensures that old arguments transition seamlessly to the new system.
	 *
	 * @access protected
	 * @since  2.8
	 *
	 * @param array $args Arguments for `EDD_Customer_Query`.
	 * @return array Prepared arguments.
	 */
	protected function prepare_customer_query_args( $args ) {
		if ( ! empty( $args['id'] ) ) {
			$args['include'] = $args['id'];
			unset( $args['id'] );
		}

		if ( ! empty( $args['user_id'] ) ) {
			$args['users_include'] = $args['user_id'];
			unset( $args['user_id'] );
		}

		if ( ! empty( $args['name'] ) ) {
			$args['search'] = '***' . $args['name'] . '***';
			unset( $args['name'] );
		}

		if ( ! empty( $args['date'] ) ) {
			$date_query = array( 'relation' => 'AND' );

			if ( is_array( $args['date'] ) ) {
				$date_query[] = array(
					'after'     => date( 'Y-m-d 00:00:00', strtotime( $args['date']['start'] ) ),
					'inclusive' => true,
				);
				$date_query[] = array(
					'before'    => date( 'Y-m-d 23:59:59', strtotime( $args['date']['end'] ) ),
					'inclusive' => true,
				);
			} else {
				$date_query[] = array(
					'year'  => date( 'Y', strtotime( $args['date'] ) ),
					'month' => date( 'm', strtotime( $args['date'] ) ),
					'day'   => date( 'd', strtotime( $args['date'] ) ),
				);
			}

			if ( empty( $args['date_query'] ) ) {
				$args['date_query'] = $date_query;
			} else {
				$args['date_query'] = array(
					'relation' => 'AND',
					$date_query,
					$args['date_query'],
				);
			}

			unset( $args['date'] );
		}

		return $args;
	}

	/**
	 * Sets the last_changed cache key for customers.
	 *
	 * @since  2.8
	 */
	public function set_last_changed() {
		wp_cache_set( 'last_changed', microtime(), $this->cache_group );
	}

	/**
	 * Retrieves the value of the last_changed cache key for customers.
	 *
	 * @since  2.8
	 */
	public function get_last_changed() {
		if ( function_exists( 'wp_cache_get_last_changed' ) ) {
			return wp_cache_get_last_changed( $this->cache_group );
		}

		$last_changed = wp_cache_get( 'last_changed', $this->cache_group );
		if ( ! $last_changed ) {
			$last_changed = microtime();
			wp_cache_set( 'last_changed', $last_changed, $this->cache_group );
		}

		return $last_changed;
	}

	/**
	 * Create the table
	 *
	 * @since   2.1
	*/
	public function create_table() {

		global $wpdb;

		require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );

		$sql = "CREATE TABLE " . $this->table_name . " (
		id bigint(20) NOT NULL AUTO_INCREMENT,
		user_id bigint(20) NOT NULL,
		email varchar(50) NOT NULL,
		name mediumtext NOT NULL,
		purchase_value mediumtext NOT NULL,
		purchase_count bigint(20) NOT NULL,
		payment_ids longtext NOT NULL,
		notes longtext NOT NULL,
		date_created datetime NOT NULL,
		PRIMARY KEY  (id),
		UNIQUE KEY email (email),
		KEY user (user_id)
		) CHARACTER SET utf8 COLLATE utf8_general_ci;";

		dbDelta( $sql );

		update_option( $this->table_name . '_db_version', $this->version );
	}

}
