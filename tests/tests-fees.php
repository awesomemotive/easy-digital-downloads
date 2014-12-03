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

		edd_add_to_cart( $this->_post->ID );

	}

	public function test_adding_fees() {
		$expected = array(
			'shipping_fee' => array(
				'amount' => '10.00',
				'label' => 'Shipping Fee',
				'type'  => 'fee',
				'no_tax' => false,
			'download_id' => 0
			),
			'item_fee' => array(
				'amount' => '20.00',
				'label' => 'Arbitrary Item',
				'type' => 'item',
				'no_tax' => false
			)
		);

		EDD()->fees->add_fee( 10, 'Shipping Fee', 'shipping_fee' );
		EDD()->fees->add_fee( array( 'amount' => '20.00', 'label' => 'Arbitrary Item', 'download_id' => $this->_post->ID, 'id' => 'item_fee', 'type' => 'item' ) );

		$this->assertEquals( $expected, EDD()->fees->get_fees( 'all' ) );
	}

	public function test_has_fees() {

		EDD()->session->set( 'edd_cart_fees', null );

		EDD()->fees->add_fee( 10, 'Shipping Fee', 'shipping_fee' );

		$this->assertTrue( EDD()->fees->has_fees() );
	}

	public function test_get_fee() {

		EDD()->session->set( 'edd_cart_fees', null );

		EDD()->fees->add_fee( 10, 'Shipping Fee', 'shipping_fee' );

		$expected = array(
			'amount' => '10.00',
			'label' => 'Shipping Fee',
			'type' => 'fee' ,
			'no_tax' => false,
			'download_id' => 0
		);
		$this->assertEquals( $expected, EDD()->fees->get_fee( 'shipping_fee' ) );

		$fee = EDD()->fees->get_fee( 'shipping_fee' );

		$this->assertEquals( '10.00', $fee['amount'] );
		$this->assertEquals( 'Shipping Fee', $fee['label'] );
		$this->assertEquals( 'fee', $fee['type'] );

	}

	public function test_get_fees() {

		// Test getting all fees
		$expected = array(
			'shipping_fee' => array(
				'amount' => '10.00',
				'label' => 'Shipping Fee',
				'type'  => 'fee',
				'no_tax' => false,
				'download_id' => 0
			)
		);

		$this->assertEquals( $expected, EDD()->fees->get_fees( 'all' ) );

		// Test getting only fee fees
		$expected = array(
			'shipping_fee' => array(
				'amount' => '10.00',
				'label' => 'Shipping Fee',
				'type'  => 'fee',
				'no_tax' => false,
				'download_id' => 0
			)
		);

		$this->assertEquals( $expected, EDD()->fees->get_fees( 'fee' ) );

		// Test getting only item fees

		$this->assertEquals( array(), EDD()->fees->get_fees( 'item' ) );

		EDD()->fees->add_fee( array( 'amount' => '20.00', 'label' => 'Arbitrary Item', 'id' => 'item_fee', 'type' => 'item' ) );

		// Test getting only item fees
		$expected = array(
			'item_fee' => array(
				'amount' => '20.00',
				'label' => 'Arbitrary Item',
				'type' => 'item',
				'no_tax' => false,
				'download_id' => 0
			)
		);

		$this->assertEquals( $expected, EDD()->fees->get_fees( 'item' ) );

		// Test getting download specific fees

		EDD()->session->set( 'edd_cart_fees', null );

		EDD()->fees->add_fee( array(
			'amount' => '20.00',
			'label' => 'Arbitrary Fee',
			'download_id' => $this->_post->ID,
			'id' => 'arb_fee'
		) );

		EDD()->fees->add_fee( array(
			'amount' => '20.00',
			'label' => 'Arbitrary Fee 2',
			'id' => 'arb_fee_2'
		) );

		$expected = array(
			'arb_fee' => array(
				'amount' => '20.00',
				'label' => 'Arbitrary Fee',
				'no_tax' => false,
				'type' => 'fee',
				'download_id' => $this->_post->ID
			)
		);

		$this->assertEquals( $expected, EDD()->fees->get_fees( 'fee', $this->_post->ID ) );

	}

	public function test_total_fees() {

		EDD()->fees->add_fee( 20, 'Tax Fee', 'Tax Fee' );

		$this->assertEquals( 60, EDD()->fees->total() );

		EDD()->fees->add_fee( array(
			'amount' => '20.00',
			'label' => 'Arbitrary Fee',
			'download_id' => $this->_post->ID,
			'id' => 'arb_fee'
		) );

		$this->assertEquals( 20, EDD()->fees->total( $this->_post->ID ) );

		$this->assertEquals( '60.00', EDD()->fees->total() );
	}

	public function test_record_fee() {
		$out = EDD()->fees->record_fees( $payment_meta = array(), $payment_data = array() );

		$expected = array(
			'fees' => array(
				'arb_fee' => array(
					'amount' => '20.00',
					'label' => 'Arbitrary Fee',
					'type'  => 'fee',
					'no_tax' => false,
					'download_id' => $this->_post->ID - 1
				),
				'arb_fee_2' => array(
					'amount' => '20.00',
					'label' => 'Arbitrary Fee 2',
					'type' => 'fee',
					'no_tax' => false,
					'download_id' => 0
				),
				'taxfee' => array(
					'amount' => '20.00',
					'label' => 'Tax Fee',
					'type' => 'fee',
					'no_tax' => false,
					'download_id' => 0
				)
			)
		);

		$this->assertEquals( $expected, $out );
	}
}