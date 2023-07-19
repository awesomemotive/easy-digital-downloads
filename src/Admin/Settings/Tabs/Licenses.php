<?php
/**
 * Licenses tab.
 *
 * @package easy-digital-downlaods
 * @since 3.1.4
 */
namespace EDD\Admin\Settings\Tabs;

defined( 'ABSPATH' ) || exit;

class Licenses extends Tab {

	/**
	 * Get the ID for this tab.
	 *
	 * @since 3.1.4
	 * @return string
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
