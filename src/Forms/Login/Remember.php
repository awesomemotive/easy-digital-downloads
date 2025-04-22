<?php
/**
 * Login Remember Field.
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
 * Login Remember Field.
 *
 * @since 3.3.8
 */
class Remember extends Field {

	/**
	 * Get the field ID.
	 *
	 * @since 3.3.8
	 * @return string
	 */
	public function get_id(): string {
		return 'rememberme';
	}

	/** Get the field label.
	 *
	 * @since 3.3.8
	 * @return string
	 */
	public function get_label(): string {
		return esc_html__( 'Remember Me', 'easy-digital-downloads' );
	}

	/**
	 * Render the field.
	 * The checkout fields have an ID on the wrapper div and the
	 * description is printed before the input.
	 *
	 * @return void
	 */
	public function render(): void {
		$classes = $this->get_form_group_classes();
		?>
		<div
			<?php if ( ! empty( $classes ) ) : ?>
				class="<?php echo esc_attr( $this->get_css_class_string( $classes ) ); ?>"
			<?php endif; ?>
		>
			<?php $this->do_input(); ?>
		</div>
		<?php
	}

	/**
	 * Render the input.
	 *
	 * @since 3.3.8
	 */
	public function do_input(): void {
		?>
		<div class="edd-blocks-form__control">
			<input name="rememberme" type="checkbox" id="rememberme" value="forever" />
			<?php $this->do_label(); ?>
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
	 * Get the field key.
	 *
	 * @since 3.3.8
	 * @return string
	 */
	protected function get_key(): string {
		return 'remember';
	}
}
