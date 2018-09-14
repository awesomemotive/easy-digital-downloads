/* global eddEmailTagsInserter, tb_remove, tb_position, send_to_editor, _, window, document */

/**
 * Filters an item list given a search term.
 *
 * @param {Array} items        Item list
 * @param {string} searchTerm  Search term.
 *
 * @return {Array}             Filtered item list.
 */
function searchItems( items, searchTerm ) {
	var normalizedSearchTerm = normalizeTerm( searchTerm );

	var matchSearch = function( string ) {
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
function normalizeTerm( term ) {
	// Lowercase.
	//  Input: "MEDIA"
	term = term.toLowerCase();

	// Strip leading and trailing whitespace.
	//  Input: " media "
	term = term.trim();

	return term;
};

/**
 * Make tags clickable and send them to the email content (wp_editor()).
 */
function setupEmailTags() {
	// Find all of the buttons.
	var insertButtons = document.querySelectorAll( '.edd-email-tags-list-button' );

	/**
	 * Listen for clicks on tag buttons.
	 *
	 * @param {object} node Button node.
	 */
	insertButtons.forEach( function( node ) {

		/**
		 * Listen for clicks on tag buttons.
		 */
		node.addEventListener( 'click', function() {
			// Close Thickbox.
			tb_remove();

			window.send_to_editor( node.dataset.to_insert );
		} );
	} );
}

/**
 * Filter tags.
 */
function filterEmailTags() {
	var filterInput = document.querySelector( '.edd-email-tags-filter-search' )
		tagItems    = document.querySelectorAll( '.edd-email-tags-list-item' );

	filterInput.addEventListener( 'keyup', function( event ) {
		var searchTerm = event.target.value;
		var foundTags = searchItems( eddEmailTagsInserter.items, searchTerm );

		tagItems.forEach( function( node ) {
			var found = _.findWhere( foundTags, { tag: node.dataset.tag } );

			node.style.display = ! found ? 'none' : 'block';
		} );
	} );
}

/**
 * DOM ready.
 */
document.addEventListener( 'DOMContentLoaded', function() {
	// Resize Thickbox when media button is clicked.
	var mediaButton = document.querySelector( '.edd-email-tags-inserter' );

	mediaButton.addEventListener( 'click', tb_position );

	// Clickable tags.
	setupEmailTags();

	// Filterable tags.
	filterEmailTags();
} );
