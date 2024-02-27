<?php
/**
 * Pro extension buttons.
 */
namespace EDD\Pro\Admin\Extensions;

class Buttons {
	/**
	 * Gets or outputs the activate/deactivate button.
	 *
	 * @since 3.1.1
	 * @param array $args The array of parameters for the button.
	 * @param bool $echo  Whether the button should be echoed or returned.
	 * @return void|string
	 */
	public function get_activate_deactivate_button( $args, $echo = false ) {
		if ( ! $echo ) {
			ob_start();
		}
		$button_text   = __( 'Activate', 'easy-digital-downloads' );
		$plugin_status = __( 'Deactivated', 'easy-digital-downloads' );
		if ( 'deactivate' === $args['action'] ) {
			$button_text   = __( 'Deactivate', 'easy-digital-downloads' );
			$plugin_status = __( 'Activated', 'easy-digital-downloads' );
		}
		?>
		<span class="edd-extension-manager__status"><?php echo esc_html( $plugin_status ); ?></span>
		<button
			class="button edd-button__toggle edd-extension-manager__action"
			<?php
			foreach ( $args as $key => $attribute ) {
				if ( empty( $attribute ) || in_array( $key, array( 'button_class', 'button_text', 'status' ), true ) ) {
					continue;
				}
				if ( 'type' === $key ) {
					$attribute = 'extension';
				}
				printf(
					' data-%s="%s"',
					esc_attr( $key ),
					esc_attr( $attribute )
				);
			}
			?>
		>
			<span class="screen-reader-text"><?php echo esc_html( $button_text ); ?></span>
		</button>
		<?php
		if ( ! $echo ) {
			return ob_get_clean();
		}
	}
}
