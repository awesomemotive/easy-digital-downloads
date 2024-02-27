<?php
/**
 * Easy Digital Downloads General Settings
 *
 * @package EDD
 * @subpackage  Settings
 * @copyright   Copyright (c) 2023, Easy Digital Downloads
 * @license     https://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since 3.1.4
 */

namespace EDD\Admin\Settings\Tabs;

defined( 'ABSPATH' ) || exit;

/**
 * General settings tab.
 *
 * @since 3.1.4
 */
class General extends Tab {

	/**
	 * Get the ID for this tab.
	 *
	 * @since 3.1.4
	 * @var string
	 */
	protected $id = 'general';

	/**
	 * Register the settings for this tab.
	 *
	 * @since 3.1.4
	 * @return array
	 */
	protected function register() {

		$pages = edd_get_pages();

		return array(
			'main'     => array(
				'business_settings'    => array(
					'id'            => 'business_settings',
					'name'          => '<h3>' . __( 'Business Info', 'easy-digital-downloads' ) . '</h3>',
					'desc'          => '',
					'type'          => 'header',
					'tooltip_title' => __( 'Business Information', 'easy-digital-downloads' ),
					'tooltip_desc'  => __( 'Easy Digital Downloads uses the following business information for things like pre-populating tax fields, and connecting third-party services with the same information.', 'easy-digital-downloads' ),
				),
				'entity_name'          => array(
					'id'          => 'entity_name',
					'name'        => __( 'Business Name', 'easy-digital-downloads' ),
					'desc'        => __( 'The official (legal) name of your store. Defaults to Site Title if empty.', 'easy-digital-downloads' ),
					'type'        => 'text',
					'std'         => $this->get_site_name(),
					'placeholder' => $this->get_site_name(),
				),
				'entity_type'          => array(
					'id'      => 'entity_type',
					'name'    => __( 'Business Type', 'easy-digital-downloads' ),
					'desc'    => __( 'Choose "Individual" if you do not have an official/legal business ID, or "Company" if a registered business entity exists.', 'easy-digital-downloads' ),
					'type'    => 'select',
					'options' => array(
						'individual' => esc_html__( 'Individual', 'easy-digital-downloads' ),
						'company'    => esc_html__( 'Company', 'easy-digital-downloads' ),
					),
				),
				'business_address'     => array(
					'id'          => 'business_address',
					'name'        => __( 'Business Address', 'easy-digital-downloads' ),
					'type'        => 'text',
					'placeholder' => '',
				),
				'business_address_2'   => array(
					'id'          => 'business_address_2',
					'name'        => __( 'Business Address (Extra)', 'easy-digital-downloads' ),
					'type'        => 'text',
					'placeholder' => '',
				),
				'business_city'        => array(
					'id'          => 'business_city',
					'name'        => __( 'Business City', 'easy-digital-downloads' ),
					'type'        => 'text',
					'placeholder' => '',
				),
				'business_postal_code' => array(
					'id'          => 'business_postal_code',
					'name'        => __( 'Business Postal Code', 'easy-digital-downloads' ),
					'type'        => 'text',
					'size'        => 'medium',
					'placeholder' => '',
				),
				'base_country'         => array(
					'id'          => 'base_country',
					'name'        => __( 'Business Country', 'easy-digital-downloads' ),
					'type'        => 'select',
					'options'     => edd_get_country_list(),
					'chosen'      => true,
					'field_class' => 'edd_countries_filter',
					'placeholder' => __( 'Select a country', 'easy-digital-downloads' ),
					'data'        => array(
						'nonce' => wp_create_nonce( 'edd-country-field-nonce' ),
					),
				),
				'base_state'           => array(
					'id'          => 'base_state',
					'name'        => __( 'Business Region', 'easy-digital-downloads' ),
					'type'        => 'shop_states',
					'chosen'      => true,
					'field_class' => 'edd_regions_filter',
					'placeholder' => __( 'Select a region', 'easy-digital-downloads' ),
				),
			),
			'pages'    => array(
				'page_settings'         => array(
					'id'            => 'page_settings',
					'name'          => '<h3>' . __( 'Pages', 'easy-digital-downloads' ) . '</h3>',
					'desc'          => '',
					'type'          => 'header',
					'tooltip_title' => __( 'Page Settings', 'easy-digital-downloads' ),
					'tooltip_desc'  => __( 'Easy Digital Downloads uses the pages below for handling the display of checkout, purchase confirmation, purchase history, and purchase failures. If pages are deleted or removed in some way, they can be recreated manually from the Pages menu. When re-creating the pages, enter the shortcode shown in the page content area.', 'easy-digital-downloads' ),
				),
				'purchase_page'         => array(
					'id'          => 'purchase_page',
					'name'        => __( 'Primary Checkout Page', 'easy-digital-downloads' ),
					'desc'        => __( 'This is the checkout page where buyers will complete their purchases.<br>The <code>[download_checkout]</code> shortcode must be on this page.', 'easy-digital-downloads' ),
					'type'        => 'select',
					'options'     => $pages,
					'chosen'      => true,
					'placeholder' => __( 'Select a page', 'easy-digital-downloads' ),
				),
				'success_page'          => array(
					'id'          => 'success_page',
					'name'        => __( 'Success Page', 'easy-digital-downloads' ),
					'desc'        => __( 'This is the page buyers are sent to after completing their purchases.<br>The <code>[edd_receipt]</code> shortcode should be on this page.', 'easy-digital-downloads' ),
					'type'        => 'select',
					'options'     => $pages,
					'chosen'      => true,
					'placeholder' => __( 'Select a page', 'easy-digital-downloads' ),
				),
				'failure_page'          => array(
					'id'          => 'failure_page',
					'name'        => __( 'Failed Transaction Page', 'easy-digital-downloads' ),
					'desc'        => __( 'This is the page buyers are sent to if their transaction is cancelled or fails.', 'easy-digital-downloads' ),
					'type'        => 'select',
					'options'     => $pages,
					'chosen'      => true,
					'placeholder' => __( 'Select a page', 'easy-digital-downloads' ),
				),
				'purchase_history_page' => array(
					'id'          => 'purchase_history_page',
					'name'        => __( 'Purchase History Page', 'easy-digital-downloads' ),
					'desc'        => __( 'This page shows a complete purchase history for the current user, including download links.<br>The <code>[purchase_history]</code> shortcode should be on this page.', 'easy-digital-downloads' ),
					'type'        => 'select',
					'options'     => $pages,
					'chosen'      => true,
					'placeholder' => __( 'Select a page', 'easy-digital-downloads' ),
				),
				'login_redirect_page'   => array(
					'id'          => 'login_redirect_page',
					'name'        => __( 'Login Redirect Page', 'easy-digital-downloads' ),
					'desc'        => sprintf(
						/* translators: %s: home URL */
						__( 'If a customer logs in using the <code>[edd_login]</code> shortcode, this is the page they will be redirected to.<br>Note: override using the redirect shortcode attribute: <code>[edd_login redirect="%s"]</code>.', 'easy-digital-downloads' ),
						trailingslashit( home_url() )
					),
					'type'        => 'select',
					'options'     => $pages,
					'chosen'      => true,
					'placeholder' => __( 'Select a page', 'easy-digital-downloads' ),
				),
			),
			'currency' => array(
				'currency_settings'   => array(
					'id'            => 'currency_settings',
					'name'          => '<h3>' . __( 'Currency', 'easy-digital-downloads' ) . '</h3>',
					'desc'          => '',
					'type'          => 'header',
					'tooltip_title' => __( 'Currency Settings', 'easy-digital-downloads' ),
					'tooltip_desc'  => __( 'Different countries use different formatting for their currency. You will want to pick what most of your users will expect to use.', 'easy-digital-downloads' ),
				),
				'currency'            => array(
					'id'      => 'currency',
					'name'    => __( 'Currency', 'easy-digital-downloads' ),
					'desc'    => __( 'Choose your currency. Note that some payment gateways have currency restrictions.', 'easy-digital-downloads' ),
					'type'    => 'select',
					'chosen'  => true,
					'options' => edd_get_currencies(),
				),
				'currency_position'   => array(
					'id'      => 'currency_position',
					'name'    => __( 'Currency Position', 'easy-digital-downloads' ),
					'desc'    => __( 'Choose the location of the currency sign.', 'easy-digital-downloads' ),
					'type'    => 'select',
					'options' => array(
						'before' => __( 'Before ($10)', 'easy-digital-downloads' ),
						'after'  => __( 'After (10$)', 'easy-digital-downloads' ),
					),
				),
				'thousands_separator' => array(
					'id'          => 'thousands_separator',
					'name'        => __( 'Thousandths Separator', 'easy-digital-downloads' ),
					'desc'        => __( 'The symbol to separate thousandths. Usually <code>,</code> or <code>.</code>.', 'easy-digital-downloads' ),
					'type'        => 'text',
					'size'        => 'small',
					'field_class' => 'code',
					'std'         => ',',
					'placeholder' => ',',
				),
				'decimal_separator'   => array(
					'id'          => 'decimal_separator',
					'name'        => __( 'Decimal Separator', 'easy-digital-downloads' ),
					'desc'        => __( 'The symbol to separate decimal points. Usually <code>,</code> or <code>.</code>.', 'easy-digital-downloads' ),
					'type'        => 'text',
					'size'        => 'small',
					'field_class' => 'code',
					'std'         => '.',
					'placeholder' => '.',
				),
			),
			'api'      => array(
				'api_settings'                => array(
					'id'            => 'api_settings',
					'name'          => '<h3>' . __( 'API', 'easy-digital-downloads' ) . '</h3>',
					'desc'          => '',
					'type'          => 'header',
					'tooltip_title' => __( 'API Settings', 'easy-digital-downloads' ),
					'tooltip_desc'  => __( 'The Easy Digital Downloads REST API provides access to store data through our API endpoints. Enable this setting if you would like all user accounts to be able to generate their own API keys.', 'easy-digital-downloads' ),
				),
				'api_allow_user_keys'         => array(
					'id'    => 'api_allow_user_keys',
					'name'  => __( 'Allow User Keys', 'easy-digital-downloads' ),
					'check' => __( 'Allow all users to generate API keys.', 'easy-digital-downloads' ),
					'desc'  => __( 'Users who can <code>manage_shop_settings</code> are always allowed to generate keys.', 'easy-digital-downloads' ),
					'type'  => 'checkbox_toggle',
				),
				'enable_public_request_logs' => $this->get_enable_public_request_logs(),
				'api_help'                    => array(
					'id'   => 'api_help',
					'desc' => sprintf(
						/* translators: %s: API documentation URL */
						__( 'Visit the <a href="%s" target="_blank">REST API documentation</a> for further information.', 'easy-digital-downloads' ),
						edd_link_helper(
							'https://easydigitaldownloads.com/categories/docs/api-reference/',
							array(
								'utm_medium'  => 'settings',
								'utm_content' => 'api-documentation',
							)
						)
					),
					'type' => 'descriptive_text',
				),
			),
		);
	}

	/**
	 * Gets the disable public requests logs setting.
	 *
	 * @since 3.2.8
	 * @return array
	 */
	private function get_enable_public_request_logs() {
		$link = edd_get_admin_url(
			array(
				'page' => 'edd-tools',
				'tab'  => 'logs',
				'view' => 'api_requests',
			)
		);

		return array(
			'id'    => 'enable_public_request_logs',
			'name'  => __( 'Request Logs', 'easy-digital-downloads' ),
			'check' => __( 'Log public API requests.', 'easy-digital-downloads' ),
			'desc'  => sprintf(
				/* translators: %1$s: opening anchor tag, %2$s: closing anchor tag */
				__( 'Authenticated requests to the EDD API are always logged. %1$sView the API request logs.%2$s', 'easy-digital-downloads' ),
				'<a href="' . $link . '">',
				'</a>'
			),
			'type'  => 'checkbox_toggle',
		);
	}
}
