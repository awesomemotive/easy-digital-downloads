/* global eddAdminOrderOverview */

// Loads the modal when the refund button is clicked.
$(document.body).on('click', '.edd-refund-order', function (e) {
	e.preventDefault();
	var link     = $(this),
		postData = {
			action  : 'edd_generate_refund_form',
			order_id: $('input[name="edd_payment_id"]').val(),
		};

	$.ajax({
		type   : 'POST',
		data   : postData,
		url    : ajaxurl,
		success: function success(data) {
			let modal_content = '';
			if (data.success) {
				modal_content = data.html;
			} else {
				modal_content = data.message;
			}

			$('#edd-refund-order-dialog').dialog({
				position: { my: 'top center', at: 'center center-25%' },
				width    : '75%',
				modal    : true,
				resizable: false,
				draggable: false,
				classes: {
					'ui-dialog': 'edd-dialog',
				},
				closeText: eddAdminOrderOverview.i18n.closeText,
				open: function( event, ui ) {
					$(this).html( modal_content );
				},
				close: function( event, ui ) {
					$( this ).html( '' );
					if ( $( this ).hasClass( 'did-refund' ) ) {
						location.reload();
					}
				}
			});
			return false;
		}
	}).fail(function (data) {
		$('#edd-refund-order-dialog').dialog({
			position: { my: 'top center', at: 'center center-25%' },
			width    : '75%',
			modal    : true,
			resizable: false,
			draggable: false
		}).html(data.message);
		return false;
	});
});

$( document.body ).on( 'click', '.ui-widget-overlay', function ( e ) {
	$( '#edd-refund-order-dialog' ).dialog( 'close' );
} );

// Handles including items in the refund.
$(document.body).on( 'change', '#edd-refund-order-dialog tbody .check-column input[type="checkbox"]', function () {
	let parent = $(this).parent().parent(),
		all_checkboxes = $('#edd-refund-order-dialog tbody .check-column input[type="checkbox"]');

	if ( $(this).is(':checked') ) {
		parent.addClass('refunded');
	} else {
		parent.removeClass('refunded');
	}

	let new_subtotal = 0,
		new_tax      = 0,
		new_total    = 0,
		can_refund   = false;

	// Set a readonly while we recalculate, to avoid race conditions in the browser.
	all_checkboxes.prop('readonly', true);
	$('#edd-refund-submit-button-wrapper .spinner').css('visibility', 'visible');

	$('#edd-refund-order-dialog tbody .check-column input[type="checkbox"]:checked').each( function() {
		let item_parent = $(this).parent().parent();

		// Values for this item.
		let item_amount   = parseFloat( item_parent.find('span[data-amount]').data('amount') ),
			item_tax      = parseFloat( item_parent.find('span[data-tax]').data('tax') ),
			item_total    = parseFloat( item_parent.find('span[data-total]').data('total') ),
			item_quantity = parseInt( item_parent.find('.column-quantity').text() );

		new_subtotal += item_amount;
		new_tax      += item_tax;
		new_total    += item_total;
		can_refund    = true;
	});

	new_subtotal = parseFloat(new_subtotal).toFixed( edd_vars.currency_decimals );
	new_tax      = parseFloat(new_tax).toFixed( edd_vars.currency_decimals );
	new_total    = parseFloat(new_total).toFixed( edd_vars.currency_decimals );

	$( '#edd-refund-submit-subtotal-amount' ).text( new_subtotal );
	$( '#edd-refund-submit-tax-amount' ).text( new_tax );
	$( '#edd-refund-submit-total-amount' ).text( new_total );

	if ( can_refund ) {
		$( '#edd-submit-refund-submit' ).attr( 'disabled', false );
	} else {
		$( '#edd-submit-refund-submit' ).attr( 'disabled', true );
	}

	// Remove the readonly.
	all_checkboxes.prop('readonly', false);
	$('#edd-refund-submit-button-wrapper .spinner').css('visibility', 'hidden');

});

// Listen for the bulk action checkbox, since WP doesn't trigger a change on sub-items.
$(document.body).on('change', '#edd-refund-order-dialog #cb-select-all-1', function() {
	let item_checkboxes = $('#edd-refund-order-dialog tbody .check-column input[type="checkbox"]');
	if ( $(this).is(':checked')) {
		item_checkboxes.each(function() {
			$(this).prop('checked', true).trigger('change');
		});
	} else {
		item_checkboxes.each(function() {
			$(this).prop('checked', false).trigger('change');
		});
	}
});

// Process the refund form after the button is clicked.
$(document.body).on( 'click', '#edd-submit-refund-submit', function(e) {
	e.preventDefault();
	$('.edd-submit-refund-message').removeClass('success').removeClass('fail');
	$( this ).attr( 'disabled', false );
	$('#edd-refund-submit-button-wrapper .spinner').css('visibility', 'visible');
	$('#edd-submit-refund-status').hide();
	let item_ids = [];

	// Get the Order Item IDs we're going to be refunding.
	const item_checkboxes = $('#edd-refund-order-dialog tbody .check-column input[type="checkbox"]');
	item_checkboxes.each(function() {
		if ( $(this).is(':checked') ) {
			let item_id = $(this).parent().parent().data('order-item');
			item_ids.push(item_id);
		}
	});

	var postData = {
		action  : 'edd_process_refund_form',
		item_ids : item_ids,
		order_id: $('input[name="edd_payment_id"]').val(),
		nonce: $( '#edd_process_refund' ).val(),
	};

	$.ajax({
		type   : 'POST',
		data   : postData,
		url    : ajaxurl,
		success: function success(data) {
			const message_target = $('.edd-submit-refund-message'),
				url_target     = $('.edd-submit-refund-url');

			if ( data.success ) {
				$('#edd-refund-order-dialog table').hide();
				$('#edd-refund-order-dialog .tablenav').hide();

				message_target.text(data.message).addClass('success');
				url_target.attr( 'href', data.refund_url ).show();

				$( '#edd-submit-refund-status' ).show();
				url_target.focus();
				$( '#edd-refund-order-dialog' ).addClass( 'did-refund' );
			} else {
				message_target.text(data.message).addClass('fail');
				url_target.hide();

				$('#edd-submit-refund-status').show();
				$( '#edd-submit-refund-submit' ).attr( 'disabled', false );
				$( '#edd-refund-submit-button-wrapper .spinner' ).css( 'visibility', 'hidden' );
			}
		}
	} ).fail( function ( data ) {
		const message_target = $('.edd-submit-refund-message'),
			url_target     = $('.edd-submit-refund-url'),
			json           = data.responseJSON;


		message_target.text(json.message).addClass('fail');
		url_target.hide();

		$( '#edd-submit-refund-status' ).show();
		$( '#edd-submit-refund-submit' ).attr( 'disabled', false );
		$( '#edd-refund-submit-button-wrapper .spinner' ).css( 'visibility', 'hidden' );
		return false;
	});
});
