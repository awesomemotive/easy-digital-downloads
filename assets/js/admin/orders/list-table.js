/* global $, ajaxurl, edd_vars */

/**
 * Internal dependencies
 */
import { jQueryReady } from 'utils/jquery.js';

jQueryReady( () => {

	// Deleting a single order.
	$( '.download_page_edd-payment-history .row-actions .delete a' ).on( 'click', function(e) {
		e.preventDefault();
		let targetUrl = $(this).attr('href');

		$("#edd-single-delete-dialog").dialog({
			buttons : [
				{
					text : edd_vars.cancel_dialog_text,
					class : 'button-secondary',
					click: function() {
						$(this).dialog('close');
					},
				},
				{
					text : edd_vars.confirm_dialog_text,
					class : 'button-primary',
					click :  function() {
						$(this).dialog('close');
						window.location.href = targetUrl;
					},
				},
			]
		});

		$('#edd-single-delete-dialog').dialog('open');
	});


	// Trashed Orders.
	$( '.download_page_edd-payment-history' ).on( 'click', '#doaction', function ( e ) {
		let action = $( '#bulk-action-selector-top' ).val(),
			form = $(this).closest( 'form' );

		if ( 'delete' !== action ) {
			return;
		}

		e.preventDefault();

		$("#edd-bulk-delete-dialog").dialog({
			buttons : [
				{
					text : edd_vars.cancel_dialog_text,
					class : 'button-secondary',
					click: function() {
						$(this).dialog('close');
					},
				},
				{
					text : edd_vars.confirm_dialog_text,
					class : 'button-primary',
					click :  function() {
						$(this).dialog('close');
						form.submit();
					},
				},
			]
		});

		$('#edd-bulk-delete-dialog').dialog('open');
	});

} );
