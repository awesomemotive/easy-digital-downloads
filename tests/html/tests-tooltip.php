<?php

namespace EDD\Tests\HTML;

use EDD\Tests\PHPUnit\EDD_UnitTestCase;

/**
 * Timeline Tooltip Tests.
 *
 * @group edd_html
 */
class Tooltip extends EDD_UnitTestCase {

	public function test_no_items_returns_empty() {
		$tooltip = new \EDD\HTML\Tooltip( array() );

		$this->assertEmpty( $tooltip->get() );
	}

	public function test_empty_content_returns_empty() {
		$tooltip = new \EDD\HTML\Tooltip(
			array(
				'content' => '',
			)
		);

		$this->assertEmpty( $tooltip->get() );
	}

	public function test_title_generation() {
		$tooltip = new \EDD\HTML\Tooltip(
			array(
				'title'   => 'Test Title',
				'content' => 'Test Content',
			)
		);

		$this->assertStringContainsString( '<span class=\'title\'>Test Title</span>', $tooltip->get() );
		$this->assertStringContainsString( 'Test Content', $tooltip->get() );
	}

	public function test_title_content_separation() {
		$tooltip = new \EDD\HTML\Tooltip(
			array(
				'title'   => 'Test Title',
				'content' => 'Test Content',
			)
		);

		$this->assertStringContainsString( ':', $tooltip->get() );
	}

	public function test_title_content_separation_has_punctuation() {
		$tooltip = new \EDD\HTML\Tooltip(
			array(
				'title'   => 'Test Title?',
				'content' => 'Test Content',
			)
		);

		$this->assertStringNotContainsString( ':', $tooltip->get() );
	}

	public function test_title_content_separation_has_line_break() {
		$tooltip = new \EDD\HTML\Tooltip(
			array(
				'title'   => 'Test Title',
				'content' => 'Test Content',
				'line_break' => true,
			)
		);
		$output = $tooltip->get();

		$this->assertStringNotContainsString( ':', $output );
		$this->assertStringContainsString( '<br>', $output );
	}
}
