<?php
namespace EDD\Tests;

/**
 * A factory for making WordPress data with a cross-object type API.
 *
 * Tests should use this factory to generate test fixtures.
 */
class Factory extends \WP_UnitTest_Factory {

	/**
	 * @var Factory\API_Request_Log
	 */
	public $api_request_log;

	/**
	 * @var Factory\Customer
	 */
	public $customer;

	/**
	 * @var Factory\Customer_Address
	 */
	public $customer_address;

	/**
	 * @var Factory\Customer_Email_Address
	 */
	public $customer_email_address;

	/**
	 * @var Factory\Discount
	 */
	public $discount;

	/**
	 * @var Factory\File_Download_Log
	 */
	public $file_download_log;

	/**
	 * @var Factory\Log
	 */
	public $log;

	/**
	 * @var Factory\Note
	 */
	public $note;

	/**
	 * @var Factory\Order
	 */
	public $order;

	/**
	 * @var Factory\Order_Address
	 */
	public $order_address;

	/**
	 * @var Factory\Order_Adjustment
	 */
	public $order_adjustment;

	/**
	 * @var Factory\Order_Item
	 */
	public $order_item;

	/**
	 * @var Factory\Order_Transaction
	 */
	public $order_transaction;

	/**
	 * @var Factory\EmailLog
	 */
	public $email_logs;

	public function __construct() {
		parent::__construct();

		$this->api_request_log        = new Factory\API_Request_Log( $this );
		$this->discount               = new Factory\Discount( $this );
		$this->customer               = new Factory\Customer( $this );
		$this->customer_address       = new Factory\Customer_Address( $this );
		$this->customer_email_address = new Factory\Customer_Email_Address( $this );
		$this->file_download_log      = new Factory\File_Download_Log( $this );
		$this->log                    = new Factory\Log( $this );
		$this->note                   = new Factory\Note( $this );
		$this->order                  = new Factory\Order( $this );
		$this->order_address          = new Factory\Order_Address( $this );
		$this->order_item             = new Factory\Order_Item( $this );
		$this->order_adjustment       = new Factory\Order_Adjustment( $this );
		$this->order_transaction      = new Factory\Order_Transaction( $this );
		$this->email_logs             = new Factory\EmailLog( $this );
	}
}
