<?php
namespace EDD\Tests\Factory;

/**
 * Unit test factory for customer addresses.
 *
 * Note: The below method notations are defined solely for the benefit of IDEs,
 * as a way to indicate expected return values from the given factory methods.
 *
 * @method int create( $args = array(), $generation_definitions = null )
 * @method \EDD\Customers\Customer_Address create_and_get( $args = array(), $generation_definitions = null )
 * @method int[] create_many( $count, $args = array(), $generation_definitions = null )
 *
 * @package EDD\Tests\Factory
 */
class Customer_Address extends \WP_UnitTest_Factory_For_Thing {

	public function __construct( $factory = null ) {
		parent::__construct( $factory );

		$this->default_generation_definitions = array(
			'customer_id' => new \WP_UnitTest_Generator_Sequence( '%d' ),
			'type'        => new \WP_UnitTest_Generator_Sequence( 'type%d' ),
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
		return edd_add_customer_address( $args );
	}

	public function update_object( $customer_id, $fields ) {
		return edd_update_customer_address( $customer_id, $fields );
	}

	public function delete( $customer_id ) {
		edd_delete_customer_address( $customer_id );
	}

	public function delete_many( $customers ) {
		foreach ( $customers as $customer ) {
			$this->delete( $customer );
		}
	}

	/**
	 * Stub out copy of parent method for IDE type hinting purposes.
	 *
	 * @param int $customer_address_id Customer address ID.
	 *
	 * @return \EDD\Customers\Customer_Address|false
	 */
	public function get_object_by_id( $customer_address_id ) {
		return edd_fetch_customer_address( $customer_address_id );
	}
}
