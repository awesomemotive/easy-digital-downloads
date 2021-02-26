/**
 * Internal dependencies
 */
import { jQueryReady } from '@easydigitaldownloads/utils';
import OrderOverview from './order-overview';
import './order-details';

jQueryReady( () => {
	// Order Overview.
	if ( window.eddAdminOrderOverview ) {
		OrderOverview.render();

		/**
		 * Add validation to Add/Edit Order form.
		 *
		 * @since 3.0
		 */
		( () => {
			const overview = OrderOverview.options.state;
			const orderItems = overview.get( 'items' );

			const noItemErrorEl = document.getElementById( 'edd-add-order-no-items-error' );
			const noCustomerErrorEl = document.getElementById( 'edd-add-order-customer-error' );

			const assignCustomerEl = document.getElementById( 'customer_id' );
			const newCustomerEmailEl = document.getElementById( 'edd_new_customer_email' );

			[
				'edd-add-order-form',
				'edd-edit-order-form',
			].forEach( ( form ) => {
				const formEl = document.getElementById( form );

				if ( ! formEl ) {
					return;
				}

				formEl.addEventListener( 'submit', submitForm );
			} );

			/**
			 * Submits an Order form.
			 *
			 * @since 3.0
			 *
			 * @param {Object} event Submit event.
			 */
			function submitForm( event ) {
				let hasError = false;

				// Ensure `OrderItem`s.
				if ( noItemErrorEl ) {
					if ( 0 === orderItems.length ) {
						noItemErrorEl.style.display = 'block';
						hasError = true;
					} else {
						noItemErrorEl.style.display = 'none';
					}
				}

				// Ensure Customer.
				if ( noCustomerErrorEl ) {
					if ( '0' === assignCustomerEl.value && '' === newCustomerEmailEl.value ) {
						noCustomerErrorEl.style.display = 'block';
						hasError = true;
					} else {
						noCustomerErrorEl.style.display = 'none';
					}

					if ( true === hasError ) {
						event.preventDefault();
					}
				}
			}

			/**
			 * Remove `OrderItem` notice when an `OrderItem` is added.
			 *
			 * @since 3.0
			 */
			orderItems.on( 'add', function() {
				noItemErrorEl.style.display = 'none';
			} );

			/**
			 * Remove Customer notice when a Customer is changed.
			 *
			 * Uses a jQuery binding for Chosen support.
			 *
			 * @since 3.0
			 *
			 * @param {Object} event Change event.
			 */
			$( assignCustomerEl ).on( 'change', ( event ) => {
				const val = event.target.value;

				if ( '0' !== val ) {
					noCustomerErrorEl.style.display = 'none';
				}
			} )

			if ( newCustomerEmailEl ) {
				/**
				 * Remove Customer notice when a Customer is set.
				 *
				 * @since 3.0
				 *
				 * @param {Object} event Input event.
				 */
				newCustomerEmailEl.addEventListener( 'input', ( event ) => {
					const val = event.target.value;

					if ( '' !== val ) {
						noCustomerErrorEl.style.display = 'none';
					}
				} );
			}
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
