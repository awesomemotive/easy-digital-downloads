/* global wp, _ */

/**
 * A row inside a table of rates.
 */
const TableRow = wp.Backbone.View.extend( {
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
		'change [type="checkbox"]': 'selectRow',
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
		this.$el.html( this.template( {
			...this.model.toJSON(),
			formattedAmount: this.model.formattedAmount(),
		} ) );

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

		const { i18n } = eddTaxRates;
		const existingCountryWide = this.collection.where( {
			region: this.model.get( 'region' ),
			country: this.model.get( 'country' ),
			global: '' === this.model.get( 'region' ),
			status: 'active',
		} );

		if ( existingCountryWide.length > 0 ) {
			const regionString = '' === this.model.get( 'region' )
				? ''
				: ': ' + this.model.get( 'region' );

			const taxRateString = this.model.get( 'country' ) + regionString;

			alert( i18n.multipleCountryWide.replace( '%s', taxRateString ) );

			return;
		}

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
		const checked = event.target.checked;

		if ( ! checked ) {
			this.collection.selected = _.reject( this.collection.selected, ( cid ) => {
				return cid === this.model.cid;
			} );
		} else {
			this.collection.selected.push( this.model.cid );
		}
	},
} );

export default TableRow;
