<?php
namespace EDD\Tests\Factory;

class Order extends \WP_UnitTest_Factory_For_Thing {

	public function __construct( $factory = null ) {
		parent::__construct( $factory );

		$this->default_generation_definitions = array(
			'status'          => 'complete',
			'type'            => 'sale',
			'date_completed'  => edd_get_utc_date_string(),
			'date_refundable' => EDD()->utils->date( 'now' )->addDays( 30 )->toDateTimeString(),
			'user_id'         => new \WP_UnitTest_Generator_Sequence( '%d' ),
			'customer_id'     => new \WP_UnitTest_Generator_Sequence( '%d' ),
			'email'           => new \WP_UnitTest_Generator_Sequence( 'user%d@edd.test' ),
			'ip'              => '10.1.1.1',
			'gateway'         => 'manual',
			'mode'            => 'live',
			'currency'        => 'USD',
			'payment_key'     => md5( 'edd' ),
			'subtotal'        => 100,
			'tax'             => 25,
			'discount'        => 5,
			'total'           => 120,
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
		return parent::create_and_get( $args, $generation_definitions );
	}

	public function create_object( $args ) {
		$order_id = edd_add_order( $args );
		$order    = edd_get_order( $order_id );

		edd_add_order_item(
			array(
				'order_id'     => $order_id,
				'product_id'   => 1,
				'product_name' => 'Simple Download',
				'status'       => $order->status,
				'amount'       => 100,
				'subtotal'     => 100,
				'discount'     => 5,
				'tax'          => 25,
				'total'        => 120,
				'quantity'     => 1,
			)
		);

		edd_add_order_adjustment( array(
			'object_type' => 'order',
			'object_id'   => $order_id,
			'type'        => 'tax_rate',
			'total'       => '0.25',
		) );

		edd_add_order_address( array(
			'order_id'   => $order_id,
			'first_name' => 'John',
			'last_name'  => 'Smith',
			'country'    => 'US',
		) );

		return $order_id;
	}

	public function update_object( $order_id, $fields ) {
		return edd_update_order( $order_id, $fields );
	}

	public function delete( $order_id ) {
		edd_destroy_order( $order_id );
	}

	public function delete_many( $orders ) {
		foreach ( $orders as $order ) {
			$this->delete( $order );
		}
	}

	/**
	 * Stub out copy of parent method for IDE type hinting purposes.
	 *
	 * @param $order_id Order ID.
	 *
	 * @return \EDD\Orders\Order|false
	 */
	public function get_object_by_id( $order_id ) {
		return edd_get_order( $order_id );
	}
}
