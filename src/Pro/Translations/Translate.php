<?php
/**
 * Downloads EDD translations from .org as needed for EDD (Pro).
 */
namespace EDD\Pro\Translations;

use \EDD\EventManagement\SubscriberInterface;

class Translate implements SubscriberInterface {

	/**
	 * The site locale.
	 *
	 * @since 3.1.1.3
	 * @var string
	 */
	private $locale;

	/**
	 * The events this class wants to subscribe to.
	 *
	 * @return array
	 */
	public static function get_subscribed_events() {
		return array(
			'init'                                  => 'load_textdomain',
			'pre_set_site_transient_update_plugins' => 'check_update',
		);
	}

	/**
	 * Loads the plugin textdomain.
	 * This actually assigns it to the same location as WP Core does.
	 *
	 * @since 3.1.1.3
	 * @return void
	 */
	public function load_textdomain() {
		load_plugin_textdomain( 'easy-digital-downloads', false, WP_LANG_DIR . '/plugins' );
	}

	/**
	 * Filters the update_plugins transient to include the EDD translation.
	 *
	 * @since 3.1.1.3
	 * @param stdClass $transient
	 * @return stdClass
	 */
	public function check_update( $transient ) {
		$locale = $this->get_locale();
		if ( 'en_US' === $locale ) {
			return $transient;
		}

		if ( ! is_object( $transient ) ) {
			$transient = new \stdClass();
		}

		$translations            = $this->get_translations_update_data();
		$transient->translations = array_merge( isset( $transient->translations ) ? $transient->translations : array(), $translations );

		return $transient;
	}

	/**
	 * Gets the EDD translations update data.
	 *
	 * @since 3.1.1.3
	 * @return array
	 */
	private function get_translations_update_data() {

		$translations = array();
		$locale       = $this->get_locale();

		if ( 'en_US' === $locale ) {
			return $translations;
		}

		$edd_translation = $this->get_edd_translation();
		if ( $edd_translation ) {
			$translations[] = array(
				'type'       => 'plugin',
				'slug'       => 'easy-digital-downloads-pro',
				'language'   => $edd_translation['language'],
				'version'    => $edd_translation['version'],
				'updated'    => $edd_translation['updated'],
				'package'    => $edd_translation['package'],
				'autoupdate' => true,
			);
		}

		return $translations;
	}

	/**
	 * Looks for the correct EDD translation.
	 *
	 * @since 3.1.1.3
	 * @return false|array
	 */
	private function get_edd_translation() {
		$remote_translations = $this->get_remote_translations();
		if ( ! $remote_translations ) {
			return $translations;
		}

		$locale = $this->get_locale();
		foreach ( $remote_translations as $translation ) {
			if ( $translation['language'] === $locale ) {
				$installed_translation_date = $this->get_installed_translation_date();
				// If the remote translation is newer than the installed, return it.
				if ( strtotime( $translation['updated'] ) > $installed_translation_date ) {
					return $translation;
				}

				return false;
			}
		}

		return false;
	}

	/**
	 * Gets the EDD translations from the wordpress.org API.
	 *
	 * @since 3.1.1.3
	 * @return false|array
	 */
	private function get_remote_translations() {

		$remote_translations = wp_safe_remote_get(
			'https://api.wordpress.org/translations/plugins/1.0/',
			array(
				'timeout' => 30,
				'body'    => array(
					'wp_version' => get_bloginfo( 'version' ),
					'locale'     => $this->get_locale(),
					'version'    => EDD_VERSION,
					'slug'       => 'easy-digital-downloads',
				),
			)
		);

		if ( is_wp_error( $remote_translations ) || 200 !== wp_remote_retrieve_response_code( $remote_translations ) ) {
			return false;
		}

		$translations_body = json_decode( wp_remote_retrieve_body( $remote_translations ), true );
		if ( ! is_object( $translations_body ) && ! is_array( $translations_body ) ) {
			return false;
		}

		return $translations_body['translations'];
	}

	/**
	 * Gets the site locale.
	 *
	 * @since 3.1.1.3
	 * @return string
	 */
	private function get_locale() {
		if ( $this->locale ) {
			return $this->locale;
		}

		$this->locale = apply_filters( 'plugin_locale', get_user_locale(), 'easy-digital-downloads' );

		return $this->locale;
	}

	/**
	 * Gets the installed translation date as a timestamp.
	 *
	 * @since 3.1.1.3
	 * @return int
	 */
	private function get_installed_translation_date() {
		$locale       = $this->get_locale();
		$translations = wp_get_installed_translations( 'plugins' );

		return isset( $translations['easy-digital-downloads'][ $locale ] )
			? strtotime( $translations['easy-digital-downloads'][ $locale ]['PO-Revision-Date'] )
			: 0;
	}
}
