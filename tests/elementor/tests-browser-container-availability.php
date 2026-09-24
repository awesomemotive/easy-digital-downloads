<?php
/**
 * Browse-time container-availability tests for the checkout-template editor map, against REAL Elementor.
 *
 * Runs under --extra elementor: the RealElementorFixture confirms a real
 * \Elementor\Plugin is installed and at/above the version floor. With the
 * Flexbox Container experiment ON the Elementor descriptor stays available; with
 * it OFF the browser must flip the descriptor to unavailable with the
 * 'container' reason so Import is disabled before the click.
 *
 * @package     EDD\Tests\Elementor
 * @copyright   Copyright (c) 2026, Sandhills Development, LLC
 * @license     https://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       3.7.0
 */

namespace EDD\Tests\Elementor;

use EDD\Tests\PHPUnit\EDD_UnitTestCase;
use EDD\Tests\Elementor\Support\RealElementorFixture;
use EDD\Admin\Checkout\Templates\Browser;
use EDD\Checkout\Templates\Config\Constants;

/**
 * Browse-time container-availability coverage for the editor map.
 *
 * @covers \EDD\Checkout\Templates\Traits\TemplateBrowserTrait::get_available_editors
 *
 * @group elementor
 *
 * @since 3.7.0
 */
class BrowserContainerAvailability extends EDD_UnitTestCase {

	use RealElementorFixture;

	/**
	 * Require real Elementor and act as an administrator.
	 *
	 * @return void
	 */
	public function setUp(): void {
		parent::setUp();

		$this->edd_require_real_elementor();

		$admin_id = self::factory()->user->create( array( 'role' => 'administrator' ) );
		wp_set_current_user( $admin_id );
	}

	/**
	 * Restore the captured real singleton for the next Elementor test class.
	 *
	 * @return void
	 */
	public function tearDown(): void {
		if ( ! empty( $GLOBALS['edd_elementor_real_plugin'] ) ) {
			\Elementor\Plugin::$instance = $GLOBALS['edd_elementor_real_plugin'];
		}

		parent::tearDown();
	}

	/**
	 * Invoke the trait's editor map through the Browser.
	 *
	 * @return array The Elementor descriptor from get_available_editors().
	 */
	private function elementor_descriptor(): array {
		$browser    = new Browser();
		$reflection = new \ReflectionClass( $browser );
		$method     = $reflection->getMethod( 'get_available_editors' );
		if (PHP_VERSION_ID < 80100) {
            $method->setAccessible( true );
        }

		$editors = $method->invoke( $browser );

		return $editors[ Constants::EDITOR_ELEMENTOR ];
	}

	/**
	 * Container ON: Elementor stays advertised as available.
	 *
	 * @since 3.7.0
	 * @return void
	 */
	public function test_elementor_available_when_container_on(): void {
		// The fixture pins the Container experiment active on the real singleton.
		$this->edd_boot_real_elementor();

		$descriptor = $this->elementor_descriptor();

		$this->assertTrue( $descriptor['available'] );
	}

	/**
	 * Container OFF: Elementor is flipped unavailable with the container reason.
	 *
	 * @since 3.7.0
	 * @return void
	 */
	public function test_elementor_unavailable_when_container_off(): void {
		// Elementor is loaded at/above the floor, but the Flexbox Container
		// experiment is OFF. Install a throwaway real Plugin whose only wired
		// member is an experiments manager reporting the feature inactive.
		$plugin              = ( new \ReflectionClass( \Elementor\Plugin::class ) )->newInstanceWithoutConstructor();
		$plugin->experiments = new class() {
			/**
			 * Report the Container experiment inactive.
			 *
			 * @param string $feature The feature slug.
			 * @return bool
			 */
			public function is_feature_active( $feature ) {
				return false;
			}
		};

		\Elementor\Plugin::$instance = $plugin;

		$descriptor = $this->elementor_descriptor();

		$this->assertFalse( $descriptor['available'] );
		$this->assertSame( 'container', $descriptor['reason'] );
		$this->assertNotEmpty( $descriptor['unavailable_text'] );
	}
}
