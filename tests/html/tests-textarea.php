<?php

namespace EDD\Tests\HTML;

use EDD\Tests\PHPUnit\EDD_UnitTestCase;

class Textarea extends EDD_UnitTestCase {

	public function test_basic_textarea() {
		$textarea = new \EDD\HTML\Textarea(
			array(
				'name'  => 'edd_textarea',
				'id'    => 'edd_textarea',
				'value' => 'Test content',
			)
		);
		$field = $textarea->get();

		$this->assertStringContainsString( 'name="edd_textarea"', $field );
		$this->assertStringContainsString( 'id="edd_textarea"', $field );
		$this->assertStringContainsString( 'Test content', $field );
		$this->assertStringContainsString( '<textarea', $field );
		$this->assertStringContainsString( '</textarea>', $field );
	}

	public function test_textarea_with_label() {
		$textarea = new \EDD\HTML\Textarea(
			array(
				'name'  => 'edd_textarea',
				'id'    => 'edd_textarea',
				'label' => 'Textarea Label',
				'value' => 'Test content',
			)
		);
		$field = $textarea->get();

		$this->assertStringContainsString( '<label class="edd-label" for="edd_textarea">', $field );
		$this->assertStringContainsString( 'Textarea Label', $field );
	}

	public function test_textarea_with_description() {
		$textarea = new \EDD\HTML\Textarea(
			array(
				'name'  => 'edd_textarea',
				'id'    => 'edd_textarea',
				'desc'  => 'This is a helpful description.',
				'value' => 'Test content',
			)
		);
		$field = $textarea->get();

		$this->assertStringContainsString( '<p class="description edd-description">', $field );
		$this->assertStringContainsString( 'This is a helpful description.', $field );
	}

	public function test_textarea_disabled() {
		$textarea = new \EDD\HTML\Textarea(
			array(
				'name'     => 'edd_textarea',
				'id'       => 'edd_textarea',
				'value'    => 'Test content',
				'disabled' => true,
			)
		);
		$field = $textarea->get();

		$this->assertStringContainsString( 'disabled', $field );
	}

	public function test_textarea_readonly() {
		$textarea = new \EDD\HTML\Textarea(
			array(
				'name'     => 'edd_textarea',
				'id'       => 'edd_textarea',
				'value'    => 'Test content',
				'readonly' => true,
			)
		);
		$field = $textarea->get();

		$this->assertStringContainsString( 'readonly', $field );
	}

	public function test_textarea_with_rows() {
		$textarea = new \EDD\HTML\Textarea(
			array(
				'name'  => 'edd_textarea',
				'id'    => 'edd_textarea',
				'value' => 'Test content',
				'rows'  => 10,
			)
		);
		$field = $textarea->get();

		$this->assertStringContainsString( 'rows="10"', $field );
	}

	public function test_textarea_with_custom_class() {
		$textarea = new \EDD\HTML\Textarea(
			array(
				'name'  => 'edd_textarea',
				'id'    => 'edd_textarea',
				'value' => 'Test content',
				'class' => 'custom-class another-class',
			)
		);
		$field = $textarea->get();

		$this->assertStringContainsString( 'custom-class', $field );
		$this->assertStringContainsString( 'another-class', $field );
	}

	public function test_textarea_has_default_class() {
		$textarea = new \EDD\HTML\Textarea(
			array(
				'name'  => 'edd_textarea',
				'id'    => 'edd_textarea',
				'value' => 'Test content',
			)
		);
		$field = $textarea->get();

		$this->assertStringContainsString( 'large-text', $field );
	}

	public function test_textarea_with_empty_value() {
		$textarea = new \EDD\HTML\Textarea(
			array(
				'name'  => 'edd_textarea',
				'id'    => 'edd_textarea',
				'value' => '',
			)
		);
		$field = $textarea->get();

		$this->assertStringContainsString( 'name="edd_textarea"', $field );
		$this->assertStringContainsString( '<textarea', $field );
		$this->assertStringContainsString( '</textarea>', $field );
	}

	public function test_textarea_with_html_in_value() {
		$textarea = new \EDD\HTML\Textarea(
			array(
				'name'  => 'edd_textarea',
				'id'    => 'edd_textarea',
				'value' => '<strong>Bold text</strong> and <em>italic</em>',
			)
		);
		$field = $textarea->get();

		// The value should be escaped
		$this->assertStringContainsString( '&lt;strong&gt;Bold text&lt;/strong&gt;', $field );
		$this->assertStringContainsString( '&lt;em&gt;italic&lt;/em&gt;', $field );
	}

	public function test_textarea_with_multiline_value() {
		$textarea = new \EDD\HTML\Textarea(
			array(
				'name'  => 'edd_textarea',
				'id'    => 'edd_textarea',
				'value' => "Line 1\nLine 2\nLine 3",
			)
		);
		$field = $textarea->get();

		$this->assertStringContainsString( 'Line 1', $field );
		$this->assertStringContainsString( 'Line 2', $field );
		$this->assertStringContainsString( 'Line 3', $field );
	}

	public function test_textarea_wrapper_span() {
		$textarea = new \EDD\HTML\Textarea(
			array(
				'name'  => 'edd_textarea',
				'id'    => 'edd_textarea',
				'value' => 'Test content',
			)
		);
		$field = $textarea->get();

		$this->assertStringContainsString( '<span id="edd-edd_textarea-wrap">', $field );
		$this->assertStringContainsString( '</span>', $field );
	}

	public function test_textarea_with_data_attributes() {
		$textarea = new \EDD\HTML\Textarea(
			array(
				'name'  => 'edd_textarea',
				'id'    => 'edd_textarea',
				'value' => 'Test content',
				'data'  => array(
					'test-attr' => 'test-value',
					'another'   => 'value',
				),
			)
		);
		$field = $textarea->get();

		$this->assertStringContainsString( 'data-test-attr="test-value"', $field );
		$this->assertStringContainsString( 'data-another="value"', $field );
	}

	public function test_textarea_with_all_options() {
		$textarea = new \EDD\HTML\Textarea(
			array(
				'name'     => 'edd_textarea_full',
				'id'       => 'edd_textarea_full',
				'value'    => 'Full test content',
				'label'    => 'Full Textarea',
				'desc'     => 'This textarea has all options set.',
				'class'    => 'custom-textarea',
				'rows'     => 15,
				'readonly' => false,
				'disabled' => false,
				'data'     => array(
					'setting' => 'value',
				),
			)
		);
		$field = $textarea->get();

		$this->assertStringContainsString( 'name="edd_textarea_full"', $field );
		$this->assertStringContainsString( 'id="edd_textarea_full"', $field );
		$this->assertStringContainsString( 'Full test content', $field );
		$this->assertStringContainsString( 'Full Textarea', $field );
		$this->assertStringContainsString( 'This textarea has all options set.', $field );
		$this->assertStringContainsString( 'custom-textarea', $field );
		$this->assertStringContainsString( 'rows="15"', $field );
		$this->assertStringContainsString( 'data-setting="value"', $field );
	}

	public function test_textarea_escapes_special_characters() {
		$textarea = new \EDD\HTML\Textarea(
			array(
				'name'  => 'edd_textarea',
				'id'    => 'edd_textarea',
				'value' => "Line with 'quotes' and \"double quotes\"",
			)
		);
		$field = $textarea->get();

		// esc_textarea should handle quotes properly
		$this->assertStringContainsString( 'quotes', $field );
		$this->assertStringContainsString( 'double quotes', $field );
	}
}

