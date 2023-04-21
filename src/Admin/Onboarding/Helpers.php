<?php
/**
 * Onboarding Wizard Helpers.
 *
 * @package     EDD
 * @subpackage  Emails
 * @copyright   Copyright (c) 2022, Easy Digital Downloads, LLC
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       3.1
 */

namespace EDD\Admin\Onboarding;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

trait Helpers {
	/**
	 * Extract requested fields from the settings array.
	 *
	 * @param array $sections Array of fields in sections that we want to extract.
	 *
	 * @since 3.1.1
	 * @return bool
	 */
	public function extract_settings_fields( $sections = array() ) {
		global $wp_settings_fields;
		$extracted_fields = array();

		// Fields extraction.
		foreach ( $sections as $section_name => $section ) {
			if ( ! empty( $wp_settings_fields[ $section_name ][ $section_name ] ) ) {
				foreach ( $section as $field ) {
					$field_name = "edd_settings[{$field}]";
					if ( array_key_exists( $field_name, $wp_settings_fields[ $section_name ][ $section_name ] ) ) {
						$extracted_fields[ $field_name ] = $wp_settings_fields[ $section_name ][ $section_name ][ $field_name ];
					}
				}
			}
		}

		return $extracted_fields;
	}

	/**
	 * Get fields HTML.
	 *
	 * @param array $screen_settings Fields.
	 *
	 * @since 3.1.1
	 */
	public function settings_html( $screen_settings ) {
		foreach ( $screen_settings as $field ) :
			$class = '';

			if ( ! empty( $field['args']['class'] ) ) {
				$class = ' class="' . esc_attr( $field['args']['class'] ) . '"';
			}

			echo "<tr{$class}>";
				if ( ! empty( $field['args']['label_for'] ) ) {
					echo '<th scope="row"><label for="' . esc_attr( $field['args']['label_for'] ) . '">' . $field['title'] . '</label></th>';
				} else {
					echo '<th scope="row">' . $field['title'] . '</th>';
				}

				echo '<td>';
				if ( ! empty( $field['args']['std'] ) ) {
					$field['args']['allow_blank'] = false;
				}
				call_user_func( $field['callback'], $field['args'] );
				echo '</td>';
			echo '</tr>';
		endforeach;
	}
}
