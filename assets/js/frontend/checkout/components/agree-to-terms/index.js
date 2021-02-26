/* global $ */

/**
 * Internal dependencies.
 */
import { jQueryReady } from '@easydigitaldownloads/utils';

/**
 * DOM ready.
 *
 * @since 3.0
 */
jQueryReady( () => {
	/**
	 * Toggles term content when clicked.
	 *
	 * @since unknown
	 *
	 * @param {Object} e Click event.
	 */
	$( document.body ).on( 'click', '.edd_terms_links', function( e ) {
		e.preventDefault();

		const terms = $( this ).parent();

		terms.prev( '.edd-terms' ).slideToggle();
		terms.find( '.edd_terms_links' ).toggle();
	} );
} );
