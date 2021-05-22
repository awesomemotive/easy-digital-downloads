<li class="cart_item empty"><?php echo edd_empty_cart_message(); ?></li>
<?php if ( edd_use_taxes() ) : ?>
<li class="cart_item edd-cart-meta edd_subtotal" style="display:none;"><?php echo __( 'Subtotal:', 'easy-digital-downloads' ). " <span class='subtotal'>" . esc_html( \EDD\Utils\Currency::display( edd_get_cart_subtotal(), edd_get_currency() ) ); ?></span></li>
<li class="cart_item edd-cart-meta edd_cart_tax" style="display:none;"><?php _e( 'Estimated Tax:', 'easy-digital-downloads' ); ?> <span class="cart-tax"><?php echo esc_html( \EDD\Utils\Currency::display( edd_get_cart_tax(), edd_get_currency() ) ); ?></span></li>
<?php endif; ?>
<li class="cart_item edd-cart-meta edd_total" style="display:none;"><?php _e( 'Total:', 'easy-digital-downloads' ); ?> <span class="cart-total"><?php echo esc_html( \EDD\Utils\Currency::display( edd_get_cart_total(), edd_get_currency() ) ); ?></span></li>
<li class="cart_item edd_checkout" style="display:none;"><a href="<?php echo edd_get_checkout_uri(); ?>"><?php _e( 'Checkout', 'easy-digital-downloads' ); ?></a></li>
