/**
 * Internal dependencies.
 */
import { forEach } from 'utils'; // eslint-disable-line @wordpress/dependency-group

/**
 * forEach implementation that can handle anything.
 */
export { default as forEach } from 'lodash.foreach';

/**
 * DOM ready.
 *
 * Handles multiple callbacks.
 *
 * @param {Function} Callback function to run.
 */
export function domReady() {
	forEach( arguments, ( callback ) => {
		document.addEventListener( 'DOMContentLoaded', callback );
	} );
}

/**
 * Retrieves all following siblings of an element.
 *
 * @param {HTMLElement} el Starting element.
 * @return {Array} siblings List of sibling elements.
 */
export function getNextSiblings( el ) {
	const siblings = [];
	let sibling = el.nextElementSibling;

	while ( sibling ) {
		if ( sibling.nodeType === 1 ) {
			siblings.push( sibling );
		}

		sibling = sibling.nextElementSibling;
	}

	return siblings;
}
