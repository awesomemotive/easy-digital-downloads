jQuery(document).ready(function($) {
    var $body = $('body'),
        $edd_cart_amount = $('.edd_cart_amount');

    // Update state/province field on checkout page
    $body.on('change', '#edd_cc_address select', function() {
        var $billing_country = $('select[name=billing_country]');

        if( $billing_country.val() == 'US') {
            $('#card_state_other, #card_state_ca').css('display', 'none');
            $('#card_state_us').css('display', '');
        } else if( $billing_country.val() == 'CA') {
            $('#card_state_other, #card_state_us').css('display', 'none');
            $('#card_state_ca').css('display', '');
        } else {
            $('#card_state_us, #card_state_ca').css('display', 'none');
            $('#card_state_other').css('display', '');
        }
        recalculate_taxes();
    });

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

    function recalculate_taxes() {
        if( '1' != edd_global_vars.taxes_enabled )
            return; // Taxes not enabled

        var $edd_cc_address = $('#edd_cc_address');

        var postData = {
            action: 'edd_recalculate_taxes',
            nonce: edd_global_vars.checkout_nonce,
            country: $edd_cc_address.find('.billing-country').val(),
            state: $edd_cc_address.find('.card-state:visible').val()
        };

        $.ajax({
            type: "POST",
            data: postData,
            dataType: "json",
            url: edd_global_vars.ajaxurl,
            success: function (tax_response) {
                $('#edd_checkout_cart').replaceWith(tax_response.html);
                $edd_cart_amount.html(tax_response.total);
            }
        }).fail(function (data) {
            console.log(data);
        });
    }

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

    $checkout_form_wrap.on('change', '#edd-email', function (event) {
        $('#edd-discount').val('');
    });

    // Validate and apply a discount
    $checkout_form_wrap.on('focusout', '#edd-discount', function (event) {

        var $this = $(this),
            discount_code = $this.val(),
            edd_email = $('#edd-email').val();
            edd_user = $('#edd_user_login').val(),
            edd_discount_loader = $('#edd-discount-loader');

        if (discount_code == '') {
            return false;
        }

        if (edd_email == '' && edd_email != 'undefined') {
            alert(edd_global_vars.no_email);
            return false;
        }

        if(edd_email == 'undefined' && edd_user == '') {
            alert(edd_global_vars.no_username);
            return false;
        }

        var postData = {
            action: 'edd_apply_discount',
            code: discount_code,
            email: edd_email,
            user: edd_user,
            nonce: edd_global_vars.checkout_nonce
        };

        $edd_discount_loader.show();

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
                        $edd_cart_amount.text(discount_response.total);
                    } else {
                        alert(discount_response.msg);
                    }
                } else {
                    console.log( discount_response );
                }
                $edd_discount_loader.hide();
            }
        }).fail(function (data) {
            console.log(data);
        });

        return false;
    });

});