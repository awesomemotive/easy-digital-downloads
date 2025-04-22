<?php
/**
 * Form Handler.
 *
 * @package EDD\Forms
 * @copyright Copyright (c) 2025, Sandhills Development, LLC
 * @license https://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since 3.3.8
 */

namespace EDD\Forms;

/**
 * Form Handler.
 */
class Handler {

	/**
	 * Render the fields.
	 *
	 * @param array $fields The fields to render.
	 * @param array $data The data to pass to the fields.
	 * @return void
	 */
	public static function render_fields( array $fields, array $data = array() ): void {
		foreach ( $fields as $field ) {
			self::render_field( $field, $data );
		}
	}

	/**
	 * Render an individual field.
	 *
	 * @param string $field The field to render.
	 * @param array  $data The data to pass to the field.
	 * @return void
	 */
	public static function render_field( string $field, array $data = array() ): void {
		if ( ! class_exists( $field ) ) {
			return;
		}

		$field = new $field( $data );
		if ( $field instanceof Fields\Field ) {
			$field->render();
		}
	}
}
