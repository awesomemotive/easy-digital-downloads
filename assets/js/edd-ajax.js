var edd_scripts;
jQuery(document).ready(function ($) {

    // Hide unneeded elements. These are things that are required in case JS breaks or isn't present
    $('.edd-no-js').hide();

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
                    $( '#edd_purchase_' + id + ' .edd-add-to-cart' ).show();
                }

                $('span.edd-cart-quantity').each(function() {
                    var quantity = parseInt($(this).text(), 10) - 1;
                    $(this).text(quantity);
                });

                if(!$('.edd-cart-item').length) {
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

		var $this = $(this);

		var container = $this.closest('div');

       // Show the ajax loader
        $('.edd-cart-ajax', container).show();

        var form           = $this.parents('form').last()
		var download       = $this.data('download-id');
        var variable_price = $this.data('variable-price');
		var price_mode     = $this.data('price-mode');
		var item_price_ids = [];

		if( variable_price == 'yes' ) {
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
                    $(cart_item_response).insertBefore('.cart_item.edd_checkout');
                    $('.cart_item.edd_checkout').show();
                    $('.cart_item.empty').remove();
                } else {

                    $(cart_item_response).insertBefore('.cart_item.edd_checkout');
                }

                // Update the cart quantity
                $('span.edd-cart-quantity').each(function() {
                    var quantity = parseInt($(this).text(), 10) + 1;
                    $(this).text(quantity);
                });

                // Hide the ajax loader
                $('.edd-cart-ajax', container).hide();

                if( variable_price == 'no' || price_mode != 'multi' ) {
                    // Switch purchase to checkout if a single price item or variable priced with radio buttons
                    $('.edd_go_to_checkout, .edd-add-to-cart', container).toggle();
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

    // Load the fields for the selected payment method -- Not used as of 1.3.2 but still here just in case. See $('select#edd-gateway').change() below
    $('#edd_payment_mode').submit(function (e) {
        if ($('select#edd-gateway').length) {
            var payment_mode = $('option:selected', '#edd-gateway').val();
        } else {
            var payment_mode = $('#edd-gateway').val();
        }

        if( payment_mode == '0' )
            return false;

        var arg_separator = edd_scripts.permalinks == '1' ? '?' : '&';

        var form = $(this),
            action = form.attr("action") + arg_separator + 'payment-mode=' + payment_mode;

        // Show the ajax loader
        $('.edd-cart-ajax').show();
        $('#edd_purchase_form_wrap').html('<img src="' + edd_scripts.ajax_loader + '"/>');
        $('#edd_payment_mode').hide();
        $('#edd_purchase_form_wrap').load(action + ' #edd_purchase_form');
        return false;
    });

    // Load the fields for the selected payment method
   $('select#edd-gateway').change( function (e) {
        if ($('select#edd-gateway').length) {
            var payment_mode = $('option:selected', '#edd-gateway').val();
        } else {
            var payment_mode = $('#edd-gateway').val();
        }

        if( payment_mode == '0' )
            return false;

        // Show the ajax loader
        $('.edd-cart-ajax').show();
        $('#edd_purchase_form_wrap').html('<img src="' + edd_scripts.ajax_loader + '"/>');

        $.post(edd_scripts.ajaxurl + '?payment-mode=' + payment_mode, { action: 'edd_load_gateway', edd_payment_mode: payment_mode },
            function(response){
                jQuery('#edd_purchase_form_wrap').html(response);
            }
        );

        return false;
    });


    $(document).on('click', '#edd_purchase_form input[type=submit]', function(e) {

        e.preventDefault();

        $.post(edd_global_vars.ajaxurl, $('#edd_purchase_form').serialize() + '&action=edd_process_checkout', function(data) {
            if ( data == 'success' ) {
                $('.edd_errors').remove();
                $('#edd_purchase_form').submit();
            } else {
                $('.edd_errors').remove();
                $('#edd_purchase_form').before(data);
            }
        });

    });

});
