<?php
/**
 * Store gateway.
 *
 * @package   EDD\Gateways\StoreGateway
 * @copyright Copyright (c) 2025, Sandhills Development, LLC
 * @license   https://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since     3.3.9
 */

namespace EDD\Gateways\StoreGateway;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit; // @codeCoverageIgnore

use EDD\Gateways\Gateway as Base;

/**
 * StoreGateway gateway.
 *
 * @since 3.3.9
 */
class Gateway extends Base {

	/**
	 * Gateway ID.
	 *
	 * @var string
	 */
	protected $id = 'manual';

	/**
	 * Get the admin label.
	 *
	 * @since 3.3.9
	 * @return string
	 */
	public function get_admin_label(): string {
		return __( 'Store Gateway', 'easy-digital-downloads' );
	}

	/**
	 * Get the checkout label.
	 *
	 * @since 3.3.9
	 * @return string
	 */
	public function get_checkout_label(): string {
		return __( 'Store Gateway', 'easy-digital-downloads' );
	}
}
