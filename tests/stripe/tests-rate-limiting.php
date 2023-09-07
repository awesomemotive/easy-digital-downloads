<?php

namespace EDD\Tests\Stripe;

use \EDD\Tests\PHPUnit\EDD_UnitTestCase;
use SteveGrunwell\PHPUnit_Markup_Assertions\MarkupAssertionsTrait;

/**
 * Tests for functions.
 */
class Rate_Limiting extends EDD_UnitTestCase {

	public function test_card_error_tracking() {
		$this->assertTrue( edd_stripe()->rate_limiting->card_error_checks_enabled() );
	}

	public function test_card_error_tracking_off_with_filter() {
		add_filter( 'edds_card_error_checking_enabled', '__return_false' );
		$this->assertFalse( edd_stripe()->rate_limiting->card_error_checks_enabled() );
		remove_filter( 'edds_card_error_checking_enabled', '__return_false' );
	}

	public function test_card_errors_id() {
		$this->assertSame( '127.0.0.1', edd_stripe()->rate_limiting->get_card_error_id() );
	}

	public function test_card_error_limit_false() {
		$this->assertFalse( edd_stripe()->rate_limiting->has_hit_card_error_limit() );
	}

	public function test_card_error_limit_true() {
		edd_stripe()->rate_limiting->write_to_log( array( '127.0.0.1' => array( 'count' => 5, 'timeout' => current_time( 'timestamp' ) + 5 ) ) );
		$this->assertTrue( edd_stripe()->rate_limiting->has_hit_card_error_limit() );
		edd_stripe()->rate_limiting->remove_log_entry( '127.0.0.1' );
	}

	public function test_card_error_limit_false_past_expiration() {
		edd_stripe()->rate_limiting->write_to_log( array( '127.0.0.1' => array( 'count' => 5, 'timeout' => current_time( 'timestamp' ) - 5 ) ) );
		$this->assertFalse( edd_stripe()->rate_limiting->has_hit_card_error_limit() );
		$this->assertTrue( empty( edd_stripe()->rate_limiting->get_rate_limiting_entry( '127.0.0.1' ) ) );
	}

	public function test_increment_card_errors() {
		$this->assertSame( 1, edd_stripe()->rate_limiting->increment_card_error_count() );
		$this->assertSame( 2, edd_stripe()->rate_limiting->increment_card_error_count() );

		$entry = edd_stripe()->rate_limiting->get_rate_limiting_entry( '127.0.0.1' );

		$this->assertSame( 2, $entry['count'] );

		edd_stripe()->rate_limiting->remove_log_entry( '127.0.0.1' );
	}

	public function test_card_form_not_at_error_limit() {
		$card_form = edds_credit_card_form( false );
		$this->assertStringContainsString( '<fieldset id="edd_cc_fields" class="edd-do-validate">', $card_form );
	}

	public function test_card_form_at_error_limit() {
		edd_stripe()->rate_limiting->write_to_log( array( '127.0.0.1' => array( 'count' => 5, 'timeout' => current_time( 'timestamp' ) + 5 ) ) );
		$card_form = edds_credit_card_form( false );
		$this->assertEquals( '', $card_form );
		$errors = edd_get_errors();
		$this->assertContains( 'We are unable to process your payment at this time, please try again later or contact support.', $errors );
		edd_stripe()->rate_limiting->remove_log_entry( '127.0.0.1' );
	}

	public function test_card_max_attempts_filter() {
		add_filter( 'edds_max_card_error_count', function() { return 2; } );
		edd_stripe()->rate_limiting->increment_card_error_count();
		edd_stripe()->rate_limiting->increment_card_error_count();

		$this->assertTrue( edd_stripe()->rate_limiting->has_hit_card_error_limit() );
		remove_filter( 'edds_max_card_error_count', function() { return 2; } );
	}

}
