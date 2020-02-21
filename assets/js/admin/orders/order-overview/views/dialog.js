/**
 * Internal dependencies
 */
import { Base } from './';

/**
 * "Dialog" view
 *
 * @since 3.0
 *
 * @class Dialog
 * @augments Base
 */
export const Dialog = Base.extend( {
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
			width: '350px',
			modal: true,
			resizable: false,
			draggable: false,
			autoOpen: false,
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
	 * @return {Dialog} Current view.
	 */
	closeDialog() {
		this.$el.dialog( 'close' );

		// Prevent events from stacking.
		this.undelegateEvents();

		return this;
	},
} );
