<?php
/**
 * Recurring Payments
 *
 * Manages automatic installation/activation for Recurring Payments.
 *
 * @package     EDD
 * @subpackage  WP_SMTP
 * @copyright   Copyright (c) 2021, Easy Digital Downloads
 * @license     https://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       2.11.x
 */
namespace EDD\Admin\Settings;

use \EDD\Admin\Extensions\Extension;

class Recurring extends Extension {

	/**
	 * The product ID on EDD.
	 *
	 * @var integer
	 */
	protected $item_id = 28530;

	/**
	 * The pass level required to automatically download this extension.
	 */
	const PASS_LEVEL = \EDD\Admin\Pass_Manager::EXTENDED_PASS_ID;

	public function __construct() {
		add_filter( 'edd_settings_sections_gateways', array( $this, 'add_section' ) );
		add_filter( 'edd_settings_gateways', array( $this, 'setting' ) );
		add_action( 'edd_recurring_install', array( $this, 'settings_field' ) );

		parent::__construct();
	}

	/**
	 * Gets the configuration for Recurring.
	 *
	 * @return array
	 */
	protected function get_configuration( $item_id = false ) {
		return array(
			'item_id'  => $this->item_id,
			'name'     => __( 'Recurring Payments', 'easy-digital-downloads' ),
			'basename' => 'edd-recurring/edd-recurring.php',
			'tab'      => 'gateways',
			'section'  => 'recurring',
		);
	}

	/**
	 * Adds the Recurring Payments section to the settings.
	 *
	 * @param array $sections
	 * @return array
	 */
	public function add_section( $sections ) {
		if ( $this->is_activated() ) {
			return $sections;
		}

		$sections['recurring'] = __( 'Recurring Payments', 'easy-digital-downloads' );

		return $sections;
	}

	/**
	 * Registers the setting/hook to display the extension card.
	 *
	 * @param array $settings
	 * @return array
	 */
	public function setting( $settings ) {
		if ( $this->is_activated() ) {
			return $settings;
		}
		$settings['recurring']['recurring'] = array(
			'id'   => 'recurring_install',
			'name' => __( 'Get Recurring Payments', 'easy-digital-downloads' ),
			'desc' => '',
			'type' => 'hook',
		);

		return $settings;
	}

	/**
	 * Whether EDD Recurring active or not.
	 *
	 * @since 2.11.x
	 *
	 * @return bool True if Recurring is active.
	 */
	protected function is_activated() {
		$config = $this->get_configuration();

		return class_exists( 'EDD_Recurring' ) && is_plugin_active( $config['basename'] );
	}
}

new Recurring();
