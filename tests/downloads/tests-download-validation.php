<?php

namespace EDD\Tests\Downloads;

use EDD\Tests\PHPUnit\EDD_UnitTestCase;
use EDD\Downloads\Process;

class DownloadValidation extends EDD_UnitTestCase {
	public function test_file_path_is_invalid_when_empty() {
		$this->assertFalse( Process::validate( '' ) );
	}

	public function test_file_path_is_valid() {
		$this->assertTrue( Process::validate( 'https://example.org/wp-content/uploads/edd/2019/05/test-file.zip' ) );
	}

	public function test_file_path_is_invalid() {
		$this->assertFalse( Process::validate( 'https://example.org/wp-content/uploads/2024/07/../../../../../../../../../../../../etc/passwd' ) );
	}

	public function test_file_path_is_invalid_windows_absolute_path() {
		$this->assertFalse( Process::validate( 'C:\Users\Public\Downloads\test-file.zip' ) );
	}

	public function test_file_path_is_valid_windows_absolute_path_in_dev() {
		add_filter( 'edd_is_dev_environment', '__return_true' );
		$this->assertTrue( Process::validate( 'C:\Users\Public\Downloads\test-file.zip' ) );
		remove_filter( 'edd_is_dev_environment', '__return_true' );
	}
}
