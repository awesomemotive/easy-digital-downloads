/* global wp */

/**
 * Internal dependencies.
 */
import TableMeta from './table-meta.js';
import TableRows from './table-rows.js';
import TableFooter from './table-footer.js';

/**
 * Manage the tax rate rows in a table.
 */
const Table = wp.Backbone.View.extend( {
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
		this.views.add( new TableMeta( {
			tagName: 'thead',
			collection: this.collection,
		} ) );

		this.views.add( new TableRows( {
			collection: this.collection,
		} ) );

		this.views.add( new TableFooter( {
			collection: this.collection,
		} ) );

		this.views.add( new TableMeta( {
			tagName: 'tfoot',
			collection: this.collection,
		} ) );

		// Trigger the `filtered` action to show/hide rows accordingly
		this.collection.trigger( 'filtered' );
	},
} );

export default Table;
