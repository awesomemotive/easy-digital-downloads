/**
 * Email filtering.
 */
const statusFilter = document.getElementById( 'edd-email-status-filter' );
const recipientFilter = document.getElementById( 'edd-email-recipient-filter' );
const senderFilter = document.getElementById( 'edd-email-sender-filter' );
const contextFilter = document.getElementById( 'edd-email-context-filter' );
const clearFilters = document.getElementById( 'edd-email-clear-filters' );
const noItemsFound = document.getElementById( 'no-items' );

if ( statusFilter ) {
	statusFilter.addEventListener( 'change', updateEmailFilters );
}
if ( recipientFilter ) {
	recipientFilter.addEventListener( 'change', updateEmailFilters );
}
if ( senderFilter ) {
	senderFilter.addEventListener( 'change', updateEmailFilters );
}
if ( contextFilter ) {
	contextFilter.addEventListener( 'change', updateEmailFilters );
}

/**
 * Updates the table to only show emails that match the filters.
 *
 * @since 2.7
 *
 * @param e
 */
function updateEmailFilters ( e ) {
	const tableRows = document.querySelectorAll( 'table.email_templates tbody tr:not(#no-items)' );

	if ( !tableRows || !( statusFilter || recipientFilter || senderFilter ) ) {
		return;
	}

	const chosenStatus = statusFilter ? statusFilter.value : '',
		chosenRecipient = recipientFilter ? recipientFilter.value : '',
		chosenSender = senderFilter ? senderFilter.value : '',
		chosenContext = contextFilter ? contextFilter.value : '';

	clearFilters.style.display = 'none';
	if ( chosenStatus || chosenRecipient || chosenSender || chosenContext ) {
		clearFilters.style.display = 'inline-block';
	}

	tableRows.forEach( tableRow => {
		// Always show the row by default, because it's easier to start that way.
		tableRow.style = '';
		tableRow.classList.remove( 'edd-hidden', 'alternate' );

		if ( chosenStatus && chosenStatus !== tableRow.getAttribute( 'data-status' ) ) {
			hideRow( tableRow );
		}

		if ( chosenRecipient && chosenRecipient !== tableRow.getAttribute( 'data-recipient' ) ) {
			hideRow( tableRow );
		}

		if ( chosenSender && chosenSender !== tableRow.getAttribute( 'data-sender' ) ) {
			hideRow( tableRow );
		}

		if ( chosenContext && chosenContext !== tableRow.getAttribute( 'data-context' ) ) {
			hideRow( tableRow );
		}
	} );

	// If there are no rows with data-type="item" visible, then toggle the "no items found" message.
	let visibleRows = document.querySelectorAll( '[data-type="item"]:not(.edd-hidden)' );
	updateRowClass( visibleRows );
	if ( visibleRows.length === 0 ) {
		noItemsFound.style = 'table-row';
	} else {
		noItemsFound.style.display = 'none';
	}
}

/**
 * Hides a table row and any associated extra content row.
 *
 * @param {HTMLElement} tableRow - The table row element to hide.
 */
function hideRow( tableRow ) {
	tableRow.style.display = 'none';
	tableRow.classList.add( 'edd-hidden' );
}

/**
 * Updates the row class of elements.
 *
 * @param {Array} elements - The elements to update the row class for.
 */
function updateRowClass( elements ) {
	elements.forEach( ( element, index ) => {
		if ( index % 2 === 0 ) {
			element.classList.add( 'alternate' );
		}
	} );
}

if ( clearFilters ) {
	clearFilters.addEventListener( 'click', e => {
		e.preventDefault();
		if ( statusFilter ) {
			statusFilter.value = '';
		}
		if ( recipientFilter ) {
			recipientFilter.value = '';
		}
		if ( senderFilter ) {
			senderFilter.value = '';
		}

		if ( contextFilter ) {
			contextFilter.value = '';
		}
		updateEmailFilters();
	} );
}
