<?php
/**
 * Backwards Compatibility Handler for Customers.
 *
 * @package     EDD
 * @subpackage  Compat
 * @copyright   Copyright (c) 2018, Easy Digital Downloads, LLC
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       3.0
 */
namespace EDD\Compat;

use EDD\Database\Table;

// Exit if accessed directly
defined( 'ABSPATH' ) || exit;

/**
 * Customer Class.
 *
 * @since 3.0
 */
class Customer extends Base {

	/**
	 * Holds the component for which we are handling back-compat. There is a chance that two methods have the same name
	 * and need to be dispatched to completely other methods. When a new instance of Back_Compat is created, a component
	 * can be passed to the constructor which will allow __call() to dispatch to the correct methods.
	 *
	 * @since 3.0
	 * @access protected
	 * @var string
	 */
	protected $component = 'customer';

	/**
	 * Magic method to handle calls to properties that no longer exist.
	 *
	 * @since 3.0
	 *
	 * @param string $property Name of the property.
	 *
	 * @return mixed
	 */
	public function __get( $property ) {
		switch( $property ) {
			case 'table_name' :
				global $wpdb;
				return $wpdb->edd_customers;

			case 'primary_key' :
				return 'id';

			case 'version' :
				$table = edd_get_component_interface( 'customer', 'table' );

				return $table instanceof Table ? $table->get_version() : false;
			case 'meta_type' :
				return 'customer';

			case 'date_key' :
				return 'date_created';

			case 'cache_group' :
				return 'customers';
		}

		return null;
	}

	/**
	 * Magic method to handle calls to method that no longer exist.
	 *
	 * @since 3.0
	 *
	 * @param string $name      Name of the method.
	 * @param array  $arguments Enumerated array containing the parameters passed to the $name'ed method.
	 * @return mixed Dependent on the method being dispatched to.
	 */
	public function __call( $name, $arguments ) {
		switch ( $name ) {
			case 'add':
			case 'insert':
				return edd_add_customer( $arguments[0] );

			case 'update':
				return edd_update_customer( $arguments[0], $arguments[1] );

			case 'delete':
				if ( ! is_bool( $arguments[0] ) ) {
					return false;
				}

				$column = is_email( $arguments[0] ) ? 'email' : 'id';
				$customer = edd_get_customer_by( $column, $arguments[0] );
				edd_delete_customer( $customer->id );
				break;
			case 'exists':
				return (bool) edd_get_customer_by( 'email', $arguments[0] );

			case 'get_customer_by':
				return edd_get_customer_by( $arguments[0], $arguments[1] );

			case 'get_customers':
				return edd_get_customers( $arguments[0] );

			case 'count':
				return edd_count_customers();

			case 'get_column':
				return edd_get_customer_by( $arguments[0], $arguments[1] );

			case 'attach_payment':
				/** @var $customer \EDD_Customer */
				$customer = edd_get_customer( $arguments[0] );

				if ( ! $customer ) {
					return false;
				}

				return $customer->attach_payment( $arguments[1], false );

			case 'remove_payment':
				/** @var $customer \EDD_Customer */
				$customer = edd_get_customer( $arguments[0] );

				if ( ! $customer ) {
					return false;
				}

				return $customer->remove_payment( $arguments[1], false );

			case 'increment_stats':
				/** @var $customer \EDD_Customer */
				$customer = edd_get_customer( $arguments[0] );

				if ( ! $customer ) {
					return false;
				}

				$increased_count = $customer->increase_purchase_count();
				$increased_value = $customer->increase_value( $arguments[1] );

				return ( $increased_count && $increased_value )
					? true
					: false;

			case 'decrement_stats':
				/** @var $customer \EDD_Customer */
				$customer = edd_get_customer( $arguments[0] );

				if ( ! $customer ) {
					return false;
				}

				$decreased_count = $customer->decrease_purchase_count();
				$decreased_value = $customer->decrease_value( $arguments[1] );

				return ( $decreased_count && $decreased_value )
					? true
					: false;
		}
	}

	/**
	 * Backwards compatibility hooks for customers.
	 *
	 * @since 3.0
	 * @access protected
	 */
	protected function hooks() {

		/** Filters **********************************************************/

		add_filter( 'get_user_metadata',    array( $this, 'get_user_meta'    ), 99, 4 );
		add_filter( 'update_user_metadata', array( $this, 'update_user_meta' ), 99, 5 );
		add_filter( 'add_user_metadata',    array( $this, 'update_user_meta' ), 99, 5 );
	}

	/**
	 * Backwards compatibility filters for get_user_meta() calls on customers.
	 *
	 * @since 3.0
	 *
	 * @param mixed  $value     The value get_post_meta would return if we don't filter.
	 * @param int    $object_id The object ID post meta was requested for.
	 * @param string $meta_key  The meta key requested.
	 * @param bool   $single    If a single value or an array of the value is requested.
	 *
	 * @return mixed The value to return.
	 */
	public function get_user_meta( $value, $object_id, $meta_key, $single ) {
		if ( 'get_user_metadata' !== current_filter() ) {
			$message = __( 'This function is not meant to be called directly. It is only here for backwards compatibility purposes.', 'easy-digital-downloads' );
			_doing_it_wrong( __FUNCTION__, esc_html( $message ), 'EDD 3.0' );
		}

		if ( '_edd_user_address' !== $meta_key ) {
			return $value;
		}

		$value = edd_get_customer_address( $object_id );

		if ( $this->show_notices ) {
			_doing_it_wrong( 'get_user_meta()', 'User addresses being stored in meta have been <strong>deprecated</strong> since Easy Digital Downloads 3.0! Use <code>edd_get_customer_address()</code> instead.', 'EDD 3.0' );

			if ( $this->show_backtrace ) {
				$backtrace = debug_backtrace();
				trigger_error( print_r( $backtrace, 1 ) );
			}
		}

		return array( $value );
	}

	/**
	 * Listen for calls to update_user_meta() for customers and see if we need to filter them.
	 *
	 * This is here for backwards compatibility purposes with the migration to custom tables in EDD 3.0.
	 *
	 * @since 3.0
	 *
	 * @param null|bool $check      Whether to allow updating metadata for the given type.
	 * @param int       $object_id  Object ID.
	 * @param string    $meta_key   Meta key.
	 * @param mixed     $meta_value Meta value. Must be serializable if non-scalar.
	 * @param mixed     $prev_value Optional. If specified, only update existing metadata entries with the specified value.
	 *                              Otherwise, update all entries.
	 *
	 * @return mixed Returns 'null' if no action should be taken and WordPress core can continue, or non-null to avoid usermeta.
	 */
	public function update_user_meta( $check, $object_id, $meta_key, $meta_value, $prev_value ) {
		if ( '_edd_user_address' !== $meta_key ) {
			return $check;
		}

		// Fetch saved primary address.
		$addresses = edd_get_customer_addresses(
			array(
				'number'      => 1,
				'is_primary'  => true,
				'customer_id' => $object_id,
			)
		);

		// Defaults.
		$defaults = array(
			'line1'   => '',
			'line2'   => '',
			'city'    => '',
			'state'   => '',
			'country' => '',
			'zip'     => '',
		);

		$address = wp_parse_args( (array) $meta_value, $defaults );

		if ( is_array( $addresses ) && ! empty( $addresses[0] ) ) {
			$customer_address = $addresses[0];

			edd_update_customer_address(
				$customer_address->id,
				array(
					'address'     => $address['line1'],
					'address2'    => $address['line2'],
					'city'        => $address['city'],
					'region'      => $address['state'],
					'postal_code' => $address['zip'],
					'country'     => $address['country'],
				)
			);
		} else {
			$customer = edd_get_customer_by( 'user_id', absint( $object_id ) );

			if ( $customer ) {
				edd_add_customer_address(
					array(
						'customer_id' => $customer->id,
						'address'     => $address['line1'],
						'address2'    => $address['line2'],
						'city'        => $address['city'],
						'region'      => $address['state'],
						'postal_code' => $address['zip'],
						'country'     => $address['country'],
						'is_primary'  => true,
					)
				);
			}
		}

		if ( $this->show_notices ) {
			_doing_it_wrong( 'add_user_meta()/update_user_meta()', 'User addresses being stored in meta have been <strong>deprecated</strong> since Easy Digital Downloads 3.0! Use <code>edd_add_customer_address()/edd_update_customer_address()()</code> instead.', 'EDD 3.0' );

			if ( $this->show_backtrace ) {
				$backtrace = debug_backtrace();
				trigger_error( print_r( $backtrace, 1 ) );
			}
		}

		return $check;
	}

}
