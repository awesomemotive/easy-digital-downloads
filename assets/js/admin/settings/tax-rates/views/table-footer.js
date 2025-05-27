/* global wp */

/**
 * Internal dependencies.
 */
import { Dialog } from '../../../../packages/edd-backbone/src/dialog.js';
import FormAddTaxRate from './form-add-tax-rate.js';
import TaxRate from '../models/tax-rate.js';

/**
 * TableFooter
 */
const TableFooter = Dialog.extend( {
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
	onAdd ( e ) {
		e.preventDefault();

		// Initialize a new tax rate model
		const model = new TaxRate({
			global: true,
			unsaved: true,
		});

		// Create and render the form with the model
		new FormAddTaxRate({
			model: model,
			collection: this.collection
		}).render().openDialog();
	},
} );

export default TableFooter;
