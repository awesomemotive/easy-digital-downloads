<?php
namespace EDD\Orders;

/**
 * Order Adjustment Tests.
 *
 * @group edd_orders
 * @group edd_order_adjustments
 * @group database
 *
 * @coversDefaultClass \EDD\Orders\Order_Item
 */
class Order_Adjustment_Tests extends \EDD_UnitTestCase {

	/**
	 * Order adjustments fixture.
	 *
	 * @var array
	 * @static
	 */
	protected static $order_adjustments = array();

	/**
	 * Set up fixtures once.
	 */
	public static function wpSetUpBeforeClass() {
		self::$order_adjustments = parent::edd()->order_adjustment->create_many( 5 );
	}

	/**
	 * @covers ::edd_update_order_adjustment()
	 */
	public function test_update_should_return_true() {
		$success = edd_update_order_adjustment( self::$order_adjustments[0], array(
			'type' => 'Adjustment Type ' . \WP_UnitTest_Generator_Sequence::$incr,
		) );

		$this->assertSame( 1, $success );
	}

	/**
	 * @covers ::edd_update_order_adjustment()
	 */
	public function test_order_object_after_update_should_return_true() {
		edd_update_order_adjustment( self::$order_adjustments[0], array(
			'description' => 'Adjustment Description 999',
		) );

		$order_adjustment = edd_get_order_adjustment( self::$order_adjustments[0] );

		$this->assertSame( 'Adjustment Description 999', $order_adjustment->description );
	}

	/**
	 * @covers ::edd_update_order_adjustment
	 */
	public function test_update_without_id_should_fail() {
		$success = edd_update_order_adjustment( null, array(
			'description' => 'Adjustment Description 999',
		) );

		$this->assertFalse( $success );
	}

	/**
	 * @covers ::edd_delete_order_adjustment
	 */
	public function test_delete_should_return_true() {
		$success = edd_delete_order_adjustment( self::$order_adjustments[0] );

		$this->assertSame( 1, $success );
	}

	/**
	 * @covers ::edd_delete_order_adjustment
	 */
	public function test_delete_without_id_should_fail() {
		$success = edd_delete_order_adjustment( '' );

		$this->assertFalse( $success );
	}

	/**
	 * @covers ::edd_get_order_adjustments
	 */
	public function test_get_order_adjustments_with_number_should_return_true() {
		$order_adjustments = edd_get_order_adjustments( array(
			'number' => 10,
		) );

		$this->assertCount( 5, $order_adjustments );
	}

	/**
	 * @covers ::edd_get_order_adjustments
	 */
	public function test_get_order_adjustments_with_offset_should_return_true() {
		$order_adjustments = edd_get_order_adjustments( array(
			'number' => 10,
			'offset' => 4,
		) );

		$this->assertCount( 1, $order_adjustments );
	}

	/**
	 * @covers ::edd_get_order_adjustments
	 */
	public function test_get_order_adjustments_with_orderby_id_and_order_asc_should_return_true() {
		$order_adjustments = edd_get_order_adjustments( array(
			'orderby' => 'id',
			'order'   => 'asc',
		) );

		$this->assertTrue( $order_adjustments[0]->id < $order_adjustments[1]->id );
	}

	/**
	 * @covers ::edd_get_order_adjustments
	 */
	public function test_get_order_adjustments_with_orderby_id_and_order_desc_should_return_true() {
		$order_adjustments = edd_get_order_adjustments( array(
			'orderby' => 'id',
			'order'   => 'desc',
		) );

		$this->assertTrue( $order_adjustments[0]->id > $order_adjustments[1]->id );
	}

	/**
	 * @covers ::edd_get_order_adjustments
	 */
	public function test_get_order_adjustments_with_orderby_object_id_and_order_asc_should_return_true() {
		$order_adjustments = edd_get_order_adjustments( array(
			'orderby' => 'object_id',
			'order'   => 'asc',
		) );

		$this->assertTrue( $order_adjustments[0]->object_id < $order_adjustments[1]->object_id );
	}

	/**
	 * @covers ::edd_get_order_adjustments
	 */
	public function test_get_order_adjustments_with_orderby_object_id_and_order_desc_should_return_true() {
		$order_adjustments = edd_get_order_adjustments( array(
			'orderby' => 'object_id',
			'order'   => 'desc',
		) );

		$this->assertTrue( $order_adjustments[0]->object_id > $order_adjustments[1]->object_id );
	}

	/**
	 * @covers ::edd_get_order_adjustments
	 */
	public function test_get_order_adjustments_with_object_id_should_return_1() {
		$order_adjustments = edd_get_order_adjustments( array(
			'object_id' => \WP_UnitTest_Generator_Sequence::$incr,
		) );

		$this->assertCount( 1, $order_adjustments );
	}

	/**
	 * @covers ::edd_get_order_adjustments
	 */
	public function test_get_order_adjustments_with_object_id__in_should_return_1() {
		$order_adjustments = edd_get_order_adjustments( array(
			'object_id__in' => array(
				\WP_UnitTest_Generator_Sequence::$incr,
			),
		) );

		$this->assertCount( 1, $order_adjustments );
	}

	/**
	 * @covers ::edd_get_order_adjustments
	 */
	public function test_get_order_adjustments_with_object_id__not_in_should_return_5() {
		$order_adjustments = edd_get_order_adjustments( array(
			'object_id__not_in' => array(
				999,
			),
		) );

		$this->assertCount( 5, $order_adjustments );
	}

	/**
	 * @covers ::edd_get_order_adjustments
	 */
	public function test_get_order_adjustments_with_object_type_should_return_5() {
		$order_adjustments = edd_get_order_adjustments( array(
			'object_type' => 'order',
		) );

		$this->assertCount( 5, $order_adjustments );
	}

	/**
	 * @covers ::edd_get_order_adjustments
	 */
	public function test_get_order_adjustments_with_orderby_type_id_and_order_asc_should_return_true() {
		$order_adjustments = edd_get_order_adjustments( array(
			'orderby' => 'type_id',
			'order'   => 'asc',
		) );

		$this->assertTrue( $order_adjustments[0]->type_id < $order_adjustments[1]->type_id );
	}

	/**
	 * @covers ::edd_get_order_adjustments
	 */
	public function test_get_order_adjustments_with_orderby_type_id_and_order_desc_should_return_true() {
		$order_adjustments = edd_get_order_adjustments( array(
			'orderby' => 'type_id',
			'order'   => 'desc',
		) );

		$this->assertTrue( $order_adjustments[0]->type_id > $order_adjustments[1]->type_id );
	}

	/**
	 * @covers ::edd_get_order_adjustments
	 */
	public function test_get_order_adjustments_with_type_id_should_return_1() {
		$order_adjustments = edd_get_order_adjustments( array(
			'type_id' => \WP_UnitTest_Generator_Sequence::$incr,
		) );

		$this->assertCount( 1, $order_adjustments );
	}

	/**
	 * @covers ::edd_get_order_adjustments
	 */
	public function test_get_order_adjustments_with_type_id__in_should_return_1() {
		$order_adjustments = edd_get_order_adjustments( array(
			'type_id__in' => array(
				\WP_UnitTest_Generator_Sequence::$incr,
			),
		) );

		$this->assertCount( 1, $order_adjustments );
	}

	/**
	 * @covers ::edd_get_order_adjustments
	 */
	public function test_get_order_adjustments_with_type_id__not_in_should_return_5() {
		$order_adjustments = edd_get_order_adjustments( array(
			'type_id__not_in' => array(
				999,
			),
		) );

		$this->assertCount( 5, $order_adjustments );
	}

	/**
	 * @covers ::edd_get_order_adjustments
	 */
	public function test_get_order_adjustments_with_orderby_type_and_order_asc_should_return_true() {
		$order_adjustments = edd_get_order_adjustments( array(
			'orderby' => 'type',
			'order'   => 'asc',
		) );

		$this->assertTrue( $order_adjustments[0]->type < $order_adjustments[1]->type );
	}

	/**
	 * @covers ::edd_get_order_adjustments
	 */
	public function test_get_order_adjustments_with_orderby_type_and_order_desc_should_return_true() {
		$order_adjustments = edd_get_order_adjustments( array(
			'orderby' => 'type',
			'order'   => 'desc',
		) );

		$this->assertTrue( $order_adjustments[0]->type > $order_adjustments[1]->type );
	}

	/**
	 * @covers ::edd_get_order_adjustments
	 */
	public function test_get_order_adjustments_with_type_should_return_1() {
		$order_adjustments = edd_get_order_adjustments( array(
			'type' => 'Adjustment Type ' . \WP_UnitTest_Generator_Sequence::$incr,
		) );

		$this->assertCount( 1, $order_adjustments );
	}

	/**
	 * @covers ::edd_get_order_adjustments
	 */
	public function test_get_order_adjustments_with_type__in_should_return_1() {
		$order_adjustments = edd_get_order_adjustments( array(
			'type__in' => array(
				'Adjustment Type ' . \WP_UnitTest_Generator_Sequence::$incr,
			),
		) );

		$this->assertCount( 1, $order_adjustments );
	}

	/**
	 * @covers ::edd_get_order_adjustments
	 */
	public function test_get_order_adjustments_with_type__not_in_should_return_5() {
		$order_adjustments = edd_get_order_adjustments( array(
			'type__not_in' => array(
				'Adjustment Type ' . 999,
			),
		) );

		$this->assertCount( 5, $order_adjustments );
	}

	/**
	 * @covers ::edd_get_order_adjustments
	 */
	public function test_get_order_adjustments_with_orderby_description_and_order_asc_should_return_true() {
		$order_adjustments = edd_get_order_adjustments( array(
			'orderby' => 'description',
			'order'   => 'asc',
		) );

		$this->assertTrue( $order_adjustments[0]->description < $order_adjustments[1]->description );
	}

	/**
	 * @covers ::edd_get_order_adjustments
	 */
	public function test_get_order_adjustments_with_orderby_description_and_order_desc_should_return_true() {
		$order_adjustments = edd_get_order_adjustments( array(
			'orderby' => 'description',
			'order'   => 'desc',
		) );

		$this->assertTrue( $order_adjustments[0]->description > $order_adjustments[1]->description );
	}

	/**
	 * @covers ::edd_get_order_adjustments
	 */
	public function test_get_order_adjustments_with_description_should_return_1() {
		$order_adjustments = edd_get_order_adjustments( array(
			'description' => 'Adjustment Description ' . \WP_UnitTest_Generator_Sequence::$incr,
		) );

		$this->assertCount( 1, $order_adjustments );
	}

	/**
	 * @covers ::edd_get_order_adjustments
	 */
	public function test_get_order_adjustments_with_description__in_should_return_1() {
		$order_adjustments = edd_get_order_adjustments( array(
			'description__in' => array(
				'Adjustment Description ' . \WP_UnitTest_Generator_Sequence::$incr,
			),
		) );

		$this->assertCount( 1, $order_adjustments );
	}

	/**
	 * @covers ::edd_get_order_adjustments
	 */
	public function test_get_order_adjustments_with_description__not_in_should_return_5() {
		$order_adjustments = edd_get_order_adjustments( array(
			'description__not_in' => array(
				'Adjustment Description ' . 999,
			),
		) );

		$this->assertCount( 5, $order_adjustments );
	}

	/**
	 * @covers ::edd_get_order_adjustments
	 */
	public function test_get_order_adjustments_with_orderby_subtotal_and_order_asc_should_return_true() {
		$order_adjustments = edd_get_order_adjustments( array(
			'orderby' => 'subtotal',
			'order'   => 'asc',
		) );

		$this->assertTrue( $order_adjustments[0]->subtotal < $order_adjustments[1]->subtotal );
	}

	/**
	 * @covers ::edd_get_order_adjustments
	 */
	public function test_get_order_adjustments_with_orderby_subtotal_and_order_desc_should_return_true() {
		$order_adjustments = edd_get_order_adjustments( array(
			'orderby' => 'subtotal',
			'order'   => 'desc',
		) );

		$this->assertTrue( $order_adjustments[0]->subtotal > $order_adjustments[1]->subtotal );
	}

	/**
	 * @covers ::edd_get_order_adjustments
	 */
	public function test_get_order_adjustments_with_subtotal_should_return_1() {
		$order_adjustments = edd_get_order_adjustments( array(
			'subtotal' => \WP_UnitTest_Generator_Sequence::$incr,
		) );

		$this->assertCount( 1, $order_adjustments );
	}

	/**
	 * @covers ::edd_get_order_adjustments
	 */
	public function test_get_order_adjustments_with_subtotal__in_should_return_1() {
		$order_adjustments = edd_get_order_adjustments( array(
			'subtotal__in' => array(
				\WP_UnitTest_Generator_Sequence::$incr,
			),
		) );

		$this->assertCount( 1, $order_adjustments );
	}

	/**
	 * @covers ::edd_get_order_adjustments
	 */
	public function test_get_order_adjustments_with_subtotal__not_in_should_return_5() {
		$order_adjustments = edd_get_order_adjustments( array(
			'subtotal__not_in' => array(
				-999,
			),
		) );

		$this->assertCount( 5, $order_adjustments );
	}

	/**
	 * @covers ::edd_get_order_adjustments
	 */
	public function test_get_order_adjustments_with_orderby_tax_and_order_asc_should_return_true() {
		$order_adjustments = edd_get_order_adjustments( array(
			'orderby' => 'tax',
			'order'   => 'asc',
		) );

		$this->assertTrue( $order_adjustments[0]->tax < $order_adjustments[1]->tax );
	}

	/**
	 * @covers ::edd_get_order_adjustments
	 */
	public function test_get_order_adjustments_with_orderby_tax_and_order_desc_should_return_true() {
		$order_adjustments = edd_get_order_adjustments( array(
			'orderby' => 'tax',
			'order'   => 'desc',
		) );

		$this->assertTrue( $order_adjustments[0]->tax > $order_adjustments[1]->tax );
	}

	/**
	 * @covers ::edd_get_order_adjustments
	 */
	public function test_get_order_adjustments_with_tax_should_return_1() {
		$order_adjustments = edd_get_order_adjustments( array(
			'tax' => \WP_UnitTest_Generator_Sequence::$incr,
		) );

		$this->assertCount( 1, $order_adjustments );
	}

	/**
	 * @covers ::edd_get_order_adjustments
	 */
	public function test_get_order_adjustments_with_tax__in_should_return_1() {
		$order_adjustments = edd_get_order_adjustments( array(
			'tax__in' => array(
				\WP_UnitTest_Generator_Sequence::$incr,
			),
		) );

		$this->assertCount( 1, $order_adjustments );
	}

	/**
	 * @covers ::edd_get_order_adjustments
	 */
	public function test_get_order_adjustments_with_tax__not_in_should_return_5() {
		$order_adjustments = edd_get_order_adjustments( array(
			'tax__not_in' => array(
				-999,
			),
		) );

		$this->assertCount( 5, $order_adjustments );
	}

	/**
	 * @covers ::edd_get_order_adjustments
	 */
	public function test_get_order_adjustments_with_orderby_total_and_order_asc_should_return_true() {
		$order_adjustments = edd_get_order_adjustments( array(
			'orderby' => 'total',
			'order'   => 'asc',
		) );

		$this->assertTrue( $order_adjustments[0]->total < $order_adjustments[1]->total );
	}

	/**
	 * @covers ::edd_get_order_adjustments
	 */
	public function test_get_order_adjustments_with_orderby_total_and_order_desc_should_return_true() {
		$order_adjustments = edd_get_order_adjustments( array(
			'orderby' => 'total',
			'order'   => 'desc',
		) );

		$this->assertTrue( $order_adjustments[0]->total > $order_adjustments[1]->total );
	}

	/**
	 * @covers ::edd_get_order_adjustments
	 */
	public function test_get_order_adjustments_with_total_should_return_1() {
		$order_adjustments = edd_get_order_adjustments( array(
			'total' => \WP_UnitTest_Generator_Sequence::$incr,
		) );

		$this->assertCount( 1, $order_adjustments );
	}

	/**
	 * @covers ::edd_get_order_adjustments
	 */
	public function test_get_order_adjustments_with_total__in_should_return_1() {
		$order_adjustments = edd_get_order_adjustments( array(
			'total__in' => array(
				\WP_UnitTest_Generator_Sequence::$incr,
			),
		) );

		$this->assertCount( 1, $order_adjustments );
	}

	/**
	 * @covers ::edd_get_order_adjustments
	 */
	public function test_get_order_adjustments_with_total__not_in_should_return_5() {
		$order_adjustments = edd_get_order_adjustments( array(
			'total__not_in' => array(
				-999,
			),
		) );

		$this->assertCount( 5, $order_adjustments );
	}

	/**
	 * @covers ::edd_add_order_adjustment
	 */
	public function test_order_adjustment_with_null_type_id_returns_null() {
		$adjustment_id = edd_add_order_adjustment( array(
			'object_id'   => 1,
			'object_type' => 'order',
			'type_id'     => null,
			'type'        => 'fee',
			'description' => 'Custom Fee',
			'subtotal'    => 5.00,
			'tax'         => 0.00,
			'total'       => 5.00,
		) );
		$adjustment = edd_get_order_adjustment( $adjustment_id );

		$this->assertNull( $adjustment->type_id );
	}

	/**
	 * @covers ::edd_add_order_adjustment
	 */
	public function test_order_adjustment_with_no_type_key_returns_null() {
		$adjustment_id = edd_add_order_adjustment( array(
			'object_id'   => 1,
			'object_type' => 'order',
			'type_id'     => null,
			'type'        => 'fee',
			'description' => 'Custom Fee',
			'subtotal'    => 5.00,
			'tax'         => 0.00,
			'total'       => 5.00,
		) );
		$adjustment = edd_get_order_adjustment( $adjustment_id );

		$this->assertNull( $adjustment->type_key );
	}
}
