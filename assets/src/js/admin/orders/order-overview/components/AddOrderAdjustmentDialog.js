/* global eddAdminOrderOverview */

/**
 * Dialog for adding a fee or credit to an order.
 *
 * @package     EDD\Admin\Orders
 * @copyright   Copyright (c) 2026, Sandhills Development, LLC
 * @license     https://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       3.7.3
 */

import { useState } from '@wordpress/element';
import Dialog from '../../../components/dialog/Dialog.js';
import { useOrderOverview } from '../state/context.js';
import { getTaxConfig, formatTaxLocation } from '../state/selectors.js';

const TITLE_ID = 'edd-add-order-adjustment-title';

const AddOrderAdjustmentDialog = ( { onClose } ) => {
	const { state, dispatch } = useOrderOverview();
	const { i18n, currencyPosition } = eddAdminOrderOverview;

	const [ type, setType ]               = useState( 'fee' );
	const [ objectType, setObjectType ]   = useState( 'order' );
	const [ objectId, setObjectId ]       = useState( 0 );
	const [ amountManual, setAmountManual ] = useState( '' );
	const [ description, setDescription ] = useState( '' );
	const [ isTaxed, setIsTaxed ]         = useState( true );
	const [ subtotal, setSubtotal ]       = useState( 0 );

	const { number } = state.formatters;
	const taxConfig = getTaxConfig( state );
	const symbolBefore = 'before' === ( currencyPosition ?? 'before' );
	const currencySymbol = state.order.currencySymbol ?? '';

	const handleTypeChange = ( e ) => {
		const val = e.target.value;
		setType( val );
		if ( 'credit' === val ) {
			setObjectType( 'order' );
			setObjectId( 0 );
		}
	};

	const handleObjectTypeChange = ( e ) => {
		const val = e.target.value;
		if ( 'order' === val ) {
			setObjectType( 'order' );
			setObjectId( 0 );
		} else {
			setObjectType( 'order_item' );
			setObjectId( val.replace( 'order_item_', '' ) );
		}
	};

	const handleAmountChange = ( e ) => {
		const rawVal = e.target.value;

		setAmountManual( rawVal );
		setSubtotal( number.unformat( rawVal ) );
	};

	const handleTaxToggle = ( e ) => {
		setIsTaxed( e.target.checked );
	};

	// Derived at submit so the amount, type, and tax checkbox can change in any order.
	const getFeeTax = () => {
		if (
			! isTaxed ||
			'fee' !== type ||
			! taxConfig ||
			'' === taxConfig.country ||
			'' === taxConfig.rate
		) {
			return 0;
		}

		return number.unformat( number.format( subtotal * ( taxConfig.rate / 100 ) ) );
	};

	const handleSubmit = ( e ) => {
		e.preventDefault();

		if ( 0 === subtotal ) {
			return;
		}

		const newAdj = {
			id:          `adj-${ Date.now() }`,
			typeId:      `adj-${ Date.now() }-type`,
			objectId:    'order_item' === objectType ? objectId : 0,
			objectType,
			type,
			description,
			subtotal,
			tax:         getFeeTax(),
			isTaxable:   isTaxed,
			amountManual,
		};

		dispatch( { type: 'ADD_ADJUSTMENT', payload: newAdj } );
		onClose();
	};

	const showTax          = null !== taxConfig && 'fee' === type;
	const showNoTaxWarning = 'fee' === type && '' === taxConfig?.country;

	const orderItems = state.items;

	return (
		<Dialog
			open={ true }
			onClose={ onClose }
			title={ i18n.addAdjustment }
			titleId={ TITLE_ID }
		>
			<div className="edd-order-overview-modal">
				<form className="edd-order-overview-add-adjustment" onSubmit={ handleSubmit }>
					<fieldset>
						<legend className="edd-form-group__label">{ i18n.type }</legend>

						<div className="edd-form-group">
							<div className="edd-form-group__control">
								<input
									type="radio"
									id="edd-adj-fee"
									name="type"
									className="edd-form-group__input"
									value="fee"
									checked={ 'fee' === type }
									onChange={ handleTypeChange }
								/>
								<label htmlFor="edd-adj-fee">{ i18n.fee }</label>
							</div>
						</div>

						<div className="edd-form-group">
							<div className="edd-form-group__control">
								<input
									type="radio"
									id="edd-adj-credit"
									name="type"
									className="edd-form-group__input"
									value="credit"
									checked={ 'credit' === type }
									onChange={ handleTypeChange }
								/>
								<label htmlFor="edd-adj-credit">{ i18n.credit }</label>
							</div>
						</div>
					</fieldset>

					{ 'fee' === type && orderItems.length > 0 && (
						<div className="edd-form-group">
							<label htmlFor="edd-adj-object-type">
								{ i18n.applyTo }
							</label>
							<div className="edd-form-group__control">
								<select
									id="edd-adj-object-type"
									className="edd-select"
									required
									value={ 'order_item' === objectType ? `order_item_${ objectId }` : objectType }
									onChange={ handleObjectTypeChange }
								>
									<option value="order">{ i18n.entireOrder }</option>
									{ orderItems.map( ( item ) => (
										<option
											key={ item.id }
											value={ `order_item_${ item.id }` }
										>
											{ item.productName }
										</option>
									) ) }
								</select>
							</div>
						</div>
					) }

					<div className="edd-form-group">
						<label htmlFor="edd-adj-amount">{ i18n.amount }</label>
						<div className="edd-form-group__control edd-amount-type-wrapper">
							{ symbolBefore && <span className="edd-input__symbol edd-input__symbol--prefix">{ currencySymbol }</span> }
							<input
								type="text"
								id="edd-adj-amount"
								value={ amountManual }
								required
								onChange={ handleAmountChange }
							/>
							{ ! symbolBefore && <span className="edd-input__symbol edd-input__symbol--suffix">{ currencySymbol }</span> }
						</div>
					</div>

					{ showTax && (
						<div className="edd-form-group">
							<div className="edd-form-group__control edd-toggle">
								<input
									type="checkbox"
									id="edd-adj-no-tax"
									checked={ isTaxed }
									onChange={ handleTaxToggle }
								/>
								<label htmlFor="edd-adj-no-tax">
									{ i18n.applyTaxToFee }
									{ taxConfig?.country && (
										<>
											<br />
											<small>
												{ i18n.taxRateLabel.replace( '%s', formatTaxLocation( taxConfig ) ) }
											</small>
										</>
									) }
								</label>
							</div>
						</div>
					) }

					{ showNoTaxWarning && (
						<div className="notice notice-warning">
							<p>
								<strong>{ i18n.noTaxRate }</strong>
								<br />
								{ i18n.taxFromAddress }
							</p>
							<p>
								<button
									className="button button-secondary"
									type="button"
									onClick={ () => {
										onClose();
										document.querySelector( '[href="#edd_general_address"]' )?.click();
										document.getElementById( 'edd_order_address_country' )?.focus();
									} }
								>
									{ i18n.setAddress }
								</button>
							</p>
						</div>
					) }

					<div className="edd-form-group">
						<label htmlFor="edd-adj-description">{ i18n.description }</label>
						<div className="edd-form-group__control">
							<input
								type="text"
								id="edd-adj-description"
								value={ description }
								onChange={ ( e ) => setDescription( e.target.value ) }
							/>
						</div>
					</div>

					<p className="submit">
						<input
							id="edd-adj-submit"
							type="submit"
							className="button button-primary"
							value={ i18n.addAdjustment }
							disabled={ 0 === subtotal }
						/>
					</p>
				</form>
			</div>
		</Dialog>
	);
};

export default AddOrderAdjustmentDialog;
