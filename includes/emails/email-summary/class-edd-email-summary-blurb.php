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
 * Takes care of fetching the available blurbs and determines
 * which blurb meets the conditions and is next to be sent.
 *
 * @since 3.1
 */
class EDD_Email_Summary_Blurb {

	/**
	 * URL of the endpoint for the blurbs JSON.
	 *
	 * @since 3.1
	 *
	 * @const string
	 */
	const BLURBS_ENDPOINT_URL = 'https://jsonkeeper.com/b/JJNK';

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
			self::BLURBS_ENDPOINT_URL,
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
	 * Get the next blurb that can be sent.
	 *
	 * @since 3.1
	 *
	 * @return array
	 */
	public function get_next() {
		$all_data    = $this->fetch_blurbs();
		$blurbs      = array();
		$blurbs_sent = get_option( 'email_summary_blurbs_sent', array() );
		$next_blurb  = false;

		// Loop through the fetched blurbs and filter out all that meet the conditions.
		foreach ( $all_data['blurbs'] as $key => $blurb ) {
			if( $this->does_blurb_meet_conditions( $blurb ) ) {
				$blurbs[] = $blurb;
			}
		}

		// Find first blurb that was not yet sent.
		foreach ( $blurbs as $blurb ) {
			$blurb_hash = $this->get_blurb_hash( $blurb );
			if ( is_array( $blurbs_sent ) && ! in_array( $blurb_hash, $blurbs_sent ) ) {
				$next_blurb = $blurb;
				break;
			}
		}

		// If all of the available blurbs were already sent out, choose random blurb.
		if ( ! $next_blurb && ! empty( $blurbs ) ) {
			$next_blurb = $blurbs[ array_rand( $blurbs ) ];
		}

		return $next_blurb;
	}

	/**
	 * Save blurb as sent to the options.
	 *
	 * @since 3.1
	 *
	 * @return void
	 */
	public function mark_blurb_sent( $blurb ) {
		$blurbs_sent = get_option( 'email_summary_blurbs_sent', array() );
		if ( ! empty( $blurb ) ) {
			$blurb_hash = $this->get_blurb_hash( $blurb );
			if ( ! in_array( $blurb_hash, $blurbs_sent ) ) {
				$blurbs_sent[] = $blurb_hash;
			}
		}

		update_option( 'email_summary_blurbs_sent', $blurbs_sent );
	}


	/**
	 * Hash blurb array
	 *
	 * @since 3.1
	 *
	 * @return string
	 */
	public function get_blurb_hash( $blurb ) {
		if ( ! empty( $blurb ) ) {
			// We want to sort the array, so that we can get reliable hash everytime even if array properties order changed.
			array_multisort( $blurb );
			return md5( json_encode( $blurb ) );
		}

		return false;
	}

	/**
	 * Check if store pass matches the condition from the blurb.
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
	 * Check if store has specific products/downloads active.
	 *
	 * @since 3.1
	 *
	 * @return bool
	 */
	public function check_blurb_has_downloads( $conditions ) {
		foreach ($conditions as $condition_name) {
			// Check if store has any products that are free.
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

				// Check for specific product/downloads.
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
