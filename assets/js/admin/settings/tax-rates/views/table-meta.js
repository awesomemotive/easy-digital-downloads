/* global wp, _ */

/**
 * Output a table header and footer.
 */
const TableMeta = wp.Backbone.View.extend( {
	// See https://codex.wordpress.org/Javascript_Reference/wp.template
	template: wp.template( 'edd-admin-tax-rates-table-meta' ),

	// Watch events.
	events: {
		'change [type="checkbox"]': 'selectAll',
	},

	/**
	 * Select all items in the collection.
	 *
	 * @param {Object} event Event.
	 */
	selectAll: function( event ) {
		const checked = event.target.checked;

		_.each( this.collection.models, ( model ) => {
			// Check individual models.
			model.set( 'selected', checked );

			// Add to global selection.
			this.collection.selected.push( model.cid );
		} );
	},
} );

export default TableMeta;
