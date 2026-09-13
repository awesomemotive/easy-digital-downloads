<?php
/**
 * Tests for EDD\Utils\ProgressBar.
 *
 * @package   EDD\Tests\Utils
 * @copyright (c) 2026, Sandhills Development, LLC
 * @license   https://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since     3.7.1
 */

namespace EDD\Tests\Utils;

use EDD\Tests\PHPUnit\EDD_UnitTestCase;
use EDD\Utils\ProgressBar as Utility;

/**
 * @coversDefaultClass \EDD\Utils\ProgressBar
 */
class ProgressBar extends EDD_UnitTestCase {

	/**
	 * Test default progress bar markup.
	 */
	public function test_default_progress_bar_markup() {
		$bar      = new Utility( array() );
		$expected = '<div class="edd-progress-bar medium"><div class="progress" style="--progress-width: 0%;"></div></div>';

		$this->assertSame( $expected, $bar->get() );
	}

	/**
	 * Test supported sizes and fallback behavior.
	 *
	 * @dataProvider size_provider
	 *
	 * @param string $size          Requested size.
	 * @param string $expected_size Expected CSS class size.
	 */
	public function test_size_options( $size, $expected_size ) {
		$bar = new Utility( array( 'size' => $size ) );
		$this->assertStringContainsString( 'edd-progress-bar ' . $expected_size, $bar->get() );
	}

	/**
	 * Data provider for test_size_options.
	 *
	 * @return array[]
	 */
	public function size_provider() {
		return array(
			'small size'            => array( 'small', 'small' ),
			'medium size'           => array( 'medium', 'medium' ),
			'large size'            => array( 'large', 'large' ),
			'invalid size fallback' => array( 'extra-huge', 'medium' ),
			'empty size fallback'   => array( '', 'medium' ),
		);
	}

	/**
	 * Test explicit current_percentage and upper limit clamping.
	 */
	public function test_explicit_percentage_and_clamping() {
		$normal = new Utility( array( 'current_percentage' => 45 ) );
		$this->assertStringContainsString( 'style="--progress-width: 45%;"', $normal->get() );

		$string_num = new Utility( array( 'current_percentage' => '80' ) );
		$this->assertStringContainsString( 'style="--progress-width: 80%;"', $string_num->get() );

		$overflow = new Utility( array( 'current_percentage' => 150 ) );
		$this->assertStringContainsString( 'style="--progress-width: 100%;"', $overflow->get() );
	}

	/**
	 * Test percentage calculated from current and total counts.
	 */
	public function test_calculated_percentage_from_counts() {
		$bar = new Utility(
			array(
				'current_count' => 30,
				'total_count'   => 100,
			)
		);
		$this->assertStringContainsString( 'style="--progress-width: 30%;"', $bar->get() );

		$clamped = new Utility(
			array(
				'current_count' => 200,
				'total_count'   => 100,
			)
		);
		$this->assertStringContainsString( 'style="--progress-width: 100%;"', $clamped->get() );

		$invalid = new Utility(
			array(
				'current_count' => 'invalid',
				'total_count'   => 100,
			)
		);
		$this->assertStringContainsString( 'style="--progress-width: 0%;"', $invalid->get() );
	}

	/**
	 * Test label rendering variations.
	 *
	 * @dataProvider label_provider
	 *
	 * @param array       $args           Configuration arguments.
	 * @param string|null $expected_label Expected inner label text or null if no label.
	 */
	public function test_label_variations( array $args, $expected_label ) {
		$bar    = new Utility( $args );
		$output = $bar->get();

		if ( null === $expected_label ) {
			$this->assertStringNotContainsString( '<div class="label">', $output );
		} else {
			$this->assertStringContainsString( '<div class="label">' . $expected_label . '</div>', $output );
		}
	}

	/**
	 * Data provider for test_label_variations.
	 *
	 * @return array[]
	 */
	public function label_provider() {
		return array(
			'no labels' => array(
				'args'           => array(
					'current_count' => 10,
					'total_count'   => 50,
				),
				'expected_label' => null,
			),
			'show current only' => array(
				'args'           => array(
					'show_current'  => true,
					'current_count' => 15,
					'total_count'   => 50,
				),
				'expected_label' => '15',
			),
			'show total only' => array(
				'args'           => array(
					'show_total'    => true,
					'current_count' => 15,
					'total_count'   => 50,
				),
				'expected_label' => '50',
			),
			'show current and total' => array(
				'args'           => array(
					'show_current'  => true,
					'show_total'    => true,
					'current_count' => 15,
					'total_count'   => 50,
				),
				'expected_label' => '15 / 50',
			),
			'show percentage only' => array(
				'args'           => array(
					'show_percentage' => true,
					'current_count'   => 25,
					'total_count'     => 100,
				),
				'expected_label' => '25%',
			),
			'show all current, total, and percentage' => array(
				'args'           => array(
					'show_current'    => true,
					'show_total'      => true,
					'show_percentage' => true,
					'current_count'   => 25,
					'total_count'     => 100,
				),
				'expected_label' => '25 / 100 (25%)',
			),
		);
	}
}
