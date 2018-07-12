<?php
namespace EDD\Tests\Factory;

class Order_Adjustment extends \WP_UnitTest_Factory_For_Thing {

	public function __construct( $factory = null ) {
		parent::__construct( $factory );

		$this->default_generation_definitions = array(
			'object_id'   => new \WP_UnitTest_Generator_Sequence( '%d' ),
			'object_type' => 'order',
			'type_id'     => new \WP_UnitTest_Generator_Sequence( '%d' ),
			'type'        => new \WP_UnitTest_Generator_Sequence( 'Adjustment Type %d' ),
			'description' => new \WP_UnitTest_Generator_Sequence( 'Adjustment Description %d' ),
			'subtotal'    => new \WP_UnitTest_Generator_Sequence( '%f' ),
			'tax'         => new \WP_UnitTest_Generator_Sequence( '%f' ),
			'total'       => new \WP_UnitTest_Generator_Sequence( '%f' ),
		);
	}

	/**
	 * Stub out copy of parent method for IDE type hinting purposes.
	 *
	 * @param array $args
	 * @param null  $generation_definitions
	 *
	 * @return \EDD\Orders\Order_Adjustment|false
	 */
	public function create_and_get( $args = array(), $generation_definitions = null ) {
		$this->default_generation_definitions['date_created'] = date( 'Y-m-d h:i:s', mt_rand( 1, time() ) );

		return parent::create_and_get( $args, $generation_definitions );
	}

	public function create_object( $args ) {
		return edd_add_order_adjustment( $args );
	}

	public function update_object( $order_adjustment_id, $fields ) {
		return edd_update_order_adjustment( $order_adjustment_id, $fields );
	}

	public function delete( $order_adjustment_id ) {
		edd_delete_order_adjustment( $order_adjustment_id );
	}

	public function delete_many( $order_adjustments ) {
		foreach ( $order_adjustments as $order_adjustment ) {
			$this->delete( $order_adjustment );
		}
	}

	/**
	 * Stub out copy of parent method for IDE type hinting purposes.
	 *
	 * @param $order_adjustment_id Order ID.
	 *
	 * @return \EDD\Orders\Order|false
	 */
	public function get_object_by_id( $order_adjustment_id ) {
		return edd_get_order_adjustment( $order_adjustment_id );
	}
}