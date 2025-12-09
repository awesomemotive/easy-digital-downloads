jQuery( document ).ready( function( $ ) {

	const sectionSelector = '.edd-vertical-sections.use-js';
	// If the current screen doesn't have JS sections, return.
	if ( 0 === $( sectionSelector ).length ) {
		return;
	}

	// Handle the hash existing on page load.
	const hash = window.location.hash,
		defaultSectionHash = $( `${ sectionSelector } .section-nav li:first-child a` ).attr( 'href' );

	if ( hash.length ) {
		// Hides the section content.
		$( `${ sectionSelector } .section-content` ).hide();
		// When the page loads, make sure a section is selected.
		processSectionChange( hash );
	}

	// When a section nav item is clicked.
	$( 'body' ).on( 'click',
		`${ sectionSelector } .section-nav li a`,
		function( e ) {
			// Prevent the default browser action when a link is clicked.
			e.preventDefault();

			// Don't do anything if the click is on the actions handle.
			if ( e.target.classList.contains( 'edd__handle-actions' ) || e.target.closest( '.edd__handle-actions' ) ) {
				return;
			}

			let href = $( this ).attr( 'href' );

			processSectionChange( href );

			// Do not add the hash to the URL if we are in the download editor.
			if ( $( '.edd-download-editor__sections' ).length ) {
				return;
			}
			// Add the current "link" to the page URL
			window.history.pushState( 'object or string', '', href );
		}
	); // click()

	$( window ).on( 'hashchange', function() {
		processSectionChange( window.location.hash );
	} ); // Back/Forward Navigation.

	function processSectionChange( hash ) {
		// If the has is empty or doesn't include edd_, use the default section hash.
		if ( hash.length === 0 || ! hash.includes( 'edd_' ) ) {
			hash = defaultSectionHash;
		}

		// If the selected nav item doesn't exist, use the default section hash.
		let selectedNavItem = $( sectionSelector + ' ' + hash + '-nav-item' );

		if ( ! selectedNavItem.length ) {
			hash = defaultSectionHash;
			selectedNavItem = $( sectionSelector + ' ' + defaultSectionHash + '-nav-item' );
		}

		let selectedContent = $( sectionSelector + ' ' + hash ),
			parents  = selectedNavItem.parents( '.edd-vertical-sections' );

			// Hide all section content.
			parents.find( '.section-content' ).hide();

			// Set the `aria-selected` attribute to false for all section nav items.
			parents.find( '.section-title' ).attr( 'aria-selected', 'false' ).removeClass( 'section-title--is-active' ).find( 'a' ).trigger( 'blur' );

			// Set the `aria-selected` attribute to true for this section nav item.
			selectedNavItem.attr( 'aria-selected', 'true' ).addClass( 'section-title--is-active' ).find( 'a' ).trigger( 'focus' );

			// Find the section content that matches the section nav item and show it.
			selectedContent.show();

			// Maybe re-Chosen
			selectedContent.find( 'div.chosen-container' ).css( 'width', '100%' );
	}
} );
