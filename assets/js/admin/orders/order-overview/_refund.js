import { NumberFormat } from '@easy-digital-downloads/currency';

const number = new NumberFormat();

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

/**
 * Listen for the bulk actions checkbox, since WP doesn't trigger a change on sub-items.
 */
$( document.body ).on( 'change', '#edd-refund-order-dialog #cb-select-all-1', function () {
	const itemCheckboxes = $( '.edd-order-item-refund-checkbox' );
	const isChecked = $( this ).prop( 'checked' );

	itemCheckboxes.each( function() {
		$( this ).prop( 'checked', isChecked ).trigger( 'change' );
	} );
} );

/**
 * Listen for individual checkbox changes.
 * When it does, trigger a quantity change.
 */
$( document.body ).on( 'change', '.edd-order-item-refund-checkbox', function () {
	const parent = $( this ).parent().parent();
	const quantityField = parent.find( '.edd-order-item-refund-quantity' );

	if ( quantityField.length ) {
		if ( $( this ).prop( 'checked' ) ) {
			// Triggering a change on the quantity field handles enabling the inputs.
			quantityField.trigger( 'change' );
		} else {
			// Disable inputs and recalculate total.
			parent.find( '.edd-order-item-refund-input' ).prop( 'disabled', true );
			recalculateRefundTotal();
		}
	}
} );

/**
 * Handles quantity changes, which includes items in the refund.
 */
$( document.body ).on( 'change', '#edd-refund-order-dialog .edd-order-item-refund-input', function () {
	let parent = $( this ).parent().parent(),
		quantityField = parent.find( '.edd-order-item-refund-quantity' ),
		quantity = parseInt( quantityField.val() );

	if ( quantity > 0 ) {
		parent.addClass( 'refunded' );
	} else {
		parent.removeClass( 'refunded' );
	}

	// Only auto calculate subtotal / tax if we've adjusted the quantity.
	if ( $( this ).hasClass( 'edd-order-item-refund-quantity' ) ) {
		// Enable/disable amount fields.
		parent.find( '.edd-order-item-refund-input:not(.edd-order-item-refund-quantity)' ).prop( 'disabled', quantity === 0 );
		if ( quantity > 0 ) {
			quantityField.prop( 'disabled', false );
		}

		let subtotalField = parent.find( '.edd-order-item-refund-subtotal' ),
			taxField = parent.find( '.edd-order-item-refund-tax' ),
			originalSubtotal = number.unformat( subtotalField.data( 'original' ) ),
			originalTax = taxField.length ? number.unformat( taxField.data( 'original' ) ) : 0.00,
			originalQuantity = parseInt( quantityField.attr( 'max' ) ),
			calculatedSubtotal = ( originalSubtotal / originalQuantity ) * quantity,
			calculatedTax = taxField.length ? ( originalTax / originalQuantity ) * quantity : 0.00;

		// Make sure totals don't go over maximums.
		if ( calculatedSubtotal > parseFloat( subtotalField.data( 'max' ) ) ) {
			calculatedSubtotal = subtotalField.data( 'max' );
		}
		if ( taxField.length && calculatedTax > parseFloat( taxField.data( 'max' ) ) ) {
			calculatedTax = taxField.data( 'max' );
		}

		// Guess the subtotal and tax for the selected quantity.
		subtotalField.val( number.format( calculatedSubtotal ) );
		if ( taxField.length ) {
			taxField.val( number.format( calculatedTax ) );
		}
	}

	recalculateRefundTotal();
} );

/**
 * Calculates all the final refund values.
 */
function recalculateRefundTotal() {
	let newSubtotal   = 0,
		newTax        = 0,
		newTotal      = 0,
		canRefund     = false,
		allInputBoxes = $( '#edd-refund-order-dialog .edd-order-item-refund-input' );

	// Set a readonly while we recalculate, to avoid race conditions in the browser.
	allInputBoxes.prop( 'readonly', true );
	$( '#edd-refund-submit-button-wrapper .spinner' ).css( 'visibility', 'visible' );

	// Loop over all order items.
	$( '#edd-refund-order-dialog .edd-order-item-refund-quantity' ).each( function() {
		const thisItemQuantity = parseInt( $( this ).val() );

		if ( ! thisItemQuantity ) {
			return;
		}

		const thisItemParent = $( this ).parent().parent();
		const thisItemSelected = thisItemParent.find( '.edd-order-item-refund-checkbox' ).prop( 'checked' );

		if ( ! thisItemSelected ) {
			return;
		}

		// Values for this item.
		let thisItemTax = 0.00;

		let thisItemSubtotal = number.unformat( thisItemParent.find( '.edd-order-item-refund-subtotal' ).val() );

		if ( thisItemParent.find( '.edd-order-item-refund-tax' ).length ) {
			thisItemTax = number.unformat( thisItemParent.find( '.edd-order-item-refund-tax' ).val() );
		}

		let thisItemTotal = thisItemSubtotal + thisItemTax;

		thisItemParent.find( '.column-total span' ).text( number.format( thisItemTotal ) );

		// Negate amounts if working with credit.
		if ( thisItemParent.data( 'credit' ) ) {
			thisItemSubtotal = thisItemSubtotal * -1;
			thisItemTax      = thisItemTax * -1;
			thisItemTotal    = thisItemTotal * -1;
		}

		newSubtotal += thisItemSubtotal;
		newTax      += thisItemTax;
		newTotal    += thisItemTotal;
	} );

	if ( parseFloat( newTotal ) > 0 ) {
		canRefund = true;
	}

	$( '#edd-refund-submit-subtotal-amount' ).text( number.format( newSubtotal ) );
	$( '#edd-refund-submit-tax-amount' ).text( number.format( newTax ) );
	$( '#edd-refund-submit-total-amount' ).text( number.format( newTotal ) );

	$( '#edd-submit-refund-submit' ).attr( 'disabled', ! canRefund );

	// Remove the readonly.
	allInputBoxes.prop( 'readonly', false );
	$( '#edd-refund-submit-button-wrapper .spinner' ).css( 'visibility', 'hidden' );
}

/**
 * Process the refund form after the button is clicked.
 */
$(document.body).on( 'click', '#edd-submit-refund-submit', function(e) {
	e.preventDefault();
	$('.edd-submit-refund-message').removeClass('success').removeClass('fail');
	$( this ).attr( 'disabled', false ).addClass( 'updating-message' );
	$('#edd-submit-refund-status').hide();

	const refundForm = $( '#edd-submit-refund-form' );
	const refundData = refundForm.serialize();

	var postData = {
		action: 'edd_process_refund_form',
		data: refundData,
		order_id: $('input[name="edd_payment_id"]').val()
	};

	$.ajax({
		type   : 'POST',
		data   : postData,
		url    : ajaxurl,
		success: function success(response) {
			const message_target = $('.edd-submit-refund-message'),
				url_target     = $('.edd-submit-refund-url');

			if ( response.success ) {
				$('#edd-refund-order-dialog table').hide();
				$('#edd-refund-order-dialog .tablenav').hide();

				message_target.text(response.data.message).addClass('success');
				url_target.attr( 'href', response.data.refund_url ).show();

				$( '#edd-submit-refund-status' ).show();
				url_target.focus();
				$( '#edd-refund-order-dialog' ).addClass( 'did-refund' );
			} else {
				message_target.html(response.data).addClass('fail');
				url_target.hide();

				$('#edd-submit-refund-status').show();
				$( '#edd-submit-refund-submit' ).attr( 'disabled', false ).removeClass( 'updating-message' ).addClass( 'updated-message' );
			}
		}
	} ).fail( function ( data ) {
		const message_target = $('.edd-submit-refund-message'),
			url_target     = $('.edd-submit-refund-url'),
			json           = data.responseJSON;


		message_target.text(json.message).addClass('fail');
		url_target.hide();

		$( '#edd-submit-refund-status' ).show();
		$( '#edd-submit-refund-submit' ).attr( 'disabled', false ).removeClass( 'updating-message' ).addClass( 'updated-message' );
		return false;
	});
});

// Initialize WP toggle behavior for the modal.
$( document.body ).on( 'click', '.refunditems .toggle-row', function () {
	$( this ).closest( 'tr' ).toggleClass( 'is-expanded' );
} );
