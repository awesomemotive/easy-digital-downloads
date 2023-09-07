<?php

namespace EDD\Tests\Stripe\Admin;

use \EDD\Tests\PHPUnit\EDD_UnitTestCase;
/**
 * Tests for EDD_Stripe_Admin_Notices_Registry class.
 *
 * @covers EDD_Stripe_Admin_Notices_Registry
 */
class Notices_Registry extends EDD_UnitTestCase {

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

		require_once EDDS_PLUGIN_DIR . '/includes/admin/class-notices-registry.php';

		$this->registry = new \EDD_Stripe_Admin_Notices_Registry();
	}

	/**
	 * @covers EDD_Stripe_Admin_Notices_Registry::add()
	 */
	public function test_add_should_add_with_defaults() {
		$notice = array(
			'type'        => 'success',
			'dismissible' => true,
			'message'     => 'bar',
		);

		$this->registry->add( 'foo', array(
			'message' => 'bar',
		) );

		$this->assertEqualSets( $notice, $this->registry->get_item( 'foo' ) );
	}

	/**
	 * @covers EDD_Stripe_Admin_Notices_Registry::add()
	 */
	public function test_add_with_no_message_throws_exception() {
		$this->expectException( \Exception::class );

		$this->registry->add( 'foo', array() );
	}

	/**
	 * @covers EDD_Stripe_Admin_Notices_Registry::add()
	 */
	public function test_add_validate_type() {
		$notice = array(
			'type'        => 'success',
			'dismissible' => true,
			'message'     => 'bar',
		);

		$this->registry->add( 'foo', array(
			'message' => 'bar',
			'type'    => 'baz',
		) );

		$this->assertEqualSets( $notice, $this->registry->get_item( 'foo' ) );
	}
}
