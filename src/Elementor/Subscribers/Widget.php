<?php
/**
 * EDD Widget Subscriber
 *
 * @package     EDD\Elementor\Subscribers
 * @copyright   Copyright (c) 2025, Sandhills Development, LLC
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       3.6.0
 */

namespace EDD\Elementor\Subscribers;

use EDD\EventManagement\SubscriberInterface;

/**
 * Class Widget
 *
 * @since 3.6.0
 */
class Widget implements SubscriberInterface {

	/**
	 * Get the subscribed events.
	 *
	 * @since 3.6.0
	 * @return array
	 */
	public static function get_subscribed_events() {
		return array(
			'elementor/elements/categories_registered' => 'register_widget_category',
			'elementor/widgets/register'               => 'register_widgets',
		);
	}

	/**
	 * Add EDD widget category
	 *
	 * @since 3.6.0
	 */
	public function register_widget_category( $elements_manager ) {
		$elements_manager->add_category(
			'edd',
			array(
				'title' => __( 'Easy Digital Downloads', 'easy-digital-downloads' ),
				'icon'  => 'dashicons dashicons-download',
			)
		);
	}

	/**
	 * Register EDD widgets
	 *
	 * @since 3.6.0
	 */
	public function register_widgets( $widgets_manager ) {
		$widgets = array(
			new \EDD\Elementor\Widgets\Checkout(),
		);

		foreach ( $widgets as $widget ) {
			$widgets_manager->register( $widget );
		}
	}
}
