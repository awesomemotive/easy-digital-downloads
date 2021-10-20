<?php
/**
 * Dashboard Review Notice
 *
 * @package   easy-digital-downloads
 * @copyright Copyright (c) 2021, Easy Digital Downloads
 * @license   GPL2+
 * @since     2.11.x
 */

namespace EDD\Admin\Promos\Notices;

class Five_Star_Review_Dashboard extends Notice {

	/**
	 * Action hook for displaying the notice.
	 */
	const DISPLAY_HOOK = 'edd_dashboard_sales_widget';

	/**
	 * Type of promotional notice.
	 */
	const TYPE = 'dashboard-widget';

	/**
	 * Capability required to view or dismiss the notice.
	 */
	const CAPABILITY = 'manage_shop_settings';

	/**
	 * The current screen.
	 *
	 * @var string
	 */
	private $screen = 'dashboard';

	/**
	 * The ID of the notice. Defined specifically here as we intend to use it twice.
	 *
	 * @since 2.11.x
	 * @return void
	 */
	public function get_id() {
		return 'five-star-review';
	}

	public function display() {
		?>
		<div
			id="edd-admin-notice-<?php echo esc_attr( $this->get_id() ); ?>"
			class="edd-admin-notice-<?php echo esc_attr( sanitize_html_class( static::TYPE ) ); ?> edd-promo-notice"
			data-nonce="<?php echo esc_attr( wp_create_nonce( 'edd-dismiss-notice-' . $this->get_id() ) ); ?>"
			data-id="<?php echo esc_attr( $this->get_id() ); ?>"
			data-lifespan="<?php echo esc_attr( static::dismiss_duration() ); ?>"
		>
			<?php
			$this->_display();
			?>
		</div>
		<?php
	}

	/**
	 * The promo notice content.
	 *
	 * @since 2.11.x
	 * @return void
	 */
	public function _display() {
		?>
		<p><?php esc_html_e( 'Hey, I noticed you\'ve made quite a few sales with Easy Digital Downloads - that’s awesome! Could you please do me a BIG favor and give it a 5-star rating on WordPress to help us spread the word and boost our motivation?', 'easy-digital-downloads' ); ?></p>
		<p><strong><?php echo wp_kses( __( '~ Chris Klosowski<br>President of Easy Digital Downloads', 'easy-digital-downloads' ), array( 'br' => array() ) ); ?></strong></p>
		<p>
			<a href="https://wordpress.org/support/plugin/easy-digital-downloads/reviews/?filter=5#new-post" class="edd-promo-notice-dismiss" target="_blank" rel="noopener noreferrer"><?php esc_html_e( 'Ok, you deserve it', 'easy-digital-downloads' ); ?></a><br>

			<a class="button-link edd-promo-notice-dismiss" target="_blank" rel="noopener noreferrer"><?php esc_html_e( 'Nope, maybe later', 'easy-digital-downloads' ); ?></a><br>
			<a class="button-link edd-promo-notice-dismiss" target="_blank" rel="noopener noreferrer"><?php esc_html_e( 'I already did', 'easy-digital-downloads' ); ?></a>
		</p>
		<?php
	}

	/**
	 * Whether the notice should display.
	 *
	 * @since 2.11.x
	 * @return bool
	 */
	protected function _should_display() {
		if ( ! current_user_can( static::CAPABILITY ) ) {
			return false;
		}
		$activated = get_option( 'edd_activation', false );
		if ( $activated ) {
			if ( ( $activated + ( DAY_IN_SECONDS * 30 ) ) > time() ) {
				// return false;
			}
		} else {
			// update_option( 'edd_activation', time() );
			// return false;
		}
		$payments = edd_count_payments();
		if ( $payments && 50 > $payments->publish ) {
			return false;
		}

		return true;
	}
}
