<?php
/**
 * Loads the EDD admin footer contents.
 *
 * @package     EDD
 * @subpackage  Admin/Footer
 * @since       3.2.4
 */

namespace EDD\Admin\Promos\Footer;

use EDD\EventManagement\SubscriberInterface;

/**
 * Footer Loader Class.
 *
 * @since 3.2.4
 */
class Loader implements SubscriberInterface {

	/**
	 * Gets the subscribed events.
	 *
	 * @since 3.2.4
	 */
	public static function get_subscribed_events() {
		return array(
			'current_screen' => 'add_hooks_and_filters',
		);
	}

	/**
	 * Adds the hooks for our footer content.
	 *
	 * @since 3.2.4
	 */
	public function add_hooks_and_filters() {
		// Bail if we're not on an EDD admin page.
		if ( ! edd_is_admin_page() ) {
			return;
		}

		// Get the hooks that are allowed to run on this admin page.
		$allowed_hooks = $this->get_allowed_hooks();

		// If no hooks are returned, bail.
		if ( empty( $allowed_hooks ) ) {
			return;
		}

		foreach ( $allowed_hooks as $allowed_hook ) {
			$hook     = $allowed_hook['hook'];
			$type     = $allowed_hook['type'];
			$target   = $allowed_hook['target'];
			$priority = $allowed_hook['priority'];
			$args     = isset( $allowed_hook['args'] ) ? $allowed_hook['args'] : 0;

			if ( 'action' === $type ) {
				add_action( $hook, $target, $priority, $args );
			} else {
				add_filter( $hook, $target, $priority, $args );
			}
		}
	}

	/**
	 * Get the allowed hooks for the current admin page.
	 *
	 * @since 3.2.4
	 *
	 * @return array
	 */
	private function get_allowed_hooks() {
		$current_screen = function_exists( 'get_current_screen' ) ? get_current_screen() : false;

		// If we can't figure out what screen we're on, just avoid loading any hooks.
		if ( empty( $current_screen ) ) {
			return array();
		}

		// We don't want to load any of these on the edit or add download screens.
		$post   = isset( $_GET['post'] ) ? absint( $_GET['post'] ) : false;
		$action = isset( $_GET['action'] ) ? sanitize_text_field( $_GET['action'] ) : false;

		$is_edit_screen = $post && 'edit' === $action;
		if ( 'add' === $current_screen->action || $is_edit_screen ) {
			return array();
		}

		// If this is the dashboard, we don't want to load our footer contents either.
		if ( 'dashboard' === $current_screen->id ) {
			return array();
		}

		$registered_classes = $this->get_classes();
		$page_exclusions    = $this->page_exclusions();
		if ( ! array_key_exists( $current_screen->id, $page_exclusions ) ) {
			return $registered_classes;
		}

		foreach ( $registered_classes as $key => $data ) {
			if ( in_array( $key, $page_exclusions[ $current_screen->id ], true ) ) {
				unset( $registered_classes[ $key ] );
			}
		}

		return $registered_classes;
	}

	/**
	 * Get the conditions for the footer content.
	 *
	 * @since 3.2.4
	 *
	 * @return array
	 */
	private function get_classes() {
		$classes = array(
			'links'   => array(
				'hook'     => 'in_admin_footer',
				'type'     => 'action',
				'target'   => array( 'EDD\Admin\Promos\Footer\Links', 'footer_content' ),
				'priority' => 99,
			),
			'review'  => array(
				'hook'     => 'admin_footer_text',
				'type'     => 'filter',
				'target'   => array( 'EDD\Admin\Promos\Footer\Review', 'review_message' ),
				'priority' => 99,
				'args'     => 1,
			),
			'version' => array(
				'hook'     => 'update_footer',
				'type'     => 'filter',
				'target'   => array( 'EDD\Admin\Promos\Footer\Version', 'version_message' ),
				'priority' => 99,
				'args'     => 1,
			),
			'flyout'  => array(
				'hook'     => 'admin_footer',
				'type'     => 'action',
				'target'   => array( 'EDD\Admin\Promos\Footer\FlyoutMenu', 'output' ),
				'priority' => 99,
			),
		);

		return $classes;
	}

	/**
	 * Get the admin pages that should not load specific footer content.
	 *
	 * @since 3.2.4
	 *
	 * @return array
	 */
	private function page_exclusions() {
		return apply_filters(
			'edd_admin_page_footer_exclusions',
			array(
				'download_page_edd-onboarding-wizard' => array(
					'flyout',
					'version',
					'review',
					'links',
				),
			)
		);
	}
}
