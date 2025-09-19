<?php
namespace EDD\Tests\Downloads;

use EDD\Tests\Helpers;
use EDD\Tests\PHPUnit\EDD_UnitTestCase;

class SalesEarnings extends EDD_UnitTestCase {

	protected $variable_download = null;

	protected $simple_download = null;

	public function setup(): void {
		parent::setUp();

		$this->variable_download = Helpers\EDD_Helper_Download::create_variable_download();
		$this->simple_download   = Helpers\EDD_Helper_Download::create_simple_download();
	}

	public function tearDown(): void {

		parent::tearDown();

		Helpers\EDD_Helper_Download::delete_download( $this->variable_download->ID );
		Helpers\EDD_Helper_Download::delete_download( $this->simple_download->ID );
	}

	public function test_simple_download_no_earnings_sales() {
		edd_recalculate_download_sales_earnings( $this->simple_download->ID );
		$download = edd_get_download( $this->simple_download->ID );

		$this->assertEquals( 0.00, $download->get_earnings() );
		$this->assertEquals( 0, $download->get_sales() );
		$this->assertEmpty( get_post_meta( $download->ID, '_edd_download_gross_sales', true ) );
		$this->assertEmpty( get_post_meta( $download->ID, '_edd_download_gross_earnings', true ) );
	}

	public function test_download_sales_net_gross_equal_after_one_sale() {
		$order_id = edd_add_order(
			array(
				'status'          => 'complete',
				'type'            => 'sale',
				'date_completed'  => EDD()->utils->date( 'now' )->toDateTimeString(),
				'date_refundable' => EDD()->utils->date( 'now' )->addDays( 30 )->toDateTimeString(),
				'ip'              => '10.1.1.1',
				'gateway'         => 'manual',
				'mode'            => 'live',
				'currency'        => 'USD',
				'payment_key'     => md5( 'edd' ),
				'subtotal'        => 20,
				'total'           => 20,
			)
		);

		edd_add_order_item(
			array(
				'order_id'     => $order_id,
				'product_id'   => $this->simple_download->ID,
				'product_name' => 'Simple Download',
				'status'       => 'complete',
				'amount'       => 20,
				'subtotal'     => 20,
				'total'        => 20,
				'quantity'     => 1,
			)
		);

		$download = edd_get_download( $this->simple_download->ID );
		$this->assertEquals( get_post_meta( $download->ID, '_edd_download_gross_sales', true ), $download->get_sales() );

		edd_delete_order( $order_id );
	}

	public function test_download_earnings_net_gross_equal_after_one_sale() {
		$order_id = edd_add_order(
			array(
				'status'          => 'complete',
				'type'            => 'sale',
				'date_completed'  => EDD()->utils->date( 'now' )->toDateTimeString(),
				'date_refundable' => EDD()->utils->date( 'now' )->addDays( 30 )->toDateTimeString(),
				'ip'              => '10.1.1.1',
				'gateway'         => 'manual',
				'mode'            => 'live',
				'currency'        => 'USD',
				'payment_key'     => md5( 'edd' ),
				'subtotal'        => 20,
				'total'           => 20,
			)
		);

		edd_add_order_item(
			array(
				'order_id'     => $order_id,
				'product_id'   => $this->simple_download->ID,
				'product_name' => 'Simple Download',
				'status'       => 'complete',
				'amount'       => 20,
				'subtotal'     => 20,
				'total'        => 20,
				'quantity'     => 1,
			)
		);

		$download = edd_get_download( $this->simple_download->ID );
		$this->assertEquals( (float) get_post_meta( $download->ID, '_edd_download_gross_earnings', true ), (float) $download->get_earnings() );

		edd_delete_order( $order_id );
	}

	public function test_download_earnings_net_gross_not_equal_after_full_refund() {
		$order_id = edd_add_order(
			array(
				'status'          => 'complete',
				'type'            => 'sale',
				'date_completed'  => EDD()->utils->date( 'now' )->toDateTimeString(),
				'date_refundable' => EDD()->utils->date( 'now' )->addDays( 30 )->toDateTimeString(),
				'ip'              => '10.1.1.1',
				'gateway'         => 'manual',
				'mode'            => 'live',
				'currency'        => 'USD',
				'payment_key'     => md5( 'edd' ),
				'subtotal'        => 20,
				'total'           => 20,
			)
		);

		edd_add_order_item(
			array(
				'order_id'     => $order_id,
				'product_id'   => $this->simple_download->ID,
				'product_name' => 'Simple Download',
				'status'       => 'complete',
				'amount'       => 20,
				'subtotal'     => 20,
				'total'        => 20,
				'quantity'     => 1,
			)
		);

		$refund_id = edd_refund_order( $order_id );

		$download = edd_get_download( $this->simple_download->ID );
		$this->assertEquals( 0, $download->get_earnings() );
		$this->assertNotEquals( $download->earnings, get_post_meta( $download->ID, '_edd_download_gross_earnings', true ) );

		edd_delete_order( $order_id );
		edd_delete_order( $refund_id );
	}

	public function test_download_sales_net_gross_not_equal_after_full_refund() {
		$order_id = edd_add_order(
			array(
				'status'          => 'complete',
				'type'            => 'sale',
				'date_completed'  => EDD()->utils->date( 'now' )->toDateTimeString(),
				'date_refundable' => EDD()->utils->date( 'now' )->addDays( 30 )->toDateTimeString(),
				'ip'              => '10.1.1.1',
				'gateway'         => 'manual',
				'mode'            => 'live',
				'currency'        => 'USD',
				'payment_key'     => md5( 'edd' ),
				'subtotal'        => 20,
				'total'           => 20,
			)
		);

		$order_item = edd_add_order_item(
			array(
				'order_id'     => $order_id,
				'product_id'   => $this->simple_download->ID,
				'product_name' => 'Simple Download',
				'status'       => 'complete',
				'amount'       => 20,
				'subtotal'     => 20,
				'total'        => 20,
				'quantity'     => 1,
			)
		);

		$refund_id = edd_refund_order( $order_id );

		$download = edd_get_download( $this->simple_download->ID );
		$this->assertEquals( 0, $download->get_sales() );
		$this->assertNotEquals( $download->sales, get_post_meta( $download->ID, '_edd_download_gross_sales', true ) );

		edd_delete_order( $order_id );
		edd_delete_order( $refund_id );
	}

	public function test_download_earnings_net_gross_not_equal_after_partial_refund() {
		$order_id = edd_add_order(
			array(
				'status'          => 'complete',
				'type'            => 'sale',
				'date_completed'  => EDD()->utils->date( 'now' )->toDateTimeString(),
				'date_refundable' => EDD()->utils->date( 'now' )->addDays( 30 )->toDateTimeString(),
				'ip'              => '10.1.1.1',
				'gateway'         => 'manual',
				'mode'            => 'live',
				'currency'        => 'USD',
				'payment_key'     => md5( 'edd' ),
				'subtotal'        => 20,
				'total'           => 20,
			)
		);

		$order_item_id = edd_add_order_item(
			array(
				'order_id'     => $order_id,
				'product_id'   => $this->simple_download->ID,
				'product_name' => 'Simple Download',
				'status'       => 'complete',
				'amount'       => 20,
				'subtotal'     => 20,
				'total'        => 20,
				'quantity'     => 1,
			)
		);

		$to_refund = array();
		$order     = edd_get_order( $order_id );
		foreach ( $order->items as $order_item ) {
			if ( $order_item->total > 0 ) {
				$to_refund[] = array(
					'order_item_id' => $order_item->id,
					'subtotal'      => ( $order_item->subtotal - $order_item->discount ) / 2,
					'tax'           => $order_item->tax / 2,
					'total'         => $order_item->total / 2,
				);
			}
		}

		$refund_id = edd_refund_order( $order->id, $to_refund );

		$download = edd_get_download( $this->simple_download->ID );
		$this->assertEquals( 10, $download->get_earnings() );
		$this->assertNotEquals( $download->earnings, get_post_meta( $download->ID, '_edd_download_gross_earnings', true ) );

		edd_delete_order( $order_id );
		edd_delete_order( $refund_id );
	}

	public function test_download_sales_net_gross_equal_after_partial_refund() {
		$order_id = edd_add_order(
			array(
				'status'          => 'complete',
				'type'            => 'sale',
				'date_completed'  => EDD()->utils->date( 'now' )->toDateTimeString(),
				'date_refundable' => EDD()->utils->date( 'now' )->addDays( 30 )->toDateTimeString(),
				'ip'              => '10.1.1.1',
				'gateway'         => 'manual',
				'mode'            => 'live',
				'currency'        => 'USD',
				'payment_key'     => md5( 'edd' ),
				'subtotal'        => 20,
				'total'           => 20,
			)
		);

		$order_item_id = edd_add_order_item(
			array(
				'order_id'     => $order_id,
				'product_id'   => $this->simple_download->ID,
				'product_name' => 'Simple Download',
				'status'       => 'complete',
				'amount'       => 20,
				'subtotal'     => 20,
				'total'        => 20,
				'quantity'     => 1,
			)
		);
		$order_item    = edd_get_order_item( $order_item_id );

		$to_refund = array();
		$order     = edd_get_order( $order_id );
		foreach ( $order->items as $order_item ) {
			if ( $order_item->total > 0 ) {
				$to_refund[] = array(
					'order_item_id' => $order_item->id,
					'subtotal'      => ( $order_item->subtotal - $order_item->discount ) / 2,
					'tax'           => $order_item->tax / 2,
					'total'         => $order_item->total / 2,
				);
			}
		}

		$refund_id = edd_refund_order( $order->id, $to_refund );

		$download = edd_get_download( $this->simple_download->ID );
		$this->assertEquals( $download->sales, get_post_meta( $download->ID, '_edd_download_gross_sales', true ) );

		edd_delete_order( $order_id );
		edd_delete_order( $refund_id );
	}

	public function test_download_sales_net_gross_quantities_after_partial_refund() {
		$order_id = edd_add_order(
			array(
				'status'          => 'complete',
				'type'            => 'sale',
				'date_completed'  => EDD()->utils->date( 'now' )->toDateTimeString(),
				'date_refundable' => EDD()->utils->date( 'now' )->addDays( 30 )->toDateTimeString(),
				'ip'              => '10.1.1.1',
				'gateway'         => 'manual',
				'mode'            => 'live',
				'currency'        => 'USD',
				'payment_key'     => md5( 'edd' ),
				'subtotal'        => 200,
				'total'           => 200,
			)
		);

		$order_item_id = edd_add_order_item(
			array(
				'order_id'     => $order_id,
				'product_id'   => $this->simple_download->ID,
				'product_name' => 'Simple Download',
				'status'       => 'complete',
				'amount'       => 20,
				'subtotal'     => 200,
				'total'        => 200,
				'quantity'     => 10,
			)
		);

		$to_refund = array();
		$order     = edd_get_order( $order_id );
		foreach ( $order->items as $order_item ) {
			if ( $order_item->total > 0 ) {
				$to_refund[] = array(
					'order_item_id' => $order_item->id,
					'subtotal'      => ( $order_item->subtotal - $order_item->discount ) / 2,
					'tax'           => $order_item->tax / 2,
					'total'         => $order_item->total / 2,
					'quantity'      => $order_item->quantity / 2,
				);
			}
		}

		$refund_id = edd_refund_order( $order->id, $to_refund );

		$download = edd_get_download( $this->simple_download->ID );
		$this->assertEquals( 5, $download->get_sales() );
		$this->assertNotEquals( $download->sales, get_post_meta( $download->ID, '_edd_download_gross_sales', true ) );

		edd_delete_order( $order_id );
		edd_delete_order( $refund_id );
	}

	public function test_download_sales_gross_less_net_equals_discount() {
		$order_id = edd_add_order(
			array(
				'status'          => 'complete',
				'type'            => 'sale',
				'date_completed'  => EDD()->utils->date( 'now' )->toDateTimeString(),
				'date_refundable' => EDD()->utils->date( 'now' )->addDays( 30 )->toDateTimeString(),
				'ip'              => '10.1.1.1',
				'gateway'         => 'manual',
				'mode'            => 'live',
				'currency'        => 'USD',
				'payment_key'     => md5( 'edd' ),
				'subtotal'        => 20,
				'total'           => 15,
			)
		);

		$order_item_id = edd_add_order_item(
			array(
				'order_id'     => $order_id,
				'product_id'   => $this->simple_download->ID,
				'product_name' => 'Simple Download',
				'status'       => 'complete',
				'amount'       => 20,
				'subtotal'     => 20,
				'total'        => 15,
				'quantity'     => 1,
				'discount'     => 5,
			)
		);

		$download = edd_get_download( $this->simple_download->ID );
		$this->assertEquals( 5, get_post_meta( $download->ID, '_edd_download_gross_earnings', true ) - $download->earnings );

		edd_delete_order( $order_id );
	}

	public function test_download_earnings_gross_equals_net_with_positive_fee() {
		$order_id = edd_add_order(
			array(
				'status'          => 'complete',
				'type'            => 'sale',
				'date_completed'  => EDD()->utils->date( 'now' )->toDateTimeString(),
				'date_refundable' => EDD()->utils->date( 'now' )->addDays( 30 )->toDateTimeString(),
				'ip'              => '10.1.1.1',
				'gateway'         => 'manual',
				'mode'            => 'live',
				'currency'        => 'USD',
				'payment_key'     => md5( 'edd' ),
				'subtotal'        => 20,
				'total'           => 25,
			)
		);

		$order_item_id = edd_add_order_item(
			array(
				'order_id'     => $order_id,
				'product_id'   => $this->simple_download->ID,
				'product_name' => 'Simple Download',
				'status'       => 'complete',
				'amount'       => 20,
				'subtotal'     => 20,
				'total'        => 20,
				'quantity'     => 1,
			)
		);

		$order_item_adjustment = edd_add_order_adjustment(
			array(
				'object_id'   => $order_item_id,
				'object_type' => 'order_item',
				'subtotal'    => 5,
				'total'       => 5,
			)
		);

		$download = edd_get_download( $this->simple_download->ID );
		$this->assertEquals( (float) $download->earnings, (float) get_post_meta( $download->ID, '_edd_download_gross_earnings', true ) );

		edd_delete_order( $order_id );
	}

	public function test_download_earnings_gross_minus_net_equals_negative_fee() {
		$order_id = edd_add_order(
			array(
				'status'          => 'complete',
				'type'            => 'sale',
				'date_completed'  => EDD()->utils->date( 'now' )->toDateTimeString(),
				'date_refundable' => EDD()->utils->date( 'now' )->addDays( 30 )->toDateTimeString(),
				'ip'              => '10.1.1.1',
				'gateway'         => 'manual',
				'mode'            => 'live',
				'currency'        => 'USD',
				'payment_key'     => md5( 'edd' ),
				'subtotal'        => 20,
				'total'           => 15,
			)
		);

		$order_item_id = edd_add_order_item(
			array(
				'order_id'     => $order_id,
				'product_id'   => $this->simple_download->ID,
				'product_name' => 'Simple Download',
				'status'       => 'complete',
				'amount'       => 20,
				'subtotal'     => 20,
				'total'        => 20,
				'quantity'     => 1,
			)
		);

		$order_item_adjustment = edd_add_order_adjustment(
			array(
				'object_id'   => $order_item_id,
				'object_type' => 'order_item',
				'subtotal'    => -5.00,
				'total'       => -5.00,
			)
		);

		$download = edd_get_download( $this->simple_download->ID );
		$this->assertEquals( 5, get_post_meta( $download->ID, '_edd_download_gross_earnings', true ) - $download->earnings );

		edd_delete_order( $order_id );
	}

	/**
	 * Test for the reported bug: Models\Download calculations inconsistent after refunds.
	 *
	 * Scenario: Sell a product twice, refund one order, verify numbers are accurate.
	 * Expected: Sales and earnings should correctly account for refunds.
	 *
	 * @since 3.5.2
	 */
	public function test_download_model_sales_earnings_consistency_after_refund() {
		// Sell the product twice
		$order1_id = edd_add_order(
			array(
				'status'         => 'complete',
				'type'           => 'sale',
				'date_completed' => EDD()->utils->date( 'now' )->toDateTimeString(),
				'ip'             => '10.1.1.1',
				'gateway'        => 'manual',
				'mode'           => 'live',
				'currency'       => 'USD',
				'payment_key'    => md5( 'edd-test-1' ),
				'subtotal'       => 20,
				'total'          => 20,
			)
		);

		edd_add_order_item(
			array(
				'order_id'     => $order1_id,
				'product_id'   => $this->simple_download->ID,
				'product_name' => 'Simple Download',
				'status'       => 'complete',
				'amount'       => 20,
				'subtotal'     => 20,
				'total'        => 20,
				'quantity'     => 1,
			)
		);

		$order2_id = edd_add_order(
			array(
				'status'         => 'complete',
				'type'           => 'sale',
				'date_completed' => EDD()->utils->date( 'now' )->toDateTimeString(),
				'ip'             => '10.1.1.2',
				'gateway'        => 'manual',
				'mode'           => 'live',
				'currency'       => 'USD',
				'payment_key'    => md5( 'edd-test-2' ),
				'subtotal'       => 20,
				'total'          => 20,
			)
		);

		edd_add_order_item(
			array(
				'order_id'     => $order2_id,
				'product_id'   => $this->simple_download->ID,
				'product_name' => 'Simple Download',
				'status'       => 'complete',
				'amount'       => 20,
				'subtotal'     => 20,
				'total'        => 20,
				'quantity'     => 1,
			)
		);

		// Verify initial state: 2 sales, $40 earnings
		$download_model = new \EDD\Models\Download( $this->simple_download->ID );
		$this->assertEquals( 2, $download_model->get_net_sales() );
		$this->assertEquals( 2, $download_model->get_gross_sales() );
		$this->assertEquals( 40.00, $download_model->get_net_earnings() );
		$this->assertEquals( 40.00, $download_model->get_gross_earnings() );

		// Refund one order
		$refund_id = edd_refund_order( $order1_id );

		// Verify after refund: 1 net sale, 2 gross sales, $20 net earnings, $40 gross earnings
		$this->assertEquals( 1, $download_model->get_net_sales() );
		$this->assertEquals( 2, $download_model->get_gross_sales() );
		$this->assertEquals( 20.00, $download_model->get_net_earnings() );
		$this->assertEquals( 40.00, $download_model->get_gross_earnings() );

		// Clean up
		edd_delete_order( $order1_id );
		edd_delete_order( $order2_id );
		edd_delete_order( $refund_id );
	}

	/**
	 * Test Models\Download calculations with multiple quantities and partial refunds.
	 *
	 * @since 3.5.2
	 */
	public function test_download_model_multiple_quantities_partial_refund() {
		// Create order with 5 quantity
		$order_id = edd_add_order(
			array(
				'status'         => 'complete',
				'type'           => 'sale',
				'date_completed' => EDD()->utils->date( 'now' )->toDateTimeString(),
				'ip'             => '10.1.1.1',
				'gateway'        => 'manual',
				'mode'           => 'live',
				'currency'       => 'USD',
				'payment_key'    => md5( 'edd-test-multi' ),
				'subtotal'       => 100,
				'total'          => 100,
			)
		);

		edd_add_order_item(
			array(
				'order_id'     => $order_id,
				'product_id'   => $this->simple_download->ID,
				'product_name' => 'Simple Download',
				'status'       => 'complete',
				'amount'       => 20,
				'subtotal'     => 100,
				'total'        => 100,
				'quantity'     => 5,
			)
		);

		// Verify initial state
		$download_model = new \EDD\Models\Download( $this->simple_download->ID );
		$this->assertEquals( 5, $download_model->get_net_sales() );
		$this->assertEquals( 5, $download_model->get_gross_sales() );

		// Partial refund of 2 quantity
		$order = edd_get_order( $order_id );
		$to_refund = array();
		foreach ( $order->items as $order_item ) {
			if ( $order_item->total > 0 ) {
				$to_refund[] = array(
					'order_item_id' => $order_item->id,
					'subtotal'      => ( $order_item->subtotal - $order_item->discount ) * 0.4, // 2/5 = 40%
					'tax'           => $order_item->tax * 0.4,
					'total'         => $order_item->total * 0.4,
					'quantity'      => 2,
				);
			}
		}

		$refund_id = edd_refund_order( $order_id, $to_refund );

		// Verify after partial refund: 3 net sales (5 - 2), 5 gross sales
		$this->assertEquals( 3, $download_model->get_net_sales() );
		$this->assertEquals( 5, $download_model->get_gross_sales() );

		// Clean up
		edd_delete_order( $order_id );
		edd_delete_order( $refund_id );
	}
}
