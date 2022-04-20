/* global $, ajaxurl, eddAdminOrderOverview */

/**
 * Internal dependencies
 */
import { jQueryReady } from 'utils/jquery.js';

jQueryReady( () => {
	$( document.body ).on( 'change', '#edd_payment_status', function( e ) {
		e.preventDefault();

		let selectedStatus = $(this).val();
		let foundStatus    = eddAdminOrderOverview.orderStatuses.incomplete.indexOf( selectedStatus );
		const dateWrapper  = $('.completed-date-wrapper');
		console.log(foundStatus);
		if ( foundStatus >= 0 ) {
			dateWrapper.slideUp();
		} else {
			dateWrapper.slideDown();
		}
	} );
} );
