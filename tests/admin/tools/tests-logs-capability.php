<?php
/**
 * Log view capability tests.
 *
 * The file download, payment error and API request log views are reached two ways: the Tools
 * screen that hosts them, and the generic `edd-action` dispatcher. These tests assert reading a
 * log requires the Tools screen's own capability, not a lower one via the dispatcher.
 *
 * @package     EDD\Tests\Admin\Tools
 * @copyright   Copyright (c) 2026, Sandhills Development, LLC
 * @license     https://opensource.org/licenses/gpl-2.0.php GNU Public License
 */

namespace EDD\Tests\Admin\Tools;

use EDD\Tests\PHPUnit\EDD_UnitTestCase;
use EDD\Admin\Tools\Logs;

/**
 * Log view capability tests.
 *
 * @since 3.7.1
 *
 * @group edd_logs
 */
class LogsCapability extends EDD_UnitTestCase {

	public function tearDown(): void {
		$_GET  = array();
		$_POST = array();
		wp_set_current_user( 0 );

		parent::tearDown();
	}

	/**
	 * The log types the Tools screen offers.
	 *
	 * @return array
	 */
	public function log_types() {
		return array(
			'file downloads' => array( 'file-downloads' ),
			'gateway errors' => array( 'gateway-error' ),
			'api requests'   => array( 'api-requests' ),
		);
	}

	/**
	 * A role which can read reports but not manage the store must not read the logs.
	 *
	 * The logs are hosted by the Tools screen, which requires `manage_shop_settings`, so gating
	 * them on `view_shop_reports` lets a lower-privileged role read them directly. The API
	 * request log is the sharpest case: its rows carry the credentials used to make each call.
	 *
	 * @dataProvider log_types
	 * @param string $type The log type.
	 */
	public function test_a_shop_accountant_cannot_set_up_a_log_view( $type ) {
		$user_id = $this->factory->user->create( array( 'role' => 'shop_accountant' ) );

		$this->assertTrue( user_can( $user_id, 'view_shop_reports' ), 'A shop accountant can read reports.' );
		$this->assertFalse( user_can( $user_id, 'manage_shop_settings' ), 'A shop accountant cannot manage the store.' );

		wp_set_current_user( $user_id );

		$this->assertFalse( Logs::setup( $type ), 'A shop accountant must not be able to read the logs.' );
	}

	/**
	 * A role which manages the store must still be able to read the logs.
	 *
	 * The control: without it, refusing everyone would satisfy the tests above.
	 *
	 * @dataProvider log_types
	 * @param string $type The log type.
	 */
	public function test_a_shop_manager_can_set_up_a_log_view( $type ) {
		$user_id = $this->factory->user->create( array( 'role' => 'shop_manager' ) );

		$this->assertTrue( user_can( $user_id, 'manage_shop_settings' ), 'A shop manager manages the store.' );

		wp_set_current_user( $user_id );

		$this->assertTrue( Logs::setup( $type ), 'A shop manager must be able to read the logs.' );
	}

	/**
	 * A request routed through the generic action dispatcher must not render a log view.
	 *
	 * These are page views, and the Tools screen reaches them without setting `edd-action`, so
	 * an `edd-action` naming one of them is only ever a request to render a page somewhere it
	 * was not meant to render.
	 */
	public function test_the_action_dispatcher_cannot_set_up_a_log_view() {
		wp_set_current_user( $this->factory->user->create( array( 'role' => 'administrator' ) ) );

		$_GET['edd-action'] = 'logs_view_api_requests';

		$this->assertFalse( Logs::setup( 'api-requests' ), 'A dispatched request must not set up a log view.' );

		unset( $_GET['edd-action'] );
		$_POST['edd-action'] = 'logs_view_api_requests';

		$this->assertFalse( Logs::setup( 'api-requests' ), 'A dispatched POST must not set up a log view either.' );
	}

	/**
	 * The Tools screen path must keep working for an administrator.
	 *
	 * The control for the dispatcher rule: the legitimate caller sets no `edd-action`.
	 */
	public function test_an_administrator_can_set_up_a_log_view_without_an_action() {
		wp_set_current_user( $this->factory->user->create( array( 'role' => 'administrator' ) ) );

		$this->assertTrue( Logs::setup( 'api-requests' ), 'An administrator must be able to read the logs.' );
	}

	/**
	 * The render methods must produce nothing for a role which cannot read the logs.
	 *
	 * `setup()` is the gate, but the render methods are what the hooks actually call, so this
	 * asserts the gate is reached rather than assuming it.
	 */
	public function test_the_render_methods_output_nothing_for_an_unauthorized_role() {
		wp_set_current_user( $this->factory->user->create( array( 'role' => 'shop_accountant' ) ) );

		$logs = new Logs();

		foreach ( array( 'render_file_downloads', 'render_gateway_errors', 'render_api_requests' ) as $method ) {
			ob_start();
			$logs->$method();
			$output = ob_get_clean();

			$this->assertSame( '', $output, sprintf( '%s must render nothing for an unauthorized role.', $method ) );
		}
	}
}
