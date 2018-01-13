<?php
/**
 * Structured Data
 *
 * @package     EDD
 * @subpackage  StructuredData
 * @copyright   Copyright (c) 2018, Pippin Williamson
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       3.0
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * EDD_Structured_Data Class.
 *
 * @since 3.0
 */
class EDD_Structured_Data {

	/**
	 * Structured data.
	 *
	 * @var array
	 */
	private $data = array();

	/**
	 * Constructor.
	 *
	 * @since 3.0
	 */
	public function __construct() {
		$this->hooks();
	}

	/**
	 * Register the hooks.
	 *
	 * @since 3.0
	 */
	public function hooks() {
		// Actions
		add_action( 'wp_footer', array( $this, 'output_structured_data' ) );

		// Filters
	}

	/**
	 * Get raw data. This data is not formatted in any way.
	 *
	 * @access public
	 * @since 3.0
	 *
	 * @return array Raw data.
	 */
	public function get_data() {
		/**
		 * Allow data to be filtered being returned.
		 *
		 * @since 3.0
		 *
		 * @param array $data Structured data.
		 */
		return apply_filters( 'edd_structured_data_get_data', $this->data );
	}

	/**
	 * Set structured data. This is then output in `wp_footer`.
	 *
	 * @access public
	 * @since 3.0
	 *
	 * @param array $data JSON-LD structured data.
	 *
	 * @return bool True if data was set, false otherwise.
	 */
	public function set_data( $data = null ) {
		if ( is_null( $data ) || empty( $data ) || ! is_array( $data ) ) {
			return false;
		}

		// Ensure the type exists and matches the format expected.
		if ( ! isset( $data['@type'] ) || ! preg_match( '|^[a-zA-Z]{1,20}$|', $data['@type'] ) ) {
			return false;
		}

		/**
		 * Apply data to be filtered before being added.
		 *
		 * @since 3.0
		 *
		 * @param array $data Structured data to be added.
		 */
		$this->data[] = apply_filters( 'edd_structured_data_set_data', $data );

		return true;
	}

	/**
	 * Get the structured data for a given context.
	 *
	 * @access public
	 * @since 3.0
	 *
	 * @param string $context Default empty as the class figures out what the context is automatically.
	 * @return string
	 */
	public function get_structured_data( $context = false ) {

	}

	/**
	 * Generate structured data for a download.
	 *
	 * @access private
	 * @since 3.0
	 *
	 * @param mixed int|EDD_Download Download ID or EDD_Download object to generate data for.
	 * @return bool
	 */
	public function generate_download_data( $download = false ) {
		if ( false === $download ) {
			global $post;
			$download = new EDD_Download( $post->ID );
		} elseif ( is_int( $download ) ) {
			$download = new EDD_Download( $download );
		} else {
			return false;
		}

		$data = array(
			'@type'       => 'Product',
			'name'        => $download->post_title,
			'description' => $download->post_excerpt,
			'url'         => get_permalink( $download->ID ),
			'brand'       => array(
				'@type' => 'Thing',
				'name'  => get_bloginfo( 'name' ),
			),
			'image'       => wp_get_attachment_image_url( get_post_thumbnail_id( $download->ID ) ),
			'sku'         => $download->get_sku(),
		);

		if ( $download->has_variable_prices() ) {
			$variable_prices = $download->get_prices();

			$offers = array();

			foreach ( $variable_prices as $price ) {
				$offers[] = array(
					'@type'           => 'Offer',
					'price'           => $price['amount'],
					'priceCurrency'   => edd_get_currency(),
					'priceValidUntil' => null,
					'itemOffered'     => $data['name'] . ' - ' . $price['name'],
					'url'             => $data['url'],
					'availability'    => 'http://schema.org/InStock',
				);
			}

			$data['offers'] = $offers;
		} else {
			$data['offers'] = array(
				'@type'         => 'Offer',
				'priceCurrency' => edd_get_currency(),
				'price'         => $download->get_price(),
			);
		}

		/**
		 * Filter the structured data for a download.
		 *
		 * @since 3.0
		 *
		 * @param array $data Structured data for a download.
		 */
		$data = apply_filters( 'edd_generate_download_structured_data', $data );

		$this->set_data( $data );
	}

	/**
	 * Sanitize the structured data.
	 *
	 * @access private
	 * @since 3.0
	 *
	 * @param array $data Data to be sanitized.
	 * @return array Sanitized data.
	 */
	private function sanitize_data( $data ) {
		if ( ! $data || ! is_array( $data ) ) {
			return array();
		}

		foreach ( $data as $key => $value ) {
			$key = sanitize_text_field( $key );
			$sanitized[ $key ] = is_array( $value ) ? $this->sanitize_data( $value ) : sanitize_text_field( $value );
		}

		return $sanitized;
	}

	/**
	 * Encode the data, ready for output.
	 *
	 * @access private
	 * @since 3.0
	 */
	private function encoded_data() {
		$structured_data = $this->get_data();

		foreach ( $structured_data as $k => $v ) {
			$structured_data[ $k ]['@context'] = 'http://schema.org/';
		}

		return wp_json_encode( $structured_data );
	}

	/**
	 * Output the structured data.
	 *
	 * @access public
	 * @since 3.0
	 *
	 * @return bool True by default, false if structured data does not exist.
	 */
	public function output_structured_data() {
		if ( is_singular( 'download' ) ) {
			$this->generate_download_data();
		}

		$this->data = $this->sanitize_data( $this->data );
		echo '<script type="application/ld+json">' . $this->encoded_data() . '</script>';

		return true;
	}
}