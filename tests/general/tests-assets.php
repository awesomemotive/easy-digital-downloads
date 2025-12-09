<?php

/**
 * Assets tests.
 *
 * @package     EDD\Tests\General
 */

namespace EDD\Tests\General;

use EDD\Tests\PHPUnit\EDD_UnitTestCase;

class Assets extends EDD_UnitTestCase {

	public function test_edd_get_assets_url() {
		$this->assertSame( EDD_PLUGIN_URL . 'assets/build/', edd_get_assets_url() );
		$this->assertSame( EDD_PLUGIN_URL . 'assets/vendor/', edd_get_assets_url( 'vendor' ) );
	}

	public function test_edd_get_assets_url_without_path() {
		$url = edd_get_assets_url();
		$this->assertStringEndsWith( 'assets/build/', $url );
	}

	public function test_edd_get_assets_url_with_js_path() {
		$url = edd_get_assets_url( 'js/admin' );
		$this->assertStringEndsWith( 'assets/build/js/admin/', $url );
	}

	public function test_edd_get_assets_url_with_vendor_path() {
		$url = edd_get_assets_url( 'vendor/jquery' );
		$this->assertStringEndsWith( 'assets/vendor/jquery/', $url );
		$this->assertStringNotContainsString( 'build', $url );
	}

	public function test_edd_get_assets_dir() {
		$this->assertSame( EDD_PLUGIN_DIR . 'assets/build/', edd_get_assets_dir() );
		$this->assertSame( EDD_PLUGIN_DIR . 'assets/vendor/', edd_get_assets_dir( 'vendor' ) );
	}

	public function test_edd_get_assets_dir_without_path() {
		$dir = edd_get_assets_dir();
		$this->assertStringEndsWith( 'assets/build/', $dir );
		$this->assertTrue( is_dir( $dir ) );
	}

	public function test_edd_get_assets_dir_with_js_path() {
		$dir = edd_get_assets_dir( 'js/admin' );
		$this->assertStringEndsWith( 'assets/build/js/admin/', $dir );
	}

	/**
	 * @dataProvider _test_includes_assets_dp
	 */
	public function test_includes_assets( $path_to_file ) {
		$this->assertFileExists( $path_to_file );
	}

	/**
	 * Data provider for test_includes_assets().
	 */
	public function _test_includes_assets_dp() {
		return array(
			array( EDD_PLUGIN_DIR . 'assets/vendor/css/chosen.min.css' ),
			array( EDD_PLUGIN_DIR . 'assets/build/css/admin/chosen.min.css' ),
			array( EDD_PLUGIN_DIR . 'assets/build/css/admin/admin.min.css' ),
			array( EDD_PLUGIN_DIR . 'assets/images/edd-cpt-2x.png' ),
			array( EDD_PLUGIN_DIR . 'assets/images/edd-cpt.png' ),
			array( EDD_PLUGIN_DIR . 'assets/images/edd-icon-2x.png' ),
			array( EDD_PLUGIN_DIR . 'assets/images/edd-icon.png' ),
			array( EDD_PLUGIN_DIR . 'assets/images/edd-logo.png' ),
			array( EDD_PLUGIN_DIR . 'assets/images/edd-media.png' ),
			array( EDD_PLUGIN_DIR . 'assets/images/loading.gif' ),
			array( EDD_PLUGIN_DIR . 'templates/images/loading.gif' ),
			array( EDD_PLUGIN_DIR . 'assets/images/media-button.png' ),
			array( EDD_PLUGIN_DIR . 'templates/images/tick.png' ),
			array( EDD_PLUGIN_DIR . 'assets/images/xit.gif' ),
			array( EDD_PLUGIN_DIR . 'assets/build/css/frontend/edd.min.css' ),
			array( EDD_PLUGIN_DIR . 'templates/images/xit.gif' ),
			array( EDD_PLUGIN_DIR . 'assets/build/js/admin/admin.js' ),
			array( EDD_PLUGIN_DIR . 'assets/build/js/frontend/edd-ajax.js' ),
			array( EDD_PLUGIN_DIR . 'assets/build/js/frontend/checkout.js' ),
			array( EDD_PLUGIN_DIR . 'assets/vendor/js/chosen.jquery.min.js' ),
			array( EDD_PLUGIN_DIR . 'assets/vendor/js/jquery.creditcardvalidator.min.js' ),
			array( EDD_PLUGIN_DIR . 'assets/vendor/js/jquery.flot.min.js' ),
			array( EDD_PLUGIN_DIR . 'assets/vendor/js/jquery.validate.min.js' ),
		);
	}
}
