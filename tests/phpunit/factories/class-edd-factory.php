<?php
namespace EDD\Tests;

/**
 * A factory for making WordPress data with a cross-object type API.
 *
 * Tests should use this factory to generate test fixtures.
 */
class Factory extends \WP_UnitTest_Factory {

	/**
	 * @var \EDD\Tests\Factory\Discount
	 */
	public $discount;

	/**
<<<<<<< HEAD
	 * @var \EDD\Tests\Factory\Log
	 */
	public $log;

	/**
	 * @var \EDD\Tests\Factory\File_Download_Log
	 */
	public $file_download_log;

	/**
	 * @var \EDD\Tests\Factory\API_Request_Log
	 */
	public $api_request_log;
=======
	 * @var \EDD\Tests\Factory\Note
	 */
	public $note;
>>>>>>> release/3.0

	public function __construct() {
		parent::__construct();

		$this->discount = new Factory\Discount( $this );
<<<<<<< HEAD
		$this->log = new Factory\Log( $this );
		$this->file_download_log = new Factory\File_Download_Log( $this );
		$this->api_request_log = new Factory\API_Request_Log( $this );
=======
		$this->note = new Factory\Note( $this );
>>>>>>> release/3.0
	}
}