/* global wp, _ */

/**
 * Internal dependencies.
 */
import { getChosenVars } from '@easydigitaldownloads/utils';

const RegionField = wp.Backbone.View.extend( {
	/**
	 * Bind passed arguments.
	 *
	 * @param {Object} options Extra options passed.
	 */
	initialize: function( options ) {
		_.extend( this, options );
	},

	/**
	 * Create a list of options.
	 */
	render: function() {
		if ( this.global ) {
			return;
		}

		if ( 'nostates' === this.states ) {
			this.setElement( '<input type="text" id="tax_rate_region" />' );
		} else {
			this.$el.html( this.states );
			this.$el.find( 'select' ).each( function() {
				const el = $( this );
				el.chosen( getChosenVars( el ) );
			} );
		}
	},
} );

export default RegionField;
