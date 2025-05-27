import { Dialog } from '../../../../packages/edd-backbone/src/dialog.js';
import RegionField from './region-field.js';
import { getChosenVars } from 'utils/chosen.js';
import TaxRate from '../models/tax-rate.js';

const FormAddTaxRate = Dialog.extend( {
	tagName: 'div',

	template: wp.template( 'edd-admin-tax-rates-table-dialog' ),

	events: {
		'click .edd-cancel': 'onCancel',
		'submit form': 'onSubmit',
		'change #tax_rate_country': 'setCountry',
		'keyup #tax_rate_region': 'setRegion',
		'change #tax_rate_region': 'setRegion',
		'change input[type="checkbox"]': 'setGlobal',
		'keyup #tax_rate_amount': 'setAmount',
		'change #tax_rate_amount': 'setAmount'
	},

	initialize () {
		// Call parent initialize first
		Dialog.prototype.initialize.call( this );

		// Set additional dialog options
		this.$el.dialog( 'option', {
			title: eddTaxRates.i18n.addNewRate,
			width: '350px',
			open: () => {
				// Initialize chosen after dialog is fully rendered
				this.$el.find( 'select' ).each( function () {
					const el = $( this );
					el.chosen( getChosenVars( el ) );
				} );

				// Make dialog content overflow visible after a short delay to ensure elements are rendered
				setTimeout( () => {
					$( '.edd-dialog' ).css( 'overflow', 'visible' );
					$( '.ui-dialog-content' ).css( 'overflow', 'visible' );
				}, 100 );
			}
		} );

		// Listen for model changes
		this.listenTo( this.model, 'change:country', this.updateRegion );
		this.listenTo( this.model, 'change:global', this.updateRegion );
	},

	prepare () {
		const { model } = this;

		return {
			model: model.toJSON(),
		};
	},

	/**
	 * Show a list of states or an input field.
	 */
	updateRegion: function () {
		const self = this;
		const regionWrapper = this.el.querySelector( '#tax_rate_region_wrapper' );

		const data = {
			action: 'edd_get_shop_states',
			country: this.model.get( 'country' ),
			nonce: eddTaxRates.nonce,
			field_name: 'tax_rate_region',
		};

		$.post( ajaxurl, data, function ( response ) {
			self.views.set( '#tax_rate_region_wrapper .edd-form-group__control', new RegionField( {
				states: response,
				global: self.model.get( 'global' ),
			} ) );

			// Only show the wrapper if we're not in global mode
			if ( !self.model.get( 'global' ) ) {
				regionWrapper.classList.remove( 'edd-hidden' );
			}
		} );
	},

	/**
	 * Set a country value.
	 *
	 * @param {Object} event Event.
	 */
	setCountry: function ( event ) {
		let country = event.target.options[ event.target.selectedIndex ].value;
		const regionGlobal = this.el.querySelector( '#tax_rate_region_global' );
		const regionGlobalCheckbox = regionGlobal.querySelector( 'input' );

		// Handle chosen dropdown
		if ( event.target.classList.contains( 'edd-select-chosen' ) ) {
			country = $( event.target ).val();
		}

		if ( '*' === country ) {
			country = '*';
			regionGlobalCheckbox.checked = true;
			this.model.set( 'region', '' );
			this.model.set( 'global', true );
			regionGlobal.classList.add( 'edd-hidden' );
			regionGlobalCheckbox.readOnly = true;
			regionGlobalCheckbox.disabled = true;
		} else {
			regionGlobal.classList.remove( 'edd-hidden' );
			regionGlobalCheckbox.disabled = false;
			regionGlobalCheckbox.readOnly = false;
		}

		this.model.set( 'country', country );
	},

	/**
	 * Set a region value.
	 *
	 * @param {Object} event Event.
	 */
	setRegion: function ( event ) {
		let value = false;

		if ( event.target.value ) {
			value = event.target.value;
		} else {
			value = event.target.options[ event.target.selectedIndex ].value;
		}

		this.model.set( 'region', value );
	},

	/**
	 * Set a global scope.
	 *
	 * @param {Object} event Event.
	 */
	setGlobal: function ( event ) {
		let isChecked = event.target.checked;
		const regionWrapper = this.el.querySelector( '#tax_rate_region_wrapper' );

		this.model.set( 'global', isChecked );
		if ( true === isChecked ) {
			this.model.set( 'region', '' );
			regionWrapper.classList.add( 'edd-hidden' );
		}
		// Don't remove the hidden class here - it will be handled in updateRegion
	},

	/**
	 * Set an amount value.
	 *
	 * @param {Object} event Event.
	 */
	setAmount: function ( event ) {
		this.model.set( 'amount', event.target.value );
	},

	onCancel ( e ) {
		e.preventDefault();
		this.closeDialog();
	},

	onSubmit ( e ) {
		e.preventDefault();

		const { i18n } = eddTaxRates;

		if ( !this.model.get( 'country' ) ) {
			alert( i18n.emptyCountry );
			return;
		}

		let addingRegion = this.model.get( 'region' );
		let addingCountry = this.model.get( 'country' );
		let addingGlobal = '' === this.model.get( 'region' );

		// For the purposes of this query, the * is really an empty query.
		if ( '*' === addingCountry ) {
			addingCountry = '';
			addingRegion = '';
			addingGlobal = false;
		}

		const existingCountryWide = this.collection.where( {
			region: addingRegion,
			country: addingCountry,
			global: addingGlobal,
			status: 'active',
		} );

		if ( existingCountryWide.length > 0 ) {
			const countryString = '' === addingCountry
				? '*'
				: addingCountry;

			const regionString = '' === addingRegion
				? ''
				: ': ' + addingRegion;

			const taxRateString = countryString + regionString;

			alert( i18n.duplicateRate.replace( '%s', `"${ taxRateString }"` ) );
			return;
		}

		if ( this.model.get( 'amount' ) < 0 ) {
			alert( i18n.negativeTax );
			return;
		}

		if ( this.model.get( 'amount' ) == 0 ) {
			confirm( i18n.emptyTax );
		}

		// Merge cid as ID to make this a unique model.
		this.collection.add( _.extend(
			this.model.attributes,
			{
				id: this.model.cid,
			}
		) );

		// Reset model
		this.model = new TaxRate( {
			global: true,
			unsaved: true,
		} );

		this.closeDialog();
	}
} );

export default FormAddTaxRate;
