<?php
/**
 * Show an upgrade notice on the extensions page.
 */
namespace EDD\Admin\Promos\Notices;

class Lite extends Notice {

	/**
	 * Action hook for displaying the notice.
	 */
	const DISPLAY_HOOK = 'download_page_edd-addons';

	/**
	 * The priority for the display hook.
	 */
	const DISPLAY_PRIORITY = 5;

	/**
	 * Type of promotional notice.
	 */
	const TYPE = 'overlay';

	/**
	 * Displays the notice content.
	 *
	 * @return void
	 */
	protected function _display() {
		$upgrade_link = edd_link_helper(
			'https://easydigitaldownloads.com/lite-upgrade',
			array(
				'utm_medium' => 'extensions-page-overlay',
				'utm_content' => 'upgrade-to-pro',
			)
		);
		?>
		<h2><?php esc_html_e( 'Thanks for your interest in Easy Digital Downloads (Pro)!', 'easy-digital-downloads' ); ?></h2>
		<p><?php esc_html_e( 'After purchasing a license, just enter your license key on the EDD Settings page. This will let your site automatically upgrade to Easy Digital Downloads (Pro)!', 'easy-digital-downloads' ); ?></p>
		<p><?php esc_html_e( '(Don\'t worry, all your products, orders, and settings will be preserved.)', 'easy-digital-downloads' ); ?></p>
		<?php $this->do_features(); ?>
		<a href="<?php echo esc_attr( $upgrade_link ); ?>" class="button button-primary"><?php esc_html_e( 'Upgrade to Pro', 'easy-digital-downloads' ); ?></a>
		<br />
		<?php
		$this->do_learn_more_link();
	}

	/**
	 * Duration (in seconds) that the notice is dismissed for.
	 * `0` means it's dismissed permanently.
	 *
	 * @return int
	 */
	public static function dismiss_duration() {
		return 1;
	}

	/**
	 * Outputs the features list.
	 *
	 * @since 3.1.1
	 * @return void
	 */
	protected function do_features() {
		?>
		<ul class="edd-promo-notice__features">
			<?php
			$list_items = array(
				'gateways'        => __( 'Pro Payment Gateways', 'easy-digital-downloads' ),
				'email-marketing' => __( 'Email Marketing Integrations', 'easy-digital-downloads' ),
				'subscriptions'   => __( 'Sell Subscriptions', 'easy-digital-downloads' ),
				'lead-magnets'    => __( 'Build Lead Magnets', 'easy-digital-downloads' ),
				'bundle'          => __( 'Advanced Bundle Features', 'easy-digital-downloads' ),
				'automate'        => __( 'Automate Your Business', 'easy-digital-downloads' ),
			);
			foreach ( $list_items as $icon => $label ) {
				printf(
					'<li><img src="%s" alt=""/>%s</li>',
					esc_url( EDD_PLUGIN_URL . "assets/images/icons/icon-{$icon}.svg" ),
					esc_html( $label )
				);
			}
			?>
		</ul>
		<?php
	}

	/**
	 * Outputs the "learn more about all extensions" link.
	 *
	 * @since 3.1.1
	 * @return void
	 */
	protected function do_learn_more_link() {
		$support_link = edd_link_helper(
			'https://easydigitaldownloads.com/support',
			array(
				'utm_medium'  => 'extensions-page-overlay',
				'utm_content' => 'have-questions',
			)
		);
		?>
		<a href="<?php echo esc_url( $support_link ); ?>" class="edd-admin-notice-overlay__link"><?php esc_html_e( 'Have a question? Let us know!', 'easy-digital-downloads' ); ?></a>
		<?php
	}

	/**
	 * @inheritDoc
	 * @since 3.1.1
	 * @return bool
	 */
	protected function _should_display() {
		return ! edd_is_pro();
	}
}
