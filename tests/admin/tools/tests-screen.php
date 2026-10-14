<?php
/**
 * Tools screen tab dispatch tests.
 *
 * `Screen::render()` fires `do_action( 'edd_tools_tab_' . $active_tab )` for whatever tab
 * `get_active_tab()` resolves to. These tests assert an unregistered tab can never reach that
 * dispatch, that a tab registered through the `edd_tools_tabs` filter still can, and that the
 * Abilities subscriber's guard still holds independently of tab validation.
 *
 * @package     EDD\Tests\Admin\Tools
 * @copyright   Copyright (c) 2026, Sandhills Development, LLC
 * @license     https://opensource.org/licenses/gpl-2.0.php GNU Public License
 */

namespace EDD\Tests\Admin\Tools;

use EDD\Tests\PHPUnit\EDD_UnitTestCase;
use EDD\Admin\Tools\Ai;
use EDD\Admin\Tools\Screen;

/**
 * Tools screen tab dispatch tests.
 *
 * @since 3.7.2
 *
 * @group edd_tools
 */
class ScreenTabDispatch extends EDD_UnitTestCase {

	/**
	 * The `tab` query var present before the test, or null when it was absent.
	 *
	 * @var string|null
	 */
	private $original_tab;

	public function setUp(): void {
		parent::setUp();

		$this->original_tab = $_GET['tab'] ?? null;
		$this->reset_tabs_cache();
	}

	public function tearDown(): void {
		if ( is_null( $this->original_tab ) ) {
			unset( $_GET['tab'] );
		} else {
			$_GET['tab'] = $this->original_tab;
		}
		$this->reset_tabs_cache();

		parent::tearDown();
	}

	/**
	 * An unregistered tab must fall back to the default tab's hook.
	 */
	public function test_unregistered_tab_renders_the_default_tab() {
		$_GET['tab'] = 'not_a_tab';

		$fired = 0;
		add_action(
			'edd_tools_tab_general',
			function () use ( &$fired ) {
				++$fired;
			}
		);

		ob_start();
		Screen::render();
		ob_get_clean();

		$this->assertSame( 1, $fired, 'The default tab must be dispatched once.' );
	}

	/**
	 * A request without a `tab` query var must render the default tab.
	 */
	public function test_missing_tab_renders_the_default_tab() {
		unset( $_GET['tab'] );

		$fired = 0;
		add_action(
			'edd_tools_tab_general',
			function () use ( &$fired ) {
				++$fired;
			}
		);

		ob_start();
		Screen::render();
		ob_get_clean();

		$this->assertSame( 1, $fired, 'The default tab must be dispatched once.' );
	}

	/**
	 * An unregistered tab must not become a hook name of its own.
	 */
	public function test_unregistered_tab_is_not_dispatched() {
		$_GET['tab'] = 'not_a_tab';

		ob_start();
		Screen::render();
		ob_get_clean();

		$this->assertSame( 0, did_action( 'edd_tools_tab_not_a_tab' ), 'An unregistered tab name must never fire an action.' );
	}

	/**
	 * A tab added through the `edd_tools_tabs` filter must still be dispatched.
	 *
	 * The control for the two tests above: validation must check the filtered tab list, not a
	 * hardcoded array, or this would fail alongside them.
	 */
	public function test_filtered_tab_is_still_dispatched() {
		$callback = function ( $tabs ) {
			$tabs['fraud_monitor'] = 'Fraud Monitor';

			return $tabs;
		};

		add_filter( 'edd_tools_tabs', $callback );
		$this->reset_tabs_cache();

		$_GET['tab'] = 'fraud_monitor';

		$fired = 0;
		add_action(
			'edd_tools_tab_fraud_monitor',
			function () use ( &$fired ) {
				++$fired;
			}
		);

		ob_start();
		Screen::render();
		ob_get_clean();

		remove_filter( 'edd_tools_tabs', $callback );

		$this->assertSame( 1, $fired, 'A tab registered through the filter must still be dispatched.' );
	}

	/**
	 * The Abilities subscriber must stay inactive without the Abilities API, regardless of tab validation.
	 */
	public function test_ai_subscriber_is_inactive_without_the_abilities_api() {
		if ( function_exists( 'wp_register_ability' ) ) {
			$this->markTestSkipped( 'The Abilities API is available in this environment.' );
		}

		$this->assertSame( array(), Ai::get_subscribed_events() );
	}

	/**
	 * Clears the cached tabs list so a filter added within a test takes effect.
	 *
	 * @return void
	 */
	private function reset_tabs_cache(): void {
		$property = new \ReflectionProperty( Screen::class, 'tabs' );
		$property->setAccessible( true );
		$property->setValue( null, null );
	}
}
