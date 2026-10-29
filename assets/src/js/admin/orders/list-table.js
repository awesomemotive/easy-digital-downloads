import { setupEddModal } from '@easy-digital-downloads/modal';

// Single order delete confirmation.
const singleDeleteDialog = document.getElementById( 'edd-single-delete-dialog' );
if ( singleDeleteDialog ) {
	const singleModal      = setupEddModal( { dialog: singleDeleteDialog } );
	let pendingDeleteUrl   = null;

	document.body.addEventListener( 'click', ( e ) => {
		const trigger = e.target.closest( '.download_page_edd-payment-history .row-actions .delete a' );
		if ( ! trigger ) {
			return;
		}
		e.preventDefault();
		pendingDeleteUrl = trigger.href;
		singleModal.open();
	} );

	singleDeleteDialog.querySelector( '.edd-modal__confirm' )?.addEventListener( 'click', () => {
		singleDeleteDialog.close();
		if ( pendingDeleteUrl ) {
			globalThis.location.href = pendingDeleteUrl;
		}
	} );

	singleDeleteDialog.querySelector( '.edd-modal__cancel' )?.addEventListener( 'click', () => {
		singleDeleteDialog.close();
	} );
}

// Bulk order delete confirmation.
const bulkDeleteDialog = document.getElementById( 'edd-bulk-delete-dialog' );
if ( bulkDeleteDialog ) {
	const bulkModal   = setupEddModal( { dialog: bulkDeleteDialog } );
	let pendingForm   = null;

	document.querySelector( '.download_page_edd-payment-history' )?.addEventListener( 'click', ( e ) => {
		const btn = e.target.closest( '#doaction, #doaction2' );
		if ( ! btn ) {
			return;
		}
		const form   = btn.closest( 'form' );
		const selectId = 'doaction2' === btn.id ? 'bulk-action-selector-bottom' : 'bulk-action-selector-top';
		const action = form?.querySelector( `#${ selectId }` )?.value;
		if ( 'delete' !== action ) {
			return;
		}
		e.preventDefault();
		pendingForm = form;
		bulkModal.open();
	} );

	bulkDeleteDialog.querySelector( '.edd-modal__confirm' )?.addEventListener( 'click', () => {
		bulkDeleteDialog.close();
		pendingForm?.submit();
	} );

	bulkDeleteDialog.querySelector( '.edd-modal__cancel' )?.addEventListener( 'click', () => {
		bulkDeleteDialog.close();
	} );
}
