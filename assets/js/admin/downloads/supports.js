/**
 * Toggle visibility of elements based on supported values. To use this,
 * add the class `edd-supports` to the element that should be toggled. This must also have a `data-edd-supported` attribute
 * that is the name of the requirement. Then add a `data-edd-supports-` attribute to the elements that should be toggled based on the requirement.
 * The values should be comma separated strings to indicate if the element should be shown when the requirement is met or not met.
 */

/**
 * Update the visibility of elements based on supported values.
 *
 * @since 3.3.6
 * @param {string} supports      The requirement to check.
 * @param {string} selectedValue The selected value.
 */
export function updateSupports ( supports, supportedValue ) {
	const elements = document.querySelectorAll( '[data-edd-supports-' + supports + ']' );
	if ( ! supportedValue.length ) {
		supportedValue = 'false';
	}

	elements.forEach( function ( element ) {
		if ( element.classList.contains( 'edd-hidden--required' ) ) {
			return;
		}
		const elementSupports = element.getAttribute( 'data-edd-supports-' + supports );
		if ( ! elementSupports.split( ',' ).includes( supportedValue ) ) {
			element.classList.add( 'edd-hidden' );
		} else {
			element.classList.remove( 'edd-hidden' );
		}
	} );
}
