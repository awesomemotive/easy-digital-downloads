<?php
/**
 * SubNav Tests.
 *
 * @package     EDD\Tests\Admin
 * @copyright   Copyright (c) 2025, Sandhills Development, LLC
 * @license     https://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       3.6.4
 */

namespace EDD\Tests\Admin;

use EDD\Tests\PHPUnit\EDD_UnitTestCase;
use EDD\Admin\Menu\SubNav;

/**
 * Tests for the SubNav class.
 *
 * @since 3.6.4
 * @group admin
 * @group subnav
 */
class SubNav_Tests extends EDD_UnitTestCase {

	/**
	 * Test that render outputs nothing when tabs array is empty.
	 *
	 * @since 3.6.4
	 */
	public function test_render_outputs_nothing_when_tabs_empty() {
		$subnav = new SubNav(
			array(
				'tabs'    => array(),
				'current' => '',
			)
		);

		ob_start();
		$subnav->render();
		$output = ob_get_clean();

		$this->assertEmpty( $output, 'render() should output nothing when tabs array is empty' );
	}

	/**
	 * Test that render outputs nothing when minimum tabs requirement is not met.
	 *
	 * @since 3.6.4
	 */
	public function test_render_outputs_nothing_when_minimum_tabs_not_met() {
		$subnav = new SubNav(
			array(
				'tabs'         => array(
					'tab1' => 'Tab One',
				),
				'current'      => 'tab1',
				'minimum_tabs' => 2,
			)
		);

		ob_start();
		$subnav->render();
		$output = ob_get_clean();

		$this->assertEmpty( $output, 'render() should output nothing when minimum tabs requirement is not met' );
	}

	/**
	 * Test that render outputs nothing when tabs count equals minimum but is below threshold.
	 *
	 * @since 3.6.4
	 */
	public function test_render_outputs_nothing_when_tabs_below_minimum_threshold() {
		$subnav = new SubNav(
			array(
				'tabs'         => array(
					'tab1' => 'Tab One',
					'tab2' => 'Tab Two',
				),
				'current'      => 'tab1',
				'minimum_tabs' => 3,
			)
		);

		ob_start();
		$subnav->render();
		$output = ob_get_clean();

		$this->assertEmpty( $output, 'render() should output nothing when tabs count is below minimum_tabs threshold' );
	}

	/**
	 * Test that render outputs HTML when tabs meet minimum requirement.
	 *
	 * @since 3.6.4
	 */
	public function test_render_outputs_html_when_minimum_tabs_met() {
		$subnav = new SubNav(
			array(
				'tabs'         => array(
					'tab1' => 'Tab One',
					'tab2' => 'Tab Two',
				),
				'current'      => 'tab1',
				'minimum_tabs' => 2,
			)
		);

		ob_start();
		$subnav->render();
		$output = ob_get_clean();

		$this->assertNotEmpty( $output, 'render() should output HTML when minimum tabs requirement is met' );
		$this->assertStringContainsString( 'edd-sub-nav__wrapper', $output );
		$this->assertStringContainsString( 'edd-sub-nav', $output );
	}

	/**
	 * Test that render outputs HTML with default minimum_tabs of 1.
	 *
	 * @since 3.6.4
	 */
	public function test_render_outputs_html_with_default_minimum_tabs() {
		$subnav = new SubNav(
			array(
				'tabs'    => array(
					'tab1' => 'Tab One',
				),
				'current' => 'tab1',
			)
		);

		ob_start();
		$subnav->render();
		$output = ob_get_clean();

		$this->assertNotEmpty( $output, 'render() should output HTML with default minimum_tabs of 1' );
		$this->assertStringContainsString( 'Tab One', $output );
	}

	/**
	 * Test that render marks the current tab correctly.
	 *
	 * @since 3.6.4
	 */
	public function test_render_marks_current_tab() {
		$subnav = new SubNav(
			array(
				'tabs'    => array(
					'tab1' => 'Tab One',
					'tab2' => 'Tab Two',
				),
				'current' => 'tab2',
			)
		);

		ob_start();
		$subnav->render();
		$output = ob_get_clean();

		// The current tab should have the 'current' class.
		$this->assertStringContainsString( 'class="current"', $output );
		$this->assertStringContainsString( 'Tab Two', $output );
	}

	/**
	 * Test that render applies wrapper style when provided.
	 *
	 * @since 3.6.4
	 */
	public function test_render_applies_wrapper_style() {
		$subnav = new SubNav(
			array(
				'tabs'          => array(
					'tab1' => 'Tab One',
				),
				'current'       => 'tab1',
				'wrapper_style' => 'margin-top: 10px;',
			)
		);

		ob_start();
		$subnav->render();
		$output = ob_get_clean();

		$this->assertStringContainsString( 'style="margin-top: 10px;"', $output );
	}

	/**
	 * Test that render generates correct URLs with url_args.
	 *
	 * @since 3.6.4
	 */
	public function test_render_generates_urls_with_url_args() {
		$subnav = new SubNav(
			array(
				'tabs'     => array(
					'settings' => 'Settings',
				),
				'current'  => 'settings',
				'url_args' => array(
					'page' => 'edd-tools',
					'tab'  => 'logs',
				),
				'url_key'  => 'view',
			)
		);

		ob_start();
		$subnav->render();
		$output = ob_get_clean();

		$this->assertStringContainsString( 'page=edd-tools', $output );
		$this->assertStringContainsString( 'tab=logs', $output );
		$this->assertStringContainsString( 'view=settings', $output );
	}

	/**
	 * Test minimum_tabs edge case with zero tabs.
	 *
	 * @since 3.6.4
	 */
	public function test_render_with_minimum_tabs_zero_and_no_tabs() {
		$subnav = new SubNav(
			array(
				'tabs'         => array(),
				'current'      => '',
				'minimum_tabs' => 0,
			)
		);

		ob_start();
		$subnav->render();
		$output = ob_get_clean();

		// Even with minimum_tabs of 0, empty tabs should still return nothing.
		$this->assertEmpty( $output, 'render() should output nothing when tabs array is empty regardless of minimum_tabs' );
	}
}
