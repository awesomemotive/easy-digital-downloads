<?php
/**
 * Base Pass Handler class.
 *
 * @package EDD
 * @subpackage EDD/PassHandler
 */

namespace EDD\Admin\PassHandler;

class Handler {

	/**
	 * Gets the pass data.
	 *
	 * @since 3.1.1
	 * @return array
	 */
	public function get_pro_license() {
		return new \EDD\Licensing\License( 'pro' );
	}

	/**
	 * Updates the pass data.
	 *
	 * @since 3.1.1
	 * @param object $license_data
	 * @return bool
	 */
	public function update_pro_license( $license_data ) {

		// When updating pass data, always delete the extension data.
		delete_site_option( 'edd_extension_category_1592_data' );

		$license = $this->get_pro_license();

		return $license->save( $license_data );
	}

	/**
	 * Gets the button for the pass field.
	 *
	 * @since 3.1.1
	 * @param string $status The pass status.
	 * @param string $key    The license key.
	 * @param bool   $echo   Whether to echo the button.
	 * @return string
	 */
	public function get_pass_actions( $status, $key = '', $echo = false ) {
		$button    = $this->get_button_args( $status, $key );
		$timestamp = time();
		if ( ! $echo ) {
			ob_start();
		}
		?>
		<div class="edd-pass-handler__actions">
			<button
				class="button button-<?php echo esc_attr( $button['class'] ); ?> edd-pass-handler__action"
				data-action="<?php echo esc_attr( $button['action'] ); ?>"
				data-timestamp="<?php echo esc_attr( $timestamp ); ?>"
				data-token="<?php echo esc_attr( \EDD\Utils\Tokenizer::tokenize( $timestamp ) ); ?>"
				data-nonce="<?php echo esc_attr( wp_create_nonce( 'edd_passhandler' ) ); ?>"
			>
				<?php echo esc_html( $button['label'] ); ?>
			</button>
			<?php if ( ! empty( $key ) && in_array( $button['action'], array( 'activate', 'verify' ), true ) ) : ?>
				<button
					class="button button-secondary edd-pass-handler__delete"
					data-action="delete"
					data-timestamp="<?php echo esc_attr( $timestamp ); ?>"
					data-token="<?php echo esc_attr( \EDD\Utils\Tokenizer::tokenize( $timestamp ) ); ?>"
					data-nonce="<?php echo esc_attr( wp_create_nonce( 'edd_passhandler-delete' ) ); ?>"
				>
					<?php esc_html_e( 'Delete', 'easy-digital-downloads' ); ?>
				</button>
				<?php
			endif;
			if ( 'deactivate' === $button['action'] ) {
				$this->do_extensions_link();
			}
			?>
		</div>
		<?php
		if ( ! $echo ) {
			return ob_get_clean();
		}
	}

	/**
	 * Get the button parameters based on the status.
	 *
	 * @since 3.1.1
	 * @param string $state
	 * @param string $key
	 * @return array
	 */
	private function get_button_args( $state = 'inactive', $key = '' ) {
		if ( ! empty( $key ) && in_array( $state, array( 'valid', 'active' ), true ) ) {
			return array(
				'action' => 'deactivate',
				'label'  => __( 'Deactivate', 'easy-digital-downloads' ),
				'class'  => 'secondary',
			);
		}

		if ( edd_is_pro() ) {
			return array(
				'action' => 'activate',
				'label'  => __( 'Activate License', 'easy-digital-downloads' ),
				'class'  => 'primary',
			);
		}

		return array(
			'action' => 'verify',
			'label'  => __( 'Verify License Key', 'easy-digital-downloads' ),
			'class'  => 'primary',
		);
	}

	/**
	 * Prints the link to the extensions screen.
	 *
	 * @since 3.1.1
	 * @return string
	 */
	private function do_extensions_link() {
		printf(
			'<a class="button button-primary edd-pass-handler__extensions-link" href="%s">%s</a>',
			esc_url( $this->get_extensions_url() ),
			esc_html__( 'View Extensions', 'easy-digital-downloads' )
		);
	}

	/**
	 * Gets the extensions screen URL.
	 *
	 * @return string
	 */
	public function get_extensions_url() {
		return edd_get_admin_url(
			array(
				'page' => 'edd-addons',
			)
		);
	}

	/**
	 * Makes the remote request to activate/deactivate a license key.
	 *
	 * @since 3.1.1
	 * @param array $api_params
	 * @return stdClass|void
	 */
	public function remote_request( $api_params ) {
		$api_params = wp_parse_args(
			$api_params,
			array(
				'url' => network_home_url(),
			)
		);
		$api        = new \EDD\Licensing\API();
		$response   = $api->make_request( $api_params );

		// Make sure there are no errors
		if ( ! $response ) {
			wp_send_json_error(
				array(
					'message' => wpautop( __( 'We could not reach the EDD server.', 'easy-digital-downloads' ) ),
				)
			);
		}

		return $response;
	}
}
