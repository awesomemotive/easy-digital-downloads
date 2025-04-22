<?php
/**
 * Forms Field Interface.
 *
 * @package     EDD\Forms\Fields
 * @copyright   Copyright (c) 2025, Sandhills Development, LLC
 * @license     https://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       3.3.8
 */

namespace EDD\Forms\Fields;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit; // @codeCoverageIgnore

/**
 * Field Interface.
 *
 * @since 3.3.8
 */
interface FieldInterface {

	/**
	 * Get the field ID.
	 *
	 * @since 3.3.8
	 * @return string
	 */
	public function get_id(): string;

	/** Get the field label.
	 *
	 * @since 3.3.8
	 * @return string
	 */
	public function get_label(): string;

	/**
	 * Render the input.
	 *
	 * @since 3.3.8
	 */
	public function do_input(): void;

	/**
	 * Get the description for the field.
	 *
	 * @since 3.3.8
	 * @return string
	 */
	public function get_description(): string;
}
