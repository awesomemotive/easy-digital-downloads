<?php
/**
 * Base Section class for sanitization of a settings section.
 *
 * @since 3.3.3
 * @package EDD\Admin\Settings\Sanitize\Tabs
 */

namespace EDD\Admin\Settings\Sanitize\Tabs;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/**
 * Base Section class for sanitization.
 *
 * In each class that extends this class you can define a method called sanitize_{setting_key} to sanitize the value of that setting.
 *
 * Example:
 * To sanitize a setting named 'currency', you can define a method called sanitize_currency.
 *
 * If you need to do anything more complex, you can register a method called 'additional_processing' and do your custom processing there.
 *
 * @since 3.3.3
 */
abstract class Section {
	/**
	 * Sanitize the section.
	 *
	 * @since 3.3.3
	 * @param array $input The array of settings being saved for this section.
	 * @return array
	 */
	public static function sanitize( $input ) {
		$section_class = get_called_class();

		foreach ( $input as $key => $value ) {
			// If there is a private method for a key, use it to sanitize the value.
			$method = 'sanitize_' . $key;
			if ( method_exists( $section_class, $method ) ) {
				$input[ $key ] = $section_class::$method( $value );
			}
		}

		// Handle any additional processing for the section.
		if ( method_exists( $section_class, 'additional_processing' ) ) {
			$processed_input = $section_class::additional_processing( $input );

			$input = is_array( $processed_input )
				? $processed_input
				: $input;
		}

		return $input;
	}
}
