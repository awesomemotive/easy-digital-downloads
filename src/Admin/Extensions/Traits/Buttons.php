<?php
/**
 * Buttons for extension cards.
 *
 * @since 3.1.1
 * @package EDD
 */
namespace EDD\Admin\Extensions\Traits;

trait Buttons {

	/**
	 * Outputs the button to activate/install a plugin/extension.
	 * If a link is passed in the args, create a button style link instead (@uses $this->link()).
	 *
	 * @since 2.11.4
	 * @param array $args The array of parameters for the button.
	 * @return void
	 */
	public function button( $args ) {
		if ( ! empty( $args['href'] ) ) {
			$this->link( $args );
			return;
		}
		$defaults = array(
			'button_class' => 'button-primary',
			'plugin'       => '',
			'action'       => '',
			'button_text'  => '',
			'type'         => 'plugin',
			'id'           => '',
			'product'      => '',
			'pass'         => $this->required_pass_id,
		);
		$args     = wp_parse_args( $args, $defaults );
		if ( empty( $args['button_text'] ) ) {
			return;
		}
		?>
		<button
			class="button <?php echo esc_attr( $args['button_class'] ); ?> edd-extension-manager__action"
			<?php
			foreach ( $args as $key => $attribute ) {
				if ( empty( $attribute ) || in_array( $key, array( 'button_class', 'button_text', 'disabled' ), true ) ) {
					continue;
				}
				printf(
					' data-%s="%s"',
					esc_attr( $key ),
					esc_attr( $attribute )
				);
			}
			if ( ! empty( $args['disabled'] ) ) {
				echo ' disabled';
			}
			?>
		>
			<?php echo esc_html( $args['button_text'] ); ?>
		</button>
		<?php
	}

	/**
	 * Outputs the link, if it should be a link.
	 *
	 * @param array $args
	 * @return void
	 */
	public function link( $args ) {
		$defaults = array(
			'button_class' => 'button-primary',
			'button_text'  => '',
		);
		$args     = wp_parse_args( $args, $defaults );
		if ( empty( $args['button_text'] ) || empty( $args['href'] ) ) {
			return;
		}
		?>
		<a
			class="button <?php echo esc_attr( $args['button_class'] ); ?>"
			href="<?php echo esc_url( $args['href'] ); ?>"
			<?php echo ! empty( $args['new_tab'] ) ? ' target="_blank" rel="noopener noreferrer"' : ''; ?>
		>
			<?php echo esc_html( $args['button_text'] ); ?>
		</a>
		<?php
	}
}
