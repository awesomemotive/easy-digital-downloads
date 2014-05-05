var edd_scripts;
jQuery(document).ready(function ($) {

    // Hide unneeded elements. These are things that are required in case JS breaks or isn't present
    $('.edd-no-js').hide();
    $('a.edd-add-to-cart').addClass('edd-has-js');

    // Send Remove from Cart requests
    $('body').on('click.eddRemoveFromCart', '.edd-remove-from-cart', function (event) {
        var $this  = $(this),
            item   = $this.data('cart-item'),
            action = $this.data('action'),
            id     = $this.data('download-id'),
            data   = {
                action: action,
                cart_item: item
            };

         $.ajax({
            type: "POST",
            data: data,
            dataType: "json",
            url: edd_scripts.ajaxurl,
            success: function (response) {
            	if (response.removed) {
	                if ( parseInt( edd_scripts.position_in_cart, 10 ) === parseInt( item, 10 ) ) {
	                    window.location = window.location;
	                    return false;
	                }

	                // Remove the selected cart item
	                $('.edd-cart').find("[data-cart-item='" + item + "']").parent().remove();

	                // Check to see if the purchase form for this download is present on this page
	                if( $( '#edd_purchase_' + id ).length ) {
	                    $( '#edd_purchase_' + id + ' .edd_go_to_checkout' ).hide();
	                    $( '#edd_purchase_' + id + ' a.edd-add-to-cart' ).show().removeAttr('data-edd-loading');
	                }

	                $('span.edd-cart-quantity').each(function() {
	                    var quantity = parseInt( $(this).text(), 10 ) - 1;
	                    if( quantity < 1 ) {
	                    	quantity = 0;
	                    }
	                    $(this).text( quantity );
	                    $('body').trigger('edd_quantity_updated', [ quantity ]);
	                });

	                $('.cart_item.edd_subtotal span').html( response.subtotal );

	                if(!$('.edd-cart-item').length) {
	                    $('.cart_item.edd_subtotal,.edd-cart-number-of-items,.cart_item.edd_checkout').hide();
	                    $('.edd-cart').append('<li class="cart_item empty">' + edd_scripts.empty_cart_message + '</li>');
	                }

                    $('body').trigger('edd_cart_item_removed', [ response ]);
	            }
	        }
        }).fail(function (response) {
            if ( window.console && window.console.log ) {
                console.log( response );
            }
        }).done(function (response) {

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

        var spinnerWidth  = $spinner.width(),
            spinnerHeight = $spinner.height();

        // Show the spinner
        $this.attr('data-edd-loading', '');

        $spinner.css({
            'margin-left': spinnerWidth / -2,
            'margin-top' : spinnerHeight / -2
        });

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

        var action = $this.data('action');
        var data   = {
            action: action,
            download_id: download,
            price_ids : item_price_ids,
            post_data: $(form).serialize()
        };

        $.ajax({
            type: "POST",
            data: data,
            dataType: "json",
            url: edd_scripts.ajaxurl,
            success: function (response) {

            	if( edd_scripts.redirect_to_checkout == '1' ) {

	                window.location = edd_scripts.checkout_page;

	            } else {

	                // Add the new item to the cart widget
	                if ($('.cart_item.empty').length) {
	                    $(response.cart_item).insertBefore('.cart_item.edd_subtotal');
	                    $('.cart_item.edd_checkout,.cart_item.edd_subtotal').show();
	                    $('.cart_item.empty').remove();
	                } else {
	                    $(response.cart_item).insertBefore('.cart_item.edd_subtotal');
	                }

	                 $('.cart_item.edd_subtotal span').html( response.subtotal );

	                // Update the cart quantity
	                $('span.edd-cart-quantity').each(function() {
	                    var quantity = parseInt($(this).text(), 10) + 1;
	                    $(this).text(quantity);
	                    $('body').trigger('edd_quantity_updated', [ quantity ]);
	                });

	                // Show the "number of items in cart" message
	                if ( $('.edd-cart-number-of-items').css('display') == 'none') {
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
	                
	                // Update all buttons for same download
					if( $( '.edd_download_purchase_form' ).length ) {
						var parent_form = $('.edd_download_purchase_form *[data-download-id="' + download + '"]').parents('form');
						$( 'a.edd-add-to-cart', parent_form ).hide();
	                	$( '.edd_go_to_checkout', parent_form ).show().removeAttr( 'data-edd-loading' );
	               	}

	                if( response != 'incart' ) {
	                    // Show the added message
	                    $('.edd-cart-added-alert', container).fadeIn();
	                    setTimeout(function () {
	                        $('.edd-cart-added-alert', container).fadeOut();
	                    }, 3000);
	                }

                    $('body').trigger('edd_cart_item_added', [ response ]);

	            }
	        }
        }).fail(function (response) {
            if ( window.console && window.console.log ) {
                console.log( response );
            }
        }).done(function (response) {

        });

        return false;
    });

    // Show the login form on the checkout page
    $('#edd_checkout_form_wrap').on('click', '.edd_checkout_register_login', function () {
        var $this = $(this),
            data = {
                action: $this.data('action')
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

    // Process the login form via ajax
    $(document).on('click', '#edd_purchase_form #edd_login_fields input[type=submit]', function(e) {

        e.preventDefault();

        var complete_purchase_val = $(this).val();

        $(this).val(edd_global_vars.purchase_loading);

        $(this).after('<span class="edd-cart-ajax"><i class="edd-icon-spinner edd-icon-spin"></i></span>');

        var data = {
            action : 'edd_process_checkout_login',
            edd_ajax : 1,
            edd_user_login : $('#edd_login_fields #edd_user_login').val(),
            edd_user_pass : $('#edd_login_fields #edd_user_pass').val()
        };

        $.post(edd_global_vars.ajaxurl, data, function(data) {

            if ( $.trim(data) == 'success' ) {
                $('.edd_errors').remove();
                window.location = edd_scripts.checkout_page;
            } else {
                $('#edd_login_fields input[type=submit]').val(complete_purchase_val);
                $('.edd-cart-ajax').remove();
                $('.edd_errors').remove();
                $('#edd-user-login-submit').before(data);
            }
        });

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

    $(document).on('click', '#edd_purchase_form #edd_purchase_submit input[type=submit]', function(e) {

        e.preventDefault();

        var complete_purchase_val = $(this).val();

        $(this).val(edd_global_vars.purchase_loading);

        $(this).after('<span class="edd-cart-ajax"><i class="edd-icon-spinner edd-icon-spin"></i></span>');

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
            jQuery('.edd-no-js').hide();
        }
    );

}
