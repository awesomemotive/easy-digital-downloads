/* global eddAdminOrderOverview */

/**
 * Internal dependencies
 */
import Base from './base.js';

/**
 * "Dialog" view
 *
 * @since 3.0
 *
 * @class Dialog
 * @augments Base
 */
const Dialog = Base.extend( {
	/**
	 * "Dialog" view.
	 *
	 * @since 3.0
	 *
	 * @constructs Dialog
	 * @augments wp.Backbone.View
	 */
	initialize() {
		this.$el.dialog( {
			position: {
				my: 'top center',
				at: 'center center-25%',
			},
			classes: {
				'ui-dialog': 'edd-dialog',
			},
			closeText: eddAdminOrderOverview.i18n.closeText,
			width: '350px',
			modal: true,
			resizable: false,
			draggable: false,
			autoOpen: false,
			create: function() {
				$( this ).css( 'maxWidth', '90vw' );
			},
		} );
	},

	/**
	 * Opens the jQuery UI Dialog containing this view.
	 *
	 * @since 3.0
	 *
	 * @return {Dialog} Current view.
	 */
	openDialog() {
		this.$el.dialog( 'open' );

		return this;
	},

	/**
	 * Closes the jQuery UI Dialog containing this view.
	 *
	 * @since 3.0
	 *
	 * @param {Object=} e Event that triggered the close.
	 * @return {Dialog} Current view.
	 */
	closeDialog( e ) {
		if ( e && e.preventDefault ) {
			e.preventDefault();
		}

		this.$el.dialog( 'close' );

		// Prevent events from stacking.
		this.undelegateEvents();

		return this;
	},
} );

export default Dialog;