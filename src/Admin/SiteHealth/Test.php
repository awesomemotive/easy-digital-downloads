<?php
/**
 * Abstract class for getting SiteHealth tests.
 */
namespace EDD\Admin\SiteHealth;

defined( 'ABSPATH' ) || exit;

abstract class Test {

	abstract public function get();

	/**
	 * Gets the default EDD test badge.
	 *
	 * @since 3.1.2
	 * @return array
	 */
	protected function get_default_badge() {
		return array(
			'label' => __( 'Easy Digital Downloads', 'easy-digital-downloads' ),
			'color' => 'blue',
		);
	}

	/**
	 * Build the markup for the action button.
	 *
	 * @since 3.2.7
	 *
	 * @param string $url   The URL to link to.
	 * @param string $label The label for the button.
	 *
	 * @return string
	 */
	protected function get_action_button( $url = '', $label = '' ) {
		return sprintf(
			'<a class="button button-primary" href="%1$s">%2$s</a>',
			esc_url( $url ),
			esc_html( $label )
		);
	}
}
