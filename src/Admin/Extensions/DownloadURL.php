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
		return false !== strpos( $this->plugin, 'https://downloads.wordpress.org/plugin' ) ? $this->plugin : false;
	}
}
