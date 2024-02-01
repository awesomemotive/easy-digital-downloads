<?php
namespace EDD\Tests;

use EDD\Tests\PHPUnit\EDD_UnitTestCase;
use \EDD_Register_Meta;
/**
 * @group edd_meta
 */
class Tests_Register_Meta extends EDD_UnitTestCase {

	protected $payment_id;

	protected $download_id;

	/**
	 * Holds the EDD_Register_Meta instance
	 *
	 * @var \EDD_Register_Meta
	 */
	private $meta_handler;

	public function setup(): void {
		parent::setUp();

		$this->meta_handler = EDD_Register_Meta::instance();

		$this->payment_id  = Helpers\EDD_Helper_Payment::create_simple_payment();
		$variable_download = Helpers\EDD_Helper_Download::create_variable_download();

		$this->download_id = $variable_download->ID;
	}

	public function tearDown(): void {
		parent::tearDown();
		Helpers\EDD_Helper_Payment::delete_payment( $this->payment_id );
		Helpers\EDD_Helper_Download::delete_download( $this->download_id );
	}

	public function test_intval_wrapper() {
		$this->setExpectedIncorrectUsage( 'add_post_meta()/update_post_meta()' );

		update_post_meta( $this->payment_id, '_edd_payment_customer_id', '90.4' );

		$this->assertEquals( '90', edd_get_payment_meta( $this->payment_id, '_edd_payment_customer_id', true ) );

		update_post_meta( $this->payment_id, '_edd_payment_customer_id', '-1.43' );
		$this->assertEquals( '0', edd_get_payment_meta( $this->payment_id, '_edd_payment_customer_id', true ) );
	}

	public function test_sanitize_price_positive_value() {
		$price = '9';

		$sanitized = $this->meta_handler->sanitize_price( $price );
		$this->assertEquals( 9, $sanitized );
	}

	public function test_sanitize_negative_value() {
		$price = -1;

		$sanitized = $this->meta_handler->sanitize_price( $price );
		$this->assertEquals( 0, $sanitized );
	}

	public function test_sanitize_zero_value() {
		// Test saving a zero value
		$price = 0;

		$sanitized = $this->meta_handler->sanitize_price( $price );
		$this->assertEquals( 0, $sanitized );
	}

	public function test_sanitize_allow_negative_values_value() {
		// Add our filter to allow negative prices.
		add_filter( 'edd_allow_negative_prices', '__return_true' );
		$price = -1;

		$sanitized = $this->meta_handler->sanitize_price( $price );
		$this->assertEquals( -1, $sanitized );

		// Remove our filter.
		remove_filter( 'edd_allow_negative_prices', '__return_true' );
	}

	public function test_sanitize_variable_prices() {
		$variable_prices = array(
			array( 'name'   => 'First Option' ),
			array( 'amount' => 5, 'name' => 'Second Option' ),
			array( 'foo'    => 'bar', 'bar' => 'baz' ),
		);

		$sanitized = $this->meta_handler->sanitize_variable_prices( $variable_prices );
		$this->assertEquals( 2, count( $sanitized ) );
		$this->assertEquals( 0, $sanitized[0]['amount'] );
	}

	public function test_sanitize_variable_prices_with_tags() {
		$variable_prices = array(
			array( 'name' => '<script>alert("hello");</script>First Option' ),
		);

		$sanitized = $this->meta_handler->sanitize_variable_prices( $variable_prices );
		$this->assertEquals( 'First Option', $sanitized[0]['name'] );
	}

	public function test_sanitize_files() {
		$files = array(
			array(
				'file' => '',
				'name' => '',
			),
			array(
				'file' => '  file2.zip  ',
				'name' => 'File 2',
			),
			array(
				'file' => 'file3.zip',
				'name' => '   File 3   ',
			),
		);

		$sanitized = $this->meta_handler->sanitize_files( $files );
		$this->assertEquals( 2, count( $sanitized ) );
		$this->assertEquals( 'file2.zip', $sanitized[1]['file'] );
		$this->assertEquals( 'File 3', $sanitized[2]['name'] );
	}


}
