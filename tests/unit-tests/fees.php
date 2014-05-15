<?php
namespace EDD_Unit_Tests;

/**
 * @group edd_fees
 */
class Tests_Fee extends EDD_UnitTestCase {
	protected $_post = null;

	public function setUp() {
		parent::setUp();
		$post_id = $this->factory->post->create( array( 'post_title' => 'Test Download', 'post_type' => 'download', 'post_status' => 'publish' ) );;
		$this->_post = get_post( $post_id );
	}

	public function test_adding_fees() {
		$expected = array(
			'shipping_fee' => array(
				'amount' => 10,
				'label' => 'Shipping Fee'
			)
		);

		$this->assertEquals( $expected, EDD()->fees->add_fee( 10, 'Shipping Fee', 'shipping_fee' ) );
	}

	public function test_has_fees() {
		$this->assertTrue( EDD()->fees->has_fees() );
	}

	public function test_get_fee() {
		$expected = array(
			'amount' => 10,
			'label' => 'Shipping Fee'
		);
		$this->assertEquals( $expected, EDD()->fees->get_fee( 'shipping_fee' ) );
	}

	public function test_get_fees() {
		$expected = array(
			'shipping_fee' => array(
				'amount' => 10,
				'label' => 'Shipping Fee'
			)
		);

		$this->assertEquals( $expected, EDD()->fees->get_fees() );
	}

	public function test_total_fees() {
		EDD()->fees->add_fee( 20, 'Tax', 'Tax' );
		$this->assertEquals( 30, EDD()->fees->total() );
	}

	public function test_record_fee() {
		$out = EDD()->fees->record_fees( $payment_meta = array(), $payment_data = array() );

		$expected = array(
			'fees' => array(
				'shipping_fee' => array(
					'amount' => 10,
					'label' => 'Shipping Fee'
				),
				'tax' => array(
					'amount' => 20,
					'label' => 'Tax'
				)
			)
		);

		$this->assertEquals( $expected, $out );
	}
}