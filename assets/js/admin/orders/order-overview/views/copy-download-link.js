/**
 * Internal dependencies
 */
import { Base } from './base.js';
import { Dialog } from './dialog.js';

/**
 * "Copy Download Link" view
 *
 * @since 3.0
 *
 * @class FormAddOrderItem
 * @augments Dialog
 */
export const CopyDownloadLink = Dialog.extend( {
	/**
	 * @since 3.0
	 */
	el: '#edd-admin-order-copy-download-link-dialog',

	/**
	 * @since 3.0
	 */
	template: wp.template( 'edd-admin-order-copy-download-link' ),

	/**
	 * "Copy Download Link" view.
	 *
	 * @since 3.0
	 *
	 * @constructs CopyDownloadLink
	 * @augments Base
	 */
	initialize() {
		Dialog.prototype.initialize.apply( this, arguments );

		this.link = false;

		this.addEvents( {
			'click #close': 'closeDialog',
		} );

		this.fetchLink.call( this );
	},

	/**
	 * Prepares data to be used in `render` method.
	 *
	 * @since 3.0
	 *
	 * @see wp.Backbone.View
	 * @see https://github.com/WordPress/WordPress/blob/master/wp-includes/js/wp-backbone.js
	 *
	 * @return {Object} The data for this view.
	 */
	prepare() {
		const { link } = this;

		return {
			link,
		};
	},

	/**
	 * Renders the view.
	 *
	 * @since 3.0
	 */
	render() {
		Base.prototype.render.apply( this, arguments );

		const { el, link } = this;

		// Select the contents if a link is available.
		if ( false !== link && '' !== link ) {
			el.querySelector( '#link' ).select();
		}
	},

	/**
	 * Fetches the Download's file URLs.
	 *
	 * @since 3.0
	 */
	fetchLink() {
		const { orderId, productId, priceId } = this.options;

		// Retrieve and set link.
		//
		// We can't use wp.ajax.send because the `edd_ajax_generate_file_download_link()`
		// does not send back JSON responses.
		$.ajax( {
			type: 'POST',
			url: ajaxurl,
			data: {
				action: 'edd_get_file_download_link',
				payment_id: orderId,
				download_id: productId,
				price_id: priceId,
			},
		} )
			.done( ( link ) => {
				link = link.trim();

				if ( [ '-1', '-2', '-3', '-4', '' ].includes( link ) ) {
					this.link = '';
				} else {
					this.link = link.trim();
				}
			} )
			.done( () => this.render() );
	},
} );
