/* global _, edd_vars */

/**
 * DOM ready.
 */
document.addEventListener( 'DOMContentLoaded', () => {
	const inputs = document.querySelectorAll( '.edd-price-field' );

	_.each( inputs, ( input ) => {
		let needsFormatting = false;
		let newValue = false;

		/**
		 * Determine if format is correct when a change is made.
		 *
		 * @todo Show a tooltip if the value will be changed when the input is left.
		 *
		 * @param {Object} e Keyboard event.
		 */
		input.addEventListener( 'keyup', ( e ) => {
			const currentValue = e.target.value;
			newValue = currentValue.replace( edd_vars.thousands_separator, '' );

			// We will have to change something, make note of this.
			if ( currentValue !== newValue ) {
				needsFormatting = true;

				// Show tooltip...
			}
		} );

		/**
		 * Make the change the use was warned of when leaving the field.
		 *
		 * @todo Hide the tooltip.
		 *
		 * @param {Object} e Keyboard event.
		 */
		input.addEventListener( 'change', ( e ) => {
			e.target.value = newValue;

			if ( needsFormatting ) {
				// Hide tooltip...
			}
		} );
	} );
} );
