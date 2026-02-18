<?php
/**
 * EnvironmentChecker.php
 *
 * Checks to see if the environment matches the passed conditions.
 * Supported conditions include:
 *
 * - EDD version (e.g. "edd-3-3", "edd-3-x" for wildcards, or legacy "3.x").
 * - Type of license (pass level, à la carte, free).
 * - Payment gateways (e.g. "gateway-stripe", "gateway-paypal").
 * - PHP version (e.g. "php-8-2", "php-7-x" for wildcards).
 * - WordPress version (e.g. "wp-6-4", "wp-6-x" for wildcards).
 * - Active plugins (e.g. "plugin-edd-recurring", "plugin-edd-software-licensing").
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
		} elseif ( $this->isPhpVersion( $condition ) ) {
			return $this->phpVersionMatch( PHP_VERSION, $condition );
		} elseif ( $this->isWordPressVersion( $condition ) ) {
			global $wp_version;
			return $this->wordPressVersionMatch( $wp_version, $condition );
		} elseif ( $this->isPluginCondition( $condition ) ) {
			return $this->pluginIsActive( $condition );
		} elseif ( $this->isEddVersion( $condition ) ) {
			return $this->eddVersionMatch( EDD_VERSION, $condition );
		} elseif ( $this->isVersionNumber( $condition ) ) {
			// Legacy support for version numbers without prefix (e.g., "3.x").
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

	/**
	 * Determines if the provided condition is a PHP version condition.
	 *
	 * @since 3.6.5
	 *
	 * @param string $condition The condition to check.
	 *
	 * @return bool
	 */
	protected function isPhpVersion( $condition ) {
		return 'php-' === substr( $condition, 0, 4 );
	}

	/**
	 * Determines if the current PHP version matches the condition.
	 *
	 * @since 3.6.5
	 *
	 * @param string $currentPhpVersion The current PHP version (e.g., "8.2.10").
	 * @param string $condition         The condition to match (e.g., "php-8-2" or "php-8-x").
	 *
	 * @return bool
	 */
	public function phpVersionMatch( $currentPhpVersion, $condition ) {
		$compareVersion = str_replace( 'php-', '', $condition );
		$compareVersion = str_replace( '-', '.', $compareVersion );

		return $this->versionNumbersMatch( $currentPhpVersion, $compareVersion );
	}

	/**
	 * Determines if the provided condition is a WordPress version condition.
	 *
	 * @since 3.6.5
	 *
	 * @param string $condition The condition to check.
	 *
	 * @return bool
	 */
	protected function isWordPressVersion( $condition ) {
		return 'wp-' === substr( $condition, 0, 3 );
	}

	/**
	 * Determines if the current WordPress version matches the condition.
	 *
	 * @since 3.6.5
	 *
	 * @param string $currentWpVersion The current WordPress version (e.g., "6.4.2").
	 * @param string $condition        The condition to match (e.g., "wp-6-4" or "wp-6-x").
	 *
	 * @return bool
	 */
	public function wordPressVersionMatch( $currentWpVersion, $condition ) {
		$compareVersion = str_replace( 'wp-', '', $condition );
		$compareVersion = str_replace( '-', '.', $compareVersion );

		return $this->versionNumbersMatch( $currentWpVersion, $compareVersion );
	}

	/**
	 * Determines if the provided condition is an EDD version condition.
	 *
	 * @since 3.6.5
	 *
	 * @param string $condition The condition to check.
	 *
	 * @return bool
	 */
	protected function isEddVersion( $condition ) {
		return 'edd-' === substr( $condition, 0, 4 );
	}

	/**
	 * Determines if the current EDD version matches the condition.
	 *
	 * @since 3.6.5
	 *
	 * @param string $currentEddVersion The current EDD version (e.g., "3.3.5").
	 * @param string $condition         The condition to match (e.g., "edd-3-3" or "edd-3-x").
	 *
	 * @return bool
	 */
	public function eddVersionMatch( $currentEddVersion, $condition ) {
		$compareVersion = str_replace( 'edd-', '', $condition );
		$compareVersion = str_replace( '-', '.', $compareVersion );

		return $this->versionNumbersMatch( $currentEddVersion, $compareVersion );
	}

	/**
	 * Determines if the provided condition is a plugin condition.
	 *
	 * @since 3.6.5
	 *
	 * @param string $condition The condition to check.
	 *
	 * @return bool
	 */
	protected function isPluginCondition( $condition ) {
		return 'plugin-' === substr( $condition, 0, 7 );
	}

	/**
	 * Determines if a plugin matching the condition is active.
	 *
	 * @since 3.6.5
	 *
	 * @param string $condition The condition to check (e.g., "plugin-edd-recurring").
	 *
	 * @return bool
	 */
	public function pluginIsActive( $condition ) {
		$pluginSlug = str_replace( 'plugin-', '', $condition );

		// Check if any active plugin directory matches the slug.
		$activePlugins = get_option( 'active_plugins', array() );
		foreach ( $activePlugins as $plugin ) {
			// $plugin is in format "plugin-folder/plugin-file.php".
			$pluginDir = dirname( $plugin );
			if ( $pluginSlug === $pluginDir ) {
				return true;
			}
		}

		// Also check network-activated plugins for multisite.
		if ( is_multisite() ) {
			$networkPlugins = get_site_option( 'active_sitewide_plugins', array() );
			foreach ( array_keys( $networkPlugins ) as $plugin ) {
				$pluginDir = dirname( $plugin );
				if ( $pluginSlug === $pluginDir ) {
					return true;
				}
			}
		}

		return false;
	}

}
