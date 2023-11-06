<?php
namespace EDD\Tests\Privacy;

use EDD\Tests\PHPUnit\EDD_UnitTestCase;

class Masking extends EDD_UnitTestCase {
	public function test_string_mask_0_char() {
		$this->assertSame( '', edd_mask_string( '' ) );
	}

	public function test_string_mask_1_char() {
		$this->assertSame( 'a', edd_mask_string( 'a' ) );
	}

	public function test_string_mask_2_char() {
		$this->assertSame( 'h*', edd_mask_string( 'hi' ) );
	}

	public function test_string_mask_5_char() {
		$this->assertSame( 'h***o', edd_mask_string( 'hello' ) );
	}

	public function test_domain_mask_0_parts() {
		$this->assertSame( '', edd_mask_domain( '' ) );
	}

	public function test_domain_mask_2_parts() {
		$this->assertSame( 'e*****e.org', edd_mask_domain( 'example.org' ) );
	}

	public function test_domain_mask_3_parts_cctld() {
		$this->assertSame( 'e*****e.co.uk', edd_mask_domain( 'example.co.uk' ) );
	}

	public function test_domain_mask_3_parts_subdomain() {
		$this->assertSame( 'e*****e.i*****d.org', edd_mask_domain( 'example.invalid.org' ) );
	}

	public function test_domain_mask_4_parts_subdomain_cctld() {
		$this->assertSame( 'e*****e.i*****d.org.uk', edd_mask_domain( 'example.invalid.org.uk' ) );
	}

	public function test_email_mask() {
		$this->assertSame( 'a***n@e*****e.org', edd_pseudo_mask_email( 'admin@example.org' ) );
	}

	public function test_email_mask_not_email() {
		$this->assertSame( 'test-string', edd_pseudo_mask_email( 'test-string' ) );
	}
}
