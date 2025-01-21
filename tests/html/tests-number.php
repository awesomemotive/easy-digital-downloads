<?php

namespace EDD\Tests\HTML;

use EDD\Tests\PHPUnit\EDD_UnitTestCase;

class Number extends EDD_UnitTestCase {

	public function test_number_field() {
		$number = new \EDD\HTML\Number(
			array(
				'name'        => 'edd_number',
				'id'          => 'edd_number',
				'value'       => '10',
				'placeholder' => 'Enter a number',
				'min'         => '1',
				'max'         => '100',
				'step'        => '1',
				'required'    => true,
			)
		);
		$field = $number->get();

		$this->assertStringContainsString( 'type="number"', $field );
		$this->assertStringContainsString( 'name="edd_number"', $field );
		$this->assertStringContainsString( 'id="edd_number"', $field );
		$this->assertStringContainsString( 'value="10"', $field );
		$this->assertStringContainsString( 'placeholder="Enter a number"', $field );
		$this->assertStringContainsString( 'min="1"', $field );
		$this->assertStringContainsString( 'max="100"', $field );
		$this->assertStringContainsString( 'step="1"', $field );
		$this->assertStringContainsString( 'required', $field );
	}

	public function test_number_field_has_datalist() {
		$number = new \EDD\HTML\Number(
			array(
				'name'        => 'edd_number',
				'id'          => 'edd_number',
				'value'       => '10',
				'placeholder' => 'Enter a number',
				'min'         => '1',
				'max'         => '100',
				'step'        => '1',
				'required'    => true,
				'datalist'    => array( 1, 2, 3, 4, 5 ),
			)
		);
		$field = $number->get();

		$this->assertStringContainsString( 'type="number"', $field );
		$this->assertStringContainsString( 'name="edd_number"', $field );
		$this->assertStringContainsString( 'required', $field );
		$this->assertStringContainsString( '<datalist id="edd_number-datalist">', $field );
		$this->assertStringContainsString( '<option value="1">', $field );
		$this->assertStringContainsString( 'list="edd_number-datalist"', $field );
	}
}
