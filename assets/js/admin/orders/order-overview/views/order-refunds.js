/**
 * Internal dependencies
 */
import { OrderRefund } from './order-refund.js';
import { Currency } from '@easy-digital-downloads/currency';

const currency = new Currency();

/**
 * Order refunds
 *
 * @since 3.0
 *
 * @class Refunds
 * @augments wp.Backbone.View
 */
export const Refunds = wp.Backbone.View.extend( {
	/**
	 * @since 3.0
	 */
	tagName: 'tbody',

	/**
	 * @since 3.0
	 */
	className: 'edd-order-overview-summary__refunds',

	/**
	 * @since 3.0
	 */
	template: wp.template( 'edd-admin-order-refunds' ),

	/**
	 * Renders initial view.
	 *
	 * @since 3.0
	 */
	render() {
		const { state } = this.options;
		const { models: refunds } = state.get( 'refunds' );

		_.each( refunds, ( model ) => (
			this.views.add(
				new OrderRefund( {
					...this.options,
					model,
				} )
			)
		) );
	},
} );
