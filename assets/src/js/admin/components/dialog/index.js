jQuery( document ).ready( function ( $ ) {
	/**
	 * If any jQueryUI Dialog instances exist with edd-dialog,
	 * instantiate them. Each instance will add their own buttons and handlers later.
	 */
	$('.edd-dialog').dialog({
		autoOpen: false,
		modal: true,
		draggable: false,
		closeOnEscape: true,
	});
} );
