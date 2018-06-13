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

	public function __construct() {
		parent::__construct();

		$this->api_request_log   = new Factory\API_Request_Log( $this );
		$this->discount          = new Factory\Discount( $this );
		$this->customer          = new Factory\Customer( $this );
		$this->file_download_log = new Factory\File_Download_Log( $this );
		$this->log               = new Factory\Log( $this );
		$this->note              = new Factory\Note( $this );
		$this->order             = new Factory\Order( $this );
	}
}