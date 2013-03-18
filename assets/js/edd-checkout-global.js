jQuery(document).ready(function($) {

    // Update state/province field on checkout page
    $( 'body').change( 'select[name=billing_country]', function() {
        if( $('select[name=billing_country]').val() == 'US') {
            $('#card_state_other').css('display', 'none');
            $('#card_state_us').css('display', '');
            $('#card_state_ca').css('display', 'none');
        } else if( $('select[name=billing_country]').val() == 'CA') {
            $('#card_state_other').css('display', 'none');
            $('#card_state_us').css('display', 'none');
            $('#card_state_ca').css('display', '');
        } else {
            $('#card_state_other').css('display', '');
            $('#card_state_us').css('display', 'none');
            $('#card_state_ca').css('display', 'none');
        }
    });

    /* Credit card verification */

    $('body').on('focusout', '.edd-do-validate .card-number', function() {
        var card_field = $(this);
        card_field.validateCreditCard(function(result) {
            if(result.card_type == null) {
                $('.card-type').addClass('off');
                card_field.removeClass('valid');
                card_field.addClass('error');
            } else {
                $('.card-type').removeClass('off');
                $('.card-type').addClass( result.card_type.name );
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

    // Toggle the tax amount shown on checkout
    $('body').on('click', '#edd_tax_opt_in', function() {

        var postData = {
            action: $(this).attr('checked') ? 'edd_local_tax_opt_in' : 'edd_local_tax_opt_out',
            nonce: edd_global_vars.checkout_nonce
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
    });

    // Make sure a gateway is selected
    $('body').on('submit', '#edd_payment_mode', function() {
        var gateway = $('#edd-gateway option:selected').val();
        if( gateway == 0 ) {
            alert( edd_global_vars.no_gateway );
            return false;
        }
    });

    /* Discounts */
    var before_discount = $('.edd_cart_amount').text();
    $('#edd_checkout_form_wrap').on('change', '#edd-email', function (event) {
        $('.edd_cart_amount').html(before_discount);
        $('#edd-discount').val('');
    });

    // Validate and apply a discount
    $('#edd_checkout_form_wrap').on('focusout', '#edd-discount', function (event) {

        var $this = $(this),
            discount_code = $('#edd-discount').val(),
            edd_email = $('#edd-email').val();
            edd_user = $('#edd_user_login').val();

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

        $('#edd-discount-loader').show();

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
                        $('.edd_cart_amount').text(discount_response.total);
                    } else {
                        alert(discount_response.msg);
                    }
                } else {
                    console.log( discount_response );
                }
                $('#edd-discount-loader').hide();
            }
        }).fail(function (data) {
            console.log(data);
        });

        return false;
    });

});
