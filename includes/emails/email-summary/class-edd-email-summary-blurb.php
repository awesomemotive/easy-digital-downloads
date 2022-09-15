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
	const BLURBS_ENDPOINT_URL = 'https://plugin.easydigitaldownloads.com/wp-content/summaries.json';

	/**
	 * Environment Checker class.
	 *
	 * @var EnvironmentChecker
	 */
	protected $environment_checker;

	/**
	 * Class constructor.
	 *
	 * @since 3.1
	 */
	public function __construct() {

		// Load plugin.php so that we can use is_plugin_active().
		require_once ABSPATH . 'wp-admin/includes/plugin.php';
		$this->environment_checker = new EnvironmentChecker();

	}

	/**
	 * Fetch all blurbs from remote endpoint.
	 *
	 * @since 3.1
	 *
	 * @return array
	 */
	public function fetch_blurbs() {
		$blurbs       = array();
		$request_body = false;

		$request = wp_safe_remote_get( self::BLURBS_ENDPOINT_URL );

		// @todo  - Detect first response code, before redirect!
		if ( ! is_wp_error( $request ) && 200 === wp_remote_retrieve_response_code( $request ) ) {
			$request_body = wp_remote_retrieve_body( $request );
			$blurbs       = json_decode( $request_body, true );
		}

		if ( empty( $request_body ) ) {
			// HTTP Request for blurbs is empty, fallback to local .json file.
			$fallback_json = wp_json_file_decode( EDD_PLUGIN_DIR . 'includes/admin/promos/email-summary/blurbs.json', array( 'associative' => true ) );
			if ( ! empty( $fallback_json ) ) {
				$blurbs = $fallback_json;
			}
		}

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

		if ( empty( $all_data['blurbs'] ) ) {
			return false;
		}

		// Loop through the fetched blurbs and filter out all that meet the conditions.
		foreach ( $all_data['blurbs'] as $key => $blurb ) {
			if ( $this->does_blurb_meet_conditions( $blurb ) ) {
				$blurbs[] = $blurb;
			}
		}

		// Find first blurb that was not yet sent.
		foreach ( $blurbs as $blurb ) {
			$blurb_hash = $this->get_blurb_hash( $blurb );
			if ( is_array( $blurbs_sent ) && ! in_array( $blurb_hash, $blurbs_sent, true ) ) {
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
	 * @param array $blurb Blurb data.
	 * @return void
	 */
	public function mark_blurb_sent( $blurb ) {
		$blurbs_sent = get_option( 'email_summary_blurbs_sent', array() );
		if ( ! empty( $blurb ) ) {
			$blurb_hash = $this->get_blurb_hash( $blurb );
			if ( ! in_array( $blurb_hash, $blurbs_sent, true ) ) {
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
	 * @param array $blurb Blurb data.
	 * @return string MD5 hashed blurb.
	 */
	public function get_blurb_hash( $blurb ) {
		if ( empty( $blurb ) ) {
			return false;
		}
		// We want to sort the array, so that we can get reliable hash everytime even if array properties order changed.
		array_multisort( $blurb );
		return md5( wp_json_encode( $blurb ) );
	}

	/**
	 * Check if store pass matches the condition from the blurb.
	 *
	 * @since 3.1
	 *
	 * @param string $condition Pass details.
	 * @return bool
	 */
	public function check_blurb_current_pass( $condition ) {
		return $this->environment_checker->meetsCondition( $condition );
	}

	/**
	 * Check if store has all requested plugins active.
	 *
	 * @since 3.1
	 *
	 * @param array $active_plugins An array of plugins that all need to be active.
	 * @return bool
	 */
	public function check_blurb_active_plugins( $active_plugins ) {
		foreach ( $active_plugins as $plugin_name ) {
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
	 * @param array $inactive_plugins An array of plugins that needs to be inactive.
	 * @return bool
	 */
	public function check_blurb_inactive_plugins( $inactive_plugins ) {
		foreach ( $inactive_plugins as $plugin_name ) {
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
	 * @param array $conditions An array of predefined conditions.
	 * @return bool
	 */
	public function check_blurb_has_downloads( $conditions ) {
		foreach ( $conditions as $condition_name ) {
			// Check if store has any products that are free.
			if ( 'free' === $condition_name ) {
				$args = array(
					'post_type'      => 'download',
					'posts_per_page' => 1,
					'fields'         => 'ids',
					'no_found_rows'  => true,
					'meta_query'     => array(
						array(
							'key'   => 'edd_price',
							'value' => '0.00',
						),
					),
				);

				$downloads = new WP_Query( $args );
				if ( 0 === $downloads->post_count ) {
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
	 * @param array $blurb Blurb data.
	 * @return bool
	 */
	public function does_blurb_meet_conditions( $blurb ) {
		if ( isset( $blurb['conditions'] ) && ! empty( $blurb['conditions'] ) ) {
			foreach ( $blurb['conditions'] as $condition_name => $condition ) {
				if ( empty( $condition ) ) {
					continue;
				}

				// Pass check.
				if ( 'current_pass' === $condition_name ) {
					if ( ! $this->check_blurb_current_pass( $condition ) ) {
						return false;
					}
				}

				// Active plugins check.
				if ( 'active_plugins' === $condition_name ) {
					if ( ! $this->check_blurb_active_plugins( $condition ) ) {
						return false;
					}
				}

				// Inactive plugins check.
				if ( 'inactive_plugins' === $condition_name ) {
					if ( ! $this->check_blurb_inactive_plugins( $condition ) ) {
						return false;
					}
				}

				// Check for specific product/downloads.
				if ( 'has_downloads' === $condition_name ) {
					if ( ! $this->check_blurb_has_downloads( $condition ) ) {
						return false;
					}
				}
			}
		}

		return true;
	}

}
