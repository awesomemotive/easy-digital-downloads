jQuery(document).ready(function($) {

    // update state/province field on checkout page
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

    // toggle the tax amount shown on checkout
    $('body').on('click', '#edd_tax_opt_in', function() {

        var tax         = parseFloat( $('.edd_cart_tax_amount').data( 'tax' ) );
        var subtotal    = parseFloat( $('.edd_cart_amount').data( 'subtotal' ) );
        var total       = parseFloat( $('.edd_cart_amount').data( 'total' ) );
        var sign        = edd_global_vars.currency_sign;
        var pos         = edd_global_vars.currency_pos;

        if( $(this).attr('checked') ) {

            $('.edd_cart_tax_row').show();

            if( pos == 'before' )
                total = sign + total;
            else
                total = total + sign;

            $('.edd_cart_amount').text( total );

        } else {

            $('.edd_cart_tax_row').hide();

            if( pos == 'before' )
                subtotal = sign + '' + subtotal;
            else
                subtotal = subtotal + '' + sign;

            $('.edd_cart_amount').text( subtotal );

        }
    });

    // make sure a gateway is selected
    $('body').on('submit', '#edd_payment_mode', function() {
        var gateway = $('#edd-gateway option:selected').val();
        if( gateway == 0 ) {
            alert( edd_global_vars.no_gateway );
            return false;
        }
    });

});
