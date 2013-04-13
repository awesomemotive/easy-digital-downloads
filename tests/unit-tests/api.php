<?php
/**
 * Test API
 */

class Test_Easy_Digital_Downloads_API extends WP_UnitTestCase {
	protected $_rewrite = null;

	protected $query = null;

	protected $_post = null;

	protected $_api_output = null;

	protected $_api_output_sales = null;

	protected $_user_id = null;

	public function setUp() {
		parent::setUp();

		global $wp_rewrite, $wp_query;
		$GLOBALS['wp_rewrite']->init();
		flush_rewrite_rules();

		EDD()->api->add_endpoint( $wp_rewrite );

		$this->_rewrite = $wp_rewrite;
		$this->_query = $wp_query;

		/** Create some downloads/sales for the API Tests */
		$wp_factory = new WP_UnitTest_Factory;
		$post_id = $wp_factory->post->create( array( 'post_title' => 'Test Download', 'post_type' => 'download', 'post_status' => 'publish' ) );

		$this->_user_id = $wp_factory->user->create( array( 'role' => 'administrator' ) );
		wp_set_current_user( $this->_user_id );

		$_variable_pricing = array(
			array(
				'name' => 'Simple',
				'amount' => 20
			),
			array(
				'name' => 'Advanced',
				'amount' => 100
			)
		);

		$_download_files = array(
			array(
				'name' => 'File 1',
				'file' => 'http://localhost/file1.jpg',
				'condition' => 0
			),
			array(
				'name' => 'File 2',
				'file' => 'http://localhost/file2.jpg',
				'condition' => 'all'
			)
		);

		$meta = array(
			'edd_price' => '0.00',
			'_variable_pricing' => 1,
			'_edd_price_options_mode' => 'on',
			'edd_variable_prices' => array_values( $_variable_pricing ), 
			'edd_download_files' => array_values( $_download_files ),
			'_edd_download_limit' => 20,
			'_edd_hide_purchase_link' => 1,
			'edd_product_notes' => 'Purchase Notes',
			'_edd_product_type' => 'default',
			'_edd_download_earnings' => 129.43,
			'_edd_download_sales' => 59,
			'_edd_download_limit_override_1' => 1
		);
		foreach ( $meta as $key => $value ) {
			update_post_meta( $post_id, $key, $value );
		}

		$this->_post = get_post( $post_id );

		/** Generate some sales */
		$user = get_userdata(1);

		$user_info = array(
			'id' => $user->ID,
			'email' => $user->user_email,
			'first_name' => $user->first_name,
			'last_name' => $user->last_name,
			'discount' => 'none'
		);

		$download_details = array(
			array(
				'id' => $this->_post->ID,
				'options' => array(
					'price_id' => 1
				)
			)
		);

		$price = '100.00';

		$total = 0;

		$prices = get_post_meta( $download_details[0]['id'], 'edd_variable_prices', true );
		$item_price = $prices[1]['amount'];

		$total += $item_price;

		$cart_details = array(
			array(
				'name' => 'Test Download',
				'id' => $this->_post->ID,
				'item_number' => array(
					'id' => $this->_post->ID,
					'options' => array(
						'price_id' => 1
					)
				),
				'price' =>  100,
				'quantity' => 1
			)
		);

		$purchase_data = array(
			'price' => number_format( (float) $total, 2 ),
			'date' => date( 'Y-m-d H:i:s', strtotime( '-1 day' ) ),
			'purchase_key' => strtolower( md5( uniqid() ) ),
			'user_email' => $user_info['email'],
			'user_info' => $user_info,
			'currency' => 'USD',
			'downloads' => $download_details,
			'cart_details' => $cart_details,
			'status' => 'pending'
		);

		$_SERVER['REMOTE_ADDR'] = '10.0.0.0';

		$payment_id = edd_insert_payment( $purchase_data );

		$this->_api_output = EDD()->api->get_products();
		$this->_api_output_sales = EDD()->api->get_recent_sales();
	}

	public function tearDown() {
		remove_action( 'edd_api_output_override_xml', array( $this, 'override_api_xml_format' ) );
	}

	public function testEndpoints() {
		$this->assertEquals('edd-api', $this->_rewrite->endpoints[0][1]);
	}

	public function test_query_vars() {
		global $wp_filter;

		foreach ( $wp_filter['query_vars'][10] as $arr ) :

			if ( 'query_vars' == $arr['function'][1] ) {
				$this->assertTrue( true );
			}

		endforeach;
	}

	public function testGetProducts() {
		$out = $this->_api_output;
		$this->assertArrayHasKey( 'id', $out['products'][0]['info'] );
		$this->assertArrayHasKey( 'slug', $out['products'][0]['info'] );
		$this->assertArrayHasKey( 'title', $out['products'][0]['info'] );
		$this->assertArrayHasKey( 'create_date', $out['products'][0]['info'] );
		$this->assertArrayHasKey( 'modified_date', $out['products'][0]['info'] );
		$this->assertArrayHasKey( 'status', $out['products'][0]['info'] );
		$this->assertArrayHasKey( 'link', $out['products'][0]['info'] );
		$this->assertArrayHasKey( 'content', $out['products'][0]['info'] );
		$this->assertArrayHasKey( 'thumbnail', $out['products'][0]['info'] );

		$this->assertEquals( $this->_post->ID, $out['products'][0]['info']['id'] );
		$this->assertEquals( 'test-download', $out['products'][0]['info']['slug'] );
		$this->assertEquals( 'Test Download', $out['products'][0]['info']['title'] );
		$this->assertEquals( 'publish', $out['products'][0]['info']['status'] );
		$this->assertEquals( 'http://example.org/downloads/test-download/', $out['products'][0]['info']['link'] );
		$this->assertEquals( 'Post content 1', $out['products'][0]['info']['content'] );
		$this->assertEquals( '', $out['products'][0]['info']['thumbnail'] );
	}

	public function testGetProducts_Stats() {
		$out = $this->_api_output;		
		$this->assertArrayHasKey( 'stats', $out['products'][0] );
		$this->assertArrayHasKey( 'total', $out['products'][0]['stats'] );
		$this->assertArrayHasKey( 'sales', $out['products'][0]['stats']['total'] );
		$this->assertArrayHasKey( 'earnings', $out['products'][0]['stats']['total'] );
		$this->assertArrayHasKey( 'monthly_average', $out['products'][0]['stats'] );
		$this->assertArrayHasKey( 'sales', $out['products'][0]['stats']['monthly_average'] );
		$this->assertArrayHasKey( 'earnings', $out['products'][0]['stats']['monthly_average'] );

		$this->assertEquals( '59', $out['products'][0]['stats']['total']['sales'] );
		$this->assertEquals( '129.43', $out['products'][0]['stats']['total']['earnings'] );
		$this->assertEquals( '59', $out['products'][0]['stats']['monthly_average']['sales'] );
		$this->assertEquals( '129.43', $out['products'][0]['stats']['monthly_average']['earnings'] );
	}

	public function testGetProducts_Pricing() {
		$out = $this->_api_output;
		$this->assertArrayHasKey( 'pricing', $out['products'][0] );
		$this->assertArrayHasKey( 'simple', $out['products'][0]['pricing'] );
		$this->assertArrayHasKey( 'advanced', $out['products'][0]['pricing'] );

		$this->assertEquals( '20', $out['products'][0]['pricing']['simple'] );
		$this->assertEquals( '100', $out['products'][0]['pricing']['advanced'] );
	}

	public function testGetProducts_Files() {
		$out = $this->_api_output;
		$this->assertArrayHasKey( 'files', $out['products'][0]) ;

		foreach ( $out['products'][0]['files'] as $file ) {
			$this->assertArrayHasKey( 'name', $file );
			$this->assertArrayHasKey( 'file', $file );
			$this->assertArrayHasKey( 'condition', $file );
		}

		$this->assertEquals( 'File 1', $out['products'][0]['files'][0]['name'] );
		$this->assertEquals( 'http://localhost/file1.jpg', $out['products'][0]['files'][0]['file'] );
		$this->assertEquals( 0, $out['products'][0]['files'][0]['condition'] );
		$this->assertEquals( 'File 2', $out['products'][0]['files'][1]['name'] );
		$this->assertEquals( 'http://localhost/file2.jpg', $out['products'][0]['files'][1]['file'] );
		$this->assertEquals( 'all', $out['products'][0]['files'][1]['condition'] );
	}

	public function testGetProducts_Notes() {
		$out = $this->_api_output;
		$this->assertArrayHasKey( 'notes', $out['products'][0] );
		$this->assertEquals( 'Purchase Notes', $out['products'][0]['notes'] );
	}

	public function testGetRecentSales() {
		$out = $this->_api_output_sales;

		$this->assertArrayHasKey( 'sales', $out );
		$this->assertArrayHasKey( 'ID', $out['sales'][0] );
		$this->assertArrayHasKey( 'key', $out['sales'][0] );
		$this->assertArrayHasKey( 'subtotal', $out['sales'][0] );
		$this->assertArrayHasKey( 'tax', $out['sales'][0] );
		$this->assertArrayHasKey( 'fees', $out['sales'][0] );
		$this->assertArrayHasKey( 'total', $out['sales'][0] );
		$this->assertArrayHasKey( 'gateway', $out['sales'][0] );
		$this->assertArrayHasKey( 'email', $out['sales'][0] );
		$this->assertArrayHasKey( 'date', $out['sales'][0] );
		$this->assertArrayHasKey( 'products', $out['sales'][0] );
		$this->assertArrayHasKey( 'name', $out['sales'][0]['products'][0] );
		$this->assertArrayHasKey( 'price', $out['sales'][0]['products'][0] );
		$this->assertArrayHasKey( 'price_name', $out['sales'][0]['products'][0] );

		$this->assertEquals( 100.00, $out['sales'][0]['subtotal'] );
		$this->assertEquals( 0, $out['sales'][0]['tax'] );
		$this->assertEquals( 100.00, $out['sales'][0]['total'] );
		$this->assertEquals( '', $out['sales'][0]['gateway'] );
		$this->assertEquals( 'admin@example.org', $out['sales'][0]['email'] );
		$this->assertEquals( 'Test Download', $out['sales'][0]['products'][0]['name'] );
		$this->assertEquals( 100, $out['sales'][0]['products'][0]['price'] );
		$this->assertEquals( 'Advanced', $out['sales'][0]['products'][0]['price_name'] );
	}

	public function test_update_key() {
		$_POST['edd_set_api_key'] = 1;
		EDD()->api->update_key( $this->_user_id );
		$this->assertNotEmpty( get_user_meta( $this->_user_id, 'edd_user_public_key', true ) );
		$this->assertNotEmpty( get_user_meta( $this->_user_id, 'edd_user_secret_key', true ) );
	}

	public function test_get_user() {
		$_POST['edd_set_api_key'] = 1;
		EDD()->api->update_key( $this->_user_id );
		$this->assertEquals( $this->_user_id, EDD()->api->get_user( get_user_meta( $this->_user_id, 'edd_user_public_key', true ) ) );
	}

	public function test_get_customers() {
		$out = EDD()->api->get_customers();

		$this->assertArrayHasKey( 'customers', $out );
		$this->assertArrayHasKey( 'info', $out['customers'][0] );
		$this->assertArrayHasKey( 'id', $out['customers'][0]['info'] );
		$this->assertArrayHasKey( 'username', $out['customers'][0]['info'] );
		$this->assertArrayHasKey( 'display_name', $out['customers'][0]['info'] );
		$this->assertArrayHasKey( 'first_name', $out['customers'][0]['info'] );
		$this->assertArrayHasKey( 'last_name', $out['customers'][0]['info'] );
		$this->assertArrayHasKey( 'email', $out['customers'][0]['info'] );
		$this->assertArrayHasKey( 'stats', $out['customers'][0] );
		$this->assertArrayHasKey( 'total_purchases', $out['customers'][0]['stats'] );
		$this->assertArrayHasKey( 'total_spent', $out['customers'][0]['stats'] );
		$this->assertArrayHasKey( 'total_downloads', $out['customers'][0]['stats'] );

		$this->assertEquals( 1, $out['customers'][0]['info']['id'] );
		$this->assertEquals( 'admin', $out['customers'][0]['info']['username'] );
		$this->assertEquals( '', $out['customers'][0]['info']['first_name'] );
		$this->assertEquals( '', $out['customers'][0]['info']['last_name'] );
		$this->assertEquals( 'admin@example.org', $out['customers'][0]['info']['email'] );
		$this->assertEquals( 0, $out['customers'][0]['stats']['total_purchases'] );
		$this->assertEquals( 0, $out['customers'][0]['stats']['total_spent'] );
		$this->assertEquals( 0, $out['customers'][0]['stats']['total_downloads'] );
	}

	public function api_override( $data, $object ) {
		return $data;
	}

	public function test_output() {
		global $wp_query;
		$wp_query->query_vars['format'] = 'o';
		add_action( 'edd_api_output_o', 10, 2 );
		$this->assertNotNull( EDD()->api->invalid_auth() );
	}
}