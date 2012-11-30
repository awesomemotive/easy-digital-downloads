var edd_scripts;
jQuery(document).ready(function ($) {

    // hide unneeded elements. These are things that are required in case JS breaks or isn't present
    $('.edd-no-js').hide();

    // send Remove from Cart requests
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
                
                // check to see if the purchase form for this download is present on this page
                if( $( '#edd_purchase_' + id ).length ) {
                    $( '#edd_purchase_' + id + ' .edd_go_to_checkout' ).hide();
                    $( '#edd_purchase_' + id + ' .edd-add-to-cart' ).show();
                }
                var quantity = $('span.edd-cart-quantity').text();
                quantity = parseInt(quantity, 10) - 1;
                $('span.edd-cart-quantity').text(quantity);
				if(!$('.edd-cart-item').length) {
                    $('.cart_item.edd_checkout').hide();
					$('.edd-cart').append('<li class="cart_item empty">' + edd_scripts.empty_cart_message + '</li>');
				} else {

                }
            }
        });
        return false;
    });

    // send Add to Cart request
    $('body').on('click.eddAddToCart', '.edd-add-to-cart', function (e) {
        
		e.preventDefault();
		
		var $this = $(this);
			
		var container = $this.closest('div');	
 
       // show the ajax loader
        $('.edd-cart-ajax', container).show();

		var download = $this.data('download-id');
		var variable_price = $this.data('variable-price');
		var item_price_id = false;
		if(typeof variable_price !== 'undefined' && variable_price !== false) {
			item_price_id = $('.edd_price_option_' + download + ':checked').val();
		}
		
        var action = $this.data('action'),
            data = {
                action: action,
                download_id: download,
				price_id : item_price_id,
                nonce: edd_scripts.ajax_nonce
            };

        $.post(edd_scripts.ajaxurl, data, function (cart_item_response) {
            // item already in the cart
			if(cart_item_response == 'incart') {
				alert(edd_scripts.already_in_cart_message);
				$('.edd-cart-ajax').hide();
				return;
			}
			
			// add the new item to the cart widget
			if ($('.cart_item.empty').length) {
                $(cart_item_response).insertBefore('.cart_item.edd_checkout');
                $('.cart_item.edd_checkout').show();
                $('.cart_item.empty').remove();
            } else {

                $(cart_item_response).insertBefore('.cart_item.edd_checkout');
            }
			
			// update the cart quantity
            var quantity = $('span.edd-cart-quantity').text();
            quantity = parseInt(quantity, 10) + 1;
            $('span.edd-cart-quantity').text(quantity);

            // hide the ajax loader
            $('.edd-cart-ajax', container).hide();
			
			// switch purchase to checkout
			$('.edd_go_to_checkout, .edd-add-to-cart', container).toggle();
				
			// show the added message
            $('.edd-cart-added-alert', container).fadeIn();
            setTimeout(function () {
                $('.edd-cart-added-alert', container).fadeOut();
            }, 3000);

        });
        return false;
    });

    // For tricksters
	var before_discount = $('.edd_cart_amount').text();	
	$('#edd_checkout_form_wrap').on('change', '#edd-email', function (event) {
		$('.edd_cart_amount').html(before_discount);
		$('#edd-discount').val('');
	});

	// validate and apply a discount
    $('#edd_checkout_form_wrap').on('click', '.edd-apply-discount', function (event) {
        
		var $this = $(this),
            discount_code = $('#edd-discount').val(),
            edd_email = $('#edd-email').val();
			edd_user = $('#edd_user_login').val();
        if (discount_code == '') {
            alert(edd_scripts.no_discount);
            return false;
        }

		if (edd_email == '' && edd_email != 'undefined') {
            alert(edd_scripts.no_email);
            return false;
        }

        if(edd_email == 'undefined' && edd_user == '') {
            alert(edd_scripts.no_username);
            return false;
        }

        var postData = {
            action: 'edd_apply_discount',
            code: discount_code,
            email: edd_email,
            user: edd_user,
            nonce: edd_scripts.ajax_nonce
        };

        $.ajax({
            type: "POST",
            data: postData,
            dataType: "json",
            url: edd_scripts.ajaxurl,
            success: function (discount_response) {
                if( discount_response ) {
                    if (discount_response.msg == 'valid') {
                        $('.edd_cart_amount').html(discount_response.amount).text();
                        $this.text(edd_scripts.discount_applied);
                    } else {
                        alert(discount_response.msg);
                    }
                } else {
                    console.log( discount_response );
                }
            }
        }).fail(function (data) {
            console.log(data);
        });
        return false;
    });

    // show the login form on the checkout page
    $('#edd_checkout_form_wrap').on('click', '.edd_checkout_register_login', function () {
        var $this = $(this),
            action = $this.data('action'),
            data = {
                action: action,
                nonce: edd_scripts.ajax_nonce
            };
        // show the ajax loader
        $('.edd-cart-ajax').show();

        $.post(edd_scripts.ajaxurl, data, function (checkout_response) {
            $('#edd_checkout_login_register').html(edd_scripts.loading);
            $('#edd_checkout_login_register').html(checkout_response);
            // hide the ajax loader
            $('.edd-cart-ajax').hide();
        });
        return false;
    });

    // load the fields for the selected payment method -- Not used as of 1.3.2 but still here just in case. See $('select#edd-gateway').change() below
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
            
        // show the ajax loader
        $('.edd-cart-ajax').show();
        $('#edd_purchase_form_wrap').html('<img src="' + edd_scripts.ajax_loader + '"/>');
        $('#edd_payment_mode').hide();
        $('#edd_purchase_form_wrap').load(action + ' #edd_purchase_form');
        return false;
    });

    // load the fields for the selected payment method
   $('select#edd-gateway').change( function (e) {
        if ($('select#edd-gateway').length) {
            var payment_mode = $('option:selected', '#edd-gateway').val();
        } else {
            var payment_mode = $('#edd-gateway').val();
        }

        if( payment_mode == '0' )
            return false;

        var arg_separator = edd_scripts.permalinks == '1' ? '?' : '&';

        var form = $(this).closest( 'form' ),
            action = form.attr("action") + arg_separator + 'payment-mode=' + payment_mode;
            
        // show the ajax loader
        $('.edd-cart-ajax').show();
        $('#edd_purchase_form_wrap').html('<img src="' + edd_scripts.ajax_loader + '"/>');
        $('#edd_purchase_form_wrap').load(action + ' #edd_purchase_form');
        return false;
    });

});