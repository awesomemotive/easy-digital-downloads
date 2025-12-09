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
