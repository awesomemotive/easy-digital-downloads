<?php

namespace EDD\Admin\Installers;

class Extensions {

	private $transient = 'edd_extensions_urls';

	/**
	 * Returns the download URL for an extension.
	 *
	 * @since 2.11.x
	 *
	 * @param int $id Extension ID.
	 *
	 * @return string
	 */
	public function get_url( $id, $license_key ) {
		return $this->get_url_from_database_or_remote( $id, $license_key );
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
	protected function get_url_from_database_or_remote( $item_id, $license_key ) {

		if ( empty( $item_id ) || empty( $license_key ) ) {
			return false;
		}

		$extension_data = get_option( "edd_extension_{$item_id}" );

		if ( ! empty( $extension_data['url'] ) && ! empty( $extension_data['timeout'] ) && time() <= $extension_data['timeout'] ) {
			return $extension_data['url'];
		}

		return $this->get_download_url( $item_id, $license_key );
	}

	/**
	 * Fetch extension URLs from the remote server.
	 *
	 * @since 2.11.x
	 *
	 * @return bool|array False if no key or failure, array of extension URLs data otherwise.
	 */
	protected function get_download_url( $item_id = false, $license = false ) {
		if ( ! $item_id || ! $license ) {
			return false;
		}

		// $stored_urls = $this->get_stored_urls();
		// if ( $stored_urls ) {
		// 	return $stored_urls;
		// }

		$request = wp_remote_post(
			$this->get_api_url(),
			array(
				'timeout'   => 15,
				'sslverify' => true,
				'body'      => array(
					'edd_action'  => 'get_version',
					'license'     => $license,
					'item_id'     => $item_id,
					'php_version' => phpversion(),
					'wp_version'  => get_bloginfo( 'version' ),
				),
			)
		);

		// If there was an API error, set transient for only 10 minutes.
		if ( ! $request || $request instanceof WP_Error ) {
			// update_option( $this->transient, strtotime( '+10 minutes' ) );

			return false;
		}

		$response = json_decode( wp_remote_retrieve_body( $request ) );

		if ( ! empty( $response->download_link ) ) {
			update_option(
				"edd_extension_{$item_id}",
				array(
					'url'     => $response->download_link,
					'timeout' => strtotime( '+1 day', time() ),
				)
			);

			return $response->download_link;
		}

		return false;
	}

	/**
	 * Gets the URL for our API request.
	 *
	 * @since 2.11.x
	 * @return string
	 */
	protected function get_api_url() {
		if ( defined( 'EDD_SL_API_URL' ) ) {
			return EDD_SL_API_URL;
		}

		return 'https://easydigitaldownloads.com/edd-sl-api';
	}

	private function get_stored_urls() {

		$stored_urls = get_option( $this->transient );

		// Request has never failed.
		if ( empty( $stored_urls ) ) {
			return false;
		}

		/*
		 * Request previously failed, but the timeout has expired.
		 * This means we're allowed to try again.
		 */
		if ( is_numeric( $stored_urls ) && time() > $stored_urls ) {
			delete_option( $this->transient );

			return false;
		}

		if ( empty( $stored_urls['timeout'] ) || time() > $stored_urls['timeout'] ) {
			delete_option( $this->transient );

			return false;
		}

		return $stored_urls;
	}
}
