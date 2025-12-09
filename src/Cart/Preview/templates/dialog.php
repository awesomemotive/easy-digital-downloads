<?php
/**
 * Slideout Cart Dialog Template
 *
 * This template is rendered as a hidden HTML <template> element
 * and cloned into Shadow DOM by JavaScript.
 *
 * @package EDD\SlideoutCart
 * @since   3.6.2
 */

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

?>

<!-- Main Dialog Template for Shadow DOM -->
<template id="edd-cart-preview-dialog-template">
	<dialog class="cart-preview__dialog" part="dialog" aria-labelledby="cart-preview-title">
		<!-- Header -->
		<header class="cart-preview__header" part="header">
			<h2 id="cart-preview-title" class="cart-preview__title" part="title">
				<?php esc_html_e( 'Your Cart', 'easy-digital-downloads' ); ?>
			</h2>
			<button class="cart-preview__close" part="close-button" data-edd-icon="x-mark" aria-label="<?php esc_attr_e( 'Close cart', 'easy-digital-downloads' ); ?>" type="button">
				<span class="screen-reader-text"><?php esc_attr_e( 'Close', 'easy-digital-downloads' ); ?></span>
			</button>
		</header>

		<div class="cart-preview__content" part="content">

			<!-- Loading State -->
			<output class="cart-preview__loading hidden">
				<div class="cart-preview__spinner"></div>
				<span class="screen-reader-text"><?php esc_html_e( 'Loading...', 'easy-digital-downloads' ); ?></span>
			</output>

			<!-- Error Messages -->
			<div class="cart-preview__error hidden" part="error" role="alert">
				<div class="cart-preview__error--message" part="error-message"></div>
				<button class="cart-preview__error--dismiss" part="error-dismiss" aria-label="<?php esc_attr_e( 'Dismiss error', 'easy-digital-downloads' ); ?>" type="button">&times;</button>
			</div>

			<!-- Empty State -->
			<div class="cart-preview__empty hidden" part="empty">
				<?php echo wp_kses_post( wpautop( edd_get_option( 'empty_cart_preview', __( 'Your cart is empty.', 'easy-digital-downloads' ) ) ) ); ?>
				<?php
				/**
				 * Hook for empty cart content.
				 *
				 * @since 3.6.2
				 */
				do_action( 'edd_cart_preview_empty' );
				?>
			</div>

			<!-- Items Container -->
			<div class="cart-preview__items hidden" part="items">
				<div class="cart-preview__items-list" part="items-list"></div>

				<?php do_action( 'edd_cart_preview_after_items' ); ?>

				<!-- Summary -->
				<div class="cart-preview__summary" part="summary">
					<?php
					/**
					 * Hook before cart summary.
					 *
					 * @since 3.6.2
					 */
					do_action( 'edd_cart_preview_before_summary' );
					?>

					<div class="cart-preview__summary-row" part="summary-row">
						<div class="cart-preview__summary-label" part="summary-label">
							<?php esc_html_e( 'Subtotal:', 'easy-digital-downloads' ); ?>
						</div>
						<div class="cart-preview__summary-value" part="summary-value"></div>
					</div>

					<?php
					if ( edd_use_taxes() && ! edd_prices_include_tax() ) {
						?>
						<p class="cart-preview__summary--taxes"><?php esc_html_e( 'Taxes will be calculated at checkout.', 'easy-digital-downloads' ); ?></p>
						<?php
					}

					/**
					 * Hook after cart summary.
					 *
					 * @since 3.6.2
					 */
					do_action( 'edd_cart_preview_after_summary' );
					?>
				</div>
			</div>
		</div>

		<!-- Scroll Indicator -->
		<div class="cart-preview__scroll-indicator hidden" part="scroll-indicator" aria-hidden="true">
			<span class="cart-preview__scroll-indicator-icon" data-edd-icon="down"></span>
		</div>

		<!-- Footer Actions -->
		<footer class="cart-preview__footer hidden" part="footer">
			<?php
			/**
			 * Hook before cart action buttons.
			 *
			 * @since 3.6.2
			 */
			do_action( 'edd_cart_preview_before_actions' );
			?>

			<button class="cart-preview__button cart-preview__button--secondary cart-preview__continue" part="button continue-button">
				<?php esc_html_e( 'Continue Shopping', 'easy-digital-downloads' ); ?>
			</button>
			<a href="<?php echo esc_url( edd_get_checkout_uri() ); ?>" class="cart-preview__button cart-preview__button--primary" part="button checkout-button">
				<?php esc_html_e( 'Checkout', 'easy-digital-downloads' ); ?>
			</a>

			<?php
			/**
			 * Hook after cart action buttons.
			 *
			 * @since 3.6.2
			 */
			do_action( 'edd_cart_preview_after_actions' );
			?>
		</footer>
		<!-- Screen reader announcements -->
		<div class="screen-reader-text" aria-live="assertive" aria-atomic="true" data-edd-cart-status></div>
	</dialog>
</template>

<!-- Individual Cart Item Template -->
<template id="edd-cart-preview-item-template">
	<div class="cart-preview__item" part="item" data-cart-key="">
		<img src="" alt="" class="cart-preview__item-image" part="item-image" style="display: none;">
		<div class="cart-preview__item-details" part="item-details">
			<h3 class="cart-preview__item-name" part="item-name"></h3>
			<div class="cart-preview__item-price" part="item-price"></div>
			<div class="cart-preview__item-quantity" part="item-quantity" style="display: none;">
				<div class="cart-preview__quantity-control" part="quantity-control">
					<button
						class="cart-preview__quantity-button cart-preview__quantity-button--minus"
						part="quantity-button quantity-decrease"
						data-edd-icon="minus"
						aria-label="<?php esc_attr_e( 'Decrease quantity', 'easy-digital-downloads' ); ?>"
						type="button"
					>
						<span class="screen-reader-text"><?php esc_attr_e( 'Decrease', 'easy-digital-downloads' ); ?></span>
					</button>

					<div class="cart-preview__quantity-display" part="quantity-display"></div>

					<button
						class="cart-preview__quantity-button cart-preview__quantity-button--plus"
						part="quantity-button quantity-increase"
						data-edd-icon="plus"
						aria-label="<?php esc_attr_e( 'Increase quantity', 'easy-digital-downloads' ); ?>"
						type="button"
					>
						<span class="screen-reader-text"><?php esc_attr_e( 'Increase', 'easy-digital-downloads' ); ?></span>
					</button>
				</div>
			</div>
			<div class="cart-preview__item-fees" part="item-fees" style="display: none;">
				<div class="cart-preview__fees-list" part="fees-list"></div>
			</div>
		</div>
		<button
			class="cart-preview__item-remove"
			part="item-remove"
			data-edd-icon="trash"
			data-cart-key=""
			aria-label="<?php esc_attr_e( 'Remove item', 'easy-digital-downloads' ); ?>"
			<?php /* translators: %s is the item name */ ?>
			data-label-template="<?php esc_attr_e( 'Remove %s from cart', 'easy-digital-downloads' ); ?>"
			type="button"
		>
			<span class="screen-reader-text"><?php esc_attr_e( 'Remove', 'easy-digital-downloads' ); ?></span>
		</button>
	</div>
</template>

<?php
/**
 * Hook for additional cart preview templates.
 *
 * Extensions can use this to add their own HTML templates
 * that will be cloned by JavaScript.
 *
 * @since 3.6.2
 */
do_action( 'edd_cart_preview_templates' );
