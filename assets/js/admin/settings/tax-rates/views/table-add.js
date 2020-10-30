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
		this.model.set( 'country', event.target.options[ event.target.selectedIndex ].value );
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
		this.model.set( 'global', event.target.checked );
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

		const existingCountryWide = this.collection.filter( ( rate ) => (
			'' === this.model.get( 'region' ) && (
				this.model.get( 'country' ) === rate.get( 'country' ) &&
				true === rate.get( 'global' ) &&
				'active' === rate.get( 'status' )
			)
		) );

		const {
			i18n,
		} = eddTaxRates;

		if ( existingCountryWide.length > 0 ) {
			alert( i18n.multipleCountryWide.replace( '%s', this.model.get( 'country' ) ) );

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
