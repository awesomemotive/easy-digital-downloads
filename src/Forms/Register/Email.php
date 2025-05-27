<?php
/**
 * Registration Email Field.
 *
 * @package     EDD\Forms\Register
 * @copyright   Copyright (c) 2025, Sandhills Development, LLC
 * @license     https://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       3.3.9
 */

namespace EDD\Forms\Register;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit; // @codeCoverageIgnore

use EDD\Forms\Fields\Field;

/**
 * Registration Email Field.
 *
 * @since 3.3.9
 */
class Email extends Field {

	/**
	 * Get the field ID.
	 *
	 * @since 3.3.9
	 * @return string
	 */
	public function get_id(): string {
		return 'email';
	}

	/** Get the field label.
	 *
	 * @since 3.3.9
	 * @return string
	 */
	public function get_label(): string {
		return esc_html__( 'Email', 'easy-digital-downloads' );
	}

	/**
	 * Render the input.
	 *
	 * @since 3.3.9
	 */
	public function do_input(): void {
		?>
		<div class="edd-blocks-form__control">
			<?php
			$email = new \EDD\HTML\Text(
				array(
					'name'     => 'edd_user_email',
					'id'       => 'edd-user-email',
					'class'    => $this->get_field_classes(),
					'type'     => 'email',
					'required' => true,
				)
			);
			$email->output();
			?>
		</div>
		<?php
	}

	/**
	 * Get the description for the field.
	 *
	 * @since 3.3.9
	 * @return string
	 */
	public function get_description(): string {
		return '';
	}

	/**
	 * Checks if the field is required.
	 *
	 * @since 3.3.9
	 * @return bool
	 */
	protected function is_required(): bool {
		return true;
	}

	/**
	 * Get the field key.
	 *
	 * @since 3.3.9
	 * @return string
	 */
	protected function get_key(): string {
		return 'edd-user-email';
	}
}
