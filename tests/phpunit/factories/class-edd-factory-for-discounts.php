<?php
namespace EDD\Tests\Factory;

class Discount extends \WP_UnitTest_Factory_For_Thing {

	function __construct( $factory = null ) {
		parent::__construct( $factory );
	}

	/**
	 * Stub out copy of parent method for IDE type hinting purposes.
	 *
	 * @param array $args
	 * @param null  $generation_definitions
	 *
	 * @return \EDD_Discount|false
	 */
	function create_and_get( $args = array(), $generation_definitions = null ) {
		return parent::create_and_get( $args, $generation_definitions );
	}

	function create_object( $args ) {
		return EDD()->discounts->insert( $args );
	}

	function update_object( $discount_id, $fields ) {
		return EDD()->discounts->update( $discount_id, $fields );
	}

	public function delete( $discount_id ) {
		EDD()->discounts->delete( $discount_id );
	}

	public function delete_many( $discounts ) {
		foreach ( $discounts as $discount ) {
			$this->delete( $discount );
		}
	}

	/**
	 * Stub out copy of parent method for IDE type hinting purposes.
	 *
	 * @param $discount_id Discount ID.
	 *
	 * @return \EDD_Discount|false
	 */
	function get_object_by_id( $discount_id ) {
		return edd_get_discount( $discount_id );
	}
}