/**
 * Hides the customer details panel.
 */
function hideCustomerDetails() {
	const wrapEl = document.querySelector( '.customer-details-wrap' );
	if ( wrapEl ) {
		wrapEl.style.display = 'none';
	}
}

/**
 * Shows the customer details panel populated from a customer details response.
 *
 * The response values are escaped server-side, so they are inserted as HTML.
 *
 * @param {Object}           data      Customer details response data.
 * @param {HTMLElement|null} detailsEl The customer details element.
 * @param {HTMLElement|null} avatarEl  The customer avatar element.
 */
function showCustomerDetails( data, detailsEl, avatarEl ) {
	if ( detailsEl ) {
		detailsEl.style.display = 'flex';
	}
	const wrapEl = document.querySelector( '.customer-details-wrap' );
	if ( wrapEl ) {
		wrapEl.style.display = 'flex';
	}
	if ( avatarEl ) {
		avatarEl.innerHTML = data.avatar;
	}
	const nameEl = document.querySelector( '.customer-name' );
	if ( nameEl ) {
		nameEl.innerHTML = data.name;
	}
	const sinceEl = document.querySelector( '.customer-since span' );
	if ( sinceEl ) {
		sinceEl.innerHTML = data.date_created_i18n;
	}
	document.querySelector( '.customer-record a' )?.setAttribute( 'href', data._links.self );
}

// Change Customer: fetch updated customer details when the customer selector changes.
const customerInput = document.querySelector( '.edd-payment-change-customer-input' );

if ( customerInput ) {
	customerInput.addEventListener( 'change', async function() {
		const customerId = this.value;
		if ( '' === customerId ) {
			return;
		}

		const detailsEl = document.querySelector( '.customer-details' );
		const avatarEl  = document.getElementById( 'customer-avatar' );

		if ( detailsEl ) {
			detailsEl.style.display = 'none';
		}
		if ( avatarEl ) {
			avatarEl.innerHTML = '<span class="spinner is-active"></span>';
		}

		const body = new FormData();
		body.append( 'action', 'edd_customer_details' );
		body.append( 'customer_id', customerId );
		body.append( 'nonce', document.getElementById( 'edd_customer_details_nonce' )?.value ?? '' );

		try {
			const response          = await fetch( globalThis.ajaxurl, { method: 'POST', body } );
			const { success, data } = await response.json();
			if ( success ) {
				showCustomerDetails( data, detailsEl, avatarEl );
			} else {
				hideCustomerDetails();
			}
		} catch {
			hideCustomerDetails();
		}
	} );

	// Trigger on load to populate details for the current customer.
	customerInput.dispatchEvent( new Event( 'change' ) );
}

// New Customer toggle: switches between existing customer search and new customer form.
document.querySelector( '.edd-order-customer__actions' )?.addEventListener( 'click', ( e ) => {
	const btn = e.target.closest( 'button' );
	if ( ! btn ) {
		return;
	}
	e.preventDefault();

	btn.classList.add( 'active' );
	btn.closest( '.edd-order-customer__actions' )?.querySelectorAll( 'button' ).forEach( ( b ) => {
		if ( b !== btn ) {
			b.classList.remove( 'active' );
		}
	} );

	const isNewCustomer = btn.classList.contains( 'edd-payment-new-customer' );
	const isCancel      = btn.classList.contains( 'edd-payment-new-customer-cancel' );

	const orderCustomerInfo = document.querySelector( '.order-customer-info' );
	const newCustomerForm   = document.querySelector( '.new-customer' );

	if ( isNewCustomer ) {
		if ( orderCustomerInfo ) {
			orderCustomerInfo.style.display = 'none';
		}
		if ( newCustomerForm ) {
			newCustomerForm.style.removeProperty( 'display' );
		}
	} else if ( isCancel ) {
		if ( orderCustomerInfo ) {
			orderCustomerInfo.style.removeProperty( 'display' );
		}
		if ( newCustomerForm ) {
			newCustomerForm.style.display = 'none';
		}
	}

	const newCustomerHidden = document.getElementById( 'edd-new-customer' );
	if ( newCustomerHidden ) {
		newCustomerHidden.value = isNewCustomer ? 1 : 0;
	}
} );
