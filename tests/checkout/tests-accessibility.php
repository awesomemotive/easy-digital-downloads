<?php

namespace EDD\Tests\Checkout;

use EDD\Tests\PHPUnit\EDD_UnitTestCase;

/**
 * Tests for the Accessibility class.
 *
 * @group checkout
 * @group accessibility
 */
class Accessibility extends EDD_UnitTestCase {

	/**
	 * Clean up errors before each test.
	 *
	 * @return void
	 */
	public function setUp(): void {
		parent::setUp();
		edd_clear_errors();
	}

	/**
	 * Clean up errors after each test.
	 *
	 * @return void
	 */
	public function tearDown(): void {
		edd_clear_errors();
		edd_delete_option( 'show_required_fields_notice' );
		remove_all_filters( 'edd_show_required_fields_notice' );
		remove_all_filters( 'edd_required_fields_notice_text' );
		remove_all_filters( 'edd_error_class' );
		\EDD\Checkout\Accessibility::reset_rendered_flag();
		parent::tearDown();
	}

	/*
	|--------------------------------------------------------------------------
	| Subscriber Registration Tests
	|--------------------------------------------------------------------------
	*/

	/**
	 * Test that the subscribed events are correct.
	 */
	public function test_get_subscribed_events() {
		$events = \EDD\Checkout\Accessibility::get_subscribed_events();

		$this->assertArrayHasKey( 'edd_checkout_form_top', $events );
		$this->assertArrayNotHasKey( 'edd_purchase_form_top', $events, 'Should not hook into purchase form which is loaded via AJAX and causes duplicate notices' );
		$this->assertArrayHasKey( 'edd_register_fields_before', $events );
		$this->assertArrayHasKey( 'edd_checkout_login_fields_before', $events );
		$this->assertArrayHasKey( 'edd_profile_editor_before', $events );
	}

	/**
	 * Test that all subscribed events have the correct priority.
	 */
	public function test_get_subscribed_events_priority() {
		$events = \EDD\Checkout\Accessibility::get_subscribed_events();

		foreach ( $events as $event => $config ) {
			$this->assertIsArray( $config, "Event '$event' should have array config with method and priority" );
			$this->assertEquals( 'render_required_fields_notice', $config[0], "Event '$event' should call render_required_fields_notice" );

			if ( 'edd_checkout_form_top' === $event ) {
				$this->assertEquals( 0, $config[1], "Event '$event' should have priority 0 to render before UserDetails block element" );
			} else {
				$this->assertEquals( 5, $config[1], "Event '$event' should have priority 5 to render early" );
			}
		}
	}

	/**
	 * Test that the subscribed events count is exactly four.
	 */
	public function test_subscribed_events_count() {
		$events = \EDD\Checkout\Accessibility::get_subscribed_events();

		$this->assertCount( 4, $events, 'There should be exactly 4 subscribed events' );
	}

	/**
	 * Test that the Accessibility class extends Subscriber.
	 */
	public function test_accessibility_extends_subscriber() {
		$accessibility = new \EDD\Checkout\Accessibility();

		$this->assertInstanceOf( \EDD\EventManagement\Subscriber::class, $accessibility );
	}

	/**
	 * Test that the Accessibility subscriber implements SubscriberInterface.
	 */
	public function test_accessibility_implements_subscriber_interface() {
		$accessibility = new \EDD\Checkout\Accessibility();

		$this->assertInstanceOf(
			\EDD\EventManagement\SubscriberInterface::class,
			$accessibility,
			'Accessibility should implement SubscriberInterface'
		);
	}

	/**
	 * Test that the Accessibility class is loaded in Checkout Loader.
	 */
	public function test_accessibility_loaded_in_checkout_loader() {
		$loader = new \EDD\Checkout\Loader();

		// Use reflection to access protected method.
		$reflection = new \ReflectionClass( $loader );
		$method     = $reflection->getMethod( 'get_event_classes' );
		$method->setAccessible( true );

		$classes = $method->invoke( $loader );

		$has_accessibility = false;
		foreach ( $classes as $class ) {
			if ( $class instanceof \EDD\Checkout\Accessibility ) {
				$has_accessibility = true;
				break;
			}
		}

		$this->assertTrue( $has_accessibility, 'Accessibility class should be registered in Checkout Loader' );
	}

	/*
	|--------------------------------------------------------------------------
	| Admin Setting Tests
	|--------------------------------------------------------------------------
	*/

	/**
	 * Test that the notice is disabled by default when the setting is not set.
	 */
	public function test_notice_disabled_by_default() {
		$accessibility = new \EDD\Checkout\Accessibility();

		ob_start();
		$accessibility->render_required_fields_notice();
		$output = ob_get_clean();

		$this->assertEmpty( $output, 'Notice should not render when admin setting is not enabled' );
	}

	/**
	 * Test that the notice renders when the admin setting is enabled.
	 */
	public function test_notice_renders_when_setting_enabled() {
		edd_update_option( 'show_required_fields_notice', 1 );

		$accessibility = new \EDD\Checkout\Accessibility();

		ob_start();
		$accessibility->render_required_fields_notice();
		$output = ob_get_clean();

		$this->assertNotEmpty( $output, 'Notice should render when admin setting is enabled' );
		$this->assertStringContainsString( 'edd-required-fields-notice', $output );
	}

	/**
	 * Test that the filter can override the admin setting to enable the notice.
	 */
	public function test_filter_can_override_setting_to_enable() {
		// Setting is off (default).
		add_filter( 'edd_show_required_fields_notice', '__return_true' );

		$accessibility = new \EDD\Checkout\Accessibility();

		ob_start();
		$accessibility->render_required_fields_notice();
		$output = ob_get_clean();

		$this->assertNotEmpty( $output, 'Filter should be able to enable notice even when setting is off' );
	}

	/**
	 * Test that the admin setting is registered in the Payments > Checkout settings tab.
	 */
	public function test_setting_registered_in_gateways_checkout_tab() {
		$gateways = new \EDD\Admin\Settings\Tabs\Gateways();

		$reflection = new \ReflectionClass( $gateways );
		$method     = $reflection->getMethod( 'register' );
		$method->setAccessible( true );

		$settings = $method->invoke( $gateways );

		$this->assertArrayHasKey( 'checkout', $settings, 'Gateways tab should have a checkout section' );
		$this->assertArrayHasKey( 'show_required_fields_notice', $settings['checkout'], 'Required fields notice setting should be in Payments > Checkout' );

		$setting = $settings['checkout']['show_required_fields_notice'];
		$this->assertEquals( 'checkbox_toggle', $setting['type'], 'Setting should be a checkbox toggle' );
		$this->assertEquals( 'show_required_fields_notice', $setting['id'], 'Setting ID should match key' );
	}

	/*
	|--------------------------------------------------------------------------
	| Filter Hook Tests
	|--------------------------------------------------------------------------
	*/

	/**
	 * Test that the notice can be disabled via filter.
	 *
	 * This tests that the filter hook is respected even when the setting is enabled.
	 */
	public function test_required_fields_notice_filter_can_disable() {
		edd_update_option( 'show_required_fields_notice', 1 );
		add_filter( 'edd_show_required_fields_notice', '__return_false' );

		$accessibility = new \EDD\Checkout\Accessibility();

		ob_start();
		$accessibility->render_required_fields_notice();
		$output = ob_get_clean();

		$this->assertEmpty( $output, 'Filter should be able to disable notice even when setting is on' );
	}

	/**
	 * Test that the notice text can be customized via filter.
	 */
	public function test_required_fields_notice_text_filter() {
		$custom_text = 'Custom required fields text';

		add_filter(
			'edd_required_fields_notice_text',
			function () use ( $custom_text ) {
				return $custom_text;
			}
		);

		$filtered_text = apply_filters(
			'edd_required_fields_notice_text',
			__( 'Fields marked with an asterisk (*) are required.', 'easy-digital-downloads' )
		);

		$this->assertEquals( $custom_text, $filtered_text );
	}

	/**
	 * Test that the default notice text contains asterisk explanation (WCAG 3.3.2).
	 */
	public function test_default_notice_text_explains_asterisk() {
		$default_text = apply_filters(
			'edd_required_fields_notice_text',
			__( 'Fields marked with an asterisk (*) are required.', 'easy-digital-downloads' )
		);

		$this->assertStringContainsString( '*', $default_text, 'Notice should mention asterisk' );
		$this->assertStringContainsString( 'required', strtolower( $default_text ), 'Notice should explain required fields' );
	}

	/*
	|--------------------------------------------------------------------------
	| Required Fields Notice Output Tests
	|--------------------------------------------------------------------------
	*/

	/**
	 * Test that render_required_fields_notice outputs the expected HTML structure.
	 *
	 * This test verifies the complete output of the notice method including:
	 * - The paragraph wrapper with the correct CSS class
	 * - The aria-hidden asterisk indicator span
	 * - The default notice text explaining the asterisk convention
	 * - The once-per-page rendering behavior (static $rendered flag)
	 */
	public function test_render_required_fields_notice_output() {
		edd_update_option( 'show_required_fields_notice', 1 );

		$accessibility = new \EDD\Checkout\Accessibility();

		ob_start();
		$accessibility->render_required_fields_notice();
		$output = ob_get_clean();

		// Verify the notice produces output.
		$this->assertNotEmpty( $output, 'Notice should produce output' );

		// Verify the paragraph wrapper with the correct CSS class.
		$this->assertStringContainsString( '<p class="edd-required-fields-notice">', $output, 'Output should be a paragraph with the notice CSS class' );

		// Verify the aria-hidden asterisk indicator.
		$this->assertStringContainsString( 'aria-hidden="true"', $output, 'Asterisk should be hidden from screen readers' );
		$this->assertStringContainsString( 'edd-required-indicator', $output, 'Asterisk should have the indicator class' );
		$this->assertStringContainsString( '>*</span>', $output, 'The asterisk character should be present' );

		// Verify the default notice text.
		$this->assertStringContainsString(
			'Fields marked with an asterisk (*) are required.',
			$output,
			'Default text should explain the asterisk convention'
		);
	}

	/**
	 * Test that the notice only renders once per page load.
	 *
	 * The static $rendered flag should prevent duplicate notices.
	 */
	public function test_render_notice_only_renders_once() {
		edd_update_option( 'show_required_fields_notice', 1 );

		$accessibility = new \EDD\Checkout\Accessibility();

		// First call should produce output.
		ob_start();
		$accessibility->render_required_fields_notice();
		$first_output = ob_get_clean();

		$this->assertNotEmpty( $first_output, 'First call should render the notice' );

		// Second call should produce no output.
		ob_start();
		$accessibility->render_required_fields_notice();
		$second_output = ob_get_clean();

		$this->assertEmpty( $second_output, 'Second call should not render the notice again' );
	}

	/**
	 * Test that the edd_show_required_fields_notice filter disables the actual output.
	 *
	 * This verifies that the filter prevents the method from producing any HTML,
	 * not just that the filter hook exists.
	 */
	public function test_render_notice_filter_disables_output() {
		edd_update_option( 'show_required_fields_notice', 1 );

		$accessibility = new \EDD\Checkout\Accessibility();

		add_filter( 'edd_show_required_fields_notice', '__return_false' );

		ob_start();
		$accessibility->render_required_fields_notice();
		$output = ob_get_clean();

		$this->assertEmpty( $output, 'Notice should not render when filter returns false' );
	}

	/**
	 * Test that the edd_required_fields_notice_text filter customizes the rendered output.
	 *
	 * This verifies that the text filter changes the actual HTML output,
	 * not just that the filter hook exists.
	 */
	public function test_render_notice_text_filter_changes_output() {
		edd_update_option( 'show_required_fields_notice', 1 );

		$accessibility = new \EDD\Checkout\Accessibility();
		$custom_text   = 'Required fields are marked with a star.';

		add_filter(
			'edd_required_fields_notice_text',
			function () use ( $custom_text ) {
				return $custom_text;
			}
		);

		ob_start();
		$accessibility->render_required_fields_notice();
		$output = ob_get_clean();

		$this->assertStringContainsString( $custom_text, $output, 'Custom text from filter should appear in output' );
		$this->assertStringNotContainsString(
			'Fields marked with an asterisk',
			$output,
			'Default text should not appear when filter provides custom text'
		);
	}

	/**
	 * Test that notice text is escaped for safe HTML output (XSS prevention).
	 *
	 * The notice uses esc_html() on the filtered text. This test verifies
	 * that malicious HTML injected via the filter is properly escaped.
	 */
	public function test_render_notice_escapes_html_in_text() {
		edd_update_option( 'show_required_fields_notice', 1 );

		$accessibility = new \EDD\Checkout\Accessibility();

		add_filter(
			'edd_required_fields_notice_text',
			function () {
				return '<script>alert("xss")</script>';
			}
		);

		ob_start();
		$accessibility->render_required_fields_notice();
		$output = ob_get_clean();

		$this->assertStringNotContainsString( '<script>', $output, 'Script tags should be escaped' );
		$this->assertStringContainsString( '&lt;script&gt;', $output, 'HTML entities should be escaped' );
	}

	/*
	|--------------------------------------------------------------------------
	| Error HTML Accessibility Tests
	|--------------------------------------------------------------------------
	*/

	/**
	 * Test that error HTML includes ARIA attributes for accessibility.
	 */
	public function test_error_html_includes_aria_attributes() {
		edd_set_error( 'test_error', 'This is a test error message.' );

		$errors = edd_get_errors();
		$html   = edd_build_errors_html( $errors );

		$this->assertStringContainsString( 'role="alert"', $html );
		$this->assertStringContainsString( 'aria-live="assertive"', $html );
	}

	/**
	 * Test that error HTML returns empty string when no errors.
	 */
	public function test_error_html_returns_empty_when_no_errors() {
		$html = edd_build_errors_html( array() );

		$this->assertEmpty( $html );
	}

	/**
	 * Test that error HTML returns empty string with null errors.
	 */
	public function test_error_html_returns_empty_with_null() {
		$html = edd_build_errors_html( null );

		$this->assertEmpty( $html );
	}

	/**
	 * Test that multiple errors are all rendered with proper ARIA attributes.
	 */
	public function test_error_html_multiple_errors_have_aria() {
		edd_set_error( 'error_1', 'First error message.' );
		edd_set_error( 'error_2', 'Second error message.' );
		edd_set_error( 'error_3', 'Third error message.' );

		$errors = edd_get_errors();
		$html   = edd_build_errors_html( $errors );

		// Container should have ARIA attributes.
		$this->assertStringContainsString( 'role="alert"', $html );
		$this->assertStringContainsString( 'aria-live="assertive"', $html );

		// All error messages should be present.
		$this->assertStringContainsString( 'First error message.', $html );
		$this->assertStringContainsString( 'Second error message.', $html );
		$this->assertStringContainsString( 'Third error message.', $html );

		// Each error should have unique ID for aria-describedby usage.
		$this->assertStringContainsString( 'id="edd_error_error_1"', $html );
		$this->assertStringContainsString( 'id="edd_error_error_2"', $html );
		$this->assertStringContainsString( 'id="edd_error_error_3"', $html );
	}

	/**
	 * Test that error container has edd_errors class for styling.
	 */
	public function test_error_html_has_edd_errors_class() {
		edd_set_error( 'test_error', 'Test error.' );

		$errors = edd_get_errors();
		$html   = edd_build_errors_html( $errors );

		$this->assertStringContainsString( 'class="edd-errors', $html );
		$this->assertStringContainsString( 'edd-alert', $html );
		$this->assertStringContainsString( 'edd-alert-error', $html );
	}

	/**
	 * Test that error class can be filtered.
	 */
	public function test_error_html_class_filter() {
		add_filter(
			'edd_error_class',
			function ( $classes ) {
				$classes[] = 'custom-error-class';
				return $classes;
			}
		);

		edd_set_error( 'test_error', 'Test error.' );

		$errors = edd_get_errors();
		$html   = edd_build_errors_html( $errors );

		$this->assertStringContainsString( 'custom-error-class', $html );
	}

	/**
	 * Test that error IDs follow a consistent pattern for aria-describedby usage.
	 *
	 * This verifies WCAG 3.3.1 compliance - error messages can be
	 * programmatically associated with form fields.
	 */
	public function test_error_ids_follow_consistent_pattern() {
		edd_set_error( 'email_invalid', 'Please enter a valid email.' );
		edd_set_error( 'card_number', 'Your card number is incomplete.' );

		$errors = edd_get_errors();
		$html   = edd_build_errors_html( $errors );

		// Verify IDs follow the edd_error_{error_id} pattern.
		$this->assertStringContainsString( 'id="edd_error_email_invalid"', $html );
		$this->assertStringContainsString( 'id="edd_error_card_number"', $html );
	}

	/**
	 * Test that error messages include "Error" prefix for clarity.
	 */
	public function test_error_messages_have_error_prefix() {
		edd_set_error( 'test', 'Test message.' );

		$errors = edd_get_errors();
		$html   = edd_build_errors_html( $errors );

		// Verify the "Error" prefix is present for screen reader clarity.
		$this->assertStringContainsString( '<strong>' . __( 'Error', 'easy-digital-downloads' ) . '</strong>', $html );
	}

	/**
	 * Test that error container only appears when there are errors.
	 */
	public function test_error_container_only_renders_with_errors() {
		// Test with empty array.
		$html = edd_build_errors_html( array() );
		$this->assertEmpty( $html, 'Should not render container for empty array' );

		// Test with null.
		$html = edd_build_errors_html( null );
		$this->assertEmpty( $html, 'Should not render container for null' );

		// Test with false.
		$html = edd_build_errors_html( false );
		$this->assertEmpty( $html, 'Should not render container for false' );
	}

	/**
	 * Test that special characters in error messages are handled properly.
	 */
	public function test_error_messages_handle_special_characters() {
		edd_set_error( 'test_error', 'Price must be > 0 and < 1000.' );

		$errors = edd_get_errors();
		$html   = edd_build_errors_html( $errors );

		// The message should be present (escaping is handled by the caller).
		$this->assertStringContainsString( 'Price must be', $html );
	}

	/**
	 * Test that error container has exactly one role="alert" attribute.
	 *
	 * Multiple alert roles can cause screen readers to announce errors multiple times.
	 */
	public function test_error_html_has_single_alert_role() {
		edd_set_error( 'error_1', 'First error.' );
		edd_set_error( 'error_2', 'Second error.' );

		$errors = edd_get_errors();
		$html   = edd_build_errors_html( $errors );

		// There should be exactly one role="alert" in the container.
		$count = substr_count( $html, 'role="alert"' );
		$this->assertEquals( 1, $count, 'Should have exactly one role="alert" on the container, not per error' );
	}

	/**
	 * Test that error HTML wraps error messages in individual paragraph elements.
	 */
	public function test_error_html_wraps_each_error_in_paragraph() {
		edd_set_error( 'error_a', 'Error A message.' );
		edd_set_error( 'error_b', 'Error B message.' );

		$errors = edd_get_errors();
		$html   = edd_build_errors_html( $errors );

		// Each error should be in its own <p> tag with the edd_error class.
		$this->assertStringContainsString( '<p class="edd_error" id="edd_error_error_a">', $html );
		$this->assertStringContainsString( '<p class="edd_error" id="edd_error_error_b">', $html );

		// Count the number of <p> tags - should match the number of errors.
		$p_count = substr_count( $html, '<p class="edd_error"' );
		$this->assertEquals( 2, $p_count, 'Each error should be wrapped in its own paragraph' );
	}

	/**
	 * Test that the error container is a div element.
	 */
	public function test_error_html_uses_div_container() {
		edd_set_error( 'test', 'Test error.' );

		$errors = edd_get_errors();
		$html   = edd_build_errors_html( $errors );

		$this->assertStringStartsWith( '<div', $html, 'Error container should be a div element' );
		$this->assertStringEndsWith( '</div>', $html, 'Error container should close with </div>' );
	}

	/**
	 * Test that error HTML builds correctly when passed errors directly.
	 *
	 * Tests edd_build_errors_html with a manually constructed array
	 * (not via edd_set_error) to verify it works independently.
	 */
	public function test_error_html_with_direct_array() {
		$errors = array(
			'field_1' => 'First field error.',
			'field_2' => 'Second field error.',
		);
		$html   = edd_build_errors_html( $errors );

		$this->assertStringContainsString( 'role="alert"', $html );
		$this->assertStringContainsString( 'aria-live="assertive"', $html );
		$this->assertStringContainsString( 'id="edd_error_field_1"', $html );
		$this->assertStringContainsString( 'id="edd_error_field_2"', $html );
		$this->assertStringContainsString( 'First field error.', $html );
		$this->assertStringContainsString( 'Second field error.', $html );
	}

	/**
	 * Test that a single error produces valid HTML structure.
	 */
	public function test_error_html_single_error_structure() {
		$errors = array( 'single_error' => 'A single error occurred.' );
		$html   = edd_build_errors_html( $errors );

		// Verify the complete structure: opening div, one p, closing div.
		$this->assertStringStartsWith( '<div', $html );
		$this->assertStringEndsWith( '</div>', $html );
		$this->assertStringContainsString( '<p class="edd_error" id="edd_error_single_error">', $html );
		$this->assertStringContainsString( 'A single error occurred.', $html );

		// Only one paragraph element.
		$p_count = substr_count( $html, '<p class="edd_error"' );
		$this->assertEquals( 1, $p_count );
	}

	/*
	|--------------------------------------------------------------------------
	| WCAG Compliance Tests
	|--------------------------------------------------------------------------
	*/

	/**
	 * Test WCAG 3.3.1 compliance - error identification.
	 *
	 * This test ensures that when an input error is detected, the item
	 * that is in error is identified and the error is described to
	 * the user in text (WCAG 3.3.1 Level A).
	 */
	public function test_wcag_331_error_identification() {
		// Simulate a validation error scenario.
		edd_set_error( 'card_incomplete', 'Your card number is incomplete.' );

		$errors = edd_get_errors();
		$html   = edd_build_errors_html( $errors );

		// Error must be identified to assistive technology.
		$this->assertStringContainsString( 'role="alert"', $html, 'Error container should have role="alert"' );
		$this->assertStringContainsString( 'aria-live="assertive"', $html, 'Error should be announced immediately' );

		// Error must be described in text.
		$this->assertStringContainsString( 'Your card number is incomplete.', $html, 'Error message should be in text' );

		// Error should have unique ID for programmatic association.
		$this->assertStringContainsString( 'id="edd_error_card_incomplete"', $html, 'Error should have unique ID' );
	}

	/**
	 * Test WCAG 3.3.2 compliance - labels or instructions.
	 *
	 * This test ensures that labels or instructions are provided
	 * when content requires user input (WCAG 3.3.2 Level A).
	 * All hooks use low priorities to render before form fields.
	 */
	public function test_wcag_332_labels_and_instructions() {
		$events = \EDD\Checkout\Accessibility::get_subscribed_events();

		// The notice should appear at the top of forms (before input fields).
		// All hooks use low priorities (0 or 5) to render early.
		foreach ( $events as $event => $config ) {
			$this->assertLessThanOrEqual( 5, $config[1], "Event '$event' should have a low priority to render before form fields" );
		}
	}

	/*
	|--------------------------------------------------------------------------
	| Additional Edge Case Tests
	|--------------------------------------------------------------------------
	*/

	/**
	 * Test that edd_build_errors_html returns empty string for non-array input types.
	 *
	 * Verifies defensive coding against unexpected input types.
	 */
	public function test_error_html_returns_empty_for_non_array_types() {
		$this->assertEmpty( edd_build_errors_html( '' ), 'Should return empty for empty string' );
		$this->assertEmpty( edd_build_errors_html( 0 ), 'Should return empty for zero' );
		$this->assertEmpty( edd_build_errors_html( 42 ), 'Should return empty for integer' );
		$this->assertEmpty( edd_build_errors_html( true ), 'Should return empty for boolean true' );
	}

	/**
	 * Test that the admin setting has proper tooltip with WCAG reference.
	 *
	 * Verifies the tooltip description mentions WCAG compliance
	 * so administrators understand the purpose of the setting.
	 */
	public function test_setting_tooltip_mentions_wcag() {
		$gateways = new \EDD\Admin\Settings\Tabs\Gateways();

		$reflection = new \ReflectionClass( $gateways );
		$method     = $reflection->getMethod( 'register' );
		$method->setAccessible( true );

		$settings = $method->invoke( $gateways );
		$setting  = $settings['checkout']['show_required_fields_notice'];

		$this->assertArrayHasKey( 'tooltip_desc', $setting, 'Setting should have a tooltip description' );
		$this->assertStringContainsString( 'WCAG', $setting['tooltip_desc'], 'Tooltip should mention WCAG compliance' );
		$this->assertStringContainsString( '3.3.2', $setting['tooltip_desc'], 'Tooltip should reference WCAG 3.3.2' );
	}

	/**
	 * Test that the admin setting has a proper checkbox description.
	 *
	 * The 'check' key provides the label text next to the toggle.
	 */
	public function test_setting_has_checkbox_description() {
		$gateways = new \EDD\Admin\Settings\Tabs\Gateways();

		$reflection = new \ReflectionClass( $gateways );
		$method     = $reflection->getMethod( 'register' );
		$method->setAccessible( true );

		$settings = $method->invoke( $gateways );
		$setting  = $settings['checkout']['show_required_fields_notice'];

		$this->assertArrayHasKey( 'check', $setting, 'Setting should have a check description' );
		$this->assertStringContainsString( 'asterisk', $setting['check'], 'Check description should mention asterisk' );
	}

	/**
	 * Test that error error IDs with special characters are handled.
	 *
	 * Error IDs are used in HTML id attributes, so they must be safe.
	 */
	public function test_error_html_handles_numeric_error_ids() {
		$errors = array(
			0 => 'First numeric error.',
			1 => 'Second numeric error.',
		);
		$html   = edd_build_errors_html( $errors );

		$this->assertStringContainsString( 'id="edd_error_0"', $html );
		$this->assertStringContainsString( 'id="edd_error_1"', $html );
		$this->assertStringContainsString( 'First numeric error.', $html );
		$this->assertStringContainsString( 'Second numeric error.', $html );
	}

	/**
	 * Test that the subscribed events all use the same callback method.
	 *
	 * Ensures consistency: all hooks point to the same rendering method.
	 */
	public function test_all_events_use_same_callback() {
		$events = \EDD\Checkout\Accessibility::get_subscribed_events();

		foreach ( $events as $event => $config ) {
			$this->assertEquals(
				'render_required_fields_notice',
				$config[0],
				"Event '$event' should use render_required_fields_notice callback"
			);
		}
	}

	/**
	 * Test that the notice includes both checkout and non-checkout form hooks.
	 *
	 * Verifies the notice applies beyond just checkout to registration,
	 * login, and profile editor forms as well.
	 */
	public function test_notice_covers_all_form_types() {
		$events     = \EDD\Checkout\Accessibility::get_subscribed_events();
		$event_keys = array_keys( $events );

		// Checkout form.
		$this->assertContains( 'edd_checkout_form_top', $event_keys, 'Should hook into checkout form' );

		// Registration form.
		$this->assertContains( 'edd_register_fields_before', $event_keys, 'Should hook into registration form' );

		// Login form.
		$this->assertContains( 'edd_checkout_login_fields_before', $event_keys, 'Should hook into login form' );

		// Profile editor.
		$this->assertContains( 'edd_profile_editor_before', $event_keys, 'Should hook into profile editor' );
	}

	/**
	 * Test that the notice output is valid HTML (properly closed tags).
	 *
	 * Ensures the HTML structure is well-formed for proper rendering.
	 */
	public function test_render_notice_produces_valid_html() {
		edd_update_option( 'show_required_fields_notice', 1 );

		$accessibility = new \EDD\Checkout\Accessibility();

		ob_start();
		$accessibility->render_required_fields_notice();
		$output = ob_get_clean();

		// Verify opening and closing tags match.
		$this->assertStringContainsString( '<p ', $output, 'Should have opening p tag' );
		$this->assertStringContainsString( '</p>', $output, 'Should have closing p tag' );
		$this->assertStringContainsString( '<span ', $output, 'Should have opening span tag' );
		$this->assertStringContainsString( '</span>', $output, 'Should have closing span tag' );

		// Count opening vs closing tags.
		$this->assertEquals( substr_count( $output, '<p ' ), substr_count( $output, '</p>' ), 'Opening and closing p tags should match' );
		$this->assertEquals( substr_count( $output, '<span ' ), substr_count( $output, '</span>' ), 'Opening and closing span tags should match' );
	}

	/**
	 * Test that the render method is public and callable.
	 *
	 * The method must be public since WordPress hooks call it directly.
	 */
	public function test_render_method_is_public() {
		$reflection = new \ReflectionMethod( \EDD\Checkout\Accessibility::class, 'render_required_fields_notice' );

		$this->assertTrue( $reflection->isPublic(), 'render_required_fields_notice must be public for WordPress hooks' );
	}

	/**
	 * Test that the get_subscribed_events method is static.
	 *
	 * The SubscriberInterface requires this method to be static.
	 */
	public function test_get_subscribed_events_is_static() {
		$reflection = new \ReflectionMethod( \EDD\Checkout\Accessibility::class, 'get_subscribed_events' );

		$this->assertTrue( $reflection->isStatic(), 'get_subscribed_events must be static per SubscriberInterface' );
	}

	/**
	 * Test that error HTML with a single error still gets ARIA attributes.
	 *
	 * Ensures ARIA is not conditionally applied only for multiple errors.
	 */
	public function test_single_error_has_aria_attributes() {
		$errors = array( 'only_error' => 'The only error.' );
		$html   = edd_build_errors_html( $errors );

		$this->assertStringContainsString( 'role="alert"', $html, 'Single error should have role="alert"' );
		$this->assertStringContainsString( 'aria-live="assertive"', $html, 'Single error should have aria-live="assertive"' );
		$this->assertEquals( 1, substr_count( $html, 'role="alert"' ), 'Should have exactly one role="alert"' );
	}

	/**
	 * Test that the edd_error_class filter receives default classes.
	 *
	 * Verifies the filter provides the expected default classes as its base.
	 */
	public function test_error_class_filter_receives_default_classes() {
		$received_classes = null;

		add_filter(
			'edd_error_class',
			function ( $classes ) use ( &$received_classes ) {
				$received_classes = $classes;
				return $classes;
			}
		);

		edd_set_error( 'test', 'Test error.' );
		$errors = edd_get_errors();
		edd_build_errors_html( $errors );

		$this->assertIsArray( $received_classes, 'Filter should receive an array' );
		$this->assertContains( 'edd-errors', $received_classes, 'Default classes should include edd-errors' );
		$this->assertContains( 'edd-alert', $received_classes, 'Default classes should include edd-alert' );
		$this->assertContains( 'edd-alert-error', $received_classes, 'Default classes should include edd-alert-error' );
	}

	/**
	 * Test that the notice does not render when setting is explicitly set to empty string.
	 *
	 * WordPress stores unchecked checkbox toggles as empty strings.
	 */
	public function test_notice_disabled_with_empty_string_setting() {
		edd_update_option( 'show_required_fields_notice', '' );

		$accessibility = new \EDD\Checkout\Accessibility();

		ob_start();
		$accessibility->render_required_fields_notice();
		$output = ob_get_clean();

		$this->assertEmpty( $output, 'Notice should not render when setting is empty string' );
	}

	/**
	 * Test that the notice does not render when setting is explicitly set to 0.
	 *
	 * Verifies the boolean cast of the setting works correctly for falsy values.
	 */
	public function test_notice_disabled_with_zero_setting() {
		edd_update_option( 'show_required_fields_notice', 0 );

		$accessibility = new \EDD\Checkout\Accessibility();

		ob_start();
		$accessibility->render_required_fields_notice();
		$output = ob_get_clean();

		$this->assertEmpty( $output, 'Notice should not render when setting is 0' );
	}

	/**
	 * Test that the notice text filter receives the default string.
	 *
	 * Verifies the filter receives the correct default value as its parameter.
	 */
	public function test_notice_text_filter_receives_default() {
		$received_default = null;

		edd_update_option( 'show_required_fields_notice', 1 );

		add_filter(
			'edd_required_fields_notice_text',
			function ( $text ) use ( &$received_default ) {
				$received_default = $text;
				return $text;
			}
		);

		$accessibility = new \EDD\Checkout\Accessibility();

		ob_start();
		$accessibility->render_required_fields_notice();
		ob_get_clean();

		$this->assertNotNull( $received_default, 'Filter should have been called' );
		$this->assertStringContainsString( 'asterisk', $received_default, 'Default text should mention asterisk' );
		$this->assertStringContainsString( 'required', $received_default, 'Default text should mention required' );
	}

	/**
	 * Test that each error in the HTML output includes the Error prefix label.
	 *
	 * Verifies every error message has the "Error:" prefix for screen reader clarity.
	 */
	public function test_each_error_has_error_prefix() {
		edd_set_error( 'err_1', 'First error.' );
		edd_set_error( 'err_2', 'Second error.' );
		edd_set_error( 'err_3', 'Third error.' );

		$errors = edd_get_errors();
		$html   = edd_build_errors_html( $errors );

		$error_prefix = '<strong>' . __( 'Error', 'easy-digital-downloads' ) . '</strong>';
		$prefix_count = substr_count( $html, $error_prefix );

		$this->assertEquals( 3, $prefix_count, 'Each of the 3 errors should have the Error prefix' );
	}
}
