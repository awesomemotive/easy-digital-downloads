/* global _, edd_vars, Backbone, wp, eddTaxRates, ajaxurl */

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
		selected: false
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
	}
} );

/**
 * A collection of multiple tax rates.
 */
var TaxRates = Backbone.Collection.extend( {
	// Map the model.
	model: TaxRate,

	/**
	 * Set initial state.
	 */
	initialize: function() {
		this.showAll = false;
		this.selected = [];
	}
} );

/**
 * Empty tax rates table.
 */
var TaxRatesTableEmpty = wp.Backbone.View.extend( {
	// Insert as a <tr>
	tagName: 'tr',

	// Set class.
	className: 'edd-tax-rate-row edd-tax-rate-row--is-empty',

	// See https://codex.wordpress.org/Javascript_Reference/wp.template
	template: wp.template( 'edd-admin-tax-rates-table-row-empty' )
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
		'click .remove': 'removeRow',
		'click .activate': 'activateRow',
		'click .deactivate': 'deactivateRow',
		'change [type="checkbox"]': 'selectRow'
	},

	/**
	 * Bind model to view.
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
				formattedAmount: this.model.formattedAmount()
			}
		) ) );

		// Ensure the wrapper class has the new name.
		this.$el.attr( 'class', _.result( this, 'className' ) );
	},

	/**
	 * Remove a rate (can only be done if it has not been saved to the database).
	 *
	 * Don't use this.model.destroy() to avoid sending a DELETE request.
	 *
	 * @param {Object} event Event.
	 */
	removeRow: function( event ) {
		event.preventDefault();

		this.collection.remove( this.model );
	},

	/**
	 * Activate a rate.
	 *
	 * @param {Object} event Event.
	 */
	activateRow: function( event ) {
		event.preventDefault();

		this.model.set( 'status', 'active' );
	},

	/**
	 * Deactivate a rate.
	 *
	 * @param {Object} event Event.
	 */
	deactivateRow: function( event ) {
		event.preventDefault();

		this.model.set( 'status', 'inactive' );
	},

	/**
	 * Select or deselect for bulk actions.
	 *
	 * @param {Object} event Event.
	 */
	selectRow: function( event ) {
		var self = this;
		var checked = event.target.checked;

		if ( ! checked ) {
			this.collection.selected = _.reject( this.collection.selected, function( cid ) {
				return cid === self.model.cid;
			} );
		} else {
			this.collection.selected.push( this.model.cid );
		}
	}
} );

/**
 * A bunch of rows inside a table of rates.
 */
var TaxRatesTableRows = wp.Backbone.View.extend( {
	// Insert as a <tbody>
	tagName: 'tbody',

	/**
	 * Bind events to collection.
	 */
	initialize: function() {
		this.listenTo( this.collection, 'add', this.render );
		this.listenTo( this.collection, 'remove', this.render );
		this.listenTo( this.collection, 'filtered change', this.filtered );
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

		// Add items.
		_.each( this.collection.models, function( rate ) {
			self.views.add( new TaxRatesTableRow( {
				collection: self.collection,
				model: rate
			} ) );
		} );
	},

	/**
	 * Show an empty state if all items are deactivated.
	 */
	filtered: function() {
		var disabledRates = this.collection.where( {
			status: 'inactive'
		} );

		// Check if all rows are invisible, and show the "No Items" row if so
		if ( disabledRates.length === this.collection.models.length && ! this.collection.showAll ) {
			this.views.add( new TaxRatesTableEmpty() );

		// Possibly re-render the view
		} else {
			this.render();
		}
	}
} );

/**
 * Output a table header and footer.
 */
var TaxRatesTableMeta = wp.Backbone.View.extend( {
	// See https://codex.wordpress.org/Javascript_Reference/wp.template
	template: wp.template( 'edd-admin-tax-rates-table-meta' ),

	// Watch events.
	events: {
		'change [type="checkbox"]': 'selectAll'
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
	}
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
	}
} );

/**
 * Add a new rate "form".
 */
var TaxRatesTableAdd = wp.Backbone.View.extend( {
	// Use <tfoot>
	tagName: 'tfoot',

	// Set class.
	className: 'add-new',

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
		'change #tax_rate_amount': 'setAmount'
	},

	/**
	 * Set initial state and bind changes to model.
	 */
	initialize: function() {
		this.model = new TaxRate( {
			global: true,
			unsaved: true
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
				global: self.model.get( 'global' )
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
				id: this.model.cid
			}
		) );

		this.render();
		this.initialize();
	}
} );

/**
 * Manage the tax rate rows in a table.
 */
var TaxRatesTable = wp.Backbone.View.extend( {
	// Render as a <table> tag.
	tagName: 'table',

	// Set class.
	className: 'wp-list-table widefat fixed tax-rates',

	// Set ID.
	id: 'edd_tax_rates',

	/**
	 * Output a table with a header, body, and footer.
	 */
	render: function() {
		this.views.add( new TaxRatesTableMeta( {
			tagName: 'thead',
			collection: this.collection
		} ) );

		this.views.add( new TaxRatesTableRows( {
			collection: this.collection
		} ) );

		this.views.add( new TaxRatesTableAdd( {
			collection: this.collection
		} ) );

		this.views.add( new TaxRatesTableMeta( {
			tagName: 'tfoot',
			collection: this.collection
		} ) );

		// Trigger the `filtered` action to show/hide rows accordingly
		this.collection.trigger( 'filtered' );
	}
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
		'change .edd-admin-tax-rates-table-hide input': 'showHide'
	},

	/**
	 * Bulk actions for selected items.
	 *
	 * Currently only supports changing the status.
	 *
	 * @param {Object} event Event.
	 */
	filter: function( event ) {
		event.preventDefault();

		var self   = this;

		// @hack - need to access the DOM directly here because the dropdown is not tied to the button event.
		var status = document.getElementById( 'edd-admin-tax-rates-table-bulk-actions' );

		_.each( this.collection.selected, function( cid ) {
			var model = self.collection.get( {
				cid: cid
			} );

			model.set( 'status', status.value );
		} );

		this.collection.trigger( 'filtered' );
	},

	/**
	 * Toggle show active/inactive rates.
	 *
	 * @param {Object} event Event.
	 */
	showHide: function( event ) {
		this.collection.showAll = event.target.checked;

		// @hack -- shouldn't access this table directly.
		document.getElementById( 'edd_tax_rates' ).classList.toggle( 'has-inactive', this.collection.showAll );

		this.collection.trigger( 'filtered' );
	}
} );

/**
 * Manage tax rates.
 */
var TaxManager = wp.Backbone.View.extend( {
	// Append to this element.
	el: '#edd-admin-tax-rates',

	/**
	 * Set bind changes to collection.
	 */
	initialize: function() {
		this.listenTo( this.collection, 'add change', this.makeDirty );

		// Clear unload confirmation when submitting parent form.
		document.querySelector( '.edd-settings-form #submit' ).addEventListener( 'click', this.makeClean );
	},

	/**
	 * Output the manager.
	 */
	render: function() {
		this.views.add( new TaxRatesBulkActions( {
			collection: this.collection
		} ) );

		this.views.add( new TaxRatesTable( {
			collection: this.collection
		} ) );
	},

	/**
	 * Collection has changed so warn the user before exiting.
	 */
	makeDirty: function() {
		window.onbeforeunload = this.confirmUnload;
	},

	/**
	 * When submitting the main form remove the dirty check.
	 */
	makeClean: function() {
		window.onbeforeunload = null;
	},

	/**
	 * Confirm page unload.
	 *
	 * @param {Object} event Close event.
	 */
	confirmUnload: function( event ) {
		event.preventDefault();

		return '';
	}
} );

/**
 * DOM ready.
 */
document.addEventListener( 'DOMContentLoaded', function() {
	// Start manager with a blank collection.
	var eddTaxRatesManager = new TaxManager( {
		collection: new TaxRates()
	} );

	var rates = [];

	// Normalize rate data.
	_.each( eddTaxRates.rates, function( rate ) {
		rates.push( {
			id: rate.id,
			country: rate.name,
			region: rate.description,
			global: 'country' === rate.scope,
			amount: rate.amount,
			status: rate.status
		} );
	} );

	// Add initial rates.
	eddTaxRatesManager.collection.set( rates, {
		silent: true
	} );

	// Render manager.
	eddTaxRatesManager.render();

	// Chosen fields. This should happen globally.
	$( '.edd-select-chosen' ).chosen( eddTaxRatesChosenVars );
} );
