<?php
namespace EDD\Tests\Factory;

class Order_Item extends \WP_UnitTest_Factory_For_Thing {

	public function __construct( $factory = null ) {
		parent::__construct( $factory );

		$this->default_generation_definitions = array(
			'order_id'     => new \WP_UnitTest_Generator_Sequence( '%d' ),
			'product_id'   => new \WP_UnitTest_Generator_Sequence( '%d' ),
			'product_name' => 'Test Product',
			'price_id'     => new \WP_UnitTest_Generator_Sequence( '%d' ),
			'cart_index'   => new \WP_UnitTest_Generator_Sequence( '%d' ),
			'type'         => 'download',
			'status'       => 'inherit',
			'quantity'     => 1,
			'amount'       => 20,
			'subtotal'     => 20,
			'tax'          => 5,
			'discount'     => 5,
			'total'        => 20,
		);
	}

	/**
	 * Stub out copy of parent method for IDE type hinting purposes.
	 *
	 * @param array $args
	 * @param null  $generation_definitions
	 *
	 * @return \EDD\Orders\Order|false
	 */
	public function create_and_get( $args = array(), $generation_definitions = null ) {
		$this->default_generation_definitions['date_created'] = date( 'Y-m-d h:i:s', mt_rand( 1, time() ) );

		return parent::create_and_get( $args, $generation_definitions );
	}

	public function create_object( $args ) {
		return edd_add_order_item( $args );
	}

	public function update_object( $order_item_id, $fields ) {
		return edd_update_order_item( $order_item_id, $fields );
	}

	public function delete( $order_item_id ) {
		edd_delete_order( $order_item_id );
	}

	public function delete_many( $order_items ) {
		foreach ( $order_items as $order_item ) {
			$this->delete( $order_item );
		}
	}

	/**
	 * Stub out copy of parent method for IDE type hinting purposes.
	 *
	 * @param $order_item_id Order ID.
	 *
	 * @return \EDD\Orders\Order|false
	 */
	public function get_object_by_id( $order_item_id ) {
		return edd_get_order_item( $order_item_id );
	}
}