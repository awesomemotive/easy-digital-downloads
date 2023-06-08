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
		$success_page  = edd_get_option( 'success_page', '' );
		$failure_page  = edd_get_option( 'failure_page', '' );
		$pages         = array(
			'checkout'       => array(
				'label' => 'Checkout',
				'value' => ! empty( $purchase_page ) ? 'Valid' : 'Invalid',
			),
			'checkout_uri'   => array(
				'label' => 'Checkout Page',
				'value' => ! empty( $purchase_page ) ? get_permalink( $purchase_page ) : '',
			),
			'success_uri'    => array(
				'label' => 'Success Page',
				'value' => ! empty( $success_page ) ? get_permalink( $success_page ) : '',
			),
			'failure_uri'    => array(
				'label' => 'Failure Page',
				'value' => ! empty( $failure_page ) ? get_permalink( $failure_page ) : '',
			),
			'downloads_slug' => array(
				'label' => 'Downloads Slug',
				'value' => defined( 'EDD_SLUG' ) ? '/' . EDD_SLUG : '/downloads',
			),
		);

		return $pages;
	}
}
