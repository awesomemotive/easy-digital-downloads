<?php

namespace EDD\Licensing\Traits;
use EDD\Admin\Pass_Manager;

trait Controls {

	/**
	 * The pass manager.
	 *
	 * @var \EDD\Admin\Pass_Manager
	 */
	private $pass_manager;

	/**
	 * The CSS class for the message.
	 *
	 * @var string
	 */
	private $class;

	/**
	 * The license status.
	 *
	 * @var string
	 */
	private $license_status;

	/**
	 * The message to display below the license key input.
	 *
	 * @var string
	 */
	private $message;

	/**
	 * Whether the current product is covered by a pass.
	 *
	 * @var bool
	 */
	private $included_in_pass = false;

	/**
	 * The item name.
	 *
	 * @var string
	 */
	private $name;

	/**
	 * Gets the button for the pass field.
	 *
	 * @since 3.1.1
	 * @param string $status The pass status.
	 * @param bool   $echo   Whether to echo the button.
	 * @return string
	 */
	public function get_actions( $status, $echo = false ) {
		$button    = $this->get_button_args( $status );
		$timestamp = time();
		if ( ! $echo ) {
			ob_start();
		}
		?>
		<div class="edd-licensing__actions">
			<button
				class="button button-<?php echo esc_attr( $button['class'] ); ?> edd-license__action"
				data-action="<?php echo esc_attr( $button['action'] ); ?>"
				data-timestamp="<?php echo esc_attr( $timestamp ); ?>"
				data-token="<?php echo esc_attr( \EDD\Utils\Tokenizer::tokenize( $timestamp ) ); ?>"
				data-nonce="<?php echo esc_attr( wp_create_nonce( 'edd_licensehandler' ) ); ?>"
			>
				<?php echo esc_html( $button['label'] ); ?>
			</button>
			<?php if ( ! empty( $this->license_key ) && 'activate' === $button['action'] ) : ?>
				<button
					class="button button-secondary edd-license__delete"
					data-action="delete"
					data-timestamp="<?php echo esc_attr( $timestamp ); ?>"
					data-token="<?php echo esc_attr( \EDD\Utils\Tokenizer::tokenize( $timestamp ) ); ?>"
					data-nonce="<?php echo esc_attr( wp_create_nonce( 'edd_licensehandler-delete' ) ); ?>"
				>
					<?php esc_html_e( 'Delete', 'easy-digital-downloads' ); ?>
				</button>
			<?php endif; ?>
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
	 * @return array
	 */
	private function get_button_args( $state = 'inactive' ) {
		if ( in_array( $state, array( 'valid', 'active' ), true ) ) {
			return array(
				'action' => 'deactivate',
				'label'  => __( 'Deactivate', 'easy-digital-downloads' ),
				'class'  => 'secondary',
			);
		}

		return array(
			'action' => 'activate',
			'label'  => __( 'Activate', 'easy-digital-downloads' ),
			'class'  => 'secondary',
		);
	}

	/**
	 * Outputs the license key message.
	 *
	 * @since 3.1.1
	 * @return void
	 */
	private function do_message( $echo = true ) {
		if ( empty( $this->message ) ) {
			return '';
		}
		$classes = array(
			'edd-license-data',
			"edd-license-{$this->class}",
			$this->license_status,
		);
		if ( ! $echo ) {
			ob_start();
		}
		?>
		<div class="<?php echo esc_attr( implode( ' ', array_filter( $classes ) ) ); ?>">
			<p><?php echo wp_kses_post( $this->message ); ?></p>
		</div>
		<?php
		if ( ! $echo ) {
			return ob_get_clean();
		}
	}

	/**
	 * Sets up the license data.
	 *
	 * @since 3.1.1
	 * @return void
	 */
	private function set_up_license_data() {

		$class          = 'empty';
		$license_status = null;
		$args           = array(
			'status'      => $this->license->license,
			'license_key' => $this->license_key,
			'expires'     => ! empty( $this->license->expires ) ? $this->license->expires : '',
			'name'        => $this->name,
		);
		if ( ! empty( $this->args['options']['api_url'] ) ) {
			$args['api_url'] = $this->args['options']['api_url'];
			if ( ! empty( $this->args['options']['file'] ) && function_exists( 'get_plugin_data' ) ) {
				$plugin_data = get_plugin_data( $this->args['options']['file'] );
				if ( ! empty( $plugin_data['PluginURI'] ) ) {
					$args['uri'] = $plugin_data['PluginURI'];
				}
			}
		}
		$messages = new \EDD\Licensing\Messages( $args );
		$message  = $messages->get_message();

		if ( ! empty( $this->license ) ) {
			$now        = current_time( 'timestamp' );
			$expiration = ! empty( $this->license->expires )
				? strtotime( $this->license->expires, $now )
				: false;

			// activate_license 'invalid' on anything other than valid, so if there was an error capture it
			if ( false === $this->license->success ) {
				$class          = ! empty( $this->license->error ) ? $this->license->error : 'error';
				$license_status = "license-{$class}-notice";
			} else {
				$class = 'valid';
				if ( 'lifetime' === $this->license->expires ) {
					$license_status = 'license-lifetime-notice';
				} elseif ( ( $expiration > $now ) && ( $expiration - $now < ( DAY_IN_SECONDS * 30 ) ) ) {
					$license_status = 'license-expires-soon-notice';
				} else {
					$license_status = 'license-expiration-date-notice';
				}
			}
		}

		$pass_manager = $this->get_pass_manager();

		if ( 'valid' !== $class && $pass_manager->has_pass_data && $this->is_included_in_pass() ) {
			$this->included_in_pass = true;
			$class                  = 'included-in-pass';
			/* translators: the all acess pass name. */
			$message = sprintf( __( 'Your %s gives you access to this extension.', 'easy-digital-downloads' ), '<strong>' . $pass_manager->get_pass_name() . '</strong>' );
		}

		$this->class          = $class;
		$this->message        = $message;
		$this->license_status = $license_status;
	}

	/**
	 * Whether a given product is included in the customer's active pass.
	 *
	 * @since 3.1.1
	 * @return bool
	 */
	private function is_included_in_pass() {
		$pass_manager = $this->get_pass_manager();
		// All Access and lifetime passes can access everything.
		if ( $pass_manager->hasAllAccessPass() && empty( $this->args['options']['api_url'] ) ) {
			return true;
		}
		// If we don't know the item ID we can't assume anything.
		if ( empty( $this->args['options']['item_id'] ) ) {
			return false;
		}
		$api          = new \EDD\Admin\Extensions\ExtensionsAPI();
		$api_item_id  = $this->args['options']['item_id'];
		$product_data = $api->get_product_data( array(), $api_item_id );
		if ( ! $product_data || empty( $product_data->categories ) ) {
			return false;
		}

		return (bool) $pass_manager->can_access_categories( $product_data->categories );
	}

	/**
	 * Gets the pass manager.
	 *
	 * @return EDD\Admin\Pass_Manager
	 */
	private function get_pass_manager() {
		if ( $this->pass_manager ) {
			return $this->pass_manager;
		}

		$this->pass_manager = new Pass_Manager();

		return $this->pass_manager;
	}
}
