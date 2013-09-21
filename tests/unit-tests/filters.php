<?php
namespace EDD_Unit_Tests;

/**
 * @group edd_filters
 */
class Tests_Filters extends EDD_UnitTestCase {
	public function setUp() {
		parent::setUp();
	}

	public function test_the_content() {
		global $wp_filter;
		$this->assertarrayHasKey( 'edd_before_download_content', $wp_filter['the_content'][10] );
		$this->assertarrayHasKey( 'edd_after_download_content', $wp_filter['the_content'][10] );
		$this->assertarrayHasKey( 'edd_filter_success_page_content', $wp_filter['the_content'][10] );
		$this->assertarrayHasKey( 'edd_microdata_wrapper', $wp_filter['the_content'][10] );
		$this->assertarrayHasKey( 'edd_microdata_title', $wp_filter['the_title'][10] );
	}


}
