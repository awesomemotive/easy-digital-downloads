/* global eddAdminOrderOverview */

/**
 * Order overview row for a single fee, credit, or discount.
 *
 * @package     EDD\Admin\Orders
 * @copyright   Copyright (c) 2026, Sandhills Development, LLC
 * @license     https://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       3.7.3
 */

import { useOrderOverview } from '../state/context.js';
import { updateAmounts } from '../utils/update-amounts.js';
import {
	getAdjustmentAmount,
	getAdjustmentTotal,
	getOrderItemForAdjustment,
} from '../state/selectors.js';

const OrderAdjustmentRow = ( { adj } ) => {
	const { state, dispatch } = useOrderOverview();
	const { i18n, urls } = eddAdminOrderOverview;

	const { currency } = state.formatters;
	const colspan = state.hasQuantity ? 2 : 1;

	const isDiscount = 'discount' === adj.type;
	const amount = getAdjustmentAmount( adj, state );
	const orderItem = getOrderItemForAdjustment( adj, state );

	const handleDelete = async ( e ) => {
		e.preventDefault();
		dispatch( { type: 'REMOVE_ADJUSTMENT', payload: adj.id } );
		const nextState = {
			...state,
			adjustments: state.adjustments.filter( ( a ) => a.id !== adj.id ),
		};
		try {
			await updateAmounts( nextState, dispatch );
		} catch {
			// The items still carry this adjustment's amounts, so restore it to keep the order consistent.
			dispatch( { type: 'ADD_ADJUSTMENT', payload: adj } );
			dispatch( { type: 'SET_AMOUNTS_ERROR', payload: true } );
		}
	};

	if ( isDiscount ) {
		return (
			<tr className="is-expanded">
				<td>
					<input type="hidden" value={adj.typeId} name={`discounts[${ adj.id }][type_id]`} />
					<input type="hidden" value={adj.description} name={`discounts[${ adj.id }][code]`} />
					<input type="hidden" value={amount} name={`discounts[${ adj.id }][subtotal]`} />
					<input type="hidden" value={amount} name={`discounts[${ adj.id }][total]`} />
				</td>

				<td className="column-primary" colSpan={colspan}>
					<div className="removable">
						{state.isAdding && (
							<button className="button-link delete" onClick={handleDelete} disabled={state.isFetching}>
								<span className="dashicons dashicons-no"></span>
								<span className="screen-reader-text">
									{i18n.removeDiscount}
								</span>
							</button>
						)}
						<div>
							{adj.typeId
								? <a href={`${ urls?.editDiscount ?? '' }&discount=${ adj.typeId }`}>{adj.description}</a>
								: adj.description
							}
							<br />
							<small>{i18n.discount}</small>
						</div>
					</div>
				</td>

				<td className="column-right" data-colname={i18n.amount}>
					&ndash;{currency.format( amount )}
				</td>
			</tr>
		);
	}

	const isFee = 'fee' === adj.type;

	let adjTypeLabel;
	if ( orderItem ) {
		adjTypeLabel = `${ i18n.fee }: ${ orderItem.productName }`;
	} else if ( isFee ) {
		adjTypeLabel = i18n.orderFee;
	} else {
		adjTypeLabel = i18n.orderCredit;
	}

	return (
		<tr className="is-expanded">
			<td></td>

			<td className="column-primary" colSpan={colspan}>
				<div className="removable">
					{state.isAdding && (
						<button className="button-link delete" onClick={handleDelete} disabled={state.isFetching}>
							<span className="dashicons dashicons-no"></span>
							<span className="screen-reader-text">
								{i18n.removeAdjustment.replace( '%s', adj.type )}
							</span>
						</button>
					)}
					<div>
						{'' !== adj.description && (
							<>{adj.description}<br /></>
						)}
						<small>
							{ adjTypeLabel }
						</small>
					</div>
				</div>
			</td>

			<td className="column-right" data-colname={i18n.amount}>
				{( !isFee || amount < 0 ) && <>&ndash;</>}
				{currency.format( amount )}
				<input type="hidden" value={adj.objectId} name={`adjustments[${ adj.id }][object_id]`} />
				<input type="hidden" value={adj.objectType} name={`adjustments[${ adj.id }][object_type]`} />
				<input type="hidden" value={adj.type} name={`adjustments[${ adj.id }][type]`} />
				<input type="hidden" value={adj.description} name={`adjustments[${ adj.id }][description]`} />
				<input type="hidden" value={adj.subtotal} name={`adjustments[${ adj.id }][subtotal]`} />
				<input type="hidden" value={adj.tax} name={`adjustments[${ adj.id }][tax]`} />
				<input type="hidden" value={getAdjustmentTotal( adj )} name={`adjustments[${ adj.id }][total]`} />
			</td>
		</tr>
	);
};

export default OrderAdjustmentRow;
