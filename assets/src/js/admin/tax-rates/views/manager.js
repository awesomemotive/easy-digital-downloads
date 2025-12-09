/* global wp */

/**
 * Internal dependencies.
 */
import Table from './table.js';
import BulkActions from './bulk-actions.js';

/**
 * Manage tax rates.
 */
const Manager = wp.Backbone.View.extend( {
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
		this.views.add( new BulkActions( {
			collection: this.collection,
		} ) );

		this.views.add( new Table( {
			collection: this.collection,
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
	},
} );

export default Manager;
