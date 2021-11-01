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

use \EDD\Admin\Pass_Manager;

class Recurring {

	/**
	 * Array of configuration data for Recurring.
	 *
	 * @var array
	 */
	private $config = array(
		'pro_plugin'   => 'edd-recurring/edd-recurring.php',
		'settings_url' => 'edit.php?post_type=download&page=edd-settings&tab=gateways&section=recurring',
		'upgrade_url'  => 'https://easydigitaldownloads.com/pricing',
		'item_id'      => 28530,
	);

	/**
	 * The Extension Manager
	 *
	 * @var \EDD\Admin\Extension_Manager
	 */
	private $manager;

	private $pass_level;

	public function __construct() {
		add_filter( 'edd_settings_sections_gateways', array( $this, 'add_recurring_section' ) );
		add_filter( 'edd_settings_gateways', array( $this, 'setting' ) );
		add_action( 'edd_recurring_install', array( $this, 'settings_field' ) );

		$this->manager    = new \EDD\Admin\Extension_Manager( Pass_Manager::EXTENDED_PASS_ID );
	}

	public function add_recurring_section( $sections ) {
		if ( $this->is_activated() ) {
			return $sections;
		}

		$sections['recurring'] = __( 'Recurring Payments', 'easy-digital-downloads' );

		return $sections;
	}

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
	 * Output the settings field (installation helper).
	 *
	 * @param array $args
	 * @return void
	 */
	public function settings_field( $args ) {
		$this->manager->do_extension_field(
			$this->config['item_id'],
			$this->get_button_parameters(),
			$this->get_link_parameters(),
			$this->is_activated()
		);
	}

	/**
	 * Gets the button parameters.
	 *
	 * @return array
	 */
	private function get_button_parameters() {
		$button = array();
		// If neither the lite nor pro plugin is installed, the button will prompt to install and activate the lite plugin.
		if ( ! $this->manager->is_plugin_installed( $this->config['pro_plugin'] ) ) {
			if ( $this->manager->pass_can_download() ) {
				$button['data-plugin'] = $this->config['item_id'];
				$button['data-action'] = 'install';
				$button['type']        = 'extension';
				$button['button_text'] = __( 'Install & Activate Recurring Payments', 'easy-digital-downloads' );
			} else {
				$button = array(
					'button_text' => __( 'Get Recurring Payments Today!', 'easy-digital-downloads' ),
					'href'        => $this->config['upgrade_url'],
					'new_tab'     => true,
				);
			}
		} elseif ( ! $this->is_activated() ) {
			// If Recurring is installed, but not activated, the button will prompt to activate it.
			$button['data-plugin'] = $this->config['pro_plugin'];
			$button['data-action'] = 'activate';
			$button['button_text'] = __( 'Activate Recurring Payments', 'easy-digital-downloads' );
		}

		return $button;
	}

	/**
	 * Gets the array of parameters for the link to configure Recurring Payments.
	 * @todo maybe this should go to the settings page instead.
	 *
	 * @since 2.11.x
	 * @return array
	 */
	private function get_link_parameters() {
		return array(
			'button_text' => __( 'Configure Recurring Payments', 'easy-digital-downloads' ),
			'href'        => admin_url( $this->config['settings_url'] ),
		);
	}

	/**
	 * Whether EDD Recurring active or not.
	 *
	 * @since 2.11.x
	 *
	 * @return bool True if Recurring is active.
	 */
	protected function is_activated() {
		return class_exists( 'EDD_Recurring' ) && is_plugin_active( $this->config['pro_plugin'] );
	}
}

new Recurring();
