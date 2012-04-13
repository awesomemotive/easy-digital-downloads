jQuery(document).ready(function($) {
	$("body").on('click', '#edd-purchase-button', function() {
		$('#edd_purchase_form').validate({
			errorPlacement: function(error, element) {},
			rules: {
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
});