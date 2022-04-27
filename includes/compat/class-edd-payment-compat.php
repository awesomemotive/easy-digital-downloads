<?php
/**
 * This class is used to build an EDD_Payment object
 * from a WP_Post object.
 *
 * This is intended for internal use only, to ensure that existing
 * payments can be accessed before the migration is complete.
 *
 * @package     EDD
 * @subpackage  Compat
 * @copyright   Copyright (c) 2022, Easy Digital Downloads
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
 * @property string $completed_date
 * @property string $status
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
	 * The Payment number (for use with sequential payments)
	 *
	 * @since  3.0
	 * @var string
	 */
	public $number = '';

	/**
	 * The Gateway mode the payment was made in
	 *
	 * @since  3.0
	 * @var string
	 */
	public $mode = 'live';

	/**
	 * The Unique Payment Key
	 *
	 * @since  3.0
	 * @var string
	 */
	public $key = '';

	/**
	 * The total amount the payment is for
	 * Includes items, taxes, fees, and discounts
	 *
	 * @since  3.0
	 * @var float
	 */
	public $total = 0.00;

	/**
	 * The Subtotal fo the payment before taxes
	 *
	 * @since  3.0
	 * @var float
	 */
	public $subtotal = 0;

	/**
	 * The amount of tax for this payment
	 *
	 * @since  3.0
	 * @var float
	 */
	public $tax = 0;

	/**
	 * The amount the payment has been discounted through discount codes
	 *
	 * @since 3.0
	 * @var int
	 */
	public $discounted_amount = 0;

	/**
	 * The tax rate charged on this payment
	 *
	 * @since 3.0
	 * @var float
	 */
	public $tax_rate = '';

	/**
	 * Array of global fees for this payment
	 *
	 * @since  3.0
	 * @var array
	 */
	public $fees = array();

	/**
	 * The sum of the fee amounts
	 *
	 * @since  3.0
	 * @var float
	 */
	public $fees_total = 0;

	/**
	 * Any discounts applied to the payment
	 *
	 * @since  3.0
	 * @var string
	 */
	public $discounts = 'none';

	/**
	 * The date the payment was created
	 *
	 * @since  3.0
	 * @var string
	 */
	public $date = '';

	/**
	 * The date the payment was marked as 'complete'
	 *
	 * @since  3.0
	 * @var string
	 */
	public $completed_date = '';

	/**
	 * The status of the payment
	 *
	 * @since  3.0
	 * @var string
	 */
	public $status = 'pending';

	/**
	 * The customer ID that made the payment
	 *
	 * @since  3.0
	 * @var integer
	 */
	public $customer_id = null;

	/**
	 * The User ID (if logged in) that made the payment
	 *
	 * @since  3.0
	 * @var integer
	 */
	public $user_id = 0;

	/**
	 * The first name of the payee
	 *
	 * @since  3.0
	 * @var string
	 */
	public $first_name = '';

	/**
	 * The last name of the payee
	 *
	 * @since  3.0
	 * @var string
	 */
	public $last_name = '';

	/**
	 * The email used for the payment
	 *
	 * @since  3.0
	 * @var string
	 */
	public $email = '';

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
	public $address = array();

	/**
	 * The transaction ID returned by the gateway
	 *
	 * @since  3.0
	 * @var string
	 */
	public $transaction_id = '';

	/**
	 * Array of downloads for this payment
	 *
	 * @since  3.0
	 * @var array
	 */
	public $downloads = array();

	/**
	 * IP Address payment was made from
	 *
	 * @since  3.0
	 * @var string
	 */
	public $ip = '';

	/**
	 * The gateway used to process the payment
	 *
	 * @since  3.0
	 * @var string
	 */
	public $gateway = '';

	/**
	 * The the payment was made with
	 *
	 * @since  3.0
	 * @var string
	 */
	public $currency = '';

	/**
	 * The cart details array
	 *
	 * @since  3.0
	 * @var array
	 */
	public $cart_details = array();

	/**
	 * Allows the files for this payment to be downloaded unlimited times (when download limits are enabled)
	 *
	 * @since  3.0
	 * @var boolean
	 */
	public $has_unlimited_downloads = false;

	/**
	 * Order object.
	 *
	 * @since 3.0
	 * @var   EDD\Orders\Order
	 */
	public $order;

	/**
	 * The parent payment (if applicable)
	 *
	 * @since  3.0
	 * @var integer
	 */
	public $parent_payment = 0;

	/**
	 * Post object.
	 *
	 * @since 3.0
	 * @var WP_Post
	 */
	private $payment;

	/**
	 * Setup the EDD Payments class
	 *
	 * @since 3.0
	 * @param int $payment_id A given payment
	 * @return mixed void|false
	 */
	public function __construct( $payment_id = false ) {

		if ( empty( $payment_id ) ) {
			return false;
		}

		$this->ID      = absint( $payment_id );
		$this->payment = get_post( $this->ID );
		if ( ! $this->payment instanceof WP_Post ) {
			return false;
		}
		$this->setup();
	}

	/**
	 * Sets up the payment object.
	 *
	 * @since 3.0
	 * @return void
	 */
	private function setup() {
		$this->payment_meta            = $this->get_meta();
		$this->cart_details            = $this->setup_cart_details();
		$this->status                  = $this->setup_status();
		$this->completed_date          = $this->setup_completed_date();
		$this->mode                    = $this->setup_mode();
		$this->total                   = $this->setup_total();
		$this->tax                     = $this->setup_tax();
		$this->tax_rate                = $this->setup_tax_rate();
		$this->fees_total              = $this->setup_fees_total();
		$this->subtotal                = $this->setup_subtotal();
		$this->discounts               = $this->setup_discounts();
		$this->currency                = $this->setup_currency();
		$this->fees                    = $this->setup_fees();
		$this->gateway                 = $this->setup_gateway();
		$this->transaction_id          = $this->setup_transaction_id();
		$this->ip                      = $this->setup_ip();
		$this->customer_id             = $this->setup_customer_id();
		$this->user_id                 = $this->setup_user_id();
		$this->email                   = $this->setup_email();
		$this->user_info               = $this->setup_user_info();
		$this->address                 = $this->setup_address();
		$this->key                     = $this->setup_payment_key();
		$this->number                  = $this->setup_payment_number();
		$this->downloads               = $this->setup_downloads();
		$this->has_unlimited_downloads = $this->setup_has_unlimited();
		$this->order                   = $this->_shim_order();
	}

	/**
	 * Get a post meta item for the payment
	 *
	 * @since  3.0
	 * @param  string  $meta_key The Meta Key
	 * @param  boolean $single   Return single item or array
	 * @return mixed             The value from the post meta
	 */
	private function get_meta( $meta_key = '_edd_payment_meta', $single = true ) {

		$meta = get_post_meta( $this->ID, $meta_key, $single );
		if ( '_edd_payment_meta' === $meta_key ) {

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
	 * Setup the payment completed date
	 *
	 * @since  3.0
	 * @return string The date the payment was completed
	 */
	private function setup_completed_date() {
		if ( in_array( $this->payment->post_status, array( 'pending', 'preapproved', 'processing' ), true ) ) {
			return false; // This payment was never completed
		}
		$date = $this->get_meta( '_edd_completed_date', true );

		// phpcs:ignore WordPress.PHP.DisallowShortTernary
		return $date ?: $this->payment->date;
	}

	/**
	 * Setup the payment mode
	 *
	 * @since  3.0
	 * @return string The payment mode
	 */
	private function setup_mode() {
		return $this->get_meta( '_edd_payment_mode' );
	}

	/**
	 * Setup the payment total
	 *
	 * @since  3.0
	 * @return float The payment total
	 */
	private function setup_total() {
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
	private function setup_tax() {
		$tax = $this->get_meta( '_edd_payment_tax', true );

		// We don't have tax as its own meta and no meta was passed
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
	private function setup_tax_rate() {
		return $this->get_meta( '_edd_payment_tax_rate', true );
	}

	/**
	 * Setup the payment fees
	 *
	 * @since  3.0
	 * @return float The fees total for the payment
	 */
	private function setup_fees_total() {
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
	private function setup_subtotal() {
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
	private function setup_discounts() {
		return ! empty( $this->payment_meta['user_info']['discount'] ) ? $this->payment_meta['user_info']['discount'] : array();
	}

	/**
	 * Setup the currency code
	 *
	 * @since  3.0
	 * @return string              The currency for the payment
	 */
	private function setup_currency() {
		return isset( $this->payment_meta['currency'] ) ? $this->payment_meta['currency'] : apply_filters( 'edd_payment_currency_default', edd_get_currency(), $this );
	}

	/**
	 * Setup any fees associated with the payment
	 *
	 * @since  3.0
	 * @return array               The Fees
	 */
	private function setup_fees() {
		return isset( $this->payment_meta['fees'] ) ? $this->payment_meta['fees'] : array();
	}

	/**
	 * Setup the gateway used for the payment
	 *
	 * @since  3.0
	 * @return string The gateway
	 */
	private function setup_gateway() {
		return $this->get_meta( '_edd_payment_gateway', true );
	}

	/**
	 * Setup the transaction ID
	 *
	 * @since  3.0
	 * @return string The transaction ID for the payment
	 */
	private function setup_transaction_id() {
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
	private function setup_ip() {
		return $this->get_meta( '_edd_payment_user_ip', true );
	}

	/**
	 * Setup the customer ID
	 *
	 * @since  3.0
	 * @return int The Customer ID
	 */
	private function setup_customer_id() {
		return $this->get_meta( '_edd_payment_customer_id', true );
	}

	/**
	 * Setup the User ID associated with the purchase
	 *
	 * @since  3.0
	 * @return int The User ID
	 */
	private function setup_user_id() {
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
	private function setup_email() {
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
	private function setup_user_info() {
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
	private function setup_address() {
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
	private function setup_payment_key() {
		return $this->get_meta( '_edd_payment_purchase_key', true );
	}

	/**
	 * Setup the payment number
	 *
	 * @since  3.0
	 * @return int|string Integer by default, or string if sequential order numbers is enabled
	 */
	private function setup_payment_number() {
		$number = false;
		if ( edd_get_option( 'enable_sequential' ) ) {
			$number = $this->get_meta( '_edd_payment_number', true );
		}

		return $number ?: $this->ID;
	}

	/**
	 * Setup the cart details
	 *
	 * @since  3.0
	 * @return array               The cart details
	 */
	private function setup_cart_details() {
		return isset( $this->payment_meta['cart_details'] ) ? maybe_unserialize( $this->payment_meta['cart_details'] ) : array();
	}

	/**
	 * Setup the downloads array
	 *
	 * @since  3.0
	 * @return array               Downloads associated with this payment
	 */
	private function setup_downloads() {
		return isset( $this->payment_meta['downloads'] ) ? maybe_unserialize( $this->payment_meta['downloads'] ) : array();
	}

	/**
	 * Setup the Unlimited downloads setting
	 *
	 * @since  3.0
	 * @return bool If this payment has unlimited downloads
	 */
	private function setup_has_unlimited() {
		return (bool) $this->get_meta( '_edd_payment_unlimited_downloads', true );
	}

	/**
	 * Sets up the payment status.
	 *
	 * @since 3.0
	 * @return string
	 */
	private function setup_status() {
		return 'publish' === $this->payment->post_status ? 'complete' : $this->payment->post_status;
	}

	/**
	 * Shims the payment, as much as possible, into an EDD Order object.
	 * @todo deprecate in 3.1
	 *
	 * @return EDD\Orders\Order
	 */
	public function _shim_order() {
		return new \EDD\Orders\Order(
			array(
				'id'             => $this->ID,
				'parent'         => $this->payment->parent,
				'order_number'   => $this->number,
				'status'         => $this->status,
				'type'           => 'sale',
				'user_id'        => $this->user_id,
				'customer_id'    => $this->customer_id,
				'email'          => $this->email,
				'ip'             => $this->ip,
				'gateway'        => $this->gateway,
				'mode'           => $this->mode,
				'currency'       => $this->currency,
				'payment_key'    => $this->key,
				'subtotal'       => $this->subtotal,
				'discount'       => $this->discounted_amount,
				'tax'            => $this->tax,
				'total'          => $this->total,
				'rate'           => $this->get_order_tax_rate(),
				'date_created'   => $this->payment->post_date_gmt,
				'date_modified'  => $this->payment->post_modified,
				'date_completed' => $this->completed_date,
				'items'          => $this->get_order_items(),
			)
		);
	}

	/**
	 * Updates the payment tax rate to match the expected order tax rate.
	 *
	 * @since 3.0
	 * @return float
	 */
	private function get_order_tax_rate() {
		$tax_rate = (float) $this->tax_rate;
		if ( $tax_rate < 1 ) {
			$tax_rate = $tax_rate * 100;
		}

		return $tax_rate;
	}

	/**
	 * Gets an array of order item objects from the cart details.
	 *
	 * @since 3.0
	 * @return array
	 */
	private function get_order_items() {
		$order_items = array();
		if ( empty( $this->cart_details ) ) {
			return $order_items;
		}
		foreach ( $this->cart_details as $key => $cart_item ) {
			$product_name = isset( $cart_item['name'] )
				? $cart_item['name']
				: '';
			$price_id     = $this->get_valid_price_id_for_cart_item( $cart_item );
			if ( ! empty( $product_name ) ) {
				$option_name = edd_get_price_option_name( $cart_item['id'], $price_id );
				if ( ! empty( $option_name ) ) {
					$product_name .= ' — ' . $option_name;
				}
			}
			$order_item_args = array(
				'order_id'      => $this->ID,
				'product_id'    => $cart_item['id'],
				'product_name'  => $product_name,
				'price_id'      => $price_id,
				'cart_index'    => $key,
				'type'          => 'download',
				'status'        => $this->status,
				'quantity'      => ! empty( $cart_item['quantity'] ) ? $cart_item['quantity'] : 1,
				'amount'        => ! empty( $cart_item['item_price'] ) ? (float) $cart_item['item_price'] : (float) $cart_item['price'],
				'subtotal'      => (float) $cart_item['subtotal'],
				'discount'      => ! empty( $cart_item['discount'] ) ? (float) $cart_item['discount'] : 0.00,
				'tax'           => $cart_item['tax'],
				'total'         => (float) $cart_item['price'],
				'date_created'  => $this->payment->post_date_gmt,
				'date_modified' => $this->payment->post_modified_gmt,
			);

			$order_items[] = new EDD\Orders\Order_Item( $order_item_args );
		}

		return $order_items;
	}

	/**
	 * Retrieves a valid price ID for a given cart item.
	 * If the product does not have variable prices, then `null` is always returned.
	 * If the supplied price ID does not match a price ID that actually exists, then the default
	 * variable price is returned instead of the supplied one.
	 *
	 * @since 3.0
	 *
	 * @param array $cart_item Array of cart item details.
	 *
	 * @return int|null
	 */
	private function get_valid_price_id_for_cart_item( $cart_item ) {
		// If the product doesn't have variable prices, just return `null`.
		if ( ! edd_has_variable_prices( $cart_item['id'] ) ) {
			return null;
		}

		$variable_prices = edd_get_variable_prices( $cart_item['id'] );
		if ( ! is_array( $variable_prices ) || empty( $variable_prices ) ) {
			return null;
		}

		// Get the price ID that's set to the cart item right now.
		$price_id = isset( $cart_item['item_number']['options']['price_id'] ) && is_numeric( $cart_item['item_number']['options']['price_id'] )
			? absint( $cart_item['item_number']['options']['price_id'] )
			: null;

		// Now let's confirm it's actually a valid price ID.
		$variable_price_ids = array_map( 'intval', array_column( $variable_prices, 'index' ) );

		return in_array( $price_id, $variable_price_ids, true ) ? $price_id : edd_get_default_variable_price( $cart_item['id'] );
	}
}
