<?php

namespace EDD\Tests\Logs;

use EDD\Tests\PHPUnit\EDD_UnitTestCase;

/**
 * Class DebugLog
 *
 * @group logs
 */
class DebugLog extends EDD_UnitTestCase {

	/**
	 * Set up fixtures.
	 */
	public static function wpSetUpBeforeClass() {
		edd_update_option( 'debug_mode', false );
		EDD()->debug_log->clear_log_file();
	}

	public function tearDown(): void {
		edd_update_option( 'debug_mode', false );
		EDD()->debug_log->clear_log_file();
	}

	public function test_debug_log_does_not_exist() {
		$this->assertFalse( file_exists( EDD()->debug_log->get_log_file_path() ) );
	}

	public function test_debug_log_empty() {
		$this->assertEmpty( EDD()->debug_log->get_file_contents() );
	}

	public function test_debug_log_message_not_added_when_debug_mode_disabled() {
		edd_debug_log( 'This is a test message' );

		$this->assertEmpty( EDD()->debug_log->get_file_contents() );
	}

	public function test_debug_log_added_message_forced() {
		$message = 'This is a test message';
		edd_debug_log( $message, true );

		$this->assertStringContainsString( $message, EDD()->debug_log->get_file_contents() );
	}

	public function test_debug_log_added_then_cleared_is_empty() {
		edd_update_option( 'debug_mode', true );
		edd_debug_log( 'This is a test message' );
		edd_debug_log( 'This is another test message' );

		$this->assertStringContainsString( 'This is a test message', EDD()->debug_log->get_file_contents() );

		EDD()->debug_log->clear_log_file();

		$this->assertEmpty( EDD()->debug_log->get_file_contents() );
	}
}
