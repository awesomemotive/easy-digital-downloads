<?php

namespace EDD\Tests\Forms;

use EDD\Tests\PHPUnit\EDD_UnitTestCase;
use EDD\Forms\Checkout\State;

class CheckoutAddress extends EDD_UnitTestCase {

	public function test_checkout_address_state_id_is_card_state() {
		$state = new State( $this->get_data() );

		$this->assertEquals( 'card_state', $state->get_id() );
	}

	public function test_checkout_address_state_label() {
		$state = new State( $this->get_data() );

		$this->assertEquals( 'Billing State / Province', $state->get_label() );
	}

	public function test_checkout_address_state_input_is_select() {
		$state = new State( $this->get_data() );


		ob_start();
		$state->do_input();
		$html = ob_get_clean();

		$this->assertStringContainsString( '<select', $html );
		$this->assertStringContainsString( '</select>', $html );
		$this->assertStringContainsString( 'California', $html );
		$this->assertStringContainsString( '<option value="CA" selected>', $html );
	}

	private function get_data( $args = array() ) {

		$args = wp_parse_args(
			$args,
			array(
				'country' => 'US',
				'state'   => 'CA',
				'city'    => 'San Francisco',
				'zip'     => '94101',
			)
		);

		return array( 'address' => $args );
	}
}
