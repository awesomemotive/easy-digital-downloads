<?php
/**
 * PayPal Standard gateway.
 *
 * @package   EDD\Gateways\PayPalStandard
 * @copyright Copyright (c) 2025, Sandhills Development, LLC
 * @license   https://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since     3.3.9
 */

namespace EDD\Gateways\PayPalStandard;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit; // @codeCoverageIgnore

use EDD\Gateways\Gateway as Base;

/**
 * PayPalStandard gateway.
 *
 * @since 3.3.9
 */
class Gateway extends Base {

	/**
	 * Gateway ID.
	 *
	 * @var string
	 */
	protected $id = 'paypal';

	/**
	 * Array of supported features.
	 *
	 * @var string
	 */
	protected $supports = array(
		'buy_now',
	);

	/**
	 * Array of icons.
	 *
	 * @var array
	 */
	protected $icons = array(
		'paypal',
	);

	/**
	 * Get the admin label.
	 *
	 * @since 3.3.9
	 * @return string
	 */
	public function get_admin_label(): string {
		return __( 'PayPal Standard', 'easy-digital-downloads' );
	}

	/**
	 * Get the checkout label.
	 *
	 * @since 3.3.9
	 * @return string
	 */
	public function get_checkout_label(): string {
		return __( 'PayPal', 'easy-digital-downloads' );
	}
}
