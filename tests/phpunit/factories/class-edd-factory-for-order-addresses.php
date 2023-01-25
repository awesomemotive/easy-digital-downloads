<?php
namespace EDD\Tests\Factory;

/**
 * Unit test factory for order addresses.
 *
 * Note: The below method notations are defined solely for the benefit of IDEs,
 * as a way to indicate expected return values from the given factory methods.
 *
 * @method int create( $args = array(), $generation_definitions = null )
 * @method \EDD\Orders\Order_Address create_and_get( $args = array(), $generation_definitions = null )
 * @method int[] create_many( $count, $args = array(), $generation_definitions = null )
 *
 * @package EDD\Tests\Factory
 */
class Order_Address extends \WP_UnitTest_Factory_For_Thing {

	public function __construct( $factory = null ) {
		parent::__construct( $factory );

		$this->default_generation_definitions = array(
			'order_id'    => new \WP_UnitTest_Generator_Sequence( '%d' ),
			'name'        => new \WP_UnitTest_Generator_Sequence( 'name%d' ),
			'status'      => new \WP_UnitTest_Generator_Sequence( 'status%d' ),
			'address'     => new \WP_UnitTest_Generator_Sequence( 'address%d' ),
			'address2'    => new \WP_UnitTest_Generator_Sequence( 'address2%d' ),
			'city'        => new \WP_UnitTest_Generator_Sequence( 'city%d' ),
			'region'      => new \WP_UnitTest_Generator_Sequence( 'region%d' ),
			'postal_code' => new \WP_UnitTest_Generator_Sequence( 'postal_code%d' ),
			'country'     => new \WP_UnitTest_Generator_Sequence( 'country%d' ),
		);
	}

	public function create_object( $args ) {
		return edd_add_order_address( $args );
	}

	public function update_object( $order_address_id, $fields ) {
		return edd_update_order_address( $order_address_id, $fields );
	}

	public function delete( $order_address_id ) {
		edd_delete_order_address( $order_address_id );
	}

	public function delete_many( $order_addresses ) {
		foreach ( $order_addresses as $order_address ) {
			$this->delete( $order_address );
		}
	}

	/**
	 * Stub out copy of parent method for IDE type hinting purposes.
	 *
	 * @param $order_address_id Order address ID.
	 *
	 * @return \EDD\Orders\Order_Address|false
	 */
	public function get_object_by_id( $order_address_id ) {
		return edd_get_order_address_by( $order_address_id );
	}
}
