<?php

namespace EDD\Tests\Downloads;

use EDD\Tests\Helpers;
use EDD\Tests\PHPUnit\EDD_UnitTestCase;

class Service extends EDD_UnitTestCase {

	public function test_download_is_service_is_false() {
		$download = $this->get_service();

		$this->assertFalse( $download->is_service() );
	}

	public function test_download_is_service_with_term_empty_options_is_false() {
		$download = $this->get_service();
		$category = wp_insert_term( 'Test Category', 'download_category' );
		$terms    = wp_set_object_terms( $download->ID, array( $category['term_id'] ), 'download_category' );

		$this->assertFalse( $download->is_service() );
	}

	public function test_download_is_service_by_type_is_true() {
		$download = $this->get_service();
		update_post_meta( $download->ID, '_edd_product_type', 'service' );

		$this->assertTrue( $download->is_service() );
	}

	public function test_download_is_service_by_type_bundle_is_false() {
		$download = $this->get_service();
		update_post_meta( $download->ID, '_edd_product_type', 'bundle' );

		$this->assertFalse( $download->is_service() );
	}

	public function test_download_is_service_with_term_is_true() {
		$download = $this->get_service();
		$category = wp_insert_term( 'Test Category', 'download_category' );
		$terms    = wp_set_object_terms( $download->ID, array( $category['term_id'] ), 'download_category' );
		edd_update_option( 'edd_das_service_categories', array( $category['term_id'] ) );

		$this->assertTrue( $download->is_service() );
	}

	public function test_download_is_service_with_term_not_in_options_is_false() {
		$download = $this->get_service();
		$category = wp_insert_term( 'Test Category', 'download_category' );
		$terms    = wp_set_object_terms( $download->ID, array( $category['term_id'] ), 'download_category' );
		edd_update_option( 'edd_das_service_categories', array( 1234, 5678 ) );

		$this->assertFalse( $download->is_service() );
	}

	private function get_service() {
		$download    = Helpers\EDD_Helper_Download::create_simple_download();
		$download_id = $download->ID;
		delete_post_meta( $download_id, 'edd_download_files' );

		return new \EDD\Downloads\Service( $download_id );
	}
}
