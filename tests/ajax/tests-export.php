<?php
/**
 * AJAX tests for the batch export handler.
 *
 * @package   EDD\Tests\Ajax
 * @copyright (c) 2026, Sandhills Development, LLC
 * @license https://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since 3.7.2
 */

namespace EDD\Tests\Ajax;

defined( 'ABSPATH' ) || exit; // @codeCoverageIgnore

use EDD\Admin\Exports\Exporters\Orders;
use EDD\Admin\Exports\Registry;
use EDD\Tests\PHPUnit\Ajax_UnitTestCase;

/**
 * Batch export AJAX handler tests.
 *
 * @since 3.7.2
 */
class Export extends Ajax_UnitTestCase {

	/**
	 * Registers the export handler and the exporter the tests use.
	 *
	 * The handler lives in an admin-only file the bootstrap does not load, and the exporter
	 * registry is populated by an admin-only subscriber, so both are wired up here.
	 *
	 * @return void
	 */
	public function set_up() {
		parent::set_up();

		require_once EDD_PLUGIN_DIR . 'includes/admin/reporting/export/export-functions.php';

		add_action( 'wp_ajax_edd_do_ajax_export', 'edd_do_ajax_export' );

		Registry::instance()->register_exporter(
			'orders',
			array(
				'label' => 'Orders',
				'class' => Orders::class,
			)
		);
	}

	/**
	 * Clears the request keys the tests set and the exporter they registered.
	 *
	 * The registry is a process-wide singleton that refuses duplicate IDs, so the exporter
	 * has to be removed for other tests that register it.
	 *
	 * @return void
	 */
	public function tear_down() {
		unset( $_POST['form'], $_POST['step'] );
		Registry::instance()->remove_item( 'orders' );

		parent::tear_down();
	}

	/**
	 * @covers ::edd_do_ajax_export
	 */
	public function test_user_without_export_capability_receives_error_response() {
		$this->_setRole( 'subscriber' );
		$this->post_export_step( 'orders', wp_create_nonce( 'edd_ajax_export' ) );

		$response = json_decode( $this->_handleAjax( 'edd_do_ajax_export' ), true );

		$this->assertTrue( $response['error'] );
		$this->assertNotEmpty( $response['message'] );
	}

	/**
	 * @covers ::edd_do_ajax_export
	 */
	public function test_mismatched_nonce_receives_error_response() {
		$this->set_up_exporting_user();
		$this->post_export_step( 'orders', 'not-the-export-nonce' );

		$response = json_decode( $this->_handleAjax( 'edd_do_ajax_export' ), true );

		$this->assertTrue( $response['error'] );
		$this->assertNotEmpty( $response['message'] );
	}

	/**
	 * @covers ::edd_do_ajax_export
	 */
	public function test_user_with_export_capability_receives_next_step() {
		$this->set_up_exporting_user();
		self::edd()->order->create();
		$this->post_export_step( 'orders', wp_create_nonce( 'edd_ajax_export' ) );

		$response = json_decode( $this->_handleAjax( 'edd_do_ajax_export' ), true );

		$this->assertSame( 2, $response['step'] );
		$this->assertArrayHasKey( 'percentage', $response );
	}

	/**
	 * Makes an administrator holding the export capability the current user.
	 *
	 * edd_install()'s role caps are not persisted in the test env, so the capability the
	 * handler checks is granted explicitly.
	 *
	 * @return void
	 */
	private function set_up_exporting_user() {
		$this->_setRole( 'administrator' );
		wp_get_current_user()->add_cap( 'export_shop_reports' );
	}

	/**
	 * Populates the request the export form JS sends for step one.
	 *
	 * @param string $exporter_id The registered exporter ID.
	 * @param string $nonce       The value to send as the export nonce.
	 * @return void
	 */
	private function post_export_step( $exporter_id, $nonce ) {
		$_POST['form'] = http_build_query(
			array(
				'edd_ajax_export' => $nonce,
				'exporter_id'     => $exporter_id,
			)
		);
		$_POST['step'] = 1;
	}
}
