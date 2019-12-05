/**
 * Internal dependencies.
 */
import { getChosenVars } from 'utils/chosen.js';

jQuery( document ).ready( function( $ ) {
	$( '.download_page_edd-payment-history table.orders .row-actions .delete a, a.edd-delete-payment' ).on( 'click', function() {
		if ( confirm( edd_vars.delete_payment ) ) {
			return true;
		}
		return false;
	} );

	$( '.download_page_edd-payment-history table.orderitems .row-actions .delete a' ).on( 'click', function() {
		if ( confirm( edd_vars.delete_order_item ) ) {
			return true;
		}
		return false;
	} );

	$( '.download_page_edd-payment-history table.orderadjustments .row-actions .delete a' ).on( 'click', function() {
		if ( confirm( edd_vars.delete_order_adjustment ) ) {
			return true;
		}
		return false;
	} );

	// Toggle advanced filters on Orders page.
	$( '.edd-advanced-filters-button' ).on( 'click', function( e ) {
		// Prevent submit action
		e.preventDefault();

		$( '#edd-advanced-filters' ).toggleClass( 'open' );
	} );
} );

/**
 * Edit payment screen JS
 */
const EDD_Edit_Payment = {

	init: function() {
		this.edit_address();
		this.remove_download();
		this.add_download();
		this.change_customer();
		this.new_customer();
		this.edit_price();
		this.recalculate_total();
		this.variable_prices_check();
		this.resend_receipt();
		this.copy_download_link();
		this.refund_order();
	},

	edit_address: function() {
		// Update base state field based on selected base country
		$( 'select[name="edd-payment-address[0][country]"]' ).change( function() {
			const select = $( this ),
				data = {
					action: 'edd_get_shop_states',
					country: select.val(),
					nonce: select.data( 'nonce' ),
					field_name: 'edd-payment-address[0][region]',
				};

			$.post( ajaxurl, data, function( response ) {
				const state_wrapper = $( '#edd-order-address-state-wrap select, #edd-order-address-state-wrap input' );

				// Remove any chosen containers here too
				$( '#edd-order-address-state-wrap .chosen-container' ).remove();

				if ( 'nostates' === response ) {
					state_wrapper.replaceWith( '<input type="text" name="edd-payment-address[0][region]" value="" class="edd-edit-toggles medium-text"/>' );
				} else {
					state_wrapper.replaceWith( response );
					$( '#edd-order-address-state-wrap select' ).each( function() {
						const el = $( this );
						el.chosen( getChosenVars( el ) );
					} );
				}
			} );

			return false;
		} );
	},

	remove_download: function() {
		// Remove a download from a purchase
		$( '#edd-order-items' ).on( 'click', '.edd-order-remove-download', function() {
			const count = $( document.body ).find( '#edd-order-items > .row:not(.header)' ).length;

			if ( count === 1 ) {
				alert( edd_vars.one_download_min );
				return false;
			}

			if ( confirm( edd_vars.delete_payment_download ) ) {
				const key = $( this ).data( 'key' ),
					download_id = $( 'input[name="edd-payment-details-downloads[' + key + '][id]"]' ).val(),
					price_id = $( 'input[name="edd-payment-details-downloads[' + key + '][price_id]"]' ).val(),
					quantity = $( 'input[name="edd-payment-details-downloads[' + key + '][quantity]"]' ).val(),
					amount = $( 'input[name="edd-payment-details-downloads[' + key + '][amount]"]' ).val(),
					order_item_id = $( 'input[name="edd-payment-details-downloads[' + key + '][order_item_id]"]' ).val();

				if ( $( 'input[name="edd-payment-details-downloads[' + key + '][tax]"]' ) ) {
					var fees = $( 'input[name="edd-payment-details-downloads[' + key + '][tax]"]' ).val();
				}

				if ( $( 'input[name="edd-payment-details-downloads[' + key + '][fees]"]' ) ) {
					var fees = $.parseJSON( $( 'input[name="edd-payment-details-downloads[' + key + '][fees]"]' ).val() );
				}

				let currently_removed = $( 'input[name="edd-payment-removed"]' ).val();
				currently_removed = $.parseJSON( currently_removed );
				if ( currently_removed.length < 1 ) {
					currently_removed = {};
				}

				const removed_item = [ { order_item_id: order_item_id, id: download_id, price_id: price_id, quantity: quantity, amount: amount, cart_index: key } ];
				currently_removed[ key ] = removed_item;

				$( 'input[name="edd-payment-removed"]' ).val( JSON.stringify( currently_removed ) );

				$( this ).parent().parent().remove();
				if ( fees && fees.length ) {
					$.each( fees, function( key, value ) {
						$( '*li[data-fee-id="' + value + '"]' ).remove();
					} );
				}

				// Flag the Downloads section as changed
				$( '#edd-payment-downloads-changed' ).val( 1 );
				$( '.edd-order-payment-recalc-totals' ).show();
			}
			return false;
		} );
	},

	change_customer: function() {
		$( '#edd-customer-details' ).on( 'click', '.edd-payment-change-customer, .edd-payment-change-customer-cancel', function( e ) {
			e.preventDefault();

			const change_customer = $( this ).hasClass( 'edd-payment-change-customer' ),
				cancel = $( this ).hasClass( 'edd-payment-change-customer-cancel' );

			if ( change_customer ) {
				$( '.order-customer-info' ).hide();
				$( '.change-customer' ).show();
				setTimeout( function() {
					$( '.edd-payment-change-customer-input' ).css( 'width', '300' );
				}, 1 );
			} else if ( cancel ) {
				$( '.order-customer-info' ).show();
				$( '.change-customer' ).hide();
			}
		} );
	},

	new_customer: function() {
		$( '#edd-customer-details' ).on( 'click', '.edd-payment-new-customer, .edd-payment-new-customer-cancel', function( e ) {
			e.preventDefault();

			var new_customer = $( this ).hasClass( 'edd-payment-new-customer' ),
				cancel = $( this ).hasClass( 'edd-payment-new-customer-cancel' );

			if ( new_customer ) {
				$( '.order-customer-info' ).hide();
				$( '.new-customer' ).show();
			} else if ( cancel ) {
				$( '.order-customer-info' ).show();
				$( '.new-customer' ).hide();
			}

			var new_customer = $( '#edd-new-customer' );

			if ( $( '.new-customer' ).is( ':visible' ) ) {
				new_customer.val( 1 );
			} else {
				new_customer.val( 0 );
			}
		} );
	},

	add_download: function() {
		// Add a New Download from the Add Downloads to Purchase Box
		$( '.edd-edit-purchase-element' ).on( 'click', '#edd-order-add-download', function( e ) {
			e.preventDefault();

			const order_download_select = $( '#edd_order_download_select' ),
				order_download_quantity = $( '#edd-order-download-quantity' ),
				order_download_price = $( '#edd-order-download-price' ),
				order_download_tax = $( '#edd-order-download-tax' ),
				selected_price_option = $( '.edd_price_options_select option:selected' );

			let download_id = order_download_select.val(),
				download_title = order_download_select.find( ':selected' ).text(),
				quantity = order_download_quantity.val(),
				item_price = order_download_price.val(),
				item_tax = order_download_tax.val(),
				price_id = selected_price_option.val(),
				price_name = selected_price_option.text();

			if ( download_id < 1 ) {
				return false;
			}

			if ( ! item_price ) {
				item_price = 0;
			}

			item_price = parseFloat( item_price );
			if ( isNaN( item_price ) ) {
				alert( edd_vars.numeric_item_price );
				return false;
			}

			item_tax = parseFloat( item_tax );
			if ( isNaN( item_tax ) ) {
				alert( edd_vars.numeric_item_tax );
				return false;
			}

			if ( isNaN( parseInt( quantity ) ) ) {
				alert( edd_vars.numeric_quantity );
				return false;
			}

			if ( price_name ) {
				download_title = download_title + ' - ' + price_name;
			}

			const count = $( '#edd-order-items div.row' ).length,
				clone = $( '#edd-order-items div.row:last' ).clone();

			clone.find( '.download span' ).html( '<a href="post.php?post=' + download_id + '&action=edit"></a>' );
			clone.find( '.download span a' ).text( download_title );
			clone.find( '.edd-payment-details-download-item-price' ).val( item_price.toFixed( edd_vars.currency_decimals ) );
			clone.find( '.edd-payment-details-download-item-tax' ).val( item_tax.toFixed( edd_vars.currency_decimals ) );
			clone.find( 'input.edd-payment-details-download-id' ).val( download_id );
			clone.find( 'input.edd-payment-details-download-price-id' ).val( price_id );

			let item_total = ( item_price * quantity ) + item_tax;
			item_total = item_total.toFixed( edd_vars.currency_decimals );
			clone.find( 'span.edd-payment-details-download-amount' ).text( item_total );
			clone.find( 'input.edd-payment-details-download-amount' ).val( item_total );
			clone.find( 'input.edd-payment-details-download-quantity' ).val( quantity );
			clone.find( 'input.edd-payment-details-download-has-log' ).val( 0 );
			clone.find( 'input.edd-payment-details-download-order-item-id' ).val( 0 );

			clone.find( '.edd-copy-download-link-wrapper' ).remove();

			// Replace the name / id attributes
			clone.find( 'input' ).each( function() {
				let name = $( this ).attr( 'name' );

				name = name.replace( /\[(\d+)\]/, '[' + parseInt( count ) + ']' );

				$( this ).attr( 'name', name ).attr( 'id', name );
			} );

			clone.find( 'a.edd-order-remove-download' ).attr( 'data-key', parseInt( count ) );

			// Flag the Downloads section as changed
			$( '#edd-payment-downloads-changed' ).val( 1 );

			$( clone ).insertAfter( '#edd-order-items div.row:last' );
			$( '.edd-order-payment-recalc-totals' ).show();
			$( '.edd-add-download-field' ).val( '' );
		} );
	},

	edit_price: function() {
		$( document.body ).on( 'change keyup', '.edd-payment-item-input', function() {
			let row = $( this ).parents( 'ul.edd-purchased-files-list-wrapper' ),
				quantity = row.find( 'input.edd-payment-details-download-quantity' ).val().replace( edd_vars.thousands_separator, '' ),
				item_price = row.find( 'input.edd-payment-details-download-item-price' ).val().replace( edd_vars.thousands_separator, '' ),
				item_tax = row.find( 'input.edd-payment-details-download-item-tax' ).val().replace( edd_vars.thousands_separator, '' );

			$( '.edd-order-payment-recalc-totals' ).show();

			item_price = parseFloat( item_price );
			if ( isNaN( item_price ) ) {
				alert( edd_vars.numeric_item_price );
				return false;
			}

			item_tax = parseFloat( item_tax );
			if ( isNaN( item_tax ) ) {
				item_tax = 0.00;
			}

			if ( isNaN( parseInt( quantity ) ) ) {
				quantity = 1;
			}

			let item_total = ( item_price * quantity ) + item_tax;
			item_total = item_total.toFixed( edd_vars.currency_decimals );
			row.find( 'input.edd-payment-details-download-amount' ).val( item_total );
			row.find( 'span.edd-payment-details-download-amount' ).text( item_total );
		} );
	},

	recalculate_total: function() {
		// Update taxes and totals for any changes made.
		$( '#edd-order-recalc-total' ).on( 'click', function( e ) {
			e.preventDefault();

			let total = 0,
				tax = 0,
				totals = $( '#edd-order-items .row input.edd-payment-details-download-amount' ),
				taxes = $( '#edd-order-items .row input.edd-payment-details-download-item-tax' );

			if ( totals.length ) {
				totals.each( function() {
					total += parseFloat( $( this ).val() );
				} );
			}

			if ( taxes.length ) {
				taxes.each( function() {
					tax += parseFloat( $( this ).val() );
				} );
			}

			if ( $( '.edd-payment-fees' ).length ) {
				$( '.edd-payment-fees span.fee-amount' ).each( function() {
					total += parseFloat( $( this ).data( 'fee' ) );
				} );
			}
			$( 'input[name=edd-payment-total]' ).val( total.toFixed( edd_vars.currency_decimals ) );
			$( 'input[name=edd-payment-tax]' ).val( tax.toFixed( edd_vars.currency_decimals ) );
		} );
	},

	variable_prices_check: function() {
		// On Download Select, Check if Variable Prices Exist
		$( '.edd-edit-purchase-element' ).on( 'change', 'select#edd_order_download_select', function() {
			const select = $( this ),
				download_id = select.val();

			if ( parseInt( download_id ) > 0 ) {
				const postData = {
					action: 'edd_check_for_download_price_variations',
					download_id: download_id,
				};

				$.ajax( {
					type: 'POST',
					data: postData,
					url: ajaxurl,
					success: function( response ) {
						$( '.edd_price_options_select' ).remove();
						$( response ).insertAfter( select.next() );
					},
				} ).fail( function( data ) {
					if ( window.console && window.console.log ) {
						console.log( data );
					}
				} );
			}
		} );
	},

	resend_receipt: function() {
		const emails_wrap = $( '.edd-order-resend-receipt-addresses' );

		$( document.body ).on( 'click', '#edd-select-receipt-email', function( e ) {
			e.preventDefault();
			emails_wrap.slideDown();
		} );

		$( document.body ).on( 'change', '.edd-order-resend-receipt-email', function() {
			const selected = $('input:radio.edd-order-resend-receipt-email:checked').val();

			$( '#edd-select-receipt-email').data( 'email', selected );
		} );

		$( document.body).on( 'click', '#edd-select-receipt-email', function () {
			if ( confirm( edd_vars.resend_receipt ) ) {
				const href = $( this ).prop( 'href' ) + '&email=' + $( this ).data( 'email' );
				window.location = href;
			}
		} );

		$( document.body ).on( 'click', '#edd-resend-receipt', function() {
			return confirm( edd_vars.resend_receipt );
		} );
	},

	copy_download_link: function() {
		$( document.body ).on( 'click', '.edd-copy-download-link', function( e ) {
			e.preventDefault();

			const link = $( this ),
				postData = {
					action: 'edd_get_file_download_link',
					payment_id: $( 'input[name="edd_payment_id"]' ).val(),
					download_id: link.data( 'download-id' ),
					price_id: link.data( 'price-id' ),
				};

			$.ajax( {
				type: 'POST',
				data: postData,
				url: ajaxurl,
				success: function( link ) {
					$( '#edd-download-link' ).dialog( {
						width: 400,
					} ).html( '<textarea rows="10" cols="40" id="edd-download-link-textarea">' + link + '</textarea>' );
					$( '#edd-download-link-textarea' ).focus().select();
					return false;
				},
			} ).fail( function( data ) {
				if ( window.console && window.console.log ) {
					console.log( data );
				}
			} );
		} );
	},

	refund_order: function refund_order() {

		// Loads the modal when the refund button is clicked.
		$(document.body).on('click', '.edd-refund-order', function (e) {
			e.preventDefault();
			var link     = $(this),
				postData = {
					action  : 'edd_generate_refund_form',
					order_id: $('input[name="edd_payment_id"]').val(),
				};

			$.ajax({
				type   : 'POST',
				data   : postData,
				url    : ajaxurl,
				success: function success(data) {
					let modal_content = '';
					if (data.success) {
						modal_content = data.html;
					} else {
						modal_content = data.message;
					}

					$('#edd-refund-order-dialog').dialog({
						position: { my: 'top center', at: 'center center-25%' },
						width    : '75%',
						modal    : true,
						resizable: false,
						draggable: false,
						open: function( event, ui ) {
							$(this).html( modal_content );
						},
						close: function( event, ui ) {
							$(this).html( '' );
							location.reload();
						}
					});
					return false;
				}
			}).fail(function (data) {
				$('#edd-refund-order-dialog').dialog({
					position: { my: 'top center', at: 'center center-25%' },
					width    : '75%',
					modal    : true,
					resizable: false,
					draggable: false
				}).html(data.message);
				return false;
			});
		});

		// Handles including items in the refund.
		$(document.body).on( 'change', '#edd-refund-order-dialog tbody .check-column input[type="checkbox"]', function () {
			let parent = $(this).parent().parent(),
				all_checkboxes = $('#edd-refund-order-dialog tbody .check-column input[type="checkbox"]');

			if ( $(this).is(':checked') ) {
				parent.addClass('refunded');
			} else {
				parent.removeClass('refunded');
			}

			let new_subtotal = 0,
				new_tax      = 0,
				new_total    = 0;

			// Set a readonly while we recalculate, to avoid race conditions in the browser.
			all_checkboxes.prop('readonly', true);
			$('#edd-refund-submit-button-wrapper .spinner').css('visibility', 'visible');

			$('#edd-refund-order-dialog tbody .check-column input[type="checkbox"]:checked').each( function() {
				let item_parent = $(this).parent().parent();

				// Values for this item.
				let item_amount   = parseFloat( item_parent.find('span[data-amount]').data('amount') ),
					item_tax      = parseFloat( item_parent.find('span[data-tax]').data('tax') ),
					item_total    = parseFloat( item_parent.find('span[data-total]').data('total') ),
					item_quantity = parseInt( item_parent.find('.column-quantity').text() );

				new_subtotal += item_amount;
				new_tax      += item_tax;
				new_total    += item_total;
			});

			new_subtotal = parseFloat(new_subtotal).toFixed( edd_vars.currency_decimals );
			new_tax      = parseFloat(new_tax).toFixed( edd_vars.currency_decimals );
			new_total    = parseFloat(new_total).toFixed( edd_vars.currency_decimals );

			$('#edd-refund-submit-subtotal-amount').data('refund-subtotal', new_subtotal ).text( new_subtotal );
			$('#edd-refund-submit-tax-amount').data('refund-tax', new_tax ).text( new_tax );
			$('#edd-refund-submit-total-amount').data('refund-total', new_total ).text( new_total );

			if ( new_total > 0 ) {
				$('#edd-submit-refund-submit').removeClass('disabled');
			} else {
				$('#edd-submit-refund-submit').addClass('disabled');
			}

			// Remove the readonly.
			all_checkboxes.prop('readonly', false);
			$('#edd-refund-submit-button-wrapper .spinner').css('visibility', 'hidden');

		});

		// Listen for the bulk action checkbox, since WP doesn't trigger a change on sub-items.
		$(document.body).on('change', '#edd-refund-order-dialog #cb-select-all-1', function() {
			let item_checkboxes = $('#edd-refund-order-dialog tbody .check-column input[type="checkbox"]');
			if ( $(this).is(':checked')) {
				item_checkboxes.each(function() {
					$(this).prop('checked', true).trigger('change');
				});
			} else {
				item_checkboxes.each(function() {
					$(this).prop('checked', false).trigger('change');
				});
			}
		});

		// Process the refund form after the button is clicked.
		$(document.body).on( 'click', '#edd-submit-refund-submit', function(e) {
			$('.edd-submit-refund-message').removeClass('success').removeClass('fail');
			$(this).addClass('disabled');
			$('#edd-refund-submit-button-wrapper .spinner').css('visibility', 'visible');
			$('#edd-submit-refund-status').hide();
			let item_ids = [],
				refund_subtotal = $('#edd-refund-submit-subtotal-amount').data('refund-subtotal'),
				refund_tax      = $('#edd-refund-submit-tax-amount').data('refund-tax'),
				refund_total    = $('#edd-refund-submit-total-amount').data('refund-total');

			// Get the Order Item IDs we're going to be refunding.
			const item_checkboxes = $('#edd-refund-order-dialog tbody .check-column input[type="checkbox"]');
			item_checkboxes.each(function() {
				if ( $(this).is(':checked') ) {
					let item_id = $(this).parent().parent().data('order-item');
					item_ids.push(item_id);
				}
			});

			e.preventDefault();

			var postData = {
					action  : 'edd_process_refund_form',
					item_ids : item_ids,
					refund_subtotal: refund_subtotal,
					refund_tax : refund_tax,
					refund_total : refund_total,
					order_id: $('input[name="edd_payment_id"]').val(),
					nonce: $('#edd-process-refund-form #_wpnonce').val(),
				};

			$.ajax({
				type   : 'POST',
				data   : postData,
				url    : ajaxurl,
				success: function success(data) {
					const message_target = $('.edd-submit-refund-message'),
						url_target     = $('.edd-submit-refund-url');

					if ( data.success ) {
						$('#edd-refund-order-dialog table').hide();
						$('#edd-refund-order-dialog .tablenav').hide();

						message_target.text(data.message).addClass('success');
						url_target.attr('href', data.refund_url).show();

						$('#edd-submit-refund-status').show();
					} else {
						message_target.text(data.message).addClass('fail');
						url_target.hide();

						$('#edd-submit-refund-status').show();
						$('#edd-submit-refund-submit').removeClass('disabled');
						$('#edd-submit-refund-button-wrapper .spinner').css('visibility', 'hidden');
					}
				}
			}).fail(function (data) {
				const message_target = $('.edd-submit-refund-message'),
					  url_target     = $('.edd-submit-refund-url'),
					  json           = data.responseJSON;


				message_target.text(json.message).addClass('fail');
				url_target.hide();

				$('#edd-submit-refund-status').show();
				$('#edd-submit-refund-submit').removeClass('disabled');
				$('#edd-submit-refund-button-wrapper .spinner').css('visibility', 'hidden');
				return false;
			});
		});
	}

};

jQuery( document ).ready( function( $ ) {
	EDD_Edit_Payment.init();
} );
