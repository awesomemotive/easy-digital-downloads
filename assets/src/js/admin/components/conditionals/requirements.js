/**
 * Toggle visibility of elements based on requirements using CSS classes.
 * Elements with class `edd-requires` and `edd-requires__***` will be toggled
 * based on the checked state of the corresponding checkbox with name/id `***`.
 */

/**
 * Update the visibility of elements based on requirements.
 *
 * @since 3.5.0
 * @param {string}       requires The requirement identifier (e.g., 'vat-enable').
 * @param {bool|string}  enabled  Whether the requirement is met (bool for checkboxes, string for selects).
 * @param {bool}         inverse  Whether to invert the logic (for inverse toggles).
 */
export function updateRequirements( requires, enabled, inverse = false ) {
	const shouldShow = inverse ? ! enabled : enabled;

	// Handle elements that show when ENABLED (exclude -disabled elements).
	const elements = document.querySelectorAll( '.edd-requires__' + requires + ':not(.edd-requires__' + requires + '-disabled)' );
	elements.forEach( function ( element ) {
		element.classList.remove( 'edd-hidden--required' );
		element.classList.toggle( 'edd-hidden', ! shouldShow );
		if ( element.classList.contains( 'edd-hidden' ) ) {
			element.classList.add( 'edd-hidden--required' );
		}
	} );

	// Handle elements that show when DISABLED (inverse of the above).
	const disabledElements = document.querySelectorAll( '.edd-requires__' + requires + '-disabled' );
	disabledElements.forEach( function ( element ) {
		element.classList.remove( 'edd-hidden--required' );
		element.classList.toggle( 'edd-hidden', shouldShow );
		if ( element.classList.contains( 'edd-hidden' ) ) {
			element.classList.add( 'edd-hidden--required' );
		}
	} );
}

/**
 * Update the visibility of elements based on select value requirements.
 *
 * @since 3.6.1
 * @param {string} requires The requirement identifier (e.g., 'captcha-provider').
 * @param {string} value    The current value of the select element.
 */
export function updateSelectRequirements( requires, value ) {
	// Find all elements that depend on this requirement
	const dependentElements = document.querySelectorAll( '[class*="edd-requires__' + requires + '-"]' );

	dependentElements.forEach( function ( element ) {
		// Extract the expected value from the class name
		const classMatch = element.className.match( new RegExp( 'edd-requires__' + requires + '-([\\w-]+)' ) );
		if ( classMatch ) {
			const expectedValue = classMatch[1];
			const shouldShow = value === expectedValue;

			element.classList.remove( 'edd-hidden--required' );
			element.classList.toggle( 'edd-hidden', ! shouldShow );
			if ( element.classList.contains( 'edd-hidden' ) ) {
				element.classList.add( 'edd-hidden--required' );
			}
		}
	} );

	// Also handle generic requirements (elements that should show for any non-empty value)
	const genericElements = document.querySelectorAll( '.edd-requires__' + requires + ':not([class*="edd-requires__' + requires + '-"])' );
	genericElements.forEach( function ( element ) {
		const shouldShow = '' !== value && null !== value && 'none' !== value;
		element.classList.remove( 'edd-hidden--required' );
		element.classList.toggle( 'edd-hidden', ! shouldShow );
		if ( element.classList.contains( 'edd-hidden' ) ) {
			element.classList.add( 'edd-hidden--required' );
		}
	} );
}

/**
 * Initialize requirements on page load.
 */
export function initializeRequirements() {
	// Find all elements with data-edd-requirement attribute (checkboxes and selects)
	const requirementElements = document.querySelectorAll( '[data-edd-requirement]' );

	requirementElements.forEach( function ( element ) {
		const requires = element.getAttribute( 'data-edd-requirement' );
		if ( ! requires ) {
			return;
		}

		// Handle select elements
		if ( element.tagName === 'SELECT' ) {
			updateSelectRequirements( requires, element.value );
		}
		// Handle checkboxes
		else if ( element.type === 'checkbox' ) {
			// Check if this is an inverse toggle
			const inverse = element.hasAttribute( 'data-edd-requirement-inverse' );
			// Update initial state
			updateRequirements( requires, element.checked, inverse );
		}
	} );
}

/**
 * Add event listeners for checkbox and select changes.
 */
export function addRequirementListeners() {
	// Listen to native change events for both checkboxes and selects
	document.addEventListener( 'change', function ( event ) {
		const target = event.target;

		// Only process elements with data-edd-requirement attribute
		if ( ! target.hasAttribute( 'data-edd-requirement' ) ) {
			return;
		}

		const requires = target.getAttribute( 'data-edd-requirement' );
		if ( ! requires ) {
			return;
		}

		// Handle select elements
		if ( target.tagName === 'SELECT' ) {
			updateSelectRequirements( requires, target.value );
		}
		// Handle checkboxes
		else if ( target.type === 'checkbox' ) {
			// Check if this is an inverse toggle
			const inverse = target.hasAttribute( 'data-edd-requirement-inverse' );
			// Update dependent elements
			updateRequirements( requires, target.checked, inverse );
		}
	} );

	// Listen for custom events from AJAX toggles
	document.addEventListener( 'eddSettingToggled', function ( event ) {
		if ( ! event.detail?.setting ) {
			return;
		}

		updateRequirements( event.detail.setting, event.detail.value );
	} );
}

/**
 * Auto-initialize on DOM ready for global settings pages.
 * Can be called manually for specific pages via explicit imports.
 */
document.addEventListener( 'DOMContentLoaded', function () {
	initializeRequirements();
	addRequirementListeners();
} );
