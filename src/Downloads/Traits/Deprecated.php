<?php
/**
 * Download Deprecated trait
 *
 * Holds the deprecated methods of EDD_Download so they stay out of the way of the class.
 *
 * @package     EDD\Downloads\Traits
 * @copyright   Copyright (c) 2026, Sandhills Development, LLC
 * @license     https://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       3.7.3
 */

namespace EDD\Downloads\Traits;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit; // @codeCoverageIgnore

trait Deprecated {

	/**
	 * Creates a download
	 *
	 * @since  2.3.6
	 * @deprecated 3.7.3 Use wp_insert_post() with 'post_type' => 'download' instead.
	 * @param  array  $data Array of attributes for a download
	 * @return mixed  false if data isn't passed and class not instantiated for creation, or New Download ID
	 */
	public function create( $data = array() ) {
		_edd_deprecated_function( 'EDD_Download::create', '3.7.3', 'wp_insert_post()' );

		if ( $this->id != 0 ) {
			return false;
		}

		$defaults = array(
			'post_type'   => 'download',
			'post_status' => 'draft',
			'post_title'  => __( 'New Download Product', 'easy-digital-downloads' )
		);

		$args = wp_parse_args( $data, $defaults );

		/**
		 * Fired before a download is created
		 *
		 * @param array $args The post object arguments used for creation.
		 */
		do_action( 'edd_download_pre_create', $args );

		$id = wp_insert_post( $args, true );

		$download = \WP_Post::get_instance( $id );

		/**
		 * Fired after a download is created
		 *
		 * @param int   $id   The post ID of the created item.
		 * @param array $args The post object arguments used for creation.
		 */
		do_action( 'edd_download_post_create', $id, $args );

		return $this->setup_download( $download );
	}

	/**
	 * Increment the sale count by one
	 *
	 * @since 2.2
	 * @param int $quantity The quantity to increase the sales by
	 * @return int New number of total sales
	 */
	public function increase_sales( $quantity = 1 ) {

		_edd_deprecated_function( 'EDD_Download::increase_sales', '3.0', 'EDD_Download::recalculate_net_sales_earnings()' );
		edd_recalculate_download_sales_earnings( $this->ID );

		$this->get_sales();
		do_action( 'edd_download_increase_sales', $this->ID, $this->sales, $this );

		return $this->sales;
	}

	/**
	 * Decrement the sale count by one
	 *
	 * @since 2.2
	 * @param int $quantity The quantity to decrease by
	 * @return int New number of total sales
	 */
	public function decrease_sales( $quantity = 1 ) {

		_edd_deprecated_function( 'EDD_Download::decrease_sales', '3.0', 'EDD_Download::recalculate_net_sales_earnings()' );
		$this->recalculate_net_sales_earnings();

		$this->get_sales();
		do_action( 'edd_download_decrease_sales', $this->ID, $this->sales, $this );

		return $this->sales;
	}

	/**
	 * Increase the earnings by the given amount
	 *
	 * @since 2.2
	 * @param int|float $amount Amount to increase the earnings by
	 * @return float New number of total earnings
	 */
	public function increase_earnings( $amount = 0 ) {

		_edd_deprecated_function( 'EDD_Download::increase_earnings', '3.0', 'edd_recalculate_download_sales_earnings()' );
		edd_recalculate_download_sales_earnings( $this->ID );

		$this->get_earnings();
		do_action( 'edd_download_increase_earnings', $this->ID, $this->earnings, $this );

		return $this->earnings;
	}

	/**
	 * Decrease the earnings by the given amount
	 *
	 * @since 2.2
	 * @param int|float $amount Number to decrease earning with
	 * @return float New number of total earnings
	 */
	public function decrease_earnings( $amount ) {

		_edd_deprecated_function( 'EDD_Download::decrease_earnings', '3.0', 'EDD_Download::recalculate_net_sales_earnings()' );

		$this->recalculate_net_sales_earnings();
		$this->get_earnings();

		do_action( 'edd_download_decrease_earnings', $this->ID, $this->earnings, $this );

		return $this->earnings;
	}
}
