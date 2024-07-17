<?php

namespace EDD\Tests\SiteHealth;

use EDD\Tests\PHPUnit\EDD_UnitTestCase;

class Sessions extends EDD_UnitTestCase {

	private static $data;

	public function test_sessions_label() {
		$this->assertEquals( 'Easy Digital Downloads &mdash; Sessions', $this->get_data()['label'] );
	}

	public function test_sessions_fields_session_enabled() {
		$this->assertArrayHasKey( 'session_enabled', $this->get_data()['fields'] );
	}

	public function test_session_enabled_is_disabled() {
		$this->assertEquals( 'Disabled', $this->get_data()['fields']['session_enabled']['value'] );
	}

	public function test_session_type_is_database() {
		$this->assertEquals( 'Database', $this->get_data()['fields']['session_type']['value'] );
	}

	private function get_data() {
		if ( is_null( self::$data ) ) {
			$sessions   = new \EDD\Admin\SiteHealth\Sessions();
			self::$data = $sessions->get();
		}

		return self::$data;
	}
}
