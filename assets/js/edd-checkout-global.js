jQuery(document).ready(function($) {
    var $body = $('body'),
        $edd_cart_amount = $('.edd_cart_amount');

    // Update state/province field on checkout page
    $body.on('change', '#edd_cc_address input.card_state, #edd_cc_address select', function() {
        var $this = $(this);
        if( 'card_state' != $this.attr('id') ) {

            // If the country field has changed, we need to update the state/provice field
            var postData = {
                action: 'edd_get_shop_states',
                country: $this.val(),
                field_name: 'card_state'
            };

            $.ajax({
                type: "POST",
                data: postData,
                url: edd_global_vars.ajaxurl,
                success: function (response) {
                    if( 'nostates' == response ) {
                        var text_field = '<input type="text" name="card_state" class="cart-state edd-input required" value=""/>';
                        $this.parent().next().find('input,select').replaceWith( text_field );
                    } else {
                        $this.parent().next().find('input,select').replaceWith( response );
                    }
                }
            }).fail(function (data) {
                console.log(data);
            }).done(function (data) {
                recalculate_taxes();
            });
        } else {
            recalculate_taxes();
        }

        return false;
    });

    function recalculate_taxes( state ) {
        if( '1' != edd_global_vars.taxes_enabled )
            return; // Taxes not enabled

        var $edd_cc_address = $('#edd_cc_address');

        if( ! state ) {
            state = $edd_cc_address.find('#card_state').val();
        }

        var postData = {
            action: 'edd_recalculate_taxes',
            nonce: edd_global_vars.checkout_nonce,
            country: $edd_cc_address.find('#billing_country').val(),
            state: state
        };

        $.ajax({
            type: "POST",
            data: postData,
            dataType: "json",
            url: edd_global_vars.ajaxurl,
            success: function (tax_response) {
                $('#edd_checkout_cart').replaceWith(tax_response.html);
                $('.edd_cart_amount').html(tax_response.total);
            }
        }).fail(function (data) {
            console.log(data);
        });
    }

    /* Credit card verification */

    $body.on('focusout', '.edd-do-validate .card-number', function() {
        var card_field = $(this);
        card_field.validateCreditCard(function(result) {
            var $card_type = $('.card-type');

            if(result.card_type == null) {
                $card_type.addClass('off');
                card_field.removeClass('valid');
                card_field.addClass('error');
            } else {
                $card_type.removeClass('off');
                $card_type.addClass( result.card_type.name );
                if (result.length_valid && result.luhn_valid) {
                    card_field.addClass('valid');
                    card_field.removeClass('error');
                } else {
                    card_field.removeClass('valid');
                    card_field.addClass('error');
                }
            }
        });
    });

    // Make sure a gateway is selected
    $body.on('submit', '#edd_payment_mode', function() {
        var gateway = $('#edd-gateway option:selected').val();
        if( gateway == 0 ) {
            alert( edd_global_vars.no_gateway );
            return false;
        }
    });

    /* Discounts */
    var before_discount = $edd_cart_amount.text(),
        $checkout_form_wrap = $('#edd_checkout_form_wrap');

    // Validate and apply a discount
    $checkout_form_wrap.on('focusout', '#edd-discount', function (event) {

        var $this = $(this),
            discount_code = $this.val(),
            edd_discount_loader = $('#edd-discount-loader');

        if (discount_code == '' || discount_code == edd_global_vars.enter_discount ) {
            return false;
        }

        var postData = {
            action: 'edd_apply_discount',
            code: discount_code,
            nonce: edd_global_vars.checkout_nonce
        };

        edd_discount_loader.show();

        $.ajax({
            type: "POST",
            data: postData,
            dataType: "json",
            url: edd_global_vars.ajaxurl,
            success: function (discount_response) {
                if( discount_response ) {
                    if (discount_response.msg == 'valid') {
                        $('.edd_cart_discount').html(discount_response.html);
                        $('.edd_cart_discount_row').show();
                        $('.edd_cart_amount').each(function() {
                            $(this).text(discount_response.total);
                        });
                        $('#edd-discount', $checkout_form_wrap ).val('');
                    } else {
                        alert(discount_response.msg);
                    }
                } else {
                    console.log( discount_response );
                }
                edd_discount_loader.hide();
            }
        }).fail(function (data) {
            console.log(data);
        });

        return false;
    });

    // Remove a discount
    $body.on('click', '.edd_discount_remove', function (event) {

        var $this = $(this), postData = {
            action: 'edd_remove_discount',
            code: $this.data('code')
        };

        $.ajax({
            type: "POST",
            data: postData,
            dataType: "json",
            url: edd_global_vars.ajaxurl,
            success: function (discount_response) {
                $('.edd_cart_discount').html(discount_response.html);
                if( ! discount_response.discounts ) {
                   $('.edd_cart_discount_row').hide();
                }
                $('.edd_cart_amount').each(function() {
                    $(this).text(discount_response.total);
                });
            }
        }).fail(function (data) {
            console.log(data);
        });

        return false;
    });

    $body.on('click', '.edd_discount_link', function(e) {
        e.preventDefault();
        $('.edd_discount_link').parent().hide();
        $('#edd-discount-code-wrap').show();
    });

    // Hide / show discount fields for browsers without javascript enabled
    $body.find('#edd-discount-code-wrap').hide();
    $body.find('#edd_show_discount').show();

});