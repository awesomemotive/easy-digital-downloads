<?php
/**
 * Payments Object.
 *
 * This class is for working with payments in EDD.
 *
 * @package     EDD
 * @subpackage  Payments
 * @copyright   Copyright (c) 2018, Easy Digital Downloads, LLC
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       2.5
 */

// Exit if accessed directly
defined( 'ABSPATH' ) || exit;

/**
 * EDD_Payment Class
 *
 * @since 2.5
 * @since 3.0 Updated to work with new custom tables.
 *
 * @property int    $ID
 * @property int    $_ID
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
class EDD_Payment {

	/**
	 * The Payment ID
	 *
	 * @since  2.5
	 * @var    integer
	 */
	public $ID     = 0;
	protected $_ID = 0;

	/**
	 * Identify if the payment is a new one or existing
	 *
	 * @since  2.5
	 * @var boolean
	 */
	protected $new = false;

	/**
	 * The Payment number (for use with sequential payments)
	 *
	 * @since  2.5
	 * @var string
	 */
	protected $number = '';

	/**
	 * The Gateway mode the payment was made in
	 *
	 * @since  2.5
	 * @var string
	 */
	protected $mode = 'live';

	/**
	 * The Unique Payment Key
	 *
	 * @since  2.5
	 * @var string
	 */
	protected $key = '';

	/**
	 * The total amount the payment is for
	 * Includes items, taxes, fees, and discounts
	 *
	 * @since  2.5
	 * @var float
	 */
	protected $total = 0.00;

	/**
	 * The Subtotal fo the payment before taxes
	 *
	 * @since  2.5
	 * @var float
	 */
	protected $subtotal = 0;

	/**
	 * The amount of tax for this payment
	 *
	 * @since  2.5
	 * @var float
	 */
	protected $tax = 0;

	/**
	 * The amount the payment has been discounted through discount codes
	 *
	 * @since 2.8.7
	 * @var int
	 */
	protected $discounted_amount = 0;

	/**
	 * The tax rate charged on this payment
	 *
	 * @since 2.7
	 * @var float
	 */
	protected $tax_rate = '';

	/**
	 * Array of global fees for this payment
	 *
	 * @since  2.5
	 * @var array
	 */
	protected $fees = array();

	/**
	 * The sum of the fee amounts
	 *
	 * @since  2.5
	 * @var float
	 */
	protected $fees_total = 0;

	/**
	 * Any discounts applied to the payment
	 *
	 * @since  2.5
	 * @var string
	 */
	protected $discounts = 'none';

	/**
	 * The date the payment was created
	 *
	 * @since  2.5
	 * @var string
	 */
	protected $date = '';

	/**
	 * The date the payment was marked as 'complete'
	 *
	 * @since  2.5
	 * @var string
	 */
	protected $completed_date = '';

	/**
	 * The status of the payment
	 *
	 * @since  2.5
	 * @var string
	 */
	protected $status      = 'pending';
	protected $post_status = 'pending'; // Same as $status but here for backwards compat

	/**
	 * When updating, the old status prior to the change
	 *
	 * @since  2.5
	 * @var string
	 */
	protected $old_status = '';

	/**
	 * The display name of the current payment status
	 *
	 * @since  2.5
	 * @var string
	 */
	protected $status_nicename = '';

	/**
	 * The customer ID that made the payment
	 *
	 * @since  2.5
	 * @var integer
	 */
	protected $customer_id = null;

	/**
	 * The User ID (if logged in) that made the payment
	 *
	 * @since  2.5
	 * @var integer
	 */
	protected $user_id = 0;

	/**
	 * The first name of the payee
	 *
	 * @since  2.5
	 * @var string
	 */
	protected $first_name = '';

	/**
	 * The last name of the payee
	 *
	 * @since  2.5
	 * @var string
	 */
	protected $last_name = '';

	/**
	 * The email used for the payment
	 *
	 * @since  2.5
	 * @var string
	 */
	protected $email = '';

	/**
	 * Legacy (not to be accessed) array of user information
	 *
	 * @since  2.5
	 * @var array
	 */
	private $user_info = array();

	/**
	 * Legacy (not to be accessed) payment meta array
	 *
	 * @since  2.5
	 * @var array
	 */
	private $payment_meta = array();

	/**
	 * The physical address used for the payment if provided
	 *
	 * @since  2.5
	 * @var array
	 */
	protected $address = array();

	/**
	 * The transaction ID returned by the gateway
	 *
	 * @since  2.5
	 * @var string
	 */
	protected $transaction_id = '';

	/**
	 * Array of downloads for this payment
	 *
	 * @since  2.5
	 * @var array
	 */
	protected $downloads = array();

	/**
	 * IP Address payment was made from
	 *
	 * @since  2.5
	 * @var string
	 */
	protected $ip = '';

	/**
	 * The gateway used to process the payment
	 *
	 * @since  2.5
	 * @var string
	 */
	protected $gateway = '';

	/**
	 * The the payment was made with
	 *
	 * @since  2.5
	 * @var string
	 */
	protected $currency = '';

	/**
	 * The cart details array
	 *
	 * @since  2.5
	 * @var array
	 */
	protected $cart_details = array();

	/**
	 * Allows the files for this payment to be downloaded unlimited times (when download limits are enabled)
	 *
	 * @since  2.5
	 * @var boolean
	 */
	protected $has_unlimited_downloads = false;

	/**
	 * Array of items that have changed since the last save() was run
	 * This is for internal use, to allow fewer update_payment_meta calls to be run
	 *
	 * @since  2.5
	 * @var array
	 */
	private $pending;

	/**
	 * The parent payment (if applicable)
	 *
	 * @since  2.5
	 * @var integer
	 */
	protected $parent_payment = 0;

	/**
	 * Order object.
	 *
	 * @since 3.0
	 * @var   EDD\Orders\Order
	 */
	protected $order;

	/**
	 * Whether the payment being retrieved is a post object.
	 *
	 * @var bool
	 */
	private $is_edd_payment = false;

	/**
	 * Constructor.
	 *
	 * @since 2.5
	 * @since 3.0 Updated to fetch transaction ID from edd_ordermeta table.
	 *
	 * @param mixed $payment_or_txn_id Payment ID or transaction ID. Default false.
	 * @param bool  $by_txn            Whether the constructor should retrieve the order ID from the transaction ID. Default false.
	 *
	 * @return mixed void|false
	 */
	public function __construct( $payment_or_txn_id = false, $by_txn = false ) {
		if ( empty( $payment_or_txn_id ) ) {
			return false;
		}

		if ( $by_txn ) {
			$payment_id = edd_get_order_id_from_transaction_id( $payment_or_txn_id );

			if ( empty( $payment_id ) ) {
				return false;
			}
		} else {
			$payment_id = absint( $payment_or_txn_id );
		}

		$this->setup_payment( $payment_id );
	}

	/**
	 * Magic GET function
	 *
	 * @since  2.5
	 *
	 * @param  string $key The property
	 *
	 * @return mixed        The value
	 */
	public function __get( $key ) {
		if ( method_exists( $this, "get_{$key}" ) ) {
			$value = call_user_func( array( $this, "get_{$key}" ) );
		} elseif ( 'id' === $key ) {
			$value = $this->ID;
		} elseif ( 'post_type' === $key ) {
			$value = 'edd_payment';
		} elseif ( 'post_date' === $key ) {
			$value = $this->date;
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
	 * @since  2.5
	 *
	 * @param string $key   The property name
	 * @param mixed  $value The value of the property
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
	 * @since  2.5
	 *
	 * @param  string $name The attribute to get
	 *
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
	 * Setup payment properties
	 *
	 * @since  2.5
	 *
	 * @param  int $payment_id The payment ID
	 *
	 * @return bool            If the setup was successful or not
	 */
	private function setup_payment( $payment_id ) {
		$this->pending = array();

		if ( empty( $payment_id ) ) {
			return false;
		}

		$this->order = $this->_shim_edd_get_order( $payment_id );

		if ( ! $this->order || is_wp_error( $this->order ) ) {
			return _edd_get_final_payment_id() ? $this->_setup_compat_payment( $payment_id ) : false;
		}

		// Allow extensions to perform actions before the payment is loaded
		do_action( 'edd_pre_setup_payment', $this, $payment_id );

		// Primary Identifier
		$this->ID = absint( $payment_id );

		// Protected ID that can never be changed
		$this->_ID = absint( $payment_id );

		// Status and Dates
		$this->date            = $this->order->date_created;
		$this->completed_date  = $this->setup_completed_date();
		$this->status          = $this->order->status;
		$this->post_status     = $this->order->status;
		$this->mode            = $this->order->mode;
		$this->parent_payment  = $this->order->parent;
		$this->status_nicename = $this->get_status_nicename();

		// Items
		$this->fees         = $this->setup_fees();
		$this->cart_details = $this->setup_cart_details();
		$this->downloads    = $this->setup_downloads();

		// Currency Based
		$this->total      = $this->order->total;
		$this->tax        = $this->order->tax;
		$this->tax_rate   = $this->setup_tax_rate();
		$this->fees_total = $this->setup_fees_total();
		$this->subtotal   = $this->order->subtotal;
		$this->currency   = $this->setup_currency();

		// Gateway based
		$this->gateway        = $this->order->gateway;
		$this->transaction_id = $this->setup_transaction_id();

		// User based
		$this->ip          = $this->order->ip;
		$this->customer_id = $this->order->customer_id;
		$this->user_id     = $this->setup_user_id();
		$this->email       = $this->setup_email();
		$this->discounts   = $this->setup_discounts();
		$this->user_info   = $this->setup_user_info();
		$this->first_name  = $this->user_info['first_name'];
		$this->last_name   = $this->user_info['last_name'];
		$this->address     = $this->setup_address();

		// Other Identifiers
		$this->key    = $this->order->payment_key;
		$this->number = $this->setup_payment_number();

		// Additional Attributes
		$this->has_unlimited_downloads = $this->setup_has_unlimited();

		// We have a payment, get the generic payment_meta item to reduce calls to it
		// This only exists for backwards compatibility purposes.
		$this->payment_meta = $this->get_meta();

		// Allow extensions to add items to this object via hook
		do_action( 'edd_setup_payment', $this, $payment_id );

		return true;
	}

	/**
	 * Create the base of a payment.
	 *
	 * @since 2.5
	 * @since 3.0 Updated to insert orders to the new custom tables.
	 *
	 * @return int|bool False on failure, the order ID on success.
	 */
	private function insert_payment() {

		$payment_data = array(
			'price'        => $this->total,
			'date'         => $this->date,
			'user_email'   => $this->email,
			'purchase_key' => $this->key,
			'currency'     => $this->currency,
			'downloads'    => $this->downloads,
			'user_info'    => array(
				'id'         => $this->user_id,
				'email'      => $this->email,
				'first_name' => $this->first_name,
				'last_name'  => $this->last_name,
				'discount'   => $this->discounts,
				'address'    => $this->address,
			),
			'cart_details' => $this->cart_details,
			'status'       => $this->status,
			'fees'         => $this->fees,
		);

		// Create an order
		$order_args = array(
			'parent'      => $this->parent_payment,
			'status'      => $this->status,
			'user_id'     => $this->user_id,
			'email'       => $this->email,
			'ip'          => $this->ip,
			'gateway'     => $this->gateway,
			'mode'        => $this->mode,
			'currency'    => $this->currency,
			'payment_key' => $this->key,
		);

		$order_id = edd_add_order( $order_args );

		if ( ! empty( $order_id ) ) {
			$this->ID  = $order_id;
			$this->_ID = $order_id;

			$customer = $this->maybe_create_customer();

			$this->customer_id = $customer->id;
			$customer->attach_payment( $this->ID, false );

			$order_data = array(
				'customer_id' => $this->customer_id,
			);

			/**
			 * This run of the edd_payment_meta filter is for backwards compatibility purposes. The filter will also run
			 * in the EDD_Payment::save method. By keeping this here, it retains compatibility of adding payment meta
			 * prior to the payment being inserted, as was previously supported by edd_insert_payment().
			 *
			 * @reference: https://github.com/easydigitaldownloads/easy-digital-downloads/issues/5838
			 */
			$this->payment_meta = apply_filters( 'edd_payment_meta', $this->payment_meta, $payment_data );
			if ( ! empty( $this->payment_meta['fees'] ) ) {
				$this->fees = array_merge( $this->payment_meta['fees'], $this->fees );
				foreach ( $this->fees as $key => $fee ) {
					add_filter( 'edd_prices_include_tax', '__return_false' );

					$tax = ( isset( $fee['no_tax'] ) && false === $fee['no_tax'] ) || $fee['amount'] < 0
						? floatval( edd_calculate_tax( $fee['amount'] ) )
						: 0.00;

					remove_filter( 'edd_prices_include_tax', '__return_false' );

					$adjustment_id = edd_add_order_adjustment(
						array(
							'object_id'   => $this->ID,
							'object_type' => 'order',
							'type_key'    => $key,
							'type'        => 'fee',
							'description' => $fee['label'],
							'subtotal'    => floatval( $fee['amount'] ),
							'tax'         => $tax,
							'total'       => floatval( $fee['amount'] ) + $tax,
						)
					);

					edd_add_extra_fee_order_adjustment_meta( $adjustment_id, $fee );

					$this->increase_fees( $fee['amount'] );
				}
			}

			$order_number = edd_set_order_number();
			if ( $order_number ) {
				$this->number = $order_number;

				$this->update_meta( '_edd_payment_number', $this->number );
				$order_data['order_number'] = $this->number;
			}

			edd_update_order( $order_id, $order_data );

			$this->update_meta( '_edd_payment_meta', $this->payment_meta );

			$tax_rate   = $this->maybe_update_tax_rate();
			$order_meta = array(
				'tax_rate' => $tax_rate,
			);

			foreach ( $order_meta as $key => $value ) {
				if ( ! empty( $value ) ) {
					edd_add_order_meta( $order_id, $key, $value );
				}
			}

			$this->new = true;
		}

		return $this->ID;
	}

	/**
	 * One items have been set, an update is needed to save them to the database.
	 *
	 * @since 3.0 Refactored to work with the new query methods.
	 *
	 * @return bool True of the save occurred, false if it failed or wasn't needed.
	 */
	public function save() {
		$saved = false;

		if ( empty( $this->ID ) ) {
			$payment_id = $this->insert_payment();

			if ( false === $payment_id ) {
				$saved = false;
			} else {
				$this->ID = $payment_id;
			}
		}

		if ( $this->ID !== $this->_ID ) {
			$this->ID = $this->_ID;
		}

		// If the order is null, it means a new order is being added
		$this->order = $this->_shim_edd_get_order( $this->ID );

		$customer = $this->maybe_create_customer();
		if ( $this->customer_id !== $customer->id ) {
			$this->customer_id            = $customer->id;
			$this->pending['customer_id'] = $this->customer_id;
		}

		// If we have something pending, let's save it
		if ( ! empty( $this->pending ) ) {
			foreach ( $this->pending as $key => $value ) {
				switch ( $key ) {
					case 'downloads':
					case 'fees':
						break;

					case 'status':
						$this->update_status( $this->status );
						break;

					case 'gateway':
						edd_update_order(
							$this->ID,
							array(
								'gateway' => $this->gateway,
							)
						);
						break;

					case 'mode':
						edd_update_order(
							$this->ID,
							array(
								'mode' => $this->mode,
							)
						);
						break;

					case 'transaction_id':
						$this->update_meta( 'transaction_id', $this->transaction_id );
						break;

					case 'customer_id':
						edd_update_order(
							$this->ID,
							array(
								'customer_id' => $this->customer_id,
							)
						);

						$customer = new EDD_Customer( $this->customer_id );
						$customer->attach_payment( $this->ID, false );
						break;

					case 'user_id':
						edd_update_order(
							$this->ID,
							array(
								'user_id' => $this->user_id,
							)
						);

						$this->user_info['id'] = $this->user_id;
						break;

					case 'first_name':
						$this->user_info['first_name'] = $this->first_name;
						break;

					case 'last_name':
						$this->user_info['last_name'] = $this->last_name;
						break;

					case 'discounts':
						if ( ! is_array( $this->discounts ) ) {
							$this->discounts = explode( ',', $this->discounts );
						}

						$cart_subtotal = 0.00;

						foreach ( $this->cart_details as $item ) {
							$cart_subtotal += $item['subtotal'];
						}

						if ( 'none' === $this->discounts[0] ) {
							break;
						}

						foreach ( $this->discounts as $discount ) {
							/** @var EDD_Discount $discount_obj */
							$discount_obj = edd_get_discount_by( 'code', $discount );
							$args         = array(
								'object_id'   => $this->ID,
								'object_type' => 'order',
								'description' => $discount,
							);

							if ( false === $discount_obj ) {
								$args['type']     = 'fee';
								$args['subtotal'] = floatval( $this->total - $cart_subtotal - $this->tax );
								$args['total']    = floatval( $this->total - $cart_subtotal - $this->tax );
							} else {
								$args['type_id']  = $discount_obj->id;
								$args['type']     = 'discount';
								$args['subtotal'] = floatval( $cart_subtotal - $discount_obj->get_discounted_amount( $cart_subtotal ) );
								$args['total']    = floatval( $cart_subtotal - $discount_obj->get_discounted_amount( $cart_subtotal ) );
							}
							$adjustment_id = edd_add_order_adjustment( $args );
							if ( 'fee' === $args['type'] ) {
								edd_add_extra_fee_order_adjustment_meta( $adjustment_id, $fee );
							}
						}

						$this->user_info['discount'] = implode( ',', $this->discounts );
						break;

					case 'address':
						$this->user_info['address'] = $this->address;
						break;

					case 'email':
						$this->payment_meta['email'] = $this->email;
						$this->user_info['email']    = $this->email;

						edd_update_order(
							$this->ID,
							array(
								'email' => $this->email,
							)
						);
						break;

					case 'key':
						edd_update_order(
							$this->ID,
							array(
								'payment_key' => $this->key,
							)
						);
						break;

					case 'tax_rate':
						$tax_rate = $this->maybe_update_tax_rate();
						$this->update_meta( '_edd_payment_tax_rate', $tax_rate );
						break;

					case 'number':
						edd_update_order(
							$this->ID,
							array(
								'order_number' => $this->number,
							)
						);
						break;

					case 'date':
						edd_update_order(
							$this->ID,
							array(
								'date_created' => $this->date,
							)
						);
						break;

					case 'completed_date':
						edd_update_order(
							$this->ID,
							array(
								'date_completed' => $this->completed_date,
							)
						);
						break;

					case 'has_unlimited_downloads':
						$this->update_meta( 'unlimited_downloads', $this->has_unlimited_downloads );
						break;

					case 'parent_payment':
						edd_update_order(
							$this->ID,
							array(
								'parent' => $this->parent_payment,
							)
						);
						break;

					default:
						/**
						 * Used to save non-standard data. Developers can hook here if they want to save
						 * specific payment data when $payment->save() is run and their item is in the $pending array
						 */
						do_action( 'edd_payment_save', $this, $key );
						break;
				}
			}

			$discount = 0.00;

			foreach ( $this->cart_details as $item ) {
				$discount += $item['discount'];
			}

			edd_update_order(
				$this->ID,
				array(
					'subtotal' => (float) $this->subtotal,
					'tax'      => (float) $this->tax,
					'discount' => $discount,
					'total'    => (float) $this->total,
				)
			);

			$this->downloads = array_values( $this->downloads );

			$new_meta = array(
				'downloads'    => $this->downloads,
				'cart_details' => $this->cart_details,
				'fees'         => $this->fees,
				'user_info'    => is_array( $this->user_info ) ? $this->user_info : array(),
				'date'         => $this->date,
				'email'        => $this->email,
				'tax'          => $this->tax,
			);

			// Do some merging of user_info before we merge it all, to honor the edd_payment_meta filter
			if ( ! empty( $this->payment_meta['user_info'] ) ) {
				$stored_discount = ! empty( $new_meta['user_info']['discount'] ) ? $new_meta['user_info']['discount'] : '';

				$new_meta['user_info'] = array_replace_recursive( (array) $this->payment_meta['user_info'], $new_meta['user_info'] );

				if ( 'none' !== $stored_discount ) {
					$new_meta['user_info']['discount'] = $stored_discount;
				}
			}

			$merged_meta = array_merge( $this->payment_meta, $new_meta );

			$payment_data = array(
				'price'        => $this->total,
				'date'         => $this->date,
				'user_email'   => $this->email,
				'downloads'    => $this->downloads,
				'user_info'    => array(
					'id'         => $this->user_id,
					'email'      => $this->email,
					'first_name' => $this->first_name,
					'last_name'  => $this->last_name,
					'discount'   => $this->discounts,
					'address'    => $this->address,
				),
				'cart_details' => $this->cart_details,
				'status'       => $this->status,
				'fees'         => $this->fees,
				'tax'          => $this->tax,
			);
			$merged_meta  = apply_filters( 'edd_payment_meta', $merged_meta, $payment_data );

			// Only save the payment meta if it's changed
			if ( md5( serialize( $this->payment_meta ) ) !== md5( serialize( $merged_meta ) ) ) {
				// First, update the order.
				$order_info = array(
					'email' => $merged_meta['email'],
				);

				if ( isset( $merged_meta['user_info']['id'] ) ) {
					$order_info['user_id'] = $merged_meta['user_info']['id'];
				}

				if ( ! empty( $merged_meta['date'] ) ) {
					$order_info['date'] = $merged_meta['date'];
				}

				edd_update_order( $this->ID, $order_info );

				// We need to check if all of the order items exist in the database.
				$items = edd_get_order_items(
					array(
						'order_id' => $this->ID,
					)
				);

				// If an empty set was returned, this is a new payment.
				if ( empty( $items ) ) {
					foreach ( $merged_meta['cart_details'] as $key => $item ) {
						edd_add_order_item(
							array(
								'order_id'     => $this->ID,
								'product_id'   => $item['id'],
								'product_name' => $item['name'],
								'price_id'     => isset( $item['item_number']['options']['price_id'] ) && is_numeric( $item['item_number']['options']['price_id'] )
									? absint( $item['item_number']['options']['price_id'] )
									: null,
								'cart_index'   => $key,
								'quantity'     => $item['quantity'],
								'amount'       => $item['item_price'],
								'subtotal'     => $item['subtotal'],
								'discount'     => $item['discount'],
								'tax'          => $item['tax'],
								'total'        => $item['price'],
								'status'       => ! empty( $item['status'] ) ? $item['status'] : $this->status,
							)
						);
					}
				}

				/**
				 * Re-fetch the order with the new items from the database as it is used for the synchronization
				 * between cart_details and the database.
				 */
				$this->order = $this->_shim_edd_get_order( $this->ID );

				$updated = $this->update_meta( '_edd_payment_meta', $merged_meta );

				if ( false !== $updated ) {
					$saved = true;
				}
			}

			$this->pending = array();
			$saved         = true;
		}

		if ( true === $saved ) {
			$this->setup_payment( $this->ID );

			/**
			 * This action fires anytime that $payment->save() is run, allowing developers to run actions
			 * when a payment is updated
			 */
			do_action( 'edd_payment_saved', $this->ID, $this );
		}

		/**
		 * Update the payment in the object cache
		 */
		$cache_key = md5( 'edd_payment' . $this->ID );
		wp_cache_set( $cache_key, $this, 'payments' );

		return $saved;
	}

	/**
	 * Add a download to a given payment
	 *
	 * @since 2.5
	 *
	 * @param int   $download_id The download to add
	 * @param array $args        Other arguments to pass to the function
	 * @param array $options     List of download options
	 *
	 * @return bool True when successful, false otherwise
	 */
	public function add_download( $download_id = 0, $args = array(), $options = array() ) {
		$download = new EDD_Download( $download_id );

		// Bail if this post isn't a download.
		if ( ! $download || 'download' !== $download->post_type ) {
			return false;
		}

		// Set up defaults.
		$defaults = array(
			'quantity'   => 1,
			'price_id'   => false,
			'item_price' => false,
			'discount'   => 0,
			'tax'        => 0.00,
			'fees'       => array(),
			'status'     => $this->status,
		);

		$args = wp_parse_args( apply_filters( 'edd_payment_add_download_args', $args, $download->ID ), $defaults );

		// Allow overriding the price.
		if ( false !== $args['item_price'] ) {
			$item_price = $args['item_price'];
		} else {

			// Deal with variable pricing.
			if ( edd_has_variable_prices( $download->ID ) ) {
				$prices = get_post_meta( $download->ID, 'edd_variable_prices', true );

				if ( $args['price_id'] && array_key_exists( $args['price_id'], (array) $prices ) ) {
					$item_price = $prices[ $args['price_id'] ]['amount'];
				} else {
					$item_price       = edd_get_lowest_price_option( $download->ID );
					$args['price_id'] = edd_get_lowest_price_id( $download->ID );
				}
			} else {
				$item_price = edd_get_download_price( $download->ID );
			}
		}

		// Sanitizing the price here so we don't have a dozen calls later
		$item_price = edd_sanitize_amount( $item_price );
		$quantity   = edd_item_quantities_enabled() ? absint( $args['quantity'] ) : 1;
		$amount     = round( $item_price * $quantity, edd_currency_decimal_filter() );

		// Setup the downloads meta item
		$new_download = array(
			'id'       => $download->ID,
			'quantity' => $quantity,
		);

		$default_options = array(
			'quantity' => $quantity,
		);

		if ( false !== $args['price_id'] ) {
			$default_options['price_id'] = (int) $args['price_id'];
		}

		$options                 = wp_parse_args( $options, $default_options );
		$new_download['options'] = $options;

		$this->downloads[] = $new_download;

		$discount = $args['discount'];
		$subtotal = $amount;
		$tax      = $args['tax'];

		if ( edd_prices_include_tax() ) {
			$subtotal -= round( $tax, edd_currency_decimal_filter() );
		}

		$fees = 0;
		if ( ! empty( $args['fees'] ) && is_array( $args['fees'] ) ) {
			foreach ( $args['fees'] as $feekey => $fee ) {
				$fees += $fee['amount'];
			}

			$fees = round( $fees, edd_currency_decimal_filter() );
		}

		$total = $subtotal - $discount + $tax + $fees;

		// Do not allow totals to go negative
		if ( $total < 0 ) {
			$total = 0;
		}

		// Silly item_number array
		$item_number = array(
			'id'       => $download->ID,
			'quantity' => $quantity,
			'options'  => $options,
		);

		$this->cart_details[] = array(
			'name'        => edd_get_download_name( $download->ID, $args['price_id'] ),
			'id'          => $download->ID,
			'item_number' => $item_number,
			'item_price'  => round( $item_price, edd_currency_decimal_filter() ),
			'quantity'    => $quantity,
			'discount'    => $discount,
			'subtotal'    => round( $subtotal, edd_currency_decimal_filter() ),
			'tax'         => round( $tax, edd_currency_decimal_filter() ),
			'fees'        => $args['fees'],
			'price'       => round( $total, edd_currency_decimal_filter() ),
		);

		$added_download           = end( $this->cart_details );
		$added_download['action'] = 'add';

		// We need to add the cart index from 3.0+ as it gets stored in the database.
		$added_download['cart_index'] = key( $this->cart_details );

		$this->pending['downloads'][] = $added_download;
		reset( $this->cart_details );

		$this->increase_subtotal( $subtotal - $discount );
		$this->increase_tax( $tax );

		return true;
	}

	/**
	 * Remove a download from the payment
	 *
	 * @since  2.5
	 *
	 * @param  int   $download_id The download ID to remove
	 * @param  array $args        Arguments to pass to identify (quantity, amount, price_id)
	 *
	 * @return bool               If the item was removed or not
	 */
	public function remove_download( $download_id, $args = array() ) {

		// Set some defaults
		$defaults = array(
			'quantity'   => 1,
			'item_price' => false,
			'price_id'   => false,
			'cart_index' => false,
		);

		$args = wp_parse_args( $args, $defaults );

		$download = new EDD_Download( $download_id );

		/**
		 * Bail if this post isn't a download post type.
		 *
		 * We need to allow this to process though for a missing post ID, in case it's a download that was deleted.
		 */
		if ( ! empty( $download->ID ) && 'download' !== $download->post_type ) {
			return false;
		}

		foreach ( $this->downloads as $key => $item ) {
			if ( (int) $download_id !== (int) $item['id'] ) {
				continue;
			}

			if ( false !== $args['price_id'] ) {
				if ( isset( $item['options']['price_id'] ) && (int) $args['price_id'] !== (int) $item['options']['price_id'] ) {
					continue;
				}
			} elseif ( false !== $args['cart_index'] ) {
				$cart_index = absint( $args['cart_index'] );
				$cart_item  = ! empty( $this->cart_details[ $cart_index ] ) ? $this->cart_details[ $cart_index ] : false;

				if ( ! empty( $cart_item ) ) {

					// If the cart index item isn't the same download ID, don't remove it
					if ( $cart_item['id'] !== $item['id'] ) {
						continue;
					}

					// If this item has a price ID, make sure it matches the cart indexed item's price ID before removing
					if ( ( isset( $item['options']['price_id'] ) && isset( $cart_item['item_number']['options']['price_id'] ) )
						&& (int) $item['options']['price_id'] !== (int) $cart_item['item_number']['options']['price_id'] ) {
						continue;
					}
				}
			}

			$item_quantity = $this->downloads[ $key ]['quantity'];

			if ( $item_quantity > $args['quantity'] ) {
				$this->downloads[ $key ]['quantity'] -= $args['quantity'];
				break;
			} else {
				unset( $this->downloads[ $key ] );
				break;
			}
		}

		$found_cart_key = false;

		if ( false === $args['cart_index'] ) {
			foreach ( $this->cart_details as $cart_key => $item ) {
				if ( (int) $download_id !== (int) $item['id'] ) {
					continue;
				}

				if ( false !== $args['price_id'] ) {
					if ( isset( $item['item_number']['options']['price_id'] ) && (int) $args['price_id'] !== (int) $item['item_number']['options']['price_id'] ) {
						continue;
					}
				}

				if ( false !== $args['item_price'] ) {
					if ( isset( $item['item_price'] ) && (float) $args['item_price'] !== (float) $item['item_price'] ) {
						continue;
					}
				}

				$found_cart_key = (int) $cart_key;
				break;
			}
		} else {
			$cart_index = absint( $args['cart_index'] );

			if ( ! array_key_exists( $cart_index, $this->cart_details ) ) {
				return false; // Invalid cart index passed.
			}

			if ( (int) $this->cart_details[ $cart_index ]['id'] !== (int) $download_id ) {
				return false; // We still need the proper Download ID to be sure.
			}

			$found_cart_key = $cart_index;
		}

		$orig_quantity = $this->cart_details[ $found_cart_key ]['quantity'];

		if ( $orig_quantity > $args['quantity'] ) {
			$this->cart_details[ $found_cart_key ]['quantity'] -= $args['quantity'];

			$item_price = $this->cart_details[ $found_cart_key ]['item_price'];
			$tax        = $this->cart_details[ $found_cart_key ]['tax'];
			$discount   = ! empty( $this->cart_details[ $found_cart_key ]['discount'] ) ? $this->cart_details[ $found_cart_key ]['discount'] : 0;

			// The total reduction equals the number removed * the item_price
			$total_reduced = round( $item_price * $args['quantity'], edd_currency_decimal_filter() );
			$tax_reduced   = round( ( $tax / $orig_quantity ) * $args['quantity'], edd_currency_decimal_filter() );

			$new_quantity = $this->cart_details[ $found_cart_key ]['quantity'];
			$new_tax      = $this->cart_details[ $found_cart_key ]['tax'] - $tax_reduced;
			$new_subtotal = $new_quantity * $item_price;
			$new_discount = 0;
			$new_total    = 0;

			$this->cart_details[ $found_cart_key ]['subtotal'] = $new_subtotal;
			$this->cart_details[ $found_cart_key ]['discount'] = $new_discount;
			$this->cart_details[ $found_cart_key ]['tax']      = $new_tax;
			$this->cart_details[ $found_cart_key ]['price']    = $new_subtotal - $new_discount + $new_tax;
		} else {
			$total_reduced = $this->cart_details[ $found_cart_key ]['item_price'];
			$tax_reduced   = $this->cart_details[ $found_cart_key ]['tax'];

			$found_fees = array();

			if ( ! empty( $this->cart_details[ $found_cart_key ]['fees'] ) ) {
				$found_fees = $this->cart_details[ $found_cart_key ]['fees'];

				foreach ( $found_fees as $key => $fee ) {
					$this->remove_fee( $key );
				}
			}

			unset( $this->cart_details[ $found_cart_key ] );
		}

		$pending_args               = $args;
		$pending_args['id']         = $download_id;
		$pending_args['amount']     = $total_reduced;
		$pending_args['price_id']   = false !== $args['price_id'] ? $args['price_id'] : false;
		$pending_args['quantity']   = $args['quantity'];
		$pending_args['action']     = 'remove';
		$pending_args['fees']       = isset( $found_fees ) ? $found_fees : array();
		$pending_args['cart_index'] = $found_cart_key;

		$this->pending['downloads'][] = $pending_args;

		/**
		 * Remove/modify the order item from the database at this point in lieu of having to synchronise with cart_details
		 * later on in update_meta().
		 */

		// Find the order item based on the cart index.
		$order_item = array_filter(
			$this->order->items,
			function ( $i ) use ( $found_cart_key ) {
				/** @var EDD\Orders\Order_Item $i */

				return (int) $i->cart_index === (int) $found_cart_key;
			}
		);

		// Reset array index.
		$order_item = array_values( $order_item );

		$order_item = ( 1 === count( $order_item ) )
			? $order_item[0]
			: null;

		/** @var EDD\Orders\Order_Item $order_item */

		// Ensure an order item exists in the database.
		if ( ! is_null( $order_item ) ) {

			// Update the order item if the quantity is being modified.
			if ( isset( $this->cart_details[ $found_cart_key ] ) ) {
				edd_update_order_item(
					$order_item->id,
					array(
						'quantity' => $this->cart_details[ $found_cart_key ]['quantity'],
						'amount'   => $this->cart_details[ $found_cart_key ]['item_price'],
						'subtotal' => $this->cart_details[ $found_cart_key ]['subtotal'],
						'discount' => $this->cart_details[ $found_cart_key ]['discount'],
						'tax'      => $this->cart_details[ $found_cart_key ]['tax'],
						'total'    => $this->cart_details[ $found_cart_key ]['price'],
					)
				);

				// Remove the order item.
			} else {
				edd_delete_order_item( $order_item->id );
			}
		}

		$this->decrease_subtotal( $total_reduced );
		$this->decrease_tax( $tax_reduced );

		return true;
	}

	/**
	 * Alter a limited set of properties of a cart item
	 *
	 * @since 2.7
	 *
	 * @param bool  $cart_index
	 * @param array $args
	 *
	 * @return bool
	 */
	public function modify_cart_item( $cart_index = false, $args = array() ) {
		if ( false === $cart_index ) {
			return false;
		}

		if ( ! array_key_exists( $cart_index, $this->cart_details ) ) {
			return false;
		}

		$current_args  = $this->cart_details[ $cart_index ];
		$allowed_items = apply_filters(
			'edd_allowed_cart_item_modifications',
			array(
				'item_price',
				'tax',
				'discount',
				'quantity',
			)
		);

		// Remove any items we don't want to modify.
		foreach ( $args as $key => $arg ) {
			if ( ! in_array( $key, $allowed_items, true ) ) {
				unset( $args[ $key ] );
			}
		}

		$merged_item = array_merge( $current_args, $args );

		if ( md5( json_encode( $current_args ) ) === md5( json_encode( $merged_item ) ) ) {
			return false;
		}

		// Format the item_price correctly now
		$merged_item['item_price'] = edd_sanitize_amount( $merged_item['item_price'] );

		$discount = isset( $merged_item['discount'] )
			? (float) $merged_item['discount']
			: 0.00;

		$new_subtotal                      = floatval( $merged_item['item_price'] ) * $merged_item['quantity'];
		$merged_item['tax']                = edd_sanitize_amount( $merged_item['tax'] );
		$merged_item['price']              = edd_prices_include_tax() ? $new_subtotal - $discount : $new_subtotal + $merged_item['tax'] - $discount;
		$this->cart_details[ $cart_index ] = $merged_item;

		// Sort the current and new args, and checksum them. If no changes. No need to fire a modification.
		ksort( $current_args );
		ksort( $merged_item );

		$modified_download                  = $merged_item;
		$modified_download['action']        = 'modify';
		$modified_download['previous_data'] = $current_args;

		$this->pending['downloads'][] = $modified_download;

		if ( $new_subtotal > $current_args['subtotal'] ) {
			$this->increase_subtotal( ( $new_subtotal - (float) $modified_download['discount'] ) - (float) $current_args['subtotal'] );
		} else {
			$this->decrease_subtotal( (float) $current_args['subtotal'] - ( $new_subtotal - (float) $modified_download['discount'] ) );
		}

		if ( (float) $modified_download['tax'] > (float) $current_args['tax'] ) {
			$this->increase_tax( (float) $modified_download['tax'] - (float) $current_args['tax'] );
		} else {
			$this->decrease_tax( (float) $current_args['tax'] - (float) $modified_download['tax'] );
		}

		/**
		 * Remove/modify the order item from the database at this point in lieu of having to synchronise with cart_details
		 * later on in update_meta().
		 */

		// Find the order item.
		$order_item_id = 0;

		foreach ( $this->order->items as $item ) {
			if ( (int) $item->cart_index === (int) $cart_index ) {
				$order_item_id = $item->id;
				break;
			}
		}

		if ( $order_item_id ) {
			edd_update_order_item(
				$order_item_id,
				array(
					'quantity' => $modified_download['quantity'],
					'amount'   => (float) $modified_download['item_price'],
					'subtotal' => (float) $new_subtotal,
					'tax'      => (float) $modified_download['tax'],
					'discount' => (float) $modified_download['discount'],
					'total'    => (float) $modified_download['price'],
				)
			);
		}

		return true;
	}

	/**
	 * Add a fee to a given payment.
	 *
	 * @since 2.5
	 *
	 * @param array $args   Array of arguments for the fee to add.
	 * @param bool  $global
	 *
	 * @return bool If the fee was added.
	 */
	public function add_fee( $args, $global = true ) {
		$default_args = array(
			'label'       => '',
			'amount'      => 0,
			'type'        => 'fee',
			'id'          => '',
			'no_tax'      => false,
			'download_id' => 0,
		);

		$fee          = wp_parse_args( $args, $default_args );
		$this->fees[] = $fee;

		$added_fee               = $fee;
		$added_fee['action']     = 'add';
		$this->pending['fees'][] = $added_fee;
		reset( $this->fees );

		$this->increase_fees( $fee['amount'] );

		return true;
	}

	/**
	 * Remove a fee from the payment
	 *
	 * @since  2.5
	 *
	 * @param  int $key The array key index to remove
	 *
	 * @return bool     If the fee was removed successfully
	 */
	public function remove_fee( $key ) {
		$removed = $this->remove_fee_by( 'index', $key );

		return $removed;
	}

	/**
	 * Remove a fee by the defined attributed
	 *
	 * @since 2.5
	 *
	 * @param string     $key    The key to remove by
	 * @param int|string $value  The value to search for
	 * @param boolean    $global False - removes the first value it finds, True - removes all matches
	 *
	 * @return boolean If the item is removed.
	 */
	public function remove_fee_by( $key, $value, $global = false ) {
		$allowed_fee_keys = apply_filters(
			'edd_payment_fee_keys',
			array(
				'index',
				'label',
				'amount',
				'type',
			)
		);

		if ( ! in_array( $key, $allowed_fee_keys, true ) ) {
			return false;
		}

		$removed = false;

		if ( 'index' === $key && array_key_exists( $value, $this->fees ) ) {
			$removed_fee             = $this->fees[ $value ];
			$removed_fee['action']   = 'remove';
			$this->pending['fees'][] = $removed_fee;

			$this->decrease_fees( $removed_fee['amount'] );

			unset( $this->fees[ $value ] );
			$removed = true;
		} elseif ( 'index' !== $key ) {
			foreach ( $this->fees as $index => $fee ) {
				if ( isset( $fee[ $key ] ) && $fee[ $key ] === $value ) {
					$removed_fee             = $fee;
					$removed_fee['action']   = 'remove';
					$this->pending['fees'][] = $removed_fee;

					$this->decrease_fees( $removed_fee['amount'] );

					unset( $this->fees[ $index ] );
					$removed = true;

					if ( false === $global ) {
						break;
					}
				}
			}
		}

		/**
		 * Remove the fee from the database at this point in lieu of having to synchronise with payment meta
		 * later on in update_meta()/save().
		 */
		if ( true === $removed ) {
			$fee = end( $this->pending['fees'] );

			$fee_id = 'index' === $key
				? $value
				: null;

			// Find by fee ID, if set.
			if ( ! is_null( $fee_id ) ) {
				foreach ( $this->order->get_fees() as $id => $f ) {
					if ( $id === $fee_id ) {
						edd_delete_order_adjustment( $f->id );

						if ( false === $global ) {
							break;
						}
					}
				}

				// Find by fee label.
			} else {
				foreach ( $this->order->get_fees() as $f ) {
					if ( $fee['label'] === $f->description ) {
						edd_delete_order_adjustment( $f->id );

						if ( false === $global ) {
							break;
						}
					}
				}
			}
		}

		return $removed;
	}

	/**
	 * Get the fees, filterable by type.
	 *
	 * @since 2.5
	 *
	 * @param string $type All, item, fee.
	 *
	 * @return array Fees for the type specified.
	 */
	public function get_fees( $type = 'all' ) {
		$fees = array();

		if ( ! empty( $this->fees ) && is_array( $this->fees ) ) {
			foreach ( $this->fees as $fee_id => $fee ) {
				if ( 'all' !== $type && ! empty( $fee['type'] ) && $type !== $fee['type'] ) {
					continue;
				}

				$fee['id'] = $fee_id;
				$fees[]    = $fee;

			}
		}

		return apply_filters( 'edd_get_payment_fees', $fees, $this->ID, $this );
	}

	/**
	 * Add a note to an order.
	 *
	 * @since 2.5
	 * @since 3.0 Return true if note was inserted successfully.
	 *
	 * @param string $note The note to add.
	 *
	 * @return bool Whether or not the note was inserted.
	 */
	public function add_note( $note = '' ) {

		// Bail if no note specified.
		if ( empty( $note ) ) {
			return false;
		}

		$note_id = edd_insert_payment_note( $this->ID, $note );

		if ( $note_id ) {
			return true;
		}

		return false;
	}

	/**
	 * Increase the payment's subtotal
	 *
	 * @since  2.5
	 *
	 * @param  float $amount The amount to increase the payment subtotal by
	 *
	 * @return void
	 */
	private function increase_subtotal( $amount = 0.00 ) {
		$amount          = (float) $amount;
		$this->subtotal += $amount;

		$this->recalculate_total();
	}

	/**
	 * Decrease the payment's subtotal
	 *
	 * @since  2.5
	 *
	 * @param  float $amount The amount to decrease the payment subtotal by
	 *
	 * @return void
	 */
	private function decrease_subtotal( $amount = 0.00 ) {
		$amount          = (float) $amount;
		$this->subtotal -= $amount;

		if ( $this->subtotal < 0 ) {
			$this->subtotal = 0;
		}

		$this->recalculate_total();
	}

	/**
	 * Increase the payment's subtotal
	 *
	 * @since  2.5
	 *
	 * @param  float $amount The amount to increase the payment subtotal by
	 *
	 * @return void
	 */
	private function increase_fees( $amount = 0.00 ) {
		$amount            = (float) $amount;
		$this->fees_total += $amount;

		$this->recalculate_total();
	}

	/**
	 * Decrease the payment's subtotal
	 *
	 * @since  2.5
	 *
	 * @param  float $amount The amount to decrease the payment subtotal by
	 *
	 * @return void
	 */
	private function decrease_fees( $amount = 0.00 ) {
		$amount            = (float) $amount;
		$this->fees_total -= $amount;

		if ( $this->fees_total < 0 ) {
			$this->fees_total = 0;
		}

		$this->recalculate_total();
	}

	/**
	 * Set or update the total for a payment
	 *
	 * @since 2.5
	 * @return void
	 */
	private function recalculate_total() {
		$this->total = $this->subtotal + $this->tax + $this->fees_total;
	}

	/**
	 * Increase the payment's tax by the provided amount
	 *
	 * @since  2.5
	 *
	 * @param  float $amount The amount to increase the payment tax by
	 *
	 * @return void
	 */
	public function increase_tax( $amount = 0.00 ) {
		$amount     = (float) $amount;
		$this->tax += $amount;

		$this->recalculate_total();
	}

	/**
	 * Decrease the payment's tax by the provided amount
	 *
	 * @since  2.5
	 *
	 * @param  float $amount The amount to reduce the payment tax by
	 *
	 * @return void
	 */
	public function decrease_tax( $amount = 0.00 ) {
		$amount     = (float) $amount;
		$this->tax -= $amount;

		if ( $this->tax < 0 ) {
			$this->tax = 0;
		}

		$this->recalculate_total();
	}

	/**
	 * Change the status of an order to refunded, and run the necessary changes.
	 *
	 * @since 2.5.7
	 */
	public function refund() {
		$this->old_status        = $this->status;
		$this->status            = 'refunded';
		$this->pending['status'] = $this->status;

		$this->save();
	}

	/**
	 * Set the order status and run any status specific changes necessary.
	 *
	 * @since 2.5
	 * @since 3.0 Updated to work with the new refunds API and new query methods
	 *            introduced.
	 *
	 * @param string $status New order status.
	 * @return bool True if the status was successfully updated, false otherwise.
	 */
	public function update_status( $status = '' ) {

		if ( ! $this->order ) {
			return false;
		}

		// Bail if an empty status is passed.
		if ( empty( $status ) || ! $status ) {
			return false;
		}

		// Override to `complete` since 3.0.
		if ( 'completed' === $status || 'publish' === $status ) {
			$status = 'complete';
		}

		// Get the old (current) status.
		$old_status = ! empty( $this->old_status )
			? $this->old_status
			: false;

		// We do not allow status changes if the status is the same to that stored in the database.
		// This prevents the `edd_update_payment_status` action from being triggered unnecessarily.
		if ( $old_status === $status ) {
			return false;
		}

		$do_change = apply_filters( 'edd_should_update_payment_status', true, $this->ID, $status, $old_status );

		$updated = false;

		if ( $do_change ) {
			do_action( 'edd_before_payment_status_change', $this->ID, $status, $old_status );

			$update_fields = apply_filters(
				'edd_update_payment_status_fields',
				array(
					'status' => $status,
				)
			);

			// Account for someone filtering and using `post_status`
			if ( isset( $update_fields['post_status'] ) ) {
				_edd_generic_deprecated( 'EDD_Payment::update_status', '3.0', __( 'Array key "post_status" is no longer a supported attribute for the "edd_update_payment_status_fields" filter. Please use "status" instead.', 'easy-digital-downloads' ) );

				$update_fields['status'] = $update_fields['post_status'];
				unset( $update_fields['post_status'] );
			}

			// Strip data that does not need to be passed to `edd_update_order()`.
			unset( $update_fields['ID'] );

			/**
			 * As per the new refund API introduced in 3.0, the order is only
			 * marked as refunded when `EDD_Payment::process_refund()` has called
			 * `edd_refund_order()` and a new order has been generated with a
			 * type of `refund`.
			 *
			 * @since 3.0
			 * @see EDD_Payment::process_refund()
			 * @see edd_refund_order()
			 * @see https://github.com/easydigitaldownloads/easy-digital-downloads/issues/2721
			 */
			if ( 'refunded' !== $status ) {
				edd_update_order( $this->ID, $update_fields );

				// Update each order item.
				foreach ( $this->order->items as $item ) {
					edd_update_order_item( $item->id, $update_fields );
				}
			}

			/**
			 * Albeit the order itself is not updated (for refunds), the EDD_Payment
			 * class vars are updated for backwards compatibility purposes and
			 * for anyone/anything that is checking that the status of the object
			 * has successfully changed.
			 */
			$this->status      = $status;
			$this->post_status = $status;

			$this->status_nicename = $this->get_status_nicename();

			// Process any specific status functions.
			switch ( $status ) {
				case 'refunded':
					$this->process_refund();
					do_action( 'edd_update_payment_status', $this->ID, $status, $old_status );
					break;
				case 'failed':
					$this->process_failure();
					break;
				case 'pending' || 'processing':
					$this->process_pending();
					break;
			}
		}

		return $updated;
	}

	/**
	 * Get a post meta item for the payment
	 *
	 * @since  2.5
	 *
	 * @param  string  $meta_key The Meta Key
	 * @param  boolean $single   Return single item or array
	 *
	 * @return mixed             The value from the post meta
	 */
	public function get_meta( $meta_key = '_edd_payment_meta', $single = true ) {
		if ( $this->is_edd_payment ) {
			return get_post_meta( $this->ID, $meta_key, $single );
		}
		$meta = edd_get_order_meta( $this->ID, $meta_key, $single );

		// Backwards compatibility.
		switch ( $meta_key ) {
			case '_edd_payment_purchase_key':
				$meta = $this->order->payment_key;
				break;

			case '_edd_payment_transaction_id':
				$transactions = array_values(
					edd_get_order_transactions(
						array(
							'number'      => 1,
							'object_id'   => $this->ID,
							'object_type' => 'order',
							'orderby'     => 'date_created',
							'order'       => 'ASC',
							'fields'      => 'transaction_id',
						)
					)
				);

				$transaction_id = '';

				if ( $transactions ) {
					$transaction_id = esc_attr( $transactions[0] );
				}

				$meta = $transaction_id;
				break;

			case '_edd_payment_user_email':
				$meta = $this->order->email;
				break;

			case '_edd_completed_date':
				$meta = $this->completed_date;
				break;

			case '_edd_payment_gateway':
				$meta = $this->order->gateway;
				break;

			case '_edd_payment_user_id':
				$meta = $this->order->user_id;
				break;

			case '_edd_payment_user_ip':
				$meta = $this->order->ip;
				break;

			case '_edd_payment_mode':
				$meta = $this->order->mode;
				break;

			case '_edd_payment_tax_rate':
				$meta = $this->order->get_tax_rate();
				break;

			case '_edd_payment_customer_id':
				$meta = $this->order->customer_id;
				break;

			case '_edd_payment_tax':
				$meta = $this->order->tax;
				break;

			case '_edd_payment_number':
				$meta = $this->order->get_number();
				break;
		}

		if ( '_edd_payment_meta' === $meta_key ) {
			if ( empty( $meta ) ) {
				$meta = array();
			}

			// Payment meta was simplified in EDD v1.5, so these are here for backwards compatibility
			if ( empty( $meta['key'] ) ) {
				$meta['key'] = $this->key;
			}

			if ( empty( $meta['email'] ) ) {
				$meta['email'] = $this->email;
			}

			if ( empty( $meta['date'] ) ) {
				$meta['date'] = $this->date;
			}

			// We need to back fill the returned meta for backwards compatibility purposes.
			$meta['key']          = $this->key;
			$meta['email']        = $this->email;
			$meta['date']         = $this->date;
			$meta['user_info']    = $this->user_info;
			$meta['downloads']    = $this->downloads;
			$meta['cart_details'] = $this->cart_details;
			$meta['fees']         = $this->fees;
			$meta['currency']     = $this->currency;
			$meta['tax']          = $this->tax;

			$migrated_payment_meta = edd_get_order_meta( $this->ID, 'payment_meta', true );

			// This is no longer stored in _edd_payment_meta.
			$core_meta_keys = array( 'key', 'email', 'date', 'user_info', 'downloads', 'cart_details', 'quantity', 'discount', 'subtotal', 'tax', 'fees', 'currency' );

			$migrated_payment_meta = array_diff_key( (array) $migrated_payment_meta, array_flip( $core_meta_keys ) );

			if ( is_array( $migrated_payment_meta ) && 0 < count( $migrated_payment_meta ) ) {
				$meta = array_merge( $meta, $migrated_payment_meta );
			}

			// #5228 Fix possible data issue introduced in 2.6.12
			if ( is_array( $meta ) && isset( $meta[0] ) ) {
				$bad_meta = $meta[0];
				unset( $meta[0] );

				if ( is_array( $bad_meta ) ) {
					$meta = array_merge( $meta, $bad_meta );
				}
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
	 * Update the order meta.
	 *
	 * @since 2.5
	 * @since 3.0 Updated to use the new custom tables.
	 *
	 * @param string $meta_key   The meta key to update.
	 * @param string $meta_value The meta value.
	 * @param string $prev_value Previous meta value.
	 *
	 * @return int|bool Meta ID if the key didn't exist, true on successful update, false on failure.
	 */
	public function update_meta( $meta_key = '', $meta_value = '', $prev_value = '' ) {
		if ( empty( $meta_key ) || empty( $this->ID ) ) {
			return false;
		}

		$meta_value = apply_filters( 'edd_update_payment_meta_' . $meta_key, $meta_value, $this->ID );

		switch ( $meta_key ) {
			case '_edd_payment_meta':
				if ( isset( $meta_value['tax'] ) && ! empty( $meta_value['tax'] ) ) {
					edd_update_order(
						$this->ID,
						array(
							'tax' => $meta_value['tax'],
						)
					);
				}

				if ( isset( $meta_value['key'] ) && ! empty( $meta_value['key'] ) ) {
					edd_update_order(
						$this->ID,
						array(
							'key' => $meta_value['key'],
						)
					);
				}

				if ( isset( $meta_value['email'] ) && ! empty( $meta_value['email'] ) ) {
					edd_update_order(
						$this->ID,
						array(
							'email' => $meta_value['email'],
						)
					);
				}

				if ( isset( $meta_value['currency'] ) && ! empty( $meta_value['currency'] ) ) {
					edd_update_order(
						$this->ID,
						array(
							'currency' => $meta_value['currency'],
						)
					);
				}

				if ( isset( $meta_value['user_info'] ) && ! empty( $meta_value['user_info'] ) ) {

					// Handle discounts.
					$discounts = isset( $meta_value['user_info']['discount'] ) && ! empty( $meta_value['user_info']['discount'] )
						? $meta_value['user_info']['discount']
						: array();

					if ( ! is_array( $discounts ) ) {
						$discounts = explode( ',', $discounts );
					}

					if ( ! empty( $discounts ) && ( 'none' !== $discounts[0] ) ) {
						foreach ( $discounts as $discount ) {

							/** @var EDD_Discount $discount */
							$discount = edd_get_discount_by( 'code', $discount );

							if ( false === $discount ) {
								continue;
							}

							$adjustments = $this->order->adjustments;

							$found_discount = array_filter(
								$adjustments,
								function ( $adjustment ) use ( $discount ) {
									/** @var EDD\Orders\Order_Adjustment $adjustment */

									return (string) $adjustment->description === (string) $discount->code;
								}
							);

							// Discount exists so update the amount.
							if ( 1 === count( $found_discount ) ) {
								$found_discount = $found_discount[0];

								/** @var EDD\Orders\Order_Adjustment $found_discount */

								edd_update_order_adjustment(
									$found_discount->id,
									array(
										'amount' => $this->subtotal - $discount->get_discounted_amount( $this->subtotal ),
									)
								);
							} else {
								// Add the discount as an adjustment.
								edd_add_order_adjustment(
									array(
										'object_id'   => $this->ID,
										'object_type' => 'order',
										'type_id'     => $discount->id,
										'type'        => 'discount',
										'description' => $discount->code,
										'subtotal'    => $this->subtotal - $discount->get_discounted_amount( $this->subtotal ),
										'total'       => $this->subtotal - $discount->get_discounted_amount( $this->subtotal ),
									)
								);
							}
						}
					}

					$user_info = array_diff_key(
						$meta_value['user_info'],
						array_flip(
							array(
								'id',
								'email',
								'discount',
							)
						)
					);

					$defaults = array(
						'first_name' => '',
						'last_name'  => '',
						'address'    => array(
							'line1'   => '',
							'line2'   => '',
							'city'    => '',
							'state'   => '',
							'country' => '',
							'zip'     => '',
						),
					);

					if ( isset( $user_info['address'] ) ) {
						$user_info['address'] = wp_parse_args( $user_info['address'], $defaults['address'] );
					}

					$user_info = wp_parse_args( $user_info, $defaults );
					$name      = $user_info['first_name'] . ' ' . $user_info['last_name'];

					if ( null !== $this->order && $this->order->get_address()->id ) {
						$order_address = $this->order->get_address();

						edd_update_order_address(
							$order_address->id,
							array(
								'name'        => $name,
								'address'     => $user_info['address']['line1'],
								'address2'    => $user_info['address']['line2'],
								'city'        => $user_info['address']['city'],
								'region'      => $user_info['address']['state'],
								'postal_code' => $user_info['address']['zip'],
								'country'     => $user_info['address']['country'],
							)
						);
					} else {
						edd_add_order_address(
							array(
								'order_id'    => $this->ID,
								'name'        => $name,
								'address'     => $user_info['address']['line1'],
								'address2'    => $user_info['address']['line2'],
								'city'        => $user_info['address']['city'],
								'region'      => $user_info['address']['state'],
								'postal_code' => $user_info['address']['zip'],
								'country'     => $user_info['address']['country'],
							)
						);
					}

					$remaining_user_info = array_diff_key(
						$meta_value['user_info'],
						array_flip(
							array(
								'id',
								'first_name',
								'last_name',
								'email',
								'address',
								'discount',
							)
						)
					);

					if ( ! empty( $remaining_user_info ) ) {
						edd_update_order_meta( $this->ID, 'user_info', $remaining_user_info );
					}
				}

				if ( isset( $meta_value['fees'] ) && ! empty( $meta_value['fees'] ) ) {
					foreach ( $meta_value['fees'] as $fee_id => $fee ) {
						if ( ! empty( $fee['download_id'] ) && 0 < $fee['download_id'] ) {
							$order_item_id = edd_get_order_items(
								array(
									'number'     => 1,
									'order_id'   => $this->ID,
									'product_id' => $fee['download_id'],
									'price_id'   => isset( $fee['price_id'] ) && ! is_null( $fee['price_id'] ) ? intval( $fee['price_id'] ) : 0,
									'fields'     => 'ids',
								)
							);

							if ( is_array( $order_item_id ) ) {
								$order_item_id = (int) $order_item_id[0];
							}

							$adjustment_id = edd_get_order_adjustments(
								array(
									'number'      => 1,
									'object_id'   => $order_item_id,
									'object_type' => 'order_item',
									'type'        => 'fee',
									'fields'      => 'ids',
									'type_key'    => $fee_id,
								)
							);

							if ( is_array( $adjustment_id ) && ! empty( $adjustment_id ) ) {
								$adjustment_id = $adjustment_id[0];

								edd_update_order_adjustment(
									$adjustment_id,
									array(
										'description' => $fee['label'],
										'subtotal'    => (float) $fee['amount'],
									)
								);
							} else {
								add_filter( 'edd_prices_include_tax', '__return_false' );

								$tax = ( isset( $fee['no_tax'] ) && false === $fee['no_tax'] ) || $fee['amount'] < 0
									? floatval( edd_calculate_tax( $fee['amount'] ) )
									: 0.00;

								remove_filter( 'edd_prices_include_tax', '__return_false' );

								$adjustment_id = edd_add_order_adjustment(
									array(
										'object_id'   => $order_item_id,
										'object_type' => 'order_item',
										'type_key'    => $fee_id,
										'type'        => 'fee',
										'description' => $fee['label'],
										'subtotal'    => floatval( $fee['amount'] ),
										'tax'         => $tax,
										'total'       => floatval( $fee['amount'] ) + $tax,
									)
								);

								edd_add_extra_fee_order_adjustment_meta( $adjustment_id, $fee );
							}
						} else {
							$adjustment_id = edd_get_order_adjustments(
								array(
									'number'      => 1,
									'object_id'   => $this->ID,
									'object_type' => 'order',
									'type'        => 'fee',
									'fields'      => 'ids',
									'type_key'    => $fee_id,
								)
							);

							if ( is_array( $adjustment_id ) && ! empty( $adjustment_id ) ) {
								$adjustment_id = $adjustment_id[0];

								edd_update_order_adjustment(
									$adjustment_id,
									array(
										'description' => $fee['label'],
										'subtotal'    => (float) $fee['amount'],
									)
								);
							} else {
								add_filter( 'edd_prices_include_tax', '__return_false' );

								$tax = ( isset( $fee['no_tax'] ) && false === $fee['no_tax'] ) || $fee['amount'] < 0
									? floatval( edd_calculate_tax( $fee['amount'] ) )
									: 0.00;

								remove_filter( 'edd_prices_include_tax', '__return_false' );

								$adjustment_id = edd_add_order_adjustment(
									array(
										'object_id'   => $this->ID,
										'object_type' => 'order',
										'type_key'    => $fee_id,
										'type'        => 'fee',
										'description' => $fee['label'],
										'subtotal'    => floatval( $fee['amount'] ),
										'tax'         => $tax,
										'total'       => floatval( $fee['amount'] ) + $tax,
									)
								);
							}
						}
					}
				}

				/**
				 * As of 3.0, the cart details array is no longer used for payments; it's purpose is for backwards compatibility
				 * purposes only. Due to the way EDD_Payment, the cart_details array needs to be synchronized with the data
				 * stored in the database as it could be different to the other class vars in the instance of EDD_Payment.
				 */

				if ( isset( $meta_value['cart_details'] ) && ! empty( $meta_value['cart_details'] ) ) {

					// Totals need to be updated based on cart details.
					$new_tax      = 0.00;
					$new_subtotal = 0.00;

					foreach ( $meta_value['cart_details'] as $key => $item ) {
						$order_item_id = edd_get_order_items(
							array(
								'number'       => 1,
								'fields'       => 'ids',
								'order_id'     => $this->ID,
								'product_id'   => $item['id'],
								'product_name' => $item['name'],
							)
						);

						$item['item_number']['options']['price_id'] = isset( $item['item_number']['options']['price_id'] ) && is_numeric( $item['item_number']['options']['price_id'] )
							? absint( $item['item_number']['options']['price_id'] )
							: null;

						if ( is_array( $order_item_id ) && ! empty( $order_item_id ) ) {
							$order_item_id = $order_item_id[0];

							edd_update_order_item(
								$order_item_id,
								array(
									'order_id'     => $this->ID,
									'product_id'   => $item['id'],
									'product_name' => $item['name'],
									'price_id'     => $item['item_number']['options']['price_id'],
									'cart_index'   => $key,
									'quantity'     => $item['quantity'],
									'amount'       => $item['item_price'],
									'subtotal'     => $item['subtotal'],
									'discount'     => $item['discount'],
									'tax'          => $item['tax'],
									'total'        => $item['price'],
								)
							);

							$new_subtotal = $item['subtotal'];
							$new_tax     += $item['tax'];
						} else {
							$order_item_id = edd_add_order_item(
								array(
									'order_id'     => $this->ID,
									'product_id'   => $item['id'],
									'product_name' => $item['name'],
									'price_id'     => $item['item_number']['options']['price_id'],
									'cart_index'   => $key,
									'quantity'     => $item['quantity'],
									'amount'       => $item['item_price'],
									'subtotal'     => $item['subtotal'],
									'discount'     => $item['discount'],
									'tax'          => $item['tax'],
									'total'        => $item['price'],
									'status'       => ! empty( $item['status'] ) ? $item->status : $this->status,
								)
							);

							$new_tax      += $item['tax'];
							$new_subtotal += $item['subtotal'];

							if ( isset( $item['fees'] ) && ! empty( $item['fees'] ) ) {
								foreach ( $item['fees'] as $fee_id => $fee ) {
									add_filter( 'edd_prices_include_tax', '__return_false' );

									$tax = ( isset( $fee['no_tax'] ) && false === $fee['no_tax'] ) || $fee['amount'] < 0
										? floatval( edd_calculate_tax( $fee['amount'] ) )
										: 0.00;

									remove_filter( 'edd_prices_include_tax', '__return_false' );

									$adjustment_id = edd_add_order_adjustment(
										array(
											'object_id'   => $order_item_id,
											'object_type' => 'order_item',
											'type_key'    => $fee_id,
											'type'        => 'fee',
											'description' => $fee['label'],
											'subtotal'    => floatval( $fee['amount'] ),
											'tax'         => $tax,
											'total'       => floatval( $fee['amount'] ) + $tax,
										)
									);

									$new_tax += $tax;
								}
							}
						}
					}
				}

				// This is no longer stored in _edd_payment_meta.
				$core_meta_keys = array( 'key', 'email', 'date', 'user_info', 'downloads', 'cart_details', 'quantity', 'discount', 'subtotal', 'tax', 'fees', 'currency' );

				$meta_value = array_diff_key( $meta_value, array_flip( $core_meta_keys ) );

				// If the above checks fall through, store anything else in a "payment_meta" meta key.
				return ! empty( $meta_value ) ? edd_update_order_meta( $this->ID, 'payment_meta', $meta_value ) : false;
			case '_edd_completed_date':
				$meta_value = empty( $meta_value )
					? null
					: $meta_value;

				edd_update_order(
					$this->ID,
					array(
						'date_completed' => $meta_value,
					)
				);
				return true;
			case '_edd_payment_gateway':
				edd_update_order(
					$this->ID,
					array(
						'gateway' => $meta_value,
					)
				);
				return true;
			case '_edd_payment_user_id':
				edd_update_order(
					$this->ID,
					array(
						'user_id' => $meta_value,
					)
				);
				return true;
			case '_edd_payment_user_email':
			case 'email':
				edd_update_order(
					$this->ID,
					array(
						'email' => $meta_value,
					)
				);
				return true;
			case '_edd_payment_user_ip':
				edd_update_order(
					$this->ID,
					array(
						'ip' => $meta_value,
					)
				);
				return true;
			case '_edd_payment_purchase_key':
			case 'key':
				edd_update_order(
					$this->ID,
					array(
						'payment_key' => $meta_value,
					)
				);
				return true;
			case '_edd_payment_mode':
				edd_update_order(
					$this->ID,
					array(
						'mode' => $meta_value,
					)
				);
				return true;
			case '_edd_payment_tax_rate':
				$tax_rate = $this->maybe_update_tax_rate( $meta_value );
				edd_update_order_meta( $this->ID, 'tax_rate', $tax_rate, $prev_value );
				return true;
			case '_edd_payment_customer_id':
				edd_update_order(
					$this->ID,
					array(
						'customer_id' => $meta_value,
					)
				);
				return true;
			case '_edd_payment_total':
				edd_update_order(
					$this->ID,
					array(
						'total' => $meta_value,
					)
				);
				return true;
			case '_edd_payment_tax':
				edd_update_order(
					$this->ID,
					array(
						'tax' => $meta_value,
					)
				);
				return true;
			case '_edd_payment_number':
				edd_update_order(
					$this->ID,
					array(
						'order_number' => $meta_value,
					)
				);
				return true;
			case '_edd_payment_transaction_id':
			case 'transaction_id':
				$transaction_ids = array_values(
					edd_get_order_transactions(
						array(
							'fields'      => 'ids',
							'number'      => 1,
							'object_id'   => $this->ID,
							'object_type' => 'order',
							'orderby'     => 'date_created',
							'order'       => 'ASC',
						)
					)
				);

				if ( $transaction_ids ) {
					$transaction_id = $transaction_ids[0];

					return edd_update_order_transaction(
						$transaction_id,
						array(
							'transaction_id' => $meta_value,
							'gateway'        => $this->gateway,
						)
					);
				} else {
					return edd_add_order_transaction(
						array(
							'object_id'      => $this->ID,
							'object_type'    => 'order',
							'transaction_id' => $meta_value,
							'gateway'        => $this->gateway,
							'status'         => 'complete',
							'total'          => $this->total,
						)
					);
				}
		}

		return edd_update_order_meta( $this->ID, $meta_key, $meta_value, $prev_value );
	}

	/**
	 * Add an item to the payment meta
	 *
	 * @since 2.8
	 *
	 * @param string $meta_key
	 * @param string $meta_value
	 * @param bool   $unique
	 *
	 * @return bool|false|int
	 */
	public function add_meta( $meta_key = '', $meta_value = '', $unique = false ) {
		if ( empty( $meta_key ) ) {
			return false;
		}

		return edd_add_order_meta( $this->ID, $meta_key, $meta_value, $unique );
	}

	/**
	 * Delete an item from payment meta
	 *
	 * @since 2.8
	 *
	 * @param string $meta_key
	 * @param string $meta_value
	 *
	 * @return bool
	 */
	public function delete_meta( $meta_key = '', $meta_value = '' ) {
		if ( empty( $meta_key ) ) {
			return false;
		}

		return edd_delete_order_meta( $this->ID, $meta_key, $meta_value );
	}

	/**
	 * Determines if this payment is able to be resumed by the user.
	 *
	 * @since 2.7
	 *
	 * @return bool
	 */
	public function is_recoverable() {
		return $this->order->is_recoverable();
	}

	/**
	 * Returns the URL that a customer can use to resume a payment, or false if it's not recoverable.
	 *
	 * @since 2.7
	 *
	 * @return bool|string
	 */
	public function get_recovery_url() {
		return $this->order->get_recovery_url();
	}

	/**
	 * When a payment is set to a status of 'refunded' process the necessary actions to reduce stats
	 *
	 * @since 2.5.7
	 * @access private
	 */
	private function process_refund() {
		$process_refund = true;

		// If the old status is refunded or the new status is not refunded, don't try to refund.
		if ( 'refunded' === $this->old_status || 'refunded' !== $this->status ) {
			$process_refund = false;
		}

		// Allow extensions to filter for their own payment types, Example: Recurring Payments.
		$process_refund = apply_filters( 'edd_should_process_refund', $process_refund, $this );

		if ( false === $process_refund ) {
			return;
		}

		do_action( 'edd_pre_refund_payment', $this );

		$decrease_store_earnings = apply_filters( 'edd_decrease_store_earnings_on_refund', true, $this );
		$decrease_customer_value = apply_filters( 'edd_decrease_customer_value_on_refund', true, $this );
		$decrease_purchase_count = apply_filters( 'edd_decrease_customer_purchase_count_on_refund', true, $this );

		$this->maybe_alter_stats( $decrease_store_earnings, $decrease_customer_value, $decrease_purchase_count );

		// Clear the This Month earnings (this_monththis_month is NOT a typo).
		delete_transient( md5( 'edd_earnings_this_monththis_month' ) );

		do_action( 'edd_post_refund_payment', $this );
	}

	/**
	 * Process when a payment is set to failed, decrement discount usages and other stats.
	 *
	 * @since 2.5.7
	 * @access private
	 */
	private function process_failure() {
		$discounts = $this->discounts;

		if ( 'none' === $discounts || empty( $discounts ) ) {
			return;
		}

		if ( ! is_array( $discounts ) ) {
			$discounts = array_map( 'trim', explode( ',', $discounts ) );
		}

		foreach ( $discounts as $discount ) {
			edd_decrease_discount_usage( $discount );
		}
	}

	/**
	 * Process when a payment moves to pending.
	 *
	 * @since 2.5.10
	 * @access private
	 */
	private function process_pending() {
		$process_pending = true;

		// If the payment was not in publish or revoked status, don't decrement stats as they were never incremented
		if ( ( 'complete' !== $this->old_status && 'revoked' !== $this->old_status ) || ! $this->in_process() ) {
			$process_pending = false;
		}

		// Allow extensions to filter for their own payment types, Example: Recurring Payments
		$process_pending = apply_filters( 'edd_should_process_pending', $process_pending, $this );

		if ( false === $process_pending ) {
			return;
		}

		$decrease_store_earnings = apply_filters( 'edd_decrease_store_earnings_on_pending', true, $this );
		$decrease_customer_value = apply_filters( 'edd_decrease_customer_value_on_pending', true, $this );
		$decrease_purchase_count = apply_filters( 'edd_decrease_customer_purchase_count_on_pending', true, $this );

		$this->maybe_alter_stats( $decrease_store_earnings, $decrease_customer_value, $decrease_purchase_count );

		$this->completed_date = false;
		$this->update_meta( '_edd_completed_date', '' );

		// Clear the This Month earnings (this_monththis_month is NOT a typo)
		delete_transient( md5( 'edd_earnings_this_monththis_month' ) );
	}

	/**
	 * Used during the process of moving to refunded or pending, to decrement stats.
	 *
	 * @since 2.5.10
	 * @since 3.2.4 Updated to better handle manual status changes.
	 * @access private
	 *
	 * @param bool $alter_store_earnings          If the method should alter the store earnings.
	 * @param bool $alter_customer_value          If the method should reduce the customer value.
	 * @param bool $alter_customer_purchase_count If the method should reduce the customer's purchase count.
	 */
	private function maybe_alter_stats( $alter_store_earnings, $alter_customer_value, $alter_customer_purchase_count ) {

		$refund = false;
		// Attempt to refund the order.
		if ( 'refunded' === $this->status ) {
			$refund = edd_refund_order( $this->ID );
		}

		// If a refund wasn't processed, update the status manually and add a note.
		if ( ! $refund || is_wp_error( $refund ) ) {
			edd_update_order(
				$this->ID,
				array(
					'status' => $this->status,
				)
			);
			if ( 'refunded' === $this->status ) {
				edd_add_note(
					array(
						'object_id'   => $this->ID,
						'object_type' => 'order',
						'user_id'     => get_current_user_id(),
						'content'     => __( 'The refund order could not be created, but the order status was manually set to Refunded.', 'easy-digital-downloads' ),
					)
				);
			}
		}

		// Decrease store earnings.
		if ( true === $alter_store_earnings ) {
			edd_decrease_total_earnings( $this->total );
		}
	}

	/**
	 * Delete sales logs for this purchase
	 *
	 * @since 2.5.10
	 * @deprecated Deprecated since 3.0 as sales logs are no longer used.
	 */
	private function delete_sales_logs() {
		_doing_it_wrong( __FUNCTION__, 'Sales logs are deprecated and are no longer used.', 'EDD 3.0' );
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
	 * Setup the payment completed date.
	 *
	 * @since 2.5
	 * @since 3.0 Updated to use the new custom tables.
	 *
	 * @return string The date the payment was completed.
	 */
	private function setup_completed_date() {
		/** @var EDD\Orders\Order $order */
		$order = $this->_shim_edd_get_order( $this->ID );

		if ( 'pending' === $order->status || 'preapproved' === $order->status || 'processing' === $order->status ) {
			return false; // This payment was never completed
		}

		return $order->date_completed ? $order->date_completed : '';
	}

	/**
	 * Setup the payment total.
	 *
	 * @since 2.5
	 *
	 * @return float Payment total.
	 */
	private function setup_total() {
		$amount = $this->get_meta( '_edd_payment_total', true );

		if ( empty( $amount ) && '0.00' !== $amount ) {
			$meta = $this->get_meta( '_edd_payment_meta', true );
			$meta = maybe_unserialize( $meta );

			if ( isset( $meta['amount'] ) ) {
				$amount = $meta['amount'];
			}
		}

		return $amount;
	}

	/**
	 * Setup the payment tax rate.
	 *
	 * @since 2.7
	 *
	 * @return float Tax rate for the payment.
	 */
	private function setup_tax_rate() {
		$tax_rate = $this->order->get_tax_rate();

		if ( ! empty( $tax_rate ) && $tax_rate > 1 ) {
			$tax_rate = $tax_rate / 100;
		}

		return $tax_rate;
	}

	/**
	 * Setup the total fee amount applied to the payment.
	 *
	 * @since 2.5.10
	 *
	 * @return float Total fee amount applied to the payment.
	 */
	private function setup_fees_total() {
		$fees_total = array_reduce(
			$this->fees,
			function ( $carry, $item ) {
				$carry += (float) $item['amount'];

				return $carry;
			},
			(float) 0.00
		);

		return $fees_total;
	}

	/**
	 * Setup the payment subtotal.
	 *
	 * @since 2.5
	 *
	 * @return float Payment subtotal.
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
	 * Setup the payments discount codes.
	 *
	 * @since 2.5
	 *
	 * @return string Discount codes on this payment.
	 */
	private function setup_discounts() {
		$discounts = array();

		$order_discounts = $this->order->get_discounts();

		foreach ( $order_discounts as $discount ) {
			$discounts[] = $discount->description;
		}

		$discounts = implode( ', ', $discounts );

		return $discounts;
	}

	/**
	 * Setup the currency code
	 *
	 * @since 2.5
	 *
	 * @return string The currency for the payment.
	 */
	private function setup_currency() {
		$currency = $this->order->currency;

		return ! empty( $currency )
			? $currency
			: apply_filters( 'edd_payment_currency_default', edd_get_currency(), $this );
	}

	/**
	 * Setup any fees associated with the payment.
	 *
	 * @since 2.5
	 * @return array Payment fees.
	 */
	private function setup_fees() {
		$fees = array();

		if ( $this->order->get_fees() ) {
			/*
			 * Build up an array of order item IDs with values set to their respective download/price IDs.
			 * This is so we can easily get that information when configuring order item fees.
			 */
			$order_items = array();
			foreach ( $this->order->get_items() as $order_item ) {
				/**
				 * @var \EDD\Orders\Order_Item $order_item
				 */
				$order_items[ intval( $order_item->id ) ] = array(
					'download_id' => $order_item->product_id,
					'price_id'    => $order_item->price_id,
				);
			}

			foreach ( $this->order->get_fees() as $order_fee ) {
				/**
				 * @var \EDD\Orders\Order_Adjustment $order_fee
				 */

				$download_id = 0;
				$price_id    = null;

				if ( 'order_item' === $order_fee->object_type && array_key_exists( intval( $order_fee->object_id ), $order_items ) ) {
					$download_id = $order_items[ intval( $order_fee->object_id ) ]['download_id'];
					$price_id    = $order_items[ intval( $order_fee->object_id ) ]['price_id'];
				}

				$no_tax = (bool) 0.00 === $order_fee->tax;
				$id     = is_null( $order_fee->type_key ) ? $order_fee->id : $order_fee->type_key;
				if ( array_key_exists( $id, $fees ) ) {
					$id .= '_2';
				}

				if ( $id != $order_fee->type_key ) {
					/*
					 * We run an update here because if we don't, then we'll send back a key of `23_2` when in the
					 * DB it's actually `null`, and if this value gets updated via the payment meta array, it
					 * will actually add a brand *new* fee instead of updating the existing one.
					 *
					 * @link https://github.com/easydigitaldownloads/easy-digital-downloads/issues/8412
					 */
					edd_update_order_adjustment(
						$order_fee->id,
						array(
							'type_key' => $id,
						)
					);
				}

				$fees[ $id ] = array(
					'amount'      => $order_fee->subtotal,
					'label'       => $order_fee->description,
					'no_tax'      => $no_tax,
					'type'        => 'fee',
					'price_id'    => $price_id,
					'download_id' => $download_id,
				);
			}
		}

		return $fees;
	}

	/**
	 * Setup the transaction ID.
	 *
	 * @since 2.5
	 *
	 * @return string The transaction ID for the payment.
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
	 * Setup the User ID associated with the purchase.
	 *
	 * @since 2.5
	 *
	 * @return int User ID.
	 */
	private function setup_user_id() {
		$user_id  = $this->get_meta( '_edd_payment_user_id', true );
		$customer = new EDD_Customer( $this->customer_id );

		// Make sure it exists, and that it matches that of the associated customer record
		if ( ! empty( $customer->user_id ) && ( empty( $user_id ) || (int) $user_id !== (int) $customer->user_id ) ) {
			$user_id = $customer->user_id;

			// Backfill the user ID, or reset it to be correct in the event of data corruption
			$this->update_meta( '_edd_payment_user_id', $user_id );
		}

		return $user_id;
	}

	/**
	 * Setup the email address for the purchase
	 *
	 * @since  2.5
	 * @return string The email address for the payment
	 */
	private function setup_email() {
		$email = $this->order->email;

		if ( empty( $email ) ) {
			$email = EDD()->customers->get_column( 'email', $this->customer_id );
		}

		return $email;
	}

	/**
	 * Setup the user info.
	 *
	 * @since 2.5
	 *
	 * @return array The user info associated with the payment.
	 */
	private function setup_user_info() {
		$order_address = $this->order->get_address();

		$user_info = array(
			'id'         => $this->user_id,
			'first_name' => $order_address->first_name,
			'last_name'  => $order_address->last_name,
			'discount'   => $this->discounts,
		);

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

		$country = $order_address->country;

		// Add address to array if one exists.
		if ( ! empty( $country ) ) {
			$user_info['address'] = array(
				'line1'   => $order_address->address,
				'line2'   => $order_address->address2,
				'city'    => $order_address->city,
				'state'   => $order_address->region,
				'country' => $country,
				'zip'     => $order_address->postal_code,
			);
		}

		// Check for old `user_info` meta which may still exist.
		$old_meta = edd_get_order_meta( $this->ID, 'payment_meta', true );
		if ( ! empty( $old_meta['user_info'] ) ) {
			$user_info = array_merge( $user_info, $old_meta['user_info'] );
		}

		return $user_info;
	}

	/**
	 * Setup the address for the payment.
	 *
	 * @since 2.5
	 *
	 * @return array The address information for the payment.
	 */
	private function setup_address() {
		$address  = ! empty( $this->user_info['address'] ) ? $this->user_info['address'] : array();
		$defaults = array(
			'line1'   => '',
			'line2'   => '',
			'city'    => '',
			'country' => '',
			'state'   => '',
			'zip'     => '',
		);

		$address = wp_parse_args( $address, $defaults );

		return $address;
	}

	/**
	 * Setup the payment number.
	 *
	 * @since 2.5
	 * @since 3.0 Refactor to use EDD\Orders\Order.
	 *
	 * @return int|string Integer by default, or string if sequential order numbers is enabled.
	 */
	private function setup_payment_number() {
		return $this->order->order_number;
	}

	/**
	 * Setup the cart details
	 *
	 * @since 2.5
	 * @since 3.0 Refactored as cart_details is no longer used and this is here for backwards compatibility purposes.
	 *
	 * @return array Cart details of an order.
	 */
	private function setup_cart_details() {
		$order_items = $this->order->items;

		$cart_details = array();

		foreach ( $order_items as $item ) {
			/** @var EDD\Orders\Order_Item $item */

			$item_fees = array();

			foreach ( $item->fees as $key => $item_fee ) {
				/** @var EDD\Orders\Order_Adjustment $item_fee */

				$download_id = $item->product_id;
				$price_id    = $item->price_id;
				$no_tax      = (bool) 0.00 === $item_fee->tax;
				$id          = is_null( $item_fee->type_key ) ? $item_fee->id : $item_fee->type_key;
				if ( array_key_exists( $id, $item_fees ) ) {
					$id .= '_2';
				}

				$item_fees[ $id ] = array(
					'amount'      => $item_fee->total,
					'label'       => $item_fee->description,
					'no_tax'      => $no_tax ? $no_tax : false,
					'type'        => 'fee',
					'price_id'    => $price_id ? $price_id : null,
					'download_id' => 0,
				);

				if ( $download_id ) {
					$item_fees[ $id ]['download_id'] = $download_id;
				}
			}

			$item_options = array(
				'quantity' => $item->quantity,
				'price_id' => $item->price_id,
			);

			/*
			 * For backwards compatibility from pre-3.0: add in order item meta prefixed with `_option_`.
			 * While saving, we've migrated these values to order item meta, but people may still be looking
			 * for them in this cart details array, so we need to fill them back in.
			 */
			$order_item_meta = edd_get_order_item_meta( $item->id );
			if ( ! empty( $order_item_meta ) ) {
				foreach ( $order_item_meta as $item_meta_key => $item_meta_value ) {
					if ( '_option_' === substr( $item_meta_key, 0, 8 ) && isset( $item_meta_value[0] ) ) {
						$item_options[ str_replace( '_option_', '', $item_meta_key ) ] = $item_meta_value[0];
					}
				}
			}

			$cart_details[ $item->cart_index ] = array(
				'name'          => $item->product_name,
				'id'            => $item->product_id,
				'item_number'   => array(
					'id'       => $item->product_id,
					'quantity' => $item->quantity,
					'options'  => $item_options,
				),
				'item_price'    => $item->amount,
				'quantity'      => $item->quantity,
				'discount'      => $item->discount,
				'subtotal'      => $item->subtotal,
				'tax'           => $item->tax,
				'fees'          => $item_fees,
				'price'         => $item->total,
				'order_item_id' => $item->id,
			);
		}

		return $cart_details;
	}

	/**
	 * Setup the downloads array.
	 *
	 * @since 2.5
	 *
	 * @internal This exists for backwards compatibility purposes.
	 *
	 * @return array Downloads associated with this payment.
	 */
	private function setup_downloads() {
		$order_items = $this->order->items;

		$downloads = array();

		foreach ( $order_items as $item ) {
			/** @var EDD\Orders\Order_Item $item */

			$downloads[] = array(
				'id'       => $item->product_id,
				'quantity' => $item->quantity,
				'options'  => array(
					'quantity' => $item->quantity,
					'price_id' => $item->price_id,
				),
			);
		}

		return $downloads;
	}

	/**
	 * Setup the Unlimited downloads setting
	 *
	 * @since  2.5
	 * @return bool If this payment has unlimited downloads
	 */
	private function setup_has_unlimited() {
		$unlimited = (bool) $this->order->has_unlimited_downloads();

		return $unlimited;
	}

	/**
	 * Converts this object into an array for special cases.
	 *
	 * @return array The payment object as an array.
	 */
	public function array_convert() {
		return get_object_vars( $this );
	}

	/**
	 * Retrieve payment cart details.
	 *
	 * @since 2.5.1
	 *
	 * @return array Cart details array.
	 */
	private function get_cart_details() {
		return apply_filters( 'edd_payment_cart_details', $this->cart_details, $this->ID, $this );
	}

	/**
	 * Retrieve payment completion date
	 *
	 * @since 2.5.1
	 * @since 3.0 Updated for backwards compatibility.
	 *
	 * @return string Date payment was completed.
	 */
	private function get_completed_date() {
		if ( is_null( $this->completed_date ) ) {
			$date = false;
		} else {
			$date = $this->completed_date;
		}

		return apply_filters( 'edd_payment_completed_date', $date, $this->ID, $this );
	}

	/**
	 * Retrieve payment tax.
	 *
	 * @since 2.5.1
	 *
	 * @return float Payment tax
	 */
	private function get_tax() {
		return apply_filters( 'edd_get_payment_tax', $this->tax, $this->ID, $this );
	}

	/**
	 * Retrieve payment subtotal.
	 *
	 * @since 2.5.1
	 *
	 * @return float Payment subtotal.
	 */
	private function get_subtotal() {
		return apply_filters( 'edd_get_payment_subtotal', $this->subtotal, $this->ID, $this );
	}

	/**
	 * Retrieve payment discounts.
	 *
	 * @since 2.5.1
	 *
	 * @return array Discount codes on payment.
	 */
	private function get_discounts() {
		return apply_filters( 'edd_payment_discounts', $this->discounts, $this->ID, $this );
	}

	/**
	 * Return the discounted amount of the payment.
	 *
	 * @since 2.8.7
	 *
	 * @return float Discounted amount.
	 */
	private function get_discounted_amount() {
		return floatval( apply_filters( 'edd_payment_discounted_amount', $this->order->discount, $this ) );
	}

	/**
	 * Retrieve payment currency.
	 *
	 * @since 2.5.1
	 *
	 * @return string Payment currency code.
	 */
	private function get_currency() {
		return apply_filters( 'edd_payment_currency_code', $this->currency, $this->ID, $this );
	}

	/**
	 * Retrieve payment gateway.
	 *
	 * @since 2.5.1
	 *
	 * @return string Payment gateway used.
	 */
	private function get_gateway() {
		return apply_filters( 'edd_payment_gateway', $this->gateway, $this->ID, $this );
	}

	/**
	 * Retrieve payment transaction ID.
	 *
	 * @since 2.5.1
	 *
	 * @return string Transaction ID from merchant processor.
	 */
	private function get_transaction_id() {
		return apply_filters( 'edd_get_payment_transaction_id', $this->transaction_id, $this->ID, $this );
	}

	/**
	 * Retrieve payment IP.
	 *
	 * @since 2.5.1
	 *
	 * @return string Payment IP address.
	 */
	private function get_ip() {
		return apply_filters( 'edd_payment_user_ip', $this->ip, $this->ID, $this );
	}

	/**
	 * Retrieve payment customer ID.
	 *
	 * @since 2.5.1
	 *
	 * @return int Payment customer ID.
	 */
	private function get_customer_id() {
		return apply_filters( 'edd_payment_customer_id', $this->customer_id, $this->ID, $this );
	}

	/**
	 * Retrieve payment user ID.
	 *
	 * @since 2.5.1
	 *
	 * @return int Payment user ID.
	 */
	private function get_user_id() {
		return apply_filters( 'edd_payment_user_id', $this->user_id, $this->ID, $this );
	}

	/**
	 * Retrieve payment email.
	 *
	 * @since 2.5.1
	 *
	 * @return string Payment customer email.
	 */
	private function get_email() {
		return apply_filters( 'edd_payment_user_email', $this->email, $this->ID, $this );
	}

	/**
	 * Retrieve payment user info.
	 *
	 * @since 2.5.1
	 *
	 * @return array Payment user info.
	 */
	private function get_user_info() {
		return apply_filters( 'edd_payment_meta_user_info', $this->user_info, $this->ID, $this );
	}

	/**
	 * Retrieve payment billing address.
	 *
	 * @since 2.5.1
	 *
	 * @return array Payment billing address.
	 */
	private function get_address() {
		return apply_filters( 'edd_payment_address', $this->address, $this->ID, $this );
	}

	/**
	 * Retrieve payment key.
	 *
	 * @since 2.5.1
	 *
	 * @return string Payment key.
	 */
	private function get_key() {
		return apply_filters( 'edd_payment_key', $this->key, $this->ID, $this );
	}

	/**
	 * Retrieve payment number.
	 *
	 * @since 2.5.1
	 *
	 * @return int|string Payment number.
	 */
	private function get_number() {
		return $this->order instanceof EDD\Orders\Order ? $this->order->get_number() : $this->ID;
	}

	/**
	 * Retrieve downloads on payment.
	 *
	 * @since 2.5.1
	 *
	 * @return array Payment downloads.
	 */
	private function get_downloads() {
		return apply_filters( 'edd_payment_meta_downloads', $this->downloads, $this->ID, $this );
	}

	/**
	 * Retrieve unlimited file downloads status.
	 *
	 * @since 2.5.1
	 *
	 * @return bool True if unlimited downloads are enabled, false otherwise.
	 */
	private function get_unlimited() {
		return apply_filters( 'edd_payment_unlimited_downloads', $this->unlimited, $this->ID, $this );
	}

	/**
	 * Easily determine if the payment is in a status of pending some action. Processing is specifically used for
	 * eChecks.
	 *
	 * @since 2.7
	 * @return bool
	 */
	private function in_process() {
		$in_process_statuses = array( 'pending', 'processing' );

		return in_array( $this->status, $in_process_statuses, true );
	}

	/**
	 * Determines if a customer needs to be created given the current payment details.
	 *
	 * @since 2.8.4
	 *
	 * @return EDD_Customer The customer object of the existing customer or new customer.
	 */
	private function maybe_create_customer() {
		$customer = new stdClass();

		if ( did_action( 'edd_pre_process_purchase' ) && is_user_logged_in() ) {
			$customer = new EDD_customer( get_current_user_id(), true );

			// Customer is logged in but used a different email to purchase with so assign to their customer record
			if ( ! empty( $customer->id ) && $this->email !== $customer->email ) {
				$customer->add_email( $this->email );
			}
		}

		if ( empty( $customer->id ) ) {
			$customer = new EDD_Customer( $this->email );
		}

		if ( empty( $customer->id ) ) {
			if ( empty( $this->first_name ) && empty( $this->last_name ) ) {
				$name = $this->email;
			} else {
				$name = $this->first_name . ' ' . $this->last_name;
			}

			$customer_data = array(
				'name'    => $name,
				'email'   => $this->email,
				'user_id' => $this->user_id,
			);

			$customer->create( $customer_data );
		}

		return $customer;
	}

	/**
	 * Sets up a payment object from a post.
	 * This is only intended to be used when a 3.0 migration is in process and the
	 * new order object is not yet available.
	 *
	 * @todo deprecate in 3.1
	 *
	 * @since 3.0
	 * @param int $payment_id
	 * @return bool
	 */
	private function _setup_compat_payment( $payment_id ) {
		$payment = get_post( $payment_id );

		if ( ! $payment || is_wp_error( $payment ) ) {
			return false;
		}

		if ( 'edd_payment' !== $payment->post_type ) {
			return false;
		}

		// Set the compatibility property to true.
		$this->is_edd_payment = true;

		// Allow extensions to perform actions before the payment is loaded
		do_action( 'edd_pre_setup_payment', $this, $payment_id );

		// Primary Identifier
		$this->ID = absint( $payment_id );

		// Protected ID that can never be changed
		$this->_ID = absint( $payment_id );

		include_once EDD_PLUGIN_DIR . 'includes/compat/class-edd-payment-compat.php';
		$payment_compat = new EDD_Payment_Compat( $this->ID );

		// We have a payment; get the generic payment_meta item to reduce calls to it
		$this->payment_meta = $payment_compat->payment_meta;

		// Status and Dates
		$this->date           = $payment->post_date;
		$this->completed_date = $payment_compat->completed_date;
		$this->status         = $payment_compat->status;
		$this->post_status    = $this->status;
		$this->mode           = $payment_compat->mode;
		$this->parent_payment = $payment->post_parent;

		$this->status_nicename = $this->get_status_nicename();

		// Items
		$this->fees         = $payment_compat->fees;
		$this->cart_details = $payment_compat->cart_details;
		$this->downloads    = $payment_compat->downloads;

		// Currency Based
		$this->total      = $payment_compat->total;
		$this->tax        = $payment_compat->tax;
		$this->tax_rate   = $payment_compat->tax_rate;
		$this->fees_total = $payment_compat->fees_total;
		$this->subtotal   = $payment_compat->subtotal;
		$this->currency   = $payment_compat->currency;

		// Gateway based
		$this->gateway        = $payment_compat->gateway;
		$this->transaction_id = $payment_compat->transaction_id;

		// User based
		$this->ip          = $payment_compat->ip;
		$this->customer_id = $payment_compat->customer_id;
		$this->user_id     = $payment_compat->user_id;
		$this->email       = $payment_compat->email;
		$this->user_info   = $payment_compat->user_info;
		$this->address     = $payment_compat->address;
		$this->discounts   = $this->user_info['discount'];
		$this->first_name  = $this->user_info['first_name'];
		$this->last_name   = $this->user_info['last_name'];

		// Other Identifiers
		$this->key    = $payment_compat->key;
		$this->number = $payment_compat->number;

		// Additional Attributes
		$this->has_unlimited_downloads = $payment_compat->has_unlimited_downloads;
		$this->order                   = $payment_compat->order;

		// Allow extensions to add items to this object via hook
		do_action( 'edd_setup_payment', $this, $payment_id );

		return true;
	}

	/**
	 * Gets the order from the database.
	 * This is a duplicate of edd_get_order, but is defined separately here
	 * for pending migration purposes.
	 *
	 * @todo deprecate in 3.1
	 *
	 * @param int $order_id
	 * @return false|EDD\Orders\Order
	 */
	private function _shim_edd_get_order( $order_id ) {
		$orders = new EDD\Database\Queries\Order();

		// Return order.
		return $orders->get_item( $order_id );
	}

	/**
	 * Ensure that the tax rate is a percentage.
	 *
	 * @since 3.2.3
	 * @return string|float
	 */
	private function maybe_update_tax_rate( $tax_rate = null ) {
		if ( is_null( $tax_rate ) ) {
			$tax_rate = $this->tax_rate;
		}

		if ( empty( $tax_rate ) ) {
			return $tax_rate;
		}

		// If the tax rate is a decimal instead of a percentage, get the percentage.
		if ( $tax_rate > 0 && $tax_rate < 1 ) {
			return $tax_rate * 100;
		}

		return $tax_rate;
	}

	/**
	 * Get the status nicename for the payment.
	 *
	 * @since 3.2.4
	 * @return string
	 */
	private function get_status_nicename() {
		$all_payment_statuses = edd_get_payment_statuses();

		return array_key_exists( $this->status, $all_payment_statuses ) ? $all_payment_statuses[ $this->status ] : ucfirst( $this->status );
	}
}
