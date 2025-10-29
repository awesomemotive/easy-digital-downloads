<?php
/**
 * EDD Checkout Subscriber
 *
 * @package     EDD\Elementor\Subscribers
 * @copyright   Copyright (c) 2025, Sandhills Development, LLC
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       3.6.0
 */

namespace EDD\Elementor\Subscribers;

use EDD\EventManagement\SubscriberInterface;
use EDD\Elementor\Utils\Page;

/**
 * Class Checkout
 *
 * @package EDD\Elementor\Subscribers
 */
class Checkout implements SubscriberInterface {

	/**
	 * Get the subscribed events.
	 *
	 * @since 3.6.0
	 *
	 * @return array
	 */
	public static function get_subscribed_events() {
		return array(
			'elementor/preview/enqueue_scripts' => 'enqueue_preview_assets',
			'pre_render_block'                  => array( 'prevent_checkout_block_render', 10, 3 ),
		);
	}

	/**
	 * Enqueue editor styles for Elementor Checkout Widget.
	 *
	 * @since 3.6.0
	 */
	public function enqueue_preview_assets() {
		\EDD\Elementor\Widgets\Checkout::enqueue_style();
	}

	/**
	 * Prevent the checkout block from rendering.
	 *
	 * @since 3.6.0
	 *
	 * @return string
	 */
	public function prevent_checkout_block_render( $pre_render, $parsed_block, $parent_block ) {
		if ( 'edd/checkout' === $parsed_block['blockName'] && Page::has_widget( 'edd-checkout' ) ) {
			return '';
		}

		return $pre_render;
	}
}
