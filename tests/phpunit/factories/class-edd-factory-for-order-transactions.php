<?php
namespace EDD\Tests\Factory;

/**
 * Unit test factory for order transactions.
 *
 * Note: The below method notations are defined solely for the benefit of IDEs,
 * as a way to indicate expected return values from the given factory methods.
 *
 * @method int create( $args = array(), $generation_definitions = null )
 * @method \EDD\Orders\Order_Transaction create_and_get( $args = array(), $generation_definitions = null )
 * @method int[] create_many( $count, $args = array(), $generation_definitions = null )
 *
 * @package EDD\Tests\Factory
 */
class Order_Transaction extends \WP_UnitTest_Factory_For_Thing {

	public function __construct( $factory = null ) {
		parent::__construct( $factory );

		$this->default_generation_definitions = array(
			'object_id'      => new \WP_UnitTest_Generator_Sequence( '%d' ),
			'object_type'    => new \WP_UnitTest_Generator_Sequence( 'order%d' ),
			'transaction_id' => new \WP_UnitTest_Generator_Sequence( 'transaction%d' ),
			'gateway'        => new \WP_UnitTest_Generator_Sequence( 'gateway%d' ),
			'status'         => new \WP_UnitTest_Generator_Sequence( 'status%d' ),
			'total'          => new \WP_UnitTest_Generator_Sequence( '%f' ),
		);
	}

	public function create_object( $args ) {
		return edd_add_order_transaction( $args );
	}

	public function update_object( $order_transaction_id, $fields ) {
		return edd_update_order_transaction( $order_transaction_id, $fields );
	}

	public function delete( $order_transaction_id ) {
		edd_delete_order_transaction( $order_transaction_id );
	}

	public function delete_many( $order_transactions ) {
		foreach ( $order_transactions as $order_transaction ) {
			$this->delete( $order_transaction );
		}
	}

	/**
	 * Stub out copy of parent method for IDE type hinting purposes.
	 *
	 * @param $order_transaction_id Order transaction ID.
	 *
	 * @return \EDD\Orders\Order_Transaction|false
	 */
	public function get_object_by_id( $order_transaction_id ) {
		return edd_get_order_transaction( $order_transaction_id );
	}
}