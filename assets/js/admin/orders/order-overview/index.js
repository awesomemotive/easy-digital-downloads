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

let overview;

( () => {
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
	overview = new Overview( {
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
} ) ();

export default overview;
