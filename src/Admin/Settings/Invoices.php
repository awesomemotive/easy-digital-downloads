<?php
/**
 * Invoices
 *
 * Manages automatic installation/activation for Invoices.
 *
 * @package     EDD
 * @subpackage  Invoices
 * @copyright   Copyright (c) 2021, Easy Digital Downloads
 * @license     https://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       2.11.4
 */
namespace EDD\Admin\Settings;

use \EDD\Admin\Extensions\Extension;
use EDD\EventManagement\SubscriberInterface;

class Invoices extends Extension implements SubscriberInterface {

	/**
	 * The product ID on EDD.
	 *
	 * @var integer
	 */
	protected $item_id = 375153;

	/**
	 * The EDD settings tab where this extension should show.
	 *
	 * @since 2.11.4
	 * @var string
	 */
	protected $settings_tab = 'gateways';

	/**
	 * The settings section for this item.
	 *
	 * @since 2.11.5
	 * @var string
	 */
	protected $settings_section = 'invoices';

	/**
	 * The pass level required to access this extension.
	 */
	const PASS_LEVEL = \EDD\Admin\Pass_Manager::EXTENDED_PASS_ID;

	/**
	 * Returns an array of events that this subscriber wants to listen to.
	 *
	 * @since 3.1.1
	 * @return array
	 */
	public static function get_subscribed_events() {
		return array(
			'edd_settings_sections_gateways'            => 'add_section',
			'edd_settings_tab_top_gateways_invoices'    => 'settings_field',
			'edd_settings_tab_bottom_gateways_invoices' => 'hide_submit_button',
		);
	}

	/**
	 * Gets the custom configuration for Invoices.
	 *
	 * @since 2.11.4
	 * @param \EDD\Admin\Extensions\ProductData $product_data The product data object.
	 * @return array
	 */
	protected function get_configuration( \EDD\Admin\Extensions\ProductData $product_data ) {
		return array(
			'style'       => 'detailed-2col',
			'heading'     => 'Attractive Invoices For Your Customers',
			'description' => $this->get_custom_description(),
			'features'    => array(
				'Generate Attractive Invoices',
				'Build Customer Confidence',
				'PDF Download Support',
				'Include in Purchase Emails',
				'Customizable Templates',
			),
		);
	}

	/**
	 * Gets a custom description for the Invoices extension card.
	 *
	 * @since 2.11.4
	 * @return string
	 */
	private function get_custom_description() {
		$description = array(
			'Impress customers and build customer loyalty with attractive invoices. Making it easy to locate, save, and print purchase history builds trust with customers.',
			'Provide a professional experience with customizable templates and one-click PDF downloads. ',
		);

		return $this->format_description( $description );
	}

	/**
	 * Adds the Invoices Payments section to the settings.
	 *
	 * @param array $sections
	 * @return array
	 */
	public function add_section( $sections ) {
		if ( ! $this->can_show_product_section() ) {
			return $sections;
		}

		$sections[ $this->settings_section ] = __( 'Invoices', 'easy-digital-downloads' );

		return $sections;
	}

	/**
	 * Whether EDD Invoices active or not.
	 *
	 * @since 2.11.4
	 *
	 * @return bool True if Invoices is active.
	 */
	protected function is_activated() {
		if ( $this->manager->is_plugin_active( $this->get_product_data() ) ) {
			return true;
		}

		return class_exists( 'EDDInvoices' );
	}
}
