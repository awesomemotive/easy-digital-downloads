/**
 * Internal dependencies
 */
import './order-overview';
import './order-details';

import { jQueryReady } from 'utils/jquery.js';

jQueryReady( () => {

	// Move `.update-nag` items below the top header.
	// `#update-nag` is legacy styling, which core still supports.
	//
	// `.notice` items are properly moved, but WordPress core
	// does not move `.update-nag`.
	if ( 0 !== $( '.edit-post-editor-regions__header' ).length ) {
		$( 'div.update-nag, div#update-nag' ).insertAfter( $( '.edit-post-editor-regions__header' ) );
	}

	// "Validate" order form before submitting.
	// @todo move somewhere else?
	$( '#edd-add-order-form' ).on( 'submit', function() {
		$( '#publishing-action .spinner' ).css( 'visibility', 'visible' );
		$( '#edd-order-submit' ).prop( 'disabled', true );

		if ( $( '.orderitems tr.no-items' ).is( ':visible' ) ) {
			$( '#edd-add-order-no-items-error' ).slideDown();
		} else {
			$( '#edd-add-order-no-items-error' ).slideUp();
		}

		if ( $( '.order-customer-info' ).is( ':visible' ) && '0' === $( '#customer_id' ).val() ) {
			$( '#edd-add-order-customer-error' ).slideDown();
		} else {
			$( '#edd-add-order-customer-error' ).slideUp();
		}

		if ( $( '.notice:not(.updated)' ).is( ':visible' ) ) {
			$( '#publishing-action .spinner' ).css( 'visibility', 'hidden' );
			$( '#edd-order-submit' ).prop( 'disabled', false );
			return false;
		}
	} );

} );
