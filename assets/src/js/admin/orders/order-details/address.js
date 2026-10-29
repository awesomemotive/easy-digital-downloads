import { initChosen } from 'utils/chosen.js';

// Store customer search results to help prefill address data.
let CUSTOMER_SEARCH_RESULTS = {
	company: '',
	addresses: {
		'0': {
			address: '',
			address2: '',
			city: '',
			region: '',
			postal_code: '',
			country: '',
		},
	},
};

function debounce( fn, ms ) {
	let t;
	return ( ...args ) => { clearTimeout( t ); t = setTimeout( () => fn( ...args ), ms ); };
}

/**
 * Adjusts Overview tax configuration when the Customer's address changes.
 */
( () => {
	// The overview publishes its bridge from a React effect, which runs after this module is
	// evaluated, so these guards read the localized config and the handler below picks the
	// bridge up once it exists. `hasTax` is 'none' or a location array, never a boolean.
	const config = globalThis.eddAdminOrderOverview;

	if ( ! config || 'none' === ( config.hasTax ?? 'none' ) ) {
		return;
	}

	// Localized booleans arrive as '1' or ''.
	if ( '1' !== String( config.isAdding ) ) {
		return;
	}

	const countryInput = document.getElementById( 'edd_order_address_country' );
	const regionInput  = document.getElementById( 'edd_order_address_region' );

	if ( ! ( countryInput && regionInput ) ) {
		return;
	}

	async function getTaxRate() {
		const country    = countryInput.tomselect?.getValue() ?? countryInput.value;
		const regionEl   = document.getElementById( 'edd_order_address_region' );
		const region     = regionEl?.tomselect?.getValue() ?? regionEl?.value ?? '';
		const customerId = document.getElementById( 'customer_id' )?.value ?? 0;
		const nonce      = document.getElementById( 'edd_get_tax_rate_nonce' )?.value ?? '';

		const body = new FormData();
		body.append( 'action', 'edd_get_tax_rate' );
		body.append( 'nonce', nonce );
		body.append( 'country', country );
		body.append( 'region', region );
		body.append( 'customerId', customerId );

		try {
			const response = await fetch( globalThis.ajaxurl, { method: 'POST', body } );
			const json     = await response.json();
			if ( ! json.success ) {
				throw new Error( json.data?.message ?? 'Failed to fetch tax rate.' );
			}
			let rate = json.data.tax_rate;
			rate = Math.round( ( rate * 100 ) * 10000 ) / 10000;
			const bridge = globalThis.eddOrderOverview;
			if ( bridge ) {
				// A failed request leaves 'none' in state, which carries no `inclusive` to keep.
				const currentTax = bridge.getHasTax();
				const location   = currentTax && 'object' === typeof currentTax ? currentTax : config.hasTax;
				bridge.dispatch( {
					type:    'SET_TAX',
					payload: { ...location, country, region, rate },
				} );
			}
		} catch {
			const bridge = globalThis.eddOrderOverview;
			if ( bridge ) {
				bridge.dispatch( { type: 'SET_TAX', payload: 'none' } );
			}
		}
	}

	const debouncedGetTaxRate = debounce( getTaxRate, 250 );

	countryInput.addEventListener( 'change', debouncedGetTaxRate );

	const addressWrap = document.getElementById( 'edd-order-address' );
	if ( addressWrap ) {
		addressWrap.addEventListener( 'change', ( e ) => {
			if ( e.target.matches( '#edd_order_address_region' ) ) {
				void getTaxRate();
			}
		} );
		addressWrap.addEventListener( 'keyup', ( e ) => {
			if ( e.target.matches( '#edd_order_address_region' ) ) {
				debouncedGetTaxRate();
			}
		} );
	}
} )();

/**
 * Hides the saved address selector.
 */
function hideAddressSelect() {
	const wrapEl = document.querySelector( '.customer-address-select-wrap' );
	if ( wrapEl ) {
		wrapEl.style.display = 'none';
	}
}

// Customer search: fetch saved addresses when a customer is selected.
document.body.addEventListener( 'change', async ( e ) => {
	if ( ! e.target.matches( '.edd-payment-change-customer-input' ) ) {
		return;
	}

	const body = new FormData();
	body.append( 'action', 'edd_customer_addresses' );
	body.append( 'customer_id', e.target.value );
	body.append( 'nonce', document.getElementById( 'edd_add_order_nonce' )?.value ?? '' );

	try {
		const response          = await fetch( globalThis.ajaxurl, { method: 'POST', body } );
		const { success, data } = await response.json();
		if ( ! success ) {
			hideAddressSelect();
			return;
		}

		const wrapEl = document.querySelector( '.customer-address-select-wrap' );

		CUSTOMER_SEARCH_RESULTS = {
			...CUSTOMER_SEARCH_RESULTS,
			...data,
			addresses: { ...CUSTOMER_SEARCH_RESULTS.addresses, ...data.addresses },
		};

		if ( data.html ) {
			if ( wrapEl ) {
				wrapEl.style.removeProperty( 'display' );
				const control = wrapEl.querySelector( '.edd-form-group__control' );
				if ( control ) {
					control.innerHTML = data.html;
				}
			}

			const firstKey = Object.keys( data.addresses ?? {} )[ 0 ];
			if ( firstKey ) {
				const select = wrapEl?.querySelector( '.add-order-customer-address-select' );
				if ( select ) {
					select.value = String( firstKey );
					select.dispatchEvent( new Event( 'change', { bubbles: true } ) );
				}
			}
		} else {
			hideAddressSelect();
		}
	} catch {
		hideAddressSelect();
	}
} );

/**
 * Retrieves a list of states based on a Country <select> element.
 *
 * @param {HTMLElement} countryEl Element containing country information.
 * @param {string} fieldName The name attribute for the region field.
 * @param {string} fieldId The id attribute for the region field.
 * @return {Promise<string>} Region HTML or 'nostates'.
 */
async function getStates( countryEl, fieldName, fieldId ) {
	const body = new FormData();
	body.append( 'action', 'edd_get_shop_states' );
	body.append( 'country', countryEl.tomselect?.getValue() ?? countryEl.value );
	body.append( 'nonce', countryEl.dataset.nonce ?? '' );
	body.append( 'field_name', fieldName );
	body.append( 'field_id', fieldId );
	// Without a region list, a free-text region field still lets the order be completed.
	try {
		const response = await fetch( globalThis.ajaxurl, { method: 'POST', body } );
		return await response.text();
	} catch {
		return 'nostates';
	}
}

/**
 * Replaces the Region area with the appropriate field type.
 *
 * @param {string} regions Regions response ('nostates' or an HTML <select>).
 */
function replaceRegionField( regions ) {
	const existing = document.getElementById( 'edd_order_address_region' );
	if ( ! existing ) {
		return;
	}

	if ( existing.matches( 'select' ) ) {
		existing.tomselect?.destroy();
	}

	if ( 'nostates' === regions ) {
		const input        = document.createElement( 'input' );
		input.type         = 'text';
		input.name         = 'edd_order_address[region]';
		input.id           = 'edd_order_address_region';
		input.value        = '';
		input.className    = 'wide-fat';
		input.style.cssText = 'max-width: none; width: 100%;';
		existing.replaceWith( input );
	} else {
		const t     = document.createElement( 'template' );
		t.innerHTML = regions.trim();
		const newEl = t.content.firstElementChild;
		if ( newEl ) {
			existing.replaceWith( newEl );
			initChosen( document.getElementById( 'edd_order_address_region' ) );
		}
	}
}

/**
 * Handles replacing a Region field when a Country field changes.
 *
 * @param {Event} e The change event.
 */
function updateRegionFieldOnChange( e ) {
	void getStates( e.currentTarget, 'edd_order_address[region]', 'edd_order_address_region' )
		.then( replaceRegionField );
}

// Address selector: fill form fields from a saved customer address.
document.body.addEventListener( 'change', ( e ) => {
	if ( ! e.target.matches( '.customer-address-select-wrap .add-order-customer-address-select' ) ) {
		return;
	}

	const val     = e.target.value;
	const address = CUSTOMER_SEARCH_RESULTS.addresses[ val ];
	const form    = document.getElementById( 'edd-add-order-form' );

	if ( ! form || ! address ) {
		return;
	}

	const setValue = ( name, value ) => {
		const el = form.querySelector( `input[name="${ name }"]` );
		if ( el ) {
			el.value = value;
		}
	};

	setValue( 'edd_order_address[address]',     address.address );
	setValue( 'edd_order_address[address2]',    address.address2 );
	setValue( 'edd_order_address[postal_code]', address.postal_code );
	setValue( 'edd_order_address[city]',        address.city );
	setValue( 'edd_order_address[company]',     CUSTOMER_SEARCH_RESULTS.company ?? '' );
	setValue( 'edd_order_address[address_id]',  val );

	const countryEl = document.getElementById( 'edd_order_address_country' );
	if ( ! countryEl ) {
		return;
	}

	// Remove region-update listener to prevent it firing while we set the country silently.
	countryEl.removeEventListener( 'change', updateRegionFieldOnChange );

	const countryInstance = countryEl.tomselect;
	if ( countryInstance ) {
		countryInstance.setValue( address.country, true );
	} else {
		countryEl.value = address.country;
	}

	void getStates( countryEl, 'edd_order_address[region]', 'edd_order_address_region' )
		.then( replaceRegionField )
		.then( () => {
			const regionEl       = document.getElementById( 'edd_order_address_region' );
			const regionInstance = regionEl?.tomselect;
			if ( regionInstance ) {
				regionInstance.setValue( address.region );
			} else if ( regionEl ) {
				regionEl.value = address.region;
				regionEl.dispatchEvent( new Event( 'change', { bubbles: true } ) );
			}
		} );

	// Restore region-update listener.
	countryEl.addEventListener( 'change', updateRegionFieldOnChange );
} );

// Country change: update region field.
document.getElementById( 'edd_order_address_country' )?.addEventListener( 'change', updateRegionFieldOnChange );
