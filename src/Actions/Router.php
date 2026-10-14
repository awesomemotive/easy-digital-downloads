<?php
/**
 * Request Action Router
 *
 * Converts an `edd_action`/`edd-action` request value into its action hook.
 *
 * @package     EDD\Actions
 * @copyright   Copyright (c) 2026, Sandhills Development, LLC
 * @license     https://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       3.7.1
 */

namespace EDD\Actions;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit; // @codeCoverageIgnore

use EDD\Component;
use EDD\Database\Query;
use EDD\Utils\Request;

/**
 * Routes request values to EDD dynamic action hooks.
 *
 * @since 3.7.1
 */
class Router {

	/**
	 * The request parameter read on the front end.
	 *
	 * @since 3.7.1
	 * @var string
	 */
	const FRONTEND_PARAM = 'edd_action';

	/**
	 * The request parameter read in the admin.
	 *
	 * @since 3.7.1
	 * @var string
	 */
	const ADMIN_PARAM = 'edd-action';

	/**
	 * Hooks which may not be built from request input, keyed for lookup.
	 *
	 * @since 3.7.1
	 * @var null|array
	 */
	private static $blocked;

	/**
	 * Dispatches a front-end action.
	 *
	 * Deferred actions run on `template_redirect` and everything else on `init`, so each pass
	 * dispatches only the actions belonging to it.
	 *
	 * @since 3.7.1
	 *
	 * @param array $request           The request array, either $_GET or $_POST.
	 * @param bool  $is_deferred_pass  Whether this is the `template_redirect` pass.
	 * @return void
	 */
	public static function frontend( $request, $is_deferred_pass = false ): void {
		$key = self::get_key( $request, self::FRONTEND_PARAM );
		if ( empty( $key ) ) {
			return;
		}

		if ( edd_is_delayed_action( $key ) !== (bool) $is_deferred_pass ) {
			return;
		}

		self::dispatch( $key, $request );
	}

	/**
	 * Dispatches an admin action. Deferral does not apply here.
	 *
	 * @since 3.7.1
	 *
	 * @param array $request The request array, either $_GET or $_POST.
	 * @return void
	 */
	public static function admin( $request ): void {
		if ( ! Request::is_request( array( 'admin', 'cli' ) ) ) {
			return;
		}

		$key = self::get_key( $request, self::ADMIN_PARAM );
		if ( empty( $key ) ) {
			return;
		}

		self::dispatch( $key, $request );
	}

	/**
	 * Whether a hook is blocked from being dispatched by the router.
	 *
	 * @since 3.7.1
	 *
	 * @param string $hook The full hook name, including the `edd_` prefix.
	 * @return bool
	 */
	public static function is_blocked( $hook ): bool {
		return isset( self::get_blocked_hooks()[ $hook ] );
	}

	/**
	 * Resets the cached blocklist.
	 *
	 * @since 3.7.1
	 * @return void
	 */
	public static function reset(): void {
		self::$blocked = null;
	}

	/**
	 * Reads and normalizes the action key from a request array.
	 *
	 * @since 3.7.1
	 *
	 * @param array  $request The request array.
	 * @param string $param   The parameter to read.
	 * @return string The sanitized key, or an empty string when there is nothing to dispatch.
	 */
	private static function get_key( $request, $param ): string {
		if ( ! is_array( $request ) || empty( $request[ $param ] ) ) {
			return '';
		}

		if ( ! is_scalar( $request[ $param ] ) ) {
			return '';
		}

		// EDD's `edd_sanitize_key` preserves `/`, so use Core's function instead.
		return sanitize_key( $request[ $param ] );
	}

	/**
	 * Fires the action for a key, unless the hook is blocked.
	 *
	 * @since 3.7.1
	 *
	 * @param string $key     The sanitized action key.
	 * @param array  $request The request array, passed to the hook as-is.
	 * @return void
	 */
	private static function dispatch( $key, $request ): void {
		$hook = "edd_{$key}";

		if ( self::is_blocked( $hook ) ) {
			edd_debug_log( sprintf( 'EDD: refused to dispatch %s from a request; the hook is blocked.', $hook ) );

			return;
		}

		do_action( $hook, $request );
	}

	/**
	 * Gets the blocked hooks, keyed by hook name for lookup.
	 *
	 * @since 3.7.1
	 *
	 * @return array
	 */
	private static function get_blocked_hooks(): array {
		if ( ! is_null( self::$blocked ) ) {
			return self::$blocked;
		}

		$core    = array(
			'edd_insert_payment',
			'edd_built_order',
			'edd_post_add_manual_order',
			'edd_pre_complete_purchase',
			'edd_complete_download_purchase',
			'edd_complete_purchase',
			'edd_after_payment_actions',
			'edd_after_order_actions',
			'edd_update_payment_status',
			'edd_refund_order',
			'edd_payment_delete',
			'edd_payment_deleted',
			'edd_pre_destroy_order',
			'edd_order_destroyed',
			'edd_pre_insert_payment_note',
			'edd_insert_payment_note',
			'edd_pre_delete_payment_note',
			'edd_post_delete_payment_note',
			'edd_insert_user',
			'edd_prune_logs',
			'edd_paypal_v3_sync_connect',
			'edd_email_legacy_data_cleanup',
			'edd_recalculate_customer_deferred',
			'edd_after_payment_scheduled_actions',
			'edd_recalculate_download_sales_earnings_deferred',
			'edd_cleanup_file_symlinks',
			'edd_daily_scheduled_events',
			'edd_weekly_scheduled_events',
			'edd_pro_weekly_scheduled_events',
			'edd_cleanup_sessions',
			'edd_send_new_user_email',
		);
		$blocked = array_merge( $core, self::get_database_hooks(), self::get_additional_blocked_hooks() );

		self::$blocked = array_fill_keys( array_filter( $blocked, 'is_string' ), true );

		return self::$blocked;
	}

	/**
	 * Gets an array of additional blocked hooks.
	 *
	 * @since 3.7.1
	 *
	 * @return array
	 */
	private static function get_additional_blocked_hooks(): array {
		/**
		 * Filters the hooks which may not be built from request input.
		 * Blocking a hook stops only the dynamic router from processing it.
		 *
		 * The list is cached on the first dispatch, which is `init` for an action that is not
		 * deferred, so a callback registered later than that is never consulted.
		 *
		 * @since 3.7.1
		 *
		 * @param array $blocked The array of full hook names; empty by default.
		 */
		return (array) apply_filters( 'edd/actions/router/blocked_hooks', array() );
	}

	/**
	 * Gets the lifecycle and transition hooks Berlin fires for each registered component.
	 *
	 * Read from the component registry so that query classes registered by extensions are covered too.
	 *
	 * @since 3.7.2
	 *
	 * @return array
	 */
	private static function get_database_hooks(): array {
		$hooks = array();

		foreach ( EDD()->components as $component ) {
			$query = $component instanceof Component ? $component->get_interface( 'query' ) : false;

			// The router only builds `edd_` hooks, so a query on any other prefix is unreachable.
			if ( ! $query instanceof Query || 'edd' !== $query->prefix || empty( $query->item_name ) ) {
				continue;
			}

			$item_name = $query->item_name;
			$hooks[]   = "edd_{$item_name}_added";
			$hooks[]   = "edd_{$item_name}_updated";
			$hooks[]   = "edd_{$item_name}_deleted";

			foreach ( wp_filter_object_list( $query->columns, array( 'transition' => true ), 'and', 'name' ) as $column ) {
				$hooks[] = "edd_transition_{$item_name}_{$column}";
			}
		}

		return $hooks;
	}
}
