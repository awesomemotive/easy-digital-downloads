/* global wp, _ */

/**
 * Apply bulk actions.
 */
const BulkActions = wp.Backbone.View.extend( {
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
	 * Currently only supports changing the status.
	 *
	 * @param {Object} event Event.
	 */
	filter: function( event ) {
		event.preventDefault();

		// @hack - need to access the DOM directly here because the dropdown is not tied to the button event.
		const status = document.getElementById( 'edd-admin-tax-rates-table-bulk-actions' );

		_.each( this.collection.selected, ( cid ) => {
			const model = this.collection.get( {
				cid: cid,
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
	},
} );

export default BulkActions;
