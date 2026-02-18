<?php
/**
 * Cart Recovery admin screen router.
 *
 * @package EDD\Admin\CartRecovery
 * @copyright Copyright (c) 2026, Sandhills Development, LLC
 * @license https://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since 3.6.5
 */

namespace EDD\Admin\CartRecovery;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

use EDD\EventManagement\SubscriberInterface;

/**
 * Cart Recovery admin screen router.
 *
 * Routes between the welcome/setup screen and the full Pro feature set.
 *
 * @since 3.6.5
 */
class Screen implements SubscriberInterface {

	/**
	 * Returns subscribed events.
	 *
	 * @since 3.6.5
	 * @return array Event subscriptions.
	 */
	public static function get_subscribed_events() {
		return array(
			'load-download_page_edd-cart-recovery' => 'handle_form_submission',
		);
	}

	/**
	 * Handles form submission for enabling/disabling Cart Recovery.
	 *
	 * @since 3.6.5
	 */
	public static function handle_form_submission() {
		if ( ! isset( $_POST['edd_action'] ) || 'cart_recovery_enable' !== $_POST['edd_action'] ) {
			return;
		}

		if ( ! current_user_can( 'manage_shop_settings' ) ) {
			return;
		}

		if ( ! isset( $_POST['edd_cart_recovery_nonce'] ) || ! wp_verify_nonce( $_POST['edd_cart_recovery_nonce'], 'edd_cart_recovery_enable' ) ) {
			return;
		}

		// Only allow Pro users to enable.
		if ( ! edd_is_pro() || edd_is_inactive_pro() ) {
			return;
		}

		$enabled = isset( $_POST['acr_enabled'] ) && '1' === $_POST['acr_enabled'];

		// Save to edd_settings.
		edd_update_option( 'acr_enabled', $enabled );

		EDD()->notices::add_transient_notice(
			'edd_cart_recovery_notice',
			$enabled
				? __( 'Cart Recovery has been enabled.', 'easy-digital-downloads' )
				: __( 'Cart Recovery has been disabled.', 'easy-digital-downloads' ),
		);

		// Redirect to refresh the page (will show appropriate screen based on enabled state).
		$redirect_url = edd_get_admin_url(
			array(
				'page' => 'edd-cart-recovery',
				'tab'  => 'settings',
			)
		);
		edd_redirect( $redirect_url );
	}

	/**
	 * Renders the admin screen.
	 *
	 * @since 3.6.5
	 */
	public static function render() {

		// Check if Pro is available, ACR is enabled, and user has valid license.
		if ( class_exists( '\\EDD\\Pro\\CartRecovery\\Admin\\Screen' ) && edd_is_pro() && ! edd_is_inactive_pro() && edd_get_option( 'acr_enabled' ) ) {
			// Delegate to Pro screen with full functionality.
			\EDD\Pro\CartRecovery\Admin\Screen::render();
		} else {
			// Show welcome/setup screen.
			self::render_welcome();
		}
	}

	/**
	 * Renders the welcome/setup screen.
	 *
	 * @since 3.6.5
	 */
	private static function render_welcome() {
		self::enqueue_assets();

		$is_lite = ! edd_is_pro() || edd_is_inactive_pro();
		?>
		<div class="wrap">
			<h1><?php esc_html_e( 'Cart Recovery', 'easy-digital-downloads' ); ?></h1>
			<hr class="wp-header-end">

			<div class="edd-cart-recovery-welcome">
				<?php if ( $is_lite ) : ?>
					<?php self::render_promo_content(); ?>
				<?php else : ?>
					<?php self::render_enable_content(); ?>
				<?php endif; ?>
			</div>
		</div>
		<?php
	}

	/**
	 * Renders promotional content for Lite users.
	 *
	 * @since 3.6.5
	 */
	private static function render_promo_content() {
		?>
		<div class="edd-cart-recovery-promo">
			<?php
			self::render_promo_hero();
			self::render_promo_stats();
			self::render_promo_features();
			self::render_promo_how_it_works();
			self::render_promo_cta();
			?>
		</div>
		<?php
	}

	/**
	 * Renders the hero section with personalized lost revenue data.
	 *
	 * @since 3.6.5
	 */
	private static function render_promo_hero() {
		$data          = self::get_abandoned_order_data();
		$has_data      = $data['total'] > 0;
		$upgrade_link  = edd_link_helper(
			'https://easydigitaldownloads.com/lite-upgrade',
			array(
				'utm_medium'  => 'cart-recovery',
				'utm_content' => 'hero-cta',
			),
			false
		);
		?>
		<div class="edd-cart-recovery-promo__hero">
			<?php if ( $has_data ) : ?>
				<p class="edd-cart-recovery-promo__revenue-callout">
					<?php
					printf(
						/* translators: %s: formatted currency amount. */
						esc_html__( 'In the last 90 days, your store has lost %s to abandoned carts', 'easy-digital-downloads' ),
						'<strong>' . esc_html( edd_currency_filter( edd_format_amount( $data['total'] ) ) ) . '</strong>'
					);
					?>
				</p>
			<?php else : ?>
				<p class="edd-cart-recovery-promo__revenue-callout">
					<?php esc_html_e( '7 out of 10 carts are abandoned before checkout', 'easy-digital-downloads' ); ?>
				</p>
			<?php endif; ?>

			<h2><?php esc_html_e( "Don't Let Abandoned Carts Cost You Sales", 'easy-digital-downloads' ); ?></h2>
			<p class="edd-cart-recovery-promo__description">
				<?php esc_html_e( 'Cart Recovery automatically brings customers back to complete their purchase with perfectly-timed email sequences — so you recover revenue while you sleep.', 'easy-digital-downloads' ); ?>
			</p>

			<a class="button edd-pro-upgrade edd-cart-recovery-promo__hero-button" href="<?php echo esc_url( $upgrade_link ); ?>" target="_blank" rel="noopener noreferrer">
				<?php esc_html_e( 'Upgrade to Pro & Start Recovering Revenue', 'easy-digital-downloads' ); ?>
			</a>
			<p class="edd-cart-recovery-promo__hero-discount">
				<?php esc_html_e( '50% off regular price, automatically applied at checkout.', 'easy-digital-downloads' ); ?>
			</p>
		</div>
		<?php
	}

	/**
	 * Renders the key stats bar.
	 *
	 * @since 3.6.5
	 */
	private static function render_promo_stats() {
		$data     = self::get_abandoned_order_data();
		$has_data = $data['count'] > 0;
		?>
		<?php if ( $has_data ) : ?>
			<p class="edd-cart-recovery-promo__stats-heading"><?php esc_html_e( 'Your store in the last 90 days:', 'easy-digital-downloads' ); ?></p>
		<?php endif; ?>
		<div class="edd-cart-recovery-promo__stats">
			<div class="edd-cart-recovery-promo__stat">
				<?php if ( $has_data ) : ?>
					<span class="edd-cart-recovery-promo__stat-number"><?php echo esc_html( number_format_i18n( $data['count'] ) ); ?>+</span>
					<span class="edd-cart-recovery-promo__stat-label"><?php esc_html_e( 'abandoned orders', 'easy-digital-downloads' ); ?></span>
				<?php else : ?>
					<span class="edd-cart-recovery-promo__stat-number"><?php esc_html_e( '70%', 'easy-digital-downloads' ); ?></span>
					<span class="edd-cart-recovery-promo__stat-label"><?php esc_html_e( 'of carts are abandoned', 'easy-digital-downloads' ); ?></span>
				<?php endif; ?>
			</div>
			<div class="edd-cart-recovery-promo__stat">
				<?php if ( $has_data ) : ?>
					<span class="edd-cart-recovery-promo__stat-number"><?php echo esc_html( edd_currency_filter( edd_format_amount( $data['total'] ) ) ); ?>+</span>
					<span class="edd-cart-recovery-promo__stat-label"><?php esc_html_e( 'abandoned revenue', 'easy-digital-downloads' ); ?></span>
				<?php else : ?>
					<span class="edd-cart-recovery-promo__stat-number"><?php esc_html_e( '2x', 'easy-digital-downloads' ); ?></span>
					<span class="edd-cart-recovery-promo__stat-label"><?php esc_html_e( 'higher open rates than marketing emails', 'easy-digital-downloads' ); ?></span>
				<?php endif; ?>
			</div>
			<div class="edd-cart-recovery-promo__stat">
				<?php if ( $has_data ) : ?>
					<span class="edd-cart-recovery-promo__stat-number"><?php echo esc_html( number_format_i18n( $data['customers'] ) ); ?>+</span>
					<span class="edd-cart-recovery-promo__stat-label"><?php esc_html_e( 'potential customers', 'easy-digital-downloads' ); ?></span>
				<?php else : ?>
					<span class="edd-cart-recovery-promo__stat-number"><?php esc_html_e( '3x', 'easy-digital-downloads' ); ?></span>
					<span class="edd-cart-recovery-promo__stat-label"><?php esc_html_e( 'more conversions with email sequences', 'easy-digital-downloads' ); ?></span>
				<?php endif; ?>
			</div>
		</div>
		<?php
	}

	/**
	 * Renders the feature cards section.
	 *
	 * @since 3.6.5
	 */
	private static function render_promo_features() {
		$features = array(
			array(
				'icon'        => 'dashicons-email-alt',
				'title'       => __( 'Automated Recovery Sequences', 'easy-digital-downloads' ),
				'description' => __( 'Timed sequences that recover sales while you sleep.', 'easy-digital-downloads' ),
				'bullets'     => array(
					__( 'Multiple emails per sequence for maximum recovery', 'easy-digital-downloads' ),
					__( 'Customizable delays between each email', 'easy-digital-downloads' ),
				),
			),
			array(
				'icon'        => 'dashicons-chart-line',
				'title'       => __( 'Measure, Learn, and Recover More', 'easy-digital-downloads' ),
				'description' => __( 'Track recovered revenue, conversion rates, and email performance.', 'easy-digital-downloads' ),
				'bullets'     => array(
					__( 'Real-time revenue recovery dashboard', 'easy-digital-downloads' ),
					__( 'Per-email open and click tracking', 'easy-digital-downloads' ),
				),
			),
			array(
				'icon'        => 'dashicons-cart',
				'title'       => __( 'Catch Abandoned Carts Instantly', 'easy-digital-downloads' ),
				'description' => __( 'Real-time cart monitoring captures emails at checkout.', 'easy-digital-downloads' ),
				'bullets'     => array(
					__( 'Automatic cart status detection', 'easy-digital-downloads' ),
					__( 'Customer email capture before checkout', 'easy-digital-downloads' ),
				),
			),
			array(
				'icon'        => 'dashicons-edit-large',
				'title'       => __( 'Emails Tailored to Each Customer', 'easy-digital-downloads' ),
				'description' => __( 'Dynamic product details, custom messaging, and branded templates.', 'easy-digital-downloads' ),
				'bullets'     => array(
					__( 'Dynamic cart contents in every email', 'easy-digital-downloads' ),
					__( 'Fully customizable email templates', 'easy-digital-downloads' ),
				),
			),
		);
		?>
		<div class="edd-cart-recovery-promo__features">
			<?php foreach ( $features as $feature ) : ?>
				<div class="edd-cart-recovery-promo__feature">
					<span class="dashicons <?php echo esc_attr( $feature['icon'] ); ?>"></span>
					<h3><?php echo esc_html( $feature['title'] ); ?></h3>
					<p><?php echo esc_html( $feature['description'] ); ?></p>
					<?php if ( ! empty( $feature['bullets'] ) ) : ?>
						<ul class="edd-cart-recovery-promo__feature-bullets">
							<?php foreach ( $feature['bullets'] as $bullet ) : ?>
								<li>
									<img src="<?php echo esc_url( EDD_PLUGIN_URL . 'assets/images/icons/edd-blue-checkmark.svg' ); ?>" alt="" aria-hidden="true" />
									<?php echo esc_html( $bullet ); ?>
								</li>
							<?php endforeach; ?>
						</ul>
					<?php endif; ?>
				</div>
			<?php endforeach; ?>
		</div>
		<?php
	}

	/**
	 * Renders the "How It Works" section.
	 *
	 * @since 3.6.5
	 */
	private static function render_promo_how_it_works() {
		?>
		<div class="edd-cart-recovery-promo__how-it-works">
			<h2><?php esc_html_e( 'How It Works', 'easy-digital-downloads' ); ?></h2>
			<div class="edd-cart-recovery-promo__steps">
				<div class="edd-cart-recovery-promo__step">
					<span class="edd-cart-recovery-promo__step-number">1</span>
					<span class="dashicons dashicons-cart"></span>
					<h3><?php esc_html_e( 'Customer Abandons Cart', 'easy-digital-downloads' ); ?></h3>
					<p><?php esc_html_e( 'A shopper adds items to their cart but leaves without completing the purchase.', 'easy-digital-downloads' ); ?></p>
				</div>
				<div class="edd-cart-recovery-promo__step-arrow">
					<span class="dashicons dashicons-arrow-right-alt"></span>
				</div>
				<div class="edd-cart-recovery-promo__step">
					<span class="edd-cart-recovery-promo__step-number">2</span>
					<span class="dashicons dashicons-email-alt"></span>
					<h3><?php esc_html_e( 'Emails Send Automatically', 'easy-digital-downloads' ); ?></h3>
					<p><?php esc_html_e( 'Your pre-configured email sequence kicks in with perfectly-timed follow-ups.', 'easy-digital-downloads' ); ?></p>
				</div>
				<div class="edd-cart-recovery-promo__step-arrow">
					<span class="dashicons dashicons-arrow-right-alt"></span>
				</div>
				<div class="edd-cart-recovery-promo__step">
					<span class="edd-cart-recovery-promo__step-number">3</span>
					<span class="dashicons dashicons-yes-alt"></span>
					<h3><?php esc_html_e( 'Lost Sale Recovered', 'easy-digital-downloads' ); ?></h3>
					<p><?php esc_html_e( 'The customer returns and completes their purchase — revenue recovered.', 'easy-digital-downloads' ); ?></p>
				</div>
			</div>
		</div>
		<?php
	}

	/**
	 * Renders the bottom CTA section.
	 *
	 * @since 3.6.5
	 */
	private static function render_promo_cta() {
		$upgrade_link = edd_link_helper(
			'https://easydigitaldownloads.com/lite-upgrade',
			array(
				'utm_medium'  => 'cart-recovery',
				'utm_content' => 'bottom-cta',
			),
			false
		);
		$learn_more_link = edd_link_helper(
			'https://easydigitaldownloads.com/docs/abandoned-cart-recovery/',
			array(
				'utm_medium'  => 'cart-recovery',
				'utm_content' => 'learn-more',
			),
			false
		);
		?>
		<div class="edd-cart-recovery-promo__cta">
			<a class="button edd-pro-upgrade edd-cart-recovery-promo__cta-button" href="<?php echo esc_url( $upgrade_link ); ?>" target="_blank" rel="noopener noreferrer">
				<?php esc_html_e( 'Upgrade to Pro & Start Recovering Revenue', 'easy-digital-downloads' ); ?>
			</a>
			<p class="edd-cart-recovery-promo__discount">
				<?php esc_html_e( '50% off regular price, automatically applied at checkout.', 'easy-digital-downloads' ); ?>
			</p>
			<p>
				<a href="<?php echo esc_url( $learn_more_link ); ?>" target="_blank" rel="noopener noreferrer" class="edd-cart-recovery-promo__learn-more">
					<?php esc_html_e( 'Learn more about Cart Recovery', 'easy-digital-downloads' ); ?>
				</a>
			</p>
		</div>
		<?php
	}

	/**
	 * Gets data from pending and abandoned orders to show lost revenue.
	 *
	 * @since 3.6.5
	 *
	 * @return array Array with 'total' (float), 'count' (int), and 'customers' (int) keys.
	 */
	private static function get_abandoned_order_data() {
		$default = array(
			'total'     => 0.00,
			'count'     => 0,
			'customers' => 0,
		);

		// Cache for 1 hour to avoid running this query on every page load.
		$cached = get_transient( 'edd_acr_promo_abandoned_data' );
		if ( false !== $cached ) {
			return $cached;
		}

		$data            = $default;
		$ninety_days_ago = EDD()->utils->date( '-90 days' );

		$orders = edd_get_orders(
			array(
				'status__in'         => array( 'pending', 'abandoned' ),
				'type'               => 'sale',
				'number'             => 999999,
				'date_created_query' => array(
					'after'     => array(
						'year'  => $ninety_days_ago->format( 'Y' ),
						'month' => $ninety_days_ago->format( 'm' ),
						'day'   => $ninety_days_ago->format( 'd' ),
					),
					'inclusive' => true,
				),
			)
		);

		if ( ! empty( $orders ) ) {
			$emails        = array();
			$data['count'] = count( $orders );
			foreach ( $orders as $order ) {
				$data['total'] += (float) $order->total;
				if ( ! empty( $order->email ) ) {
					$emails[ strtolower( $order->email ) ] = true;
				}
			}
			$data['customers'] = count( $emails );
		}

		set_transient( 'edd_acr_promo_abandoned_data', $data, HOUR_IN_SECONDS );

		return $data;
	}

	/**
	 * Renders enable content for Pro users.
	 *
	 * @since 3.6.5
	 */
	private static function render_enable_content() {
		?>
		<div class="edd-cart-recovery-enable">
			<div class="edd-cart-recovery-enable__header">
				<h2><?php esc_html_e( 'Enable Cart Recovery', 'easy-digital-downloads' ); ?></h2>
				<p><?php esc_html_e( 'Automatically recover lost revenue by sending targeted emails to customers who abandon their shopping carts.', 'easy-digital-downloads' ); ?></p>
			</div>

			<div class="edd-cart-recovery-enable__features">
				<ul>
					<li><span class="dashicons dashicons-yes-alt"></span> <?php esc_html_e( 'Automated email sequences', 'easy-digital-downloads' ); ?></li>
					<li><span class="dashicons dashicons-yes-alt"></span> <?php esc_html_e( 'Detailed analytics and reporting', 'easy-digital-downloads' ); ?></li>
					<li><span class="dashicons dashicons-yes-alt"></span> <?php esc_html_e( 'Customer activity tracking', 'easy-digital-downloads' ); ?></li>
					<li><span class="dashicons dashicons-yes-alt"></span> <?php esc_html_e( 'Customizable email templates', 'easy-digital-downloads' ); ?></li>
				</ul>
			</div>

			<form method="post" action="">
				<?php wp_nonce_field( 'edd_cart_recovery_enable', 'edd_cart_recovery_nonce' ); ?>
				<input type="hidden" name="edd_action" value="cart_recovery_enable" />

				<table class="form-table">
					<tr>
						<th scope="row">
							<label for="acr_enabled"><?php esc_html_e( 'Enable Cart Recovery', 'easy-digital-downloads' ); ?></label>
						</th>
						<td>
							<label class="edd-toggle">
								<input type="checkbox" id="acr_enabled" name="acr_enabled" value="1">
								<?php esc_html_e( 'Enable abandoned cart recovery.', 'easy-digital-downloads' ); ?>
							</label>
							<p class="description">
								<?php esc_html_e( 'Recover lost revenue by automatically sending effective, targeted emails to customers who abandon their shopping cart.', 'easy-digital-downloads' ); ?>
							</p>
						</td>
					</tr>
				</table>

				<p class="submit">
					<button type="submit" class="button button-primary button-hero">
						<?php esc_html_e( 'Save Changes', 'easy-digital-downloads' ); ?>
					</button>
				</p>
			</form>
		</div>
		<?php
	}

	/**
	 * Enqueues assets for the welcome screen.
	 *
	 * @since 3.6.5
	 */
	private static function enqueue_assets() {
		?>
		<style>
			/* Layout. */
			.edd-cart-recovery-welcome {
				max-width: 1000px;
				margin: 20px auto;
			}

			/* Hero section. */
			.edd-cart-recovery-promo__hero {
				text-align: center;
				margin-bottom: 20px;
				padding: 28px 30px 24px;
				background: #fff;
				border: 1px solid #c3c4c7;
				border-radius: 4px;
			}
			.edd-cart-recovery-promo__revenue-callout {
				font-size: 18px;
				color: #2271b1;
				margin: 0 0 8px;
			}
			.edd-cart-recovery-promo__revenue-callout strong {
				font-size: 22px;
			}
			.edd-cart-recovery-promo__hero h2 {
				font-size: 26px;
				margin: 0 0 8px;
				line-height: 1.3;
			}
			.edd-cart-recovery-promo__description {
				font-size: 15px;
				color: #646970;
				max-width: 650px;
				margin: 0 auto 16px;
			}
			.edd-cart-recovery-promo__hero-button {
				font-size: 15px !important;
				padding: 8px 24px !important;
				height: auto !important;
				line-height: 1.6 !important;
			}
			.edd-cart-recovery-promo__hero-discount {
				font-size: 12px;
				color: #646970;
				margin: 8px 0 0;
			}

			/* Stats bar heading. */
			.edd-cart-recovery-promo__stats-heading {
				text-align: center;
				font-size: 13px;
				color: #646970;
				text-transform: uppercase;
				letter-spacing: 0.5px;
				margin: 0 0 6px;
			}

			/* Stats bar. */
			.edd-cart-recovery-promo__stats {
				display: flex;
				justify-content: space-around;
				background: #2271b1;
				border-radius: 4px;
				padding: 20px;
				margin-bottom: 20px;
			}
			.edd-cart-recovery-promo__stat {
				text-align: center;
				flex: 1;
			}
			.edd-cart-recovery-promo__stat-number {
				display: block;
				font-size: 30px;
				font-weight: 700;
				color: #fff;
				line-height: 1.2;
			}
			.edd-cart-recovery-promo__stat-label {
				display: block;
				font-size: 13px;
				color: rgba(255, 255, 255, 0.85);
				margin-top: 2px;
			}

			/* Feature cards. */
			.edd-cart-recovery-promo__features {
				display: grid;
				grid-template-columns: repeat(2, 1fr);
				gap: 16px;
				margin-bottom: 20px;
			}
			.edd-cart-recovery-promo__feature {
				background: #fff;
				padding: 20px;
				border: 1px solid #c3c4c7;
				border-radius: 4px;
			}
			.edd-cart-recovery-promo__feature .dashicons {
				font-size: 30px;
				width: 30px;
				height: 30px;
				color: #2271b1;
				margin-bottom: 8px;
			}
			.edd-cart-recovery-promo__feature h3 {
				margin: 0 0 6px;
				font-size: 15px;
			}
			.edd-cart-recovery-promo__feature > p {
				margin: 0 0 8px;
				color: #646970;
				font-size: 13px;
			}
			.edd-cart-recovery-promo__feature-bullets {
				list-style: none;
				margin: 0;
				padding: 0;
			}
			.edd-cart-recovery-promo__feature-bullets li {
				display: flex;
				align-items: center;
				gap: 8px;
				padding: 3px 0;
				font-size: 13px;
				color: #3c434a;
			}
			.edd-cart-recovery-promo__feature-bullets img {
				width: 16px;
				height: 16px;
				flex-shrink: 0;
			}

			/* How It Works. */
			.edd-cart-recovery-promo__how-it-works {
				background: #fff;
				border: 1px solid #c3c4c7;
				border-radius: 4px;
				padding: 20px 30px;
				margin-bottom: 20px;
				text-align: center;
			}
			.edd-cart-recovery-promo__how-it-works > h2 {
				font-size: 20px;
				margin: 0 0 16px;
			}
			.edd-cart-recovery-promo__steps {
				display: flex;
				align-items: flex-start;
				justify-content: center;
				gap: 0;
			}
			.edd-cart-recovery-promo__step {
				flex: 1;
				max-width: 240px;
				text-align: center;
				padding: 0 12px;
			}
			.edd-cart-recovery-promo__step-number {
				display: inline-flex;
				align-items: center;
				justify-content: center;
				width: 28px;
				height: 28px;
				border-radius: 50%;
				background: #2271b1;
				color: #fff;
				font-size: 14px;
				font-weight: 700;
				margin-bottom: 6px;
			}
			.edd-cart-recovery-promo__step .dashicons {
				display: block;
				font-size: 28px;
				width: 28px;
				height: 28px;
				color: #2271b1;
				margin: 0 auto 6px;
			}
			.edd-cart-recovery-promo__step h3 {
				font-size: 15px;
				margin: 0 0 6px;
			}
			.edd-cart-recovery-promo__step p {
				margin: 0;
				font-size: 13px;
				color: #646970;
			}
			.edd-cart-recovery-promo__step-arrow {
				display: flex;
				align-items: center;
				padding-top: 20px;
			}
			.edd-cart-recovery-promo__step-arrow .dashicons {
				font-size: 28px;
				width: 28px;
				height: 28px;
				color: #c3c4c7;
			}

			/* Bottom CTA. */
			.edd-cart-recovery-promo__cta {
				text-align: center;
				padding: 24px 30px;
				background: #f6f7f7;
				border-radius: 4px;
				margin-bottom: 20px;
			}
			.edd-cart-recovery-promo__cta-button {
				font-size: 16px !important;
				padding: 10px 28px !important;
				height: auto !important;
				line-height: 1.6 !important;
			}
			.edd-cart-recovery-promo__discount {
				font-size: 14px;
				color: #3c434a;
				margin: 12px 0 8px;
				font-weight: 500;
			}
			.edd-cart-recovery-promo__learn-more {
				font-size: 13px;
				color: #2271b1;
				text-decoration: none;
			}
			.edd-cart-recovery-promo__learn-more:hover {
				text-decoration: underline;
			}

			/* Enable screen (Pro users). */
			.edd-cart-recovery-enable__header {
				margin-bottom: 30px;
			}
			.edd-cart-recovery-enable__header h2 {
				font-size: 24px;
				margin-bottom: 10px;
			}
			.edd-cart-recovery-enable__features {
				background: #fff;
				border: 1px solid #c3c4c7;
				border-radius: 4px;
				padding: 20px 30px;
				margin-bottom: 30px;
			}
			.edd-cart-recovery-enable__features ul {
				list-style: none;
				margin: 0;
				padding: 0;
			}
			.edd-cart-recovery-enable__features li {
				padding: 8px 0;
				font-size: 15px;
			}
			.edd-cart-recovery-enable__features .dashicons {
				color: #00a32a;
				margin-right: 8px;
			}

			/* Responsive. */
			@media (max-width: 782px) {
				.edd-cart-recovery-promo__stats {
					flex-direction: column;
					gap: 20px;
				}
				.edd-cart-recovery-promo__features {
					grid-template-columns: 1fr;
				}
				.edd-cart-recovery-promo__steps {
					flex-direction: column;
					align-items: center;
					gap: 16px;
				}
				.edd-cart-recovery-promo__step-arrow {
					padding-top: 0;
					transform: rotate(90deg);
				}
				.edd-cart-recovery-promo__step {
					max-width: 100%;
				}
			}
		</style>
		<?php
	}
}
