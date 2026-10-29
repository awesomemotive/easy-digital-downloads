/* global ajaxurl */

/**
 * Order overview amount recalculation requests.
 *
 * @package     EDD\Admin\Orders
 * @copyright   Copyright (c) 2026, Sandhills Development, LLC
 * @license     https://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       3.7.3
 */

import { getTaxCountry, getTaxRegion } from '../state/selectors.js';

/**
 * Recursively serializes an object to jQuery-style bracket-notation pairs,
 * e.g. { products: [{ id: 1 }] } → [["products[0][id]", "1"]].
 */
function buildParams( prefix, value, pairs ) {
	if ( Array.isArray( value ) ) {
		value.forEach( ( v, i ) => buildParams( `${ prefix }[${ i }]`, v, pairs ) );
	} else if ( value !== null && typeof value === 'object' ) {
		Object.keys( value ).forEach( ( k ) => buildParams( `${ prefix }[${ k }]`, value[ k ], pairs ) );
	} else {
		pairs.push( [ prefix, value ?? '' ] );
	}
}

/**
 * Builds FormData from an object, flattening nested values the way jQuery.param() does,
 * so PHP receives the nested $_POST arrays the AJAX handler expects.
 *
 * @param {Object} data Request fields.
 * @return {FormData} The request body.
 */
export function toFormData( data ) {
	const fd = new FormData();
	Object.keys( data ).forEach( ( key ) => {
		const pairs = [];
		buildParams( key, data[ key ], pairs );
		pairs.forEach( ( [ k, v ] ) => fd.append( k, v ) );
	} );
	return fd;
}

/**
 * Builds the cart sent with a get-item-amounts request, always including the item being priced.
 *
 * The handler splits flat discounts across this cart, so leaving the item out gives it the wrong share.
 *
 * @param {Array}  items   Order items in state.
 * @param {Object} current The item being priced: productId, priceId and quantity.
 * @return {Array} Products in the shape the handler expects, without duplicates.
 */
export function getCartProducts( items, current ) {
	return [ ...items, current ]
		.map( ( item ) => ( {
			id: item.productId,
			quantity: item.quantity,
			options: { price_id: item.priceId },
		} ) )
		.filter( ( p, i, arr ) =>
			i === arr.findIndex( ( q ) => q.id === p.id && q.options.price_id === p.options.price_id )
		);
}

/**
 * Builds the get-item-amounts request body for one item, priced against the order's current tax location, customer, and discounts.
 *
 * @param {Object} state Order overview state.
 * @param {Object} item  The item being priced: productId, priceId, quantity, amount, tax and subtotal.
 * @return {FormData} The request body.
 */
export function getItemAmountsBody( state, item ) {
	const { nonces } = globalThis.eddAdminOrderOverview;

	return toFormData( {
		action:     'edd-admin-order-get-item-amounts',
		nonce:      nonces.edd_admin_order_get_item_amounts,
		productId:  item.productId,
		priceId:    item.priceId,
		quantity:   item.quantity,
		amount:     item.amount,
		tax:        item.tax,
		subtotal:   item.subtotal,
		country:    getTaxCountry( state ),
		region:     getTaxRegion( state ),
		customerId: document.getElementById( 'customer_id' )?.value ?? 0,
		products:   getCartProducts( state.items, item ),
		discounts:  state.adjustments
			.filter( ( a ) => 'discount' === a.type )
			.map( ( a ) => a.typeId ),
	} );
}

/**
 * Fires the get-item-amounts AJAX request for every item in state, then
 * dispatches UPDATE_ITEM_AMOUNTS for each result only if every request
 * succeeded.  Wraps the whole batch in SET_FETCHING so callers don't need
 * to manage the loading flag.
 *
 * @param {Object}   state
 * @param {Function} dispatch
 * @return {Promise<void>}
 */
export async function updateAmounts( state, dispatch ) {
	dispatch( { type: 'SET_FETCHING', payload: true } );

	try {
		// Settle every request before writing any, so one failure leaves no item half-updated.
		const results = await Promise.allSettled(
			state.items.map( async ( item ) => {
				const body = getItemAmountsBody( state, item );
				const resp = await fetch( ajaxurl, { method: 'POST', body } );
				const json = await resp.json();

				if ( ! json.success ) {
					throw new Error( json.data?.message ?? 'Unknown error' );
				}

				const { adjustments: rawAdj = [], ...amounts } = json.data;

				// Map returned discount adjustments, preserving objectId link.
				const newAdjustments = rawAdj.map( ( adj ) => ( {
					...adj,
					objectId: item.id,
				} ) );

				return { id: item.id, amounts, newAdjustments };
			} )
		);

		const failure = results.find( ( r ) => 'rejected' === r.status );
		if ( failure ) {
			throw failure.reason;
		}

		results.forEach( ( { value } ) => dispatch( { type: 'UPDATE_ITEM_AMOUNTS', payload: value } ) );
		dispatch( { type: 'SET_AMOUNTS_ERROR', payload: false } );
	} finally {
		dispatch( { type: 'SET_FETCHING', payload: false } );
	}
}

/**
 * Recalculates amounts for callers with no error display of their own.
 *
 * A failure is flagged in state so the overview can report that the
 * displayed amounts are stale.
 *
 * @param {Object}   state
 * @param {Function} dispatch
 * @param {Function} [onSuccess] Called once the amounts are updated.
 */
export function recalculateAmounts( state, dispatch, onSuccess ) {
	updateAmounts( state, dispatch ).then(
		onSuccess,
		() => dispatch( { type: 'SET_AMOUNTS_ERROR', payload: true } )
	);
}
