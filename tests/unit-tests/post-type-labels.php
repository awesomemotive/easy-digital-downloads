<?php
namespace EDD_Unit_Tests;

/**
 * @group edd_cpt
 */
class Tests_Post_Type_Labels extends EDD_UnitTestCase {
	public function setUp() {
		parent::setUp();
	}

	public function tearDown() {
		parent::tearDown();
	}

	public function test_get_default_labels() {
		$out = edd_get_default_labels();
		$this->assertArrayHasKey( 'singular', $out );
		$this->assertArrayHasKey( 'plural', $out );

		$this->assertEquals( 'Download', $out['singular'] );
		$this->assertEquals( 'Downloads', $out['plural'] );
	}

	public function test_singular_label() {
		$this->assertEquals( 'Download', edd_get_label_singular() );
		$this->assertEquals( 'download', edd_get_label_singular( true ) );
	}

	public function test_plural_label() {
		$this->assertEquals( 'Downloads', edd_get_label_plural() );
		$this->assertEquals( 'downloads', edd_get_label_plural( true ) );
	}
}