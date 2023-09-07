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

add_filter( 'edd_settings_misc', __NAMESPACE__ . '\register_keys' );
/**
 * Register the settings for reCAPTCHA keys.
 *
 * @since 2.0
 * @param array $settings EDD settings array.
 * @return array
 */
function register_keys( $settings ) {

	$settings['main']['recaptcha'] = array(
		'id'   => 'recaptcha',
		'name' => __( 'reCAPTCHA v3', 'easy-digital-downloads' ),
		'desc' => sprintf(
			/* translators: 1. opening anchor tag; 2. closing anchor tag */
			__( '%1$sRegister with Google%2$s to get reCAPTCHA v3 keys. Setting the keys here will enable reCAPTCHA on your registration block and when a user requests a password reset using the login block.', 'easy-digital-downloads' ),
			'<a href="https://www.google.com/recaptcha/admin#list" target="_blank">',
			'</a>'
		),
		'type' => 'descriptive_text',
	);

	$settings['main']['recaptcha_site_key'] = array(
		'id'   => 'recaptcha_site_key',
		'name' => __( 'reCAPTCHA Site Key', 'easy-digital-downloads' ),
		'type' => 'text',
		'std'  => '',
	);

	$settings['main']['recaptcha_secret_key'] = array(
		'id'   => 'recaptcha_secret_key',
		'name' => __( 'reCAPTCHA Secret Key', 'easy-digital-downloads' ),
		'type' => 'password',
		'std'  => '',
	);

	return $settings;
}
