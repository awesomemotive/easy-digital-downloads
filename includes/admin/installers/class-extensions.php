<?php

namespace EDD\Admin\Installers;

class Extensions {

	private $api_url = 'https://easydigitaldownloads.local/edd-sl-api';

	private $transient = 'edd_extensions_urls';

	private $license;

	/**
	 * Returns the download URL for an extension.
	 *
	 * @since 2.11.x
	 *
	 * @param int $id Extension ID.
	 *
	 * @return string
	 */
	public function get_url( $id ) {

		$urls = $this->get_urls();

		return empty( $urls[ $id ] ) ? '' : $urls[ $id ];
	}

	/**
	 * Retrieve extension URLs from the stored transient or remote server.
	 *
	 * @since 2.11.x
	 *
	 * @param bool $force Whether to force the extensions retrieval or re-use option cache.
	 *
	 * @return array|bool
	 */
	protected function get_urls( $force = false ) {

		if ( empty( $this->license ) ) {
			// return false;
		}

		// Avoid multiple requests to the database.
		static $urls = null;

		if ( is_null( $urls ) ) {
			$urls = get_transient( $this->transient );
		}

		if ( ! $force && ! empty( $urls ) ) {
			return $urls;
		}

		// Avoid multiple remote requests.
		static $remote_urls = null;

		if ( is_null( $remote_urls ) ) {
			$remote_urls = $this->get_remote_urls();
		}

		return $remote_urls;
	}

	/**
	 * Fetch extension URLs from the remote server.
	 *
	 * @since 2.11.x
	 *
	 * @return bool|array False if no key or failure, array of extension URLs data otherwise.
	 */
	protected function get_remote_urls() {

		$extensions = wp_remote_post(
			$this->api_url,
			array(
				'timeout'   => 15,
				'sslverify' => true,
				'body'      => array(
					'edd_action'  => 'get_extension_urls',
					'license'     => $this->license,
					'php_version' => phpversion(),
					'wp_version'  => get_bloginfo( 'version' ),
				),
			)
		);
		rgc_error_log( $extensions );

		// If there was an API error, set transient for only 10 minutes.
		if ( ! $extensions || $extensions instanceof WP_Error ) {
			set_transient( $this->transient, false, 10 * MINUTE_IN_SECONDS );

			return false;
		}

		$urls = array();

		foreach ( (array) $extensions as $extension ) {
			if ( ! empty( $extension->slug ) ) {
				$urls[ $extension->slug ] = ! empty( $extension->url ) ? $extension->url : '';
			}
		}

		// Otherwise, our request worked. Save the data and return it.
		set_transient( $this->transient, $urls, DAY_IN_SECONDS );

		return $urls;
	}
}
