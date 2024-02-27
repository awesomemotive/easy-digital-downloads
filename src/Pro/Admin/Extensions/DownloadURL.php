<?php

namespace EDD\Pro\Admin\Extensions;

class DownloadURL extends \EDD\Admin\Extensions\DownloadURL {

	/**
	 * Returns the download URL for an extension.
	 *
	 * @since 3.1.1
	 *
	 * @param int $id Extension ID.
	 *
	 * @return string
	 */
	public function get_url() {
		$item_id = filter_input( INPUT_POST, 'product', FILTER_SANITIZE_NUMBER_INT );
		if ( ! $item_id || ! $this->license_key ) {
			return parent::get_url();
		}

		// If the download package has already been retrieved,
		// it was already saved to a one hour transient.
		$transient_name = "edd_extension_{$item_id}";
		$transient      = get_transient( $transient_name );
		if ( false !== $transient ) {
			return $transient;
		}

		$download_url = $this->get_package_from_remote( $item_id );

		// phpcs:ignore WordPress.PHP.DisallowShortTernary
		return $download_url ?: parent::get_url();
	}

	/**
	 * Makes the API request to get the download package from Software Licensing.
	 *
	 * @since 3.1.1
	 * @param int $item_id
	 * @return false|string
	 */
	private function get_package_from_remote( $item_id ) {
		$api_params = array(
			'edd_action'                     => 'get_version',
			'license'                        => $this->license_key,
			'item_id'                        => $item_id,
			'php_version'                    => phpversion(),
			'wp_version'                     => get_bloginfo( 'version' ),
			'easy-digital-downloads_version' => EDD_VERSION,
		);
		$api        = new \EDD\Licensing\API();
		$response   = $api->make_request( $api_params );

		// If there was an API error, return false.
		if ( ! $response || empty( $response->download_link ) ) {
			return false;
		}

		set_transient(
			"edd_extension_{$item_id}",
			$response->download_link,
			HOUR_IN_SECONDS
		);

		return $response->download_link;
	}
}
