<?php

namespace EDD\Tests\Stripe\Admin;

use \EDD\Tests\PHPUnit\EDD_UnitTestCase;
use SteveGrunwell\PHPUnit_Markup_Assertions\MarkupAssertionsTrait;

/**
 * Tests for EDD_Stripe_Admin_Notices class.
 *
 * @covers EDD_Stripe_Admin_Notices
 */
class Notices extends EDD_UnitTestCase {

	use MarkupAssertionsTrait;

	/**
	 * Notices test fixture.
	 *
	 * @access protected
	 * @var    EDD_Stripe_Admin_Notices
	 */
	protected $notices;

	/**
	 * Registry test fixture.
	 *
	 * @access protected
	 * @var    EDD_Stripe_Admin_Notices_Registry
	 */
	protected $registry;

	/**
	 * Set up fixtures once.
	 */
	public function setUp(): void {
		parent::setUp();

		require_once EDDS_PLUGIN_DIR . '/includes/admin/class-notices.php';
		require_once EDDS_PLUGIN_DIR . '/includes/admin/class-notices-registry.php';

		$this->registry = new \EDD_Stripe_Admin_Notices_Registry();
		$this->notices  = new \EDD_Stripe_Admin_Notices( $this->registry );
	}

	/**
	 * @covers EDD_Stripe_Admin_Notices::get_dismissed_option_name()
	 */
	public function test_get_dismissed_option_name() {
		$this->assertEquals( 'edds_notice_foo_dismissed', $this->notices->get_dismissed_option_name( 'foo' ) );
	}

	/**
	 * @covers EDD_Stripe_Admin_Notices::get_dismissed_option_name()
	 */
	public function test_get_dismissed_option_name_backwards_compat() {
		$this->assertEquals( 'edds_stripe_connect_intro_notice_dismissed', $this->notices->get_dismissed_option_name( 'stripe-connect' ) );
	}

	/**
	 * @covers EDD_Stripe_Admin_Notices::build()
	 */
	public function test_build() {
		$this->registry->add( 'foo', array(
			'message'     => 'bar',
			'type'        => 'info',
			'dismissible' => true,
		) );

		$output = $this->notices->build( 'foo' );

		$this->assertContainsSelector( '#edds-foo-notice', $output );
		$this->assertContainsSelector( '.edds-admin-notice', $output );
		$this->assertContainsSelector( '.notice', $output );
		$this->assertContainsSelector( '.notice-info', $output );

		$this->assertElementContains( 'bar', 'div', $output );
	}

	/**
	 * @covers EDD_Stripe_Admin_Notices::build()
	 */
	public function test_build_with_callable_message() {
		$this->registry->add( 'foo', array(
			'message' => function() {
				return 'bar';
			},
		) );

		$output = $this->notices->build( 'foo' );

		$this->assertElementContains( 'bar', 'div', $output );
	}

	/**
	 * @covers EDD_Stripe_Admin_Notices::build()
	 */
	public function test_build_dismissed_notice_is_empty() {
		$this->registry->add( 'foo', array(
			'message'     => 'bar',
			'type'        => 'info',
			'dismissible' => true,
		) );

		$this->notices->dismiss( 'foo' );

		$output = $this->notices->build( 'foo' );

		$this->assertEquals( '', $output );
	}

}
