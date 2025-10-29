<?php
/**
 * Control configuration for checkout forms.
 *
 * @package     EDD\Elementor\Widgets\Config
 * @copyright   Copyright (c) 2025, Sandhills Development, LLC
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       3.6.0
 */

namespace EDD\Elementor\Widgets\Config;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

use EDD\Elementor\Widgets\Config\Checkout\General;
use EDD\Elementor\Widgets\Config\Checkout\Cart;
use EDD\Elementor\Widgets\Config\Checkout\Titles;
use EDD\Elementor\Widgets\Config\Checkout\Styles\Buttons;
use EDD\Elementor\Widgets\Config\Checkout\Styles\Cart as StylesCart;
use EDD\Elementor\Widgets\Config\Checkout\Styles\FormSections;
use EDD\Elementor\Widgets\Config\Checkout\Styles\FormElements;
use EDD\Elementor\Widgets\Config\Checkout\Styles\Sections;
use EDD\Elementor\Widgets\Config\Checkout\Styles\General as StylesGeneral;

/**
 * Control configuration for checkout forms.
 *
 * @since 3.6.0
 */
class Checkout {

	/**
	 * Get all checkout controls.
	 *
	 * @since 3.6.0
	 * @return array
	 */
	public static function get_all_controls(): array {
		return array_merge(
			General::get_controls(),
			Cart::get_controls(),
			Titles::get_controls(),
			StylesGeneral::get_controls(),
			Buttons::get_controls(),
			StylesCart::get_controls(),
			FormSections::get_controls(),
			FormElements::get_controls(),
			Sections::get_controls()
		);
	}
}
