<li class="cart_item edd-cart-meta edd_subtotal"><?php echo __( 'Subtotal:', 'edd' ). " <span class='subtotal'>" . edd_currency_filter( edd_format_amount( edd_get_cart_subtotal() ) ); ?></span></li>
<?php if ( edd_use_taxes() ) : ?>
<li class="cart_item edd-cart-meta edd_purchase_tax_rate"><?php _e( 'Tax:', 'edd' ); ?> <?php echo edd_currency_filter( edd_format_amount( edd_get_cart_tax() ) ); ?></li>
<?php endif; ?>
<li class="cart_item edd-cart-meta edd_total"><?php _e( 'Total:', 'edd' ); ?> <?php echo edd_currency_filter( edd_format_amount( edd_get_cart_total() ) ); ?></li>
