/* global eddAdminOrderOverview */

/**
 * Order overview row for a single order item.
 *
 * @package     EDD\Admin\Orders
 * @copyright   Copyright (c) 2026, Sandhills Development, LLC
 * @license     https://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       3.7.3
 */

import { useState } from '@wordpress/element';
import { useOrderOverview } from '../state/context.js';
import { updateAmounts } from '../utils/update-amounts.js';
import { isItemAdjustment } from '../state/reducer.js';
import CopyDownloadLinkDialog from './CopyDownloadLinkDialog.js';
import {
	getItemSubtotal,
	getItemTax,
	getItemTotal,
	getItemDiscountAmount,
	getTaxConfig,
} from '../state/selectors.js';

const OrderItem = ( { item } ) => {
	const { state, dispatch } = useOrderOverview();
	const { i18n, urls } = eddAdminOrderOverview;

	const [ copyDialogOpen, setCopyDialogOpen ] = useState( false );

	const { currency } = state.formatters;

	const subtotal         = getItemSubtotal( item, state );
	const tax              = getItemTax( item, state );
	const isAdjustManually = item._isAdjustingManually;

	const handleDelete = async ( e ) => {
		e.preventDefault();
		const itemAdjustments = state.adjustments.filter( ( adj ) => isItemAdjustment( adj, item.id ) );
		dispatch( { type: 'REMOVE_ITEM', payload: item.id } );
		const nextState = {
			...state,
			items: state.items.filter( ( i ) => i.id !== item.id ),
			adjustments: state.adjustments.filter( ( adj ) => ! isItemAdjustment( adj, item.id ) ),
		};
		try {
			await updateAmounts( nextState, dispatch );
		} catch {
			// The remaining items still reflect this item, so restore it to keep the order consistent.
			dispatch( { type: 'RESTORE_ITEM', payload: { item, adjustments: itemAdjustments } } );
			dispatch( { type: 'SET_AMOUNTS_ERROR', payload: true } );
		}
	};

	const handleCopyLink = ( e ) => {
		e.preventDefault();
		setCopyDialogOpen( true );
	};

	const isRefunded = [ 'refunded', 'partially_refunded' ].includes( item.status );

	return (
		<tr className={ `edd-order-overview-summary__item` }>
			<td className="has-row-actions column-primary">
				<div className="removable">
					{ state.isAdding && (
						<button className="button-link delete" onClick={ handleDelete } disabled={ state.isFetching }>
							<span className="dashicons dashicons-no" id="remove-item"></span>
							<span className="screen-reader-text">
								{ i18n.removeItem }
							</span>
						</button>
					) }

					<div className="edd-order-overview-summary__items-name">
						<a
							href={ `${ urls?.editDownload ?? '' }&post=${ item.productId }` }
							className="row-title"
						>
							{ item.productName }
						</a>

						{ ! state.isRefund && isRefunded && (
							<>
								{ ' — ' }
								{ item.statusLabel }
							</>
						) }

						<div className="row-actions">
							{ null !== getTaxConfig( state ) && (
								<span className="text">
									<strong>{ `${ i18n.tax }:` }</strong>
									{ ' ' }
									{ currency.format( tax ) }
									{ isAdjustManually && <>&dagger;</> }
								</span>
							) }

							{ ! state.isAdding && item.deliverable && (
								<span>
									<button className="button-link copy-download-link" onClick={ handleCopyLink }>
										{ i18n.copyDownloadLinks }
									</button>
								</span>
							) }
						</div>
					</div>

					<button type="button" className="toggle-row">
						<span className="screen-reader-text">
							{ i18n.showMoreDetails }
						</span>
					</button>
				</div>
			</td>

			<td data-colname={ i18n.unitPrice }>
				{ currency.format( item.amount ) }
				{ isAdjustManually && <>&dagger;</> }
			</td>

			{ state.hasQuantity && (
				<td data-colname={ i18n.quantity }>
					{ item.quantity }
				</td>
			) }

			<td className="column-right" data-colname={ i18n.amount }>
				{ currency.format( subtotal ) }
				{ isAdjustManually && <>&dagger;</> }
				<input type="hidden" value={ item.productId } name={ `downloads[${ item.id }][id]` } />
				{ /* priceId is null for downloads without variable prices, which React rejects as a value. */ }
				<input type="hidden" value={ item.priceId ?? '' } name={ `downloads[${ item.id }][price_id]` } />
				<input type="hidden" value={ item.quantity }  name={ `downloads[${ item.id }][quantity]` } />
				<input type="hidden" value={ item.amount }    name={ `downloads[${ item.id }][amount]` } />
				<input type="hidden" value={ getItemDiscountAmount( item ) } name={ `downloads[${ item.id }][discount]` } />
				<input type="hidden" value={ tax }            name={ `downloads[${ item.id }][tax]` } />
				<input type="hidden" value={ subtotal }       name={ `downloads[${ item.id }][subtotal]` } />
				<input type="hidden" value={ getItemTotal( item, state ) } name={ `downloads[${ item.id }][total]` } />
			</td>

			{ copyDialogOpen && (
				<CopyDownloadLinkDialog
					orderId={ item.orderId }
					productId={ item.productId }
					priceId={ item.priceId }
					onClose={ () => setCopyDialogOpen( false ) }
				/>
			) }
		</tr>
	);
};

export default OrderItem;
