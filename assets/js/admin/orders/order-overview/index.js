/**
 * Internal dependencies
 */
import { Currency, NumberFormat } from '@easydigitaldownloads/currency';
import { Overview } from './views/overview.js';
import { OrderItems } from './collections/order-items.js';
import { OrderItem } from './models/order-item.js';
import { OrderAdjustments } from './collections/order-adjustments.js';
import { OrderRefunds } from './collections/order-refunds.js';
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
		isRefund,
		hasTax,
		hasQuantity,
		hasDiscounts,
		order,
		items,
		adjustments,
		refunds,
	} = window.eddAdminOrderOverview;

	const currencyFormatter = new Currency( {
		currency: order.currency,
		currencySymbol: order.currencySymbol,
	} );

	// Create and hydrate state.
	const state = new State( {
		isAdding: '1' === isAdding,
		isRefund: '1' === isRefund,
		hasTax: '0' === hasTax ? false : hasTax,
		hasQuantity: '1' === hasQuantity,
		hasDiscounts: '1' === hasDiscounts,
		formatters: {
			currency: currencyFormatter,
			// Backbone doesn't merge nested defaults.
			number: new NumberFormat(),
		},
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
		refunds: new OrderRefunds( null, {
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

	// Hydrate `OrderRefund`s.
	refunds.forEach( ( refund ) => {
		state.get( 'refunds' ).add( {
			state,
			...refund,
		} );
	} );
} ) ();

export default overview;
