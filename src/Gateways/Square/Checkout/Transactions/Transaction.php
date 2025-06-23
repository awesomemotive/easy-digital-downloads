<?php
/**
 * Transaction class for the Square integration.
 *
 * @package     EDD\Gateways\Square\Checkout\Transactions
 * @copyright   Copyright (c) 2025, Sandhills Development, LLC
 * @license     https://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       3.4.0
 */

namespace EDD\Gateways\Square\Checkout\Transactions;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

use EDD\Gateways\Square\Helpers\Api;
use EDD\Vendor\Square\Orders\Requests\CreateOrderRequest;
use EDD\Vendor\Square\ApiResponse;
use EDD\Vendor\Square\ApiException;
use EDD\Vendor\Square\Models\Order;
use EDD\Vendor\Square\Models\Payment;

/**
 * Transaction class for the Square integration.
 *
 * @since 3.4.0
 */
abstract class Transaction {

	/**
	 * The purchase data from EDD.
	 *
	 * @var array
	 */
	protected $purchase_data;

	/**
	 * The transaction arguments for Square.
	 *
	 * @var array
	 */
	protected $args;

	/**
	 * The transaction errors.
	 *
	 * @var array
	 */
	protected $errors = array();

	/**
	 * The Square client.
	 *
	 * @var SquareClient
	 */
	protected $client;

	/**
	 * The Square Order request.
	 *
	 * @var CreateOrderRequest
	 */
	protected $order_request;

	/**
	 * The Square response.
	 *
	 * @var ApiResponse
	 */
	protected $order_response;

	/**
	 * The Square Payment request.
	 *
	 * @var CreatePaymentRequest
	 */
	protected $payment_request;

	/**
	 * The Square Payment response.
	 *
	 * @var ApiResponse
	 */
	protected $payment_response;

	/**
	 * The Square Update Order request.
	 *
	 * @var UpdateOrderRequest
	 */
	protected $update_order_request;

	/**
	 * The Square Update Order response.
	 *
	 * @var ApiResponse
	 */
	protected $update_order_response;

	/**
	 * Last API call exception.
	 *
	 * @var ApiException
	 */
	protected $exception;

	/**
	 * The Square order.
	 *
	 * @var Order
	 */
	protected $order;

	/**
	 * The Square payment.
	 *
	 * @var Payment
	 */
	protected $payment;

	/**
	 * The currency for the transaction.
	 *
	 * @var string
	 */
	protected $currency;

	/**
	 * Constructor.
	 *
	 * @since 3.4.0
	 * @param array $purchase_data The purchase data.
	 * @param array $args The transaction arguments.
	 */
	public function __construct( $purchase_data, $args ) {
		$this->purchase_data = $purchase_data;
		$this->args          = $args;
		$this->client        = Api::client();
	}

	/**
	 * Process the transaction.
	 *
	 * @since 3.4.0
	 * @return void
	 */
	abstract public function process();

	/**
	 * Get any errors.
	 *
	 * @since 3.4.0
	 * @return array
	 */
	public function get_errors() {
		return $this->errors;
	}
}
