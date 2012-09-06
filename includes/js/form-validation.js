var edd_scripts_validation;
jQuery(document).ready(function($) {
	$("body").on('click', '#edd-purchase-button', function() {
		$('#edd_purchase_form').validate({
			errorPlacement: function(error, element) {},
			rules: {
				edd_first: {
				    required: edd_scripts_validation.firstname === '1' ? true : false,
				},
				edd_last: {
				    required: edd_scripts_validation.lastname === '1' ? true : false,
				},
				edd_email: {
					required: true,
					email: true
				}
			},
			submitHandler: function(form) {
				form.submit();
			}
		});
	});

    // update state/province field on checkout page
    $('select[name=billing_country]').change(function() {
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
    $('#card_state_other').change(function() {
        $('#card_state').val( $('#card_state_other').val() );
    });
    $('#card_state_us').change(function() {
		$('#card_state').val( $('#card_state_us').val() );
    });
    $('#card_state_ca').change(function() {
        $('#card_state').val( $('#card_state_ca').val() );
    });
});
