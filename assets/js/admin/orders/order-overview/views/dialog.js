/* global wp */

/**
 * "Dialog" view
 *
 * @since 3.0
 *
 * @class Dialog
 * @augments wp.Backbone.View
 */
export const Dialog = wp.Backbone.View.extend( /** Lends FormAddItem.prototype */ {

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
				at: 'center center-25%'
			},
			width: '350px',
			modal: true,
			resizable: false,
			draggable: false,
			autoOpen: false,
		} );

		// Automatically close dialog when an OrderItem is added.
		this.listenTo( this.collection, 'add', this.closeDialog );
	},

	/**
	 * Opens the jQuery UI Dialog containing this view.
	 *
	 * @since 3.0
	 *
	 * @return {Dialog}
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
	 * @return {Dialog}
	 */
	closeDialog() {
		this.$el.dialog( 'close' );

		// Prevent events from stacking.
		this.undelegateEvents();

		return this;
	},
} );
