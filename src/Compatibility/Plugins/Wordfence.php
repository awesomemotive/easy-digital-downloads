<?php
/**
 * Handles compatibility with Wordfence
 *
 * @package     EDD
 * @subpackage  Compat/Wordfence
 * @since       3.2.8
 */

namespace EDD\Compatibility\Plugins;

defined( 'ABSPATH' ) || exit;

/**
 * Wordfence compatibility
 *
 * @since 3.2.8
 */
class Wordfence extends Plugin {
	/**
	 * Check if the plugin is active
	 *
	 * @since 3.2.8
	 *
	 * @return bool
	 */
	public function is_active(): bool {
		return class_exists( 'wordfence' );
	}

	/**
	 * Return the events for Wordfence compatibility
	 *
	 * @since 3.2.8
	 *
	 * @return void
	 */
	protected function register_events() {

		/**
		 * Disable the Wordfence Login Security module when logging in via EDD.
		 *
		 * @since 3.2.8
		 */
		add_action(
			'edd_pre_log_user_in',
			function () {
				add_filter( 'wordfence_ls_require_captcha', '__return_false' );
			}
		);
	}
}
