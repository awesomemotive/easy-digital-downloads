<?php
/**
 * Tests for the Email Summary test-send AJAX handler.
 *
 * @package     EDD\Tests\Ajax
 * @copyright   Copyright (c) 2026, Sandhills Development, LLC
 * @license     https://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       3.7.2
 */

namespace EDD\Tests\Ajax;

defined( 'ABSPATH' ) || exit; // @codeCoverageIgnore

use EDD\Tests\PHPUnit\Ajax_UnitTestCase;

/**
 * Tests for the Email Summary test-send AJAX handler.
 *
 * @since 3.7.2
 */
class EmailSummaryAjax extends Ajax_UnitTestCase {

	/**
	 * The handler instance whose hook the tests attach and detach.
	 *
	 * @var \EDD_Email_Summary_Admin
	 */
	protected static $handler;

	/**
	 * Count of mail sends dispatched during a test.
	 *
	 * @var int
	 */
	protected $mail_count = 0;

	/**
	 * Loads the handler class and creates the instance the tests hook.
	 *
	 * @param \WP_UnitTest_Factory $factory The shared test factory.
	 * @return void
	 */
	public static function wpSetUpBeforeClass( $factory ) {
		if ( ! class_exists( 'EDD_Email_Summary_Admin' ) ) {
			require_once EDD_PLUGIN_DIR . 'includes/admin/emails/email-summary/class-edd-email-summary-admin.php';
		}

		self::$handler = new \EDD_Email_Summary_Admin();
	}

	/**
	 * Re-attaches the handler's hook and starts counting mail sends.
	 *
	 * @return void
	 */
	public function set_up() {
		parent::set_up();

		add_action( 'wp_ajax_edd_send_test_email_summary', array( self::$handler, 'send_test_email_summary' ) );

		// Count sends instead of dispatching real mail: pre_wp_mail short-circuits wp_mail when it
		// returns a non-null value, so returning true marks the mail handled and nothing is sent.
		$this->mail_count = 0;
		add_filter( 'pre_wp_mail', array( $this, 'count_mail' ) );
	}

	/**
	 * Detaches the handler's hook and the mail counter.
	 *
	 * @return void
	 */
	public function tear_down() {
		remove_action( 'wp_ajax_edd_send_test_email_summary', array( self::$handler, 'send_test_email_summary' ) );
		remove_filter( 'pre_wp_mail', array( $this, 'count_mail' ) );

		parent::tear_down();
	}

	/**
	 * A user without manage_shop_settings is refused and no summary email is sent.
	 *
	 * @return void
	 */
	public function test_user_without_manage_shop_settings_cannot_send_test_summary() {
		$subscriber_id = self::factory()->user->create( array( 'role' => 'subscriber' ) );
		wp_set_current_user( $subscriber_id );

		$this->assertFalse(
			user_can( $subscriber_id, 'manage_shop_settings' ),
			'The actor must lack this capability for the test to mean anything.'
		);

		$response = $this->response();
		$data     = json_decode( $response, true );

		$this->assertSame( 'error', $data['status'], 'The handler must refuse the request.' );
		$this->assertSame( 0, $this->mail_count, 'No summary email may be sent when the capability check fails.' );
	}

	/**
	 * A capable user who sends no nonce is refused and no summary email is sent.
	 *
	 * @return void
	 */
	public function test_request_without_a_nonce_is_refused() {
		$admin_id = self::factory()->user->create( array( 'role' => 'administrator' ) );

		$user = new \WP_User( $admin_id );
		$user->add_cap( 'manage_shop_settings' );

		wp_set_current_user( $admin_id );

		$this->assertTrue(
			current_user_can( 'manage_shop_settings' ),
			'The actor must clear the capability gate, so that only the nonce can refuse the request.'
		);

		$response = $this->response();
		$data     = json_decode( $response, true );

		$this->assertSame( 'error', $data['status'], 'The handler must refuse the request.' );
		$this->assertSame(
			'Nonce verification failed.',
			$data['message'],
			'The refusal must come from the nonce check, not from a failed send further down the method.'
		);
		$this->assertSame( 0, $this->mail_count, 'No summary email may be sent when the nonce check fails.' );
	}

	/**
	 * A nonce minted for another action is refused, so the check cannot be satisfied by any
	 * nonce merely being present, and no summary email is sent.
	 *
	 * @return void
	 */
	public function test_request_with_a_nonce_for_another_action_is_refused() {
		$admin_id = self::factory()->user->create( array( 'role' => 'administrator' ) );

		$user = new \WP_User( $admin_id );
		$user->add_cap( 'manage_shop_settings' );

		wp_set_current_user( $admin_id );

		$this->assertTrue(
			current_user_can( 'manage_shop_settings' ),
			'The actor must clear the capability gate, so that only the nonce action can refuse the request.'
		);

		$_POST['_wpnonce'] = wp_create_nonce( 'some_other_action' );

		$response = $this->response();
		$data     = json_decode( $response, true );

		$this->assertSame( 'error', $data['status'], 'The handler must refuse a nonce minted for a different action.' );
		$this->assertSame(
			'Nonce verification failed.',
			$data['message'],
			'The refusal must come from the nonce check, not from a failed send further down the method.'
		);
		$this->assertSame( 0, $this->mail_count, 'No summary email may be sent when the nonce action does not match.' );
	}

	/**
	 * A capable user with a valid nonce sends exactly one summary email.
	 *
	 * @return void
	 */
	public function test_capable_user_with_valid_nonce_sends_one_summary() {
		$admin_id = self::factory()->user->create( array( 'role' => 'administrator' ) );

		$user = new \WP_User( $admin_id );
		$user->add_cap( 'manage_shop_settings' );

		wp_set_current_user( $admin_id );

		// _handleAjax() copies $_POST into $_REQUEST before dispatch and check_ajax_referer() reads
		// $_REQUEST, so the nonce has to be seeded in $_POST to reach the handler.
		$_POST['_wpnonce'] = wp_create_nonce( 'edd_trigger_email_summary' );

		$response = $this->response();
		$data     = json_decode( $response, true );

		$this->assertSame( 'success', $data['status'], 'The handler must accept a capable, correctly-nonced request.' );
		$this->assertSame( 1, $this->mail_count, 'Exactly one summary email must be sent.' );
	}

	/**
	 * Counts a mail send and short-circuits wp_mail so nothing is dispatched.
	 *
	 * @param null|bool $pre The pre_wp_mail short-circuit value.
	 * @return bool True, so wp_mail reports the mail as handled.
	 */
	public function count_mail( $pre ) {
		++$this->mail_count;

		return true;
	}

	/**
	 * Dispatches the handler and returns the body of whatever it exits with.
	 *
	 * @return string
	 */
	private function response() {
		try {
			return $this->_handleAjax( 'edd_send_test_email_summary' );
		} catch ( \WPAjaxDieStopException $e ) {
			return $e->getMessage();
		}
	}
}
