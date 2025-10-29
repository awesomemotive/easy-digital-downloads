<?php

namespace EDD\Tests\Utils;

use EDD\Utils\Colors as Utility;
use EDD\Tests\PHPUnit\EDD_UnitTestCase;

class Colors extends EDD_UnitTestCase {

	/**
	 * Test that adjust_color_brightness darkens a color correctly.
	 *
	 * @since 3.5.3
	 */
	public function test_adjust_color_brightness_darkens_color() {
		$original = '#8899aa';
		$darkened = Utility::adjust_color_brightness( $original, -20 );

		$this->assertSame( '#748596', $darkened );
	}

	/**
	 * Test that adjust_color_brightness lightens a color correctly.
	 *
	 * @since 3.5.3
	 */
	public function test_adjust_color_brightness_lightens_color() {
		$original  = '#8899aa';
		$lightened = Utility::adjust_color_brightness( $original, 20 );

		$this->assertSame( '#9cadbe', $lightened );
	}

	/**
	 * Test that adjust_color_brightness works without hash symbol.
	 *
	 * @since 3.5.3
	 */
	public function test_adjust_color_brightness_works_without_hash() {
		$original = '8899aa';
		$darkened = Utility::adjust_color_brightness( $original, -20 );

		$this->assertSame( '#748596', $darkened );
	}

	/**
	 * Test that adjust_color_brightness handles 3-character shorthand.
	 *
	 * @since 3.5.3
	 */
	public function test_adjust_color_brightness_handles_shorthand_hex() {
		$original = '#fff';
		$darkened = Utility::adjust_color_brightness( $original, -20 );

		$this->assertSame( '#ebebeb', $darkened );
	}

	/**
	 * Test that adjust_color_brightness doesn't go below 0.
	 *
	 * @since 3.5.3
	 */
	public function test_adjust_color_brightness_clamps_to_zero() {
		$original = '#102030';
		$darkened = Utility::adjust_color_brightness( $original, -255 );

		$this->assertSame( '#000000', $darkened );
	}

	/**
	 * Test that adjust_color_brightness doesn't go above 255.
	 *
	 * @since 3.5.3
	 */
	public function test_adjust_color_brightness_clamps_to_255() {
		$original  = '#e0e0e0';
		$lightened = Utility::adjust_color_brightness( $original, 255 );

		$this->assertSame( '#ffffff', $lightened );
	}

	/**
	 * Test that black remains black when darkened.
	 *
	 * @since 3.5.3
	 */
	public function test_adjust_color_brightness_black_stays_black() {
		$original = '#000000';
		$darkened = Utility::adjust_color_brightness( $original, -50 );

		$this->assertSame( '#000000', $darkened );
	}

	/**
	 * Test that white remains white when lightened.
	 *
	 * @since 3.5.3
	 */
	public function test_adjust_color_brightness_white_stays_white() {
		$original  = '#ffffff';
		$lightened = Utility::adjust_color_brightness( $original, 50 );

		$this->assertSame( '#ffffff', $lightened );
	}

	/**
	 * Test that get_readable_text_color returns white for dark backgrounds.
	 *
	 * @since 3.5.3
	 */
	public function test_get_readable_text_color_returns_white_for_dark_background() {
		$dark_color = '#000000';
		$text_color = Utility::get_readable_text_color( $dark_color );

		$this->assertSame( '#ffffff', $text_color );
	}

	/**
	 * Test that get_readable_text_color returns black for light backgrounds.
	 *
	 * @since 3.5.3
	 */
	public function test_get_readable_text_color_returns_black_for_light_background() {
		$light_color = '#ffffff';
		$text_color  = Utility::get_readable_text_color( $light_color );

		$this->assertSame( '#000000', $text_color );
	}

	/**
	 * Test get_readable_text_color with a medium blue background.
	 *
	 * @since 3.5.3
	 */
	public function test_get_readable_text_color_medium_blue() {
		$blue       = '#0066cc';
		$text_color = Utility::get_readable_text_color( $blue );

		$this->assertSame( '#ffffff', $text_color );
	}

	/**
	 * Test get_readable_text_color with a yellow background.
	 *
	 * @since 3.5.3
	 */
	public function test_get_readable_text_color_yellow() {
		$yellow     = '#ffff00';
		$text_color = Utility::get_readable_text_color( $yellow );

		$this->assertSame( '#000000', $text_color );
	}

	/**
	 * Test get_readable_text_color with a red background.
	 *
	 * @since 3.5.3
	 */
	public function test_get_readable_text_color_red() {
		$red        = '#cc0000';
		$text_color = Utility::get_readable_text_color( $red );

		$this->assertSame( '#ffffff', $text_color );
	}

	/**
	 * Test get_readable_text_color with a green background.
	 *
	 * @since 3.5.3
	 */
	public function test_get_readable_text_color_green() {
		$green      = '#00ff00';
		$text_color = Utility::get_readable_text_color( $green );

		$this->assertSame( '#000000', $text_color );
	}

	/**
	 * Test get_readable_text_color with shorthand hex.
	 *
	 * @since 3.5.3
	 */
	public function test_get_readable_text_color_shorthand_hex() {
		$text_color = Utility::get_readable_text_color( '#fff' );

		$this->assertSame( '#000000', $text_color );
	}

	/**
	 * Test get_readable_text_color without hash symbol.
	 *
	 * @since 3.5.3
	 */
	public function test_get_readable_text_color_without_hash() {
		$text_color = Utility::get_readable_text_color( '000000' );

		$this->assertSame( '#ffffff', $text_color );
	}

	/**
	 * Test get_rgb_from_hex converts full hex correctly.
	 *
	 * @since 3.5.3
	 */
	public function test_get_rgb_from_hex_full_hex() {
		$rgb = $this->invoke_protected_method( 'get_rgb_from_hex', array( '#8899aa' ) );

		$this->assertSame( 136, $rgb['r'] );
		$this->assertSame( 153, $rgb['g'] );
		$this->assertSame( 170, $rgb['b'] );
	}

	/**
	 * Test get_rgb_from_hex converts shorthand hex correctly.
	 *
	 * @since 3.5.3
	 */
	public function test_get_rgb_from_hex_shorthand() {
		$rgb = $this->invoke_protected_method( 'get_rgb_from_hex', array( '#abc' ) );

		$this->assertSame( 170, $rgb['r'] );
		$this->assertSame( 187, $rgb['g'] );
		$this->assertSame( 204, $rgb['b'] );
	}

	/**
	 * Test get_rgb_from_hex works without hash symbol.
	 *
	 * @since 3.5.3
	 */
	public function test_get_rgb_from_hex_without_hash() {
		$rgb = $this->invoke_protected_method( 'get_rgb_from_hex', array( 'ff0000' ) );

		$this->assertSame( 255, $rgb['r'] );
		$this->assertSame( 0, $rgb['g'] );
		$this->assertSame( 0, $rgb['b'] );
	}

	/**
	 * Test get_rgb_from_hex with black.
	 *
	 * @since 3.5.3
	 */
	public function test_get_rgb_from_hex_black() {
		$rgb = $this->invoke_protected_method( 'get_rgb_from_hex', array( '#000000' ) );

		$this->assertSame( 0, $rgb['r'] );
		$this->assertSame( 0, $rgb['g'] );
		$this->assertSame( 0, $rgb['b'] );
	}

	/**
	 * Test get_rgb_from_hex with white.
	 *
	 * @since 3.5.3
	 */
	public function test_get_rgb_from_hex_white() {
		$rgb = $this->invoke_protected_method( 'get_rgb_from_hex', array( '#ffffff' ) );

		$this->assertSame( 255, $rgb['r'] );
		$this->assertSame( 255, $rgb['g'] );
		$this->assertSame( 255, $rgb['b'] );
	}

	/**
	 * Test get_rgb_from_hex with shorthand white.
	 *
	 * @since 3.5.3
	 */
	public function test_get_rgb_from_hex_shorthand_white() {
		$rgb = $this->invoke_protected_method( 'get_rgb_from_hex', array( '#fff' ) );

		$this->assertSame( 255, $rgb['r'] );
		$this->assertSame( 255, $rgb['g'] );
		$this->assertSame( 255, $rgb['b'] );
	}

	/**
	 * Test css_name_to_hex converts CSS color names to hex.
	 *
	 * @since 3.5.3
	 */
	public function test_css_name_to_hex_blue() {
		$hex = $this->invoke_protected_method( 'css_name_to_hex', array( 'blue' ) );

		$this->assertSame( '#428bca', $hex );
	}

	/**
	 * Test css_name_to_hex converts red to hex.
	 *
	 * @since 3.5.3
	 */
	public function test_css_name_to_hex_red() {
		$hex = $this->invoke_protected_method( 'css_name_to_hex', array( 'red' ) );

		$this->assertSame( '#d9534f', $hex );
	}

	/**
	 * Test css_name_to_hex converts green to hex.
	 *
	 * @since 3.5.3
	 */
	public function test_css_name_to_hex_green() {
		$hex = $this->invoke_protected_method( 'css_name_to_hex', array( 'green' ) );

		$this->assertSame( '#5cb85c', $hex );
	}

	/**
	 * Test css_name_to_hex handles case insensitivity.
	 *
	 * @since 3.5.3
	 */
	public function test_css_name_to_hex_case_insensitive() {
		$hex = $this->invoke_protected_method( 'css_name_to_hex', array( 'BLUE' ) );

		$this->assertSame( '#428bca', $hex );
	}

	/**
	 * Test css_name_to_hex handles whitespace.
	 *
	 * @since 3.5.3
	 */
	public function test_css_name_to_hex_with_whitespace() {
		$hex = $this->invoke_protected_method( 'css_name_to_hex', array( ' orange ' ) );

		$this->assertSame( '#ed9c28', $hex );
	}

	/**
	 * Test css_name_to_hex returns hex code unchanged.
	 *
	 * @since 3.5.3
	 */
	public function test_css_name_to_hex_passes_through_hex() {
		$hex = $this->invoke_protected_method( 'css_name_to_hex', array( '#ff5500' ) );

		$this->assertSame( '#ff5500', $hex );
	}

	/**
	 * Test css_name_to_hex returns shorthand hex unchanged.
	 *
	 * @since 3.5.3
	 */
	public function test_css_name_to_hex_passes_through_shorthand_hex() {
		$hex = $this->invoke_protected_method( 'css_name_to_hex', array( '#f50' ) );

		$this->assertSame( '#f50', $hex );
	}

	/**
	 * Test css_name_to_hex returns default for unknown color.
	 *
	 * @since 3.5.3
	 */
	public function test_css_name_to_hex_unknown_color_returns_default() {
		$hex = $this->invoke_protected_method( 'css_name_to_hex', array( 'notacolor' ) );

		$this->assertSame( '#333', $hex );
	}

	/**
	 * Test adjust_color_brightness with CSS color name.
	 *
	 * @since 3.5.3
	 */
	public function test_adjust_color_brightness_with_css_name() {
		$darkened = Utility::adjust_color_brightness( 'blue', -20 );

		// blue (#428bca) darkened by 20 should be #2e77b6.
		$this->assertSame( '#2e77b6', $darkened );
	}

	/**
	 * Test get_readable_text_color with CSS color name.
	 *
	 * @since 3.5.3
	 */
	public function test_get_readable_text_color_with_css_name() {
		$text_color = Utility::get_readable_text_color( 'blue' );

		// blue is dark, so should return white text.
		$this->assertSame( '#ffffff', $text_color );
	}

	/**
	 * Test get_button_colors returns expected structure.
	 *
	 * @since 3.5.3
	 */
	public function test_get_button_colors_returns_expected_structure() {
		$colors = Utility::get_button_colors();

		$this->assertIsArray( $colors );
		$this->assertArrayHasKey( 'buttonColor', $colors );
		$this->assertArrayHasKey( 'buttonTextColor', $colors );
		$this->assertArrayHasKey( 'buttonHoverColor', $colors );
	}

	/**
	 * Test get_button_colors returns valid hex colors.
	 *
	 * @since 3.5.3
	 */
	public function test_get_button_colors_returns_valid_hex() {
		$colors = Utility::get_button_colors();

		// Check that all color values start with #.
		$this->assertStringStartsWith( '#', $colors['buttonColor'] );
		$this->assertStringStartsWith( '#', $colors['buttonTextColor'] );
		$this->assertStringStartsWith( '#', $colors['buttonHoverColor'] );

		// Check that all color values are valid hex (7 characters including #).
		$this->assertSame( 7, strlen( $colors['buttonColor'] ) );
		$this->assertSame( 7, strlen( $colors['buttonTextColor'] ) );
		$this->assertSame( 7, strlen( $colors['buttonHoverColor'] ) );
	}

	/**
	 * Test get_button_colors with custom button color setting.
	 *
	 * @since 3.5.3
	 */
	public function test_get_button_colors_with_custom_colors() {
		// Set custom button colors.
		edd_update_option(
			'button_colors',
			array(
				'background' => '#ff5500',
				'text'       => '#ffffff',
			)
		);

		$colors = Utility::get_button_colors();

		$this->assertSame( '#ff5500', $colors['buttonColor'] );
		// Note: buttonTextColor is auto-calculated based on background, so we test that it's valid.
		$this->assertStringStartsWith( '#', $colors['buttonTextColor'] );
		// Hover should be darker than original.
		$this->assertNotSame( $colors['buttonColor'], $colors['buttonHoverColor'] );

		// Clean up.
		edd_delete_option( 'button_colors' );
	}

	/**
	 * Helper method to invoke protected methods for testing.
	 *
	 * @since 3.5.3
	 * @param string $method_name The method name to invoke.
	 * @param array  $parameters  The parameters to pass to the method.
	 * @return mixed The result of the method call.
	 */
	private function invoke_protected_method( $method_name, $parameters = array() ) {
		$reflection = new \ReflectionClass( Utility::class );
		$method     = $reflection->getMethod( $method_name );
		$method->setAccessible( true );

		return $method->invokeArgs( null, $parameters );
	}
}

