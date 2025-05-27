<?php
/**
 * Stripe gateway.
 *
 * @package   EDD\Gateways\Stripe
 * @copyright Copyright (c) 2025, Sandhills Development, LLC
 * @license   https://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since     3.3.9
 */

namespace EDD\Gateways\Stripe;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit; // @codeCoverageIgnore

use EDD\Gateways\Gateway as Base;

/**
 * Stripe gateway.
 *
 * @since 3.3.9
 */
class Gateway extends Base {

	/**
	 * Gateway ID.
	 *
	 * @var string
	 */
	protected $id = 'stripe';

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
		'mastercard',
		'visa',
		'discover',
		'americanexpress',
	);

	/**
	 * Get the admin label.
	 *
	 * @since 3.3.9
	 * @return string
	 */
	public function get_admin_label(): string {
		return 'Stripe';
	}

	/**
	 * Get the checkout label.
	 *
	 * @since 3.3.9
	 * @return string
	 */
	public function get_checkout_label(): string {
		return __( 'Credit Card', 'easy-digital-downloads' );
	}
}
