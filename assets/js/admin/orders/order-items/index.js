/* global $, ajaxurl */

/**
 * Internal dependencies
 */
import { jQueryReady } from 'utils/jquery.js';

import './add.js';
import './remove.js';
import './refund.js';

import { reindexRows } from './../utils/list-table.js';

/**
 * Reindex order item table rows.
 * 
 * @since 3.0
 */
export const reindex = () => reindexRows( $( '.orderitems tbody tr:not(.no-items)' ) );

// @todo move somewhere else?
jQueryReady( () => {

	// Copy Download file URL.
	$( document.body ).on( 'click', '.edd-copy-download-link', function( e ) {
		e.preventDefault();

		const button = $( this ),
			postData = {
				action: 'edd_get_file_download_link',
				payment_id: $( 'input[name="edd_payment_id"]' ).val(),
				download_id: button.data( 'download-id' ),
				price_id: button.data( 'price-id' ),
			};

		$.ajax( {
			type: 'POST',
			data: postData,
			url: ajaxurl,
			success: function( link ) {
				console.log(link);
				$( '#edd-download-link' )
					.dialog( {
						width: 400,
					} )
					.html( '<textarea rows="10" cols="40" id="edd-download-link-textarea">' + link + '</textarea>' );

				$( '#edd-download-link-textarea' )
					.focus()
					.select();
			},
		} )
			.fail( function( data ) {
				if ( window.console && window.console.log ) {
					console.log( data );
				}
			} );
	} );

} );
