const sections = document.querySelector( '.edd-download-editor__sections' );
if ( sections ) {
	addRowListeners();
}

const events = [ 'edd_repeatable_row_change', 'edd_download_type_changed' ];
events.forEach( function ( event ) {
	document.addEventListener( event, function () {
		addRowListeners();
	} );
} );

function addRowListeners () {
	const dynamicRows = sections.querySelectorAll( '.edd-has-handle-actions' );
	if ( ! dynamicRows.length ) {
		return;
	}

	updateRowButtons();

	dynamicRows.forEach( function ( section ) {
		section.querySelector( '.edd__handle-actions-order--higher' ).addEventListener( 'click', function () {
			const thisSectionTitle = this.closest( '.edd-has-handle-actions' );
			const prevSectionTitle = thisSectionTitle.previousElementSibling;
			if ( !prevSectionTitle.classList.contains( 'edd-has-handle-actions' ) ) {
				return;
			}
			this.disabled = true;
			prevSectionTitle.insertAdjacentElement( 'beforebegin', thisSectionTitle );
			updateRowButtons();
		} );

		section.querySelector( '.edd__handle-actions-order--lower' ).addEventListener( 'click', function () {
			const thisSectionTitle = this.closest( '.edd-has-handle-actions' );
			const nextSectionTitle = thisSectionTitle.nextElementSibling;
			if ( !nextSectionTitle.classList.contains( 'edd-has-handle-actions' ) ) {
				return;
			}
			this.disabled = true;
			thisSectionTitle.insertAdjacentElement( 'beforebegin', nextSectionTitle );
			updateRowButtons();
		} );
	} );
}

function updateRowButtons () {
	const rows = document.querySelectorAll( '.edd-has-handle-actions' );
	rows.forEach( function ( section ) {
		section.querySelector( '.edd__handle-actions-order--higher' ).disabled = false;
		section.querySelector( '.edd__handle-actions-order--lower' ).disabled = false;
	} );

	const firstSection = rows[ 0 ];
	firstSection.querySelector( '.edd__handle-actions-order--higher' ).disabled = true;

	const lastSection = rows[ rows.length - 1 ];
	lastSection.querySelector( '.edd__handle-actions-order--lower' ).disabled = true;

	// if there is only one section, hide some things
	if ( rows.length === 1 ) {
		firstSection.querySelector( '.edd__handle-actions-order' ).classList.add( 'edd-hidden' );
	} else {
		firstSection.querySelector( '.edd__handle-actions-order' ).classList.remove( 'edd-hidden' );
	}
}
