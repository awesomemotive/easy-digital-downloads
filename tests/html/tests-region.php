<?php

namespace EDD\Tests\HTML;

use EDD\Tests\PHPUnit\EDD_UnitTestCase;

class Region extends EDD_UnitTestCase {

	public function test_region_input_is_select_us() {
		$input = new \EDD\HTML\Region(
			array(
				'country' => 'US',
			)
		);

		$this->assertStringContainsString( 'select', $input->get() );
	}

	public function test_region_input_is_text_ro() {
		$input = new \EDD\HTML\Region(
			array(
				'country' => 'RO',
			)
		);
		$output = $input->get();

		$this->assertStringContainsString( 'input', $output );
		$this->assertStringContainsString( 'text', $output );
	}

	public function test_region_input_same_as_region_select() {
		$input = new \EDD\HTML\Region(
			array(
				'country' => 'US',
			)
		);

		$select = new \EDD\HTML\RegionSelect(
			array(
				'country' => 'US',
			)
		);

		$this->assertEquals( $select->get(), $input->get() );
	}
}
