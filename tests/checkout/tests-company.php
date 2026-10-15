<?php

namespace EDD\Tests\Checkout;

use EDD\Forms\Checkout\Company;
use EDD\Forms\Checkout\Registry;
use EDD\Tests\PHPUnit\EDD_UnitTestCase;

/**
 * Company checkout field tests.
 */
class CompanyField extends EDD_UnitTestCase {

	public function test_company_field_id() {
		$field = new Company( array( 'address' => array( 'company' => 'Acme Corp' ) ) );
		$this->assertSame( 'company', $field->get_id() );
	}

	public function test_company_field_label() {
		$field = new Company( array( 'address' => array( 'company' => '' ) ) );
		$this->assertSame( 'Company', $field->get_label() );
	}

	public function test_company_field_description() {
		$field = new Company( array( 'address' => array( 'company' => '' ) ) );
		$this->assertSame( 'The company or organization name for your order.', $field->get_description() );
	}

	public function test_company_field_renders_input() {
		$field = new Company( array( 'address' => array( 'company' => 'Acme Corp' ) ) );

		ob_start();
		$field->render();
		$output = ob_get_clean();

		$this->assertStringContainsString( 'company', $output );
		$this->assertStringContainsString( 'Acme Corp', $output );
		$this->assertStringContainsString( 'autocomplete="organization"', $output );
		$this->assertStringContainsString( 'placeholder=', $output );
	}

	public function test_company_field_renders_empty_value() {
		$field = new Company( array( 'address' => array( 'company' => '' ) ) );

		ob_start();
		$field->render();
		$output = ob_get_clean();

		$this->assertStringContainsString( 'name="company"', $output );
		$this->assertStringContainsString( 'id="company"', $output );
		$this->assertStringContainsString( 'edd-card-company-wrap', $output );
	}

	public function test_company_field_registered_in_registry() {
		$fields = Registry::get_fields();
		$this->assertArrayHasKey( 'company', $fields );
		$this->assertSame( Company::class, $fields['company']['class'] );
	}

	public function test_company_field_is_allowed() {
		$this->assertContains( 'company', Registry::get_allowed_fields() );
	}

	public function test_company_included_in_checkout_fields_when_enabled() {
		edd_update_option(
			'checkout_address_fields',
			array(
				'address' => 1,
				'company' => 1,
				'country' => 1,
			)
		);

		$checkout_fields = Registry::get_checkout_fields();

		$this->assertArrayHasKey( 'company', $checkout_fields );
		$this->assertEquals( 1, $checkout_fields['company'] );
	}

	public function test_company_excluded_from_checkout_fields_when_disabled() {
		edd_update_option(
			'checkout_address_fields',
			array(
				'address' => 1,
				'company' => 0,
				'country' => 1,
			)
		);

		$checkout_fields = Registry::get_checkout_fields();

		$this->assertArrayHasKey( 'company', $checkout_fields );
		$this->assertEquals( 0, $checkout_fields['company'] );
	}

	public function test_company_saved_as_order_meta() {
		$order_id = \EDD\Tests\Helpers\EDD_Helper_Payment::create_simple_payment();
		edd_update_order_meta( $order_id, 'company_name', 'Acme Corp' );

		$this->assertEquals( 'Acme Corp', edd_get_order_meta( $order_id, 'company_name', true ) );

		\EDD\Tests\Helpers\EDD_Helper_Payment::delete_payment( $order_id );
	}

	public function test_company_saved_as_customer_meta() {
		$customer_id = edd_add_customer(
			array(
				'name'  => 'Test Customer',
				'email' => 'test-company@example.com',
			)
		);

		$customer = edd_get_customer( $customer_id );
		$customer->update_meta( 'company_name', 'Acme Corp' );

		$this->assertEquals( 'Acme Corp', $customer->get_meta( 'company_name', true ) );

		$customer->delete_meta( 'company_name' );

		$this->assertEmpty( $customer->get_meta( 'company_name', true ) );

		edd_delete_customer( $customer_id );
	}

	public function test_company_field_key() {
		$field = new Company( array( 'address' => array( 'company' => '' ) ) );
		$reflection = new \ReflectionMethod( $field, 'get_key' );
		if (PHP_VERSION_ID < 80100) {
            $reflection->setAccessible( true );
        }

		$this->assertSame( 'company', $reflection->invoke( $field ) );
	}

	public function test_company_field_defaults_include_name_and_id() {
		$field = new Company( array( 'address' => array( 'company' => '' ) ) );
		$reflection = new \ReflectionMethod( $field, 'get_defaults' );
		if (PHP_VERSION_ID < 80100) {
            $reflection->setAccessible( true );
        }

		$defaults = $reflection->invoke( $field );

		$this->assertSame( 'company', $defaults['name'] );
		$this->assertSame( 'company', $defaults['id'] );
	}

	public function test_company_order_meta_delete_clears_value() {
		$order_id = \EDD\Tests\Helpers\EDD_Helper_Payment::create_simple_payment();
		edd_update_order_meta( $order_id, 'company_name', 'Delete Me' );

		edd_delete_order_meta( $order_id, 'company_name' );

		$this->assertEmpty( edd_get_order_meta( $order_id, 'company_name', true ) );

		\EDD\Tests\Helpers\EDD_Helper_Payment::delete_payment( $order_id );
	}

	public function test_company_order_meta_persists_after_status_change() {
		$order_id = \EDD\Tests\Helpers\EDD_Helper_Payment::create_simple_payment();
		edd_update_order_status( $order_id, 'complete' );
		edd_update_order_meta( $order_id, 'company_name', 'Test Corp' );

		$this->assertEquals( 'Test Corp', edd_get_order_meta( $order_id, 'company_name', true ) );

		edd_delete_order_meta( $order_id, 'company_name' );

		$this->assertEmpty( edd_get_order_meta( $order_id, 'company_name', true ) );

		\EDD\Tests\Helpers\EDD_Helper_Payment::delete_payment( $order_id );
	}

	public function tearDown(): void {
		edd_delete_option( 'checkout_address_fields' );
		parent::tearDown();
	}
}
