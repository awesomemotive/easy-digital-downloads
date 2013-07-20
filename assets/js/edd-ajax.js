var edd_scripts;
jQuery(document).ready(function ($) {

    // Hide unneeded elements. These are things that are required in case JS breaks or isn't present
    $('.edd-no-js').hide();
    $('a.edd-add-to-cart').addClass('edd-has-js');

    // Send Remove from Cart requests
    $('body').on('click.eddRemoveFromCart', '.edd-remove-from-cart', function (event) {
        var $this = $(this),
            item = $this.data('cart-item'),
            action = $this.data('action'),
            id = $this.data('download-id'),
            data = {
                action: action,
                cart_item: item,
                nonce: edd_scripts.ajax_nonce
            };

        $.post(edd_scripts.ajaxurl, data, function (response) {
            if (response == 'removed') {
                if ( parseInt(edd_scripts.position_in_cart,10) === parseInt(item,10) ) {
                    window.location = window.location;
                    return false;
                }
                $this.parent().remove();

                // Check to see if the purchase form for this download is present on this page
                if( $( '#edd_purchase_' + id ).length ) {
                    $( '#edd_purchase_' + id + ' .edd_go_to_checkout' ).hide();
                    $( '#edd_purchase_' + id + ' a.edd-add-to-cart' ).show();
                }

                $('span.edd-cart-quantity').each(function() {
                    var quantity = parseInt($(this).text(), 10) - 1;
                    $(this).text(quantity);
                });

                data = {
    				action: 'edd_get_subtotal',
					nonce: edd_scripts.ajax_nonce
				};
				//Update subtotal
				$.post(edd_scripts.ajaxurl, data, function (response) {
					$('.cart_item.edd_subtotal span').html( response );

				});

                if(!$('.edd-cart-item').length) {
                    $('.cart_item.edd_subtotal').hide();
                    $('.edd-cart-number-of-items').hide();
                    $('.cart_item.edd_checkout').hide();
                    $('.edd-cart').append('<li class="cart_item empty">' + edd_scripts.empty_cart_message + '</li>');
                } else {

                }
            }
        });
        return false;
    });

    // Send Add to Cart request
    $('body').on('click.eddAddToCart', '.edd-add-to-cart', function (e) {

        e.preventDefault();

        var $this = $(this), form = $this.closest('form');

        if( 'straight_to_gateway' == form.find('.edd_action_input').val() ) {
            form.submit();
            return true; // Submit the form
        }

        var $spinner = $this.find('.edd-loading');
        var container = $this.closest('div');

        var spinnerWidth  = $spinner.width();
            spinnerHeight = $spinner.height();

        // Show the spinner
        $this.attr('data-edd-loading', '');

        $spinner.css({
            'margin-left': spinnerWidth / -2,
            'margin-top' : spinnerHeight / -2
        });

        console.log(spinnerHeight);

        var form           = $this.parents('form').last();
        var download       = $this.data('download-id');
        var variable_price = $this.data('variable-price');
        var price_mode     = $this.data('price-mode');
        var item_price_ids = [];

        if( variable_price == 'yes' ) {

            if( ! $('.edd_price_option_' + download + ':checked', form).length ) {
                 // hide the spinner
                $this.removeAttr( 'data-edd-loading' );
                alert( edd_scripts.select_option );
                return;
            }

            $('.edd_price_option_' + download + ':checked', form).each(function( index ) {
                item_price_ids[ index ] = $(this).val();
            });

        } else {
            item_price_ids[0] = download;
        }


        var action = $this.data('action'),
            data = {
                action: action,
                download_id: download,
                price_ids : item_price_ids,
                nonce: edd_scripts.ajax_nonce,
                post_data: $(form).serialize()
            };

        $.post(edd_scripts.ajaxurl, data, function (cart_item_response) {

            if( edd_scripts.redirect_to_checkout == '1' ) {

                window.location = edd_scripts.checkout_page;

            } else {

                // Add the new item to the cart widget
                if ($('.cart_item.empty').length) {
                    $(cart_item_response).insertBefore('.cart_item.edd_subtotal');
                    $('.cart_item.edd_checkout').show();
                    $('.cart_item.edd_subtotal').show();
                    $('.cart_item.empty').remove();
                } else {

                    $(cart_item_response).insertBefore('.cart_item.edd_subtotal');
                }

                 $('.cart_item.edd_subtotal span').html( $('.temp-subtotal').text() );
                 $('.temp-subtotal').remove();

                // Update the cart quantity
                $('span.edd-cart-quantity').each(function() {
                    var quantity = parseInt($(this).text(), 10) + 1;
                    $(this).text(quantity);
                });

                // Show the "number of items in cart" message
                if ( $('.edd-cart-number-of-items').css('display') == 'none'){
                    $('.edd-cart-number-of-items').show('slow');
                }

                if( variable_price == 'no' || price_mode != 'multi' ) {
                    // Switch purchase to checkout if a single price item or variable priced with radio buttons
                    $('a.edd-add-to-cart', container).toggle();
                    $('.edd_go_to_checkout', container).css('display', 'inline-block');
                }

                if ( price_mode == 'multi' ) {
                    // remove spinner for multi
                    $this.removeAttr( 'data-edd-loading' );
                }

                if( cart_item_response != 'incart' ) {
                    // Show the added message
                    $('.edd-cart-added-alert', container).fadeIn();
                    setTimeout(function () {
                        $('.edd-cart-added-alert', container).fadeOut();
                    }, 3000);
                }
            }

        });
        return false;
    });

    // Show the login form on the checkout page
    $('#edd_checkout_form_wrap').on('click', '.edd_checkout_register_login', function () {
        var $this = $(this),
            action = $this.data('action'),
            data = {
                action: action,
                nonce: edd_scripts.ajax_nonce
            };
        // Show the ajax loader
        $('.edd-cart-ajax').show();

        $.post(edd_scripts.ajaxurl, data, function (checkout_response) {
            $('#edd_checkout_login_register').html(edd_scripts.loading);
            $('#edd_checkout_login_register').html(checkout_response);
            // Hide the ajax loader
            $('.edd-cart-ajax').hide();
        });
        return false;
    });

    // Load the fields for the selected payment method
   $('select#edd-gateway, input.edd-gateway').change( function (e) {

        var payment_mode = $('#edd-gateway option:selected, input.edd-gateway:checked').val();

        if( payment_mode == '0' )
            return false;

        edd_load_gateway( payment_mode );

        return false;
    });

    // Auto load first payment gateway
    if( edd_scripts.is_checkout == '1' && $('select#edd-gateway, input.edd-gateway').length ) {
        setTimeout( function() {
            edd_load_gateway( edd_scripts.default_gateway );
        }, 200);
    }

    $(document).on('click', '#edd_purchase_form input[type=submit]', function(e) {

        e.preventDefault();

        var complete_purchase_val = $(this).val();

        $(this).val(edd_global_vars.purchase_loading);

        $(this).after('<img src="' + edd_scripts.ajax_loader + '" class="edd-cart-ajax" />');

        $.post(edd_global_vars.ajaxurl, $('#edd_purchase_form').serialize() + '&action=edd_process_checkout&edd_ajax=true', function(data) {
            if ( $.trim(data) == 'success' ) {
                $('.edd_errors').remove();
                $('#edd_purchase_form').submit();
            } else {
                $('#edd_purchase_form input[type=submit]').val(complete_purchase_val);
                $('.edd-cart-ajax').remove();
                $('.edd_errors').remove();
                $('#edd_purchase_submit').before(data);
            }
        });

    });

});

function edd_load_gateway( payment_mode ) {

    // Show the ajax loader
    jQuery('.edd-cart-ajax').show();
    jQuery('#edd_purchase_form_wrap').html('<img src="' + edd_scripts.ajax_loader + '"/>');

    jQuery.post(edd_scripts.ajaxurl + '?payment-mode=' + payment_mode, { action: 'edd_load_gateway', edd_payment_mode: payment_mode },
        function(response){
            jQuery('#edd_purchase_form_wrap').html(response);
        }
    );

}
