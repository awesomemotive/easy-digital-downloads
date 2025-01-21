/**
 * Toggle visibility of elements based on requirements. To use this,
 * add the class `edd-requirement` to the element that should be toggled. This must also have a `data-edd-requirement` attribute
 * that is the name of the requirement. Then add a `data-edd-requires-` attribute to the elements that should be toggled based on the requirement.
 * The values should be `true` or `false` to indicate if the element should be shown when the requirement is met or not met.
 */

import { updateSupports } from "./supports";

// Look for requirments on initial load.
document.querySelectorAll( '.edd-requirement' ).forEach( function ( element ) {
	const requires = element.getAttribute( 'data-edd-requirement' );
	if ( !requires ) {
		return;
	}

	updateRequirements( requires, element.checked );
} );

// Listen for changes to requirements.
document.addEventListener( 'change', function ( event ) {
	if ( !event.target.classList.contains( 'edd-requirement' ) ) {
		return;
	}
	const requires = event.target.getAttribute( 'data-edd-requirement' );
	if ( !requires ) {
		return;
	}

	updateRequirements( requires, event.target.checked );
} );

/**
 * Update the visibility of elements based on requirements.
 *
 * @since 3.3.6
 * @param {string} requires The requirement to check.
 * @param {bool}   enabled  Whether the requirement is met.
 */
function updateRequirements ( requires, enabled ) {
	const elements = document.querySelectorAll( '[data-edd-requires-' + requires + ']' );
	elements.forEach( function ( element ) {
		element.classList.remove( 'edd-hidden--required' );
		if ( element.getAttribute( 'data-edd-requires-' + requires ) === 'true' ) {
			element.classList.toggle( 'edd-hidden', !enabled );
		} else {
			element.classList.toggle( 'edd-hidden', enabled );
		}
		if ( element.classList.contains( 'edd-hidden' ) ) {
			element.classList.add( 'edd-hidden--required' );
		}
	} );
}

document.querySelectorAll( '.edd-supports' ).forEach( function ( element ) {
	const supports = element.getAttribute( 'data-edd-supported' );
	if ( ! supports ) {
		return;
	}

	updateSupports( supports, element.value );
} );

// Listen for changes to supported values.
document.addEventListener( 'click', function ( event ) {
	if ( ! event.target.classList.contains( 'edd-supports' ) ) {
		return;
	}
	const supports = event.target.getAttribute( 'data-edd-supported' );
	if ( ! supports ) {
		return;
	}

	updateSupports( supports, event.target.value );
} );
