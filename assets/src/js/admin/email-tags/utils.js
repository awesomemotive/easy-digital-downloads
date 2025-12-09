/* global _ */

/**
 * Filters an item list given a search term.
 *
 * @param {Array} items        Item list
 * @param {string} searchTerm  Search term.
 *
 * @return {Array}             Filtered item list.
 */
export const searchItems = function( items, searchTerm ) {
	const normalizedSearchTerm = normalizeTerm( searchTerm );

	const matchSearch = function( string ) {
		return normalizeTerm( string ).indexOf( normalizedSearchTerm ) !== -1;
	};

	return _.filter( items, function( item ) {
		return matchSearch( item.title ) || _.some( item.keywords, matchSearch );
	} );
};

/**
 * Converts the search term into a normalized term.
 *
 * @param {string} term The search term to normalize.
 *
 * @return {string} The normalized search term.
 */
export const normalizeTerm = function( term ) {
	// Lowercase.
	//  Input: "MEDIA"
	term = term.toLowerCase();

	// Strip leading and trailing whitespace.
	//  Input: " media "
	term = term.trim();

	return term;
};
