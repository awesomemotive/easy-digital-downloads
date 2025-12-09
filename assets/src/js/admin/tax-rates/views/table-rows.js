/* global wp, _ */

/**
 * Internal dependencies.
 */
import TableRowEmpty from './table-row-empty.js';
import TableRow from './table-row.js';

/**
 * A bunch of rows inside a table of rates.
 */
const TableRows = wp.Backbone.View.extend( {
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
		// Clear to handle sorting.
		this.views.remove();

		// Show empty placeholder.
		if ( 0 === this.collection.models.length ) {
			return this.views.add( new TableRowEmpty() );
		}

		// Add items.
		_.each( this.collection.models, ( rate ) => {
			this.views.add( new TableRow( {
				collection: this.collection,
				model: rate,
			} ) );
		} );
	},

	/**
	 * Show an empty state if all items are deactivated.
	 */
	filtered: function() {
		const disabledRates = this.collection.where( {
			status: 'inactive',
		} );

		// Check if all rows are invisible, and show the "No Items" row if so
		if ( disabledRates.length === this.collection.models.length && ! this.collection.showAll ) {
			this.views.add( new TableRowEmpty() );

		// Possibly re-render the view
		} else {
			this.render();
		}
	},
} );

export default TableRows;
