/**
 * Tax rates state reducer.
 *
 * @package     EDD\Admin\TaxRates
 * @copyright   Copyright (c) 2026, Sandhills Development, LLC
 * @license     https://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       3.7.3
 */

let nextId = 1;

export const initialState = {
	rates: [],
	selected: [],
	showAll: false,
	isDirty: false,
};

export function reducer( state, action ) {
	switch ( action.type ) {
		case 'INIT_RATES':
			return { ...state, rates: action.payload };

		case 'ADD_RATE': {
			const id = `new-${ nextId++ }`;
			return {
				...state,
				rates: [ ...state.rates, { ...action.payload, id, unsaved: true } ],
				isDirty: true,
			};
		}

		case 'REMOVE_RATE':
			return {
				...state,
				rates: state.rates.filter( ( r ) => r.id !== action.payload ),
				selected: state.selected.filter( ( id ) => id !== action.payload ),
				isDirty: true,
			};

		case 'SET_STATUS': {
			const { id, status } = action.payload;
			return {
				...state,
				rates: state.rates.map( ( r ) => r.id === id ? { ...r, status } : r ),
				isDirty: true,
			};
		}

		case 'BULK_STATUS': {
			const { status, ids } = action.payload;
			return {
				...state,
				rates: state.rates.map( ( r ) =>
					ids.includes( r.id ) ? { ...r, status } : r
				),
				isDirty: true,
			};
		}

		case 'SELECT_RATE': {
			const { id, checked } = action.payload;
			const selected = checked
				? [ ...state.selected, id ]
				: state.selected.filter( ( s ) => s !== id );
			return { ...state, selected };
		}

		case 'SELECT_ALL': {
			const selected = action.payload ? state.rates.map( ( r ) => r.id ) : [];
			return { ...state, selected };
		}

		case 'TOGGLE_SHOW_ALL':
			return { ...state, showAll: ! state.showAll };

		case 'MARK_DIRTY':
			return { ...state, isDirty: true };

		case 'MARK_CLEAN':
			return { ...state, isDirty: false };

		default:
			return state;
	}
}
