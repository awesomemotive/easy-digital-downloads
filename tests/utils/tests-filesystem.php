<?php

namespace EDD\Tests\Utils;

use EDD\Tests\PHPUnit\EDD_UnitTestCase;

class FileSystem extends EDD_UnitTestCase {
	public function test_init_filesystem() {
		unset( $GLOBALS['wp_filesystem'] );
		$file_system = \EDD\Utils\FileSystem::get_fs();
		$this->assertSame( $GLOBALS['wp_filesystem'], $file_system );
	}

	public function test_file_exists_returns_true_when_file_exists() {
		$file = __FILE__;

		$this->assertTrue( \EDD\Utils\FileSystem::file_exists( $file ) );
	}

	public function test_file_exists_returns_false_when_file_does_not_exist() {
		$file = __FILE__ . 'does-not-exist';

		$this->assertFalse( \EDD\Utils\FileSystem::file_exists( $file ) );
	}

	public function test_file_exists_returns_true_when_sanitized() {
		$file = 'phar://' . __FILE__;

		$this->assertTrue( \EDD\Utils\FileSystem::file_exists( $file ) );
	}

	public function test_file_exists_is_sanitized() {
		$this->assertSame( __FILE__, \EDD\Utils\FileSystem::sanitize_file_path( 'phar://' . __FILE__ ) );
		$this->assertSame( __FILE__, \EDD\Utils\FileSystem::sanitize_file_path( 'php://' . __FILE__ ) );
		$this->assertSame( __FILE__, \EDD\Utils\FileSystem::sanitize_file_path( 'glob://' . __FILE__ ) );
		$this->assertSame( __FILE__, \EDD\Utils\FileSystem::sanitize_file_path( 'data://' . __FILE__ ) );
		$this->assertSame( __FILE__, \EDD\Utils\FileSystem::sanitize_file_path( 'expect://' . __FILE__ ) );
		$this->assertSame( __FILE__, \EDD\Utils\FileSystem::sanitize_file_path( 'zip://' . __FILE__ ) );
		$this->assertSame( __FILE__, \EDD\Utils\FileSystem::sanitize_file_path( 'rar://' . __FILE__ ) );
		$this->assertSame( __FILE__, \EDD\Utils\FileSystem::sanitize_file_path( 'zlib://' . __FILE__ ) );
	}

	public function test_file_exists_is_sanitized_with_url_encoded_protocols() {
		$this->assertSame( __FILE__, \EDD\Utils\FileSystem::sanitize_file_path( urlencode( 'phar://' ) . __FILE__ ) );
		$this->assertSame( __FILE__, \EDD\Utils\FileSystem::sanitize_file_path( urlencode( 'php://' ) . __FILE__ ) );
		$this->assertSame( __FILE__, \EDD\Utils\FileSystem::sanitize_file_path( urlencode( 'glob://' ) . __FILE__ ) );
		$this->assertSame( __FILE__, \EDD\Utils\FileSystem::sanitize_file_path( urlencode( 'data://' ) . __FILE__ ) );
		$this->assertSame( __FILE__, \EDD\Utils\FileSystem::sanitize_file_path( urlencode( 'expect://' ) . __FILE__ ) );
		$this->assertSame( __FILE__, \EDD\Utils\FileSystem::sanitize_file_path( urlencode( 'zip://' ) . __FILE__ ) );
		$this->assertSame( __FILE__, \EDD\Utils\FileSystem::sanitize_file_path( urlencode( 'rar://' ) . __FILE__ ) );
		$this->assertSame( __FILE__, \EDD\Utils\FileSystem::sanitize_file_path( urlencode( 'zlib://' ) . __FILE__ ) );
	}

	public function test_file_exists_is_sanitized_with_case() {
		$this->assertSame( __FILE__, \EDD\Utils\FileSystem::sanitize_file_path( urlencode( 'pHaR://' ) . __FILE__ ) );
	}

	public function test_fopen_returns_resource_when_file_exists() {
		$file = __FILE__;

		$this->assertIsResource( \EDD\Utils\FileSystem::fopen( $file, 'r' ) );
	}

	public function test_fopen_returns_resource_when_file_exists_and_mode_is_w() {
		$file = __FILE__;

		$this->assertIsResource( \EDD\Utils\FileSystem::fopen( $file, 'w' ) );
	}

	public function test_fopen_returns_resource_when_sanitized() {
		$file = 'phar://' . __FILE__;

		$this->assertIsResource( \EDD\Utils\FileSystem::fopen( $file, 'r' ) );
	}

	public function test_size_returns_true_when_file_exists() {
		$file = __FILE__;

		$this->assertIsInt( \EDD\Utils\FileSystem::size( $file ) );
	}

	public function test_size_returns_false_when_file_does_not_exist() {
		$file = __FILE__ . 'does-not-exist';

		$this->assertFalse( \EDD\Utils\FileSystem::size( $file ) );
	}

	public function test_size_returns_true_when_sanitized() {
		$file = 'phar://' . __FILE__;

		$this->assertIsInt( \EDD\Utils\FileSystem::size( $file ) );
	}

	public function test_get_contents_returns_false_when_file_does_not_exist() {
		$file = __FILE__ . 'does-not-exist';

		$this->assertFalse( \EDD\Utils\FileSystem::get_contents( $file ) );
	}

	public function test_get_contents_returns_string_when_file_exists() {
		$file = __FILE__;

		$this->assertSame( file_get_contents( $file ), \EDD\Utils\FileSystem::get_contents( $file ) );
	}

	public function test_file_as_array_returns_false_when_file_does_not_exist() {
		$file = __FILE__ . 'does-not-exist';

		$this->assertFalse( \EDD\Utils\FileSystem::file( $file ) );
	}

	public function test_file_as_array_returns_array_when_file_exists() {
		$file = __FILE__;

		$this->assertSame( file( $file ), \EDD\Utils\FileSystem::file( $file ) );
	}

	public function test_filemtime_returns_false_when_file_does_not_exist() {
		$file = __FILE__ . 'does-not-exist';

		$this->assertFalse( \EDD\Utils\FileSystem::filemtime( $file ) );
	}

	public function test_filemtime_returns_int_when_file_exists() {
		$file = __FILE__;

		$this->assertSame( filemtime( $file ), \EDD\Utils\FileSystem::filemtime( $file ) );
	}
}
