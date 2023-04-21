<?php
namespace EDD\Tests;

use EDD\Tests\PHPUnit\EDD_UnitTestCase;

/**
 * Migration tests.
 */
class Migration_Tests extends EDD_UnitTestCase {

	public function test_edd_needs_v3_migration_no_data_should_return_false() {
		$this->assertFalse( _edd_needs_v3_migration() );
	}

	public function test_edd_needs_v3_migration_final_payment_should_return_true() {
		update_option( 'edd_v3_migration_pending', 123456, false );

		$this->assertTrue( _edd_needs_v3_migration() );
		delete_option( 'edd_v3_migration_pending' );
	}

	public function test_edd_needs_v3_migration_default_tax_rate_should_return_true() {
		edd_update_option( 'tax_rate', '.14' );

		$this->assertTrue( _edd_needs_v3_migration() );
		edd_delete_option( 'tax_rate' );
	}

	public function test_edd_needs_v3_migration_discount_should_return_true() {
		$discount_id = wp_insert_post(
			array(
				'post_type'   => 'edd_discount',
				'post_title'  => 'Legacy Discount',
				'post_status' => 'active',
			)
		);

		$this->assertTrue( _edd_needs_v3_migration() );
		wp_delete_post( $discount_id );
	}

	public function test_edd_needs_v3_migration_user_address_should_return_true() {
		$this->setExpectedIncorrectUsage( 'add_user_meta()/update_user_meta()' );
		$user_id = wp_create_user(
			'test_migration_user',
			'password'
		);
		$address = add_metadata(
			'user',
			$user_id,
			'_edd_user_address',
			array(
				'line1'   => 'First address',
				'line2'   => 'Line two',
				'city'    => 'MyCity',
				'zip'     => '12345',
				'country' => 'US',
				'state'   => 'AL',
			)
		);

		$this->assertTrue( _edd_needs_v3_migration() );
		wp_delete_user( $user_id );
	}

	public function test_edd_needs_v3_migration_tax_rates_should_return_true() {
		$tax_rates = array(
			array(
				'country' => 'US',
				'state'   => 'AL',
				'rate'    => 15,
			),
		);
		update_option( 'edd_tax_rates', $tax_rates );

		$this->assertTrue( _edd_needs_v3_migration() );
		delete_option( 'edd_tax_rates' );
	}
}
