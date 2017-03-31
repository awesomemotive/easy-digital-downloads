<?php

/**
 * @group edd_api
 */
class Tests_API extends EDD_UnitTestCase {
	protected static $_rewrite = null;
	protected static $_query = null;
	protected static $_payment_id = null;
	protected static $_payment = null;
	protected static $_api = null;
	protected static $_api_output = null;
	protected static $_api_output_sales = null;
	protected static $_user_id = null;

	public static function wpSetUpBeforeClass() {
		global $wp_rewrite, $wp_query;
		$GLOBALS['wp_rewrite']->init();
		flush_rewrite_rules( false );

		self::$_api = EDD()->api;

		$roles = new EDD_Roles;
		$roles->add_roles();
		$roles->add_caps();

		self::$_api->add_endpoint( $wp_rewrite );

		self::$_rewrite = $wp_rewrite;
		self::$_query = $wp_query;

		wp_set_current_user( 1, 'admin' );
		self::$_payment_id = EDD_Helper_Payment::create_simple_payment();
		self::$_payment     = edd_get_payment( self::$_payment_id );

		self::$_user_id = 1;
		self::$_payment->status = 'publish';
		self::$_payment->save();

		self::$_api_output       = self::$_api->get_products();
		self::$_api_output_sales = self::$_api->get_recent_sales();

		global $wp_query;
		$wp_query->query_vars['format'] = 'override';
	}

	public function test_endpoints() {
		$this->assertEquals('edd-api', self::$_rewrite->endpoints[0][1]);
	}

	public function test_query_vars() {
		global $wp_filter;

		foreach ( $wp_filter['query_vars'][10] as $arr ) :

			if ( 'query_vars' == $arr['function'][1] ) {
				$this->assertTrue( true );
			}

		endforeach;

		$out = self::$_api->query_vars( array() );
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
		$this->assertInternalType( 'array', self::$_api->get_versions() );
		$this->assertArrayHasKey( 'v1', self::$_api->get_versions() );
	}

	public function test_get_default_version() {

		$this->assertEquals( 'v2', self::$_api->get_default_version() );

		define( 'EDD_API_VERSION', 'v1' );
		$this->assertEquals( 'v1', self::$_api->get_default_version() );

	}

	public function test_get_queried_version() {
		$this->markTestIncomplete( 'This test is causing the suite to die for some reason' );
		global $wp_query;

		$wp_query->query_vars['edd-api'] = 'sales';

		self::$_api->process_query();

		$this->assertEquals( 'v1', self::$_api->get_queried_version() );

		define( 'EDD_API_VERSION', 'v2' );

		self::$_api->process_query();

		$this->assertEquals( 'v2', self::$_api->get_queried_version() );

	}

	public function test_get_products() {
		$out = self::$_api_output;
		$this->assertArrayHasKey( 'id', $out['products'][0]['info'] );
		$this->assertArrayHasKey( 'slug', $out['products'][0]['info'] );
		$this->assertArrayHasKey( 'title', $out['products'][0]['info'] );
		$this->assertArrayHasKey( 'create_date', $out['products'][0]['info'] );
		$this->assertArrayHasKey( 'modified_date', $out['products'][0]['info'] );
		$this->assertArrayHasKey( 'status', $out['products'][0]['info'] );
		$this->assertArrayHasKey( 'link', $out['products'][0]['info'] );
		$this->assertArrayHasKey( 'content', $out['products'][0]['info'] );
		$this->assertArrayHasKey( 'thumbnail', $out['products'][0]['info'] );

		$product = new EDD_Download( self::$_payment->downloads[0]['id'] );
		$this->assertEquals( $product->ID, $out['products'][1]['info']['id'] );
		$this->assertEquals( 'variable-test-download-product', $out['products'][0]['info']['slug'] );
		$this->assertEquals( 'Variable Test Download Product', $out['products'][0]['info']['title'] );
		$this->assertEquals( 'publish', $out['products'][0]['info']['status'] );
		$this->assertEquals( $product->post_content, $out['products'][0]['info']['content'] );
		$this->assertEquals( '', $out['products'][0]['info']['thumbnail'] );
	}

	public function test_get_product_stats() {
		$out = self::$_api_output;
		$this->assertArrayHasKey( 'stats', $out['products'][0] );
		$this->assertArrayHasKey( 'total', $out['products'][0]['stats'] );
		$this->assertArrayHasKey( 'sales', $out['products'][0]['stats']['total'] );
		$this->assertArrayHasKey( 'earnings', $out['products'][0]['stats']['total'] );
		$this->assertArrayHasKey( 'monthly_average', $out['products'][0]['stats'] );
		$this->assertArrayHasKey( 'sales', $out['products'][0]['stats']['monthly_average'] );
		$this->assertArrayHasKey( 'earnings', $out['products'][0]['stats']['monthly_average'] );

		$this->assertEquals( '7', $out['products'][0]['stats']['total']['sales'] );
		$this->assertEquals( '220.000000', $out['products'][0]['stats']['total']['earnings'] );
		$this->assertEquals( '7', $out['products'][0]['stats']['monthly_average']['sales'] );
		$this->assertEquals( '220.000000', $out['products'][0]['stats']['monthly_average']['earnings'] );
	}

	public function test_get_products_pricing() {
		$out = self::$_api_output;
		$this->assertArrayHasKey( 'pricing', $out['products'][0] );
		$this->assertArrayHasKey( 'simple', $out['products'][0]['pricing'] );
		$this->assertArrayHasKey( 'advanced', $out['products'][0]['pricing'] );

		$this->assertEquals( '20', $out['products'][0]['pricing']['simple'] );
		$this->assertEquals( '100', $out['products'][0]['pricing']['advanced'] );
	}

	public function test_get_products_files() {
		$out = self::$_api_output;
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
		$out = self::$_api_output;
		$this->assertArrayHasKey( 'notes', $out['products'][0] );
		$this->assertEquals( 'Purchase Notes', $out['products'][0]['notes'] );
	}

	public function test_get_recent_sales() {
		$out = self::$_api_output_sales;
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

		$this->assertEquals( 120.00, $out['sales'][0]['subtotal'] );
		$this->assertEquals( 0, $out['sales'][0]['tax'] );
		$this->assertEquals( 120.00, $out['sales'][0]['total'] );
		$this->assertEquals( '', $out['sales'][0]['gateway'] );
		$this->assertEquals( 'admin@example.org', $out['sales'][0]['email'] );
		$this->assertEquals( 'Variable Test Download Product', $out['sales'][0]['products'][1]['name'] );
		$this->assertEquals( 100, $out['sales'][0]['products'][1]['price'] );
		$this->assertEquals( 'Advanced', $out['sales'][0]['products'][1]['price_name'] );
	}

	public function test_update_key() {
		wp_set_current_user( 1, 'admin' );
		$_POST['edd_set_api_key'] = 1;

		EDD()->api->update_key( self::$_user_id );

		$user_public = EDD()->api->get_user_public_key( self::$_user_id );
		$user_secret = EDD()->api->get_user_secret_key( self::$_user_id );

		$this->assertNotEmpty( $user_public );
		$this->assertNotEmpty( $user_secret );

		// Backwards compatibilty check for API Keys
		$this->assertEquals( $user_public, get_user_meta( self::$_user_id, 'edd_user_public_key', true ) );
		$this->assertEquals( $user_secret, get_user_meta( self::$_user_id, 'edd_user_secret_key', true ) );

	}

	public function test_get_user() {
		wp_set_current_user( 1, 'admin' );
		$_POST['edd_set_api_key'] = 1;
		EDD()->api->update_key( self::$_user_id );
		$public_key = EDD()->api->get_user_public_key( self::$_user_id );
		$this->assertEquals( self::$_user_id, EDD()->api->get_user( $public_key ) );

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
		$this->assertEquals( 1, $out['customers'][0]['stats']['total_purchases'] );
		$this->assertEquals( 120.000000, $out['customers'][0]['stats']['total_spent'] );
		$this->assertEquals( 0, $out['customers'][0]['stats']['total_downloads'] );
	}

	public function test_missing_auth() {
		$this->markTestIncomplete('Needs to be rewritten since this outputs xml that kills travis with a 255 error (fatal PHP error)');
		//$this->_api->missing_auth();
		//$out = $this->_api->get_output();
		//$this->assertArrayHasKey( 'error', $out );
		//$this->assertEquals( 'You must specify both a token and API key!', $out['error'] );

	}

	public function test_invalid_auth() {
		$this->markTestIncomplete('Needs to be rewritten since this outputs xml that kills travis with a 255 error (fatal PHP error)');
		//$this->_api->invalid_auth();
		//$out = $this->_api->get_output();
		//$this->assertArrayHasKey( 'error', $out );
		//$this->assertEquals( 'Your request could not be authenticated!', $out['error'] );
	}

	public function test_invalid_key() {
		$this->markTestIncomplete('Needs to be rewritten since this outputs xml that kills travis with a 255 error (fatal PHP error)');
		//$out = $this->_api->invalid_key();
		//$out = $this->_api->get_output();
		//$this->assertArrayHasKey( 'error', $out );
		//$this->assertEquals( 'Invalid API key!', $out['error'] );
	}

	public function test_info() {
		$out = EDD()->api->get_info();

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

		$this->markTestIncomplete('Needs to be rewritten since this outputs xml that kills travis with a 255 error (fatal PHP error)');
		$_POST['edd_set_api_key'] = 1;

		$this->_api->update_key( self::$_user_id );

		$wp_query->query_vars['edd-api'] = 'products';
		$wp_query->query_vars['key'] = get_user_meta( self::$_user_id, 'edd_user_public_key', true );
		$wp_query->query_vars['token'] = hash( 'md5', get_user_meta( self::$_user_id, 'edd_user_secret_key', true ) . get_user_meta( self::$_user_id, 'edd_user_public_key', true ) );

		self::$_api->process_query();

		$out = self::$_api->get_output();

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

		$this->markTestIncomplete( 'This test needs to be fixed. The stats key doesn\'t exist due to not being able to correctly check the user\'s permissions' );
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
