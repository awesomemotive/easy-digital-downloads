var edd_scripts_validation;
jQuery(document).ready(function($) {
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
	return false;
});
