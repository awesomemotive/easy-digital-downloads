<?php
/**
 * Recurring Payments
 *
 * Manages automatic activation for Recurring Payments.
 *
 * @package     EDD
 * @subpackage  Recurring
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
	 * The pass level required to access this extension.
	 */
	const PASS_LEVEL = \EDD\Admin\Pass_Manager::EXTENDED_PASS_ID;

	public function __construct() {
		$this->product_data = $this->get_product_data();
		if ( ! $this->product_data ) {
			return;
		}
		add_filter( 'edd_settings_sections_gateways', array( $this, 'add_section' ) );
		add_action( 'edd_settings_tab_top_gateways_recurring', array( $this, 'settings_field' ) );
		add_action( 'edd_settings_tab_top_gateways_recurring', array( $this, 'hide_submit_button' ) );

		parent::__construct();
	}

	/**
	 * Gets the custom configuration for Recurring.
	 *
	 * @since 2.11.x
	 * @param array $product_data The array of product data from the API.
	 * @return array
	 */
	protected function get_configuration( $product_data = array() ) {
		return array(
			'style'       => 'detailed-2col',
			'title'       => 'Increase Revenue By Selling Subscriptions!',
			'description' => $this->get_custom_description(),
			'features'    => array(
				'Flexible Recurring Payments',
				'Custom Reminder Emails',
				'Free Trial Support',
				'Signup Fees',
				'Recurring Revenue Reports',
				'Integrates with Software Licensing',
				'Integrates with All Access',
			),
		);
	}

	/**
	 * Gets a custom description for the Recurring extension card.
	 *
	 * @since 2.11.x
	 * @return string
	 */
	private function get_custom_description() {
		$description  = sprintf( '<p>%s</p>', 'You are already selling one-time digital products to your customers. But do you also have products that you can sell on a recurring basis?' );
		$description .= sprintf( '<p>%s</p>', 'Recurring revenue provides more predictable income and allows you to make better forecasts and decisions for your business.' );

		return $description;
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
	 * Whether EDD Recurring active or not.
	 *
	 * @since 2.11.x
	 *
	 * @return bool True if Recurring is active.
	 */
	protected function is_activated() {
		if ( $this->manager->is_plugin_active( $this->product_data ) ) {
			return true;
		}

		return class_exists( 'EDD_Recurring' );
	}
}

new Recurring();
