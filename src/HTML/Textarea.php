<?php
/**
 * Textarea HTML Element
 *
 * @package EDD
 * @subpackage HTML
 * @since 3.2.8
 */

namespace EDD\HTML;

defined( 'ABSPATH' ) || exit;

/**
 * Class Textarea
 *
 * @since 3.2.8
 * @package EDD\HTML
 */
class Textarea extends Base {

	/**
	 * Gets the HTML for the textarea.
	 *
	 * @since 3.2.8
	 * @return string textarea
	 */
	public function get() {
		ob_start();
		?>
		<span id="edd-<?php echo edd_sanitize_key( $this->args['name'] ); ?>-wrap">
			<?php
			if ( ! empty( $this->args['label'] ) ) {
				?>
				<label class="edd-label" for="<?php echo edd_sanitize_key( $this->args['name'] ); ?>">
					<?php echo esc_html( $this->args['label'] ); ?>
				</label>
				<?php
			}
			?>
			<textarea
				name="<?php echo esc_attr( $this->args['name'] ); ?>"
				id="<?php echo edd_sanitize_key( $this->args['name'] ); ?>"
				class="<?php echo esc_attr( $this->get_css_class_string() ); ?>"
				<?php if ( $this->args['disabled'] ) : ?>
					disabled
				<?php endif; ?>
			><?php echo esc_textarea( $this->args['value'] ); ?></textarea>

			<?php
			if ( ! empty( $this->args['desc'] ) ) {
				?>
				<p class="description edd-description"><?php echo esc_html( $this->args['desc'] ); ?></p>
				<?php
			}
			?>
		</span>
		<?php

		return ob_get_clean();
	}

	/**
	 * Get the default arguments for the textarea.
	 *
	 * @since 3.2.8
	 * @return array
	 */
	protected function defaults() {
		return array(
			'name'     => 'textarea',
			'value'    => '',
			'label'    => '',
			'desc'     => null,
			'class'    => 'large-text',
			'disabled' => false,
		);
	}
}
