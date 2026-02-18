<?php
/**
 * Accessibility enhancements for checkout and forms.
 *
 * @package     EDD\Checkout
 * @copyright   Copyright (c) 2026, Sandhills Development, LLC
 * @license     https://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       3.6.5
 */

namespace EDD\Checkout;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit; // @codeCoverageIgnore

use EDD\EventManagement\Subscriber;

/**
 * Accessibility class.
 *
 * Provides accessibility improvements for checkout and other EDD forms
 * to meet WCAG 3.3.2 compliance requirements.
 *
 * @since 3.6.5
 */
class Accessibility extends Subscriber {

	/**
	 * Whether the required fields notice has been rendered.
	 *
	 * @since 3.6.5
	 * @var bool
	 */
	private static $rendered = false;

	/**
	 * Gets the events this subscriber should be subscribed to.
	 *
	 * Hooks into multiple early hooks to display the required fields notice
	 * as close to the top of the checkout form as possible. The notice only
	 * renders once per page load regardless of how many hooks fire.
	 *
	 * The edd_checkout_form_top hook uses priority 0 to ensure the notice
	 * renders before the UserDetails block element (priority 1) on the
	 * checkout block, while still appearing after the discount field (priority -1).
	 *
	 * Note: edd_purchase_form_top is intentionally excluded because its content
	 * is loaded via AJAX (edd_load_ajax_gateway), creating a separate PHP request
	 * where the static $rendered flag resets. This would cause the notice to
	 * appear twice on the checkout block.
	 *
	 * @since 3.6.5
	 * @return array
	 */
	public static function get_subscribed_events() {
		return array(
			'edd_checkout_form_top'            => array( 'render_required_fields_notice', 0 ),
			'edd_register_fields_before'       => array( 'render_required_fields_notice', 5 ),
			'edd_checkout_login_fields_before' => array( 'render_required_fields_notice', 5 ),
			'edd_profile_editor_before'        => array( 'render_required_fields_notice', 5 ),
		);
	}

	/**
	 * Renders the required fields notice for accessibility (WCAG 3.3.2).
	 *
	 * Displays a notice explaining that fields marked with an asterisk (*) are required.
	 * This helps users understand the meaning of the required field indicator.
	 *
	 * The notice is controlled by the 'show_required_fields_notice' admin setting
	 * (Settings > Payments > Checkout) and is disabled by default.
	 *
	 * @since 3.6.5
	 * @return void
	 */
	public function render_required_fields_notice() {
		/**
		 * Filters whether to show the required fields notice.
		 *
		 * The admin setting 'show_required_fields_notice' determines the default.
		 * This filter can override the setting for programmatic control.
		 *
		 * @since 3.6.5
		 *
		 * @param bool $show Whether to show the notice. Default based on admin setting.
		 */
		if ( ! apply_filters( 'edd_show_required_fields_notice', (bool) edd_get_option( 'show_required_fields_notice' ) ) ) {
			return;
		}

		// Only render once per page load across all hooked actions.
		if ( self::$rendered ) {
			return;
		}
		self::$rendered = true;

		$notice_text = apply_filters(
			'edd_required_fields_notice_text',
			__( 'Fields marked with an asterisk (*) are required.', 'easy-digital-downloads' )
		);

		printf(
			'<p class="edd-required-fields-notice"><span class="edd-required-indicator" aria-hidden="true">*</span> %s</p>',
			esc_html( $notice_text )
		);
	}

	/**
	 * Resets the rendered flag for testing purposes.
	 *
	 * @since 3.6.5
	 * @return void
	 */
	public static function reset_rendered_flag() {
		self::$rendered = false;
	}
}
