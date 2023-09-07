<?php
use EDD\Blocks\Checkout\Functions as CheckoutFunctions;
?>
<form id="edd_checkout_cart_form" class="edd-blocks-form edd-blocks-form__cart" method="post">
	<?php
	$cart_classes      = array(
		'edd-blocks-cart',
		'ajaxed',
	);
	$is_checkout_block = empty( $is_cart_widget ) && ( edd_is_checkout() || edd_doing_ajax() );
	?>
	<div id="edd_checkout_cart" class="<?php echo esc_attr( implode( ' ', $cart_classes ) ); ?>">
		<?php if ( $is_checkout_block ) : ?>
			<div class="edd-blocks-cart__row edd-blocks-cart__row-header edd_cart_header_row">
				<div class="edd_cart_item_name"><?php esc_html_e( 'Item Name', 'easy-digital-downloads' ); ?></div>
				<div class="edd_cart_item_price"><?php esc_html_e( 'Item Price', 'easy-digital-downloads' ); ?></div>
			</div>
			<?php
		endif;
		do_action( 'edd_cart_items_before' );
		if ( $cart_items ) {
			?>
			<div class="edd-blocks-cart__items">
			<?php
			foreach ( $cart_items as $key => $item ) {
				include 'cart-item.php';
			}
			?>
			</div>
			<?php
		}
		do_action( 'edd_cart_items_middle' );
		if ( edd_cart_has_fees() ) {
			include 'cart-fees.php';
		}
		CheckoutFunctions\do_cart_action( 'edd_cart_items_after' );

		if ( edd_use_taxes() && ! edd_prices_include_tax() ) {
			include 'cart-subtotal.php';
		}

		require 'cart-discounts.php';

		if ( edd_use_taxes() ) {
			include 'cart-taxes.php';
		}

		require 'cart-total.php';

		if ( has_action( 'edd_cart_footer_buttons' ) ) {
			include 'cart-footer-row.php';
		}
		?>
	</div>
</form>
