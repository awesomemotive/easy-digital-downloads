<?php
/**
 * EnvironmentChecker.php
 *
 * Checks to see if the environment matches the passed conditions.
 * Supported conditions include:
 *
 * - EDD version number -- either specific versions or wildcards (e.g. "2.x").
 * - Type of license (pass level, à la carte, free).
 *
 * @package   easy-digital-downloads
 * @copyright Copyright (c) 2021, Easy Digital Downloads
 * @license   GPL2+
 * @since     2.11.4
 */

namespace EDD\Utils;

use EDD\Admin\Pass_Manager;

class EnvironmentChecker {

	/**
	 * @var Pass_Manager
	 */
	protected $passManager;

	/**
	 * Number of EDD license keys entered.
	 *
	 * @since 2.11.4
	 *
	 * @var int
	 */
	protected $numberLicenseKeys;

	/**
	 * Types of license/pass conditions that we support.
	 *
	 * @since 2.11.4
	 *
	 * @var string[]
	 */
	protected $validLicenseConditions = array(
		'free',
		'ala-carte',
		'pass-personal',
		'pass-extended',
		'pass-professional',
		'pass-all-access',
	);

	/**
	 * Constructor.
	 */
	public function __construct() {
		$this->passManager = new Pass_Manager();

		global $edd_licensed_products;

		$this->numberLicenseKeys = is_array( $edd_licensed_products ) ? count( $edd_licensed_products ) : 0;
	}

	/**
	 * Checks to see if this environment meets the specified condition.
	 *
	 * @since 2.11.4
	 *
	 * @param string $condition Condition to check. Can either be a type of license/pass or a version number.
	 *
	 * @return bool
	 * @throws \InvalidArgumentException
	 */
	public function meetsCondition( $condition ) {
		if ( in_array( $condition, $this->validLicenseConditions ) ) {
			return $this->hasLicenseType( $condition );
		} elseif ( $this->isVersionNumber( $condition ) ) {
			return $this->versionNumbersMatch( EDD_VERSION, $condition );
		}

		throw new \InvalidArgumentException( 'Invalid condition. Must either be a type of license or a version number.' );
	}

	/**
	 * Checks to see if this environment meets all the specified conditions. If any one condition
	 * is not met then this returns false.
	 *
	 * @since 2.11.4
	 *
	 * @param array $conditions
	 */
	public function checkConditions( $conditions ) {
		foreach ( $conditions as $condition ) {
			$this->meetsCondition( $condition );
		}
	}

	/**
	 * Determines if the site has the specified pass condition.
	 *
	 * @see   EnvironmentChecker::$validLicenseConditions
	 *
	 * @since 2.11.4
	 *
	 * @param string $passLevel License type that we're checking to see if the system has.
	 *
	 * @return bool
	 */
	protected function hasLicenseType( $passLevel ) {

	}

	/**
	 * Determines if the provided condition is a version number.
	 *
	 * @since 2.11.4
	 *
	 * @param string $condition
	 *
	 * @return bool
	 */
	protected function isVersionNumber( $condition ) {
		// First character should always be numeric.
		if ( ! is_numeric( substr( $condition, 0, 1 ) ) ) {
			return false;
		}

		// Must contain at least one `.` or `-`.
		return false !== strpos( $condition, '.' ) || false !== strpos( $condition, '-' );
	}

	/**
	 * Determines if two version numbers match, or if the `$currentVersion` falls within the wildcard
	 * range specified by `$compareVersion`.
	 *
	 * @since 2.11.4
	 *
	 * @param string $currentVersion Version number currently in use. This must be a full, exact version number.
	 * @param string $compareVersion Version to compare with. This can either be an exact version number or a
	 *                               wildcard (e.g. `2.11.3` or `2.x`). Hyphens are also accepted in lieu of
	 *                               full stops (e.g. `2-11-3` or `2-x`).
	 *
	 * @return bool
	 * @throws \InvalidArgumentException
	 */
	public function versionNumbersMatch( $currentVersion, $compareVersion ) {
		$currentVersionPieces = explode( '.', $currentVersion );

		if ( false !== strpos( $compareVersion, '.' ) ) {
			$compareVersionPieces = explode( '.', $compareVersion );
		} else if ( false !== strpos( $compareVersion, '-' ) ) {
			$compareVersionPieces = explode( '-', $compareVersion );
		} else {
			throw new \InvalidArgumentException( sprintf(
				'Invalid version number: %s',
				$compareVersion
			) );
		}

		$numberCurrentVersionParts = count( $currentVersionPieces );
		$numberCompareVersionParts = count( $compareVersionPieces );

		/*
		 * Normalize the two parts so that they have the same lengths and
		 * wildcards (`x`) are removed.
		 */
		for ( $i = 0; $i < $numberCurrentVersionParts || $i < $numberCompareVersionParts; $i ++ ) {
			if ( isset( $compareVersionPieces[ $i ] ) && 'x' === strtolower( $compareVersionPieces[ $i ] ) ) {
				unset( $compareVersionPieces[ $i ] );
			}

			if ( ! isset( $currentVersionPieces[ $i ] ) ) {
				unset( $compareVersionPieces[ $i ] );
			} elseif ( ! isset( $compareVersionPieces[ $i ] ) ) {
				unset( $currentVersionPieces[ $i ] );
			}
		}

		// Now make sure all the numbers match.
		foreach ( $compareVersionPieces as $index => $versionPiece ) {
			if ( ! isset( $currentVersionPieces[ $index ] ) || $currentVersionPieces[ $index ] !== $versionPiece ) {
				return false;
			}
		}

		return true;
	}

}
