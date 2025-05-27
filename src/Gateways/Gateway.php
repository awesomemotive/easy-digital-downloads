<?php
/**
 * Abstract class for registered gateways.
 *
 * @package   EDD\Gateways
 * @copyright Copyright (c) 2025, Sandhills Development, LLC
 * @license   https://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since     3.3.9
 */

namespace EDD\Gateways;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit; // Exit if accessed directly.

/**
 * Abstract class for registered gateways.
 *
 * @since 3.3.9
 */
abstract class Gateway {

	/**
	 * Gateway ID.
	 *
	 * @var string
	 */
	protected $id;

	/**
	 * Array of supported features.
	 *
	 * @var string
	 */
	protected $supports = array();

	/**
	 * Array of icons.
	 *
	 * @var array
	 */
	protected $icons = array();

	/**
	 * Get the admin label.
	 *
	 * @since 3.3.9
	 * @return string
	 */
	abstract public function get_admin_label(): string;

	/**
	 * Get the checkout label.
	 *
	 * @since 3.3.9
	 * @return string
	 */
	abstract public function get_checkout_label(): string;

	/**
	 * Get the gateway ID.
	 *
	 * @since 3.3.9
	 * @return string
	 */
	final public function get_id(): string {
		return $this->id;
	}

	/**
	 * Get the features supported by this gateway.
	 *
	 * @since 3.3.9
	 * @return array
	 */
	public function get_supports(): array {
		return $this->supports;
	}

	/**
	 * Get the icons for this gateway.
	 *
	 * @since 3.3.9
	 * @return array
	 */
	public function get_icons(): array {
		return $this->icons;
	}
}
