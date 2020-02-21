/* global _, $ */

/**
 * Internal dependencies
 */
import { jQueryReady } from 'utils/jquery.js';

import { Overview } from './views';

import { OrderItems, OrderAdjustments } from './collections';

import { State } from './models';

/**
 * Setup Order Overview on DOM ready.
 *
 * @since 3.0
 */
jQueryReady( () => {
	// Do nothing if no data is available for hydration.
	if ( ! window.eddAdminOrderOverview ) {
		return;
	}

	const {
		isAdding,
		hasTax,
		hasQuantity,
		items,
		adjustments,
	} = window.eddAdminOrderOverview;

	// Create and hydrate state.
	const state = new State( {
		isAdding: '1' === isAdding,
		hasTax: '0' === hasTax ? false : hasTax,
		hasQuantity: '1' === hasQuantity,
	} );

	// Create collections and add to state.
	state.set( {
		items: new OrderItems( null, {
			state,
		} ),
		adjustments: new OrderAdjustments( null, {
			state,
		} ),
	} );

	// Create and render the Overview.
	const overview = new Overview( {
		state,
	} ).render();

	// Hydrate collections.
	// Adding individually vs. `set` so the state can be attached.
	items.forEach( ( item ) => state.get( 'items' ).add( {
		state,
		...item,
	} ) );

	adjustments.forEach( ( adjustment ) => state.get( 'adjustments' ).add( {
		state,
		...adjustment,
	} ) );

	/**
	 * Adjusts Overview tax configuration when a region changes.
	 *
	 * @since 3.0
	 */
	( () => {
		const countryInput = document.getElementById(
			'edd_order_address_country'
		);
		const regionInput = document.getElementById(
			'edd_order_address_region'
		);

		if ( ! ( countryInput && regionInput ) ) {
			return;
		}

		/**
		 * Retrieves a tax rate based on the currently selected Address.
		 *
		 * @since 3.0
		 */
		function getTaxRate() {
			const country =
				countryInput.options[ countryInput.selectedIndex ].value;
			const region = regionInput.options
				? regionInput.options[ regionInput.selectedIndex ].value
				: regionInput.value;

			const nonce = document.getElementById( 'edd_get_tax_rate_nonce' )
				.value;

			wp.ajax.send( 'edd_get_tax_rate', {
				data: {
					nonce,
					country,
					region,
				},
				/**
				 * Updates the Overview's tax configuration on successful retrieval.
				 *
				 * @since 3.0
				 *
				 * @param {Object} response AJAX response.
				 */
				success( response ) {
					let { tax_rate: rate } = response;

					// Make a percentage.
					rate = rate * 100;

					overview.options.state.set( 'hasTax', {
						country,
						region,
						rate,
					} );
				},
				/*
				 * Updates the Overview's tax configuration on failed retrieval.
				 *
				 * @since 3.0
				 */
				error() {
					overview.options.state.set( 'hasTax', false );
				},
			} );
		}

		// Update rate on Address change.
		//
		// Wait for Region field to be replaced when Country changes.
		// Wait for typing when Regino field changes.
		// jQuery listeners for Chosen compatibility.
		$( countryInput ).on( 'change', _.debounce( getTaxRate, 250 ) );
		$( regionInput ).on( 'change', getTaxRate );
		$( regionInput ).on( 'keyup', _.debounce( getTaxRate, 250 ) );
	} )();
} );
