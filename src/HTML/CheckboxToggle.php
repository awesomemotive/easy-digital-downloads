<?php
/**
 * Checkbox HTML Element
 *
 * @package EDD
 * @subpackage HTML
 * @since 3.2.8
 */

namespace EDD\HTML;

defined( 'ABSPATH' ) || exit;

/**
 * Class Checkbox
 *
 * @since 3.2.8
 * @package EDD\HTML
 */
class CheckboxToggle extends Base {

	/**
	 * Gets the HTML for the checkbox.
	 *
	 * @since 3.2.8
	 * @return string Checkbox HTML.
	 */
	public function get() {

		ob_start();
		?>
		<div class="<?php echo esc_attr( $this->get_css_class_string( array( $this->args['name'] ) ) ); ?>">
			<input
				type="checkbox"
				name="<?php echo esc_attr( $this->args['name'] ); ?>"
				id="<?php echo esc_attr( $this->args['name'] ); ?>"
				value="1"
				<?php
				checked( true, ! empty( $this->args['current'] ) );
				if ( ! empty( $this->args['options']['disabled'] ) ) {
					?>
					disabled
					<?php
				}
				if ( ! empty( $this->args['options']['readonly'] ) ) {
					?>
					readonly
					<?php
				}
				?>
			/>
			<label for="<?php echo esc_attr( $this->args['name'] ); ?>"><?php echo wp_kses_post( $this->args['label'] ); ?></label>
		</div>
		<?php

		return ob_get_clean();
	}

	/**
	 * Default arguments for the checkbox.
	 *
	 * @since 3.2.8
	 * @return array
	 */
	protected function defaults() {
		return array(
			'name'    => null,
			'current' => null,
			'class'   => '',
			'options' => array(
				'disabled' => false,
				'readonly' => false,
			),
			'label'   => '',
			'value'   => 1,
		);
	}

	/**
	 * Gets the base CSS classes for the select element.
	 *
	 * @since 3.2.8
	 * @return array
	 */
	protected function get_base_classes(): array {
		return array(
			'edd-toggle',
		);
	}
}
