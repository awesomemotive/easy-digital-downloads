<?php

/**
 * EDD Order Class
 *
 * @package Plugins/EDD/Database/Objects/Order
 */

// Exit if accessed directly
defined( 'ABSPATH' ) || exit;

/**
 * Order Class
 *
 * @since 3.0.0
 */
final class EDD_Order {

	/**
	 * Order ID.
	 *
	 * @since 3.0.0
	 * @access public
	 * @var int
	 */
	public $order_id;

	/**
	 * The ID of the order's object.
	 *
	 * @since 3.0.0
	 * @access public
	 * @var string
	 */
	public $object_id = 0;

	/**
	 * Type of order.
	 *
	 * @since 3.0.0
	 * @access public
	 * @var string
	 */
	public $object_type = 'post';

	/**
	 * The date on which the order was created.
	 *
	 * @since 3.0.0
	 * @access public
	 * @var string Date in MySQL's datetime format.
	 */
	public $order_start = '';

	/**
	 * The date on which the order was created.
	 *
	 * @since 3.0.0
	 * @access public
	 * @var string Date in MySQL's datetime format.
	 */
	public $order_end = '';

	/**
	 * Time zone.
	 *
	 * @since 3.0.0
	 * @access public
	 * @var string
	 */
	public $order_tz = '';

	/**
	 * All day.
	 *
	 * @since 3.0.0
	 * @access public
	 * @var bool
	 */
	public $order_all_day = false;

	/**
	 * Type of order recurrence.
	 *
	 * @since 3.0.0
	 * @access public
	 * @var string
	 */
	public $order_recurrence = null;

	/**
	 * The recurrence end date and time in ISO 8601 date format.
	 *
	 * @since 3.0.0
	 * @access public
	 * @var mixed null|DateTime
	 */
	public $order_recurrence_end = null;

	/**
	 * Creates a new EDD_Order object.
	 *
	 * Will populate object properties from the object provided and assign other
	 * default properties based on that information.
	 *
	 * @since 3.0.0
	 * @access public
	 *
	 * @param EDD_Order|object $order An order object.
	 */
	public function __construct( $order = null ) {

		// Bail if no order data
		if ( empty( $order ) ) {
			return;
		}

		// Set object vars
		foreach ( get_object_vars( (object) $order ) as $key => $value ) {
			$this->{$key} = $value;
		}
	}

	/**
	 * Converts an object to array.
	 *
	 * @since 3.0.0
	 * @access public
	 *
	 * @return array Object as array.
	 */
	public function to_array() {
		return get_object_vars( $this );
	}

	/**
	 * Getter.
	 *
	 * @since 3.0.0
	 * @access public
	 *
	 * @param string $key Property to get.
	 * @return mixed Value of the property. Null if not available.
	 */
	public function __get( $key = '' ) {
		switch ( $key ) {
			case 'id' :
			case 'ID' :
				return (int) $this->order_id;
			case 'object_id' :
				return (int) $this->object_id;
			case 'object_type' :
				return sanitize_key( $this->object_id );
			case 'order_start' :
				return $this->order_start;
			case 'order_end' :
				return $this->order_end;
			case 'order_tz' :
				return $this->order_tz;
			case 'order_all_day' :
				return (bool) $this->order_all_day;
			default :
				return get_order_meta( $this->order_id, $key, true );
		}

		return null;
	}

	/**
	 * Isset-er.
	 *
	 * Allows current multisite naming conventions when checking for properties.
	 * Checks for extended site properties.
	 *
	 * @since 3.0.0
	 * @access public
	 *
	 * @param string $key Property to check if set.
	 * @return bool Whether the property is set.
	 */
	public function __isset( $key = '' ) {
		switch ( $key ) {
			case 'id' :
			case 'order_id' :
			case 'object_id' :
			case 'object_type' :
				return true;
		}

		return isset( $this->{$key} );
	}

	/**
	 * Setter.
	 *
	 * Allows current multisite naming conventions while setting properties.
	 *
	 * @since 3.0.0
	 * @access public
	 *
	 * @param string $key   Property to set.
	 * @param mixed  $value Value to assign to the property.
	 */
	public function __set( $key, $value ) {
		switch ( $key ) {
			case 'id' :
			case 'ID' :
				$this->order_id = (int) $value;
				break;
			case 'object_id' :
				$this->object_id = (int) $value;
				break;
			case 'object_type' :
				$this->object_type = sanitize_key( $value );
				break;
			case 'order_all_day' :
				$this->order_all_day = (bool) $value;
				break;
			default:
				$this->{$key} = $value;
		}
	}

	/**
	 * Update the order
	 *
	 * @since 3.0.0
	 *
	 * @param array|stdClass $data Order fields (associative array or object properties)
	 *
	 * @return bool|WP_Error True if we updated, false if we didn't need to, or WP_Error if an error occurred
	 */
	public function update( $data = array() ) {
		global $wpdb;

		$data    = (array) $data;
		$fields  = array();
		$formats = array();

		// Object ID is changed
		if ( ! empty( $data['object_id'] ) && ( $this->object_id !== $data['object_id'] ) ) {
			$fields['object_id'] = absint( $data['object_id'] );
			$formats[]           = '%d';
		}

		// Object Type is changed
		if ( ! empty( $data['object_type'] ) && ( $this->object_type !== $data['object_type'] ) ) {
			$fields['object_type'] = sanitize_key( $data['object_type'] );
			$formats[]             = '%s';
		}

		// Order Start is changed
		if ( ! empty( $data['order_start'] ) && ( $this->order_start !== $data['order_start'] ) ) {
			$fields['order_start'] = $data['order_start'];
			$formats[]             = '%s';
		}

		// Order End is changed
		if ( ! empty( $data['order_end'] ) && ( $this->order_end !== $data['order_end'] ) ) {
			$fields['order_end'] = $data['order_end'];
			$formats[]           = '%s';
		}

		// Order Timezone is changed
		if ( ! empty( $data['order_tz'] ) && ( $this->order_tz !== $data['order_tz'] ) ) {
			$fields['order_tz'] = (int) $data['order_tz'];
			$formats[]          = '%d';
		}


		// Order All Day is changed
		if ( ! empty( $data['order_all_day'] ) && ( $this->order_tz !== $data['order_all_day'] ) ) {
			$fields['order_all_day'] = (bool) $data['order_all_day'];
			$formats[]               = '%d';
		}

		// Do we have things to update?
		if ( empty( $fields ) ) {
			return false;
		}

		$order_id     = $this->order_id;
		$where        = array( 'order_id' => $order_id );
		$where_format = array( '%d' );
		$result       = $wpdb->update( $wpdb->orders, $fields, $where, $formats, $where_format );

		if ( empty( $result ) && ! empty( $wpdb->last_error ) ) {
			return new WP_Error( 'wp_orders_order_update_failed' );
		}

		// Clone this object
		$old_order = clone( $this );

		// Update internal state
		foreach ( $fields as $key => $val ) {
			$this->{$key} = $val;
		}

		// Update the order caches
		wp_cache_set( $order_id, $this, 'orders' );

		/**
		 * Fires after a order has been updated.
		 *
		 * @param  EDD_Order  $order  The order object.
		 * @param  EDD_Order  $order  The previous order object.
		 */
		do_action( 'wp_orders_updated', $this, $old_order );

		return true;
	}

	/**
	 * Delete the order
	 *
	 * @since 3.0.0
	 *
	 * @return bool|WP_Error True if we updated, false if we didn't need to, or WP_Error if an error occurred
	 */
	public function delete() {
		global $wpdb;

		// Try to delete the order
		$order_id     = $this->order_id;
		$where        = array( 'id' => $order_id );
		$where_format = array( '%d' );
		$result       = $wpdb->delete( $wpdb->orders, $where, $where_format );

		// Bail if no order to delete
		if ( empty( $result ) ) {
			return new WP_Error( 'wp_orders_order_delete_failed' );
		}

		// Delete order meta
		$order_meta_ids = $wpdb->get_col( $wpdb->prepare( "SELECT meta_id FROM {$wpdb->ordermeta} WHERE order_id = %d", $order_id ) );
		foreach ( $order_meta_ids as $mid ) {
			delete_metadata_by_mid( 'order', $mid );
		}

		// Delete the blog order cache
		clean_order_cache( $this );

		/**
		 * Fires after a order has been delete.
		 *
		 * @param  EDD_Order  $order The order object.
		 */
		do_action( 'wp_orders_deleted', $this );

		return true;
	}

	/**
	 * Retrieves a order from the database by its ID.
	 *
	 * @static
	 * @since 3.0.0
	 * @access public
	 *
	 * @global wpdb $wpdb WordPress database abstraction object.
	 *
	 * @param int $order The ID of the site to retrieve.
	 * @return EDD_Order|false The order object if found. False if not.
	 */
	public static function get_instance( $order = 0 ) {
		global $wpdb;

		// Allow passing a order in
		if ( $order instanceof EDD_Order ) {
			return $order;
		}

		// Bail if
		if ( ! is_numeric( $order ) ) {
			return new WP_Error( 'wp_orders_invalid_id' );
		}

		// Check cache first
		$_order = wp_cache_get( $order, 'orders' );

		// No cached order
		if ( false === $_order ) {
			$_order = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM {$wpdb->orders} WHERE order_id = %d LIMIT 1", $order ) );

			// Bail if no order found
			if ( empty( $_order ) || is_wp_error( $_order ) ) {
				return false;
			}

			// Add order to cache
			wp_cache_add( $order, $_order, 'orders' );
		}

		// Return order object
		return new EDD_Order( $_order );
	}

	/**
	 * Get order by object ID & type
	 *
	 * @since 3.0.0
	 *
	 * @param int    $object_id
	 * @param string $object_type
	 *
	 * @return EDD_Order|WP_Error|null Order on success, WP_Error if error occurred, or null if no order found
	 */
	public static function get_for_object( $object_id = 0, $object_type = 'post' ) {

		// Get orders
		$orders = new EDD_Order_Query( array(
			'object_id'   => $object_id,
			'object_type' => $object_type,
			'number'      => 1
		) );

		// Bail if no orders
		if ( empty( $orders->found_orders ) ) {
			return new EDD_Order();
		}

		// Return the first order
		return reset( $orders->orders );
	}

	/**
	 * Create a new order
	 *
	 * @param array|stdClass $data Order fields (associative array or object properties)
	 *
	 * @return bool|WP_Error True if we updated, false if we didn't need to, or WP_Error if an error occurred
	 */
	public static function create( $data = array() ) {
		global $wpdb;

		// Parse
		$r = wp_parse_args( $data, array(
			'object_id'        => 0,
			'object_type'      => 'post',
			'order_start'      => '0000-00-00 00:00:00',
			'order_end'        => '0000-00-00 00:00:00',
			'order_tz'         => '',
			'order_all_day'    => 0,
			'order_recurrence' => ''
		) );

		// Sanitize
		$r['object_type']   = sanitize_key( $r['object_type'] );
		$r['order_all_day'] = (bool) $r['order_all_day'];

		// Does an order exist already?
		$existing = static::get_for_object( $r['object_id'], $r['object_type'] );
		if ( is_wp_error( $existing ) ) {
			return $existing;
		} elseif ( ! empty( $existing ) ) {
			return new WP_Error( 'wp_orders_order_domain_exists', esc_html__( 'That order is already in use.', 'wp-site-orders' ) );
		}

		// Table format
		$f = array( '%d', '%s', '%s', '%s', '%s', '%d', '%s' );

		// Create the order!
		$prev_errors = ! empty( $GLOBALS['EZSQL_ERROR'] ) ? $GLOBALS['EZSQL_ERROR'] : array();
		$suppress    = $wpdb->suppress_errors( true );
		$result      = $wpdb->insert( $wpdb->orders, $r, $f );

		$wpdb->suppress_errors( $suppress );

		// Other error. We suppressed errors before, so we need to make sure
		// we handle that now.
		if ( empty( $result ) ) {
			$recent_errors = array_diff_key( $GLOBALS['EZSQL_ERROR'], $prev_errors );

			while ( count( $recent_errors ) > 0 ) {
				$error = array_shift( $recent_errors );
				$wpdb->print_error( $error['error_str'] );
			}

			return new WP_Error( 'wp_orders_order_insert_failed' );
		}

		// Ensure the cache is flushed
		clean_order_cache( $wpdb->insert_id );

		// Get the order, and prime the caches
		$order = static::get_instance( $wpdb->insert_id );

		/**
		 * Fires after a order has been created.
		 *
		 * @param  EDD_Order  $order  The order object.
		 */
		do_action( 'wp_orders_created', $order );

		return $order;
	}
}
