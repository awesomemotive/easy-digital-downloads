<?php
/**
 * Dashboard Review Notice
 *
 * @package   easy-digital-downloads
 * @copyright Copyright (c) 2021, Easy Digital Downloads
 * @license   GPL2+
 * @since     2.11.4
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
	const TYPE = 'dashboard';

	/**
	 * Capability required to view or dismiss the notice.
	 */
	const CAPABILITY = 'manage_shop_settings';

	/**
	 * The current screen.
	 *
	 * @var string
	 */
	protected $screen = 'dashboard';

	/**
	 * The ID of the notice. Defined specifically here as we intend to use it twice.
	 *
	 * @since 2.11.4
	 * @return string
	 */
	public function get_id() {
		return 'five-star-review';
	}

	/**
	 * Display the notice.
	 *
	 * @since 2.11.4
	 * @return void
	 */
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
	 * @since 2.11.4
	 * @return void
	 */
	public function _display() {
		?>
		<div class="edd-review-step edd-review-step-1">
			<p><?php esc_html_e( 'Hey, I noticed you\'ve made quite a few sales with Easy Digital Downloads! Are you enjoying Easy Digital Downloads?', 'easy-digital-downloads' ); ?></p>
			<div class="edd-review-actions">
				<button class="button-primary edd-review-switch-step" data-step="3"><?php esc_html_e( 'Yes', 'easy-digital-downloads' ); ?></button><br />
				<button class="button-link edd-review-switch-step" data-step="2"><?php esc_html_e( 'Not Really', 'easy-digital-downloads' ); ?></button>
			</div>
		</div>
		<div class="edd-review-step edd-review-step-2" style="display:none;">
			<p><?php esc_html_e( 'We\'re sorry to hear you aren\'t enjoying Easy Digital Downloads. We would love a chance to improve. Could you take a minute and let us know what we can do better?', 'easy-digital-downloads' ); ?></p>
			<div class="edd-review-actions">
				<a href="<?php echo esc_url( $this->url() ); ?>" class="button button-secondary edd-promo-notice-dismiss" target="_blank"><?php esc_html_e( 'Give Feedback', 'easy-digital-downloads' ); ?></a><br>
				<button class="button-link edd-promo-notice-dismiss"><?php esc_html_e( 'No thanks', 'easy-digital-downloads' ); ?></button>
			</div>
		</div>
		<div class="edd-review-step edd-review-step-3" style="display:none;">
			<p><?php esc_html_e( 'That\'s awesome! Could you please do me a BIG favor and give it a 5-star rating on WordPress to help us spread the word and boost our motivation?', 'easy-digital-downloads' ); ?></p>
			<p><strong><?php echo wp_kses( __( '~ Chris Klosowski<br>President of Easy Digital Downloads', 'easy-digital-downloads' ), array( 'br' => array() ) ); ?></strong></p>
			<div class="edd-review-actions">
				<a href="https://wordpress.org/support/plugin/easy-digital-downloads/reviews/?filter=5#new-post" class="button button-primary edd-promo-notice-dismiss" target="_blank" rel="noopener noreferrer"><?php esc_html_e( 'Ok, you deserve it!', 'easy-digital-downloads' ); ?></a><br>
				<button class="button-link edd-promo-notice-dismiss"><?php esc_html_e( 'No thanks', 'easy-digital-downloads' ); ?></button>
			</div>
		</div>
		<img alt="" class="edd-peeking" src="<?php echo esc_url( EDD_PLUGIN_URL . 'assets/images/edd-peeking.png' ); ?>" />
		<script type="text/javascript">
			document.addEventListener( 'DOMContentLoaded', function() {
				var steps = document.querySelectorAll( '.edd-review-switch-step' );
				steps.forEach( function(step) {
					step.addEventListener( 'click', function ( e ) {
						e.preventDefault();
						var target = this.getAttribute( 'data-step' );
						if ( target ) {
							var notice = this.closest( '.edd-promo-notice' );
							var review_step = notice.querySelector( '.edd-review-step-' + target );
							if ( review_step ) {
								var thisStep = this.closest( '.edd-review-step' );
								eddFadeOut( thisStep );
								eddFadeIn( review_step );
							}
						}
					} )
				} )

				function eddFadeIn( element ) {
					var op = 0;
					element.style.opacity = op;
					element.style.display = 'block';
					var timer = setInterval( function () {
						if ( op >= 1 ) {
							clearInterval( timer );
						}
						element.style.opacity = op;
						element.style.filter = 'alpha(opacity=' + op * 100 + ')';
						op = op + 0.1;
					}, 80 );
				}

				function eddFadeOut( element ) {
					var op = 1;
					var timer = setInterval( function () {
						if ( op <= 0 ) {
							element.style.display = 'none';
							clearInterval( timer );
						}
						element.style.opacity = op;
						element.style.filter = 'alpha(opacity=' + op * 100 + ')';
						op = op - 0.1;
					}, 80 );
				}
			} );
		</script>
		<?php
	}

	/**
	 * Whether the notice should display.
	 *
	 * @since 2.11.4
	 * @return bool
	 */
	protected function _should_display() {

		$activated = edd_get_activation_date();
		// Do not show if EDD was activated less than 30 days ago.
		if ( ! is_numeric( $activated ) || ( $activated + ( DAY_IN_SECONDS * 30 ) ) > time() ) {
			return false;
		}
		$orders = edd_count_orders(
			array(
				'type'       => 'sale',
				'status__in' => edd_get_complete_order_statuses(),
			)
		);

		return $orders >= 15;
	}

	/**
	 * Builds the UTM parameters for the URLs.
	 *
	 * @since 2.11.4
	 *
	 * @return string
	 */
	private function url() {
		$url = edd_link_helper(
			'https://easydigitaldownloads.com/plugin-feedback/',
			array(
				'utm_medium'  => 'feedback-' . static::TYPE,
				'utm_content' => 'give-feedback',
			)
		);

		return $url;
	}
}
