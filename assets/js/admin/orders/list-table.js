/* global $, ajaxurl */

/**
 * Internal dependencies
 */
import { jQueryReady } from 'utils/jquery.js';

jQueryReady( () => {

	$( '.download_page_edd-payment-history .row-actions .delete a' ).on( 'click', function() {
		if( confirm( edd_vars.delete_order ) ) {
			return true;
		}
		return false;
	});

} );
