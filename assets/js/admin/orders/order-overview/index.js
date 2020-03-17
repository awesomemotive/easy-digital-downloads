/* global _, $ */

/**
 * Internal dependencies
 */
import { jQueryReady } from 'utils/jquery.js';
import { Overview } from './views/overview.js';
import { OrderItems } from './collections/order-items.js';
import { OrderItem } from './models/order-item.js';
import { OrderAdjustments } from './collections/order-adjustments.js';
import { State } from './models/state.js';

// Temporarily include old Refund flow.
import './_refund.js';

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
		hasDiscounts,
		order,
		items,
		adjustments,
	} = window.eddAdminOrderOverview;

	// Create and hydrate state.
	const state = new State( {
		isAdding: '1' === isAdding,
		hasTax: '0' === hasTax ? false : hasTax,
		hasQuantity: '1' === hasQuantity,
		hasDiscounts: '1' === hasDiscounts,
		order,
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

	// Create Overview.
	const overview = new Overview( {
		state,
	} );

	// Hydrate collections.

	// Hydrate `OrderItem`s.
	//
	// Models are created manually before being added to the collection to
	// ensure attributes maintain schema with deep model attributes.
	items.forEach( ( item ) => {
		const orderItemAdjustments = new OrderAdjustments( item.adjustments );
		const orderItem = new OrderItem( {
			...item,
			adjustments: orderItemAdjustments,
			state,
		} );

		state.get( 'items' ).add( orderItem );
	} );

	// Hyrdate `Order`-level `Adjustments`.
	adjustments.forEach( ( adjustment ) => {
		state.get( 'adjustments' ).add( {
			state,
			...adjustment,
		} )
	} );

	// ... finally render the Overview once all data is set.
	overview.render();

	window.state = state;

	/**
	 * Adjusts Overview tax configuration when the Customer's address changes.
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
