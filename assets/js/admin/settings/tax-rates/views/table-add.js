/* global wp */

/**
 * Internal dependencies.
 */
import TaxRate from './../models/tax-rate.js';
import RegionField from './../views/region-field.js';
import { getChosenVars } from 'utils/chosen.js';

/**
 * Add a new rate "form".
 */
const TableAdd = wp.Backbone.View.extend( {
	// Use <tfoot>
	tagName: 'tfoot',

	// Set class.
	className: 'add-new',

	// See https://codex.wordpress.org/Javascript_Reference/wp.template
	template: wp.template( 'edd-admin-tax-rates-table-add' ),

	// Watch events.
	events: {
		'click button': 'addTaxRate',
		'keypress': 'maybeAddTaxRate',

		'change #tax_rate_country': 'setCountry',

		// Can be select or input.
		'keyup #tax_rate_region': 'setRegion',
		'change #tax_rate_region': 'setRegion',

		'change input[type="checkbox"]': 'setGlobal',

		// Can be click increase or keyboard.
		'keyup #tax_rate_amount': 'setAmount',
		'change #tax_rate_amount': 'setAmount',
	},

	/**
	 * Set initial state and bind changes to model.
	 */
	initialize: function() {
		this.model = new TaxRate( {
			global: true,
			unsaved: true,
		} );

		this.listenTo( this.model, 'change:country', this.updateRegion );
		this.listenTo( this.model, 'change:global', this.updateRegion );
	},

	/**
	 * Render. Only overwritten so we can reinit chosen once cleared.
	 */
	render: function() {
		wp.Backbone.View.prototype.render.apply( this, arguments );

		this.$el.find( 'select' ).each( function() {
			const el = $( this );
			el.chosen( getChosenVars( el ) );
		} );

		return this;
	},

	/**
	 * Show a list of states or an input field.
	 */
	updateRegion: function() {
		const self = this;

		const data = {
			action: 'edd_get_shop_states',
			country: this.model.get( 'country' ),
			nonce: eddTaxRates.nonce,
			field_name: 'tax_rate_region',
		};

		$.post( ajaxurl, data, function( response ) {
			self.views.set( '#tax_rate_region_wrapper', new RegionField( {
				states: response,
				global: self.model.get( 'global' ),
			} ) );
		} );
	},

	/**
	 * Set a country value.
	 *
	 * @param {Object} event Event.
	 */
	setCountry: function( event ) {
		let country = event.target.options[ event.target.selectedIndex ].value;
		let regionGlobalCheckbox = document.getElementById( "tax_rate_region_global" );
		if ( 'all' === country ) {
			country = '*';
			regionGlobalCheckbox.checked  = true;
			this.model.set( 'region', '' );
			this.model.set( 'global', true );
			regionGlobalCheckbox.readOnly = true;
			regionGlobalCheckbox.disabled = true;
		} else {
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
	setRegion: function( event ) {
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
	setGlobal: function( event ) {
		let isChecked = event.target.checked;
		this.model.set( 'global', isChecked );
		if ( true === isChecked ) {
			this.model.set( 'region', '' );
		}
	},

	/**
	 * Set an amount value.
	 *
	 * @param {Object} event Event.
	 */
	setAmount: function( event ) {
		this.model.set( 'amount', event.target.value );
	},

	/**
	 * Monitors keyepress for "Enter" key.
	 *
	 * We cannot use the `submit` event because we cannot nest <form>
	 * elements inside the settings API.
	 *
	 * @param {Object} event Keypress event.
	 */
	maybeAddTaxRate: function( event ) {
		if ( 13 !== event.keyCode ) {
			return;
		}

		this.addTaxRate( event );
	},

	/**
	 * Add a single rate when the "form" is submitted.
	 *
	 * @param {Object} event Event.
	 */
	addTaxRate: function( event ) {
		event.preventDefault();

		const { i18n } = eddTaxRates;

		if ( ! this.model.get( 'country' ) ) {
			alert( i18n.emptyCountry );

			return;
		}

		let addingRegion  = this.model.get( 'region' );
		let addingCountry = this.model.get( 'country' );
		let addingGlobal  = '' === this.model.get( 'region' );

		// For the purposes of this query, the * is really an empty query.
		if ( '*' === addingCountry ) {
			addingCountry = '';
			addingRegion  = '';
			addingGlobal  = false;
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

		if ( this.model.get( 'amount' ) <= 0 ) {
			alert( i18n.emptyTax );

			return;
		}

		// Merge cid as ID to make this a unique model.
		this.collection.add( _.extend(
			this.model.attributes,
			{
				id: this.model.cid,
			}
		) );

		this.render();
		this.initialize();
	},
} );

export default TableAdd;
