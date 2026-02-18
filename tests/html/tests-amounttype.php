<?php

namespace EDD\Tests\HTML;

use EDD\Tests\PHPUnit\EDD_UnitTestCase;

class AmountType extends EDD_UnitTestCase {

	/**
	 * The wrapper span class is always present.
	 */
	public function test_amount_type_renders_wrapper() {
		$amount_type = new \EDD\HTML\AmountType( array() );
		$field       = $amount_type->get();

		$this->assertStringContainsString( 'class="edd-amount-type-wrapper"', $field );
	}

	/**
	 * The default input type is 'number'.
	 */
	public function test_amount_type_default_input_is_number() {
		$amount_type = new \EDD\HTML\AmountType(
			array(
				'name' => 'edd_amount',
				'id'   => 'edd_amount',
			)
		);
		$field = $amount_type->get();

		$this->assertStringContainsString( 'type="number"', $field );
	}

	/**
	 * When type is not 'number', a text input is rendered.
	 */
	public function test_amount_type_text_input_when_type_is_not_number() {
		$amount_type = new \EDD\HTML\AmountType(
			array(
				'name' => 'edd_amount',
				'id'   => 'edd_amount',
				'type' => 'text',
			)
		);
		$field = $amount_type->get();

		$this->assertStringContainsString( 'type="text"', $field );
		$this->assertStringNotContainsString( 'type="number"', $field );
	}

	/**
	 * The default position is 'after', so the unit appears as a suffix.
	 */
	public function test_amount_type_default_position_is_after() {
		$amount_type = new \EDD\HTML\AmountType(
			array(
				'name' => 'edd_amount',
				'id'   => 'edd_amount',
				'unit' => '%',
			)
		);
		$field = $amount_type->get();

		$this->assertStringContainsString( 'edd-input__symbol--suffix', $field );
		$this->assertStringNotContainsString( 'edd-input__symbol--prefix', $field );
	}

	/**
	 * When position is 'before', the unit appears as a prefix.
	 */
	public function test_amount_type_position_before_renders_prefix() {
		$amount_type = new \EDD\HTML\AmountType(
			array(
				'name'     => 'edd_amount',
				'id'       => 'edd_amount',
				'unit'     => '$',
				'position' => 'before',
			)
		);
		$field = $amount_type->get();

		$this->assertStringContainsString( 'edd-input__symbol--prefix', $field );
		$this->assertStringNotContainsString( 'edd-input__symbol--suffix', $field );
	}

	/**
	 * When position is 'after', the unit appears as a suffix.
	 */
	public function test_amount_type_position_after_renders_suffix() {
		$amount_type = new \EDD\HTML\AmountType(
			array(
				'name'     => 'edd_amount',
				'id'       => 'edd_amount',
				'unit'     => '%',
				'position' => 'after',
			)
		);
		$field = $amount_type->get();

		$this->assertStringContainsString( 'edd-input__symbol--suffix', $field );
		$this->assertStringNotContainsString( 'edd-input__symbol--prefix', $field );
	}

	/**
	 * The unit value is rendered inside the symbol span.
	 */
	public function test_amount_type_renders_unit() {
		$amount_type = new \EDD\HTML\AmountType(
			array(
				'name' => 'edd_amount',
				'id'   => 'edd_amount',
				'unit' => '$',
			)
		);
		$field = $amount_type->get();

		$this->assertStringContainsString( '$', $field );
	}

	/**
	 * The symbol span is always rendered, even when the unit is empty.
	 */
	public function test_amount_type_symbol_always_rendered_when_unit_is_empty() {
		$amount_type = new \EDD\HTML\AmountType(
			array(
				'name' => 'edd_amount',
				'id'   => 'edd_amount',
				'unit' => '',
			)
		);
		$field = $amount_type->get();

		$this->assertStringContainsString( 'edd-input__symbol', $field );
	}

	/**
	 * The name and id attributes are passed through to the input.
	 */
	public function test_amount_type_passes_name_and_id() {
		$amount_type = new \EDD\HTML\AmountType(
			array(
				'name' => 'edd_amount',
				'id'   => 'edd_amount',
			)
		);
		$field = $amount_type->get();

		$this->assertStringContainsString( 'name="edd_amount"', $field );
		$this->assertStringContainsString( 'id="edd_amount"', $field );
	}

	/**
	 * The value is passed through to the input.
	 */
	public function test_amount_type_passes_value() {
		$amount_type = new \EDD\HTML\AmountType(
			array(
				'name'  => 'edd_amount',
				'id'    => 'edd_amount',
				'value' => '25',
			)
		);
		$field = $amount_type->get();

		$this->assertStringContainsString( 'value="25"', $field );
	}

	/**
	 * The min, max, and step attributes are passed through to the number input.
	 */
	public function test_amount_type_passes_min_max_step() {
		$amount_type = new \EDD\HTML\AmountType(
			array(
				'name' => 'edd_amount',
				'id'   => 'edd_amount',
				'min'  => '0',
				'max'  => '100',
				'step' => '0.01',
			)
		);
		$field = $amount_type->get();

		$this->assertStringContainsString( 'min="0"', $field );
		$this->assertStringContainsString( 'max="100"', $field );
		$this->assertStringContainsString( 'step="0.01"', $field );
	}

	/**
	 * The required attribute is passed through to the input.
	 */
	public function test_amount_type_passes_required() {
		$amount_type = new \EDD\HTML\AmountType(
			array(
				'name'     => 'edd_amount',
				'id'       => 'edd_amount',
				'required' => true,
			)
		);
		$field = $amount_type->get();

		$this->assertStringContainsString( 'required', $field );
	}

	/**
	 * The placeholder attribute is passed through to the input.
	 */
	public function test_amount_type_passes_placeholder() {
		$amount_type = new \EDD\HTML\AmountType(
			array(
				'name'        => 'edd_amount',
				'id'          => 'edd_amount',
				'placeholder' => '0.00',
			)
		);
		$field = $amount_type->get();

		$this->assertStringContainsString( 'placeholder="0.00"', $field );
	}

	/**
	 * The prefix symbol span appears before the input when position is 'before'.
	 */
	public function test_amount_type_prefix_appears_before_input() {
		$amount_type = new \EDD\HTML\AmountType(
			array(
				'name'     => 'edd_amount',
				'id'       => 'edd_amount',
				'unit'     => '$',
				'position' => 'before',
			)
		);
		$field = $amount_type->get();

		$prefix_pos = strpos( $field, 'edd-input__symbol--prefix' );
		$input_pos  = strpos( $field, '<input' );

		$this->assertLessThan( $input_pos, $prefix_pos );
	}

	/**
	 * The suffix symbol span appears after the input when position is 'after'.
	 */
	public function test_amount_type_suffix_appears_after_input() {
		$amount_type = new \EDD\HTML\AmountType(
			array(
				'name'     => 'edd_amount',
				'id'       => 'edd_amount',
				'unit'     => '%',
				'position' => 'after',
			)
		);
		$field = $amount_type->get();

		$suffix_pos = strpos( $field, 'edd-input__symbol--suffix' );
		$input_pos  = strpos( $field, '<input' );

		$this->assertGreaterThan( $input_pos, $suffix_pos );
	}
}
