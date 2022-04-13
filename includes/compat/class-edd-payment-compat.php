<?php
/**
 * This class is used to build an EDD_Payment object
 * from a WP_Post object.
 *
 * @package     EDD
 * @subpackage  Classes/Payment
 * @copyright   Copyright (c) 2014, Pippin Williamson
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       3.0
 */


// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * EDD_Payment_Compat Class
 *
 * @since 3.0
 *
 * @property int    $ID
 * @property bool   $new
 * @property string $number
 * @property string $mode
 * @property string $key
 * @property float  $total
 * @property float  $subtotal
 * @property float  $tax
 * @property float  $discounted_amount
 * @property float  $tax_rate
 * @property array  $fees
 * @property float  $fees_total
 * @property string $discounts
 * @property string $date
 * @property string $completed_date
 * @property string $status
 * @property string $post_status
 * @property string $old_status
 * @property string $status_nicename
 * @property int    $customer_id
 * @property int    $user_id
 * @property string $first_name
 * @property string $last_name
 * @property string $email
 * @property array  $user_info
 * @property array  $payment_meta
 * @property array  $address
 * @property string $transaction_id
 * @property array  $downloads
 * @property string $ip
 * @property string $gateway
 * @property string $currency
 * @property array  $cart_details
 * @property bool   $has_unlimited_downloads
 * @property int    $parent_payment
 */
class EDD_Payment_Compat {

	/**
	 * The Payment ID
	 *
	 * @since  3.0
	 * @var    integer
	 */
	public $ID = 0;

	/**
	 * Identify if the payment is a new one or existing
	 *
	 * @since  3.0
	 * @var boolean
	 */
	protected $new = false;

	/**
	 * The Payment number (for use with sequential payments)
	 *
	 * @since  3.0
	 * @var string
	 */
	protected $number = '';

	/**
	 * The Gateway mode the payment was made in
	 *
	 * @since  3.0
	 * @var string
	 */
	protected $mode = 'live';

	/**
	 * The Unique Payment Key
	 *
	 * @since  3.0
	 * @var string
	 */
	protected $key = '';

	/**
	 * The total amount the payment is for
	 * Includes items, taxes, fees, and discounts
	 *
	 * @since  3.0
	 * @var float
	 */
	protected $total = 0.00;

	/**
	 * The Subtotal fo the payment before taxes
	 *
	 * @since  3.0
	 * @var float
	 */
	protected $subtotal = 0;

	/**
	 * The amount of tax for this payment
	 *
	 * @since  3.0
	 * @var float
	 */
	protected $tax = 0;

	/**
	 * The amount the payment has been discounted through discount codes
	 *
	 * @since 3.0
	 * @var int
	 */
	protected $discounted_amount = 0;

	/**
	 * The tax rate charged on this payment
	 *
	 * @since 3.0
	 * @var float
	 */
	protected $tax_rate = '';

	/**
	 * Array of global fees for this payment
	 *
	 * @since  3.0
	 * @var array
	 */
	protected $fees = array();

	/**
	 * The sum of the fee amounts
	 *
	 * @since  3.0
	 * @var float
	 */
	protected $fees_total = 0;

	/**
	 * Any discounts applied to the payment
	 *
	 * @since  3.0
	 * @var string
	 */
	protected $discounts = 'none';

	/**
	 * The date the payment was created
	 *
	 * @since  3.0
	 * @var string
	 */
	protected $date = '';

	/**
	 * The date the payment was marked as 'complete'
	 *
	 * @since  3.0
	 * @var string
	 */
	protected $completed_date = '';

	/**
	 * The status of the payment
	 *
	 * @since  3.0
	 * @var string
	 */
	protected $status      = 'pending';
	protected $post_status = 'pending'; // Same as $status but here for backwards compat

	/**
	 * When updating, the old status prior to the change
	 *
	 * @since  3.0
	 * @var string
	 */
	protected $old_status = '';

	/**
	 * The display name of the current payment status
	 *
	 * @since  3.0
	 * @var string
	 */
	protected $status_nicename = '';

	/**
	 * The customer ID that made the payment
	 *
	 * @since  3.0
	 * @var integer
	 */
	protected $customer_id = null;

	/**
	 * The User ID (if logged in) that made the payment
	 *
	 * @since  3.0
	 * @var integer
	 */
	protected $user_id = 0;

	/**
	 * The first name of the payee
	 *
	 * @since  3.0
	 * @var string
	 */
	protected $first_name = '';

	/**
	 * The last name of the payee
	 *
	 * @since  3.0
	 * @var string
	 */
	protected $last_name = '';

	/**
	 * The email used for the payment
	 *
	 * @since  3.0
	 * @var string
	 */
	protected $email = '';

	/**
	 * Legacy (not to be accessed) array of user information
	 *
	 * @since  3.0
	 * @var array
	 */
	public $user_info = array();

	/**
	 * Legacy (not to be accessed) payment meta array
	 *
	 * @since  3.0
	 * @var array
	 */
	public $payment_meta = array();

	/**
	 * The physical address used for the payment if provided
	 *
	 * @since  3.0
	 * @var array
	 */
	protected $address = array();

	/**
	 * The transaction ID returned by the gateway
	 *
	 * @since  3.0
	 * @var string
	 */
	protected $transaction_id = '';

	/**
	 * Array of downloads for this payment
	 *
	 * @since  3.0
	 * @var array
	 */
	protected $downloads = array();

	/**
	 * IP Address payment was made from
	 *
	 * @since  3.0
	 * @var string
	 */
	protected $ip = '';

	/**
	 * The gateway used to process the payment
	 *
	 * @since  3.0
	 * @var string
	 */
	protected $gateway = '';

	/**
	 * The the payment was made with
	 *
	 * @since  3.0
	 * @var string
	 */
	protected $currency = '';

	/**
	 * The cart details array
	 *
	 * @since  3.0
	 * @var array
	 */
	protected $cart_details = array();

	/**
	 * Allows the files for this payment to be downloaded unlimited times (when download limits are enabled)
	 *
	 * @since  3.0
	 * @var boolean
	 */
	protected $has_unlimited_downloads = false;

	/**
	 * Array of items that have changed since the last save() was run
	 * This is for internal use, to allow fewer update_payment_meta calls to be run
	 *
	 * @since  3.0
	 * @var array
	 */
	public $pending;

	/**
	 * The parent payment (if applicable)
	 *
	 * @since  3.0
	 * @var integer
	 */
	protected $parent_payment = 0;

	/**
	 * Setup the EDD Payments class
	 *
	 * @since 3.0
	 * @param int $payment_id A given payment
	 * @return mixed void|false
	 */
	public function __construct( $payment_or_txn_id = false, $by_txn = false ) {

		if ( empty( $payment_or_txn_id ) ) {
			return false;
		}

		if ( $by_txn ) {
			global $wpdb;
			$query      = $wpdb->prepare( "SELECT post_id FROM $wpdb->postmeta WHERE meta_key = '_edd_payment_transaction_id' AND meta_value = '%s'", $payment_or_txn_id );
			$payment_id = $wpdb->get_var( $query );

			if ( empty( $payment_id ) ) {
				return false;
			}
		} else {
			$payment_id = absint( $payment_or_txn_id );
		}

		$this->ID = $payment_id;
	}

	/**
	 * Magic GET function
	 *
	 * @since  3.0
	 * @param  string $key  The property
	 * @return mixed        The value
	 */
	public function __get( $key ) {

		if ( method_exists( $this, 'get_' . $key ) ) {

			$value = call_user_func( array( $this, 'get_' . $key ) );

		} else {

			$value = $this->$key;

		}

		return $value;
	}

	/**
	 * Magic SET function
	 *
	 * Sets up the pending array for the save method
	 *
	 * @since  3.0
	 * @param string $key   The property name
	 * @param mixed $value  The value of the property
	 */
	public function __set( $key, $value ) {
		$ignore = array( 'downloads', 'cart_details', 'fees', '_ID' );

		if ( $key === 'status' ) {
			$this->old_status = $this->status;
		}

		if ( ! in_array( $key, $ignore ) ) {
			$this->pending[ $key ] = $value;
		}

		if ( '_ID' !== $key ) {
			$this->$key = $value;
		}
	}

	/**
	 * Magic ISSET function, which allows empty checks on protected elements
	 *
	 * @since  3.0
	 * @param  string  $name The attribute to get
	 * @return boolean       If the item is set or not
	 */
	public function __isset( $name ) {
		if ( property_exists( $this, $name ) ) {
			return false === empty( $this->$name );
		} else {
			return null;
		}
	}

	/**
	 * Get a post meta item for the payment
	 *
	 * @since  3.0
	 * @param  string  $meta_key The Meta Key
	 * @param  boolean $single   Return single item or array
	 * @return mixed             The value from the post meta
	 */
	public function get_meta( $meta_key = '_edd_payment_meta', $single = true ) {

		$meta = get_post_meta( $this->ID, $meta_key, $single );
		if ( $meta_key === '_edd_payment_meta' ) {

			if ( empty( $meta ) ) {
				$meta = array();
			}

			// #5228 Fix possible data issue introduced in 2.6.12
			if ( is_array( $meta ) && isset( $meta[0] ) ) {
				$bad_meta = $meta[0];
				unset( $meta[0] );

				if ( is_array( $bad_meta ) ) {
					$meta = array_merge( $meta, $bad_meta );
				}

				update_post_meta( $this->ID, '_edd_payment_meta', $meta );
			}

			// Payment meta was simplified in EDD v1.5, so these are here for backwards compatibility
			if ( empty( $meta['key'] ) ) {
				$meta['key'] = $this->setup_payment_key();
			}

			if ( empty( $meta['email'] ) ) {
				$meta['email'] = $this->setup_email();
			}

			if ( empty( $meta['date'] ) ) {
				$meta['date'] = get_post_field( 'post_date', $this->ID );
			}
		}

		$meta = apply_filters( 'edd_get_payment_meta_' . $meta_key, $meta, $this->ID );

		if ( is_serialized( $meta ) ) {
			preg_match( '/[oO]\s*:\s*\d+\s*:\s*"\s*(?!(?i)(stdClass))/', $meta, $matches );
			if ( ! empty( $matches ) ) {
				$meta = array();
			}
		}

		return apply_filters( 'edd_get_payment_meta', $meta, $this->ID, $meta_key );
	}

	/**
	 * Setup functions only, these are not to be used by developers.
	 * These functions exist only to allow the setup routine to be backwards compatible with our old
	 * helper functions.
	 *
	 * These will run whenever setup_payment is called, which should only be called once.
	 * To update an attribute, update it directly instead of re-running the setup routine
	 */

	/**
	 * Setup the payment completed date
	 *
	 * @since  3.0
	 * @return string The date the payment was completed
	 */
	public function setup_completed_date() {
		$payment = get_post( $this->ID );

		if ( 'pending' == $payment->post_status || 'preapproved' == $payment->post_status || 'processing' == $payment->post_status ) {
			return false; // This payment was never completed
		}

		$date = ( $date = $this->get_meta( '_edd_completed_date', true ) ) ? $date : $payment->date;

		return $date;
	}

	/**
	 * Setup the payment mode
	 *
	 * @since  3.0
	 * @return string The payment mode
	 */
	public function setup_mode() {
		return $this->get_meta( '_edd_payment_mode' );
	}

	/**
	 * Setup the payment total
	 *
	 * @since  3.0
	 * @return float The payment total
	 */
	public function setup_total() {
		$amount = $this->get_meta( '_edd_payment_total', true );

		if ( empty( $amount ) && '0.00' != $amount ) {
			$meta = $this->get_meta( '_edd_payment_meta', true );
			$meta = maybe_unserialize( $meta );

			if ( isset( $meta['amount'] ) ) {
				$amount = $meta['amount'];
			}
		}

		return $amount;
	}

	/**
	 * Setup the payment tax
	 *
	 * @since  3.0
	 * @return float The tax for the payment
	 */
	public function setup_tax() {
		$tax = $this->get_meta( '_edd_payment_tax', true );

		// We don't have tax as it's own meta and no meta was passed
		if ( '' === $tax ) {

			$tax = isset( $this->payment_meta['tax'] ) ? $this->payment_meta['tax'] : 0;

		}

		return $tax;

	}

	/**
	 * Setup the payment tax rate
	 *
	 * @since  3.0
	 * @return float The tax rate for the payment
	 */
	public function setup_tax_rate() {
		return $this->get_meta( '_edd_payment_tax_rate', true );
	}

	/**
	 * Setup the payment fees
	 *
	 * @since  3.0
	 * @return float The fees total for the payment
	 */
	public function setup_fees_total() {
		$fees_total = (float) 0.00;

		$payment_fees = isset( $this->payment_meta['fees'] ) ? $this->payment_meta['fees'] : array();
		if ( ! empty( $payment_fees ) ) {
			foreach ( $payment_fees as $fee ) {
				$fees_total += (float) $fee['amount'];
			}
		}

		return $fees_total;

	}

	/**
	 * Setup the payment subtotal
	 *
	 * @since  3.0
	 * @return float The subtotal of the payment
	 */
	public function setup_subtotal() {
		$subtotal     = 0;
		$cart_details = $this->cart_details;

		if ( is_array( $cart_details ) ) {

			foreach ( $cart_details as $item ) {

				if ( isset( $item['subtotal'] ) ) {

					$subtotal += $item['subtotal'];

				}
			}
		} else {

			$subtotal  = $this->total;
			$tax       = edd_use_taxes() ? $this->tax : 0;
			$subtotal -= $tax;

		}

		return $subtotal;
	}

	/**
	 * Setup the payments discount codes
	 *
	 * @since  3.0
	 * @return array               Array of discount codes on this payment
	 */
	public function setup_discounts() {
		$discounts = ! empty( $this->payment_meta['user_info']['discount'] ) ? $this->payment_meta['user_info']['discount'] : array();
		return $discounts;
	}

	/**
	 * Setup the currency code
	 *
	 * @since  3.0
	 * @return string              The currency for the payment
	 */
	public function setup_currency() {
		return isset( $this->payment_meta['currency'] ) ? $this->payment_meta['currency'] : apply_filters( 'edd_payment_currency_default', edd_get_currency(), $this );
	}

	/**
	 * Setup any fees associated with the payment
	 *
	 * @since  3.0
	 * @return array               The Fees
	 */
	public function setup_fees() {
		return isset( $this->payment_meta['fees'] ) ? $this->payment_meta['fees'] : array();
	}

	/**
	 * Setup the gateway used for the payment
	 *
	 * @since  3.0
	 * @return string The gateway
	 */
	public function setup_gateway() {
		return $this->get_meta( '_edd_payment_gateway', true );
	}

	/**
	 * Setup the transaction ID
	 *
	 * @since  3.0
	 * @return string The transaction ID for the payment
	 */
	public function setup_transaction_id() {
		$transaction_id = $this->get_meta( '_edd_payment_transaction_id', true );

		if ( empty( $transaction_id ) || (int) $transaction_id === (int) $this->ID ) {

			$gateway        = $this->gateway;
			$transaction_id = apply_filters( 'edd_get_payment_transaction_id-' . $gateway, $this->ID );

		}

		return $transaction_id;
	}

	/**
	 * Setup the IP Address for the payment
	 *
	 * @since  3.0
	 * @return string The IP address for the payment
	 */
	public function setup_ip() {
		return $this->get_meta( '_edd_payment_user_ip', true );
	}

	/**
	 * Setup the customer ID
	 *
	 * @since  3.0
	 * @return int The Customer ID
	 */
	public function setup_customer_id() {
		return $this->get_meta( '_edd_payment_customer_id', true );
	}

	/**
	 * Setup the User ID associated with the purchase
	 *
	 * @since  3.0
	 * @return int The User ID
	 */
	public function setup_user_id() {
		$user_id  = $this->get_meta( '_edd_payment_user_id', true );
		$customer = new EDD_Customer( $this->customer_id );

		// Make sure it exists, and that it matches that of the associated customer record
		if ( ! empty( $customer->user_id ) && ( empty( $user_id ) || (int) $user_id !== (int) $customer->user_id ) ) {

			$user_id = $customer->user_id;

			// Backfill the user ID, or reset it to be correct in the event of data corruption
			update_post_meta( $this->ID, '_edd_payment_user_id', $user_id );

		}

		return $user_id;
	}

	/**
	 * Setup the email address for the purchase
	 *
	 * @since  3.0
	 * @return string The email address for the payment
	 */
	public function setup_email() {
		$email = $this->get_meta( '_edd_payment_user_email', true );

		if ( empty( $email ) ) {
			$email = EDD()->customers->get_column( 'email', $this->customer_id );
		}

		return $email;
	}

	/**
	 * Setup the user info
	 *
	 * @since  3.0
	 * @return array               The user info associated with the payment
	 */
	public function setup_user_info() {
		$defaults = array(
			'first_name' => $this->first_name,
			'last_name'  => $this->last_name,
			'discount'   => $this->discounts,
		);

		$user_info = isset( $this->payment_meta['user_info'] ) ? $this->payment_meta['user_info'] : array();

		if ( is_serialized( $user_info ) ) {
			preg_match( '/[oO]\s*:\s*\d+\s*:\s*"\s*(?!(?i)(stdClass))/', $user_info, $matches );
			if ( ! empty( $matches ) ) {
				$user_info = array();
			}
		}

		// As per Github issue #4248, we need to run maybe_unserialize here still.
		$user_info = wp_parse_args( maybe_unserialize( $user_info ), $defaults );

		// Ensure email index is in the old user info array
		if ( empty( $user_info['email'] ) ) {
			$user_info['email'] = $this->email;
		}

		if ( empty( $user_info ) ) {
			// Get the customer, but only if it's been created
			$customer = new EDD_Customer( $this->customer_id );

			if ( $customer->id > 0 ) {
				$name      = explode( ' ', $customer->name, 2 );
				$user_info = array(
					'first_name' => $name[0],
					'last_name'  => $name[1],
					'email'      => $customer->email,
					'discount'   => 'none',
				);
			}
		} else {
			// Get the customer, but only if it's been created
			$customer = new EDD_Customer( $this->customer_id );
			if ( $customer->id > 0 ) {
				foreach ( $user_info as $key => $value ) {
					if ( ! empty( $value ) ) {
						continue;
					}

					switch ( $key ) {
						case 'first_name':
							$name = explode( ' ', $customer->name, 2 );

							$user_info[ $key ] = $name[0];
							break;

						case 'last_name':
							$name      = explode( ' ', $customer->name, 2 );
							$last_name = ! empty( $name[1] ) ? $name[1] : '';

							$user_info[ $key ] = $last_name;
							break;

						case 'email':
							$user_info[ $key ] = $customer->email;
							break;
					}
				}
			}
		}

		return $user_info;
	}

	/**
	 * Setup the Address for the payment
	 *
	 * @since  3.0
	 * @return array               The Address information for the payment
	 */
	public function setup_address() {
		$address  = ! empty( $this->payment_meta['user_info']['address'] ) ? $this->payment_meta['user_info']['address'] : array();
		$defaults = array(
			'line1'   => '',
			'line2'   => '',
			'city'    => '',
			'country' => '',
			'state'   => '',
			'zip'     => '',
		);

		return wp_parse_args( $address, $defaults );
	}

	/**
	 * Setup the payment key
	 *
	 * @since  3.0
	 * @return string The Payment Key
	 */
	public function setup_payment_key() {
		return $this->get_meta( '_edd_payment_purchase_key', true );
	}

	/**
	 * Setup the payment number
	 *
	 * @since  3.0
	 * @return int|string Integer by default, or string if sequential order numbers is enabled
	 */
	public function setup_payment_number() {
		$number = $this->ID;

		if ( edd_get_option( 'enable_sequential' ) ) {

			$number = $this->get_meta( '_edd_payment_number', true );

			if ( ! $number ) {

				$number = $this->ID;

			}
		}

		return $number;
	}

	/**
	 * Setup the cart details
	 *
	 * @since  3.0
	 * @return array               The cart details
	 */
	public function setup_cart_details() {
		return isset( $this->payment_meta['cart_details'] ) ? maybe_unserialize( $this->payment_meta['cart_details'] ) : array();
	}

	/**
	 * Setup the downloads array
	 *
	 * @since  3.0
	 * @return array               Downloads associated with this payment
	 */
	public function setup_downloads() {
		return isset( $this->payment_meta['downloads'] ) ? maybe_unserialize( $this->payment_meta['downloads'] ) : array();
	}

	/**
	 * Setup the Unlimited downloads setting
	 *
	 * @since  3.0
	 * @return bool If this payment has unlimited downloads
	 */
	public function setup_has_unlimited() {
		return (bool) $this->get_meta( '_edd_payment_unlimited_downloads', true );
	}
}
