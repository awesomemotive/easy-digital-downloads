const variablePricing = document.getElementById( 'edd_variable_pricing' );
const variablePricingSection = document.getElementById( 'edd_download_editor__variable-pricing' );
const variablePriceRowClass = 'edd-section-content__row';
const variablePriceRowSelector = '.' + variablePriceRowClass;

// Add an event listener to the variable pricing toggle.
if ( variablePricing ) {
	toggleVariablePricing( variablePricing.checked );

	variablePricing.addEventListener( 'change', function () {
		toggleVariablePricing( this.checked );
	} );
}

if ( variablePricingSection ) {
	addOrderListeners();

	variablePricingSection.addEventListener( 'click', function ( event ) {
		if ( event.target.classList.contains( 'edd-button__add--variation' ) || event.target.closest( '.edd-button__add--variation' ) ) {
			cloneSection.call( event.target, event );
		}
		if ( event.target.classList.contains( 'edd-section-content__remove' ) ) {
			deleteSection.call( event.target, event );
			hideRemoveButton();
		}

		if ( event.target.classList.contains( 'edd-button__edit' ) ) {
			const priceRow = event.target.closest( variablePriceRowSelector );
			priceRow.classList.toggle( 'open' );
			priceRow.classList.toggle( 'closed' );
		}

		if ( event.target.classList.contains( 'edd-button__toggle-expand-custom' ) ) {
			event.preventDefault();
			const priceRows = variablePricingSection.querySelectorAll( variablePriceRowSelector );
			priceRows.forEach( function ( priceRow ) {
				priceRow.classList.add( 'open' );
				priceRow.classList.remove( 'closed' );
			} );
		}

		if ( event.target.classList.contains( 'edd-button__toggle-collapse-custom' ) ) {
			event.preventDefault();
			const priceRows = variablePricingSection.querySelectorAll( variablePriceRowSelector );
			priceRows.forEach( function ( priceRow ) {
				priceRow.classList.remove( 'open' );
				priceRow.classList.add( 'closed' );
			} );
		}
	} );

	// Ensure only one default price is selected.
	variablePricingSection.addEventListener( 'change', function ( event ) {
		if ( event.target.name !== '_edd_default_price_id' ) {
			return;
		}

		if ( event.target.checked ) {
			const checkboxes = variablePricingSection.querySelectorAll( 'input[name="_edd_default_price_id"]' );
			checkboxes.forEach( function ( checkbox ) {
				if ( checkbox !== event.target ) {
					checkbox.checked = false;
				}
			} );
		} else {
			event.target.checked = true;
		}
	} );

	// Add an additional listener to the document to handle clicks on the remove button from the modal.
	const modal = document.getElementById( 'edd-admin-notice-pricechanges' );
	if ( modal ) {
		modal.addEventListener( 'click', function ( event ) {
			if ( event.target.classList.contains( 'edd-section-content__remove' ) ) {
				deleteSection.call( event.target, event );
				hideRemoveButton();
			}
		} );
	}
}

function addOrderListeners() {
	const priceVariations = variablePricingSection.querySelectorAll( variablePriceRowSelector );
	if ( ! priceVariations.length ) {
		return;
	}

	updateOrderButtons();

	priceVariations.forEach( function ( section ) {
		section.querySelector( '.edd__handle-actions-order--higher' ).addEventListener( 'click', function () {
			const thisRow = this.closest( variablePriceRowSelector );
			const previousRow = thisRow.previousElementSibling;
			if ( ! previousRow || ! previousRow.classList.contains( variablePriceRowClass ) ) {
				return;
			}
			this.disabled = true;
			if ( previousRow ) {
				previousRow.insertAdjacentElement( 'beforebegin', thisRow );
			}
			updateOrderButtons();
		} );

		section.querySelector( '.edd__handle-actions-order--lower' ).addEventListener( 'click', function () {
			const thisRow = this.closest( variablePriceRowSelector );
			const nextRow = thisRow.nextElementSibling;
			if ( ! nextRow || ! nextRow.classList.contains( variablePriceRowClass ) ) {
				return;
			}
			this.disabled = true;
			if ( nextRow ) {
				thisRow.insertAdjacentElement( 'beforebegin', nextRow );
			}
			updateOrderButtons();
		} );
	} );
}

function toggleVariablePricing ( enabled ) {
	// Get all .edd-bundled-product-row, .edd-repeatable-row-standard-fields; using the document is intentional.
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

	const button = variablePricingSection.querySelector( '.edd-button__add--variation' );
	button.disabled = true;

	// Get a fresh list of rows.
	const updatedVariations = variablePricingSection.querySelectorAll( variablePriceRowSelector );
	const data = new FormData();
	data.append( 'action', 'edd_clone_variation' );
	data.append( 'timestamp', button.dataset.timestamp );
	data.append( 'token', button.dataset.token );
	data.append( 'download_id', edd_vars.post_id );

	let lastVariationNumber = 0;
	updatedVariations.forEach( function ( section ) {
		const hiddenInput = section.querySelector( '.edd-section__id' );
		const sectionNumber = parseInt( hiddenInput.value );
		if ( sectionNumber > lastVariationNumber ) {
			lastVariationNumber = sectionNumber;
		}
	} );

	data.append( 'section', parseInt( lastVariationNumber ) + 1 );

	fetch( ajaxurl, {
		method: 'POST',
		body: data,
	} ).then( function ( response ) {
		return response.json();
	} ).then( function ( data ) {
		if ( data.success ) {
			const lastVariation = updatedVariations[ updatedVariations.length - 1 ];
			lastVariation.insertAdjacentHTML( 'afterend', data.data );

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

	let section = this.closest( variablePriceRowSelector );
	if ( ! section ) {
		const dataId = this.getAttribute( 'data-id' );
		if ( dataId ) {
			const hiddenInput = variablePricingSection.querySelector( '.edd-section__id[value="' + dataId + '"]' );
			section = hiddenInput.closest( variablePriceRowSelector );
		}
	}

	section.remove();
}

// Hide the remove button for the first section.
function hideRemoveButton () {
	if ( ! variablePricingSection ) {
		return;
	}
	const variationRemoveButtons = variablePricingSection.querySelectorAll( '.edd-section-content__remove' );
	if ( variationRemoveButtons.length === 1 ) {
		variationRemoveButtons[ 0 ].classList.add( 'edd-hidden' );
	} else {
		variationRemoveButtons.forEach( function ( el ) {
			el.classList.remove( 'edd-hidden' );
		} );
	}
}

function updateOrderButtons() {
	const rows = variablePricingSection.querySelectorAll( variablePriceRowSelector );
	if ( ! rows.length ) {
		return;
	}
	rows.forEach( function ( section ) {
		section.querySelector( '.edd__handle-actions-order--higher' ).disabled = false;
		section.querySelector( '.edd__handle-actions-order--lower' ).disabled = false;
	} );

	const firstVariation = rows[ 0 ];
	firstVariation.querySelector( '.edd__handle-actions-order--higher' ).disabled = true;

	const lastVariation = rows[ rows.length - 1 ];
	lastVariation.querySelector( '.edd__handle-actions-order--lower' ).disabled = true;

	// if there is only one section, hide some things
	if ( rows.length === 1 ) {
		firstVariation.querySelector( '.edd__handle-actions-order' ).classList.add( 'edd-hidden' );
	} else {
		firstVariation.querySelector( '.edd__handle-actions-order' ).classList.remove( 'edd-hidden' );
	}
}

