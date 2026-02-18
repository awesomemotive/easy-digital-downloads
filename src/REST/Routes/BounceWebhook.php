<?php
/**
 * REST API route for Bounce Webhook.
 *
 * Registers the bounce webhook endpoint for EDD emails.
 *
 * @package EDD\REST\Routes
 * @copyright Copyright (c) 2026, Sandhills Development, LLC
 * @license https://opensource.org/licenses/GPL-2.0.php GNU Public License
 * @since 3.6.5
 */

namespace EDD\REST\Routes;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

use EDD\REST\Controllers\BounceWebhook as Controller;

/**
 * Bounce Webhook route class.
 *
 * @since 3.6.5
 */
final class BounceWebhook extends Route {

	/**
	 * REST API base.
	 *
	 * @since 3.6.5
	 * @var string
	 */
	const BASE = 'webhooks/bounce';

	/**
	 * Constructor.
	 *
	 * @since 3.6.5
	 */
	public function __construct() {
		$this->controller = new Controller();
	}

	/**
	 * Register REST routes.
	 *
	 * @since 3.6.5
	 */
	public function register(): void {
		// Bounce webhook endpoint.
		register_rest_route(
			self::NAMESPACE,
			'/' . self::$version . '/' . self::BASE,
			array(
				'methods'             => \WP_REST_Server::CREATABLE,
				'callback'            => array( $this->controller, 'handle_bounce' ),
				'permission_callback' => array( $this->controller, 'verify_webhook_permission' ),
			)
		);
	}
}
