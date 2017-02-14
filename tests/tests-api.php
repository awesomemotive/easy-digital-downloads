<?php

/**
 * @group edd_api
 */
class Tests_API extends EDD_UnitTestCase {

	/**
	 * User ID fixture.
	 *
	 * @access protected
	 * @var    int
	 */
	protected static $user_id;

	/**
	 * User public key fixture.
	 *
	 * @access protected
	 * @var    string
	 */
	protected static $user_public_key;

	/**
	 * Download ID fixture.
	 *
	 * @access protected
	 * @var    int
	 */
	protected static $download_id;

	/**
	 * Payment ID fixture.
	 *
	 * @access protected
	 * @var    int
	 */
	protected static $payment_id;

	/**
	 * API instance fixture.
	 *
	 * @access protected
	 * @var    EDD_API
	 */
	protected static $edd_api;

	/**
	 * Roles instance fixture.
	 *
	 * @access protected
	 * @var    EDD_Roles
	 */
	protected static $roles;

	/**
	 * WP_Rewrite fixture.
	 *
	 * @access protected
	 * @var    WP_Rewrite
	 */
	protected static $wp_rewrite;

	/**
	 * WP_Query fixture.
	 *
	 * @access protected
	 * @var    WP_Query
	 */
	protected static $wp_query;

	protected $_rewrite = null;

	protected $query = null;

	protected $_post = null;

	protected $_api = null;

	protected $_api_output = null;

	protected $_api_output_sales = null;

	protected $_user_id = null;

	/**
	 * Set up fixtures once.
	 */
	public static function wpSetUpBeforeClass() {
		self::$user_id = self::edd()->user->create( array( 'role' => 'administrator' ) );

		self::$download_id = self::edd()->post->create( array(
			'post_title'  => 'Test Download',
			'post_type'   => 'download',
			'post_status' => 'publish'
		) );

		self::$wp_query = $GLOBALS['wp_query'];

		self::$wp_rewrite = $GLOBALS['wp_rewrite'];
		self::$wp_rewrite->init();
		flush_rewrite_rules( false );

		self::$edd_api = new EDD_API;
		self::$edd_api->add_endpoint( self::$wp_rewrite );

		self::$roles = new EDD_Roles;
		self::$roles->add_roles();
		self::$roles->add_caps();

		self::_setup_download();
		self::_setup_payment();
		self::_setup_user();
	}

	/**
	 * Helper to set up the download meta.
	 *
	 * @access private
	 * @static
	 */
	private static function _setup_download() {
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
			update_post_meta( self::$download_id, $key, $value );
		}
	}

	/**
	 * Helper to set up the payment object.
	 *
	 * @access private
	 * @static
	 */
	private static function _setup_payment() {
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
				'id' => self::$download_id,
				'options' => array(
					'price_id' => 1
				)
			)
		);

		$total = 0;
		$prices = get_post_meta( self::$download_id, 'edd_variable_prices', true );
		$item_price = $prices[1]['amount'];
		$total += $item_price;

		$cart_details = array(
			array(
				'name' => 'Test Download',
				'id' => self::$download_id,
				'item_number' => array(
					'id' => self::$download_id,
					'options' => array(
						'price_id' => 1
					)
				),
				'item_price' =>  100,
				'subtotal' =>  100,
				'price' =>  100,
				'tax' => 0,
				'quantity' => 1
			)
		);

		$purchase_data = array(
			'price'        => number_format( (float) $total, 2 ),
			'date'         => date( 'Y-m-d H:i:s', strtotime( '-1 day' ) ),
			'purchase_key' => strtolower( md5( uniqid() ) ),
			'user_email'   => $user_info['email'],
			'user_info'    => $user_info,
			'currency'     => 'USD',
			'downloads'    => $download_details,
			'cart_details' => $cart_details,
			'status'       => 'pending'
		);

		self::$payment_id = edd_insert_payment( $purchase_data );
	}

	/**
	 * Helper to set up the user meta.
	 *
	 * @access private
	 * @static
	 *
	 * @param int $user_id Optional. User ID. Default is the value of the static $user_id fixture.
	 */
	private static function _setup_user( $user_id = 0 ) {
		if ( ! $user_id ) {
			$user_id = self::$user_id;
		}

		$_POST['edd_set_api_key'] = 1;

		self::$edd_api->update_key( $user_id );

		unset( $_POST['edd_set_api_key'] );
	}

	public function setUp() {
		parent::setUp();

		$_SERVER['REMOTE_ADDR'] = '10.0.0.0';

		edd_update_payment_status( self::$payment_id, 'complete' );

		$this->_api_output = self::$edd_api->get_products();
		$this->_api_output_sales = self::$edd_api->get_recent_sales();

		$GLOBALS['wp_query']->query_vars['format'] = 'override';
	}

	public function tearDown() {
		remove_action( 'edd_api_output_override_xml', array( $this, 'override_api_xml_format' ) );

		parent::tearDown();
	}

	public function test_endpoints() {
		$this->assertEquals('edd-api', self::$wp_rewrite->endpoints[0][1]);
	}

	public function test_query_vars() {
		global $wp_filter;

		foreach ( $wp_filter['query_vars'][10] as $arr ) :

			if ( 'query_vars' == $arr['function'][1] ) {
				$this->assertTrue( true );
			}

		endforeach;

		$out = self::$edd_api->query_vars( array() );
		$this->assertEquals( 'token', $out[0] );
		$this->assertEquals( 'key', $out[1] );
		$this->assertEquals( 'query', $out[2] );
		$this->assertEquals( 'type', $out[3] );
		$this->assertEquals( 'product', $out[4] );
		$this->assertEquals( 'category', $out[5] );
		$this->assertEquals( 'tag', $out[6] );
		$this->assertEquals( 'term_relation', $out[7] );
		$this->assertEquals( 'number', $out[8] );
		$this->assertEquals( 'date', $out[9] );
		$this->assertEquals( 'startdate', $out[10] );
		$this->assertEquals( 'enddate', $out[11] );
		$this->assertEquals( 'customer', $out[12] );
		$this->assertEquals( 'discount', $out[13] );
		$this->assertEquals( 'format', $out[14] );
	}

	public function test_get_versions() {
		$this->assertInternalType( 'array', self::$edd_api->get_versions() );
		$this->assertArrayHasKey( 'v1', self::$edd_api->get_versions() );
	}

	public function test_get_default_version() {

		$this->assertEquals( 'v2', self::$edd_api->get_default_version() );

		define( 'EDD_API_VERSION', 'v1' );
		$this->assertEquals( 'v1', self::$edd_api->get_default_version() );

	}

	public function test_get_queried_version() {
		$this->markTestSkipped( 'This test is causing the suite to die for some reason' );

		global $wp_query;
		$wp_query = self::$wp_query;

		$wp_query->query_vars['edd-api'] = 'sales';

		self::$edd_api->process_query();

		$this->assertEquals( 'v1', self::$edd_api->get_queried_version() );

		define( 'EDD_API_VERSION', 'v2' );

		self::$edd_api->process_query();

		$this->assertEquals( 'v2', self::$edd_api->get_queried_version() );

	}

	public function test_get_products() {
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

		$this->assertEquals( self::$download_id, $out['products'][0]['info']['id'] );
		$this->assertEquals( 'test-download', $out['products'][0]['info']['slug'] );
		$this->assertEquals( 'Test Download', $out['products'][0]['info']['title'] );
		$this->assertEquals( 'publish', $out['products'][0]['info']['status'] );
		$this->assertEquals( '', $out['products'][0]['info']['thumbnail'] );

		$post = get_post( self::$download_id );
		$this->assertEquals( $post->post_content, $out['products'][0]['info']['content'] );
	}

	public function test_get_product_stats() {
		$out = $this->_api_output;
		$this->assertArrayHasKey( 'stats', $out['products'][0] );
		$this->assertArrayHasKey( 'total', $out['products'][0]['stats'] );
		$this->assertArrayHasKey( 'sales', $out['products'][0]['stats']['total'] );
		$this->assertArrayHasKey( 'earnings', $out['products'][0]['stats']['total'] );
		$this->assertArrayHasKey( 'monthly_average', $out['products'][0]['stats'] );
		$this->assertArrayHasKey( 'sales', $out['products'][0]['stats']['monthly_average'] );
		$this->assertArrayHasKey( 'earnings', $out['products'][0]['stats']['monthly_average'] );

		$this->assertEquals( '60', $out['products'][0]['stats']['total']['sales'] );
		$this->assertEquals( '229.43', $out['products'][0]['stats']['total']['earnings'] );
		$this->assertEquals( '60', $out['products'][0]['stats']['monthly_average']['sales'] );
		$this->assertEquals( '229.43', $out['products'][0]['stats']['monthly_average']['earnings'] );
	}

	public function test_get_products_pricing() {
		$out = $this->_api_output;
		$this->assertArrayHasKey( 'pricing', $out['products'][0] );
		$this->assertArrayHasKey( 'simple', $out['products'][0]['pricing'] );
		$this->assertArrayHasKey( 'advanced', $out['products'][0]['pricing'] );

		$this->assertEquals( '20', $out['products'][0]['pricing']['simple'] );
		$this->assertEquals( '100', $out['products'][0]['pricing']['advanced'] );
	}

	public function test_get_products_files() {
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


	public function test_get_products_notes() {
		$out = $this->_api_output;
		$this->assertArrayHasKey( 'notes', $out['products'][0] );
		$this->assertEquals( 'Purchase Notes', $out['products'][0]['notes'] );
	}

	public function test_get_recent_sales() {
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

		self::$edd_api->update_key( self::$user_id );

		$user_public = self::$edd_api->get_user_public_key( self::$user_id );
		$user_secret = self::$edd_api->get_user_secret_key( self::$user_id );

		$this->assertNotEmpty( $user_public );
		$this->assertNotEmpty( $user_secret );

		// Backwards compatibilty check for API Keys
		$this->assertEquals( $user_public, get_user_meta( self::$user_id, 'edd_user_public_key', true ) );
		$this->assertEquals( $user_secret, get_user_meta( self::$user_id, 'edd_user_secret_key', true ) );

	}

	public function test_get_user() {
		$result = self::$edd_api->get_user( self::$edd_api->get_user_public_key( self::$user_id ) );

		$this->assertEquals( self::$user_id, $result );
	}

	public function test_get_customers() {
		$out = self::$edd_api->get_customers();

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
		$this->assertEquals( 'Admin', $out['customers'][0]['info']['first_name'] );
		$this->assertEquals( 'User', $out['customers'][0]['info']['last_name'] );
		$this->assertEquals( 'admin@example.org', $out['customers'][0]['info']['email'] );
		$this->assertEquals( 1, $out['customers'][0]['stats']['total_purchases'] );
		$this->assertEquals( 100.0, $out['customers'][0]['stats']['total_spent'] );
		$this->assertEquals( 0, $out['customers'][0]['stats']['total_downloads'] );
	}

	public function test_missing_auth() {
		$this->markTestSkipped( 'Needs to be rewritten since this outputs xml that kills travis with a 255 error (fatal PHP error)' );
		//self::$api->missing_auth();
		//$out = self::$api->get_output();
		//$this->assertArrayHasKey( 'error', $out );
		//$this->assertEquals( 'You must specify both a token and API key!', $out['error'] );

	}

	public function test_invalid_auth() {
		$this->markTestSkipped('Needs to be rewritten since this outputs xml that kills travis with a 255 error (fatal PHP error)');
		//self::$api->invalid_auth();
		//$out = self::$api->get_output();
		//$this->assertArrayHasKey( 'error', $out );
		//$this->assertEquals( 'Your request could not be authenticated!', $out['error'] );
	}

	public function test_invalid_key() {
		$this->markTestSkipped('Needs to be rewritten since this outputs xml that kills travis with a 255 error (fatal PHP error)');
		//$out = self::$api->invalid_key();
		//$out = self::$api->get_output();
		//$this->assertArrayHasKey( 'error', $out );
		//$this->assertEquals( 'Invalid API key!', $out['error'] );
	}

	public function test_info() {
		$out = self::$edd_api->get_info();

		$this->assertArrayHasKey( 'info', $out );
		$this->assertArrayHasKey( 'site', $out['info'] );
		$this->assertArrayHasKey( 'currency', $out['info']['site'] );
		$this->assertArrayHasKey( 'currency_position', $out['info']['site'] );
		$this->assertArrayHasKey( 'decimal_separator', $out['info']['site'] );
		$this->assertArrayHasKey( 'thousands_separator', $out['info']['site'] );
		$this->assertArrayNotHasKey( 'integrations', $out['info'] ); // By default we shouldn't have any integrations

		$this->markTestIncomplete( 'This test needs to be fixed. The permissions key doesn\'t exist due to not being able to correctly check the user\'s permissions' );
	}

	public function test_process_query() {
		global $wp_query;

		$this->markTestSkipped('Needs to be rewritten since this outputs xml that kills travis with a 255 error (fatal PHP error)');
		$_POST['edd_set_api_key'] = 1;

		self::$edd_api->update_key( self::$user_id );

		$wp_query->query_vars['edd-api'] = 'products';
		$wp_query->query_vars['key'] = get_user_meta( self::$user_id, 'edd_user_public_key', true );
		$wp_query->query_vars['token'] = hash( 'md5', get_user_meta( self::$user_id, 'edd_user_secret_key', true ) . get_user_meta( self::$user_id, 'edd_user_public_key', true ) );

		self::$edd_api->process_query();

		$out = self::$edd_api->get_output();

		$this->assertArrayHasKey( 'info', $out['products'][0] );
		$this->assertArrayHasKey( 'id', $out['products'][0]['info'] );
		$this->assertArrayHasKey( 'slug', $out['products'][0]['info'] );
		$this->assertEquals( 'test-download', $out['products'][0]['info']['slug'] );
		$this->assertArrayHasKey( 'title', $out['products'][0]['info'] );
		$this->assertEquals( 'Test Download', $out['products'][0]['info']['title'] );
		$this->assertArrayHasKey( 'create_date', $out['products'][0]['info'] );
		$this->assertArrayHasKey( 'modified_date', $out['products'][0]['info'] );
		$this->assertArrayHasKey( 'status', $out['products'][0]['info'] );
		$this->assertEquals( 'publish', $out['products'][0]['info']['status'] );
		$this->assertArrayHasKey( 'link', $out['products'][0]['info'] );
		$this->assertArrayHasKey( 'content', $out['products'][0]['info'] );
		$this->assertEquals( 'Post content 1', $out['products'][0]['info']['content'] );
		$this->assertArrayHasKey( 'thumbnail', $out['products'][0]['info'] );

		$this->markTestSkipped( 'This test needs to be fixed. The stats key doesn\'t exist due to not being able to correctly check the user\'s permissions' );
		$this->assertArrayHasKey( 'stats', $out['products'][0] );
		$this->assertArrayHasKey( 'total', $out['products'][0]['stats'] );
		$this->assertArrayHasKey( 'sales', $out['products'][0]['stats']['total'] );
		$this->assertEquals( 59, $out['products'][0]['stats']['total']['sales'] );
		$this->assertArrayHasKey( 'earnings', $out['products'][0]['stats']['total'] );
		$this->assertEquals( 129.43, $out['products'][0]['stats']['total']['earnings'] );
		$this->assertArrayHasKey( 'monthly_average', $out['products'][0]['stats'] );
		$this->assertArrayHasKey( 'sales', $out['products'][0]['stats']['monthly_average'] );
		$this->assertEquals( 59, $out['products'][0]['stats']['monthly_average']['sales'] );
		$this->assertArrayHasKey( 'earnings', $out['products'][0]['stats']['monthly_average'] );
		$this->assertEquals( 129.43, $out['products'][0]['stats']['monthly_average']['earnings'] );

		$this->assertArrayHasKey( 'pricing', $out['products'][0] );
		$this->assertArrayHasKey( 'simple', $out['products'][0]['pricing'] );
		$this->assertEquals( 20, $out['products'][0]['pricing']['simple'] );
		$this->assertArrayHasKey( 'advanced', $out['products'][0]['pricing'] );
		$this->assertEquals( 100, $out['products'][0]['pricing']['advanced'] );

		$this->assertArrayHasKey( 'files', $out['products'][0] );
		$this->assertArrayHasKey( 'name', $out['products'][0]['files'][0] );
		$this->assertArrayHasKey( 'file', $out['products'][0]['files'][0] );
		$this->assertArrayHasKey( 'condition', $out['products'][0]['files'][0] );
		$this->assertArrayHasKey( 'name', $out['products'][0]['files'][1] );
		$this->assertArrayHasKey( 'file', $out['products'][0]['files'][1] );
		$this->assertArrayHasKey( 'condition', $out['products'][0]['files'][1] );
		$this->assertEquals( 'File 1', $out['products'][0]['files'][0]['name'] );
		$this->assertEquals( 'http://localhost/file1.jpg', $out['products'][0]['files'][0]['file'] );
		$this->assertEquals( 0, $out['products'][0]['files'][0]['condition'] );
		$this->assertEquals( 'File 2', $out['products'][0]['files'][1]['name'] );
		$this->assertEquals( 'http://localhost/file2.jpg', $out['products'][0]['files'][1]['file'] );
		$this->assertEquals( 'all', $out['products'][0]['files'][1]['condition'] );

		$this->assertArrayHasKey( 'notes', $out['products'][0] );
		$this->assertEquals( 'Purchase Notes', $out['products'][0]['notes'] );
	}

}
