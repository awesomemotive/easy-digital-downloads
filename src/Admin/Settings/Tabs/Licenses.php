<?php
/**
 * Licenses tab.
 *
 * @package     EDD
 * @subpackage  Settings
 * @copyright   Copyright (c) 2023, Easy Digital Downloads
 * @license     https://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       3.1.4
 */

namespace EDD\Admin\Settings\Tabs;

defined( 'ABSPATH' ) || exit;

/**
 * Licenses settings tab class.
 *
 * @since 3.1.4
 */
class Licenses extends Tab {

	/**
	 * Get the ID for this tab.
	 *
	 * @since 3.1.4
	 *
	 * @var string
	 */
	protected $id = 'licenses';

	/**
	 * Register the settings for this tab.
	 *
	 * @since 3.1.4
	 * @return array
	 */
	protected function register() {
		return array();
	}
}
