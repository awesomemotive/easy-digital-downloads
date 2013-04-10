<?php

/**
 * Test that the EDD Fees class is working correctly by adding and removing fees
 * as well as going out and querying the database for them
 *
 * @package EDD
 * @subpackage UnitTests
 * @since 1.6
 */

class Tests_EDD_Fees extends WP_UnitTestCase {
	/**
	 * Post
	 * @var object
	 */
	protected $_post = null;

	/**
	 * Setup the test
	 */
	public function setUp() {
		parent::setUp();
		$wp_factory = new WP_UnitTest_Factory;
		$post_id = $wp_factory->post->create( array( 'post_title' => 'Test Download', 'post_type' => 'download', 'post_status' => 'publish' ) );;
		$this->_post = get_post( $post_id );
	}

	/**
	 * Add Fees
	 */
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