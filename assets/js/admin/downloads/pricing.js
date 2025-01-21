// get the .edd-download-editor__sections element
const sections = document.querySelector( '.edd-download-editor__sections' );
const variablePricing = document.querySelector( '#edd_variable_pricing' );

if ( sections ) {

	addOrderListeners();

	// if the variablePricing checkbox is not checked, hide the variable pricing section
	if ( variablePricing ) {
		toggleVariablePricing( variablePricing.checked );

		variablePricing.addEventListener( 'change', function () {
			toggleVariablePricing( this.checked );
		} );
	}

	document.addEventListener( 'click', function ( event ) {
		if ( event.target.classList.contains( 'edd-add-new-section' ) || event.target.closest( '.edd-add-new-section' ) ) {
			cloneSection.call( event.target, event );
		}
		if ( event.target.classList.contains( 'edd-section-content__remove' ) ) {
			deleteSection.call( event.target, event );
			hideRemoveButton();
		}
	} );

	document.addEventListener( 'change', function( event ) {
		// Update the nav item label when changing a variation name.
		if ( event.target.classList.contains( 'edd_variable_prices_name' ) ) {
			const section = event.target.closest( '.section-content--is-dynamic' );
			const navItem = sections.querySelector( '#' + section.id + '-nav-item' );
			const label = navItem.querySelector( '.label' );
			label.textContent = event.target.value;
		}
	} );

	// Ensure only one default price is selected.
	document.addEventListener( 'change', function( event ) {
		if ( event.target.name !== '_edd_default_price_id' ) {
			return;
		}

		if ( event.target.checked ) {
			const checkboxes = document.querySelectorAll( 'input[name="_edd_default_price_id"]' );
			checkboxes.forEach( function( checkbox ) {
				if ( checkbox !== event.target ) {
					checkbox.checked = false;
				}
			} );
		} else {
			event.target.checked = true;
		}
	} );
}

function addOrderListeners() {
	const dynamicSections = sections.querySelectorAll( '.edd-section-title__handle-actions' );
	if ( ! dynamicSections.length ) {
		return;
	}

	updateOrderButtons();

	dynamicSections.forEach( function ( section ) {
		section.querySelector( '.edd__handle-actions-order--higher' ).addEventListener( 'click', function () {
			const thisSectionTitle = this.closest( '.section-title--is-dynamic' );
			const prevSectionTitle = thisSectionTitle.previousElementSibling;
			if ( ! prevSectionTitle.classList.contains( 'section-title--is-dynamic' ) ) {
				return;
			}
			this.disabled = true;
			if ( prevSectionTitle ) {
				prevSectionTitle.insertAdjacentElement( 'beforebegin', thisSectionTitle );
			}
			const thisSectionId = thisSectionTitle.id.replace( '-nav-item', '' );
			const thisSection = sections.querySelector( '#' + thisSectionId );
			const prevSection = thisSection.previousElementSibling;
			if ( prevSection ) {
				prevSection.insertAdjacentElement( 'beforebegin', thisSection );
			}
			updateOrderButtons();
		} );

		section.querySelector( '.edd__handle-actions-order--lower' ).addEventListener( 'click', function () {
			const thisSectionTitle = this.closest( '.section-title--is-dynamic' );
			const nextSectionTitle = thisSectionTitle.nextElementSibling;
			if ( ! nextSectionTitle.classList.contains( 'section-title--is-dynamic' ) ) {
				return;
			}
			this.disabled = true;
			if ( nextSectionTitle ) {
				thisSectionTitle.insertAdjacentElement( 'beforebegin', nextSectionTitle );
			}
			const thisSectionId = thisSectionTitle.id.replace( '-nav-item', '' );
			const thisSection = sections.querySelector( '#' + thisSectionId );
			const nextSection = thisSection.nextElementSibling;
			if ( nextSection ) {
				thisSection.insertAdjacentElement( 'beforebegin', nextSection );
			}
			updateOrderButtons();
		} );
	} );
}

function toggleVariablePricing ( enabled ) {
	// get all .edd-bundled-product-row, .edd-repeatable-row-standard-fields
	const rows = document.querySelectorAll( '.edd-bundled-product-row, .edd-repeatable-row-standard-fields' );
	rows.forEach( function ( row ) {
		if ( enabled ) {
			row.classList.add( 'has-variable-pricing' );
		} else {
			row.classList.remove( 'has-variable-pricing' );
		}
	} );
	hideRemoveButton();
}

function cloneSection ( event ) {
	event.preventDefault();

	const button = document.querySelector( '.edd-add-new-section' );
	button.disabled = true;

	// Get a fresh list of sections.
	const updatedSections = document.querySelectorAll( '.section-content--is-dynamic' );
	const data = new FormData();
	data.append( 'action', 'edd_clone_section' );
	data.append( 'timestamp', button.dataset.timestamp );
	data.append( 'token', button.dataset.token );
	data.append( 'download_id', edd_vars.post_id );

	let lastSectionNumber = 0;
	updatedSections.forEach( function ( section ) {
		const hiddenInput = section.querySelector( '.edd-section__id' );
		const sectionNumber = parseInt( hiddenInput.value );
		if ( sectionNumber > lastSectionNumber ) {
			lastSectionNumber = sectionNumber;
		}
	} );

	data.append( 'section', parseInt( lastSectionNumber ) + 1 );

	fetch( ajaxurl, {
		method: 'POST',
		body: data,
	} ).then( function ( response ) {
		return response.json();
	} ).then( function ( data ) {
		if ( data.success ) {
			const lastSection = updatedSections[ updatedSections.length - 1 ];
			lastSection.insertAdjacentHTML( 'afterend', data.data.section );

			const sectionTitles = sections.querySelectorAll( '.section-title--is-dynamic' );
			const lastSectionTitle = sectionTitles[ sectionTitles.length - 1 ];
			lastSectionTitle.insertAdjacentHTML( 'afterend', data.data.link );

			// Go to the new section.
			lastSectionTitle.nextElementSibling.querySelector( 'a' ).click();

			button.disabled = false;
			hideRemoveButton();
			updateOrderButtons();
			addOrderListeners();
		}
	} );
}

function deleteSection ( e ) {
	e.preventDefault();
	if ( this.classList.contains( 'edd-promo-notice__trigger' ) ) {
		return;
	}

	let section = this.closest( '.section-content--is-dynamic' );
	if ( ! section ) {
		const dataId = this.getAttribute( 'data-id' );
		if ( dataId ) {
			const hiddenInput = sections.querySelector( '.edd-section__id[value="' + dataId + '"]' );
			section = hiddenInput.closest( '.section-content--is-dynamic' );
		}
	}

	sections.querySelector( '#' + section.id + '-nav-item' ).remove();
	section.remove();
	const navMenu = sections.querySelector( '.section-nav' );
	const selectedNavItem = navMenu.querySelector( 'li' );
	selectedNavItem.classList.add( 'section-title--is-active' );
	selectedNavItem.setAttribute( 'aria-selected', 'true' );
	selectedNavItem.querySelector( 'a' ).focus();
	sections.querySelector( '#' + selectedNavItem.getAttribute( 'aria-controls' ) ).style.display = 'block';
}

// Hide the remove button for the first section.
function hideRemoveButton () {
	const dynamicSectionRemoveButtons = sections.querySelectorAll( '.edd-section-content__remove' );
	if ( dynamicSectionRemoveButtons.length === 1 ) {
		dynamicSectionRemoveButtons[ 0 ].classList.add( 'edd-hidden' );
	} else {
		dynamicSectionRemoveButtons.forEach( function ( el ) {
			el.classList.remove( 'edd-hidden' );
		} );
	}
}

function updateOrderButtons() {
	const sections = document.querySelectorAll( '.section-title--is-dynamic' );
	sections.forEach( function ( section ) {
		section.querySelector( '.edd__handle-actions-order--higher' ).disabled = false;
		section.querySelector( '.edd__handle-actions-order--lower' ).disabled = false;
	} );

	const firstSection = sections[ 0 ];
	firstSection.querySelector( '.edd__handle-actions-order--higher' ).disabled = true;

	const lastSection = sections[ sections.length - 1 ];
	lastSection.querySelector( '.edd__handle-actions-order--lower' ).disabled = true;

	// if there is only one section, hide some things
	if ( sections.length === 1 ) {
		firstSection.querySelector( '.edd-section-title__handle-actions' ).classList.add( 'edd-hidden' );
	} else {
		firstSection.querySelector( '.edd-section-title__handle-actions' ).classList.remove( 'edd-hidden' );
	}
}

