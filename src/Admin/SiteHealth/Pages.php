<?php
/**
 * Gets the information about the EDD registered pages.
 *
 * @since 3.1.2
 * @package EDD\Admin\SiteHealth
 */

namespace EDD\Admin\SiteHealth;

/**
 * Loads the EDD Page Settings into the Site Health.
 *
 * @since 3.1.2
 */
class Pages {

	/**
	 * Gets the data for the section.
	 *
	 * @since 3.1.2
	 * @return array
	 */
	public function get() {
		return array(
			'label'  => __( 'Easy Digital Downloads &mdash; Pages', 'easy-digital-downloads' ),
			'fields' => $this->get_pages(),
		);
	}

	/**
	 * Gets the page data.
	 *
	 * @since 3.1.2
	 * @return array
	 */
	private function get_pages() {
		$purchase_page = edd_get_option( 'purchase_page', '' );
		$pages         = array(
			'checkout'           => array(
				'label' => 'Checkout',
				'value' => ! empty( $purchase_page ) ? 'Valid' : 'Invalid',
			),
			'checkout_uri'       => array(
				'label' => 'Checkout Page',
				'value' => ! empty( $purchase_page ) ? get_permalink( $purchase_page ) : '',
			),
			'confirmation_uri'   => array(
				'label' => 'Confirmation Page',
				'value' => get_permalink( edd_get_option( 'confirmation_page', '' ) ),
			),
			'success_uri'        => array(
				'label' => 'Receipt (Success) Page',
				'value' => get_permalink( edd_get_option( 'success_page', '' ) ),
			),
			'failure_uri'        => array(
				'label' => 'Failure Page',
				'value' => get_permalink( edd_get_option( 'failure_page', '' ) ),
			),
			'order_history_uri'  => array(
				'label' => 'Order History Page',
				'value' => get_permalink( edd_get_option( 'purchase_history_page', '' ) ),
			),
			'login_uri'          => array(
				'label' => 'Login Page',
				'value' => get_permalink( edd_get_option( 'login_page', '' ) ),
			),
			'login_redirect_uri' => array(
				'label' => 'Login Redirect Page',
				'value' => get_permalink( edd_get_option( 'login_redirect_page', '' ) ),
			),
			'downloads_slug'     => array(
				'label' => 'Downloads Slug',
				'value' => defined( 'EDD_SLUG' ) ? '/' . EDD_SLUG : '/downloads',
			),
		);

		return $pages;
	}
}
