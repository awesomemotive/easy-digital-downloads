window.EDD_Checkout = (function($) {
	'use strict';

	var $body,
		$form,
		$edd_cart_amount,
		before_discount,
		$checkout_form_wrap;

	function init() {
		$body = $(document.body);
		$form = $("#edd_purchase_form");
		$edd_cart_amount = $('.edd_cart_amount');
		before_discount = $edd_cart_amount.text();
		$checkout_form_wrap = $('#edd_checkout_form_wrap');

		// Update state/province field on checkout page
		$body.on('change', '#edd_cc_address input.card_state, #edd_cc_address select', update_state_field);

		$body.on('keyup change', '.edd-do-validate .card-number', function() {
			edd_validate_card( $(this) );
		});

		// Make sure a gateway is selected
		$body.on('submit', '#edd_payment_mode', function() {
			var gateway = $('#edd-gateway option:selected').val();
			if( gateway == 0 ) {
				alert( edd_global_vars.no_gateway );
				return false;
			}
		});

		// Add a class to the currently selected gateway on click
		$body.on('click', '#edd_payment_mode_select input', function() {
			$('#edd_payment_mode_select label.edd-gateway-option-selected').removeClass( 'edd-gateway-option-selected' );
			$('#edd_payment_mode_select input:checked').parent().addClass( 'edd-gateway-option-selected' );
		});

		// Validate and apply a discount
		$checkout_form_wrap.on('click', '.edd-apply-discount', apply_discount);

		// Prevent the checkout form from submitting when hitting Enter in the discount field
		$checkout_form_wrap.on('keypress', '#edd-discount', function (event) {
			if (event.keyCode == '13') {
				return false;
			}
		});

		// Apply the discount when hitting Enter in the discount field instead
		$checkout_form_wrap.on('keyup', '#edd-discount', function (event) {
			if (event.keyCode == '13') {
				$checkout_form_wrap.find('.edd-apply-discount').trigger('click');
			}
		});

		// Remove a discount
		$body.on('click', '.edd_discount_remove', remove_discount);

		// When discount link is clicked, hide the link, then show the discount input and set focus.
		$body.on('click', '.edd_discount_link', function(e) {
			e.preventDefault();
			$('.edd_discount_link').parent().hide();
			$('#edd-discount-code-wrap').show().find('#edd-discount').focus();
		});

		// Hide / show discount fields for browsers without javascript enabled
		$body.find('#edd-discount-code-wrap').hide();
		$body.find('#edd_show_discount').show();

		// Update the checkout when item quantities are updated
		$body.on('change', '.edd-item-quantity', update_item_quantities);

		$body.on('click', '.edd-amazon-logout #Logout', function(e) {
			e.preventDefault();
			amazon.Login.logout();
			window.location = edd_amazon.checkoutUri;
		});

	}

	function update_state_field() {

		var $this = $(this);
		if( 'card_state' != $this.attr('id') ) {

			// If the country field has changed, we need to update the state/province field
			var postData = {
				action: 'edd_get_shop_states',
				country: $this.val(),
				field_name: 'card_state'
			};

			$.ajax({
				type: "POST",
				data: postData,
				url: edd_global_vars.ajaxurl,
				xhrFields: {
					withCredentials: true
				},
				success: function (response) {
					if( 'nostates' == response ) {
						var text_field = '<input type="text" name="card_state" class="cart-state edd-input required" value=""/>';
						$form.find('input[name="card_state"], select[name="card_state"]').replaceWith( text_field );
					} else {
						$form.find('input[name="card_state"], select[name="card_state"]').replaceWith( response );
					}

					$body.trigger('edd_cart_billing_address_updated', [ response ]);

				}
			}).fail(function (data) {
				if ( window.console && window.console.log ) {
					console.log( data );
				}
			}).done(function (data) {
				recalculate_taxes();
			});
		} else {
			recalculate_taxes();
		}

		return false;
	}

	function recalculate_taxes(state) {

		if( '1' != edd_global_vars.taxes_enabled )
			return; // Taxes not enabled

		var $edd_cc_address = $('#edd_cc_address');

		if( ! state ) {
			state = $edd_cc_address.find('#card_state').val();
		}

		var postData = {
			action: 'edd_recalculate_taxes',
			billing_country: $edd_cc_address.find('#billing_country').val(),
			state: state
		};

		$.ajax({
			type: "POST",
			data: postData,
			dataType: "json",
			url: edd_global_vars.ajaxurl,
			xhrFields: {
				withCredentials: true
			},
			success: function (tax_response) {
				$('#edd_checkout_cart_form').replaceWith(tax_response.html);
				$('.edd_cart_amount').html(tax_response.total);
				var tax_data = new Object();
				tax_data.postdata = postData;
				tax_data.response = tax_response;
				$body.trigger('edd_taxes_recalculated', [ tax_data ]);
			}
		}).fail(function (data) {
			if ( window.console && window.console.log ) {
				console.log( data );
				$body.trigger('edd_taxes_recalculated', [ tax_data ]);
			}
		});
	}

	function edd_validate_card(field) {
		var card_field = field;
		card_field.validateCreditCard(function(result) {
			var $card_type = $('.card-type');

			if(result.card_type == null) {
				$card_type.removeClass().addClass('off card-type');
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
	}

	function apply_discount(event) {

		event.preventDefault();

		var $this = $(this),
			discount_code = $('#edd-discount').val(),
			edd_discount_loader = $('#edd-discount-loader');

		if (discount_code == '' || discount_code == edd_global_vars.enter_discount ) {
			return false;
		}

		var postData = {
			action: 'edd_apply_discount',
			code: discount_code,
			form: $( '#edd_purchase_form' ).serialize()
		};

		$('#edd-discount-error-wrap').html('').hide();
		edd_discount_loader.show();

		$.ajax({
			type: "POST",
			data: postData,
			dataType: "json",
			url: edd_global_vars.ajaxurl,
			xhrFields: {
				withCredentials: true
			},
			success: function (discount_response) {
				if( discount_response ) {
					if (discount_response.msg == 'valid') {
						$('.edd_cart_discount').html(discount_response.html);
						$('.edd_cart_discount_row').show();
						$('.edd_cart_amount').each(function() {
							$(this).text(discount_response.total);
						});
						$('#edd-discount', $checkout_form_wrap ).val('');

						recalculate_taxes();

						if( '0.00' == discount_response.total_plain ) {

							$('#edd_cc_fields,#edd_cc_address,#edd_payment_mode_select').slideUp();
							$('input[name="edd-gateway"]').val( 'manual' );

						} else {

							$('#edd_cc_fields,#edd_cc_address').slideDown();

						}

						$body.trigger('edd_discount_applied', [ discount_response ]);

					} else {
						$('#edd-discount-error-wrap').html( '<span class="edd_error">' + discount_response.msg + '</span>' );
						$('#edd-discount-error-wrap').show();
						$body.trigger('edd_discount_invalid', [ discount_response ]);
					}
				} else {
					if ( window.console && window.console.log ) {
						console.log( discount_response );
					}
					$body.trigger('edd_discount_failed', [ discount_response ]);
				}
				edd_discount_loader.hide();
			}
		}).fail(function (data) {
			if ( window.console && window.console.log ) {
				console.log( data );
			}
		});

		return false;
	};

	function remove_discount(event) {

		var $this = $(this), postData = {
			action: 'edd_remove_discount',
			code: $this.data('code')
		};

		$.ajax({
			type: "POST",
			data: postData,
			dataType: "json",
			url: edd_global_vars.ajaxurl,
			xhrFields: {
				withCredentials: true
			},
			success: function (discount_response) {

				$('.edd_cart_amount').each(function() {
					if( edd_global_vars.currency_sign + '0.00' == $(this).text() || '0.00' + edd_global_vars.currency_sign == $(this).text() ) {
						// We're removing a 100% discount code so we need to force the payment gateway to reload
						window.location.reload();
					}
					$(this).text(discount_response.total);
				});

				$('.edd_cart_discount').html(discount_response.html);

				if( ! discount_response.discounts ) {

					$('.edd_cart_discount_row').hide();

				}

				recalculate_taxes();

				$('#edd_cc_fields,#edd_cc_address').slideDown();

				$body.trigger('edd_discount_removed', [ discount_response ]);

			}
		}).fail(function (data) {
			if ( window.console && window.console.log ) {
				console.log( data );
			}
		});

		return false;
	}

	function update_item_quantities(event) {

		var $this = $(this),
			quantity = $this.val(),
			key = $this.data('key'),
			download_id = $this.closest('.edd_cart_item').data('download-id'),
			options = $this.parent().find('input[name="edd-cart-download-' + key + '-options"]').val();

		var postData = {
			action: 'edd_update_quantity',
			quantity: quantity,
			download_id: download_id,
			options: options
		};

		//edd_discount_loader.show();

		$.ajax({
			type: "POST",
			data: postData,
			dataType: "json",
			url: edd_global_vars.ajaxurl,
			xhrFields: {
				withCredentials: true
			},
			success: function (response) {

				$('.edd_cart_subtotal_amount').each(function() {
					$(this).text(response.subtotal);
				});

				$('.edd_cart_tax_amount').each(function() {
					$(this).text(response.taxes);
				});

				$('.edd_cart_amount').each(function() {
					$(this).text(response.total);
					$body.trigger('edd_quantity_updated', [ response ]);
				});
			}
		}).fail(function (data) {
			if ( window.console && window.console.log ) {
				console.log( data );
			}
		});

		return false;
	}

	// Expose some functions or variables to window.EDD_Checkout object
	return {
		'init': init,
		'recalculate_taxes': recalculate_taxes
	}

})(window.jQuery);

// init on document.ready
window.jQuery(document).ready(EDD_Checkout.init);