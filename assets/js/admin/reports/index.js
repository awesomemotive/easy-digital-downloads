/* global pagenow, postboxes */

/**
 * Internal dependencies.
 */
import { eddLabelFormatter, eddLegendFormatterSales, eddLegendFormatterEarnings } from './formatting.js';

// Enable reports meta box toggle states.
if ( typeof postboxes !== 'undefined' && /edd-reports/.test( pagenow ) ) {
	postboxes.add_postbox_toggles( pagenow );
}

/**
 * Reports / Exports screen JS
 */
var EDD_Reports = {

	init : function() {
		this.meta_boxes();
		this.date_options();
		this.customers_export();
		this.filters();
	},

	meta_boxes : function() {
		$( '.edd-reports-wrapper .postbox .handlediv' ).remove();
		$( '.edd-reports-wrapper .postbox' ).removeClass( 'closed' );

		// Use a timeout to ensure this happens after core binding
		setTimeout( function() {
			$( '.edd-reports-wrapper .postbox .hndle' ).unbind( 'click.postboxes' );
		}, 1 );
	},

	date_options : function() {

		// Show hide extended date options
		$( 'select.edd-graphs-date-options' ).on( 'change', function( event ) {
			var	select             = $( this ),
				date_range_options = select.parent().siblings( '.edd-date-range-options' );

			if ( 'other' === select.val() ) {
				date_range_options.removeClass( 'screen-reader-text' );
			} else {
				date_range_options.addClass( 'screen-reader-text' );
			}
		});
	},

	customers_export : function() {

		// Show / hide Download option when exporting customers
		$( '#edd_customer_export_download' ).change( function() {

			var $this = $( this ),
				download_id = $('option:selected', $this).val(),
				customer_export_option = $( '#edd_customer_export_option' );

			if ( '0' === $this.val() ) {
				customer_export_option.show();
			} else {
				customer_export_option.hide();
			}

			// On Download Select, Check if Variable Prices Exist
			if ( parseInt( download_id ) !== 0 ) {
				var data = {
					action : 'edd_check_for_download_price_variations',
					download_id: download_id,
					all_prices: true
				};

				var price_options_select = $('.edd_price_options_select');

				$.post(ajaxurl, data, function(response) {
					price_options_select.remove();
					$('#edd_customer_export_download_chosen').after( response );
				});
			} else {
				price_options_select.remove();
			}
		});
	},

	filters : function() {
		$('.edd_countries_filter').on( 'change', function() {
			var select = $( this ),
				data   = {
					action:    'edd_get_shop_states',
					country:    select.val(),
					nonce:      select.data('nonce'),
					field_name: 'edd_countries_filter'
				};

			$.post( ajaxurl, data, function ( response ) {
				$( 'select.edd_regions_filter' ).find( 'option:gt(0)' ).remove();

				if ( 'nostates' !== response ) {
					$( response ).find( 'option:gt(0)' ).appendTo( 'select.edd_regions_filter' );
				}

				$( 'select.edd_regions_filter' ).trigger( 'chosen:updated' );
			});

			return false;
		} );
	}
};

export default EDD_Reports;
