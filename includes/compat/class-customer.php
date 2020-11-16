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

		/** Actions **********************************************************/
		add_action( 'profile_update', array( $this, 'update_customer_email_on_user_update' ), 10 );
	}

	/**
	 * Updates the email address of a customer record when the email on a user is updated.
	 *
	 * @since 2.4.0
	 *
	 * @param int   $user_id User ID.
	 *
	 * @return bool False if customer does not exist for given user ID.
	 */
	public function update_customer_email_on_user_update( $user_id = 0 ) {

		// Bail if no customer
		$customer = edd_get_customer_by( 'user_id', $user_id );
		if ( empty( $customer ) ) {
			return false;
		}

		// Bail if no user
		$user = get_userdata( $user_id );
		if ( empty( $user ) || ( $user->user_email === $customer->email ) ) {
			return;
		}

		// Bail if customer already has this email address
		if ( edd_get_customer_by( 'email', $user->user_email ) ) {
			return;
		}

		// Try to update the customer
		$success = edd_update_customer( $customer->id, array(
			'email' => $user->user_email
		) );

		// Bail on failure
		if ( empty( $success ) ) {
			return;
		}

		// Bail if no payment IDs to update
		$payments_array = explode( ',', $customer->payment_ids );
		if ( empty( $payments_array ) ) {
			return;
		}

		// Loop through and update payment meta
		foreach ( $payments_array as $payment_id ) {
			edd_update_payment_meta( $payment_id, 'email', $user->user_email );
		}

		do_action( 'edd_update_customer_email_on_user_update', $user, $customer );
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
