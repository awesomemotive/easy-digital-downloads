<?php
/**
 * Stripe Disconnect Confirmation
 *
 * The dialog that asks the merchant how to disconnect Stripe before anything is removed.
 *
 * @package     EDD\Gateways\Stripe\Admin\Disconnect
 * @copyright   Copyright (c) 2026, Sandhills Development, LLC
 * @license     https://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       3.7.3
 */

namespace EDD\Gateways\Stripe\Admin\Disconnect;

defined( 'ABSPATH' ) || exit; // @codeCoverageIgnore

/**
 * Confirmation class.
 *
 * @since 3.7.3
 */
class Confirmation {

	/**
	 * The ID of the confirmation dialog.
	 *
	 * @since 3.7.3
	 * @var string
	 */
	const DIALOG_ID = 'edds-stripe-disconnect-dialog';

	/**
	 * Prints the dialog asking the merchant how to disconnect.
	 *
	 * Disconnecting removes this site's keys only. Revoking turns the authorization off for every site
	 * using the Stripe account, so it is its own, clearly labeled choice, offered only when the store holds
	 * an authorization to revoke and is the production site.
	 *
	 * @since 3.7.3
	 * @return void
	 */
	public static function render() {
		$deauthorize  = new Deauthorize();
		$is_revocable = $deauthorize->is_revocable();
		$offer_revoke = $deauthorize->can_offer_revoke();
		?>
		<dialog
			id="<?php echo esc_attr( self::DIALOG_ID ); ?>"
			class="edd-modal edd-modal--confirm"
			aria-labelledby="<?php echo esc_attr( self::DIALOG_ID ); ?>__title"
		>
			<div class="edd-modal__header">
				<h2 id="<?php echo esc_attr( self::DIALOG_ID ); ?>__title"><?php esc_html_e( 'Disconnect Stripe', 'easy-digital-downloads' ); ?></h2>
				<button type="button" class="edd-modal__close" aria-label="<?php esc_attr_e( 'Close', 'easy-digital-downloads' ); ?>">
					<span class="dashicons dashicons-no-alt"></span>
					<span class="screen-reader-text"><?php esc_html_e( 'Close', 'easy-digital-downloads' ); ?></span>
				</button>
			</div>
			<div class="edd-modal__content">
				<p><?php esc_html_e( 'This removes the Stripe keys from this site only, and Stripe stops being available at checkout here. The keys keep working in Stripe, and other sites using this Stripe account are unaffected. Removing Easy Digital Downloads under Settings > Installed Apps in your Stripe dashboard revokes the keys for every site using this Stripe account.', 'easy-digital-downloads' ); ?></p>
				<?php if ( $offer_revoke ) : ?>
					<p><?php esc_html_e( 'Revoking turns off the Stripe keys for this site and every other site connected to this Stripe account, such as a staging copy. Each of them stops taking Stripe payments right away until it reconnects.', 'easy-digital-downloads' ); ?></p>
				<?php elseif ( $is_revocable ) : ?>
					<p><?php esc_html_e( 'Revoking is available from the production site.', 'easy-digital-downloads' ); ?></p>
				<?php endif; ?>
			</div>
			<div class="edd-modal__footer">
				<a class="button button-primary" href="<?php echo esc_url( edds_stripe_connect_disconnect_url() ); ?>"><?php esc_html_e( 'Disconnect this site', 'easy-digital-downloads' ); ?></a>
				<?php if ( $offer_revoke ) : ?>
					<a class="button edd-modal__revoke" href="<?php echo esc_url( self::get_revoke_url() ); ?>"><?php esc_html_e( 'Revoke for every site', 'easy-digital-downloads' ); ?></a>
				<?php endif; ?>
			</div>
		</dialog>
		<?php
	}

	/**
	 * Gets the URL that disconnects and revokes the authorization for every site using the account.
	 *
	 * Carries its own nonce, so the link that only removes this site's keys cannot trigger a revocation.
	 *
	 * @since 3.7.3
	 * @return string
	 */
	private static function get_revoke_url() {
		return wp_nonce_url(
			add_query_arg(
				'edds-revoke-disconnect',
				1,
				remove_query_arg( '_wpnonce', edds_stripe_connect_disconnect_url() )
			),
			'edds-stripe-connect-revoke'
		);
	}
}
