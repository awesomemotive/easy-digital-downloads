/* global $, ajaxurl */

/**
 * Internal dependencies
 */
import { jQueryReady } from '@easydigitaldownloads/utils';

jQueryReady( () => {

	$( '.edd-advanced-filters-button' ).on( 'click', function( e ) {
		e.preventDefault();

		$( '#edd-advanced-filters' ).toggleClass( 'open' );
	} );

} );
