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

}
