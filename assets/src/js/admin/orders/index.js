import './order-overview';
import './order-details';

// Customer notice: cleared when customer changes.
if ( globalThis.eddAdminOrderOverview ) {
	const noCustomerErrorEl  = document.getElementById( 'edd-add-order-customer-error' );
	const assignCustomerEl   = document.getElementById( 'customer_id' );
	const newCustomerEmailEl = document.getElementById( 'edd_new_customer_email' );

	if ( noCustomerErrorEl && assignCustomerEl ) {
		assignCustomerEl.addEventListener( 'change', ( event ) => {
			if ( '0' !== event.target.value ) {
				noCustomerErrorEl.style.display = 'none';
			}
		} );
	}

	if ( noCustomerErrorEl && newCustomerEmailEl ) {
		newCustomerEmailEl.addEventListener( 'input', ( event ) => {
			if ( '' !== event.target.value ) {
				noCustomerErrorEl.style.display = 'none';
			}
		} );
	}
}

// Move `.update-nag` items below the top header.
// WordPress core does not move `.update-nag` the way it moves `.notice` items.
const header = document.querySelector( '.edit-post-editor-regions__header' );
if ( header ) {
	document.querySelectorAll( 'div.update-nag, div#update-nag' ).forEach( ( el ) => {
		header.after( el );
	} );
}
