/* global _, Backbone, wp, eddTaxRates, ajaxurl */

// These should be defined globally for all of EDD.
var eddTaxRatesChosenVars = {
	disable_search_threshold:  13,
	search_contains:           true,
	inherit_select_classes:    true,
	single_backstroke_delete:  false,
	placeholder_text_single:   edd_vars.one_option,
	placeholder_text_multiple: edd_vars.one_or_more_option,
	no_results_text:           edd_vars.no_results_text
};

/**
 * Model a tax rate.
 */
var TaxRate = Backbone.Model.extend( {
	defaults: {
		id: '',
		country: '',
		region: '',
		global: true,
		amount: 0,
		status: 'active',
		unsaved: false,
		selected: false,
	},

	/**
	 * Format a rate amount (adds a %)
	 *
	 * @todo This should support dynamic decimal types.
	 */
	formattedAmount: function() {
		var amount = 0;

		if ( this.get( 'amount' ) ) {
			amount = parseFloat( this.get( 'amount' ) ).toFixed( 2 );
		}

		return amount + '%';
	},
} );

/**
 * A collection of multiple tax rates.
 */
var TaxRates = Backbone.Collection.extend( {
	// Map the model.
	model: TaxRate,

	/**
	 * Initial state.
	 */
	initialize: function() {
		this.showAll = false;
		this.selected = [];
	},
} );

/**
 * Empty tax rates table.
 */
var TaxRatesTableEmpty = wp.Backbone.View.extend( {
	// Insert as a <tr>
	tagName: 'tr',

	// See https://codex.wordpress.org/Javascript_Reference/wp.template
	template: wp.template( 'edd-admin-tax-rates-table-row-empty' ),
} );

/**
 * A row inside a table of rates.
 */
var TaxRatesTableRow = wp.Backbone.View.extend( {
	// Insert as a <tr>
	tagName: 'tr',

	// Set class.
	className: function() {
		return 'edd-tax-rate-row edd-tax-rate-row--' + this.model.get( 'status' );
	},

	// See https://codex.wordpress.org/Javascript_Reference/wp.template
	template: wp.template( 'edd-admin-tax-rates-table-row' ),

	// Watch events.
	events: {
		'click .remove': 'remove',
		'click .activate': 'activate',
		'click .deactivate': 'deactivate',
		'change [type="checkbox"]': 'select',
	},

	/**
	 * Bind model to view.
	 *
	 * @return {undefined}
	 */
	initialize: function() {
		this.listenTo( this.model, 'change', this.render );
	},

	/**
	 * Render
	 */
	render: function() {
		this.$el.html( this.template( _.extend(
			this.model.toJSON(),
			{
				formattedAmount: this.model.formattedAmount(),
			}
		) ) );

		// Ensure the wrapper class has the new name.
		this.$el.attr( 'class', _.result( this, 'className' ) );
	},

	/**
	 * Remove a rate (can only be done if it has not been saved to the database).
	 *
	 * @param {Object} event Event.
	 */
	deactivate: function( event ) {
		event.preventDefault();

		this.model.destroy();
	},

	/**
	 * Activate a rate.
	 *
	 * @param {Object} event Event.
	 */
	activate: function( event ) {
		event.preventDefault();

		this.model.set( 'status', 'active' );
	},

	/**
	 * Deactivate a rate.
	 *
	 * @param {Object} event Event.
	 */
	deactivate: function( event ) {
		event.preventDefault();

		this.model.set( 'status', 'inactive' );
	},

	/**
	 * Select or deselect for bulk actions.
	 *
	 * @param {Object} event Event.
	 */
	select: function( event ) {
		var self = this;
		var checked = event.target.checked;

		if ( ! checked ) {
			this.collection.selected = _.reject( this.collection.selected, function( cid ) {
				return cid === self.model.cid;
			} );
		} else {
			this.collection.selected.push( this.model.cid );
		}
	},
} );

/**
 * A bunch of rows inside a table of rates.
 */
var TaxRatesTableRows = wp.Backbone.View.extend( {
	// Insert as a <tbody>
	tagName: 'tbody',

	/**
	 * Bind model to view.
	 *
	 * @return {undefined}
	 */
	initialize: function() {
		this.listenTo( this.collection, 'change', this.render );
		this.listenTo( this.collection, 'sort', this.render );

		// Rerender the whole list so the "empty" placeholder can be removed.
		this.listenTo( this.collection, 'add', this.render );
	},

	/**
	 * Render a collection of rows.
	 */
	render: function() {
		var self = this;

		// Clear to handle sorting.
		this.views.remove();

		// Show empty placeholder.
		if ( 0 === this.collection.models.length ) {
			return this.views.add( new TaxRatesTableEmpty() );
		}

		// Remove inactive if needed.
		var toShow = this.collection.models;

		if ( ! this.collection.showAll ) {
			toShow = _.filter( this.collection.models, function( model ) {
				return 'active' === model.get( 'status' );
			} );
		}

		// Add items.
		_.each( toShow, function( rate ) {
			self.views.add( new TaxRatesTableRow( {
				collection: self.collection,
				model: rate,
			} ) );
		} );
	},
} );

/**
 * Output a table header and footer.
 */
var TaxRatesTableMeta = wp.Backbone.View.extend( {
	// See https://codex.wordpress.org/Javascript_Reference/wp.template
	template: wp.template( 'edd-admin-tax-rates-table-meta' ),

	// Watch events.
	events: {
		'change [type="checkbox"]': 'selectAll',
	},

	/**
	 * Render a collection of rows.
	 */
	render: function() {
		this.$el.html( this.template( {
			selected: this.selected ? 'checked' : '',
		} ) );
	},

	/**
	 * Select all items in the collection.
	 *
	 * @param {Object} event Event.
	 */
	selectAll: function( event ) {
		var self = this;
		var checked = event.target.checked;

		_.each( this.collection.models, function( model ) {
			// Check individual models.
			model.set( 'selected', checked );

			// Add to global selection.
			self.collection.selected.push( model.cid );
		} );
	},
} );

var TaxRatesTableRegion = wp.Backbone.View.extend( {
	/**
	 * Bind passed arguments.
	 *
	 * @param {Object} options Extra options passed.
	 */
	initialize: function( options ) {
		_.extend( this, options );
	},

	/**
	 * Create a list of options.
	 */
	render: function() {
		if ( this.global ) {
			return;
		}

		if ( 'nostates' === this.states ) {
			this.setElement( '<input type="text" id="tax_rate_region" />' );
		} else {
			this.$el.html( this.states );
			this.$el.find( 'select' ).chosen( eddTaxRatesChosenVars );
		}
	},
} );

/**
 * Add a new rate "form"
 *
 * A rate is managed through a "dumb" object instead of a model to avoid
 * issues with Backbone thinking the same model is being added more than once.
 *
 * @todo Try to manage this view through a proper model for easier event tracking.
 */
var TaxRatesTableAdd = wp.Backbone.View.extend( {
	// Use <tfoot>
	tagName: 'tfoot',

	// See https://codex.wordpress.org/Javascript_Reference/wp.template
	template: wp.template( 'edd-admin-tax-rates-table-add' ),

	// Watch events.
	events: {
		'click button': 'addTaxRate',

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
	 * Initialize.
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
		this.$el.html( this.template() );
		this.$el.find( 'select' ).chosen( 'eddTaxRatesChosenVars' );
	},

	/**
	 * Show a list of states or an input field.
	 */
	updateRegion: function() {
		var self = this;

		var data = {
			action: 'edd_get_shop_states',
			country: this.model.get( 'country' ),
			nonce: eddTaxRates.nonce,
			field_name: 'tax_rate_region'
		};

		$.post( ajaxurl, data, function( response ) {
			self.views.set( '#tax_rate_region_wrapper', new TaxRatesTableRegion( {
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
		this.model.set( 'country', event.target.options[event.target.selectedIndex].value );
	},

	/**
	 * Set a region value.
	 *
	 * @param {Object} event Event.
	 */
	setRegion: function( event ) {
		var value = false;

		if ( event.target.value ) {
			value = event.target.value;
		} else {
			value = event.target.options[event.target.selectedIndex].value;
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
	 * Add a single rate when the "form" is submitted.
	 *
	 * @param {Object} event Event.
	 */
	addTaxRate: function( event ) {
		event.preventDefault();

		// Merge cid as ID to make this a unique model.
		this.collection.add( _.extend(
			this.model.attributes,
			{
				id: this.model.cid,
			}
		) );

		this.render();

		// Reset model.
		this.model = new TaxRate( {
			global: true,
			unsaved: true,
		} );
	},
} );

/**
 * Manage the tax rate rows in a table.
 */
var TaxRatesTable = wp.Backbone.View.extend( {
	// Render as a <table> tag.
	tagName: 'table',

	// Set class.
	className: 'wp-list-table widefat fixed striped tax-rates',

	// Set ID.
	id: 'edd_tax_rates',

	/**
	 * Output a table with a header, body, and footer.
	 */
	render: function() {
		this.views.add( new TaxRatesTableMeta( {
			tagName: 'thead',
			collection: this.collection,
		} ) );

		this.views.add( new TaxRatesTableRows( {
			collection: this.collection,
		} ) );

		this.views.add( new TaxRatesTableAdd( {
			collection: this.collection,
		} ) );

		this.views.add( new TaxRatesTableMeta( {
			tagName: 'tfoot',
			collection: this.collection,
		} ) );
	},
} );

/**
 * Apply bulk actions.
 */
var TaxRatesBulkActions = wp.Backbone.View.extend( {
	// See https://codex.wordpress.org/Javascript_Reference/wp.template
	template: wp.template( 'edd-admin-tax-rates-table-bulk-actions' ),

	// Watch events.
	events: {
		'click .edd-admin-tax-rates-table-filter': 'filter',
		'change .edd-admin-tax-rates-table-hide input': 'showHide',
	},

	/**
	 * Bulk actions for selected items.
	 *
	 * @param {Object} event Event.
	 */
	filter: function( event ) {
		event.preventDefault();

		var self   = this;

		// Need to access the DOM directly here because the dropdown is not tied to the button event.
		var status = document.getElementById( 'edd-admin-tax-rates-table-bulk-actions' );

		_.each( this.collection.selected, function( cid ) {
			var model = self.collection.get( {
				cid: cid,
			} );

			model.set( 'status', status.value );
		} );
	},

	/**
	 * Toggle show active/inactive rates.
	 *
	 * @param {Object} event Event.
	 */
	showHide: function( event ) {
		this.collection.showAll = ! event.target.checked;
		this.collection.trigger( 'change' );
	},
} );

/**
 * Manage tax rates.
 */
var TaxManager = wp.Backbone.View.extend( {
	// Append to this element.
	el: '#edd-admin-tax-rates',

	/**
	 * Output the manager.
	 */
	render: function() {
		this.views.add( new TaxRatesBulkActions( {
			collection: this.collection,
		} ) );

		this.views.add( new TaxRatesTable( {
			collection: this.collection,
		} ) );
	},
} );

/**
 * DOM ready.
 */
document.addEventListener( 'DOMContentLoaded', function() {
	// Start manager with a blank collection.
	var eddTaxRatesManager = new TaxManager( {
		collection: new TaxRates(),
	} );

	// Populate collection with bootstrapped data.
	// @todo Can set these all at once if the schema is updated to reflect the columns used.
	_.each( eddTaxRates.rates, function( rate ) {
		eddTaxRatesManager.collection.add( {
			id: rate.id,
			country: rate.name,
			region: rate.description,
			global: 'country' === rate.scope,
			amount: rate.amount,
			status: rate.status,
		} );
	} );

	// Render manager.
	eddTaxRatesManager.render();

	// Chosen fields. This should happen globally.
	$( '.edd-select-chosen' ).chosen( eddTaxRatesChosenVars );
} );
