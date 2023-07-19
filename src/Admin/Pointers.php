<?php
/**
 * Class for registering all EDD Pointers.
 *
 * @since 3.1.4
 */

namespace EDD\Admin;

defined( 'ABSPATH' ) || exit;

use \EDD\EventManagement\SubscriberInterface;

class Pointers implements SubscriberInterface {

	public static function get_subscribed_events() {
		return array(
			'admin_enqueue_scripts' => 'enqueue',
		);
	}

	/**
	 * Enqueue the pointer scripts if there are any pointers to show.
	 *
	 * @since 3.1.4
	 * @return void
	 */
	public function enqueue() {
		$pointers = $this->get_valid_pointers();
		if ( empty( $pointers ) ) {
			return;
		}
		wp_enqueue_style( 'wp-pointer' );
		wp_enqueue_script( 'edd-pointers', EDD_PLUGIN_URL . 'assets/js/edd-admin-pointers.js', array( 'wp-pointer' ), EDD_VERSION, true );
		wp_localize_script(
			'edd-pointers',
			'eddPointers',
			array(
				'pointers'    => $pointers,
				'next_label'  => __( 'Next', 'easy-digital-downloads' ),
				'close_label' => __( 'Close', 'easy-digital-downloads' ),
			)
		);
	}

	/**
	 * Gets the dismissed_wp_pointers user meta.
	 *
	 * @since 3.1.4
	 * @param int $user_id THe current user ID.
	 * @return array
	 */
	private function get_user_dismissals( $user_id ) {
		return explode( ',', (string) get_user_meta( $user_id, 'dismissed_wp_pointers', true ) );
	}

	/**
	 * Gets the pointers that have not been dismissed by the user.
	 *
	 * @since 3.1.4
	 * @return array
	 */
	private function get_valid_pointers() {
		$pointers = $this->get_pointers();
		if ( empty( $pointers ) ) {
			return array();
		}

		$valid_pointers = array();
		$dismissed      = $this->get_user_dismissals( get_current_user_id() );

		foreach ( $pointers as $pointer ) {
			if (
				empty( $pointer ) ||
				empty( $pointer['pointer_id'] ) ||
				empty( $pointer['target'] ) ||
				empty( $pointer['options'] ) ||
				in_array( $pointer['pointer_id'], $dismissed, true )
			) {
				continue;
			}

			$valid_pointers[] = $pointer;
		}

		return $valid_pointers;
	}

	/**
	 * Gets all EDD pointers.
	 *
	 * @since 3.1.4
	 * @return false|array
	 */
	private function get_pointers() {
		if ( ! $this->can_register() ) {
			return false;
		}

		/**
		 * Allows adding pointers for registration within the EDD Ecosystem.
		 *
		 * @since 3.1.1
		 * @param array $pointers The registered pointers for EDD to load.
		 */
		return apply_filters( 'edd_pointers', array() );
	}

	/**
	 * Determine whether pointers can be registered.
	 * Currently this just checks for excluded pages.
	 * Individual pointers should do their own capability checks.
	 *
	 * @since 3.1.4
	 * @return bool
	 */
	private function can_register() {
		// Exclude some pages from showing our pointers so we don't interfeer with user behavior.
		$excluded_pages = array(
			'update-core.php',
			'plugin-install.php',
		);

		global $pagenow;

		return ! in_array( $pagenow, $excluded_pages, true );
	}
}
