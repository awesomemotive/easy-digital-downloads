/**
 * Internal dependencies
 */
import OrderOverview from './order-overview';
import './order-details';
import { jQueryReady } from 'utils/jquery.js';

jQueryReady( () => {
	// Order Overview.
	if ( window.eddAdminOrderOverview ) {
		OrderOverview.render();

		/**
		 * Enable "Create Order" button when an Order has items.
		 *
		 * @since 3.0
		 */
		( () => {
			const createButton = document.getElementById( 'edd-order-submit' );

			if ( ! createButton ) {
				return;
			}

			const items = OrderOverview.options.state.get( 'items' );

			items.on( 'add remove', function() {
				createButton.disabled = items.length === 0;
			} );
		} )();
	}

	// Move `.update-nag` items below the top header.
	// `#update-nag` is legacy styling, which core still supports.
	//
	// `.notice` items are properly moved, but WordPress core
	// does not move `.update-nag`.
	if ( 0 !== $( '.edit-post-editor-regions__header' ).length ) {
		$( 'div.update-nag, div#update-nag' ).insertAfter( $( '.edit-post-editor-regions__header' ) );
	}

} );
