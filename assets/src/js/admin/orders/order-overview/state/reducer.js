/**
 * Order overview state reducer.
 *
 * @package     EDD\Admin\Orders
 * @copyright   Copyright (c) 2026, Sandhills Development, LLC
 * @license     https://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       3.7.3
 */

import { getTaxConfig } from './selectors.js';

export const initialState = {
	isAdding: false,
	isRefund: false,
	hasTax: 'none',
	hasNewTaxRate: false,
	hasQuantity: false,
	hasDiscounts: false,
	isFetching: false,
	hasAmountsError: false,
	order: {
		status: '',
		currency: '',
		currencySymbol: '',
		subtotal: 0,
		discount: 0,
		tax: 0,
		total: 0,
		gateway: '',
	},
	formatters: {
		currency: null,
		number: null,
	},
	items: [],
	adjustments: [],
	refunds: [],
};

/**
 * Whether an order adjustment is applied to a specific order item.
 *
 * @param {Object} adj    Order adjustment.
 * @param {string} itemId Order item ID.
 * @return {boolean}
 */
export function isItemAdjustment( adj, itemId ) {
	return 'order_item' === adj.objectType && adj.objectId === itemId;
}

export function reducer( state, action ) {
	switch ( action.type ) {

		case 'SET_TAX': {
			const hasChanged = JSON.stringify( state.hasTax ) !== JSON.stringify( action.payload );
			return {
				...state,
				hasTax: action.payload,
				hasNewTaxRate: state.hasNewTaxRate || hasChanged,
			};
		}

		case 'DISMISS_TAX_NOTICE':
			return { ...state, hasNewTaxRate: false };

		case 'SET_FETCHING':
			return { ...state, isFetching: action.payload };

		case 'SET_AMOUNTS_ERROR':
			return { ...state, hasAmountsError: action.payload };

		case 'ADD_ITEM':
			return { ...state, items: [ ...state.items, action.payload ] };

		// A fee applied to the item has nothing to attach to once the item is gone, so it goes too.
		case 'REMOVE_ITEM':
			return {
				...state,
				items: state.items.filter( ( item ) => item.id !== action.payload ),
				adjustments: state.adjustments.filter(
					( adj ) => ! isItemAdjustment( adj, action.payload )
				),
			};

		case 'RESTORE_ITEM':
			return {
				...state,
				items: [ ...state.items, action.payload.item ],
				adjustments: [ ...state.adjustments, ...action.payload.adjustments ],
			};

		case 'UPDATE_ITEM_AMOUNTS': {
			// Update one item's amounts + replace its item-level discount adjustments.
			const { id, amounts, newAdjustments } = action.payload;
			const { amount = 0, discount = 0, tax = 0, subtotal = 0, total = 0 } = amounts;

			const items = state.items.map( ( item ) => {
				if ( item.id !== id ) {
					return item;
				}

				// Preserve non-discount item-level adjustments.
				const preserved = ( item.adjustments ?? [] ).filter(
					( a ) => [ 'fee', 'credit' ].includes( a.type )
				);

				let updated;
				if ( item._isAdjustingManually ) {
					updated = { ...item, discount, adjustments: [ ...newAdjustments, ...preserved ] };
				} else {
					updated = {
						...item,
						amount,
						discount,
						tax,
						subtotal,
						total,
						adjustments: [ ...newAdjustments, ...preserved ],
					};
				}
				return updated;
			} );

			return { ...state, items };
		}

		case 'ADD_ADJUSTMENT': {
			const adj = action.payload;
			let items = state.items;

			// If fee applies to a specific order item, add it there too.
			if ( 'order_item' === adj.objectType ) {
				items = state.items.map( ( item ) => {
					if ( item.id !== adj.objectId ) {
						return item;
					}
					return {
						...item,
						adjustments: [ ...( item.adjustments ?? [] ), adj ],
					};
				} );
			}

			return {
				...state,
				items,
				adjustments: [ ...state.adjustments, adj ],
			};
		}

		case 'REMOVE_ADJUSTMENT': {
			const id = action.payload;
			const adj = state.adjustments.find( ( a ) => a.id === id );

			const items = adj?.objectType === 'order_item'
				? state.items.map( ( item ) => ( {
					...item,
					adjustments: ( item.adjustments ?? [] ).filter( ( a ) => a.id !== id ),
				} ) )
				: state.items;

			return {
				...state,
				items,
				adjustments: state.adjustments.filter( ( a ) => a.id !== id ),
			};
		}

		case 'UPDATE_ADJUSTMENT_TAX': {
			// Recalculate tax on fee adjustments using the current tax rate.
			const taxConfig = getTaxConfig( state );
			if ( ! taxConfig || '' === taxConfig.country || '' === taxConfig.rate ) {
				return state;
			}

			const { number } = state.formatters;
			const taxRate = taxConfig.rate / 100;

			const adjustments = state.adjustments.map( ( adj ) => {
				if ( 'fee' !== adj.type || false === adj.isTaxable ) {
					return adj;
				}
				const taxAmount = number.unformat( number.format( adj.subtotal * taxRate ) );
				return { ...adj, tax: taxAmount };
			} );

			return { ...state, adjustments };
		}

		default:
			return state;
	}
}
