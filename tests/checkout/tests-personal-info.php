<?php

namespace EDD\Pro\Tests\Checkout;

use EDD\Forms\Checkout\PersonalInfo\Email;
use EDD\Forms\Checkout\PersonalInfo\FirstName;
use EDD\Forms\Checkout\PersonalInfo\LastName;
use EDD\Tests\PHPUnit\EDD_UnitTestCase;
use EDD\Forms\Handler;

/**
 * Personal Info field tests.
 *
 * @see \EDD\Forms\Checkout\PersonalInfo
 */
class PersonalInfo extends EDD_UnitTestCase {

	public function test_first_name_field_id() {
		$field = new FirstName( array( 'first_name' => 'John' ) );
		$this->assertSame( 'edd-first', $field->get_id() );
	}

	public function test_first_name_field_label() {
		$field = new FirstName( array( 'first_name' => 'John' ) );
		$this->assertSame( 'First Name', $field->get_label() );
	}

	public function test_last_name_field_id() {
		$field = new LastName( array( 'last_name' => 'Doe' ) );
		$this->assertSame( 'edd-last', $field->get_id() );
	}

	public function test_last_name_field_label() {
		$field = new LastName( array( 'last_name' => 'Doe' ) );
		$this->assertSame( 'Last Name', $field->get_label() );
	}

	public function test_email_field_id() {
		$field = new Email( array( 'email' => 'john.doe@example.com' ) );
		$this->assertSame( 'edd-email', $field->get_id() );
	}

	public function test_email_field_label() {
		$field = new Email( array( 'email' => 'john.doe@example.com' ) );
		$this->assertSame( 'Email', $field->get_label() );
	}

	/**
	 * Test that the edd_purchase_form_after_email action is fired.
	 */
	public function test_email_field_action_fires() {
		$fields_to_render = array(
			Email::class,
			FirstName::class,
			LastName::class,
		);
		$customer_data = array(
			'email'      => 'jane.doe@example.com',
			'first_name' => 'Jane',
			'last_name'  => 'Doe',
		);

		$before_action_count = did_action( 'edd_purchase_form_after_email' );

		ob_start();
		Handler::render_fields( $fields_to_render, $customer_data );
		ob_end_clean();

		$after_action_count = did_action( 'edd_purchase_form_after_email' );

		$this->assertGreaterThan(
			$before_action_count,
			$after_action_count,
			'Action edd_purchase_form_after_email did not fire during render_fields.'
		);
	}
}
