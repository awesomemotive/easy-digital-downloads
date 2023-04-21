<?php
/**
 * EDD's custom Plugin API Manager.
 *
 * @since 3.1.1
 * @package EDD
 */
namespace EDD\EventManagement;

class PluginAPIManager {

	/**
	 * Adds a callback to a specific hook of the WordPress plugin API.
	 *
	 * @uses add_filter()
	 *
	 * @param string   $hook_name
	 * @param callable $callback
	 * @param int      $priority
	 * @param int      $accepted_args
	 */
	public function add_callback( $hook_name, $callback, $priority = 10, $accepted_args = 1 ) {
		add_filter( $hook_name, $callback, $priority, $accepted_args );
	}

	/**
	 * Executes all the callbacks registered with the given hook.
	 *
	 * @uses do_action()
	 *
	 * @param string $hook_name
	 */
	public function execute() {
		return call_user_func_array( 'do_action', func_get_args() );
	}

	/**
	 * Filters the given value by applying all the changes from the callbacks
	 * registered with the given hook. Returns the filtered value.
	 *
	 * @uses apply_filters()
	 *
	 * @param string $hook_name
	 * @param mixed  $value
	 *
	 * @return mixed
	 */
	public function filter() {
		return call_user_func_array( 'apply_filters', func_get_args() );
	}

	/**
	 * Get the name of the hook that WordPress plugin API is executing. Returns
	 * false if it isn't executing a hook.
	 *
	 * @uses current_filter()
	 *
	 * @return string|bool
	 */
	public function get_current_hook() {
		return current_filter();
	}

	/**
	 * Checks the WordPress plugin API to see if the given hook has
	 * the given callback. The priority of the callback will be returned
	 * or false. If no callback is given will return true or false if
	 * there's any callbacks registered to the hook.
	 *
	 * @uses has_filter()
	 *
	 * @param string $hook_name
	 * @param mixed  $callback
	 *
	 * @return bool|int
	 */
	public function has_callback( $hook_name, $callback = false ) {
		return has_filter( $hook_name, $callback );
	}

	/**
	 * Removes the given callback from the given hook. The WordPress plugin API only
	 * removes the hook if the callback and priority match a registered hook.
	 *
	 * @uses remove_filter()
	 *
	 * @param string   $hook_name
	 * @param callable $callback
	 * @param int      $priority
	 *
	 * @return bool
	 */
	public function remove_callback( $hook_name, $callback, $priority = 10 ) {
		return remove_filter( $hook_name, $callback, $priority );
	}
}
