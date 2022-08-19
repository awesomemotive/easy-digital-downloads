<?php
/**
 * Email Summary Blurb Class.
 *
 * @package     EDD
 * @subpackage  Emails
 * @copyright   Copyright (c) 2022, Easy Digital Downloads, LLC
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       3.1
 */

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

use \EDD\Utils\EnvironmentChecker;

/**
 * EDD_Email_Summary_Blurb Class.
 *
 * Takes care of preparing the necessary dataset, building the
 * email template and sending the Email Summary.
 *
 * @since 3.1
 */
class EDD_Email_Summary_Blurb {

	/**
	 * URL of the endpoint for the blurbs json.
	 *
	 * @since 3.1
	 *
	 * @const string
	 */
	const BLURB_ENDPOINT_URL = 'https://edd-localhost.test/wp-content/uploads/product-blurbs.json';

	/**
	 * Environment Checker class.
	 *
	 * @var EnvironmentChecker
	 */
	protected $environmentChecker;

	/**
	 * Class constructor.
	 *
	 * @since 3.1
	 */
	public function __construct() {

		$this->environmentChecker = new EnvironmentChecker();

	}

	/**
	 * Fetch all blurbs from remote endpoint.
	 *
	 * @since 3.1
	 *
	 * @return array
	 */
	public function fetch_blurbs() {
		$blurbs = array();

		$res = wp_remote_get(
			self::BLURB_ENDPOINT_URL,
			array(
				'sslverify' => false, // @todo - Remove!
			)
		);

		if ( is_wp_error( $res ) ) {
			edd_debug_log( __( 'Error while retrieving Email Summary Blurbs', 'easy-digital-downloads' ), true ); // @todo - Add exact error response!
			return $blurbs;
		}

		$body = wp_remote_retrieve_body( $res );

		if ( empty( $body ) ) {
			return $blurbs;
		}

		$blurbs = json_decode( $body, true );

		return $blurbs;
	}


	/**
	 * Get next blurb that wasn't sent yet.
	 *
	 * @since 3.1
	 *
	 * @return array
	 */
	public function get_next() {
		$all_data = $this->fetch_blurbs();
		$blurbs   = array();

		// Loop through the fetched blurbs and determine all that meet the conditions
		foreach ( $all_data['blurbs'] as $key => $blurb ) {
			if( $this->does_blurb_meet_conditions( $blurb ) ) {
				$blurbs[] = $blurb;
			}
		}

		// @todo - It might be good to sort the array so that we get reliable hash?
		// zan_return( md5(json_encode($blurbs[0]))	);

		return $blurbs;
	}

	/**
	 * Check if store pass matches the condition from the blurb
	 *
	 * @since 3.1
	 *
	 * @return bool
	 */
	public function check_blurb_current_pass( $condition ) {
		if ( ! $this->environmentChecker->meetsCondition( $condition ) ) {
			return false;
		}

		return true;
	}

	/**
	 * Check if store has certain plugins active.
	 *
	 * @since 3.1
	 *
	 * @return bool
	 */
	public function check_blurb_active_plugins( $active_plugins ) {
		foreach ($active_plugins as $plugin_name) {
			if ( ! is_plugin_active( $plugin_name ) ) {
				return false;
			}
		}
		return true;
	}

	/**
	 * Check if store has certain plugins inactive.
	 *
	 * @since 3.1
	 *
	 * @return bool
	 */
	public function check_blurb_inactive_plugins( $inactive_plugins ) {
		foreach ($inactive_plugins as $plugin_name) {
			if ( is_plugin_active( $plugin_name ) ) {
				return false;
			}
		}
		return true;
	}

	/**
	 * Check if store has specific products/downloads active
	 *
	 * @since 3.1
	 *
	 * @return bool
	 */
	public function check_blurb_has_downloads( $conditions ) {
		foreach ($conditions as $condition_name) {
			// Check if store has any products that are free
			if ( 'free' == $condition_name ) {
				$args = array(
					'post_type'      => 'download',
					'posts_per_page' => 1,
					'fields'         => 'ids',
					'no_found_rows'  => true,
					'meta_query'     => array(
						array(
							'key'    => 'edd_price',
							'value'  => '0.00',
						),
					),
				);

				$downloads = new WP_Query( $args );
				if ( $downloads->post_count == 0 ) {
					return false;
				}
			}
		}
		return true;
	}

	/**
	 * Check if blurb meets conditions.
	 *
	 * @since 3.1
	 *
	 * @return bool
	 */
	public function does_blurb_meet_conditions( $blurb ) {
		if ( isset( $blurb['conditions'] ) && ! empty( $blurb['conditions'] ) ) {
			foreach ( $blurb['conditions'] as $condition_name => $condition ) {
				if ( empty( $condition ) ) {
					continue;
				}

				// Pass check.
				if ( 'current_pass' == $condition_name ) {
					if ( ! $this->check_blurb_current_pass( $condition ) ) {
						return false;
					}
				}

				// Active plugins check.
				if ( 'active_plugins' == $condition_name ) {
					if ( ! $this->check_blurb_active_plugins( $condition ) ) {
						return false;
					}
				}

				// Inactive plugins check.
				if ( 'inactive_plugins' == $condition_name ) {
					if ( ! $this->check_blurb_inactive_plugins( $condition ) ) {
						return false;
					}
				}

				// Check for specific product/downloads
				if ( 'has_downloads' == $condition_name ) {
					if ( ! $this->check_blurb_has_downloads( $condition ) ) {
						return false;
					}
				}
			}
		}

		return true;
	}

}
