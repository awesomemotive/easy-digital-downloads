<?php

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
			'purchase_value' => '%s',
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
			'user_id'      => 0,
			'date_created' => date( 'Y-m-d H:i:s' )
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
			'purchase_value' => 0,
			'purchase_count' => 0,
			'payment_ids'    => array()
		);

		$args = wp_parse_args( $data, $defaults );

		if( empty( $args['email'] ) ) {
			return false;
		}

		if( ! empty( $args['payment_ids'] ) && is_array( $args['payment_ids'] ) ) {
			$args['payment_ids'] = implode( ',', array_unique( array_values( $args['payment_ids'] ) ) );
		}

		$customer = $this->get_by( 'email', $args['email'] );

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
	 * Checks if a customer exists by email
	 *
	 * @access  public
	 * @since   2.1
	*/
	public function exists( $email = '' ) {

		return $this->get_column_by( 'id', 'email', $email );

	}

	/**
	 * Attaches a payment ID to a customer
	 *
	 * @access  public
	 * @since   2.1
	*/
	public function attach_payment( $customer_id = 0, $payment_id = 0 ) {

		$customer = $this->get( $customer_id );

		if( ! $customer ) {
			return false;
		}

		if( empty( $customer->payment_ids ) ) {

			$customer->payment_ids = $payment_id;
		
		} else {

			$payment_ids   = array_map( 'absint', explode( ',', $customer->payment_ids ) );
			$payment_ids[] = $payment_id;
			$customer->payment_ids = implode( ',', array_unique( array_values( $payment_ids ) ) );

		}

		return $this->update( $customer_id, (array) $customer );

	}

	/**
	 * Removes a payment ID from a customer
	 *
	 * @access  public
	 * @since   2.1
	*/
	public function remove_payment( $customer_id = 0, $payment_id = 0 ) {

		$customer = $this->get( $customer_id );

		if( ! $customer ) {
			return false;
		}

		if( ! $payment_id ) {
			return false;
		}

		if( ! empty( $customer->payment_ids ) ) {

			$payment_ids = array_map( 'absint', explode( ',', $customer->payment_ids ) );

			$pos = array_search( $payment_id, $payment_ids );
			if ( false === $pos ) {
				return false;
			}

			array_splice( $payment_ids, $pos, 1 );
			$payment_ids = array_filter( $payment_ids );

			$customer->payment_ids = implode( ',', array_unique( array_values( $payment_ids ) ) );

		}

		return $this->update( $customer_id, (array) $customer );

	}

	/**
	 * Increments customer purchase stats
	 *
	 * @access  public
	 * @since   2.1
	*/
	public function increment_stats( $customer_id = 0, $amount = '' ) {

		$customer = $this->get( $customer_id );

		if( ! $customer ) {
			return false;
		}

		$customer->purchase_count += 1;
		$customer->purchase_value += $amount;

		return $this->update( $customer_id, (array) $customer );

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
			'order'        => 'DESC'
		);

		$args  = wp_parse_args( $args, $defaults );

		if( $args['number'] < 1 ) {
			$args['number'] = 999999999999;
		}

		$where    = '';

		// specific customers
		if( ! empty( $args['id'] ) ) {

			if( is_array( $args['id'] ) ) {
				$ids = implode( ',', $args['id'] );
			} else {
				$ids = intval( $args['id'] );
			}	

			$where .= "WHERE `id` IN( {$ids} ) ";

		}

		// customers for specific user accounts
		if( ! empty( $args['user_id'] ) ) {

			if( is_array( $args['user_id'] ) ) {
				$user_ids = implode( ',', $args['user_id'] );
			} else {
				$user_ids = intval( $args['user_id'] );
			}	

			$where .= "WHERE `user_id` IN( {$user_ids} ) ";

		}

		//specific customers by email
		if( ! empty( $args['email'] ) ) {

			if( is_array( $args['email'] ) ) {
				$emails = "'" . implode( "', '", $args['email'] ) . "'";
			} else {
				$emails = "'" . $args['email'] . "'";
			}	

			$where .= "WHERE `email` IN( {$emails} ) ";

		}

		// Customers created for a specific date or in a date range
		if( ! empty( $args['date'] ) ) {

			if( is_array( $args['date'] ) ) {

				if( ! empty( $args['date']['start'] ) ) {

					$start = date( 'Y-m-d H:i:s', strtotime( $args['date']['start'] ) );

					if( ! empty( $where ) ) {

						$where .= " AND `date_created` >= '{$start}'";
					
					} else {
						
						$where .= " WHERE `date_created` >= '{$start}'";
		
					}

				}

				if( ! empty( $args['date']['end'] ) ) {

					$end = date( 'Y-m-d H:i:s', strtotime( $args['date']['end'] ) );

					if( ! empty( $where ) ) {

						$where .= " AND `date_created` <= '{$end}'";
					
					} else {
						
						$where .= " WHERE `date_created` <= '{$end}'";
		
					}

				}

			} else {

				$year  = date( 'Y', strtotime( $args['date'] ) );
				$month = date( 'm', strtotime( $args['date'] ) );
				$day   = date( 'd', strtotime( $args['date'] ) );

				if( empty( $where ) ) {
					$where .= " WHERE";
				} else {
					$where .= " AND";
				}

				$where .= " $year = YEAR ( date_created ) AND $month = MONTH ( date_created ) AND $day = DAY ( date_created )";
			}

		}

		$cache_key = md5( 'edd_customers_' . serialize( $args ) );

		$customers = wp_cache_get( $cache_key, 'customers' );
		
		if( $customers === false ) {
			$customers = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM  $this->table_name $where ORDER BY {$args['orderby']} {$args['order']} LIMIT %d,%d;", absint( $args['offset'] ), absint( $args['number'] ) ) );
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

		$where = '';

		if( ! empty( $args['date'] ) ) {

			if( is_array( $args['date'] ) ) {

				$start = date( 'Y-m-d H:i:s', strtotime( $args['date']['start'] ) );
				$end   = date( 'Y-m-d H:i:s', strtotime( $args['date']['end'] ) );

				if( empty( $where ) ) {

					$where .= " WHERE `date_created` >= '{$start}' AND `date_created` <= '{$end}'";
				
				} else {
					
					$where .= " AND `date_created` >= '{$start}' AND `date_created` <= '{$end}'";
	
				}

			} else {

				$year  = date( 'Y', strtotime( $args['date'] ) );
				$month = date( 'm', strtotime( $args['date'] ) );
				$day   = date( 'd', strtotime( $args['date'] ) );

				if( empty( $where ) ) {
					$where .= " WHERE";
				} else {
					$where .= " AND";
				}

				$where .= " $year = YEAR ( date_created ) AND $month = MONTH ( date_created ) AND $day = DAY ( date_created )";
			}

		}


		$cache_key = md5( 'edd_customers_count' . serialize( $args ) );

		$count = wp_cache_get( $cache_key, 'customers' );
		
		if( $count === false ) {
			$count = $wpdb->get_var( "SELECT COUNT($this->primary_key) FROM " . $this->table_name . "{$where};" );
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
		`id` bigint(20) NOT NULL AUTO_INCREMENT,
		`user_id` bigint(20) NOT NULL,
		`email` varchar(50) NOT NULL,
		`name` mediumtext NOT NULL,
		`purchase_value` mediumtext NOT NULL,
		`purchase_count` bigint(20) NOT NULL,
		`payment_ids` longtext NOT NULL,
		`notes` longtext NOT NULL,
		`date_created` datetime NOT NULL,
		PRIMARY KEY  (id),
		UNIQUE KEY email (email),
		INDEX user_id (user_id)
		) CHARACTER SET utf8 COLLATE utf8_general_ci;";

		dbDelta( $sql );

		update_option( $this->table_name . '_db_version', $this->version );
	}
}