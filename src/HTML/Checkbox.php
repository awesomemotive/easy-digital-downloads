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
class Checkbox extends Base {

	/**
	 * Gets the HTML for the checkbox.
	 *
	 * @since 3.2.8
	 * @return string Checkbox HTML.
	 */
	public function get() {

		ob_start();
		?>
		<input
			type="checkbox"
			name="<?php echo esc_attr( $this->args['name'] ); ?>"
			id="<?php echo esc_attr( $this->args['name'] ); ?>"
			class="<?php echo esc_attr( $this->get_css_class_string( array( $this->args['name'] ) ) ); ?>"
			<?php
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
			if ( ! empty( $this->args['value'] ) ) {
				?>
				value="<?php echo esc_attr( $this->args['value'] ); ?>"
				<?php
			}
			// Checked could mean 'on' or 1 or true, so sanitize it for checked().
			checked( true, ! empty( $this->args['current'] ) );
			?>
		/>
		<?php
		if ( ! empty( $this->args['label'] ) ) {
			?>
			<label for="<?php echo esc_attr( $this->args['name'] ); ?>"><?php echo wp_kses_post( $this->args['label'] ); ?></label>
			<?php
		}

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
			'class'   => 'edd-checkbox',
			'options' => array(
				'disabled' => false,
				'readonly' => false,
			),
			'label'   => '',
			'value'   => null,
		);
	}
}
