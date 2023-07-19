<?php
/**
 * Easy Digital Downloads Marketing Settings
 *
 * @package EDD
 * @subpackage  Settings
 * @copyright   Copyright (c) 2023, Easy Digital Downloads
 * @license     https://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since 3.1.4
 */
namespace EDD\Admin\Settings\Tabs;

defined( 'ABSPATH' ) || exit;

class Marketing extends Tab {

	/**
	 * Get the ID for this tab.
	 *
	 * @since 3.1.4
	 * @return string
	 */
	protected $id = 'marketing';

	/**
	 * Register the settings for this tab.
	 *
	 * @since 3.1.4
	 * @return array
	 */
	protected function register() {
		return array(
			'main' => array(
				'recapture'                => array(
					'id'   => 'recapture',
					'name' => __( 'Abandoned Cart Recovery', 'easy-digital-downloads' ),
					'desc' => '',
					'type' => 'recapture',
				),
				'allow_multiple_discounts' => array(
					'id'   => 'allow_multiple_discounts',
					'name' => __( 'Multiple Discounts', 'easy-digital-downloads' ),
					'desc' => __( 'Allow customers to use multiple discounts on the same purchase?', 'easy-digital-downloads' ),
					'type' => 'checkbox',
				),
			),
		);
	}
}
