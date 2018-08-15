<?php
namespace EDD\Tests\Factory;

class Tax_Rate extends \WP_UnitTest_Factory_For_Thing {

	/**
	 * Stub out copy of parent method for IDE type hinting purposes.
	 *
	 * @param array $args
	 * @param null  $generation_definitions
	 *
	 * @return \EDD\Tax_Rates\Tax_Rate|false
	 */
	public function create_and_get( $args = array(), $generation_definitions = null ) {
		return parent::create_and_get( $args, $generation_definitions );
	}

	public function create_object( $args ) {
		return edd_add_tax_rate( $args );
	}

	public function update_object( $tax_rate_id, $fields ) {
		return edd_update_tax_rate( $tax_rate_id, $fields );
	}

	public function delete( $tax_rate_id ) {
		edd_delete_tax_rate( $tax_rate_id );
	}

	public function delete_many( $tax_rates ) {
		foreach ( $tax_rates as $tax_rate ) {
			edd_delete_tax_rate( $tax_rate );
		}
	}

	/**
	 * Stub out copy of parent method for IDE type hinting purposes.
	 *
	 * @param int $tax_rate_id Tax rate ID.
	 * @return \EDD\Tax_Rates\Tax_Rate|false
	 */
	public function get_object_by_id( $tax_rate_id ) {
		return edd_get_tax_rate( $tax_rate_id );
	}
}