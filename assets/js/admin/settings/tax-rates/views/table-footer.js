/* global wp */

/**
 * Internal dependencies.
 */
import FormAddTaxRate from './form-add-tax-rate.js';

/**
 * TableFooter
 */
const TableFooter = wp.Backbone.View.extend( {
	// Use <tfoot>
	tagName: 'tfoot',

	// See https://codex.wordpress.org/Javascript_Reference/wp.template
	template: wp.template( 'edd-admin-tax-rates-table-footer' ),

	// Watch events.
	events: {
		'click button': 'onAdd',
	},

	/**
	 * Renders the "Add Tax Rate" flow.
	 *
	 * @since 3.0
	 *
	 * @param {Object} e Click event.
	 */
	onAdd( e ) {
		e.preventDefault();

		new FormAddTaxRate().openDialog().render();
	},
} );

export default TableFooter;
