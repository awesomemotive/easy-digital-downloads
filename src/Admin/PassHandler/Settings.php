<?php

/**
 * Settings display/functions for EDD passes.
 * @package EDD
 * @subpackage Admin/PassHandler
 */
namespace EDD\Admin\PassHandler;

use EDD\EventManagement\SubscriberInterface;

class Settings implements SubscriberInterface {

	/**
	 * The pass handler.
	 *
	 * @var \EDD\Admin|PassHandler\Handler;
	 */
	protected $handler;

	public function __construct( Handler $handler ) {
		$this->handler = $handler;
	}

	/**
	 * Returns an array of events that this subscriber wants to listen to.
	 *
	 * @since 3.1.1
	 * @return array
	 */
	public static function get_subscribed_events() {
		return array(
			'edd_settings_tab_top_general_main' => 'do_pass_field',
			'admin_enqueue_scripts'             => 'register_assets',
		);
	}

	/**
	 * Outputs the EDD pass license field on the main EDD settings screen.
	 *
	 * @since 3.1.1
	 * @return void
	 */
	public function do_pass_field() {
		$pro_license = $this->handler->get_pro_license();
		$license_key = $pro_license->key;
		if ( empty( $pro_license->key ) ) {
			$pass_manager = new \EDD\Admin\Pass_Manager();
			if ( ! empty( $pass_manager->highest_license_key ) ) {
				$license_key = $pass_manager->highest_license_key;
			}
		}
		$this->enqueue();
		?>
		<h3><?php echo esc_html( $this->get_heading_text() ); ?></h3>
		<table class="form-table" role="presentation">
			<tbody>
				<tr>
					<th scope="row">
						<label for="edd_pass_key"><?php esc_html_e( 'License Key', 'easy-digital-downloads' ); ?></label>
					</th>
					<td>
						<?php $this->show_free_message( $pro_license ); ?>
						<div class="edd-pass-handler__control">
							<input
								id="edd_pass_key"
								type="password"
								class="regular-text"
								value="<?php echo esc_attr( $license_key ); ?>"
								placeholder="<?php esc_html_e( 'Paste license key', 'easy-digital-downloads' ); ?>"
								<?php echo ( ! empty( $pro_license->key ) && 'valid' === $pro_license->license ? 'readonly' : '' ); ?>
							>
							<?php $this->handler->get_pass_actions( $pro_license->license, $pro_license->key, true ); ?>
						</div>
						<?php
						if ( edd_is_pro() ) {
							$messages = new \EDD\Licensing\Messages(
								array(
									'status'       => $pro_license->license,
									'license_key'  => $pro_license->key,
									'expires'      => $pro_license->expires,
									'name'         => $pro_license->item_name,
									'subscription' => $pro_license->subscription,
								)
							);
							$message  = $messages->get_message();
							if ( $message ) {
								echo wp_kses_post( wpautop( $message ) );
							}
						} elseif ( empty( $pro_license->key ) && ! empty( $license_key ) ) {
							?>
							<p><?php esc_html_e( 'We see that you have an active pass--just hit Verify to get started with (Pro).', 'easy-digital-downloads' ); ?></p>
							<?php
						}
						?>
					</td>
				</tr>
			</tbody>
		</table>
		<?php
	}

	/**
	 * Registers the pass handler script and style.
	 *
	 * @since 3.1.1
	 * @return void
	 */
	public function register_assets() {
		if ( wp_script_is( 'edd-pass-handler', 'registered' ) ) {
			return;
		}
		if ( ! edd_is_admin_page( 'settings' ) ) {
			return;
		}
		wp_register_style( 'edd-pass-handler', EDD_PLUGIN_URL . 'assets/css/edd-admin-pass-handler.min.css', array(), EDD_VERSION );
		wp_register_script( 'edd-pass-handler', EDD_PLUGIN_URL . 'assets/js/edd-admin-pass-handler.js', array( 'jquery' ), EDD_VERSION, true );
		wp_localize_script(
			'edd-pass-handler',
			'EDDPassManager',
			array(
				'verifying'     => __( 'Verifying', 'easy-digital-downloads' ),
				'activating'    => __( 'Activating', 'easy-digital-downloads' ),
				'deactivating'  => __( 'Deactivating', 'easy-digital-downloads' ),
				'verify_loader' => __( 'Just a moment while we connect your site and upgrade you to (Pro).', 'easy-digital-downloads' ),
			)
		);
	}

	/**
	 * Enqueues the pass handler script/style.
	 *
	 * @since 3.1.1
	 * @return void
	 */
	public function enqueue() {
		wp_enqueue_style( 'edd-pass-handler' );
		wp_enqueue_script( 'edd-pass-handler' );
	}

	/**
	 * Gets the heading text for the pass key field.
	 *
	 * @since 3.1.1
	 * @return string
	 */
	private function get_heading_text() {
		return edd_is_pro() ?
			__( 'Easy Digital Downloads (Pro) Key', 'easy-digital-downloads' ) :
			__( 'Go Pro With Easy Digital Downloads', 'easy-digital-downloads' );
	}

	/**
	 * Show the free message to users without active passes.
	 *
	 * @since 3.1.1
	 * @param array $pro_license
	 * @return void
	 */
	private function show_free_message( $pro_license ) {
		// If we're running the Pro version of EDD, we don't need to show this.
		if ( edd_is_pro() ) {
			return;
		}

		// The user could have the Lite version, but with a Pass activated on extensions, so we need to check for that.
		$pass_manager = new \EDD\Admin\Pass_Manager();
		if ( $pass_manager->has_pass() ) {
			return;
		}

		?>
		<div class="edd-pass-handler__description">
			<p>
				<?php esc_html_e( 'You\'re using Easy Digital Downloads &mdash; no license needed. Enjoy!', 'easy-digital-downloads' ); ?>
				<img src="<?php echo esc_url( EDD_PLUGIN_URL . 'assets/images/icons/icon-smiley.svg' ); ?>" alt="" class="emoji">
			</p>
			<p>
				<?php
				$url = edd_link_helper(
					'https://easydigitaldownloads.com/lite-upgrade/',
					array(
						'utm_medium'  => 'settings-general',
						'utm_content' => 'upgrade-to-pro',
					)
				);
				echo wp_kses_post(
					sprintf(
						/* translators: 1. opening link tag; do not translate; 2. closing link tag; do not translate. */
						__( 'To unlock more features, consider %1$supgrading to Pro%2$s.', 'easy-digital-downloads' ),
						'<strong><a href="' . $url . '" class="edd-pro-upgrade">',
						'</a></strong>'
					)
				);
				?>
			</p>
			<p><?php esc_html_e( 'As a valued EDD user you receive 50% off, automatically applied at checkout!', 'easy-digital-downloads' ); ?></p>
			<p><?php esc_html_e( 'Already purchased? Simply enter your license key to enable EDD (Pro).', 'easy-digital-downloads' ); ?></p>
		</div>
		<?php
	}
}
