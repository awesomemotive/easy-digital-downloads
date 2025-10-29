const sections = document.querySelector( '.edd-download-editor__sections' );
if ( sections ) {
	addRowListeners();
}

const events = [ 'edd_repeatable_row_change', 'edd_download_type_changed' ];
events.forEach( function ( event ) {
	document.addEventListener( event, function ( e ) {
		addRowListeners();
	}, false );  // false for bubbling phase
} );

function addRowListeners () {
	if ( ! sections ) {
		return;
	}
	const dynamicRows = sections.querySelectorAll( '.edd-has-handle-actions' );
	if ( ! dynamicRows.length ) {
		return;
	}

	updateRowButtons();

	dynamicRows.forEach( function ( section ) {
		section.querySelector( '.edd__handle-actions-order--higher' ).addEventListener( 'click', function () {
			const thisSectionTitle = this.closest( '.edd-has-handle-actions' );
			const prevSectionTitle = thisSectionTitle.previousElementSibling;
			if ( ! prevSectionTitle || !prevSectionTitle.classList.contains( 'edd-has-handle-actions' ) ) {
				return;
			}
			this.disabled = true;
			prevSectionTitle.insertAdjacentElement( 'beforebegin', thisSectionTitle );
			updateRowButtons();
		} );

		section.querySelector( '.edd__handle-actions-order--lower' ).addEventListener( 'click', function () {
			const thisSectionTitle = this.closest( '.edd-has-handle-actions' );
			const nextSectionTitle = thisSectionTitle.nextElementSibling;
			if ( ! nextSectionTitle || !nextSectionTitle.classList.contains( 'edd-has-handle-actions' ) ) {
				return;
			}
			this.disabled = true;
			thisSectionTitle.insertAdjacentElement( 'beforebegin', nextSectionTitle );
			updateRowButtons();
		} );
	} );
}

function updateRowButtons () {
	const containers = document.querySelectorAll( '.edd-handle-actions__group' );
	containers.forEach( function ( container ) {
		const rows = container.querySelectorAll( '.edd-has-handle-actions' );
		if ( ! rows.length ) {
			return;
		}
		rows.forEach( function ( row ) {
			row.querySelector( '.edd__handle-actions-order--higher' ).disabled = false;
			row.querySelector( '.edd__handle-actions-order--lower' ).disabled = false;
			row.querySelector( '.edd__handle-actions-order' ).classList.remove( 'edd-hidden' );
		} );

		const firstSection = rows[ 0 ];
		firstSection.querySelector( '.edd__handle-actions-order--higher' ).disabled = true;

		const lastSection = rows[ rows.length - 1 ];
		lastSection.querySelector( '.edd__handle-actions-order--lower' ).disabled = true;

		// if there is only one row, hide some things
		if ( rows.length === 1 ) {
			firstSection.querySelector( '.edd__handle-actions-order' ).classList.add( 'edd-hidden' );
		}
	} );
}
