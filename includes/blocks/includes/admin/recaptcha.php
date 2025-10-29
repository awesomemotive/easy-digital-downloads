<?php
/**
 * reCAPTCHA settings.
 *
 * @package   edd-blocks
 * @copyright 2022 Easy Digital Downloads
 * @license   GPL2+
 * @since 2.0
 */

namespace EDD\Blocks\Recaptcha\Settings;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/**
 * Register the settings for reCAPTCHA keys.
 *
 * @since 2.0
 * @deprecated 3.5.3
 * @param array $settings EDD settings array.
 * @return array
 */
function register_keys( $settings ) {
	return $settings;
}
