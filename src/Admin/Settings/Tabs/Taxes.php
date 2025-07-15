<?php
/**
 * Easy Digital Downloads Taxes Settings.
 *
 * @package     EDD\Admin\Settings\Tabs
 * @copyright   Copyright (c) 2023, Sandhills Development, LLC
 * @license     https://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       3.1.4
 */

namespace EDD\Admin\Settings\Tabs;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit; // @codeCoverageIgnore

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
	 * Update the docs link.
	 *
	 * @since 3.5.0
	 * @param string $link The link to update.
	 * @return string
	 */
	public function update_docs_link( $link ) {
		if ( ! $this->is_admin_page( 'settings', 'taxes' ) ) {
			return $link;
		}

		$section = $this->get_section();
		if ( 'vat' === $section ) {
			return 'https://easydigitaldownloads.com/docs/eu-vat/';
		}

		if ( 'rates' === $section ) {
			return 'https://easydigitaldownloads.com/docs/tax-settings/#rates';
		}

		return 'https://easydigitaldownloads.com/docs/tax-settings/';
	}

	/**
	 * Register the settings for this tab.
	 *
	 * @since 3.1.4
	 * @return array
	 */
	protected function register() {
		return array(
			'main' => array(
				'enable_taxes'         => array(
					'id'      => 'enable_taxes',
					'name'    => __( 'Enable Taxes', 'easy-digital-downloads' ),
					'type'    => 'checkbox_toggle',
					'check'   => __( 'Enable taxes and tax rates.', 'easy-digital-downloads' ),
					/* translators: %s: tax setup documentation URL. */
					'desc'    => sprintf( __( 'Visit the <a href="%s" target="_blank">Tax setup documentation</a> for further information.', 'easy-digital-downloads' ), 'https://easydigitaldownloads.com/docs/tax-settings/' ),
					'tooltip' => array(
						'title'   => __( 'Enabling Taxes', 'easy-digital-downloads' ),
						'content' => __( 'With taxes enabled, customers will be taxed based on the rates you define, and are required to input their address on checkout so rates can be calculated accordingly.', 'easy-digital-downloads' ),
					),
					'data'    => array(
						'edd-requirement' => 'enable_taxes',
					),
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
					'class'         => $this->get_requires_css_class( 'enable_taxes' ),
				),
				'display_tax_rate'     => array(
					'id'    => 'display_tax_rate',
					'name'  => __( 'Show Tax Rate on Prices', 'easy-digital-downloads' ),
					'check' => __( 'Some countries require a notice that product prices include tax.', 'easy-digital-downloads' ),
					'type'  => 'checkbox_toggle',
					'class' => $this->get_requires_css_class( 'enable_taxes' ),
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
					'class'         => $this->get_requires_css_class( 'enable_taxes' ),
				),
				'vat_enable'           => $this->get_vat_enable(),
			),
		);
	}

	/**
	 * Get the VAT enable setting.
	 *
	 * @since 3.5.0
	 * @return array
	 */
	private function get_vat_enable() {
		$setting = array(
			'id'      => 'vat_enable',
			'name'    => __( 'EU VAT', 'easy-digital-downloads' ),
			'check'   => __( 'Enable VAT handling and validation.', 'easy-digital-downloads' ),
			'type'    => 'checkbox_toggle',
			'class'   => $this->get_requires_css_class( 'enable_taxes' ),
			'options' => array(
				'disabled' => true,
			),
			'desc'    => '',
		);

		if ( ! $this->is_admin_page( 'settings', 'taxes' ) ) {
			return $setting;
		}

		$description = array();

		if ( function_exists( '\Barn2\Plugin\EDD_VAT\edd_eu_vat' ) ) {
			$description[] = __( 'Enabling VAT handling in EDD will automatically deactivate the Easy Digital Downloads - EU VAT plugin.', 'easy-digital-downloads' );
		}

		if ( ! edd_is_pro() ) {
			$pass_manager = new \EDD\Admin\Pass_Manager();
			if ( $pass_manager->has_pass() ) {
				$description[] = __( 'This feature requires Easy Digital Downloads (Pro) to be installed and activated.', 'easy-digital-downloads' );
			} else {
				$description[] = sprintf(
					/* translators: 1: opening button tag, 2: closing button tag */
					__( '%1$sUpgrade to Pro%2$s to enable VAT handling.', 'easy-digital-downloads' ),
					'<button class="edd-pro-upgrade button-link edd-promo-notice__trigger">',
					'</button>'
				);
			}
		}

		if ( ! empty( $description ) ) {
			$setting['desc'] = implode( '<br />', $description );
		}

		return $setting;
	}
}
