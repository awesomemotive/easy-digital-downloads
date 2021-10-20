<?php


/**
 * @group edd_downloads
 */
class Tests_Process_Download extends EDD_UnitTestCase {
	public function setUp() {
		parent::setUp();
	}

	public function tearDown() {
		parent::tearDown();
	}

	public function test_set_scheme() {
		$home_url = get_home_url();
		$file       = trailingslashit( $home_url ) . 'test-file.jpg';
		$https_file = str_replace( 'http://', 'https://', $file );

		$this->assertEquals( $file, edd_set_requested_file_scheme( $https_file, array(), '' ) );
	}

	public function test_file_download_token() {
		$eddfile = '1:2:3:4';
		$ttl     = current_time( 'timestamp' ) + HOUR_IN_SECONDS;
		$file    = 4;

		$args = array(
			'eddfile' => $eddfile,
			'ttl'     => $ttl,
			'file'    => $file,
		);

		$token         = edd_get_download_token( add_query_arg( $args, site_url() ) );
		$args['token'] = $token;

		$url = add_query_arg( $args, site_url() );

		$this->assertTrue( edd_validate_url_token( $url ) );
	}

	public function test_file_download_token_out_of_order() {
		$eddfile = '1:2:3:4';
		$ttl     = current_time( 'timestamp' ) + HOUR_IN_SECONDS;
		$file    = 4;

		$args = array(
			'eddfile' => $eddfile,
			'ttl'     => $ttl,
			'file'    => $file,
		);

		$token         = edd_get_download_token( add_query_arg( $args, site_url() ) );

		// Re-order the arguments to verify for #8851.
		$new_args = array(
			'file'    => $file,
			'ttl'     => $ttl,
			'token'   => $token,
			'eddfile' => $eddfile,
		);

		$url = add_query_arg( $new_args, site_url() );

		$this->assertTrue( edd_validate_url_token( $url ) );
	}

	public function test_custom_parameters() {

		$payment_id = EDD_Helper_Payment::create_simple_payment();
		$payment    = edd_get_payment( $payment_id );
		$download   = EDD_Helper_Download::create_simple_download();

		add_filter( 'edd_get_download_file_url_args', function ( $args, $payment_id, $params ) {
			$args['beta'] = 1;

			return $args;
		}, 10, 3 );

		add_filter( 'edd_url_token_allowed_params', function ( $args ) {
			$args[] = 'beta';

			return $args;
		} );

		$parts = parse_url( add_query_arg( array(), edd_get_download_file_url( $payment->key, $payment->email, '', $download->ID ) ) );
		wp_parse_str( $parts['query'], $query_args );
		$url = add_query_arg( $query_args, site_url() );

		$this->assertTrue( edd_validate_url_token( $url ) );
	}
}
