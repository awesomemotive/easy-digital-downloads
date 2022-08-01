/* global jQuery */

/**
 * Safe wrapper for jQuery DOM ready.
 *
 * This should be used only when a script requires the use of jQuery.
 *
 * @param {Function} callback Function to call when ready.
 */
export const jQueryReady = function( callback ) {
	( function( $ ) {
		$( callback );
	}( jQuery ) );
};
