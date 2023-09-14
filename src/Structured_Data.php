<?php
/**
 * Structured Data Object.
 *
 * @package     EDD
 * @subpackage  StructuredData
 * @copyright   Copyright (c) 2018, Easy Digital Downloads, LLC
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       3.0
 */
namespace EDD;

// Exit if accessed directly
defined( 'ABSPATH' ) || exit;

/**
 * EDD_Structured_Data Class.
 *
 * @since 3.0
 */
class Structured_Data {

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
	private function hooks() {
		add_action( 'wp_footer', array( $this, 'output_structured_data' ) );
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
		return $this->data;
	}

	/**
	 * Reset the data.
	 *
	 * @since 3.1.4
	 * @return void
	 */
	public function reset() {
		$this->data = array();
	}

	/**
	 * Set structured data. This is then output in `wp_footer`.
	 *
	 * @access private
	 * @since 3.0
	 *
	 * @param array $data JSON-LD structured data.
	 *
	 * @return bool True if data was set, false otherwise.
	 */
	private function set_data( $data = null ) {
		if ( is_null( $data ) || empty( $data ) || ! is_array( $data ) ) {
			return false;
		}

		// Ensure the type exists and matches the format expected.
		if ( ! isset( $data['@type'] ) || ! preg_match( '|^[a-zA-Z]{1,20}$|', $data['@type'] ) ) {
			return false;
		}

		$this->data[] = $data;

		return true;
	}

	/**
	 * Generate the structured data for a given context.
	 *
	 * @access public
	 * @since 3.0
	 *
	 * @param mixed string|false $context Default empty as the class figures out what the context is automatically.
	 * @param mixed $args Arguments that can be passed to the generators.
	 *
	 * @return string
	 */
	public function generate_structured_data( $context = false, $args = null ) {
		if ( is_singular( 'download' ) || 'download' === $context ) {
			$this->generate_download_data( $args );
		}

		/**
		 * Allow actions to fire here to allow for different types of structured data.
		 *
		 * @since 3.0
		 *
		 * @param EDD_Structured_Data Instance of the object.
		 * @param mixed string|bool $context Context.
		 */
		do_action( 'edd_generate_structured_data', $this, $context );
	}

	/**
	 * Generate structured data for a download.
	 *
	 * @access public
	 * @since 3.0
	 *
	 * @param mixed int|EDD_Download Download ID or EDD_Download object to generate data for.
	 *
	 * @return bool True if data generated successfully, false otherwise.
	 */
	public function generate_download_data( $download = false ) {
		if ( false === $download || is_null( $download ) ) {
			global $post;
			$download = edd_get_download( $post->ID );
		} elseif ( is_int( $download ) ) {
			$download = edd_get_download( $download );
		} else {
			return false;
		}

		// Return false if a download object could not be retrieved.
		if ( ! $download instanceof \EDD_Download ) {
			return false;
		}

		$data = array(
			'@type' => 'Product',
			'name'  => $download->post_title,
			'url'   => $this->get_url( $download ),
			'brand' => array(
				'@type' => 'https://schema.org/Brand',
				'name'  => get_bloginfo( 'name' ),
			),
			'sku'   => '-' === $download->get_sku()
				? $download->ID
				: $download->get_sku(),
			'image' => $this->get_image( $download ),
		);

		// Add description if it is not blank.
		if ( '' !== $download->post_excerpt ) {
			$data['description'] = $download->post_excerpt;
		}

		$data['offers'] = $this->get_offers( $download, $data );

		$download_categories = wp_get_post_terms( $download->ID, 'download_category' );
		if ( is_array( $download_categories ) && ! empty( $download_categories ) ) {
			$download_categories = wp_list_pluck( $download_categories, 'name' );
			$data['category']    = implode( ', ', $download_categories );
		}

		/**
		 * Filter the structured data for a download.
		 *
		 * @since 3.0
		 *
		 * @param array        $data Structured data for a download.
		 * @param EDD_Download $download Download object (added in 3.1.4).
		 */
		$data = apply_filters( 'edd_generate_download_structured_data', $data, $download );

		$this->set_data( $data );

		return true;
	}

	/**
	 * Sanitize the structured data.
	 *
	 * @access private
	 * @since 3.0
	 *
	 * @param array $data Data to be sanitized.
	 *
	 * @return array Sanitized data.
	 */
	private function sanitize_data( $data ) {
		$sanitized = array();

		// Bail with an empty array if data does not exist.
		if ( ! $data || ! is_array( $data ) ) {
			return $sanitized;
		}

		foreach ( $data as $key => $value ) {
			$key               = sanitize_text_field( $key );
			$sanitized[ $key ] = is_array( $value )
				? $this->sanitize_data( $value )
				: sanitize_text_field( $value );
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
		$this->generate_structured_data();

		// Bail if no data was generated.
		$data = $this->get_data();
		if ( empty( $data ) ) {
			return;
		}

		$structured_data = $this->sanitize_data( $data );
		foreach ( $structured_data as $k => $v ) {
			$structured_data[ $k ]['@context'] = 'https://schema.org/';
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

		$output_data = $this->encoded_data();
		if ( empty( $output_data ) ) {
			return false;
		}

		echo '<script type="application/ld+json">' . $output_data . '</script>';

		return true;
	}

	/**
	 * Gets the offer(s) for a download.
	 *
	 * @since 3.1.4
	 * @param EDD_Download $download Download object.
	 * @return array
	 */
	private function get_offers( $download ) {
		if ( $download->has_variable_prices() ) {
			$variable_prices = $download->get_prices();

			$offers = array();
			foreach ( $variable_prices as $key => $price ) {
				$offers[] = $this->get_variable_price_offer( $price, $download, $key );
			}

			return $offers;
		}

		return $this->get_single_price_offer( $download );
	}

	/**
	 * Gets the offer data for a variable price.
	 *
	 * @since 3.1.4
	 * @param array        $price
	 * @param EDD_Download $download
	 * @param int          $key
	 * @return array
	 */
	private function get_variable_price_offer( $price, $download, $key ) {
		$price_id = isset( $price['index'] ) ? $price['index'] : $key;
		$offer    = array(
			'@type'           => 'Offer',
			'price'           => $price['amount'],
			'priceCurrency'   => edd_get_currency(),
			'priceValidUntil' => date( 'c', time() + YEAR_IN_SECONDS ),
			'itemOffered'     => edd_get_download_name( $download->ID, $price_id ),
			'url'             => $this->get_url( $download ),
			'availability'    => 'https://schema.org/InStock',
			'seller'          => array(
				'@type' => 'Organization',
				'name'  => get_bloginfo( 'name' ),
			),
		);

		/**
		 * Filter the structured data for a variable price offer.
		 *
		 * @since 3.1.4
		 * @param array        $offer   Structured data for a variable price offer.
		 * @param EDD_Download $download Download object.
		 * @param array        $price    Price data.
		 */
		return apply_filters( 'edd_generate_download_structured_data_variable_price_offer', $offer, $download, $price );
	}

	/**
	 * Gets the offer data for a single price product.
	 *
	 * @param EDD_Download $download
	 * @return array
	 */
	private function get_single_price_offer( $download ) {
		$offer = array(
			'@type'           => 'Offer',
			'price'           => $download->get_price(),
			'priceCurrency'   => edd_get_currency(),
			'priceValidUntil' => null,
			'url'             => $this->get_url( $download ),
			'availability'    => 'https://schema.org/InStock',
			'seller'          => array(
				'@type' => 'Organization',
				'name'  => get_bloginfo( 'name' ),
			),
		);

		/**
		 * Filter the structured data for a single price offer.
		 *
		 * @since 3.1.4
		 * @param array        $offer   Structured data for a single price offer.
		 * @param EDD_Download $download Download object.
		 */
		return apply_filters( 'edd_generate_download_structured_data_offer', $offer, $download );
	}

	/**
	 * Gets the download URL.
	 *
	 * @param EDD_Download $download
	 * @return string
	 */
	private function get_url( $download ) {
		return get_permalink( $download->ID );
	}

	/**
	 * Gets the download image URL.
	 *
	 * @since 3.1.4
	 * @param EDD_Download $download
	 * @return string
	 */
	private function get_image( $download ) {
		if ( ! has_post_thumbnail( $download->ID ) ) {
			return '';
		}

		$image_url = wp_get_attachment_image_url( get_post_thumbnail_id( $download->ID ) );
		if ( false !== $image_url ) {
			return esc_url( $image_url );
		}

		return '';
	}
}
