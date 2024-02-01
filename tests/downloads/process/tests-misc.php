<?php
namespace EDD\Tests\Downloads\Process;

use EDD\Tests\Helpers;

/**
 * Download Process Tests for completed orders
 *
 * Tests for file downloads and downloading permissions for completed orders.
 *
 * @group edd_downloads
 */
class Misc extends Helpers\Process_Download {
	public function test_set_scheme() {
		$home_url = get_home_url();
		$file       = trailingslashit( $home_url ) . 'test-file.jpg';
		$https_file = str_replace( 'http://', 'https://', $file );

		$this->assertEquals( $file, edd_set_requested_file_scheme( $https_file, array(), '' ) );
	}

	/**
	 * If no order exists with the provided ID, files cannot be downloaded
	 */
	public function test_non_existent_order_number_should_return_false() {
		// Generate a random order ID until we get one that doesn't exist.
		$order_id = 12345;
		do {
			$order_id++;
			$order = edd_get_order( $order_id );
		} while ( $order instanceof \EDD\Orders\Order );

		$this->assertFalse( edd_order_grants_access_to_download_files( array(
			'order_id'   => $order_id,
			'product_id' => 1
		) ) );
	}

	/**
	 * If specifying a product ID that doesn't exist in the order, files cannot be downloaded
	 */
	public function test_order_with_non_existent_product_id_should_return_false() {
		$this->assertFalse( edd_order_grants_access_to_download_files( array(
			'order_id'   => self::$order->id,
			'product_id' => 9999
		) ) );
	}

	/**
	 * Test the file download token.
	 */
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

	/**
	 * Test the file download toekn whe items are out of order.
	 */
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

	/**
	 * Test custom parameters being including in the download URL.
	 */
	public function test_custom_parameters() {

		$payment_id = Helpers\EDD_Helper_Payment::create_simple_payment();
		$order      = edd_get_order( $payment_id );
		$download   = Helpers\EDD_Helper_Download::create_simple_download();

		add_filter( 'edd_get_download_file_url_args', function ( $args, $payment_id, $params ) {
			$args['beta'] = 1;

			return $args;
		}, 10, 3 );

		add_filter( 'edd_url_token_allowed_params', function ( $args ) {
			$args[] = 'beta';

			return $args;
		} );

		$parts = parse_url( add_query_arg( array(), edd_get_download_file_url( $order, $order->email, '', $download->ID ) ) );
		wp_parse_str( $parts['query'], $query_args );
		$url = add_query_arg( $query_args, site_url() );

		$this->assertTrue( edd_validate_url_token( $url ) );
	}

	/**
	 * Test that a completed order but an expired link cannot download the file.
	 */
	public function test_order_cannot_download_expired_link() {
		// Add our hook to 'fake' files to download.
		Helpers\EDD_Helper_Download::add_download_files();

		$args = array(
			'download' => self::$order->items[0]->product_id,
			'email'    => self::$order->email,
			'expire'   => current_time( 'timestamp' ) - HOUR_IN_SECONDS,
			'file_key' => 0,
			'price_id' => false,
			'key'      => self::$order->payment_key,
			'eddfile'  => sprintf( '%d:%d:%d', self::$order->id, 0, rawurlencode( '' ) ),
			'ttl'      => '',
		);
		$args['token'] = edd_get_download_token( add_query_arg( array_filter( $args ), untrailingslashit( site_url() ) ) );

		$file_download_url = edd_get_download_file_url( self::$order, self::$order->email, 0, self::$order->items[0]->product_id );
		$this->go_to( $file_download_url );
		$process_signed_url = edd_process_signed_download_url( $args );

		$this->assertTrue( $process_signed_url['has_access'] );

		// Remove our hook to 'fake' files to download.
		Helpers\EDD_Helper_Download::remove_download_files();
	}
}
