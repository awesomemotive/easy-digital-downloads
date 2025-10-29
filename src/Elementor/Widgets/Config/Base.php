<?php
/**
 * Base Control Configuration Class.
 *
 * @package     EDD\Elementor\Widgets\Config
 * @copyright   Copyright (c) 2025, Sandhills Development, LLC
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       3.6.0
 */

namespace EDD\Elementor\Widgets\Config;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/**
 * Abstract base class for control configurations.
 *
 * @since 3.6.0
 */
abstract class Base {

	use Traits\Controls;
	use Traits\Groups;
	use Traits\Options;

	/**
	 * Get controls configuration.
	 *
	 * @since 3.6.0
	 * @return array
	 */
	abstract public static function get_controls(): array;
}
