<?php

namespace EDD\Tests\HTML;

use EDD\Tests\PHPUnit\EDD_UnitTestCase;

/**
 * Timeline Tooltip Tests.
 *
 * @group edd_html
 */
class TimelineTooltip extends EDD_UnitTestCase {

	/**
	 * Set of predictable timestamps for the entire test.
	 *
	 * @var array
	 */
	private static $timestamps = array();

	public static function setUpBeforeClass(): void {
		parent::setUpBeforeClass();

		// Generate timestamps for the entire test.
		$i = 1;
		while ( $i <= 20 ) {
			$timestamp          = strtotime( '+' . $i . ' days' );
			self::$timestamps[ $i ] = $timestamp;
			++$i;
		}

	}

	public function test_no_items_returns_empty() {
		$tooltip = new \EDD\HTML\TimelineTooltip( array() );

		$this->assertEmpty( $tooltip->get() );
	}

	public function test_empty_items_array_returns_empty() {
		$tooltip = new \EDD\HTML\TimelineTooltip(
			array(
				'items' => array(),
			)
		);

		$this->assertEmpty( $tooltip->get() );
	}

	public function test_title_generation() {
		$tooltip = new \EDD\HTML\TimelineTooltip(
			array(
				'title' => 'Test Title',
				'items' => $this->generate_test_item_array( 1 ),
			)
		);

		$this->assertStringContainsString( 'Test', $tooltip->get() );
	}

	public function test_title_generation_kses_allowed() {
		$tooltip = new \EDD\HTML\TimelineTooltip(
			array(
				'title' => '<strong><em>Test Title</em></strong>',
				'items' => $this->generate_test_item_array( 1 ),
			)
		);

		$this->assertStringContainsString( '<strong><em>Test Title</em></strong>', $tooltip->get() );
	}

	public function test_title_generation_kses_not_allowed() {
		$tooltip = new \EDD\HTML\TimelineTooltip(
			array(
				'title' => '<script>alert("Test Title");</script>',
				'items' => $this->generate_test_item_array( 1 ),
			)
		);

		$tooltip_content = $tooltip->get();

		$this->assertStringNotContainsString( '<script>alert("Test Title");</script>', $tooltip_content );
		$this->assertStringContainsString( 'Test Title', $tooltip_content );
	}

	public function test_items_less_than_max_items_timestamp() {
		$tooltip = new \EDD\HTML\TimelineTooltip(
			array(
				'items' => $this->generate_test_item_array( 5 ),
			)
		);

		$item     = $this->get_date_string( self::$timestamps[5] );
		$expected = '<li>' . $item . '</li></ul>';

		$this->assertStringContainsString( $expected, $tooltip->get() );
	}

	public function test_items_less_than_max_items_preformatted() {
		$date    = $this->get_date_string( self::$timestamps[1] );
		$tooltip = new \EDD\HTML\TimelineTooltip(
			array(
				'items' => array( $date ),
			)
		);

		$expected = '<li>' . $date . '</li></ul>';

		$this->assertStringContainsString( $expected, $tooltip->get() );
	}

	public function test_items_more_than_max_items_less_than_cap_defaults() {
		// Create a list of items more than the default max of 5.
		$items = $this->generate_test_item_array();

		$tooltip = new \EDD\HTML\TimelineTooltip(
			array(
				'items' => $items,
			)
		);

		$last_date       = $this->get_date_string( self::$timestamps[5] );
		$over_max_date   = $this->get_date_string( self::$timestamps[6] );
		$tooltip_content = $tooltip->get();
		$this->assertStringContainsString( $last_date, $tooltip_content );
		$this->assertStringContainsString( '5 More', $tooltip_content );
		$this->assertStringNotContainsString( $over_max_date, $tooltip_content );
	}

	public function test_items_more_than_max_items_more_than_cap_defaults() {
		// Create a list of items more than the default max of 5.
		$items = $this->generate_test_item_array( 20 );

		$tooltip = new \EDD\HTML\TimelineTooltip(
			array(
				'items' => $items,
			)
		);

		$tooltip_content = $tooltip->get();
		$this->assertStringContainsString( '10+ More', $tooltip_content );
	}

	public function test_items_more_than_max_items_more_than_cap_custom() {
		// Create a list of items more than the default max of 5.
		$items = $this->generate_test_item_array( 20 );

		$tooltip = new \EDD\HTML\TimelineTooltip(
			array(
				'items'     => $items,
				'max_items' => 15,
			)
		);

		$tooltip_content = $tooltip->get();
		$this->assertStringContainsString( '5 More', $tooltip_content );
		$this->assertStringNotContainsString( '10+ More', $tooltip_content );
	}

	public function test_items_more_than_max_position_default() {
		// Create a list of items more than the default max of 5.
		$items = $this->generate_test_item_array();

		$tooltip = new \EDD\HTML\TimelineTooltip(
			array(
				'items' => $items,
			)
		);

		$date = $this->get_date_string( self::$timestamps[5] );

		$tooltip_content = $tooltip->get();
		$this->assertStringContainsString(
			'<li>' . $date . '</li><li>5 More</li></ul>',
			$tooltip_content
		);
	}

	public function test_items_more_than_max_position_bottom() {
		// Create a list of items more than the default max of 5.
		$items = $this->generate_test_item_array();

		$tooltip = new \EDD\HTML\TimelineTooltip(
			array(
				'items'         => $items,
				'more_position' => 'bottom',
			)
		);

		$date = $this->get_date_string( self::$timestamps[5] );

		$tooltip_content = $tooltip->get();
		$this->assertStringContainsString(
			'<li>' . $date . '</li><li>5 More</li></ul>',
			$tooltip_content
		);
	}

	public function test_items_more_than_max_position_top() {
		// Create a list of items more than the default max of 5.
		$items = $this->generate_test_item_array();

		$tooltip = new \EDD\HTML\TimelineTooltip(
			array(
				'items'         => $items,
				'more_position' => 'top',
			)
		);

		$date = $this->get_date_string( self::$timestamps[1] );

		$tooltip_content = $tooltip->get();
		$this->assertStringContainsString(
			'<ul class=\'timeline\'><li>5 More</li><li>' . $date . '</li>',
			$tooltip_content
		);
	}

	public function test_items_more_than_max_position_false() {
		// Create a list of items more than the default max of 5.
		$items = $this->generate_test_item_array( 6 );

		$tooltip = new \EDD\HTML\TimelineTooltip(
			array(
				'items'         => $items,
				'more_position' => false,
			)
		);

		$tooltip_content = $tooltip->get();
		$this->assertStringContainsString(
			'<li>' . $this->get_date_string( self::$timestamps[5] ) . '</li></ul>',
			$tooltip_content
		);
	}

	public function test_output_not_empty() {
		$tooltip = new \EDD\HTML\TimelineTooltip(
			array(
				'items' => $this->generate_test_item_array( 1 ),
			)
		);

		ob_start();
		$tooltip->output();
		$tooltip_content = ob_get_clean();
		$this->assertNotEmpty( $tooltip_content );
	}

	private function generate_test_item_array( $count = 10 ) {
		return array_slice( self::$timestamps, 0, $count, true );
	}

	private function get_date_string( $timestamp ) {
		return edd_date_i18n( $timestamp, get_option( 'date_format' ) . ' H:i:s' ) . ' ' . edd_get_timezone_abbr();
	}
}
