/**
 * Export screen JS
 */
var EDD_Export = {

	init : function() {
		this.submit();
	},

	submit : function() {
		var self = this;

		$( document.body ).on( 'submit', '.edd-export-form', function(e) {
			e.preventDefault();

			var form         = $( this ),
				submitButton = form.find( 'input[type="submit"]' ).first();

			if ( submitButton.hasClass( 'button-disabled' ) || submitButton.is( ':disabled' ) ) {
				return;
			}

			var data = form.serialize();

			submitButton.addClass( 'button-disabled' );
			form.find('.notice-wrap').remove();
			form.append( '<div class="notice-wrap"><span class="spinner is-active"></span><div class="edd-progress"><div></div></div></div>' );

			// start the process
			self.process_step( 1, data, self );
		});
	},

	process_step : function( step, data, self ) {

		$.ajax({
			type: 'POST',
			url: ajaxurl,
			data: {
				form: data,
				action: 'edd_do_ajax_export',
				step: step
			},
			dataType: "json",
			success: function( response ) {
				if ( 'done' === response.step || response.error || response.success ) {

					// We need to get the actual in progress form, not all forms on the page
					var export_form    = $('.edd-export-form').find('.edd-progress').parent().parent();
					var notice_wrap    = export_form.find('.notice-wrap');

					export_form.find('.button-disabled').removeClass('button-disabled');

					if ( response.error ) {
						var error_message = response.message;
						notice_wrap.html('<div class="updated error"><p>' + error_message + '</p></div>');

					} else if ( response.success ) {
						var success_message = response.message;
						notice_wrap.html('<div id="edd-batch-success" class="updated notice"><p>' + success_message + '</p></div>');

					} else {
						notice_wrap.remove();
						window.location = response.url;
					}

				} else {
					$('.edd-progress div').animate({
						width: response.percentage + '%'
					}, 50, function() {
						// Animation complete.
					});
					self.process_step( parseInt( response.step ), data, self );
				}

			}
		}).fail(function (response) {
			if ( window.console && window.console.log ) {
				console.log( response );
			}
		});
	}
};

export default EDD_Export;
