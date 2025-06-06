<?php
/**
 * Login Username Field.
 *
 * @package     EDD\Forms\Login
 * @copyright   Copyright (c) 2025, Sandhills Development, LLC
 * @license     https://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       3.3.8
 */

namespace EDD\Forms\Login;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit; // @codeCoverageIgnore

use EDD\Forms\Fields\Field;

/**
 * Login Username Field.
 *
 * @since 3.3.8
 */
class Username extends Field {

	/**
	 * Get the field ID.
	 *
	 * @since 3.3.8
	 * @return string
	 */
	public function get_id(): string {
		return 'edd_user_login';
	}

	/** Get the field label.
	 *
	 * @since 3.3.8
	 * @return string
	 */
	public function get_label(): string {
		return esc_html__( 'Username or Email', 'easy-digital-downloads' );
	}

	/**
	 * Render the input.
	 *
	 * @since 3.3.8
	 */
	public function do_input(): void {
		?>
		<div class="edd-blocks-form__control">
			<input name="edd_user_login" id="edd_user_login" class="edd-required edd-input" type="text" required/>
		</div>
		<?php
	}

	/**
	 * Get the description for the field.
	 *
	 * @since 3.3.8
	 * @return string
	 */
	public function get_description(): string {
		return '';
	}

	/**
	 * Checks if the field is required.
	 *
	 * @since 3.3.8
	 * @return bool
	 */
	protected function is_required(): bool {
		return true;
	}

	/**
	 * Get the field key.
	 *
	 * @since 3.3.8
	 * @return string
	 */
	protected function get_key(): string {
		return 'username';
	}
}
