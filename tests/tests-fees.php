<?php


/**
 * @group edd_fees
 */
class Tests_Fee extends WP_UnitTestCase {
	protected $_post = null;
	
	protected $_post2 = null;

	public function setUp() {

		parent::setUp();
		$post_id = $this->factory->post->create( array( 'post_title' => 'Test Download', 'post_type' => 'download', 'post_status' => 'publish' ) );
		$this->_post = get_post( $post_id );
		
		$this->_post2 = EDD_Helper_Download::create_variable_download();

		edd_add_to_cart( $this->_post->ID );

	}

	public function test_adding_fee_legacy() {
		
		EDD()->session->set( 'edd_cart_fees', null );
				
		//This is not using the $args array because it's for backwards compatibility.
		EDD()->fees->add_fee( 10, 'Shipping Fee', 'shipping_fee' );
		
		$expected = array(
			'shipping_fee' => array(
				'amount' => '10.00',
				'label' => 'Shipping Fee',
				'type'  => 'fee',
				'no_tax' => false,
				'download_id' => 0,
				'price_id'    => NULL
			),
		);
		
		$this->assertEquals( $expected, EDD()->fees->get_fees( 'all' ) );
	}
	
	public function test_adding_fee() {
		
		EDD()->session->set( 'edd_cart_fees', null );
		
		//Arbitrary fee test.
		EDD()->fees->add_fee( array( 
			'amount' => '20.00', 
			'label' => 'Arbitrary Item', 
			'download_id' => $this->_post->ID, 
			'id' => 'arbitrary_fee', 
			'type' => 'item' 
		) );
		
		$expected = array(
			'arbitrary_fee' => array(
				'amount' => '20.00',
				'label' => 'Arbitrary Item',
				'type' => 'item',
				'no_tax' => false
			)
		);

		$this->assertEquals( $expected, EDD()->fees->get_fees( 'all' ) );
	}
	
	public function test_adding_fee_for_variable_price() {
		
		EDD()->session->set( 'edd_cart_fees', null );
		
		//Test with variable price id attached to a fee.
		EDD()->fees->add_fee( array( 
			'amount' => '10.00', 
			'label' => 'Shipping Fee (Small)', 
			'download_id' => $this->_post->ID, 
			'price_id' => 1, 
			'id' => 'shipping_fee_with_variable_price_id', 
			'type' => 'fee' 
		) );
		
		$expected = array(
			'shipping_fee_with_variable_price_id' => array(
				'amount' => '10.00',
				'label' => 'Shipping Fee (Small)',
				'type'  => 'fee',
				'no_tax' => false,
				'download_id' => $this->_post->ID,
				'price_id'    => 1
			),
		);

		$this->assertEquals( $expected, EDD()->fees->get_fees( 'all' ) );
	}
	
	public function test_adding_fees() {
		
		EDD()->session->set( 'edd_cart_fees', null );
				
		//Add Legacy Fee
		EDD()->fees->add_fee( 10, 'Shipping Fee', 'shipping_fee' );
		
		//Add Normal Fee with variable price id for to a fee.
		EDD()->fees->add_fee( array( 
			'amount' => '10.00', 
			'label' => 'Shipping Fee (Small)', 
			'download_id' => $this->_post->ID, 
			'price_id' => 1, 
			'id' => 'shipping_fee_with_variable_price_id', 
			'type' => 'fee' 
		) );
		
		//Add Normal fee
		EDD()->fees->add_fee( array( 
			'amount' => '20.00', 
			'label' => 'Arbitrary Item', 
			'download_id' => $this->_post->ID, 
			'id' => 'arbitrary_fee', 
			'type' => 'item' 
		) );
		
		$expected = array(
			//Legacy Fee
			'shipping_fee' => array(
				'amount' => '10.00',
				'label' => 'Shipping Fee',
				'type'  => 'fee',
				'no_tax' => false,
				'download_id' => 0,
				'price_id'    => NULL
			),
			//Normal Fee with Variable Price
			'shipping_fee_with_variable_price_id' => array(
				'amount' => '10.00',
				'label' => 'Shipping Fee (Small)',
				'type'  => 'fee',
				'no_tax' => false,
				'download_id' => $this->_post->ID,
				'price_id'    => 1
			),
			//Normal Fee
			'arbitrary_fee' => array(
				'amount' => '20.00',
				'label' => 'Arbitrary Item',
				'type' => 'item',
				'no_tax' => false
			)
		);

		$this->assertEquals( $expected, EDD()->fees->get_fees( 'all' ) );
	}

	public function test_has_fees() {

		EDD()->session->set( 'edd_cart_fees', null );

		EDD()->fees->add_fee( array( 
			'amount' => '10.00', 
			'label' => 'Shipping Fee (Small)', 
			'download_id' => $this->_post->ID, 
			'price_id' => 1, 
			'id' => 'shipping_fee_with_variable_price_id', 
			'type' => 'fee' 
		) );

		$this->assertTrue( EDD()->fees->has_fees() );
	}

	public function test_get_fee() {

		EDD()->session->set( 'edd_cart_fees', null );

		EDD()->fees->add_fee( array( 
			'amount' => '10.00', 
			'label' => 'Shipping Fee (Small)', 
			'download_id' => $this->_post->ID, 
			'price_id' => 1, 
			'id' => 'shipping_fee_with_variable_price_id', 
			'type' => 'fee' 
		) );

		$expected = array(
			'amount' => '10.00',
			'label' => 'Shipping Fee (Small)',
			'type'  => 'fee',
			'no_tax' => false,
			'download_id' => $this->_post->ID,
			'price_id'    => 1
		);
		
		$this->assertEquals( $expected, EDD()->fees->get_fee( 'shipping_fee_with_variable_price_id' ) );

		$fee = EDD()->fees->get_fee( 'shipping_fee_with_variable_price_id' );

		$this->assertEquals( '10.00', $fee['amount'] );
		$this->assertEquals( 'Shipping Fee (Small)', $fee['label'] );
		$this->assertEquals( 'fee', $fee['type'] );

	}

	public function test_get_all_fees() {

		EDD()->session->set( 'edd_cart_fees', null );
		
		//Add Legacy Fee
		EDD()->fees->add_fee( 10, 'Shipping Fee', 'shipping_fee' );
		
		//Add Normal Fee with variable price id for to a fee.
		EDD()->fees->add_fee( array( 
			'amount' => '10.00', 
			'label' => 'Shipping Fee (Small)', 
			'download_id' => $this->_post->ID, 
			'price_id' => 1, 
			'id' => 'shipping_fee_with_variable_price_id', 
			'type' => 'fee' 
		) );
		
		//Add Normal fee
		EDD()->fees->add_fee( array( 
			'amount' => '20.00', 
			'label' => 'Arbitrary Item', 
			'download_id' => $this->_post->ID, 
			'id' => 'arbitrary_fee', 
			'type' => 'item' 
		) );
		
		$expected = array(
			//Legacy Fee
			'shipping_fee' => array(
				'amount' => '10.00',
				'label' => 'Shipping Fee',
				'type'  => 'fee',
				'no_tax' => false,
				'download_id' => 0,
				'price_id'    => NULL
			),
			//Normal Fee with Variable Price
			'shipping_fee_with_variable_price_id' => array(
				'amount' => '10.00',
				'label' => 'Shipping Fee (Small)',
				'type'  => 'fee',
				'no_tax' => false,
				'download_id' => $this->_post->ID,
				'price_id'    => 1
			),
			//Normal Fee
			'arbitrary_fee' => array(
				'amount' => '20.00',
				'label' => 'Arbitrary Item',
				'type' => 'item',
				'no_tax' => false
			)
		);
		
		//Test getting all Fees
		$this->assertEquals( $expected, EDD()->fees->get_fees( 'all' ) );
		
		$expected = array(
			//Legacy Fee
			'shipping_fee' => array(
				'amount' => '10.00',
				'label' => 'Shipping Fee',
				'type'  => 'fee',
				'no_tax' => false,
				'download_id' => 0,
				'price_id'    => NULL
			),
			//Normal Fee with Variable Price
			'shipping_fee_with_variable_price_id' => array(
				'amount' => '10.00',
				'label' => 'Shipping Fee (Small)',
				'type'  => 'fee',
				'no_tax' => false,
				'download_id' => $this->_post->ID,
				'price_id'    => 1
			),
		);
		
		//Test getting all fees with the type set to 'fee'
		$this->assertEquals( $expected, EDD()->fees->get_fees( 'fee' ) );
		
		$expected = array(
			//Normal Fee
			'arbitrary_fee' => array(
				'amount' => '20.00',
				'label' => 'Arbitrary Item',
				'type' => 'item',
				'no_tax' => false
			)
		);
		
		// Test getting only fees with the type set to 'item'
		$this->assertEquals( $expected, EDD()->fees->get_fees( 'item' ) );

		$expected = array(
			//Normal Fee with Variable Price
			'shipping_fee_with_variable_price_id' => array(
				'amount' => '10.00',
				'label' => 'Shipping Fee (Small)',
				'type'  => 'fee',
				'no_tax' => false,
				'download_id' => $this->_post->ID,
				'price_id'    => 1
			),
		);
		
		// Test getting download-specific fees
		$this->assertEquals( $expected, EDD()->fees->get_fees( 'fee', $this->_post->ID ) );

	}

	public function test_total_fees() {
		
		EDD()->session->set( 'edd_cart_fees', null );
		
		//Add Normal Fee 
		EDD()->fees->add_fee( array( 
			'amount' => '20.00', 
			'label' => 'Tax Fee', 
			'download_id' => NULL, 
			'id' => 'arbitrary_fee_one', 
			'type' => 'item' 
		) );
		
		//Add a variable price fee
		EDD()->fees->add_fee( array( 
			'amount' => '10.00', 
			'label' => 'Shipping Fee (Small)', 
			'download_id' => $this->_post->ID, 
			'price_id' => 1, 
			'id' => 'shipping_fee_with_variable_price_id_one', 
			'type' => 'fee' 
		) );
		
		//Add another variable price fee
		EDD()->fees->add_fee( array( 
			'amount' => '10.00', 
			'label' => 'Shipping Fee (Medium)', 
			'download_id' => $this->_post->ID, 
			'price_id' => 2, 
			'id' => 'shipping_fee_with_variable_price_id_two', 
			'type' => 'fee' 
		) );
		
		//Add another normal Fee 
		EDD()->fees->add_fee( array( 
			'amount' => '20.00', 
			'label' => 'Arbitrary Fee', 
			'download_id' => NULL, 
			'id' => 'arbitrary_fee_two', 
			'type' => 'item' 
		) );
		
		//Test adding up all the fees
		$this->assertEquals( 60, EDD()->fees->total() );
		
		//Test getting the total of fees that match the post ID passed
		$this->assertEquals( 20, EDD()->fees->total( $this->_post->ID ) );
		
		//Test the string value of the fees total
		$this->assertEquals( '60.00', EDD()->fees->total() );
	}

	public function test_record_fee() {
		
		EDD()->session->set( 'edd_cart_fees', null );
				
		//Add Legacy Fee
		EDD()->fees->add_fee( 10, 'Shipping Fee', 'shipping_fee' );
		
		//Add Normal Fee with variable price id for to a fee.
		EDD()->fees->add_fee( array( 
			'amount' => '10.00', 
			'label' => 'Shipping Fee (Small)', 
			'download_id' => $this->_post->ID, 
			'price_id' => 1, 
			'id' => 'shipping_fee_with_variable_price_id', 
			'type' => 'fee' 
		) );
		
		//Add Normal fee
		EDD()->fees->add_fee( array( 
			'amount' => '20.00', 
			'label' => 'Arbitrary Item', 
			'download_id' => $this->_post->ID, 
			'id' => 'arbitrary_fee', 
			'type' => 'item' 
		) );
				
		$expected = array(
			'fees' => array(
				//Legacy Fee
				'shipping_fee' => array(
					'amount' => '10.00',
					'label' => 'Shipping Fee',
					'type'  => 'fee',
					'no_tax' => false,
					'download_id' => 0,
					'price_id'    => NULL
				),
				//Normal Fee with Variable Price
				'shipping_fee_with_variable_price_id' => array(
					'amount' => '10.00',
					'label' => 'Shipping Fee (Small)',
					'type'  => 'fee',
					'no_tax' => false,
					'download_id' => $this->_post->ID,
					'price_id'    => 1
				),
				//Normal Fee
				'arbitrary_fee' => array(
					'amount' => '20.00',
					'label' => 'Arbitrary Item',
					'type' => 'item',
					'no_tax' => false
				)
			)
		);
		
		$actual = EDD()->fees->record_fees( $payment_meta = array(), $payment_data = array() );

		$this->assertEquals( $expected, $actual );
	}
	
	public function test_product_earnings_fee() {
		edd_empty_cart();
		EDD()->session->set( 'edd_cart_fees', null );
		$options = array(
			'price_id' => 1
		);
		$this->assertEquals( 0, edd_add_to_cart( $this->_post2->ID, $options ) ); // Add price 100 to cart
		
		//Arbitrary fee test.
		EDD()->fees->add_fee( array( 
			'amount' => '-20.00', 
			'label' => 'Arbitrary Item', 
			'download_id' => $this->_post2->ID,
			'price_id'    => 1,
			'id' => 'arbitrary_fee', 
			'type' => 'item' 
		) );
		
		$user = get_userdata(1);
		$user_info = array(
			'id'         => $user->ID,
			'email'      => $user->user_email,
			'first_name' => 'Network',
			'last_name'  => 'Administrator',
			'discount'   => 'none'
		);
		
		$purchase_data = array(
			'downloads'    => edd_get_cart_contents(),
			'fees'         => edd_get_cart_fees(),        // Any arbitrary fees that have been added to the cart
			'subtotal'     => edd_get_cart_subtotal(),    // Amount before taxes and discounts
			'discount'     => edd_get_cart_discounted_amount(), // Discounted amount
			'tax'          => edd_get_cart_tax(),               // Taxed amount
			'price'        => edd_get_cart_total(),    // Amount after taxes
			'date'         => date( 'Y-m-d H:i:s', strtotime( '-1 day' ) ),
			'purchase_key' => strtolower( md5( uniqid() ) ),
			'key'          => strtolower( md5( uniqid() ) ),
			'user_email'   => $user_info['email'],
			'user_info'    => $user_info,
			'cart_details' => edd_get_cart_content_details(),
			'status'       => 'pending',
			'gateway'      => 'manual',
			'currency'     => 'USD',
			'email'        => 'admin@example.org',
		);
		$_SERVER['REMOTE_ADDR'] = '127.0.0.1';
		$_SERVER['SERVER_NAME'] = 'edd_virtual';
		$payment_id = edd_insert_payment( $purchase_data );
		edd_complete_purchase( $payment_id, 'publish',  'pending' );
		$payment = new EDD_Payment( $payment_id );
		$stats = new EDD_Payment_Stats();
		$total_earnings = $stats->get_earnings( $this->_post2->ID );
		$this->assertEquals( '80.00', $total_earnings );
		EDD()->session->set( 'edd_cart_fees', null );
	}

}
