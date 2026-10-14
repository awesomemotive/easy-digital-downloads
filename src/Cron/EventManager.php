<?php
/**
 * Event manager which runs cron component callbacks only in a cron context.
 *
 * Cron components are wired on every request, because their hooks have to be registered before
 * a scheduled event can fire them. Wiring them through here means a component does not have to
 * check for itself, and components added later are covered automatically.
 *
 * Covers Cron\Components\Component subclasses only. A listener wired as a plain
 * SubscriberInterface, or with a raw add_action(), goes nowhere near this and carries its own
 * check.
 *
 * @package     EDD\Cron
 * @copyright   Copyright (c) 2026, Sandhills Development, LLC
 * @license     https://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       3.7.1
 */

namespace EDD\Cron;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit; // @codeCoverageIgnore

use EDD\Utils\Request;

/**
 * Cron EventManager Class
 *
 * A callback wired through here is registered as a closure, so it cannot be removed by the
 * original callable: remove_subscriber() and has_filter() will not find it. To stop a component
 * running, remove it with the edd_cron_components filter instead.
 *
 * @since 3.7.1
 */
class EventManager extends \EDD\EventManagement\EventManager {

	/**
	 * Hooks which legitimately run on an ordinary page load.
	 *
	 * @var array
	 * @since 3.7.1
	 */
	private $request_time_hooks;

	/**
	 * Constructor.
	 *
	 * @since 3.7.1
	 *
	 * @param array $request_time_hooks Hook names which run on an ordinary page load.
	 */
	public function __construct( array $request_time_hooks = array() ) {
		$this->request_time_hooks = $request_time_hooks;
	}

	/**
	 * Adds a callback to a hook, wrapped in a cron context check.
	 *
	 * @since 3.7.1
	 *
	 * @param string   $hook_name     The name of the hook.
	 * @param callable $callback      The callback to register.
	 * @param int      $priority      The priority of the callback.
	 * @param int      $accepted_args The number of arguments the callback accepts.
	 * @return void
	 */
	public function add_callback( $hook_name, $callback, $priority = 10, $accepted_args = 1 ) {
		if ( in_array( $hook_name, $this->request_time_hooks, true ) ) {
			parent::add_callback( $hook_name, $callback, $priority, $accepted_args );

			return;
		}

		parent::add_callback( $hook_name, self::cron_only( $callback ), $priority, $accepted_args );
	}

	/**
	 * Wraps a callback so that it only runs when the request may run scheduled work.
	 *
	 * For components which register callbacks dynamically rather than through
	 * get_subscribed_events().
	 *
	 * @since 3.7.1
	 *
	 * @param callable $callback The callback to wrap.
	 * @return callable The wrapped callback.
	 */
	public static function cron_only( callable $callback ): callable {
		return function ( ...$args ) use ( $callback ) {
			if ( ! self::is_cron_context() ) {
				// Callbacks are registered with add_filter(), so pass the value through untouched.
				return isset( $args[0] ) ? $args[0] : null;
			}

			return $callback( ...$args );
		};
	}

	/**
	 * Whether the current request may run scheduled work.
	 *
	 * Deliberately stricter than edd_doing_cron(), which answers "does this request look like
	 * cron" and is used in enough places that tightening it there would have a much wider blast
	 * radius.
	 *
	 * @since 3.7.1
	 *
	 * @return bool
	 */
	public static function is_cron_context(): bool {
		// WP-CLI is not reachable over HTTP, so a job invoked there was invoked on purpose.
		// Action Scheduler tells us precisely when a job is executing, checked independently
		// because its queue runner can be attached to `init`.
		if ( Request::is_request( array( 'cli', 'action_scheduler' ) ) ) {
			return true;
		}

		// DOING_CRON is defined before WordPress loads, so `wp_loaded` is what separates a real
		// dispatch from a request that merely arrives with the constant already set.
		return wp_doing_cron() && did_action( 'wp_loaded' );
	}
}
