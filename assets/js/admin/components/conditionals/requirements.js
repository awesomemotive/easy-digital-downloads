/**
 * Toggle visibility of elements based on requirements using CSS classes.
 * Elements with class `edd-requires` and `edd-requires__***` will be toggled
 * based on the checked state of the corresponding checkbox with name/id `***`.
 */

/**
 * Update the visibility of elements based on requirements.
 *
 * @since 3.5.0
 * @param {string} requires The requirement identifier (e.g., 'vat-enable').
 * @param {bool}   enabled  Whether the requirement is met.
 */
function updateRequirements ( requires, enabled ) {
	const elements = document.querySelectorAll( '.edd-requires__' + requires );
	elements.forEach( function ( element ) {
		element.classList.remove( 'edd-hidden--required' );
		element.classList.toggle( 'edd-hidden', !enabled );
		if ( element.classList.contains( 'edd-hidden' ) ) {
			element.classList.add( 'edd-hidden--required' );
		}
	} );
}

/**
 * Initialize requirements on page load.
 */
function initializeRequirements () {
	// Find all checkboxes with data-edd-requirement attribute
	const requirementCheckboxes = document.querySelectorAll( '[data-edd-requirement]' );

	requirementCheckboxes.forEach( function ( checkbox ) {
		const requires = checkbox.getAttribute( 'data-edd-requirement' );
		if ( !requires ) {
			return;
		}

		// Update initial state
		updateRequirements( requires, checkbox.checked );
	} );
}

/**
 * Add event listeners for checkbox changes.
 */
function addRequirementListeners () {
	document.addEventListener( 'change', function ( event ) {
		const target = event.target;

		// Only process checkboxes with data-edd-requirement attribute
		if ( target.type !== 'checkbox' || !target.hasAttribute( 'data-edd-requirement' ) ) {
			return;
		}

		const requires = target.getAttribute( 'data-edd-requirement' );
		if ( !requires ) {
			return;
		}

		// Update dependent elements
		updateRequirements( requires, target.checked );
	} );
}

document.addEventListener( 'DOMContentLoaded', function () {
	initializeRequirements();
	addRequirementListeners();
} );
