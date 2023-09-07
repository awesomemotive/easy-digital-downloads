/* global wp, jQuery */

/**
 * Handle dismissing admin notices.
 */
jQuery( () => {
	/**
	 * Loops through each admin notice on the page for processing.
	 *
	 * @param {HTMLElement} noticeEl Notice element.
	 */
	jQuery( '.edds-admin-notice' ).each( function() {
		const notice = $( this );
		const id = notice.data( 'id' );
		const nonce = notice.data( 'nonce' );

		/**
		 * Listens for a click event on the dismiss button, and dismisses the notice.
		 *
		 * @param {Event} e Click event.
		 * @return {jQuery.Deferred} Deferred object.
		 */
		notice.on( 'click', '.notice-dismiss', ( e ) => {
			e.preventDefault();
			e.stopPropagation();

			return wp.ajax.post(
				'edds_admin_notices_dismiss_ajax',
				{
					id,
					nonce,
				}
			);
		} );
	} );
} );