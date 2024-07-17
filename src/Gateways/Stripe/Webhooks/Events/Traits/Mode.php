<?php
/**
 * The Stripe Webhooks mode trait.
 *
 * @package     EDD
 * @subpackage  Gateways\Stripe\Webhooks\Events\Traits
 * @since       3.3.0
 */

namespace EDD\Gateways\Stripe\Webhooks\Events\Traits;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit; // @codeCoverageIgnore

/**
 * Trait Mode
 *
 * @since 3.3.0
 */
trait Mode {
	/**
	 * Verify the webhook mode.
	 *
	 * @since 3.3.0
	 * @return bool
	 */
	public function verify_mode() {
		$store_mode = edd_is_test_mode() ? 'test' : 'live';
		$event_mode = $this->event->livemode ? 'live' : 'test';

		return $store_mode === $event_mode;
	}
}
