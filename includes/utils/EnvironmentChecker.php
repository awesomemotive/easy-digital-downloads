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
	 * Types of license/pass conditions that we support.
	 * The key is the condition slug and the value is the corresponding
	 * method to call in the `Pass_Manager` class to check the condition.
	 *
	 * @since 2.11.4
	 *
	 * @var string[]
	 */
	protected $validLicenseConditions = array(
		'free'              => 'isFree',
		'ala-carte'         => 'hasIndividualLicense',
		'pass-personal'     => 'hasPersonalPass',
		'pass-extended'     => 'hasExtendedPass',
		'pass-professional' => 'hasProfessionalPass',
		'pass-all-access'   => 'hasAllAccessPass',
		'pass-any'          => 'has_pass',
	);

	/**
	 * Constructor.
	 */
	public function __construct() {
		$this->passManager = new Pass_Manager();
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
		if ( array_key_exists( $condition, $this->validLicenseConditions ) ) {
			return $this->hasLicenseType( $condition );
		} elseif ( $this->isPaymentGateway( $condition ) ) {
			return $this->paymentGatewayMatch( array_keys( edd_get_enabled_payment_gateways() ), $condition );
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
	 *
	 * @return bool
	 */
	public function meetsConditions( $conditions ) {
		foreach ( $conditions as $condition ) {
			if ( ! $this->meetsCondition( $condition ) ) {
				return false;
			}
		}

		return true;
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
	 * @throws \InvalidArgumentException
	 */
	protected function hasLicenseType( $passLevel ) {
		$method = isset( $this->validLicenseConditions[ $passLevel ] )
			? $this->validLicenseConditions[ $passLevel ]
			: false;

		if ( ! $method || ! method_exists( $this->passManager, $method ) ) {
			throw new \InvalidArgumentException( sprintf( 'Method %s not found in Pass_Manager.', $method ) );
		}

		return call_user_func( array( $this->passManager, $method ) );
	}

	/**
	 * Determines if the provided condition is a payment gateway.
	 *
	 * @since 2.11.4
	 *
	 * @param string $condition
	 *
	 * @return bool
	 */
	protected function isPaymentGateway( $condition ) {
		return 'gateway-' === substr( $condition, 0, 8 );
	}

	/**
	 * Determines if the supplied gateway condition is applicable to this site.
	 * Will return `true` if the condition is the slug of a payment gateway (potentially with a `gateway-` prefix)
	 * that's enabled on this site.
	 *
	 * @since 2.11.4
	 *
	 * @param array  $enabledGateways Gateways that are enabled on this site.
	 * @param string $condition       Gateway we're checking to see if it's enabled.
	 *
	 * @return bool True if the gateway is enabled, false if not.
	 */
	public function paymentGatewayMatch( $enabledGateways, $condition ) {
		$gatewayToCheck = str_replace( 'gateway-', '', $condition );

		return in_array( $gatewayToCheck, $enabledGateways, true );
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
