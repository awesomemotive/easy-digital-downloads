<?php
/**
 * Form Handler.
 *
 * @package EDD\Admin\Forms
 * @copyright Copyright (c) 2025, Sandhills Development, LLC
 * @license https://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since 3.3.9
 */

namespace EDD\Admin\Forms;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit; // @codeCoverageIgnore

use EDD\Admin\Forms\Field;

/**
 * Form Handler.
 */
class Handler {

	/**
	 * Render the fields.
	 *
	 * @since 3.3.9
	 * @param array        $fields The fields to render.
	 * @param array|object $data The data to pass to the fields.
	 * @return void
	 */
	public static function render_fields( array $fields, $data = array() ): void {
		foreach ( $fields as $field ) {
			self::render_field( $field, $data );
		}
	}

	/**
	 * Render an individual field.
	 *
	 * @since 3.3.9
	 * @param string       $field The field to render.
	 * @param array|object $data The data to pass to the field.
	 * @return void
	 */
	public static function render_field( string $field, $data = array() ): void {
		if ( ! class_exists( $field ) ) {
			return;
		}

		$field = new $field( $data );
		if ( $field instanceof Field ) {
			$field->render();
		}
	}
}
