<?php
/**
 * Empty Cart Behavior Settings Validation
 *
 * @package     EDD\Admin\Settings
 * @copyright   Copyright (c) 2025, Sandhills Development, LLC
 * @license     https://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       3.5.0
 */

namespace EDD\Admin\Settings;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/**
 * Empty Cart Behavior Settings Validation class.
 *
 * @since 3.5.0
 */
class EmptyCartBehavior {

	/**
	 * Validate empty cart behavior settings.
	 *
	 * @since 3.5.0
	 * @param string $value The value to validate.
	 * @return string The validated value.
	 */
	public static function validate_empty_cart_behavior( $value ) {
		return 'message';
	}

	/**
	 * Validate empty cart message.
	 *
	 * @since 3.5.0
	 * @param string $value The value to validate.
	 * @return string The validated value.
	 */
	public static function validate_empty_cart_message( $value ) {
		if ( empty( $value ) || ! is_string( $value ) ) {
			$value = '';
		}

		return wp_kses_post( $value );
	}

	/**
	 * Validate empty cart redirect page.
	 *
	 * @since 3.5.0
	 * @param string $value The value to validate.
	 * @return string The validated value.
	 */
	public static function validate_empty_cart_redirect_page( $value ) {
		return is_numeric( $value ) ? $value : 0;
	}

	/**
	 * Validate empty cart redirect URL.
	 *
	 * @since 3.5.0
	 * @param string $value The value to validate.
	 * @return string The validated value.
	 */
	public static function validate_empty_cart_redirect_url( $value ) {
		return ! empty( $value ) ? $value : '';
	}

	/**
	 * Get the empty cart behavior setting.
	 *
	 * @since 3.5.0
	 * @return array The empty cart behavior setting.
	 */
	public static function get_empty_cart_behavior_setting() {
		$options = array(
			'message'       => self::get_behavior_option_label( 'message' ),
			'redirect_page' => array(
				'label'    => self::get_behavior_option_label( 'redirect_page' ),
				'disabled' => true,
			),
			'redirect_url'  => array(
				'label'    => self::get_behavior_option_label( 'redirect_url' ),
				'disabled' => true,
			),
		);

		/**
		 * If someone has set the behavior to something other than 'message',
		 * we need to delete the option. At this runtime we cannot update the option as it will
		 * result in an infinite loop.
		 */
		$current = edd_get_option( 'empty_cart_behavior', 'message' );
		if ( 'message' !== $current ) {
			edd_delete_option( 'empty_cart_behavior' );
		}

		$description = self::get_behavior_setting_description();
		return self::build_empty_cart_behavior_setting( $options, $description );
	}

	/**
	 * Get the empty cart message setting.
	 *
	 * @since 3.5.0
	 * @return array The empty cart message setting.
	 */
	public static function get_empty_cart_message_setting() {
		// Get current behavior to determine initial visibility.
		$current_behavior = edd_get_option( 'empty_cart_behavior', 'message' );
		$class            = '';

		if ( 'message' !== $current_behavior ) {
			$class = 'edd-hidden';
		}

		$description    = __( 'The message to display when the cart is empty.', 'easy-digital-downloads' );
		$being_filtered = false;

		// If the edd_empty_cart_message filter is used, add a warning that the value is being filtered.
		if ( has_filter( 'edd_empty_cart_message' ) ) {
			$being_filtered = true;
			$description   .= ' ' . __( 'This setting is being modified by the <code>edd_empty_cart_message</code> filter and changes here may not be reflected.', 'easy-digital-downloads' );
		}

		/**
		 * Filters the default empty cart message.
		 *
		 * @since 1.1.4.1
		 * @param string $message The default empty cart message.
		 * @return string The filtered empty cart message.
		 */
		$std_message = apply_filters( 'edd_empty_cart_message', __( 'Your cart is empty.', 'easy-digital-downloads' ) );

		return array(
			'id'          => 'empty_cart_message',
			'name'        => __( 'Empty Cart Message', 'easy-digital-downloads' ),
			'desc'        => $description,
			'type'        => 'rich_editor',
			'size'        => 'regular',
			'std'         => $std_message,
			'class'       => $class,
			'allow_blank' => false,
		);
	}

	/**
	 * Get the empty cart redirect page setting.
	 *
	 * @since 3.5.0
	 * @return array The empty cart redirect page setting.
	 */
	public static function get_empty_cart_redirect_page_setting() {
		return array();
	}

	/**
	 * Get the empty cart redirect URL setting.
	 *
	 * @since 3.5.0
	 * @return array The empty cart redirect URL setting.
	 */
	public static function get_empty_cart_redirect_url_setting() {
		return array();
	}

	/**
	 * Build the empty cart behavior setting.
	 *
	 * @since 3.5.0
	 * @param array  $options     The options to include in the select list.
	 * @param string $description The description to display for the setting.
	 * @return array The setting array.
	 */
	protected static function build_empty_cart_behavior_setting( $options, $description ) {
		return array(
			'id'      => 'empty_cart_behavior',
			'name'    => __( 'Empty Cart Behavior', 'easy-digital-downloads' ),
			'desc'    => $description,
			'type'    => 'select',
			'std'     => 'message',
			'options' => $options,
		);
	}

	/**
	 * Get the label for a behavior option.
	 *
	 * This allows us to not require maintain translatable strings in two places.
	 *
	 * @since 3.5.0
	 * @param string $option The option to get the label for.
	 * @return string The label for the option.
	 */
	protected static function get_behavior_option_label( $option ) {
		$labels = array(
			'message'       => _x(
				'Show a message',
				'Empty cart behavior option to show a message on the empty cart.',
				'easy-digital-downloads'
			),
			'redirect_page' => _x(
				'Redirect to a Page',
				'Empty cart behavior option to redirect to a page on the empty cart.',
				'easy-digital-downloads'
			),
			'redirect_url'  => _x(
				'Redirect to a URL',
				'Empty cart behavior option to redirect to a URL on the empty cart.',
				'easy-digital-downloads'
			),
		);

		return isset( $labels[ $option ] ) ? $labels[ $option ] : '';
	}

	/**
	 * Get the description for the empty cart behavior setting.
	 *
	 * @since 3.5.0
	 * @return string The description for the setting.
	 */
	protected static function get_behavior_setting_description() {
		return sprintf(
			/* translators: 1: opening button tag, 2: closing button tag */
			__( 'Enable more options when you %1$sUpgrade to Pro%2$s.', 'easy-digital-downloads' ),
			'<button class="edd-pro-upgrade button-link edd-promo-notice__trigger" data-id="emptycartbehavior" data-product-id="0" data-value="upgrade">',
			'</button>'
		);
	}
}
