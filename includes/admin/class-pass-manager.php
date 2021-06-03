<?php
/**
 * Pass Manager
 *
 * Tool for determining what kind of pass, if any, is activated on the site.
 *
 * @package   easy-digital-downloads
 * @copyright Copyright (c) 2021, Sandhills Development, LLC
 * @license   GPL2+
 * @since     2.10.6
 */

namespace EDD\Admin;

class Pass_Manager {

	const PERSONAL_PASS_ID = 1245715;
	const EXTENDED_PASS_ID = 1245716;
	const PROFESSIONAL_PASS_ID = 1245717;
	const ALL_ACCESS_PASS_ID = 1150319;
	const ALL_ACCESS_PASS_LIFETIME_ID = 1464807;

	/**
	 * ID of the highest tier pass that's activated.
	 *
	 * @var int|null
	 */
	public $highest_pass_id;

	/**
	 * Pass data from the database. This will be an array with
	 * the key being the license key, and the value being another
	 * array with the `pass_id` and `time_checked`.
	 *
	 * @var array|null
	 */
	public $pass_data;

	/**
	 * Whether or not we've stored any pass data yet.
	 * If no pass data has been stored, then that means the user
	 * might have a pass activated, we just haven't figured it out
	 * yet and we're still waiting for the first cron to run.
	 *
	 * @see \EDD_License::weekly_license_check()
	 * @see \EDD_License::activate_license()
	 * @see \EDD_License::maybe_set_pass_flag()
	 *
	 * @var bool
	 */
	public $has_pass_data;

	/**
	 * Hierarchy of passes. This helps us determine if one pass
	 * is "higher" than another.
	 *
	 * @see Pass_Manager::pass_compare()
	 *
	 * @var int[]
	 */
	private static $pass_hierarchy = array(
		self::PERSONAL_PASS_ID            => 10,
		self::EXTENDED_PASS_ID            => 20,
		self::PROFESSIONAL_PASS_ID        => 30,
		self::ALL_ACCESS_PASS_ID          => 40,
		self::ALL_ACCESS_PASS_LIFETIME_ID => 50
	);

	/**
	 * Pass_Manager constructor.
	 */
	public function __construct() {
		$pass_data = get_option( 'edd_pass_licenses' );
		if ( false === $pass_data ) {
			$this->has_pass_data = false;
		} else {
			$this->pass_data     = json_decode( $pass_data, true );
			$this->has_pass_data = true;
		}

		$this->highest_pass_id = $this->get_highest_pass_id();
	}

	/**
	 * Returns the ID of the highest tier pass that's activated.
	 *
	 * @since 2.10.6
	 *
	 * @return int|null Pass ID if one is found, null if one was not found.
	 */
	private function get_highest_pass_id() {
		if ( ! $this->has_pass_data || ! is_array( $this->pass_data ) ) {
			return null;
		}

		$highest_pass_id = null;

		foreach ( $this->pass_data as $pass_data ) {
			/*
			 * If this pass was last verified more than 2 weeks ago, we're not using it.
			 * This ensures we never deal with a "stale" record for a pass that's no longer
			 * actually activated, but still exists in our DB array for some reason.
			 *
			 * Our cron job should always be updating with active data once per week.
			 */
			if ( empty( $pass_data['time_checked'] ) || strtotime( '-2 weeks' ) > $pass_data['time_checked'] ) {
				continue;
			}

			// We need a pass ID.
			if ( empty( $pass_data['pass_id'] ) ) {
				continue;
			}

			// If we don't yet have a "highest pass", then this one is it automatically.
			if ( empty( $highest_pass_id ) ) {
				$highest_pass_id = intval( $pass_data['pass_id'] );
				continue;
			}

			// Otherwise, this pass only takes over the highest pass if it's actually higher.
			if ( self::pass_compare( (int) $pass_data['pass_id'], $highest_pass_id, '>' ) ) {
				$highest_pass_id = intval( $pass_data['pass_id'] );
			}
		}

		return $highest_pass_id;
	}

	/**
	 * Whether or not a pass is activated.
	 *
	 * @since 2.10.6
	 *
	 * @return bool
	 */
	public function has_pass() {
		return ! empty( $this->highest_pass_id );
	}

	/**
	 * Compares two passes with each other according to the supplied operator.
	 *
	 * @since 2.10.6
	 *
	 * @param int    $pass_1     ID of the first pass.
	 * @param int    $pass_2     ID of the second pass
	 * @param string $comparison Comparison operator.
	 *
	 * @return bool
	 * @throws \InvalidArgumentException
	 */
	public static function pass_compare( $pass_1, $pass_2, $comparison = '>' ) {
		if ( ! array_key_exists( $pass_1, self::$pass_hierarchy ) ) {
			throw new \InvalidArgumentException( 'Invalid pass 1: ' . $pass_1 );
		}
		if ( ! array_key_exists( $pass_2, self::$pass_hierarchy ) ) {
			throw new \InvalidArgumentException( 'Invalid pass 2: ' . $pass_2 );
		}

		return version_compare( self::$pass_hierarchy[ $pass_1 ], self::$pass_hierarchy[ $pass_2 ], $comparison );
	}

}
