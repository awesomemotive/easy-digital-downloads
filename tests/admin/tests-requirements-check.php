<?php
/**
 * Tests for the plugin row output of the requirements check.
 *
 * WordPress 7.1 moved the list table row header from the checkbox column to the
 * primary column, so the checkbox cell is now a `td` and the primary column is a
 * `th scope="row"`. These tests lock in markup and styles that work on both the
 * old and the new structure.
 *
 * @package     EDD\Tests\Admin
 * @copyright   Copyright (c) 2026, Sandhills Development, LLC
 * @license     https://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       3.7.0
 * @covers      \EDD\RequirementsCheck
 */

namespace EDD\Tests\Admin;

use EDD\Tests\PHPUnit\EDD_UnitTestCase;
use EDD\RequirementsCheck;

/**
 * @group edd_requirements
 */
class RequirementsCheckOutput extends EDD_UnitTestCase {

	/**
	 * The requirements check instance being tested.
	 *
	 * @var RequirementsCheck
	 */
	private static $requirements;

	/**
	 * Build a single instance for the whole class.
	 *
	 * The constructor hooks itself into `plugins_loaded` when requirements are met;
	 * that hook is removed again so the bootstrapper is not run a second time.
	 */
	public static function setUpBeforeClass(): void {
		parent::setUpBeforeClass();

		self::$requirements = new RequirementsCheck();

		remove_action( 'plugins_loaded', array( self::$requirements, 'bootstrap' ), 4 );
	}

	/**
	 * Gets the markup produced by the extra plugin row.
	 *
	 * @return string
	 */
	private function get_row_markup() {
		ob_start();
		self::$requirements->plugin_row_notice();

		return ob_get_clean();
	}

	/**
	 * Gets the inline styles, with all whitespace runs collapsed to a single space.
	 *
	 * @return string
	 */
	private function get_normalized_styles() {
		ob_start();
		self::$requirements->admin_head();

		return trim( preg_replace( '/\s+/', ' ', ob_get_clean() ) );
	}

	/**
	 * The checkbox cell must be a `td`, matching WordPress 7.1 row structure.
	 */
	public function test_plugin_row_notice_checkbox_cell_is_a_td() {
		$this->assertStringContainsString( '<td class="check-column">', $this->get_row_markup() );
	}

	/**
	 * The checkbox cell must no longer be emitted as a `th`.
	 */
	public function test_plugin_row_notice_does_not_emit_a_th_checkbox_cell() {
		$this->assertStringNotContainsString( '<th class="check-column">', $this->get_row_markup() );
	}

	/**
	 * The warning icon still lives inside the checkbox cell, which the styles target.
	 */
	public function test_plugin_row_notice_warning_icon_is_inside_the_check_column() {
		$this->assertMatchesRegularExpression(
			'#<td class="check-column">\s*<span class="dashicons dashicons-warning"></span>\s*</td>#',
			$this->get_row_markup()
		);
	}

	/**
	 * The notice row itself is unchanged apart from the checkbox cell.
	 */
	public function test_plugin_row_notice_keeps_primary_and_description_cells() {
		$markup = $this->get_row_markup();

		$this->assertStringContainsString( '<td class="column-primary">', $markup );
		$this->assertStringContainsString( '<td class="column-description"', $markup );
	}

	/**
	 * No style may qualify `.check-column` with an element, since the element changed.
	 */
	public function test_styles_do_not_qualify_check_column_with_an_element() {
		$styles = $this->get_normalized_styles();

		$this->assertStringNotContainsString( 'th.check-column', $styles );
		$this->assertStringNotContainsString( 'td.check-column', $styles );
	}

	/**
	 * The red accent must target the checkbox cell on both the plugin row and the
	 * notice row. Before WordPress 7.1 the plugin row was matched with a bare `th`,
	 * which now resolves to the plugin name column instead.
	 */
	public function test_red_accent_targets_the_check_column_on_both_rows() {
		$base = defined( 'EDD_PLUGIN_BASE' ) ? EDD_PLUGIN_BASE : '';

		$this->assertStringContainsString(
			sprintf(
				'.plugins tr[data-plugin="%1$s"] .check-column, .plugins .edd-requirements-row .check-column { border-left: 4px solid #dc3232 !important; }',
				$base
			),
			$this->get_normalized_styles()
		);
	}

	/**
	 * The suppressed row separator must also target the checkbox cell, not a bare `th`.
	 */
	public function test_box_shadow_reset_targets_the_check_column() {
		$base    = defined( 'EDD_PLUGIN_BASE' ) ? EDD_PLUGIN_BASE : '';
		$styles  = $this->get_normalized_styles();
		$partial = sprintf( '.plugins tr[data-plugin="%1$s"]', $base );

		$this->assertStringContainsString( $partial . ' .check-column { box-shadow: none; }', $styles );
		$this->assertStringNotContainsString( $partial . ' th { box-shadow: none; }', $styles );
	}

	/**
	 * The warning icon styles must follow the checkbox cell too.
	 */
	public function test_warning_icon_styles_target_the_check_column() {
		$styles = $this->get_normalized_styles();

		$this->assertStringContainsString(
			'.plugins .edd-requirements-row .check-column span { margin-left: 6px; color: #dc3232; }',
			$styles
		);
	}

	/**
	 * The row background still covers both cell types, so it survives either structure.
	 */
	public function test_row_background_covers_both_cell_elements() {
		$base   = defined( 'EDD_PLUGIN_BASE' ) ? EDD_PLUGIN_BASE : '';
		$styles = $this->get_normalized_styles();

		foreach ( array( 'th', 'td' ) as $element ) {
			$this->assertStringContainsString( sprintf( '.plugins tr[data-plugin="%1$s"] %2$s,', $base, $element ), $styles );
			$this->assertStringContainsString( sprintf( '.plugins .edd-requirements-row %s', $element ), $styles );
		}
	}
}
