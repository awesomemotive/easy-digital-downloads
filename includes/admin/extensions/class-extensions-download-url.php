<?php

namespace EDD\Admin\Extensions;

class ExtensionsDownloadURL {

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
		return $this->get_download_url( $id, $license_key );
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

		$option_name = "edd_extension_{$item_id}";
		$option      = $this->get_stored_extension_data( $option_name );
		if ( $option ) {
			return $option['url'];
		}

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
		if ( ! $request || $request instanceof \WP_Error ) {
			return false;
		}

		$response = json_decode( wp_remote_retrieve_body( $request ) );

		if ( ! empty( $response->download_link ) ) {
			update_option(
				"edd_extension_{$item_id}",
				array(
					'url'     => $response->download_link,
					'timeout' => strtotime( '+1 day', time() ),
				),
				false
			);

			return $response->download_link;
		}

		return false;
	}

	/**
	 * Gets the stored extension data from the database.
	 * If it doesn't exist, or has expired, deletes the option and returns false.
	 *
	 * @since 2.11.x
	 * @param string $option_name The option name to look for in the database.
	 * @return array|bool         Returns the option data if not expired, or false if expired or doesn't exist yet.
	 */
	private function get_stored_extension_data( $option_name ) {
		$option = get_option( $option_name );
		if ( ! empty( $option['timeout'] ) && time() <= $option['timeout'] ) {
			return $option;
		};

		delete_option( $option_name );
		return false;
	}

	/**
	 * Gets the URL for our Software Licensing API request.
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
}
