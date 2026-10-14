<?php
/**
 * PayPal V3 TransactionRecorder Tests
 *
 * Characterizes TransactionRecorder::record() — the shared capture transaction
 * + note recording used by every v3 capture path.
 *
 * @package   EDD\Tests\Gateways\PayPal
 * @copyright Copyright (c) 2026, Sandhills Development, LLC
 * @license   GPL2+
 * @since     3.6.9
 */

namespace EDD\Tests\Gateways\PayPal;

use EDD\Gateways\PayPal\V3\TransactionRecorder;
use EDD\Tests\PHPUnit\EDD_UnitTestCase;

/**
 * Tests for TransactionRecorder::record().
 *
 * @group gateways
 * @group paypal
 */
class TransactionRecorderTest extends EDD_UnitTestCase {

	/**
	 * Builds a minimal EDD order.
	 *
	 * @return int
	 */
	private function make_order(): int {
		return edd_add_order(
			array(
				'status'   => 'pending',
				'gateway'  => 'paypal_commerce',
				'total'    => 10.00,
				'currency' => 'USD',
			)
		);
	}

	/**
	 * Records the capture transaction against the order.
	 */
	public function test_records_transaction() {
		$order_id = $this->make_order();

		TransactionRecorder::record( $order_id, 'CAPTURE-123' );

		$order = edd_get_order( $order_id );
		$this->assertSame( 'CAPTURE-123', $order->get_transaction_id() );
	}

	/**
	 * Adds a note carrying the PayPal transaction id.
	 */
	public function test_adds_transaction_note() {
		$order_id = $this->make_order();

		TransactionRecorder::record( $order_id, 'CAPTURE-456' );

		$notes      = edd_get_notes(
			array(
				'object_id'   => $order_id,
				'object_type' => 'order',
			)
		);
		$note_blobs = wp_list_pluck( $notes, 'content' );

		$this->assertNotEmpty(
			array_filter(
				$note_blobs,
				function ( $content ) {
					return false !== strpos( $content, 'CAPTURE-456' );
				}
			)
		);
	}

	/**
	 * An empty transaction id is a no-op (no transaction recorded).
	 */
	public function test_empty_transaction_id_is_noop() {
		$order_id = $this->make_order();

		TransactionRecorder::record( $order_id, '' );

		$order = edd_get_order( $order_id );
		$this->assertEmpty( $order->get_transaction_id() );
	}

	/**
	 * A declined capture never becomes the order's transaction, which webhooks and refunds act on.
	 */
	public function test_declined_capture_is_not_the_orders_transaction() {
		$order_id = $this->make_order();

		TransactionRecorder::record_declined( $order_id, 'CAPTURE-DECLINED' );
		TransactionRecorder::record( $order_id, 'CAPTURE-789' );

		$this->assertSame( 'CAPTURE-789', edd_get_order( $order_id )->get_transaction_id() );
	}

	/**
	 * A declined capture leaves a note naming its ID for the merchant.
	 */
	public function test_declined_capture_adds_note() {
		$order_id = $this->make_order();

		TransactionRecorder::record_declined( $order_id, 'CAPTURE-DECLINED' );

		$notes = wp_list_pluck(
			edd_get_notes(
				array(
					'object_id'   => $order_id,
					'object_type' => 'order',
				)
			),
			'content'
		);

		$this->assertNotEmpty(
			array_filter(
				$notes,
				function ( $content ) {
					return false !== strpos( $content, 'CAPTURE-DECLINED' );
				}
			)
		);
	}

	/**
	 * An empty declined capture id adds no note.
	 */
	public function test_empty_declined_capture_id_is_noop() {
		$order_id   = $this->make_order();
		$note_query = array(
			'object_id'   => $order_id,
			'object_type' => 'order',
		);
		$note_count = edd_count_notes( $note_query );

		TransactionRecorder::record_declined( $order_id, '' );

		$this->assertSame( $note_count, edd_count_notes( $note_query ) );
	}
}
