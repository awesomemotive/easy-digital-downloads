<?php
/**
 * Removing a cron component through the edd_cron_components filter.
 *
 * Guarded callbacks are registered as closures, so remove_subscriber() cannot unhook them. The
 * filter is the supported route, and these tests pin that it keeps working.
 *
 * @package EDD\Tests\Cron
 * @since   3.7.2
 */

namespace EDD\Tests\Cron;

use EDD\Tests\PHPUnit\EDD_UnitTestCase;
use EDD\Cron\Loader;
use EDD\Cron\Components\EmailSummaries;

/**
 * ComponentRemoval Tests
 *
 * @group edd_cron
 */
class ComponentRemoval extends EDD_UnitTestCase {

	/**
	 * Set up each test.
	 */
	public function set_up() {
		parent::set_up();

		// The bootstrap already wired every component, so start each test from an unwired hook.
		remove_all_actions( EmailSummaries::CRON_EVENT_NAME );
	}

	/**
	 * Tear down each test.
	 */
	public function tear_down() {
		parent::tear_down();
	}

	/**
	 * The route the Cron EventManager docblock points site owners at.
	 */
	public function test_component_filtered_out_of_edd_cron_components_is_not_wired() {
		add_filter( 'edd_cron_components', array( $this, 'remove_email_summaries' ) );

		( new Loader() )->load_components();

		$this->assertFalse( has_action( EmailSummaries::CRON_EVENT_NAME ) );
	}

	/**
	 * Without this, the removal test would also pass if load_components() wired nothing.
	 */
	public function test_component_left_in_edd_cron_components_is_wired() {
		( new Loader() )->load_components();

		$this->assertTrue( has_action( EmailSummaries::CRON_EVENT_NAME ) );
	}

	/**
	 * Drops the Email Summaries component, as a site owner's snippet would.
	 *
	 * @param array $components The registered components, as class names or instances.
	 * @return array
	 */
	public function remove_email_summaries( $components ) {
		return array_filter(
			$components,
			function ( $component ) {
				return ! is_a( $component, EmailSummaries::class, true );
			}
		);
	}
}
