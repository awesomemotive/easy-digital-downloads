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
	 * Get things started
	 *
	 * @access  public
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
	 * @access  public
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
	 * @access  public
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
	 * @access  public
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
	 * Delete a customer
	 *
	 * NOTE: This should not be called directly as it does not make necessary changes to
	 * the payment meta and logs. Use edd_customer_delete() instead
	 *
	 * @access  public
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
			return $wpdb->delete( $this->table_name, array( 'id' => $customer->id ), array( '%d' ) );

		} else {
			return false;
		}

	}

	/**
	 * Checks if a customer exists
	 *
	 * @access  public
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
	 * @access  public
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
	 * @access  public
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
	 * @access  public
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
	 * @access  public
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
	 * @access  public
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
	 * @access public
	 * @since  2.3
	 * @param  string $column id or email
	 * @param  mixed  $value  The Customer ID or email to search
	 * @return mixed          Upon success, an object of the customer. Upon failure, NULL
	 */
	public function get_customer_by( $field = 'id', $value = 0 ) {
		global $wpdb;

		if ( empty( $field ) || empty( $value ) ) {
			return NULL;
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

		switch ( $field ) {
			case 'id':
				$db_field = 'id';
				break;
			case 'email':
				$value    = sanitize_text_field( $value );
				$db_field = 'email';
				break;
			case 'user_id':
				$db_field = 'user_id';
				break;
			default:
				return false;
		}

		if ( ! $customer = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM $this->table_name WHERE $db_field = %s LIMIT 1", $value ) ) ) {

			// Look for customer from an additional email
			if( 'email' === $field ) {

				$meta_table  = EDD()->customer_meta->table_name;
				$customer_id = $wpdb->get_var( $wpdb->prepare( "SELECT customer_id FROM $meta_table WHERE meta_key = 'additional_email' AND meta_value = '%s' LIMIT 1", $value ) );

				if( ! empty( $customer_id ) ) {
					return $this->get( $customer_id );
				}

			}


			return false;
		}

		return $customer;
	}

	/**
	 * Retrieve customers from the database
	 *
	 * @access  public
	 * @since   2.1
	*/
	public function get_customers( $args = array() ) {

		global $wpdb;

		$defaults = array(
			'number'       => 20,
			'offset'       => 0,
			'user_id'      => 0,
			'orderby'      => 'id',
			'order'        => 'DESC',
		);

		$args  = wp_parse_args( $args, $defaults );

		if( $args['number'] < 1 ) {
			$args['number'] = 999999999999;
		}

		$join  = '';
		$where = ' WHERE 1=1 ';

		// specific customers
		if( ! empty( $args['id'] ) ) {

			if( is_array( $args['id'] ) ) {
				$ids = implode( ',', array_map('intval', $args['id'] ) );
			} else {
				$ids = intval( $args['id'] );
			}

			$where .= " AND `id` IN( {$ids} ) ";

		}

		// customers for specific user accounts
		if( ! empty( $args['user_id'] ) ) {

			if( is_array( $args['user_id'] ) ) {
				$user_ids = implode( ',', array_map('intval', $args['user_id'] ) );
			} else {
				$user_ids = intval( $args['user_id'] );
			}

			$where .= " AND `user_id` IN( {$user_ids} ) ";

		}

		//specific customers by email
		if( ! empty( $args['email'] ) ) {

			if( is_array( $args['email'] ) ) {

				$emails_count       = count( $args['email'] );
				$emails_placeholder = array_fill( 0, $emails_count, '%s' );
				$emails             = implode( ', ', $emails_placeholder );

				$where .= $wpdb->prepare( " AND `email` IN( $emails ) ", $args['email'] );
			} else {
				$meta_table      = $wpdb->prefix . 'edd_customermeta';
				$customers_table = $this->table_name;

				$join  .= " LEFT JOIN $meta_table ON $customers_table.id = $meta_table.customer_id";
				$where .= $wpdb->prepare( " AND ( ( `meta_key` = 'additional_email' AND `meta_value` = %s ) OR `email` = %s )", $args['email'], $args['email'] );
			}
		}

		// specific customers by name
		if( ! empty( $args['name'] ) ) {
			$where .= $wpdb->prepare( " AND `name` LIKE '%%%%" . '%s' . "%%%%' ", $args['name'] );
		}

		// Customers created for a specific date or in a date range
		if( ! empty( $args['date'] ) ) {

			if( is_array( $args['date'] ) ) {

				if( ! empty( $args['date']['start'] ) ) {

					$start = date( 'Y-m-d 00:00:00', strtotime( $args['date']['start'] ) );
					$where .= " AND `date_created` >= '{$start}'";

				}

				if( ! empty( $args['date']['end'] ) ) {

					$end = date( 'Y-m-d 23:59:59', strtotime( $args['date']['end'] ) );
					$where .= " AND `date_created` <= '{$end}'";

				}

			} else {

				$year  = date( 'Y', strtotime( $args['date'] ) );
				$month = date( 'm', strtotime( $args['date'] ) );
				$day   = date( 'd', strtotime( $args['date'] ) );

				$where .= " AND $year = YEAR ( date_created ) AND $month = MONTH ( date_created ) AND $day = DAY ( date_created )";
			}

		}

		$args['orderby'] = ! array_key_exists( $args['orderby'], $this->get_columns() ) ? 'id' : $args['orderby'];

		if( 'purchase_value' == $args['orderby'] ) {
			$args['orderby'] = 'purchase_value+0';
		}

		$cache_key = md5( 'edd_customers_' . serialize( $args ) );

		$customers = wp_cache_get( $cache_key, 'customers' );

		$args['orderby'] = esc_sql( $args['orderby'] );
		$args['order']   = esc_sql( $args['order'] );

		$customers = false;

		if( $customers === false ) {
			$query     = $wpdb->prepare( "SELECT * FROM  $this->table_name $join $where GROUP BY $this->primary_key ORDER BY {$args['orderby']} {$args['order']} LIMIT %d,%d;", absint( $args['offset'] ), absint( $args['number'] ) );
			$customers = $wpdb->get_results( $query );
			wp_cache_set( $cache_key, $customers, 'customers', 3600 );
		}

		return $customers;

	}


	/**
	 * Count the total number of customers in the database
	 *
	 * @access  public
	 * @since   2.1
	*/
	public function count( $args = array() ) {

		global $wpdb;

		$join  = '';
		$where = ' WHERE 1=1 ';

		// specific customers
		if( ! empty( $args['id'] ) ) {

			if( is_array( $args['id'] ) ) {
				$ids = implode( ',', array_map('intval', $args['id'] ) );
			} else {
				$ids = intval( $args['id'] );
			}

			$where .= " AND `id` IN( {$ids} ) ";

		}

		// customers for specific user accounts
		if( ! empty( $args['user_id'] ) ) {

			if( is_array( $args['user_id'] ) ) {
				$user_ids = implode( ',', array_map('intval', $args['user_id'] ) );
			} else {
				$user_ids = intval( $args['user_id'] );
			}

			$where .= " AND `user_id` IN( {$user_ids} ) ";

		}

		//specific customers by email
		if( ! empty( $args['email'] ) ) {

			if( is_array( $args['email'] ) ) {

				$emails_count       = count( $args['email'] );
				$emails_placeholder = array_fill( 0, $emails_count, '%s' );
				$emails             = implode( ', ', $emails_placeholder );

				$where .= $wpdb->prepare( " AND `email` IN( $emails ) ", $args['email'] );
			} else {
				$meta_table      = $wpdb->prefix . 'edd_customermeta';
				$customers_table = $this->table_name;

				$join  .= " LEFT JOIN $meta_table ON $customers_table.id = $meta_table.customer_id";
				$where .= $wpdb->prepare( " AND ( ( `meta_key` = 'additional_email' AND `meta_value` = %s ) OR `email` = %s )", $args['email'], $args['email'] );
			}
		}

		// specific customers by name
		if( ! empty( $args['name'] ) ) {
			$where .= $wpdb->prepare( " AND `name` LIKE '%%%%" . '%s' . "%%%%' ", $args['name'] );
		}

		// Customers created for a specific date or in a date range
		if( ! empty( $args['date'] ) ) {

			if( is_array( $args['date'] ) ) {

				if( ! empty( $args['date']['start'] ) ) {

					$start = date( 'Y-m-d 00:00:00', strtotime( $args['date']['start'] ) );
					$where .= " AND `date_created` >= '{$start}'";

				}

				if( ! empty( $args['date']['end'] ) ) {

					$end = date( 'Y-m-d 23:59:59', strtotime( $args['date']['end'] ) );
					$where .= " AND `date_created` <= '{$end}'";

				}

			} else {

				$year  = date( 'Y', strtotime( $args['date'] ) );
				$month = date( 'm', strtotime( $args['date'] ) );
				$day   = date( 'd', strtotime( $args['date'] ) );

				$where .= " AND $year = YEAR ( date_created ) AND $month = MONTH ( date_created ) AND $day = DAY ( date_created )";
			}

		}

		$cache_key = md5( 'edd_customers_count' . serialize( $args ) );

		$count = wp_cache_get( $cache_key, 'customers' );

		if( $count === false ) {
			$query = "SELECT COUNT($this->primary_key) FROM " . $this->table_name . "{$join} {$where};";
			$count = $wpdb->get_var( $query);
			wp_cache_set( $cache_key, $count, 'customers', 3600 );
		}

		return absint( $count );

	}

	/**
	 * Create the table
	 *
	 * @access  public
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
