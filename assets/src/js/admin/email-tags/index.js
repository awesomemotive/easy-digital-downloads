/* global eddEmailTagsInserter, send_to_editor, _, window, document */

/**
 * Internal dependencies.
 */
import { setupEddModal } from '@easy-digital-downloads/modal';
import { searchItems } from './utils.js';

/**
 * Make tags clickable and send them to the email content (wp_editor()).
 *
 * @param {Object} modalApi From setupEddModal; used to close the dialog on insert.
 */
function setupEmailTags( modalApi ) {
	const insertButtons = document.querySelectorAll( '.edd-email-tags-list-button' );
	if ( ! insertButtons.length ) {
		return;
	}

	_.each( insertButtons, function( node ) {
		node.addEventListener( 'click', function() {
			if ( modalApi && typeof modalApi.close === 'function' ) {
				modalApi.close();
			}
			window.send_to_editor( node.dataset.to_insert );
		} );
	} );
}

/**
 * Tags search event setup.
 */
function setupEmailTagSearch() {
	const filterInput = document.querySelector( '.edd-email-tags-filter-search' );
	if ( ! filterInput ) {
		return;
	}

	filterInput.addEventListener( 'input', function( event ) {
		filterEmailTags( event.target.value );
	} );
}

/**
 * Tags navigation event setup.
 */
function setupEmailTagNavigation() {
	document.addEventListener( 'keydown', function( event ) {
		const key = event.key || event.keyCode;
		if ( ! key ) {
			return;
		}

		if ( key === 'ArrowDown' || key === 40 ) {
			handleNavigation( 'down' );
			return;
		}

		if ( key === 'ArrowUp' || key === 38 ) {
			handleNavigation( 'up' );
		}
	} );
}

/**
 * Focus and reset the search input when the dialog is opened (used as onOpen callback).
 *
 * @param {HTMLDialogElement} dialog The modal dialog element.
 */
function focusSearchOnOpen( dialog ) {
	if ( ! dialog ) {
		return;
	}
	const filterInput = dialog.querySelector( '.edd-email-tags-filter-search' );
	if ( filterInput ) {
		filterInput.value = '';
		filterInput.dispatchEvent( new Event( 'input' ) );
		filterInput.focus();
	}
}

/**
 * Filter tags.
 *
 * @param {string} term Tags search term.
 */
function filterEmailTags( term = '' ) {

	const tagItems = document.querySelectorAll( '.edd-email-tags-list-item' );
	const foundTags = searchItems( eddEmailTagsInserter.items, term );

	_.each( tagItems, function( node ) {
		const found = _.findWhere( foundTags, { tag: node.dataset.tag } );

		node.style.display = ! found ? 'none' : 'block';
	} );
}

/**
 * Handle navigation between tags and search input.
 *
 * @param {string} direction Navigation direction.
 */
function handleNavigation( direction ) {
	const tagItems = document.querySelectorAll( '.edd-email-tags-list-item' );
	if ( ! tagItems.length ) {
		return;
	}

	const searchInputSelector = '.edd-email-tags-filter-search';
	const tagButtonSelector = '.edd-email-tags-list-button';

	const currentElement = document.activeElement;
	if ( ! currentElement || ( ! currentElement.matches( searchInputSelector ) && ! currentElement.matches( tagButtonSelector ) ) ) {
		return;
	}

	let nextTag;
	let isVisible = false;

	// skip the search input navigation from the loop.
	if ( currentElement.matches( searchInputSelector ) ) {
		if ( direction === 'up' ) {
			return;
		}
		nextTag = _.find( tagItems, ( node ) => node.style.display !== 'none' );
		isVisible = true;
	} else {
		nextTag = currentElement.parentElement;
	}

	// find a visible tag.
	while ( ! isVisible ) {
		nextTag = direction === 'down' ? nextTag.nextElementSibling : nextTag.previousElementSibling;
		isVisible = ! nextTag || nextTag.style.display !== 'none';
	}

	if ( ! nextTag ) {
		// focus on the search input when no previous tags found.
		if ( direction === 'up' ) {
			document.querySelector( searchInputSelector ).focus();
		}
		return;
	}

	nextTag.querySelector( tagButtonSelector ).focus();
}

/**
 * DOM ready.
 */
document.addEventListener( 'DOMContentLoaded', function() {
	// Use selector + dialogId so delegation works when the button/dialog are added later (e.g. onboarding step via AJAX).
	const modalApi = setupEddModal( {
		trigger: '.edd-email-tags-inserter',
		dialogId: 'edd-insert-email-tag-dialog',
		onOpen: focusSearchOnOpen,
	} );

	if ( ! modalApi ) {
		return;
	}

	setupEmailTags( modalApi );
	setupEmailTagSearch();
	setupEmailTagNavigation();
} );
