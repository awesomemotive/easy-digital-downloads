<?php
/**
 * General tests for the export system
 *
 * @group exports
 */

namespace EDD\Tests\Exports;

use EDD\Tests\PHPUnit\EDD_UnitTestCase;
use EDD\Admin\Exports\Registry;

class General extends EDD_UnitTestCase {
	public function test_get_exports_dir() {
		$dir = edd_get_exports_dir();

		$this->assertStringEndsWith( 'exports', $dir );
		$this->assertStringStartsWith( WP_CONTENT_DIR, $dir );
		$this->assertStringContainsString( edd_get_upload_dir(), $dir );
	}

	public function test_registering_invalid_report_throws_exception() {
		$this->expectException( 'Exception' );

		Registry::instance()->register_exporter( 'invalid', array() );
	}

	public function test_registering_valid_report_does_not_throw_exception() {
		$this->expectNotToPerformAssertions();

		Registry::instance()->register_exporter(
			'orders',
			array(
				'label'       => __( 'Orders', 'easy-digital-downloads' ),
				'description' => __( 'Download a CSV of all orders.', 'easy-digital-downloads' ),
				'class'       => \EDD\Admin\Exports\Exporters\Orders::class,
				'view'        => 'export-orders',
			)
		);
	}
}
