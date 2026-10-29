<?php
/**
 * Order address sanitization tests.
 */

namespace EDD\Tests\Orders;

use EDD\Tests\Helpers;
use EDD\Tests\PHPUnit\EDD_UnitTestCase;

/**
 * Tests that order and customer address fields are sanitized on the way into the database.
 *
 * Covers both admin writers and the schema itself, which is what covers the writers these
 * tests do not call directly: the CSV importer and the gateway webhooks.
 *
 * @group edd_orders
 * @group edd_customers
 */
class OrderAddressSanitization extends EDD_UnitTestCase {

	/**
	 * The order id the manual order handler created, captured from its own action.
	 *
	 * @var int
	 */
	private $manual_order_id = 0;

	public function setUp(): void {
		parent::setUp();

		$this->manual_order_id = 0;

		add_action( 'edd_post_add_manual_order', array( $this, 'capture_manual_order_id' ) );
	}

	public function tearDown(): void {
		remove_action( 'edd_post_add_manual_order', array( $this, 'capture_manual_order_id' ) );

		unset( $_POST['edd_add_order_nonce'], $_POST['_wpnonce'], $_REQUEST['_wpnonce'] );

		wp_set_current_user( 0 );

		parent::tearDown();
	}

	/**
	 * Records the order id the manual order handler created.
	 *
	 * @param int $order_id The new order id.
	 */
	public function capture_manual_order_id( $order_id ) {
		$this->manual_order_id = (int) $order_id;
	}

	/**
	 * The premise these cases rest on: the role that writes an address cannot open the screen
	 * that renders it, and is not trusted to author markup anywhere else in WordPress.
	 */
	public function test_shop_worker_writes_addresses_but_cannot_read_them() {
		$worker = $this->factory->user->create( array( 'role' => 'shop_worker' ) );

		$this->assertTrue(
			user_can( $worker, 'edit_shop_payments' ),
			'A shop worker must be able to write an order address, or these cases test nothing.'
		);
		$this->assertFalse(
			user_can( $worker, 'unfiltered_html' ),
			'A shop worker is not trusted to author markup.'
		);
		$this->assertFalse(
			user_can( $worker, 'view_shop_reports' ),
			'A shop worker cannot open the customer addresses screen its own address renders on.'
		);
	}

	/**
	 * The New Order screen must not store markup in any address field.
	 */
	public function test_manual_order_address_is_sanitized() {
		$this->sign_in_as_shop_worker();

		$_POST['edd_add_order_nonce'] = wp_create_nonce( 'edd_add_order_nonce' );

		edd_add_manual_order( $this->manual_order_args() );

		$order_id = $this->manual_order_id;
		$this->assertNotEmpty( $order_id, 'The manual order handler must have created an order.' );

		$order_addresses = edd_get_order_addresses( array( 'order_id' => $order_id ) );
		$this->assertCount( 1, $order_addresses, 'The handler must have written exactly one order address.' );

		$this->assertAddressHasNoMarkup( $order_addresses[0], true );

		$order = edd_get_order( $order_id );
		$this->assertNotEmpty( $order->customer_id, 'The handler must have created a customer to attach the address to.' );

		$customer_addresses = edd_get_customer_addresses( array( 'customer_id' => $order->customer_id ) );
		$this->assertCount( 1, $customer_addresses, 'The handler must have copied the address to the customer.' );

		$this->assertAddressHasNoMarkup( $customer_addresses[0], true );

		$this->assertSame(
			'Acme',
			edd_get_order_meta( $order_id, 'company_name', true ),
			'The company field was already sanitized and must stay that way.'
		);
	}

	/**
	 * An address must be compared against the customer's existing addresses using the value it
	 * is stored as, so saving the same address twice does not duplicate it. This is what
	 * sanitizing inside edd_maybe_add_customer_address() adds over the schema callback alone.
	 */
	public function test_manual_order_does_not_duplicate_an_existing_customer_address() {
		$this->sign_in_as_shop_worker();

		$customer_id = edd_add_customer(
			array(
				'name'  => 'Existing Customer',
				'email' => 'addresses-existing@example.test',
			)
		);
		$this->assertNotEmpty( $customer_id, 'The customer fixture must exist.' );

		// Seeded with the values the input sanitizes down to, so this case measures the
		// handler and not the schema callback.
		$seeded = edd_add_customer_address(
			array(
				'customer_id' => $customer_id,
				'address'     => '1 Market St',
				'address2'    => 'Unit',
				'city'        => 'Town',
				'region'      => 'CA',
				'postal_code' => '9',
				'country'     => 'US',
			)
		);
		$this->assertNotEmpty( $seeded, 'The seeded customer address must exist.' );
		$this->assertCount(
			1,
			edd_get_customer_addresses( array( 'customer_id' => $customer_id ) ),
			'The customer must start with exactly one address.'
		);

		$_POST['edd_add_order_nonce'] = wp_create_nonce( 'edd_add_order_nonce' );

		$args                     = $this->manual_order_args();
		$args['edd-new-customer'] = 0;
		$args['customer-id']      = $customer_id;
		unset( $args['edd-new-customer-email'], $args['edd-new-customer-first-name'], $args['edd-new-customer-last-name'] );

		edd_add_manual_order( $args );

		$this->assertNotEmpty( $this->manual_order_id, 'The manual order handler must have created an order.' );

		$this->assertCount(
			1,
			edd_get_customer_addresses( array( 'customer_id' => $customer_id ) ),
			'Re-saving the same address must not add a second customer address row.'
		);
	}

	/**
	 * An address whose every value is markup is not an address, and must not be written as a
	 * row of empty strings. edd_add_order_address() already refuses an empty address; that
	 * rule only holds if the values are sanitized before it is applied.
	 */
	public function test_an_address_made_only_of_markup_is_not_written() {
		$order_id = Helpers\EDD_Helper_Payment::create_simple_payment();

		$this->assertEmpty(
			edd_get_order_addresses( array( 'order_id' => $order_id ) ),
			'The order must start with no address, or this case proves nothing.'
		);

		$written = edd_add_order_address(
			array(
				'order_id'    => $order_id,
				'address'     => '<svg onload=alert("ADDR")>',
				'address2'    => '<img src=x onerror=alert("ADDR2")>',
				'city'        => '<svg onload=alert("CITY")>',
				'region'      => '<svg onload=alert("REGION")>',
				'postal_code' => '<svg onload=alert("ZIP")>',
				'country'     => '<svg onload=alert("COUNTRY")>',
			)
		);

		$this->assertFalse( $written, 'An address with nothing left in it must be refused.' );
		$this->assertEmpty(
			edd_get_order_addresses( array( 'order_id' => $order_id ) ),
			'No address row may be written for an address made only of markup.'
		);
	}

	/**
	 * The order edit screen must not store markup in any address field.
	 */
	public function test_order_edit_address_is_sanitized() {
		// The order edit handler lives in an admin file that is only loaded on that screen.
		require_once EDD_PLUGIN_DIR . 'includes/admin/payments/actions.php';

		$order_id = Helpers\EDD_Helper_Payment::create_simple_payment();
		$order    = edd_get_order( $order_id );
		$this->assertNotEmpty( $order, 'The order fixture must exist.' );

		$this->sign_in_as_shop_worker();

		$nonce                = wp_create_nonce( 'edd_update_payment_details_nonce' );
		$_REQUEST['_wpnonce'] = $nonce;
		$_POST['_wpnonce']    = $nonce;

		$address               = $this->address_with_markup();
		$address['address_id'] = 0;
		$address['company']    = 'Acme<svg onload=alert("COMPANY")>';

		// The order edit screen takes the name from the customer record, not the request.
		unset( $address['name'] );

		edd_update_payment_details(
			array(
				'edd_payment_id'        => $order_id,
				'_wpnonce'              => $nonce,
				'edd-payment-status'    => $order->status,
				'edd-payment-date'      => '2026-08-16',
				'edd-payment-time-hour' => '11',
				'edd-payment-time-min'  => '00',
				'current-customer-id'   => $order->customer_id,
				'customer-id'           => $order->customer_id,
				'edd-new-customer'      => 0,
				'edd_order_address'     => $address,
			)
		);

		$order_addresses = edd_get_order_addresses( array( 'order_id' => $order_id ) );
		$this->assertNotEmpty( $order_addresses, 'The handler must have written an order address.' );

		foreach ( $order_addresses as $order_address ) {
			$this->assertAddressHasNoMarkup( $order_address );
		}

		$this->assertSame(
			'Acme',
			edd_get_order_meta( $order_id, 'company_name', true ),
			'The company field was already sanitized and must stay that way.'
		);
	}

	/**
	 * A row written straight through the order address API is sanitized by the schema, which
	 * is the only thing covering the CSV importer and the gateway webhooks.
	 */
	public function test_schema_sanitizes_order_address_columns_on_add() {
		$order_id = Helpers\EDD_Helper_Payment::create_simple_payment();

		$address_id = edd_add_order_address(
			array_merge( $this->address_with_markup(), array( 'order_id' => $order_id ) )
		);
		$this->assertNotEmpty( $address_id, 'The order address must have been written.' );

		$this->assertAddressHasNoMarkup( edd_get_order_address( $address_id ), true );
	}

	/**
	 * The same, on update: an existing row edited through the API is sanitized too.
	 */
	public function test_schema_sanitizes_order_address_columns_on_update() {
		$order_id = Helpers\EDD_Helper_Payment::create_simple_payment();

		$address_id = edd_add_order_address(
			array(
				'order_id' => $order_id,
				'address'  => '1 Market St',
				'city'     => 'Town',
				'country'  => 'US',
			)
		);
		$this->assertNotEmpty( $address_id, 'The order address fixture must exist.' );

		$updated = edd_update_order_address( $address_id, $this->address_with_markup() );
		$this->assertNotEmpty( $updated, 'The order address must have been updated.' );

		$this->assertAddressHasNoMarkup( edd_get_order_address( $address_id ), true );
	}

	/**
	 * A row written straight through the customer address API is sanitized by the schema.
	 */
	public function test_schema_sanitizes_customer_address_columns_on_add() {
		$customer_id = edd_add_customer(
			array(
				'name'  => 'Schema Customer',
				'email' => 'addresses-schema@example.test',
			)
		);
		$this->assertNotEmpty( $customer_id, 'The customer fixture must exist.' );

		$address_id = edd_add_customer_address(
			array_merge( $this->address_with_markup(), array( 'customer_id' => $customer_id ) )
		);
		$this->assertNotEmpty( $address_id, 'The customer address must have been written.' );

		$this->assertAddressHasNoMarkup( edd_fetch_customer_address( $address_id ), true );
	}

	/**
	 * The same, on update.
	 */
	public function test_schema_sanitizes_customer_address_columns_on_update() {
		$customer_id = edd_add_customer(
			array(
				'name'  => 'Schema Customer',
				'email' => 'addresses-schema-update@example.test',
			)
		);

		$address_id = edd_add_customer_address(
			array(
				'customer_id' => $customer_id,
				'address'     => '1 Market St',
				'city'        => 'Town',
				'country'     => 'US',
			)
		);
		$this->assertNotEmpty( $address_id, 'The customer address fixture must exist.' );

		$updated = edd_update_customer_address( $address_id, $this->address_with_markup() );
		$this->assertNotEmpty( $updated, 'The customer address must have been updated.' );

		$this->assertAddressHasNoMarkup( edd_fetch_customer_address( $address_id ), true );
	}

	/**
	 * Signs in a fresh user holding the shop worker role EDD composes on install.
	 */
	private function sign_in_as_shop_worker() {
		$worker = $this->factory->user->create( array( 'role' => 'shop_worker' ) );

		wp_set_current_user( $worker );

		return $worker;
	}

	/**
	 * The address fields every case writes, each containing markup that has to be removed.
	 *
	 * @return array
	 */
	private function address_with_markup() {
		return array(
			'name'        => 'Addr<svg onload=alert("NAME")>',
			'address'     => '1 Market St<svg onload=alert("ADDR")>',
			'address2'    => 'Unit<img src=x onerror=alert("ADDR2")>',
			'city'        => 'Town<svg onload=alert("CITY")>',
			'region'      => 'CA<svg onload=alert("REGION")>',
			'postal_code' => '9<svg onload=alert("ZIP")>',
			'country'     => 'US',
		);
	}

	/**
	 * The New Order screen's request, as the handler reads it.
	 *
	 * @return array
	 */
	private function manual_order_args() {
		$address            = $this->address_with_markup();
		$address['company'] = 'Acme<svg onload=alert("COMPANY")>';

		return array(
			'edd-payment-status'          => 'pending',
			'edd-new-customer'            => 1,
			'edd-new-customer-email'      => 'addresses-manual@example.test',
			'edd-new-customer-first-name' => 'Addr',
			'edd-new-customer-last-name'  => 'Test',
			'edd-payment-date'            => '2026-08-16',
			'edd-payment-time-hour'       => '11',
			'edd-payment-time-min'        => '00',
			'gateway'                     => 'manual',
			'ip'                          => '203.0.113.11',
			'subtotal'                    => 1.00,
			'total'                       => 1.00,
			'edd_order_address'           => $address,
		);
	}

	/**
	 * Asserts that a stored address row kept its text and none of its markup.
	 *
	 * @param object $address    An order address or customer address row.
	 * @param bool   $check_name Whether the name field contained markup on the way in.
	 */
	private function assertAddressHasNoMarkup( $address, $check_name = false ) {
		$this->assertNotEmpty( $address, 'The address row must exist before it can be asserted on.' );

		$expected = array(
			'address'     => '1 Market St',
			'address2'    => 'Unit',
			'city'        => 'Town',
			'region'      => 'CA',
			'postal_code' => '9',
		);

		if ( $check_name ) {
			$expected['name'] = 'Addr';
		}

		foreach ( $expected as $field => $value ) {
			$this->assertSame(
				$value,
				$address->{$field},
				sprintf( 'The %s field must be stored without its markup.', $field )
			);
		}

		foreach ( array_keys( $expected ) as $field ) {
			$this->assertStringNotContainsString(
				'<',
				$address->{$field},
				sprintf( 'The %s field must not store a tag opener.', $field )
			);
		}
	}
}
