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
 * @since       2.11.x
 */
namespace EDD\Admin\Settings;

use \EDD\Admin\Extensions\Extension;

class Invoices extends Extension {

	/**
	 * The product ID on EDD.
	 *
	 * @var integer
	 */
	protected $item_id = 375153;

	/**
	 * The pass level required to automatically download this extension.
	 */
	const PASS_LEVEL = \EDD\Admin\Pass_Manager::EXTENDED_PASS_ID;

	public function __construct() {
		add_filter( 'edd_settings_sections_gateways', array( $this, 'add_section' ) );
		add_action( 'edd_settings_tab_top_gateways_invoices', array( $this, 'settings_field' ) );
		add_action( 'edd_settings_tab_top_gateways_invoices', array( $this, 'hide_submit_button' ) );

		parent::__construct();
	}

	/**
	 * Gets the configuration for Invoices.
	 *
	 * @return array
	 */
	protected function get_configuration( $item_id = false ) {
		return array(
			'basename'    => 'edd-invoices/edd-invoices.php',
			'tab'         => 'gateways',
			'section'     => 'edd-invoices',
			'title'       => __( 'Impress Your Customers with Custom Invoices', 'easy-digital-downloads' ),
			'description' => __( 'Allow your customers to download beautiful, professional invoices with one click!', 'easy-digital-downloads' ),
		);
	}

	/**
	 * Adds the Invoices Payments section to the settings.
	 *
	 * @param array $sections
	 * @return array
	 */
	public function add_section( $sections ) {
		if ( $this->is_activated() ) {
			return $sections;
		}

		$sections['invoices'] = __( 'Invoices', 'easy-digital-downloads' );

		return $sections;
	}

	/**
	 * Whether EDD Invoices active or not.
	 *
	 * @since 2.11.x
	 *
	 * @return bool True if Invoices is active.
	 */
	protected function is_activated() {
		$config = $this->get_configuration();

		return class_exists( 'EDDInvoices' ) && is_plugin_active( $config['basename'] );
	}
}

new Invoices();
