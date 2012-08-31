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
            $('input.card-state-other').css('display', 'none');
            $('input.card-state-us').css('display', '');
            $('input.card-state-ca').css('display', 'none');
        } else if( $('select[name=billing_country]').val() == 'CA') {
            $('input.card-state-other').css('display', 'none');
            $('input.card-state-us').css('display', 'none');
            $('input.card-state-ca').css('display', '');
        } else {
            $('input.card-state-other').css('display', '');
            $('input.card-state-us').css('display', 'none');
            $('input.card-state-ca').css('display', 'none');
        }
    });
});
