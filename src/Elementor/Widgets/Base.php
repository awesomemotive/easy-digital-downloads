<?php
/**
 * Base Elementor Widget for EDD
 *
 * @package     EDD\Elementor\Widgets
 * @copyright   Copyright (c) 2025, Sandhills Development, LLC
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       3.6.0
 */

namespace EDD\Elementor\Widgets;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

use Elementor\Widget_Base;

/**
 * Abstract base class for EDD Elementor widgets.
 *
 * @since 3.6.0
 */
abstract class Base extends Widget_Base {

	/**
	 * Get widget categories
	 *
	 * @since 3.6.0
	 * @return array Widget categories
	 */
	public function get_categories(): array {
		return array( 'edd' );
	}

	/**
	 * Get widget keywords
	 *
	 * @since 3.6.0
	 * @return array Widget keywords
	 */
	public function get_keywords(): array {
		return array( 'edd' );
	}

	/**
	 * Whether the widget has a widget inner wrapper.
	 *
	 * @since 3.6.0
	 * @return bool Whether the widget has a widget inner wrapper.
	 */
	public function has_widget_inner_wrapper(): bool {
		return false;
	}
}
