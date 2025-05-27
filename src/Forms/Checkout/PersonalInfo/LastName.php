<?php
/**
 * Last Name Field.
 *
 * @package     EDD\Forms\Checkout\PersonalInfo
 * @copyright   Copyright (c) 2025, Sandhills Development, LLC
 * @license     https://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       3.3.9
 */

namespace EDD\Forms\Checkout\PersonalInfo;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit; // @codeCoverageIgnore

/**
 * The last name field.
 *
 * @since 3.3.9
 */
class LastName extends Field {

	/**
	 * Render the input.
	 *
	 * @since 3.3.9
	 */
	public function do_input(): void {
		$input = new \EDD\HTML\Text(
			wp_parse_args(
				array(
					'placeholder'  => esc_html__( 'Last Name', 'easy-digital-downloads' ),
					'value'        => $this->data['last_name'],
					'autocomplete' => 'billing last-name',
				),
				$this->get_defaults()
			)
		);
		$input->output();
	}

	/**
	 * Get the field ID.
	 *
	 * @since 3.3.9
	 * @return string
	 */
	public function get_id(): string {
		return 'edd-last';
	}

	/** Get the field label.
	 *
	 * @since 3.3.9
	 * @return string
	 */
	public function get_label(): string {
		return esc_html__( 'Last Name', 'easy-digital-downloads' );
	}

	/**
	 * Get the description.
	 *
	 * @since 3.3.9
	 * @return string
	 */
	public function get_description(): string {
		return __( 'We will use this as well to personalize your account experience.', 'easy-digital-downloads' );
	}

	/**
	 *  Get the field key.
	 *
	 * @since 3.3.9
	 * @return string
	 */
	protected function get_key(): string {
		return 'edd_last';
	}
}
