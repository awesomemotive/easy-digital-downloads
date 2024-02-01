<?php
namespace EDD\Tests\Fees;

use EDD\Tests\PHPUnit\EDD_UnitTestCase;

/**
 * @group edd_fees
 */
class Orders extends EDD_UnitTestCase {

	private static $download;

	public static function setUpBeforeClass(): void {
		parent::setUpBeforeClass();
		edd_update_option( 'enable_taxes', true );
		edd_add_tax_rate(
			array(
				'scope'       => 'region',
				'name'        => 'US',
				'description' => 'TN',
				'amount'      => 10,
			)
		);

		self::$download = \EDD\Tests\Helpers\EDD_Helper_Download::create_simple_download();
	}

	public static function tearDownAfterClass(): void {
		parent::tearDownAfterClass();
		edd_update_option( 'enable_taxes', false );
	}

	public function test_fee_with_no_tax_has_no_tax() {
		$fee = $this->build_order_and_get_fee( true );

		$this->assertEquals( 0, $fee->tax );
	}

	public function test_fee_with_no_tax_subtotal_equals_total() {
		$fee = $this->build_order_and_get_fee( true );

		$this->assertEquals( $fee->subtotal, $fee->total );
	}

	public function test_fee_with_tax_has_tax() {
		$fee = $this->build_order_and_get_fee( false );

		$this->assertEquals( 0.2, $fee->tax );
	}

	public function test_fee_with_tax_total_includes_tax() {
		$fee = $this->build_order_and_get_fee( false );

		$this->assertEquals( 2.2, $fee->total );
	}

	public function test_fee_with_custom_data_gets_saved() {
		// Replace the fees.
		$fees       = array(
			'custom_fee_data' => array(
				'id'          => 'custom_fee_data',
				'label'       => 'Custom Fee With Extra Data',
				'amount'      => 10,
				'no_tax'      => true,
				'type'        => 'fee',
				'download_id' => self::$download->ID,
				'price_id'    => null,
				'points_type' => 'custom',
				'points'      => 5,
				'money'       => edd_format_amount( 10 ),
			),
		);
		$order_data = $this->get_order_data( false, $fees );

		$order_id = edd_build_order( $order_data );

		$order_adjustments = edd_get_order_adjustments(
			array(
				'object_id' => $order_id,
			)
		);

		$adjustment = reset( $order_adjustments );

		$this->assertSame( 'custom', edd_get_order_adjustment_meta( $adjustment->id, 'points_type', true ) );
	}

	public function test_negative_fee_no_tax_is_true() {
		// Replace the fees.
		$fees       = array(
			'discount_pro_123' => array(
				'id'          => 'discount_pro_123',
				'label'       => 'Something from Discounts Pro',
				'amount'      => -10,
				'type'        => 'fee',
				'download_id' => self::$download->ID,
				'price_id'    => null,
				'no_tax'      => false, // This is incorrect and should be overridden.
			),
		);
		$order_data = $this->get_order_data( false, $fees );
		$order_id   = edd_build_order( $order_data );
		$payment    = edd_get_payment( $order_id );
		$fee        = $payment->fees[0];

		$this->assertTrue( $fee['no_tax'] );
	}

	private function build_order_and_get_fee( $no_tax = false ) {
		$order_id = edd_build_order( $this->get_order_data( $no_tax ) );

		$order_adjustments = edd_get_order_adjustments(
			array(
				'object_id' => $order_id,
			)
		);

		return reset( $order_adjustments );
	}

	private function get_order_data( $no_tax = false, $fees = array() ) {
		if ( empty( $fees ) ) {
			$fees = array(
				'simple_shipping_1234' => array(
					'amount'      => 2.00,
					'label'       => 'Simple Download Fee',
					'no_tax'      => $no_tax,
					'type'        => 'fee',
					'download_id' => self::$download->ID,
				),
			);
		}

		return array(
			'price'        => 24,
			'date'         => '2023-06-12 13:41:53',
			'user_email'   => 'test@edd.local',
			'purchase_key' => '5c73adfa5ebb0bc47de72a82df7f0ae9',
			'currency'     => 'USD',
			'downloads'    => array(
				array(
					'id'       => self::$download->ID,
					'options'  => array(),
					'quantity' => 1,
				),
			),
			'user_info'    => array(
				'id'         => 1,
				'email'      => 'test@edd.local',
				'first_name' => 'John',
				'last_name'  => 'Doe',
				'discount'   => 'none',
				'address'    => array(
					'line1'   => '1234 Main Street',
					'city'    => 'Chattanooga',
					'state'   => 'TN',
					'country' => 'US',
					'zip'     => '37403',
				),
			),
			'cart_details' => array(
				array(
					'name'        => edd_get_download_name( self::$download->ID ),
					'id'          => self::$download->ID,
					'item_number' => array(
						'id'       => self::$download->ID,
						'options'  => array(),
						'quantity' => 1,
					),
					'item_price'  => 20,
					'quantity'    => 1,
					'discount'    => 0,
					'subtotal'    => 22,
					'tax'         => 0.10,
					'fees'        => $fees,
					'price'       => 24,
				),
			),
		);
	}
}
