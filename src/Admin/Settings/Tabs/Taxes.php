<?php
/**
 * Easy Digital Downloads Taxes Settings
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
 * Taxes settings tab class.
 *
 * @since 3.1.4
 */
class Taxes extends Tab {

	/**
	 * Get the ID for this tab.
	 *
	 * @since 3.1.4
	 *
	 * @var string
	 */
	protected $id = 'taxes';

	/**
	 * Register the settings for this tab.
	 *
	 * @since 3.1.4
	 * @return array
	 */
	protected function register() {
		return array(
			'main'  => array(
				'enable_taxes'         => array(
					'id'            => 'enable_taxes',
					'name'          => __( 'Enable Taxes', 'easy-digital-downloads' ),
					'type'          => 'checkbox_toggle',
					'tooltip_title' => __( 'Enabling Taxes', 'easy-digital-downloads' ),
					'tooltip_desc'  => __( 'With taxes enabled, customers will be taxed based on the rates you define, and are required to input their address on checkout so rates can be calculated accordingly.', 'easy-digital-downloads' ),
				),
				'tax_help'             => array(
					'id'   => 'tax_help',
					'name' => '',
					/* translators: %s: tax setup documentation URL. */
					'desc' => sprintf( __( 'Visit the <a href="%s" target="_blank">Tax setup documentation</a> for further information. <p class="description">If you need VAT support, there are options listed on the documentation page.</p>', 'easy-digital-downloads' ), 'https://easydigitaldownloads.com/docs/tax-settings/' ),
					'type' => 'descriptive_text',
				),
				'prices_include_tax'   => array(
					'id'            => 'prices_include_tax',
					'name'          => __( 'Prices Include Tax', 'easy-digital-downloads' ),
					'desc'          => __( 'This option affects how you enter prices.', 'easy-digital-downloads' ),
					'type'          => 'radio',
					'std'           => 'no',
					'options'       => array(
						'yes' => __( 'Yes, I will enter prices inclusive of tax', 'easy-digital-downloads' ),
						'no'  => __( 'No, I will enter prices exclusive of tax', 'easy-digital-downloads' ),
					),
					'tooltip_title' => __( 'Prices Inclusive of Tax', 'easy-digital-downloads' ),
					'tooltip_desc'  => __( 'When using prices inclusive of tax, you will be entering your prices as the total amount you want a customer to pay for the download, including tax. Easy Digital Downloads will calculate the proper amount to tax the customer for the defined total price.', 'easy-digital-downloads' ),
				),
				'display_tax_rate'     => array(
					'id'    => 'display_tax_rate',
					'name'  => __( 'Show Tax Rate on Prices', 'easy-digital-downloads' ),
					'check' => __( 'Some countries require a notice that product prices include tax.', 'easy-digital-downloads' ),
					'type'  => 'checkbox_toggle',
				),
				'checkout_include_tax' => array(
					'id'            => 'checkout_include_tax',
					'name'          => __( 'Show in Checkout', 'easy-digital-downloads' ),
					'desc'          => __( 'Should prices on the checkout page be shown with or without tax?', 'easy-digital-downloads' ),
					'type'          => 'select',
					'std'           => 'no',
					'options'       => array(
						'yes' => __( 'Including tax', 'easy-digital-downloads' ),
						'no'  => __( 'Excluding tax', 'easy-digital-downloads' ),
					),
					'tooltip_title' => __( 'Taxes Displayed for Products on Checkout', 'easy-digital-downloads' ),
					'tooltip_desc'  => __( 'This option will determine whether the product price displays with or without tax on checkout.', 'easy-digital-downloads' ),
				),
			),
		);
	}
}
