<?php
namespace EDD\Tests\Downloads\Process;

use EDD\Tests\Helpers;

/**
 * Download Process Tests for refuded orders
 *
 * Tests for file downloads and downloading permissions for refunded orders.
 *
 * @group edd_downloads
 */
class Refunded extends Helpers\Process_Download {

	/**
	 * The refunded order used for testing.
	 *
	 * @var \EDD_Order
	 */
	protected static $refunded_order;

	/**
	 * Sets up fixtures once
	 */
	public static function setUpBeforeClass(): void {
		parent::setUpBeforeClass();

		// Refund the entire order.
		$refund_id = edd_refund_order( self::$order->id );
		self::$refunded_order = edd_get_order( $refund_id );

		// Fetch the order again, so we have the latest data.
		self::$order = edd_get_order( self::$order->id );
	}

	/**
	 * If an order has been fully refunded, files cannot be downloaded for any of the items.
	 */
	public function test_fully_refunded_order_should_return_false() {
		foreach ( self::$order->items as $item ) {
			$order_item_args = array(
				'order_id'   => self::$order->id,
				'product_id' => $item->product_id,
			);

			if ( ! is_null( $item->price_id ) ) {
				$args['price_id'] = $item->price_id;
			}

			$this->assertFalse( edd_order_grants_access_to_download_files( $order_item_args ) );
		}
	}

	/**
	 * Test that a refunded order cannot download the file.
	 */
	public function test_order_cannot_download() {
		// Add our hook to 'fake' files to download.
		Helpers\EDD_Helper_Download::add_download_files();

		$args = array(
			'download' => self::$order->items[0]->product_id,
			'email'    => self::$order->email,
			'expire'   => current_time( 'timestamp' ) + HOUR_IN_SECONDS,
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

		$this->assertFalse( $process_signed_url['has_access'] );

		// Remove our hook to 'fake' files to download.
		Helpers\EDD_Helper_Download::remove_download_files();
	}

	/**
	 * Test that a refunded order cannot download the file with a price ID.
	 */
	public function test_order_cannot_download_with_price_id_condition_all() {
		// Add our hook to 'fake' files to download.
		Helpers\EDD_Helper_Download::add_download_files();

		$args = array(
			'download' => self::$order->items[1]->product_id,
			'email'    => self::$order->email,
			'expire'   => current_time( 'timestamp' ) + HOUR_IN_SECONDS,
			'file_key' => 0,
			'price_id' => self::$order->items[1]->price_id,
			'key'      => self::$order->payment_key,
			'eddfile'  => sprintf( '%d:%d:%d', self::$order->id, 0, rawurlencode( '' ) ),
			'ttl'      => '',
		);
		$args['token'] = edd_get_download_token( add_query_arg( array_filter( $args ), untrailingslashit( site_url() ) ) );

		$file_download_url = edd_get_download_file_url( self::$order, self::$order->email, 0, self::$order->items[1]->product_id, self::$order->items[1]->price_id );
		$this->go_to( $file_download_url );
		$process_signed_url = edd_process_signed_download_url( $args );

		$this->assertFalse( $process_signed_url['has_access'] );

		// Remove our hook to 'fake' files to download.
		Helpers\EDD_Helper_Download::remove_download_files();
	}

	/**
	 * Test that a refunded order cannot download the file with a price ID.
	 */
	public function test_order_cannot_download_with_purchased_bundle() {
		// Add our hook to 'fake' files to download.
		Helpers\EDD_Helper_Download::add_download_files();

		$args = array(
			'download' => self::$bundled_download->get_bundled_downloads()[0],
			'email'    => self::$order->email,
			'expire'   => current_time( 'timestamp' ) + HOUR_IN_SECONDS,
			'file_key' => 0,
			'key'      => self::$order->payment_key,
			'eddfile'  => sprintf( '%d:%d:%d', self::$order->id, 0, rawurlencode( '' ) ),
			'ttl'      => '',
		);
		$args['token'] = edd_get_download_token( add_query_arg( array_filter( $args ), untrailingslashit( site_url() ) ) );

		$file_download_url = edd_get_download_file_url( self::$order, self::$order->email, 0, self::$bundled_download->get_bundled_downloads()[0] );
		$this->go_to( $file_download_url );
		$process_signed_url = edd_process_signed_download_url( $args );

		$this->assertFalse( $process_signed_url['has_access'] );

		// Remove our hook to 'fake' files to download.
		Helpers\EDD_Helper_Download::remove_download_files();
	}

	public function test_cannot_use_refund_order_to_download() {
		// Add our hook to 'fake' files to download.
		Helpers\EDD_Helper_Download::add_download_files();

		$args = array(
			'download' => self::$refunded_order->items[0]->product_id,
			'email'    => self::$refunded_order->email,
			'expire'   => current_time( 'timestamp' ) + HOUR_IN_SECONDS,
			'file_key' => 0,
			'price_id' => false,
			'key'      => self::$refunded_order->payment_key,
			'eddfile'  => sprintf( '%d:%d:%d', self::$refunded_order->id, 0, rawurlencode( '' ) ),
			'ttl'      => '',
		);
		$args['token'] = edd_get_download_token( add_query_arg( array_filter( $args ), untrailingslashit( site_url() ) ) );

		$file_download_url = edd_get_download_file_url( self::$refunded_order, self::$refunded_order->email, 0, self::$refunded_order->items[0]->product_id );
		$this->go_to( $file_download_url );
		$process_signed_url = edd_process_signed_download_url( $args );

		$this->assertFalse( $process_signed_url['has_access'] );

		// Remove our hook to 'fake' files to download.
		Helpers\EDD_Helper_Download::remove_download_files();
	}
}
