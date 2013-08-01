<li class="cart_item empty"><?php echo edd_empty_cart_message(); ?></li>
<li class="cart_item edd_subtotal" style="display:none;"><?php echo __( 'Subtotal:', 'edd' ). " <span class='subtotal'>" . edd_currency_filter( edd_get_cart_amount( false ) ); ?></span></li>
<li class="cart_item edd_checkout" style="display:none;"><a href="<?php echo edd_get_checkout_uri(); ?>"><?php _e( 'Checkout', 'edd' ); ?></a></li>
