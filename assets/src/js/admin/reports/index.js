/* global pagenow, postboxes */

/**
 * Internal dependencies.
 */
import { eddLabelFormatter, eddLegendFormatterSales, eddLegendFormatterEarnings } from './formatting.js';
import './charts';

// Enable reports meta box toggle states.
if ( typeof postboxes !== 'undefined' && /edd-reports/.test( pagenow ) ) {
	postboxes.add_postbox_toggles( pagenow );
}

/**
 * Reports / Exports screen JS
 */
const EDD_Reports = {

	init: function() {
		this.meta_boxes();
		this.date_options();
		this.customers_export();
	},

	meta_boxes: function() {
		$( '.edd-reports-wrapper .postbox .handlediv' ).remove();
		$( '.edd-reports-wrapper .postbox' ).removeClass( 'closed' );

		// Use a timeout to ensure this happens after core binding
		setTimeout( function() {
			$( '.edd-reports-wrapper .postbox .hndle' ).off( 'click.postboxes' );
		}, 1 );
	},

	date_options: function() {
		// Show hide extended date options
		$( 'select.edd-graphs-date-options' ).on( 'change', function( event ) {
			const	select = $( this ),
				date_range_options = select.parent().siblings( '.edd-date-range-options' );

			if ( 'other' === select.val() ) {
				date_range_options.removeClass( 'screen-reader-text' );
			} else {
				date_range_options.addClass( 'screen-reader-text' );
				$( '.edd-date-range-selected-date span' ).addClass( 'hidden' )
				$( '.edd-date-range-selected-date span[data-range="' + select.val() + '"]' ).removeClass( 'hidden' )
			}
		} );

		$( '.edd-date-range-dates' ).on( 'click', function( event ) {
			event.preventDefault();
			$( 'select.edd-graphs-date-options' ).trigger( 'focus' );
		});

		/**
		 * Relative date ranges.
		 */

			const relativeDateRangesParent   = $( '.edd-date-range-selected-relative-date' ),
			      relativeDateRangesDropdown = $( '.edd-date-range-relative-dropdown' );

			// Detect when HTML select for normal date range is changed.
			$( '.edd-graphs-date-options' ).on( 'change', function() {
				var range          = $( this ).val();
				var relative_range = $( '.edd-date-range-selected-date span[data-range="' + range + '"]' ).data( 'default-relative-range' );

				$( '.edd-date-range-picker' ).attr( 'data-range', range );
				$( '.edd-graphs-relative-date-options' ).val( relative_range ).trigger( 'change' );

				// Get relative date ranges from backend.
				$.ajax( {
					type: 'GET',
					dataType: 'html',
					url: ajaxurl,
					data: {
						action: 'edd_reports_get_relative_date_ranges',
						range: range,
						relative_range: relative_range,
					},
					beforeSend: function() {
						relativeDateRangesDropdown.html( '<div class="spinner"></div>' ).addClass( 'loading' );
					},
					success: function( data ) {
						relativeDateRangesDropdown.html( data );
					},
				} ).fail( function( response ) {
					if ( window.console && window.console.log ) {
						console.log( response );
					}
				} ).done( function( response ) {
					relativeDateRangesDropdown.remove( '.spinner' );
					relativeDateRangesDropdown.removeClass( 'loading' );
				} );

			} )

			// Open relative daterange dropdown.
			relativeDateRangesParent.on( 'click', function( event ) {
				event.preventDefault();
				$( this ).toggleClass( 'opened' );
			});

			// When selecting relative daterange from dropdown.
			$( document ).on( 'click', '.edd-date-range-relative-dropdown li', function() {
				var range = $(this).data( 'range' );
				$('.edd-graphs-relative-date-options').val( range ).trigger( 'change' );
			});

			// Detect when HTML select for relative date range is changed.
			$('.edd-graphs-relative-date-options').on( 'change', function() {
				// Get relative date range name.
				var range               = $( this ).val();
				var selected_range_item = $( '.edd-date-range-relative-dropdown li[data-range="' + range + '"]' );
				var selected_range_name = selected_range_item.find( '.date-range-name' ).first().text();

				$( '.edd-date-range-selected-relative-range-name' ).html( selected_range_name )
				$( '.edd-date-range-relative-dropdown li.active' ).removeClass( 'active' );
				selected_range_item.addClass( 'active' );
			} )


			// If a click event is triggered on body.
			$( document ).on( 'click', function( e ) {
				EDD_Reports.close_relative_ranges_dropdown( e.target );
			});

			// If the Escape key is pressed.
			$( document ).on( 'keydown', function( event ) {
				const key = event.key;
				if ( key === "Escape" ) {
					EDD_Reports.close_relative_ranges_dropdown();
				}
			});

	},

	close_relative_ranges_dropdown: function( target = false ) {
		var relativeDateRangesParent = $( '.edd-date-range-selected-relative-date' );

		if ( ! relativeDateRangesParent.hasClass( 'opened' ) ) {
			return false;
		}

		if ( false === target || ( ! relativeDateRangesParent.is( target ) && ! relativeDateRangesParent.has( target ).length ) ) {
			relativeDateRangesParent.removeClass( 'opened' );
		}
	},

	customers_export: function() {
		// Show / hide Download option when exporting customers
		$( '#edd_customer_export_download' ).on( 'change', function () {
			const $this = $( this ),
				download_id = $( 'option:selected', $this ).val(),
				customer_export_option = $( '#edd_customer_export_option' );

			if ( '0' === $this.val() ) {
				customer_export_option.show();
			} else {
				customer_export_option.hide();
			}

			// On Download Select, Check if Variable Prices Exist
			if ( parseInt( download_id ) !== 0 ) {
				const data = {
					action: 'edd_check_for_download_price_variations',
					download_id: download_id,
					all_prices: true,
				};

				var price_options_select = $( '.edd_price_options_select' );

				$.post( ajaxurl, data, function( response ) {
					price_options_select.remove();
					$( '#edd_customer_export_download_chosen' ).after( response );
				} );
			} else {
				price_options_select.remove();
			}
		} );
	},
};

jQuery( document ).ready( function( $ ) {
	EDD_Reports.init();
} );
