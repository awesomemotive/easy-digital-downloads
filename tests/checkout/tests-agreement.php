<?php
namespace EDD\Tests\Checkout;

use EDD\Tests\PHPUnit\EDD_UnitTestCase;

/**
 * Checkout tests.
 */
class Agreement extends EDD_UnitTestCase {
	/**
	 * On tear down, reset the agree_text option.
	 */
	public static function wpTearDownAfterClass() {
		global $edd_options;
		$edd_options['agree_text'] = '';
	}

	public function test_agreement_text() {
		global $edd_options;
		$edd_options['agree_text'] = 'I agree to the terms and conditions';
		$this->assertSame( 'I agree to the terms and conditions', edd_get_option( 'agree_text' ) );
	}

	public function test_agreement_option_sanitization() {
		edd_update_option( 'agree_text', '<script>alert("I agree to the terms and conditions")</script>' );

		$this->assertSame( 'alert("I agree to the terms and conditions")', edd_get_option( 'agree_text' ) );
	}

	public function test_agreement_text_sanitization() {
		$agreement_text = 'I agree to the terms and conditions';
		$sanitized_text = \EDD\Settings\Sanitize\Types\RichEditor::sanitize( $agreement_text );
		$this->assertSame( $agreement_text, $sanitized_text );

		$agreement_text = '<script>alert("I agree to the terms and conditions")</script>';
		$sanitized_text = \EDD\Settings\Sanitize\Types\RichEditor::sanitize( $agreement_text );
		$this->assertSame( 'alert("I agree to the terms and conditions")', $sanitized_text );
	}
}
