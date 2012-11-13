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
});
