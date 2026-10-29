/* global eddAdminOrderOverview */

/**
 * Dialog for adding a discount to an order.
 *
 * @package     EDD\Admin\Orders
 * @copyright   Copyright (c) 2026, Sandhills Development, LLC
 * @license     https://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       3.7.3
 */

import { useState } from '@wordpress/element';
import Dialog from '../../../components/dialog/Dialog.js';
import TomSelectField from '../../../components/tom-select/index.js';
import { useOrderOverview } from '../state/context.js';
import { updateAmounts } from '../utils/update-amounts.js';

const TITLE_ID = 'edd-add-order-discount-title';

const AddOrderDiscountDialog = ( { onClose } ) => {
	const { state, dispatch } = useOrderOverview();
	const { i18n, initialDiscounts = [] } = eddAdminOrderOverview;

	const [ typeId, setTypeId ]         = useState( 0 );
	const [ code, setCode ]             = useState( '' );
	const [ isDuplicate, setDuplicate ] = useState( false );
	const [ error, setError ]           = useState( '' );

	const handleChange = ( value ) => {
		if ( ! value ) {
			setTypeId( 0 );
			setCode( '' );
			setDuplicate( false );
			return;
		}

		const id       = Number.parseInt( value, 10 );
		const selectEl = document.getElementById( 'edd-add-discount-select' );
		const tsOption = selectEl?.tomselect?.options[ value ];
		const initial  = initialDiscounts.find( ( d ) => d.id === id );
		const dup      = state.adjustments.some( ( a ) => 'discount' === a.type && a.typeId === id );

		setTypeId( id );
		setCode( tsOption?.code ?? initial?.code ?? '' );
		setDuplicate( dup );
	};

	const handleSubmit = async ( e ) => {
		e.preventDefault();

		if ( 0 === typeId || isDuplicate ) {
			return;
		}

		setError( '' );

		const newAdj = {
			id:          `disc-${ Date.now() }`,
			typeId,
			type:        'discount',
			description: code,
			objectId:    0,
			objectType:  'order',
			subtotal:    0,
			tax:         0,
			total:       0,
		};

		dispatch( { type: 'ADD_ADJUSTMENT', payload: newAdj } );

		const nextState = { ...state, adjustments: [ ...state.adjustments, newAdj ] };
		try {
			await updateAmounts( nextState, dispatch );
			onClose();
		} catch ( err ) {
			dispatch( { type: 'REMOVE_ADJUSTMENT', payload: newAdj.id } );
			setError( err.message );
		}
	};

	const disabled = 0 === typeId || isDuplicate || state.isFetching;

	// A failed add reports its error in this dialog, so it must still be open when the request settles.
	const handleClose = () => {
		if ( ! state.isFetching ) {
			onClose();
		}
	};

	return (
		<Dialog
			open={ true }
			onClose={ handleClose }
			title={ i18n.addDiscount }
			titleId={ TITLE_ID }
			className="edd-modal--overflow-visible"
		>
			<div className="edd-order-overview-modal">
				<form className="edd-order-overview-add-discount" onSubmit={ handleSubmit }>
					{ error && (
						<div className="notice notice-error">
							<p>{ error }</p>
						</div>
					) }

					<div className="edd-form-group">
						<label htmlFor="edd-add-discount-select">
							{ i18n.discount }
						</label>
						<div className="edd-form-group__control">
							<TomSelectField
								id="edd-add-discount-select"
								data-placeholder={ i18n.chooseDiscount }
								data-search-type="discount"
								data-search-filter-invalid="1"
								onChange={ handleChange }
							>
								<option value=""></option>
								{ initialDiscounts.map( ( d ) => (
									<option key={ d.id } value={ d.id }>{ d.name }</option>
								) ) }
							</TomSelectField>

							{ isDuplicate && (
								<span className="edd-order-overview-error">
									{ i18n.discountAlreadyApplied }
								</span>
							) }
						</div>
					</div>

					<p className="submit">
						<button
							type="submit"
							className={ `button button-primary${ state.isFetching ? ' updating-message' : '' }` }
							disabled={ disabled }
						>
							{ i18n.addDiscount }
						</button>
					</p>
				</form>
			</div>
		</Dialog>
	);
};

export default AddOrderDiscountDialog;
