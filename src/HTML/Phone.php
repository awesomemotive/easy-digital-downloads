<?php
/**
 * Phone Class.
 *
 * @package     EDD\HTML
 * @copyright   Copyright (c) 2025, Sandhills Development, LLC
 * @license     https://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       3.3.8
 */

namespace EDD\HTML;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit; // @codeCoverageIgnore

/**
 * Phone class.
 */
class Phone extends Base {

	/**
	 * Gets the HTML for the element.
	 *
	 * @since 3.3.8
	 * @return string Element HTML.
	 */
	public function get(): string {
		self::enqueue();
		ob_start();
		?>
		<input
			type="tel"
			name="<?php echo esc_attr( $this->args['name'] ); ?>"
			id="<?php echo esc_attr( $this->args['id'] ); ?>"
			value="<?php echo esc_attr( $this->args['value'] ); ?>"
			<?php if ( ! empty( $this->args['placeholder'] ) && ! wp_script_is( 'intl-tel-input', 'enqueued' ) ) { ?>
				placeholder="<?php echo esc_attr( $this->args['placeholder'] ); ?>"
			<?php } ?>
			class="<?php echo esc_attr( $this->get_css_class_string() ); ?>"
			<?php
			echo $this->get_data_elements();
			if ( $this->args['autocomplete'] ) :
				?>
				autocomplete="<?php echo esc_attr( $this->args['autocomplete'] ); ?>"
				<?php
			endif;
			if ( $this->args['disabled'] ) :
				?>
				disabled
				<?php
			endif;
			if ( $this->args['required'] ) :
				?>
				required
				<?php
			endif;
			if ( ! empty( $this->args['pattern'] ) ) :
				?>
				pattern="<?php echo esc_attr( $this->args['pattern'] ); ?>"
				<?php
			endif;
			?>
		/>
		<?php

		return ob_get_clean();
	}

	/**
	 * Gets the default arguments for the element.
	 *
	 * @since 3.3.8
	 * @return array
	 */
	protected function defaults(): array {
		return array(
			'id'           => '',
			'name'         => 'phone',
			'value'        => '',
			'desc'         => '',
			'placeholder'  => '',
			'class'        => 'regular-text',
			'disabled'     => false,
			'autocomplete' => '',
			'data'         => array(
				'country' => edd_get_shop_country(),
			),
			'required'     => false,
			'pattern'      => false,
		);
	}

	/**
	 * Enqueues the international tel input script and style.
	 *
	 * @since 3.3.8
	 * @return void
	 */
	public static function enqueue(): void {
		if ( ! apply_filters( 'edd_intl_tel_input', true ) ) {
			return;
		}
		if ( wp_script_is( 'intl-tel-input', 'enqueued' ) ) {
			return;
		}

		wp_enqueue_script( 'intl-tel-input', EDD_PLUGIN_URL . 'assets/js/vendor/intl-tel-input/js/intlTelInput.min.js', array(), edd_admin_get_script_version(), true );
		wp_enqueue_script( 'edd-intl-tel-input', EDD_PLUGIN_URL . 'assets/js/utils/intl-tel-input.js', array( 'intl-tel-input' ), edd_admin_get_script_version(), true );
		wp_enqueue_style( 'intl-tel-input', EDD_PLUGIN_URL . 'assets/js/vendor/intl-tel-input/css/intlTelInput.min.css', array(), edd_admin_get_script_version() );
		wp_localize_script(
			'intl-tel-input',
			'EDDIntlTelInput',
			array(
				'utils' => EDD_PLUGIN_URL . 'assets/js/vendor/intl-tel-input/js/intlTelInput.min.js',
			)
		);
	}

	/**
	 * Gets the base CSS classes for the phone input.
	 *
	 * @since 3.3.8
	 * @return array
	 */
	protected function get_base_classes(): array {
		$base_classes   = parent::get_base_classes();
		$base_classes[] = 'edd-input__phone';

		return $base_classes;
	}
}
