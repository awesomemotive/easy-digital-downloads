<?php
/**
 * Number HTML Element
 *
 * @package EDD
 * @subpackage HTML
 * @since 3.3.6
 */

namespace EDD\HTML;

defined( 'ABSPATH' ) || exit;

/**
 * Class Number
 *
 * @since 3.3.6
 * @package EDD\HTML
 */
class Number extends Base {

	/**
	 * Gets the HTML for the text field.
	 *
	 * @since 1.5.2
	 * @return string Number field
	 */
	public function get() {
		ob_start();

		if ( ! empty( $this->args['label'] ) ) {
			?>
			<label class="edd-label" for="<?php echo edd_sanitize_key( $this->args['id'] ); ?>">
				<?php echo esc_html( $this->args['label'] ); ?>
			</label>
			<?php
		}
		?>
		<input
			type="number"
			name="<?php echo esc_attr( $this->args['name'] ); ?>"
			id="<?php echo esc_attr( $this->args['id'] ); ?>"
			value="<?php echo esc_attr( $this->args['value'] ); ?>"
			placeholder="<?php echo esc_attr( $this->args['placeholder'] ); ?>"
			class="<?php echo esc_attr( $this->get_css_class_string() ); ?>"
			<?php
			echo $this->get_data_elements();
			if ( $this->args['disabled'] ) :
				?>
				disabled
				<?php
			endif;
			if ( $this->args['readonly'] ) :
				?>
				readonly
				<?php
			endif;
			if ( $this->args['required'] ) :
				?>
				required
				<?php
			endif;
			if ( ! empty( $this->args['min'] ) ) :
				?>
				min="<?php echo esc_attr( $this->args['min'] ); ?>"
				<?php
			endif;
			if ( ! empty( $this->args['max'] ) ) :
				?>
				max="<?php echo esc_attr( $this->args['max'] ); ?>"
				<?php
			endif;
			if ( ! empty( $this->args['step'] ) ) :
				?>
				step="<?php echo esc_attr( $this->args['step'] ); ?>"
				<?php
			endif;
			if ( ! empty( $this->args['datalist'] ) ) :
				?>
				list="<?php echo esc_attr( $this->args['name'] ); ?>-datalist"
				<?php
			endif;
			?>
		/>
		<?php
		if ( ! empty( $this->args['datalist'] ) ) {
			?>
			<datalist id="<?php echo esc_attr( $this->args['name'] ); ?>-datalist">
				<?php
				foreach ( $this->args['datalist'] as $option ) {
					?>
					<option value="<?php echo esc_attr( $option ); ?>"></option>
					<?php
				}
				?>
			</datalist>
			<?php
		}

		if ( ! empty( $this->args['desc'] ) ) {
			?>
			<span class="description edd-description"><?php echo wp_kses_post( wpautop( $this->args['desc'] ) ); ?></span>
			<?php
		}

		return ob_get_clean();
	}

	/**
	 * Get the default arguments for the number field.
	 *
	 * @since 3.3.6
	 * @return array
	 */
	protected function defaults() {
		return array(
			'id'          => '',
			'name'        => 'number',
			'value'       => '',
			'label'       => '',
			'desc'        => '',
			'placeholder' => '',
			'class'       => '',
			'disabled'    => false,
			'data'        => false,
			'required'    => false,
			'min'         => '',
			'max'         => '',
			'step'        => '',
			'datalist'    => array(),
			'readonly'    => false,
		);
	}
}
