<?php
/**
 * Easy Digital Downloads Settings Tabs
 *
 * @package EDD
 * @subpackage  Settings
 * @copyright   Copyright (c) 2023, Easy Digital Downloads
 * @license     https://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since 3.1.4
 */

namespace EDD\Admin\Settings\Tabs;

defined( 'ABSPATH' ) || exit;

use EDD\Admin\Pass_Manager;

/**
 * Abstract class for settings tabs.
 *
 * @since 3.1.4
 */
abstract class Tab {

	/**
	 * The ID for this tab.
	 *
	 * @since 3.1.4
	 * @var string
	 */
	protected $id;

	/**
	 * The pass ID.
	 *
	 * @since 3.2.8
	 * @var int|bool
	 */
	private $pass_id;

	/**
	 * Get the settings for this tab.
	 *
	 * @since 3.1.4
	 * @return array
	 */
	public function get() {
		return apply_filters( 'edd_settings_' . $this->get_id(), $this->register() );
	}

	/**
	 * Get the ID for this tab.
	 *
	 * @since 3.1.4
	 * @return string
	 */
	protected function get_id() {
		try {
			if ( empty( $this->id ) ) {
				// translators: %s: Tab class name.
				throw new \Exception( sprintf( __( 'The %s settings class is missing the required ID property.', 'easy-digital-downloads' ), get_class( $this ) ) );
			}
		} catch ( \Exception $e ) {
			wp_die( esc_html( $e->getMessage() ) );
		}

		return $this->id;
	}

	/**
	 * Register the settings for this tab.
	 *
	 * @since 3.1.4
	 * @return array
	 */
	abstract protected function register();

	/**
	 * Gets the site name.
	 *
	 * @since 3.1.4
	 * @return string
	 */
	protected function get_site_name() {
		return get_bloginfo( 'name' );
	}

	/**
	 * Gets the admin email address.
	 *
	 * @since 3.1.4
	 * @return string
	 */
	protected function get_admin_email() {
		return get_bloginfo( 'admin_email' );
	}

	/**
	 * Gets the pass ID.
	 *
	 * @since 3.2.8
	 * @return int
	 */
	protected function get_pass_id() {
		if ( is_null( $this->pass_id ) ) {
			$pass_manager  = new \EDD\Admin\Pass_Manager();
			$this->pass_id = $pass_manager->highest_pass_id;
		}

		return $this->pass_id;
	}
}
