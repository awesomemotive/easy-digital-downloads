/**
 * Handles the disabled state of amount type wrapper containers
 *
 * @since 3.3.8
 */

( function () {
	'use strict';

	/**
	 * Updates the disabled state of the wrapper based on input state
	 *
	 * @param {HTMLElement} wrapper The wrapper element to check
	 */
	function updateWrapperState ( wrapper ) {
		const inputs = wrapper.querySelectorAll( 'input' );
		const hasDisabledInput = Array.from( inputs ).some( input => input.disabled );

		if ( hasDisabledInput ) {
			wrapper.classList.add( 'edd-disabled' );
		} else {
			wrapper.classList.remove( 'edd-disabled' );
		}
	}

	/**
	 * Initializes the amount type wrapper handlers
	 */
	function init () {
		// Create a MutationObserver to watch for changes in the DOM
		const observer = new MutationObserver( ( mutations ) => {
			mutations.forEach( ( mutation ) => {
				if ( mutation.type === 'attributes' && mutation.attributeName === 'disabled' ) {
					const wrapper = mutation.target.closest( '.edd-amount-type-wrapper' );
					if ( wrapper ) {
						updateWrapperState( wrapper );
					}
				}
			} );
		} );

		// Function to process existing wrappers
		function processExistingWrappers () {
			document.querySelectorAll( '.edd-amount-type-wrapper' ).forEach( wrapper => {
				updateWrapperState( wrapper );

				// Observe all inputs within the wrapper
				wrapper.querySelectorAll( 'input' ).forEach( input => {
					observer.observe( input, {
						attributes: true,
						attributeFilter: [ 'disabled' ]
					} );
				} );
			} );
		}

		// Process existing wrappers on DOMContentLoaded
		if ( document.readyState === 'loading' ) {
			document.addEventListener( 'DOMContentLoaded', processExistingWrappers );
		} else {
			processExistingWrappers();
		}
	}

	// Initialize when the document is ready
	if ( document.readyState === 'loading' ) {
		document.addEventListener( 'DOMContentLoaded', init );
	} else {
		init();
	}

} )();
