<?php
namespace EDD\Tests\Downloads\Process;

use EDD\Tests\Helpers;

/**
 * Download Process Tests for partially refunded orders
 *
 * Tests for file downloads and downloading permissions for partially refunded orders.
 *
 * @group edd_downloads
 */
class Partially_Refunded extends Helpers\Process_Download {

	/**
	 * Sets up fixtures once
	 */
	public static function setUpBeforeClass(): void {
		parent::setUpBeforeClass();

		// Refund a single item from the order, so that the order is partially refunded.
		edd_refund_order(
			self::$order->id,
			array(
				array(
					'order_item_id' => self::$order->items[0]->id,
					'subtotal'      => self::$order->items[0]->subtotal - self::$order->items[0]->discount,
					'tax'           => self::$order->items[0]->tax,
				)
			)
		);

		// Fetch the order again, so we have the latest data.
		self::$order = edd_get_order( self::$order->id );
	}

	/**
	 * If an order item has been refunded, the associated files can no longer be downloaded.
	 */
	public function test_refunded_item_in_partially_refunded_order_should_return_false() {
		$this->assertFalse( edd_order_grants_access_to_download_files( array(
			'order_id'   => self::$order->id,
			'product_id' => self::$order->items[0]->product_id
		) ) );
	}

	/**
	 * If an order has been partially refunded, the item that's still `complete` can still be downloaded.
	 */
	public function test_complete_item_in_partially_refunded_order_should_return_true() {
		$this->assertTrue( edd_order_grants_access_to_download_files( array(
			'order_id'   => self::$order->id,
			'product_id' => self::$order->items[1]->product_id,
			'price_id'   => self::$order->items[1]->price_id,
		) ) );
	}

	/**
	 * Test that a partially refunded order cannot download the file for the refunded item.
	 */
	public function test_order_cannot_download_refunded_item() {
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
	 * Test that a partially refunded order cannot download the file with a price ID.
	 */
	public function test_order_can_download_non_refunded_item() {
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

		$this->assertTrue( $process_signed_url['has_access'] );

		// Remove our hook to 'fake' files to download.
		Helpers\EDD_Helper_Download::remove_download_files();
	}
}
