<?php
/**
 * Handles compatibility with User Switching plugin
 *
 * @package     EDD\Compatibility\Plugins
 * @copyright   Copyright (c) 2025, Sandhills Development, LLC
 * @license     https://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       3.5.0
 */

namespace EDD\Compatibility\Plugins;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit; // @codeCoverageIgnore

/**
 * User Switching compatibility
 *
 * @since 3.5.0
 */
class UserSwitching extends Plugin {

	/**
	 * The plugin name
	 *
	 * @since 3.5.0
	 *
	 * @var string
	 */
	public $plugin_name = 'user-switching';

	/**
	 * Check if the plugin is active
	 *
	 * @since 3.5.0
	 *
	 * @return bool
	 */
	public function is_active(): bool {
		return function_exists( 'switch_to_user' );
	}

	/**
	 * Register the events for User Switching compatibility.
	 *
	 * @since 3.5.0
	 */
	protected function register_events() {
		add_action( 'switch_to_user', array( $this, 'forget' ) );
		add_action( 'switch_back_user', array( $this, 'forget' ) );
		add_action( 'switch_off_user', array( $this, 'forget' ) );
	}

	/**
	 * Forgets the EDD session data but preserves the ability to recover it.
	 *
	 * @since 3.5.0
	 */
	public function forget() {
		// Get the EDD session handler.
		$session = EDD()->session;
		if ( ! $session || ! method_exists( $session, 'forget' ) ) {
			return;
		}

		// Clear session data but preserve the ability to recover it.
		$session->forget();
	}
}
