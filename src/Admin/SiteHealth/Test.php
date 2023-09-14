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
}
