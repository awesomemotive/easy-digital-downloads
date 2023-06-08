<?php

namespace EDD\Admin\Extensions;

class DownloadURL {

	/**
	 * Potentially the provided .zip file source.
	 *
	 * @since 3.1.1
	 * @var bool|string
	 */
	protected $plugin = false;

	/**
	 * The license key.
	 *
	 * @since 3.1.1
	 * @var bool|string
	 */
	protected $license_key = false;

	public function __construct( $license_key ) {
		if ( ! empty( $_POST['plugin'] ) ) {
			$this->plugin = sanitize_text_field( $_POST['plugin'] );
		}
		$this->license_key = $license_key;
	}

	/**
	 * Gets the download URL.
	 *
	 * @since 3.1.1
	 * @return bool|string
	 */
	public function get_url() {
		if ( ! $this->plugin ) {
			return false;
		}
		if ( false === strpos( $this->plugin, 'https://downloads.wordpress.org/plugin' ) ) {
			return false;
		}
		if ( ! in_array( $this->plugin, $this->get_allowed_urls(), true ) ) {
			return false;
		}

		return $this->plugin;
	}

	/**
	 * Gets an array of allowed download URLs.
	 *
	 * @since 3.1.2
	 * @return array
	 */
	private function get_allowed_urls() {
		return array(
			'https://downloads.wordpress.org/plugin/edd-auto-register.zip',
			'https://downloads.wordpress.org/plugin/wp-mail-smtp.zip',
			'https://downloads.wordpress.org/plugin/google-analytics-for-wordpress.zip',
			'https://downloads.wordpress.org/plugin/all-in-one-seo-pack.zip',
		);
	}
}
