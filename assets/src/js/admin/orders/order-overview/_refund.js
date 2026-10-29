import { NumberFormat } from '@easy-digital-downloads/currency';
import { setupEddModal } from '@easy-digital-downloads/modal';

const number = new NumberFormat();

// Tracks whether a refund was successfully submitted so we can reload on close.
let didRefund = false;

const dialogEl = document.getElementById( 'edd-refund-order-dialog' );

if ( dialogEl ) {
	const modal = setupEddModal( { dialog: dialogEl } );

	// Reload if a refund was processed; clear content for next open.
	dialogEl.addEventListener( 'close', () => {
		const content = dialogEl.querySelector( '.edd-modal__content' );
		if ( content ) {
			content.innerHTML = '';
		}
		if ( didRefund ) {
			location.reload();
		}
		didRefund = false;
	} );

	// Open the refund modal and load the form via AJAX.
	document.body.addEventListener( 'click', async ( e ) => {
		const btn = e.target.closest( '.edd-refund-order' );
		if ( ! btn ) {
			return;
		}
		e.preventDefault();

		const content = dialogEl.querySelector( '.edd-modal__content' );
		if ( ! content ) {
			return;
		}

		content.innerHTML = '<span class="spinner is-active"></span>';
		modal.open();

		const formData = new FormData();
		formData.append( 'action', 'edd_generate_refund_form' );
		formData.append( 'order_id', document.querySelector( 'input[name="edd_payment_id"]' )?.value ?? '' );

		try {
			const response = await fetch( globalThis.ajaxurl, { method: 'POST', body: formData } );
			const data = await response.json();
			content.innerHTML = data.success ? data.html : ( data.message ?? '' );
		} catch {
			content.innerHTML = '';
		}
	} );
}

// Delegate all refund form interactions to document.body so they work on dynamically injected content.

document.body.addEventListener( 'change', handleRefundChange );

function handleRefundChange( e ) {
	if ( handleSelectAllChange( e ) ) {
		return;
	}
	if ( handleItemCheckboxChange( e ) ) {
		return;
	}
	handleAmountInputChange( e );
}

function handleSelectAllChange( e ) {
	if ( ! e.target.matches( '#edd-refund-order-dialog #cb-select-all-1' ) ) {
		return false;
	}
	const isChecked = e.target.checked;
	document.querySelectorAll( '#edd-refund-order-dialog .edd-order-item-refund-checkbox' ).forEach( ( cb ) => {
		cb.checked = isChecked;
		cb.dispatchEvent( new Event( 'change', { bubbles: true } ) );
	} );
	return true;
}

function handleItemCheckboxChange( e ) {
	if ( ! e.target.matches( '#edd-refund-order-dialog .edd-order-item-refund-checkbox' ) ) {
		return false;
	}
	const row = e.target.closest( 'tr' );
	const quantityField = row?.querySelector( '.edd-order-item-refund-quantity' );
	if ( quantityField ) {
		if ( e.target.checked ) {
			quantityField.dispatchEvent( new Event( 'change', { bubbles: true } ) );
		} else {
			row.querySelectorAll( '.edd-order-item-refund-input' ).forEach( ( input ) => {
				input.disabled = true;
			} );
			recalculateRefundTotal();
		}
	}
	return true;
}

function handleAmountInputChange( e ) {
	if ( ! e.target.matches( '#edd-refund-order-dialog .edd-order-item-refund-input' ) ) {
		return;
	}
	const row = e.target.closest( '.refunditem' );
	const quantityField = row?.querySelector( '.edd-order-item-refund-quantity' );
	const quantity = Number.parseInt( quantityField?.value ?? '0', 10 );

	if ( row ) {
		row.classList.toggle( 'refunded', quantity > 0 );
	}

	if ( e.target.classList.contains( 'edd-order-item-refund-quantity' ) ) {
		updateQuantityInputs( row, quantityField, quantity );
	}

	recalculateRefundTotal();
}

function updateQuantityInputs( row, quantityField, quantity ) {
	row?.querySelectorAll( '.edd-order-item-refund-input:not(.edd-order-item-refund-quantity)' ).forEach( ( input ) => {
		input.disabled = quantity === 0;
	} );

	if ( quantity <= 0 || ! quantityField ) {
		return;
	}

	quantityField.disabled = false;

	const subtotalField = row.querySelector( '.edd-order-item-refund-subtotal' );
	const taxField      = row.querySelector( '.edd-order-item-refund-tax' );

	const originalSubtotal = number.unformat( subtotalField?.dataset.original ?? '0' );
	const originalTax      = taxField ? number.unformat( taxField.dataset.original ?? '0' ) : 0;
	const originalQuantity = Number.parseInt( quantityField.dataset.max ?? '1', 10 );

	let calculatedSubtotal = ( originalSubtotal / originalQuantity ) * quantity;
	let calculatedTax      = taxField ? ( originalTax / originalQuantity ) * quantity : 0;

	const maxSubtotal = Number.parseFloat( subtotalField?.dataset.max ?? '0' );
	const maxTax      = taxField ? Number.parseFloat( taxField.dataset.max ?? '0' ) : 0;

	if ( calculatedSubtotal > maxSubtotal ) {
		calculatedSubtotal = maxSubtotal;
	}
	if ( taxField && calculatedTax > maxTax ) {
		calculatedTax = maxTax;
	}

	if ( subtotalField ) {
		subtotalField.value = number.format( calculatedSubtotal );
	}
	if ( taxField ) {
		taxField.value = number.format( calculatedTax );
	}
}

// Submit the refund form.
document.body.addEventListener( 'click', ( e ) => {
	const btn = e.target.closest( '#edd-refund-order-dialog #edd-submit-refund-submit' );
	if ( btn ) {
		void submitRefund( btn, e );
	}
} );

async function submitRefund( btn, e ) {
	e.preventDefault();

	const dialog = document.getElementById( 'edd-refund-order-dialog' );
	if ( ! dialog ) {
		return;
	}

	dialog.querySelectorAll( '.edd-submit-refund-message' ).forEach( ( el ) => {
		el.classList.remove( 'success', 'fail' );
	} );

	btn.classList.remove( 'button-primary' );
	btn.disabled = true;
	btn.classList.add( 'updating-message' );

	const statusEl = dialog.querySelector( '#edd-submit-refund-status' );
	if ( statusEl ) {
		statusEl.style.display = 'none';
	}

	const refundForm = dialog.querySelector( '#edd-submit-refund-form' );
	const formData   = new FormData();
	formData.append( 'action', 'edd_process_refund_form' );
	formData.append( 'order_id', document.querySelector( 'input[name="edd_payment_id"]' )?.value ?? '' );

	// Serialize form fields as a URL-encoded string, matching jQuery's .serialize().
	if ( refundForm ) {
		const params = new URLSearchParams();
		new FormData( refundForm ).forEach( ( value, key ) => params.append( key, value ) );
		formData.append( 'data', params.toString() );
	}

	try {
		const response = await fetch( globalThis.ajaxurl, { method: 'POST', body: formData } );
		const data     = await response.json();

		if ( data.success ) {
			showRefundSuccess( dialog, data.data );
			didRefund = true;
		} else {
			showRefundError( dialog, data.data ?? '' );
			enableSubmitButton( btn );
		}
	} catch {
		enableSubmitButton( btn );
	}
}

/**
 * Reveals the refund result message and the link to the new refund order.
 *
 * @param {HTMLElement} dialog Refund dialog.
 * @param {Object}      refund Refund data returned by the AJAX handler.
 */
function showRefundSuccess( dialog, refund ) {
	const messageTarget = dialog.querySelector( '.edd-submit-refund-message' );
	if ( messageTarget ) {
		messageTarget.textContent = refund.message;
		messageTarget.classList.add( 'success' );
	}

	const urlTarget = dialog.querySelector( '.edd-submit-refund-url' );
	if ( urlTarget ) {
		urlTarget.setAttribute( 'href', refund.refund_url );
		urlTarget.style.removeProperty( 'display' );
		urlTarget.focus();
	}

	showRefundStatus( dialog );

	const refundForm = dialog.querySelector( '#edd-submit-refund-form' );
	if ( refundForm ) {
		refundForm.style.display = 'none';
	}
}

/**
 * Reveals the error returned by the AJAX handler and hides the refund link.
 *
 * The message is already escaped server side and may carry markup, so it is
 * assigned as HTML rather than text.
 *
 * @param {HTMLElement} dialog  Refund dialog.
 * @param {string}      message Error message.
 */
function showRefundError( dialog, message ) {
	const messageTarget = dialog.querySelector( '.edd-submit-refund-message' );
	if ( messageTarget ) {
		messageTarget.innerHTML = message;
		messageTarget.classList.add( 'fail' );
	}

	const urlTarget = dialog.querySelector( '.edd-submit-refund-url' );
	if ( urlTarget ) {
		urlTarget.style.display = 'none';
	}

	showRefundStatus( dialog );
}

/**
 * Shows the status area that holds the refund result message.
 *
 * @param {HTMLElement} dialog Refund dialog.
 */
function showRefundStatus( dialog ) {
	const statusEl = dialog.querySelector( '#edd-submit-refund-status' );
	if ( statusEl ) {
		statusEl.style.removeProperty( 'display' );
	}
}

/**
 * Returns the submit button to its resting state so the refund can be retried.
 *
 * @param {HTMLElement} btn Refund submit button.
 */
function enableSubmitButton( btn ) {
	btn.disabled = false;
	btn.classList.remove( 'updating-message' );
	btn.classList.add( 'button-primary' );
}

// Toggle row expansion in the mobile-responsive refund items list.
document.body.addEventListener( 'click', ( e ) => {
	const toggle = e.target.closest( '.refund-items .toggle-row' );
	if ( toggle ) {
		toggle.closest( 'tr' )?.classList.toggle( 'is-expanded' );
	}
} );

/**
 * Recalculates all refund totals based on currently selected items and entered amounts.
 */
function recalculateRefundTotal() {
	const dialog = document.getElementById( 'edd-refund-order-dialog' );
	if ( ! dialog ) {
		return;
	}

	let newSubtotal = 0;
	let newTax      = 0;
	let newTotal    = 0;
	let canRefund   = false;

	const allInputBoxes = dialog.querySelectorAll( '.edd-order-item-refund-input' );
	const allReadOnly   = dialog.querySelectorAll( '.edd-order-item-refund-input.readonly' );

	// Set readonly during calculation to prevent race conditions.
	allInputBoxes.forEach( ( input ) => { input.readOnly = true; } );

	dialog.querySelectorAll( '.edd-order-item-refund-quantity' ).forEach( ( qtyEl ) => {
		const qty = Number.parseInt( qtyEl.value, 10 );
		if ( ! qty ) {
			return;
		}

		const row        = qtyEl.closest( '.refunditem' );
		const isSelected = row?.querySelector( '.edd-order-item-refund-checkbox' )?.checked;

		if ( ! isSelected ) {
			row?.classList.remove( 'refunded' );
			return;
		}

		let itemSubtotal = number.unformat( row.querySelector( '.edd-order-item-refund-subtotal' )?.value ?? '0' );
		const taxEl      = row.querySelector( '.edd-order-item-refund-tax' );
		let itemTax      = taxEl ? number.unformat( taxEl.value ) : 0;
		let itemTotal    = itemSubtotal + itemTax;

		const totalSpan = row.querySelector( '.column-total span' );
		if ( totalSpan ) {
			totalSpan.textContent = number.format( itemTotal );
		}

		// Negate amounts for credit rows.
		if ( row.dataset.credit ) {
			itemSubtotal *= -1;
			itemTax      *= -1;
			itemTotal    *= -1;
		}

		if ( row.dataset.orderItem ) {
			newSubtotal += itemSubtotal;
		}

		newTax   += itemTax;
		newTotal += itemTotal;
	} );

	if ( Number.parseFloat( newTotal ) > 0 ) {
		canRefund = true;
	}

	const subtotalAmountEl = dialog.querySelector( '#edd-refund-submit-subtotal-amount' );
	const taxAmountEl      = dialog.querySelector( '#edd-refund-submit-tax-amount' );
	const totalAmountEl    = dialog.querySelector( '#edd-refund-submit-total-amount' );
	const submitBtn        = dialog.querySelector( '#edd-submit-refund-submit' );

	if ( subtotalAmountEl ) { subtotalAmountEl.textContent = number.format( newSubtotal ); }
	if ( taxAmountEl )      { taxAmountEl.textContent = number.format( newTax ); }
	if ( totalAmountEl )    { totalAmountEl.textContent = number.format( newTotal ); }
	if ( submitBtn )        { submitBtn.disabled = ! canRefund; }

	// Restore readonly state.
	allInputBoxes.forEach( ( input ) => { input.readOnly = false; } );
	allReadOnly.forEach( ( input ) => { input.readOnly = true; } );
}
