<?php


/**
 * @group edd_fees
 */
class Tests_Fee extends WP_UnitTestCase {
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
				'label' => 'Shipping Fee',
				'type'  => 'fee',
				'no_tax' => false
			),
			'item_fee' => array(
				'amount' => 20,
				'label' => 'Arbitrary Item',
				'type' => 'item',
				'no_tax' => false
			)
		);

		EDD()->fees->add_fee( 10, 'Shipping Fee', 'shipping_fee' );
		EDD()->fees->add_fee( array( 'amount' => 20, 'label' => 'Arbitrary Item', 'id' => 'item_fee', 'type' => 'item' ) );

		$this->assertEquals( $expected, EDD()->fees->get_fees( 'all' ) );
	}

	public function test_has_fees() {
		$this->assertTrue( EDD()->fees->has_fees() );
	}

	public function test_get_fee() {

		$expected = array(
			'amount' => 10,
			'label' => 'Shipping Fee',
			'type' => 'fee' ,
			'no_tax' => false
		);
		$this->assertEquals( $expected, EDD()->fees->get_fee( 'shipping_fee' ) );
	
		$item_fee = EDD()->fees->get_fee( 'item_fee' );

		$this->assertEquals( 20, $item_fee['amount'] );
		$this->assertEquals( 'Arbitrary Item', $item_fee['label'] );
		$this->assertEquals( 'item', $item_fee['type'] );

	}

	public function test_get_fees() {
		
		// Test getting all fees
		$expected = array(
			'shipping_fee' => array(
				'amount' => 10,
				'label' => 'Shipping Fee',
				'type'  => 'fee',
				'no_tax' => false
			),
			'item_fee' => array(
				'amount' => 20,
				'label' => 'Arbitrary Item',
				'type' => 'item',
				'no_tax' => false
			)
		);

		$this->assertEquals( $expected, EDD()->fees->get_fees( 'all' ) );

		// Test getting only fee fees
		$expected = array(
			'shipping_fee' => array(
				'amount' => 10,
				'label' => 'Shipping Fee',
				'type'  => 'fee',
				'no_tax' => false
			)
		);

		$this->assertEquals( $expected, EDD()->fees->get_fees( 'fee' ) );

		// Test getting only item fees
		$expected = array(
			'item_fee' => array(
				'amount' => 20,
				'label' => 'Arbitrary Item',
				'type' => 'item',
				'no_tax' => false
			)
		);

		$this->assertEquals( $expected, EDD()->fees->get_fees( 'item' ) );
	}

	public function test_total_fees() {
		EDD()->fees->add_fee( 20, 'Tax Fee', 'Tax Fee' );
		$this->assertEquals( 50, EDD()->fees->total() );
	}

	public function test_record_fee() {
		$out = EDD()->fees->record_fees( $payment_meta = array(), $payment_data = array() );

		$expected = array(
			'fees' => array(
				'shipping_fee' => array(
					'amount' => 10,
					'label' => 'Shipping Fee',
					'type'  => 'fee',
					'no_tax' => false
				),
				'item_fee' => array(
					'amount' => 20,
					'label' => 'Arbitrary Item',
					'type' => 'item'
					,
					'no_tax' => false
				),
				'taxfee' => array(
					'amount' => 20,
					'label' => 'Tax Fee',
					'type' => 'fee',
					'no_tax' => false
				)
			)
		);

		$this->assertEquals( $expected, $out );
	}
}