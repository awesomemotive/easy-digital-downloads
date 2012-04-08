jQuery(document).ready(function($) {
	
	// send Remove from Cart requests
	$('.edd-remove-from-cart').live('click', function(event) {
		var $this = $(this);
		var item = $this.data('cart-item');
		var action = $this.data('action');
		var data = {
			action: action,
			cart_item: item
		};
						
		$.post(edd_scripts.ajaxurl, data, function(response) {
			if(response == 'removed') {
				$this.parent().remove();
				var quantity = $('span.edd-cart-quantity').text();
				quantity = parseInt(quantity) - 1;
				$('span.edd-cart-quantity').text(quantity);
			} else {
				//alert('bad');
			}
		});	
		return false;
	});
	
	// send Add to Cart request
	$('.edd-add-to-cart').on('click', function(event) {
		var $this = $(this);
		
		// show the ajax loader
		$this.next().show();
		
		var download = $this.data('download-id');
		var action = $this.data('action');
		var data = {
			action: action,
			download_id: download
		};
						
		$.post(edd_scripts.ajaxurl, data, function(cart_item_response) {
			if($('.cart_item.empty').length) {
				$(cart_item_response).insertBefore('.cart_item.empty');
				$('.cart_item.edd_checkout').show();
				$('.cart_item.empty').remove();
			} else {
				$(cart_item_response).insertBefore('.cart_item.edd_checkout');	
			}
			var quantity = $('span.edd-cart-quantity').text();
			quantity = parseInt(quantity) + 1;
			$('span.edd-cart-quantity').text(quantity);
			
			// hide the ajax loader
			$('.edd-cart-ajax').hide();
			$this.next().next().fadeIn();
			setTimeout( function() {
				$this.next().next().fadeOut();
			}, 3000);
		});	
		return false;
	});
	
	// validate and apply a discount
	$('#edd_checkout_form_wrap').on('click', '.edd-apply-discount', function(event) {
		var $this = $(this);
		var discount_code = $('#edd-discount').val();
		if(discount_code == '') {
			alert(edd_scripts.no_discount);
			return false;
		}
		
		var postData = {
			action: 'edd_apply_discount',
			code: discount_code
		};
		
		$.ajax({
			type: "POST",
			data: postData,
			dataType: "json",
			url: edd_scripts.ajaxurl,
			success: function(discount_response) {
				if(discount_response.msg == 'valid') {
					$('.edd_cart_amount').html(discount_response.amount).text();
					$this.text(edd_scripts.discount_applied);
				} else {
					alert(discount_response.msg);
				}
			}
		}).fail(function(data) {
			console.log(data);
		});
		return false;
	});
	
	// show the login form on the checkout page
	$('#edd_checkout_form_wrap').on('click', '.edd_checkout_register_login', function() {
		var $this = $(this);
		var action = $this.data('action');
		var data = {
			action: action
		};
		// show the ajax loader
		$('.edd-cart-ajax').show();
		
		$.post(edd_scripts.ajaxurl, data, function(checkout_response) {
			$('#edd_checkout_login_register').html(edd_scripts.loading);
			$('#edd_checkout_login_register').html(checkout_response);
			// hide the ajax loader
			$('.edd-cart-ajax').hide();
		});
		return false;
	});
	
	// load the fields for the selected payment method
	$('#edd_payment_mode').submit(function(e) {
		if($('select#edd-gateway').length) {
			var payment_mode = $('option:selected', '#edd-gateway').val();
		} else {
			var payment_mode = $('#edd-gateway').val();
		}
		var form = $(this);
		var action = form.attr("action") + '?payment-mode=' + payment_mode;
		// show the ajax loader
		$('.edd-cart-ajax').show();
		$('#edd_checkout_form_wrap').html('<img src="' + edd_scripts.ajax_loader + '"/>');
		$('#edd_checkout_form_wrap').load(action + ' #edd_checkout_form_wrap');
		return false;
	});
	
});