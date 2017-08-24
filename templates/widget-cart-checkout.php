<?php if ( edd_use_taxes() ) : ?>
<li class="cart_item edd-cart-meta edd_subtotal"><?php echo __( 'Subtotal:', 'easy-digital-downloads' ). " <span class='subtotal'>" . edd_currency_filter( edd_format_amount( edd_get_cart_subtotal() ) ); ?></span></li>
<li class="cart_item edd-cart-meta edd_cart_tax"><?php _e( 'Estimated Tax:', 'easy-digital-downloads' ); ?> <span class="cart-tax"><?php echo edd_currency_filter( edd_format_amount( edd_get_cart_tax() ) ); ?></span></li>
<?php endif; ?>
<li class="cart_item edd-cart-meta edd_total"><?php _e( 'Total:', 'easy-digital-downloads' ); ?> <span class="cart-total"><?php echo edd_currency_filter( edd_format_amount( edd_get_cart_total() ) ); ?></span></li>
<li class="cart_item edd_checkout"><a href="<?php echo edd_get_checkout_uri(); ?>"><?php _e( 'Checkout', 'easy-digital-downloads' ); ?></a></li>
