<?php
/**
 * Tests for EDD\Utils\StatusBadge.
 *
 * @package   EDD\Tests\Utils
 * @copyright (c) 2026, Sandhills Development, LLC
 * @license   https://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since     3.7.1
 */

namespace EDD\Tests\Utils;

use EDD\Tests\PHPUnit\EDD_UnitTestCase;
use EDD\Utils\StatusBadge as Utility;

/**
 * @coversDefaultClass \EDD\Utils\StatusBadge
 */
class StatusBadge extends EDD_UnitTestCase {

	/**
	 * Test that an empty label produces an empty string.
	 */
	public function test_empty_label_returns_empty_string() {
		$badge = new Utility( array( 'label' => '' ) );
		$this->assertSame( '', $badge->get() );

		$badge_no_args = new Utility( array() );
		$this->assertSame( '', $badge_no_args->get() );
	}

	/**
	 * Test default status badge markup generation.
	 */
	public function test_default_badge_markup() {
		$badge    = new Utility( array( 'label' => 'Pending' ) );
		$expected = '<span class="edd-status-badge edd-status-badge--default"><span class="edd-status-badge__text">Pending</span></span>';

		$this->assertSame( $expected, $badge->get() );
	}

	/**
	 * Test badge with custom status identifier.
	 */
	public function test_custom_status_class() {
		$badge  = new Utility(
			array(
				'label'  => 'Completed',
				'status' => 'complete',
			)
		);
		$output = $badge->get();

		$this->assertStringContainsString( 'edd-status-badge--complete', $output );
		$this->assertStringContainsString( '<span class="edd-status-badge__text">Completed</span>', $output );
	}

	/**
	 * Test named color class vs hex color handling.
	 */
	public function test_color_class_named_vs_hex() {
		$named = new Utility(
			array(
				'label' => 'Paid',
				'color' => 'green',
			)
		);
		$this->assertStringContainsString( 'edd-status-badge--green', $named->get() );

		$hex = new Utility(
			array(
				'label' => 'Paid',
				'color' => '#28a745',
			)
		);
		$this->assertStringNotContainsString( 'edd-status-badge--#28a745', $hex->get() );
		$this->assertStringNotContainsString( '28a745', $hex->get() );
	}

	/**
	 * Test tooltip title attribute and helper class.
	 */
	public function test_tooltip_attribute_and_class() {
		$badge = new Utility(
			array(
				'label'   => 'Refunded',
				'tooltip' => 'Payment has been refunded.',
			)
		);
		$output = $badge->get();

		$this->assertStringContainsString( 'title="Payment has been refunded."', $output );
		$this->assertStringContainsString( 'edd-help-tip', $output );
	}

	/**
	 * Test custom classes as string and array.
	 */
	public function test_custom_classes() {
		$with_string = new Utility(
			array(
				'label' => 'Order',
				'class' => 'custom-badge-string',
			)
		);
		$this->assertStringContainsString( 'custom-badge-string', $with_string->get() );

		$with_array = new Utility(
			array(
				'label' => 'Order',
				'class' => array( 'first-custom-class', 'second-custom-class' ),
			)
		);
		$output_array = $with_array->get();
		$this->assertStringContainsString( 'first-custom-class', $output_array );
		$this->assertStringContainsString( 'second-custom-class', $output_array );
	}

	/**
	 * Test dashicon markup and positioning.
	 */
	public function test_dashicon_markup_and_position() {
		$after = new Utility(
			array(
				'label'    => 'Verified',
				'icon'     => 'yes',
				'dashicon' => true,
				'position' => 'after',
			)
		);
		$output_after = $after->get();
		$this->assertStringContainsString( 'dashicons dashicons-yes', $output_after );
		$this->assertGreaterThan(
			strpos( $output_after, 'edd-status-badge__text' ),
			strpos( $output_after, 'dashicons-yes' )
		);

		$before = new Utility(
			array(
				'label'    => 'Attention',
				'icon'     => 'warning',
				'dashicon' => true,
				'position' => 'before',
			)
		);
		$output_before = $before->get();
		$this->assertStringContainsString( 'dashicons dashicons-warning', $output_before );
		$this->assertLessThan(
			strpos( $output_before, 'edd-status-badge__text' ),
			strpos( $output_before, 'dashicons-warning' )
		);

		$non_dashicon = new Utility(
			array(
				'label'    => 'FontIcon',
				'icon'     => 'fa-check',
				'dashicon' => false,
			)
		);
		$output_nd = $non_dashicon->get();
		$this->assertStringContainsString( 'fa-check', $output_nd );
		$this->assertStringNotContainsString( 'dashicons-fa-check', $output_nd );
	}

	/**
	 * Test get with custom icon parameter override.
	 */
	public function test_get_with_custom_icon_parameter() {
		$badge  = new Utility( array( 'label' => 'Custom' ) );
		$output = $badge->get( '<svg class="custom-svg"></svg>' );

		$this->assertStringContainsString( '<svg class="custom-svg"></svg>', $output );
	}

	/**
	 * Test render method echoes get output.
	 */
	public function test_render_echoes_output() {
		$badge = new Utility(
			array(
				'label'  => 'Live',
				'status' => 'publish',
			)
		);

		ob_start();
		$badge->render();
		$echoed = ob_get_clean();

		$this->assertSame( $badge->get(), $echoed );
	}
}
