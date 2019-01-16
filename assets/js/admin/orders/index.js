/**
 * Internal dependencies.
 */
import { chosenVars } from 'js/utils/chosen.js';

jQuery( document ).ready( function( $ ) {
	// Toggle advanced filters on Orders page.
	$( '.edd-advanced-filters-button' ).on( 'click', function( e ) {
		// Prevnt submit action
		e.preventDefault();

		$( '#edd-advanced-filters' ).toggleClass( 'open' );
	} );
} );

const edd_admin_globals = {};

/**
 * Add order
 */
var EDD_Add_Order = {
	init: function() {
		this.add_order_item();
		this.add_adjustment();
		this.override();
		this.remove();
		this.fetch_addresses();
		this.select_address();
		this.recalculate_total();
		this.validate();
	},

	add_order_item: function() {
		const button = $( '.edd-add-order-item-button' );

		// Toggle form.
		$( '#edd-order-items' ).on( 'click', 'h3 .edd-metabox-title-action', function( e ) {
			e.preventDefault();
			$( '#edd-order-items' ).children( '.edd-add-download-to-purchase' ).slideToggle();
		} );

		button.prop( 'disabled', 'disabled' );

		$( '.edd-order-add-download-select' ).on( 'change', function() {
			button.removeAttr( 'disabled' );
		} );

		// Add item.
		button.on( 'click', function( e ) {
			e.preventDefault();

			let select = $( '.edd-order-add-download-select' ),
				spinner = $( '.edd-add-download-to-purchase .spinner' ),
				data = {
					action: 'edd_add_order_item',
					nonce: $( '#edd_add_order_nonce' ).val(),
					country: $( '.edd-order-address-country' ).val(),
					region: $( '.edd-order-address-region' ).val(),
					download: select.val(),
					quantity: $( '.edd-add-order-quantity' ).val(),
				};

			spinner.css( 'visibility', 'visible' );

			$.post( ajaxurl, data, function( response ) {
				const { success, data } = response;

				if ( ! success ) {
					return;
				}

				$( '.orderitems .no-items' ).hide();
				$( '.orderitems tbody' ).append( data.html );

				EDD_Add_Order.update_totals();
				EDD_Add_Order.reindex();

				spinner.css( 'visibility', 'hidden' );

				// Display `Override` button if it exists.
				$( '.edd-override' ).removeAttr( 'disabled' );
			}, 'json' );
		} );
	},

	add_adjustment: function() {
		// Toggle form.
		$( '#edd-order-adjustments' ).on( 'click', 'h3 .edd-metabox-title-action', function( e ) {
			e.preventDefault();
			$( '#edd-order-adjustments' ).children( '.edd-add-adjustment-to-purchase' ).slideToggle();
		} );

		$( '.edd-order-add-adjustment-select' ).on( 'change', function() {
			const type = $( this ).val();

			$( '.edd-add-adjustment-to-purchase li.fee, .edd-add-adjustment-to-purchase li.discount, .edd-add-adjustment-to-purchase li.fee, .edd-add-adjustment-to-purchase li.credit' ).hide();

			$( '.' + type, '.edd-add-adjustment-to-purchase' ).show();
		} );

		$( '.edd-add-order-adjustment-button' ).on( 'click', function( e ) {
			e.preventDefault();

			let data = {
					action: 'edd_add_adjustment_to_order',
					nonce: $( '#edd_add_order_nonce' ).val(),
					type: $( '.edd-order-add-adjustment-select' ).val(),
					adjustment_data: {
						fee: $( '.edd-order-add-fee-select' ).val(),
						discount: $( '.edd-order-add-discount-select' ).val(),
						credit: {
							description: $( '.edd-add-order-credit-description' ).val(),
							amount: $( '.edd-add-order-credit-amount' ).val(),
						},
					},
				},
				spinner = $( '.edd-add-adjustment-to-purchase .spinner' );

			spinner.css( 'visibility', 'visible' );

			$.post( ajaxurl, data, function( response ) {
				const { success, data } = response;

				if ( ! success ) {
					return;
				}

				$( '.orderadjustments .no-items' ).hide();
				$( '.orderadjustments tbody' ).append( data.html );

				EDD_Add_Order.update_totals();
				EDD_Add_Order.reindex();

				spinner.css( 'visibility', 'hidden' );
			}, 'json' );
		} );
	},

	override: function() {
		$( '.edd-override' ).on( 'click', function() {
			$( this ).prop( 'disabled', 'disabled' );

			$( this ).attr( 'data-override', 'true' );

			$( document.body ).on( 'click', '.orderitems tr td .value', EDD_Add_Order.switchToInput );

			$( '<input>' ).attr( {
				type: 'hidden',
				name: 'edd_add_order_override',
				value: 'true',
			} ).appendTo( '#edd-add-order-form' );
		} );
	},

	switchToInput: function() {
		const input = $( '<input>', {
			val: $( this ).text(),
			type: 'text',
		} );

		$( this ).replaceWith( input );
		input.on( 'blur', EDD_Add_Order.switchToSpan );
		input.select();
	},

	switchToSpan: function() {
		const span = $( '<span>', {
			text: parseFloat( $( this ).val() ).toLocaleString( edd_vars.currency, {
				style: 'decimal',
				currency: edd_vars.currency,
				minimumFractionDigits: edd_vars.currency_decimals,
				maximumFractionDigits: edd_vars.currency_decimals,
			} ),
		} );

		let type = $( this ).parent().data( 'type' ),
			input = $( this ).parents( 'tr' ).find( '.download-' + type );

		if ( 'quantity' === type ) {
			span.text( parseInt( $( this ).val() ) );
		}

		input.val( span.text() );

		span.addClass( 'value' );
		$( this ).replaceWith( span );

		EDD_Add_Order.update_totals();

		span.on( 'click', EDD_Add_Order.switchToInput );
	},

	remove: function() {
		$( document.body ).on( 'click', '.orderitems .remove-item, .orderadjustments .remove-item', function( e ) {
			e.preventDefault();

			let $this = $( this ),
				tbody = $this.parents( 'tbody' );

			$this.parents( 'tr' ).remove();

			if ( 1 === $( 'tr', tbody ).length ) {
				$( '.no-items', tbody ).show();
			}

			EDD_Add_Order.update_totals();
			EDD_Add_Order.reindex();

			return false;
		} );
	},

	fetch_addresses: function() {
		$( '.edd-payment-change-customer-input' ).on( 'change', function() {
			let $this = $( this ),
				data = {
					action: 'edd_customer_addresses',
					customer_id: $this.val(),
					nonce: $( '#edd_add_order_nonce' ).val(),
				};

			$.post( ajaxurl, data, function( response ) {
				const { success, data } = response;

				if ( ! success ) {
					$( '.customer-address-select-wrap' ).html( '' ).hide();

					return;
				}

				// Store response for later use.
				edd_admin_globals.customer_address_ajax_result = data;

				if ( data.html ) {
					$( '.customer-address-select-wrap' ).html( data.html ).show();
					$( '.customer-address-select-wrap select' ).chosen( chosenVars );
				} else {
					$( '.customer-address-select-wrap' ).html( '' ).hide();
				}
			}, 'json' );

			return false;
		} );
	},

	select_address: function() {
		$( document.body ).on( 'change', '.customer-address-select-wrap .add-order-customer-address-select', function() {
			let $this = $( this ),
				val = $this.val(),
				select = $( '#edd-add-order-form select#edd_order_address_country' ),
				address = edd_admin_globals.customer_address_ajax_result.addresses[ val ];

			$( '#edd-add-order-form input[name="edd_order_address[address]"]' ).val( address.address );
			$( '#edd-add-order-form input[name="edd_order_address[address2]"]' ).val( address.address2 );
			$( '#edd-add-order-form input[name="edd_order_address[postal_code]"]' ).val( address.postal_code );
			$( '#edd-add-order-form input[name="edd_order_address[city]"]' ).val( address.city );
			select.val( address.country ).trigger( 'chosen:updated' );
			$( '#edd-add-order-form input[name="edd_order_address[address_id]"]' ).val( val );

			const data = {
				action: 'edd_get_shop_states',
				country: select.val(),
				nonce: $( '.add-order-customer-address-select' ).data( 'nonce' ),
				field_name: 'edd_order_address_region',
			};

			$.post( ajaxurl, data, function( response ) {
				$( 'select#edd_order_address_region' ).find( 'option:gt(0)' ).remove();

				if ( 'nostates' !== response ) {
					$( response ).find( 'option:gt(0)' ).appendTo( 'select#edd_order_address_region' );
				}

				$( 'select#edd_order_address_region' ).trigger( 'chosen:updated' );
				$( 'select#edd_order_address_region' ).val( address.region ).trigger( 'chosen:updated' );
			} );

			return false;
		} );

		$( '.edd-order-address-country' ).on( 'change', function() {
			let select = $( this ),
				data = {
					action: 'edd_get_shop_states',
					country: select.val(),
					nonce: select.data( 'nonce' ),
					field_name: 'edd-order-address-country',
				};

			$.post( ajaxurl, data, function( response ) {
				$( 'select.edd-order-address-region' ).find( 'option:gt(0)' ).remove();

				if ( 'nostates' !== response ) {
					$( response ).find( 'option:gt(0)' ).appendTo( 'select.edd-order-address-region' );
				}

				$( 'select.edd-order-address-region' ).trigger( 'chosen:updated' );
			} ).done( function( response ) {
				EDD_Add_Order.recalculate_taxes();
			} );

			return false;
		} );

		$( '.edd-order-address-region' ).on( 'change', function() {
			EDD_Add_Order.recalculate_taxes();
		} );
	},

	reindex: function() {
		let key = 0;

		$( '.orderitems tbody tr:not(.no-items), .orderadjustments tbody tr:not(.no-items)' ).each( function() {
			$( this ).attr( 'data-key', key );

			$( this ).find( 'input' ).each( function() {
				let name = $( this ).attr( 'name' );

				if ( name ) {
					name = name.replace( /\[(\d+)\]/, '[' + parseInt( key ) + ']' );
					$( this ).attr( 'name', name );
				}
			} );

			key++;
		} );
	},

	recalculate_taxes: function() {
		$( '#publishing-action .spinner' ).css( 'visibility', 'visible' );

		const data = {
			action: 'edd_add_order_recalculate_taxes',
			country: $( '.edd-order-address-country' ).val(),
			region: $( '.edd-order-address-region' ).val(),
			nonce: $( '#edd_add_order_nonce' ).val(),
		};

		$.post( ajaxurl, data, function( response ) {
			const { success, data } = response;

			if ( ! success ) {
				return;
			}

			if ( '' !== data.tax_rate ) {
				const tax_rate = parseFloat( data.tax_rate );

				$( '.orderitems tbody tr:not(.no-items)' ).each( function() {
					const amount = parseFloat( $( '.amount .value', this ).text() );
					const quantity = $( '.quantity .value', this ) ? parseFloat( $( '.column-quantity .value', this ).text() ) : 1;
					const calculated = amount * quantity;
					let tax = 0;

					if ( data.prices_include_tax ) {
						const pre_tax = parseFloat( calculated / ( 1 + tax_rate ) );
						tax = parseFloat( calculated - pre_tax );
					} else {
						tax = calculated * tax_rate;
					}

					const storeCurrency = edd_vars.currency;
					const decimalPlaces = edd_vars.currency_decimals;
					const total = calculated + tax;

					$( '.tax .value', this ).text( tax.toLocaleString( storeCurrency, {
						style: 'decimal',
						currency: storeCurrency,
						minimumFractionDigits: decimalPlaces,
						maximumFractionDigits: decimalPlaces ,
					} ) );

					$( '.total .value', this ).text( total.toLocaleString( storeCurrency, {
						style: 'decimal',
						currency: storeCurrency,
						minimumFractionDigits: decimalPlaces,
						maximumFractionDigits: decimalPlaces,
					} ) );
				} );
			}
		}, 'json' ).done( function() {
			$( '#publishing-action .spinner' ).css( 'visibility', 'hidden' );

			EDD_Add_Order.update_totals();
		} );
	},

	recalculate_total: function() {
		$( '#edd-add-order' ).on( 'click', '#edd-order-recalc-total', function() {
			EDD_Add_Order.update_totals();
		} );
	},

	update_totals: function() {
		let subtotal = 0,
			discounts = 0,
			adjustments = 0,
			tax = 0,
			total = 0;

		$( '.orderitems tbody tr:not(.no-items)' ).each( function() {
			let row = $( this ),
				item_amount,
				item_quantity = 1,
				item_tax = 0,
				item_total;

			item_amount = parseFloat( row.find( '.amount .value' ).text() );

			if ( row.find( '.quantity' ).length ) {
				item_quantity = parseFloat( row.find( '.quantity .value' ).text() );
			}

			subtotal += item_amount * item_quantity;

			if ( row.find( '.tax' ).length ) {
				item_tax = parseFloat( row.find( '.tax .value' ).text() );

				if ( ! isNaN( item_tax ) && ! edd_vars.taxes_included ) {
					item_amount += item_tax;
					tax += item_tax;
				}
			}

			item_total = item_amount * item_quantity;

			total += item_total;
		} );

		$( '.orderadjustments tbody tr:not(.no-items)' ).each( function() {
			let row = $( this ),
				type,
				amount = 0;

			type = row.data( 'adjustment' );

			switch ( type ) {
				case 'credit':
					amount = parseFloat( row.find( 'input.credit-amount', row ).val() );
					adjustments += amount;
					total -= amount;
					break;
				case 'discount':
					amount = parseFloat( row.find( 'input.discount-amount', row ).val() );

					if ( 'percent' === row.find( 'input.discount-type' ).val() ) {
						$( '.orderitems tbody tr:not(.no-items)' ).each( function() {
							let item_amount = $( this ).find( '.amount .value' ).text(),
								quantity = 1;

							if ( $( this ).find( '.quantity' ).length ) {
								quantity = parseFloat( $( this ).find( '.quantity' ).text() );
							}

							item_amount *= quantity;

							const reduction = parseFloat( ( item_amount / 100 ) * amount );

							if ( $( this ).find( '.tax' ).length ) {
								let item_tax = parseFloat( $( this ).find( '.tax .value' ).text() ),
									item_tax_reduction = parseFloat( item_tax / 100 * amount );

								tax -= item_tax_reduction;
								total -= item_tax_reduction;
							}

							discounts += reduction;
							total -= reduction;
						} );
					} else {
						adjustments += amount;
						total -= amount;
					}

					break;
			}
		} );

		if ( isNaN( total ) ) {
			total = 0;
		}

		if ( isNaN( subtotal ) ) {
			subtotal = 0;
		}

		if ( isNaN( tax ) ) {
			tax = 0;
		}

		if ( isNaN( discounts ) ) {
			discounts = 0;
		}

		if ( isNaN( adjustments ) ) {
			adjustments = 0;
		}

		$( ' .edd-order-subtotal .value' ).html( subtotal.toFixed( edd_vars.currency_decimals ) );
		$( ' .edd-order-discounts .value' ).html( discounts.toFixed( edd_vars.currency_decimals ) );
		$( ' .edd-order-adjustments .value' ).html( adjustments.toFixed( edd_vars.currency_decimals ) );
		$( ' .edd-order-taxes .value' ).html( tax.toFixed( edd_vars.currency_decimals ) );
		$( ' .edd-order-total .value ' ).html( total.toFixed( edd_vars.currency_decimals ) );
	},

	validate: function() {
		$( '#edd-add-order-form' ).on( 'submit', function() {
			$( '#publishing-action .spinner' ).css( 'visibility', 'visible' );

			if ( $( '.orderitems tr.no-items' ).is( ':visible' ) ) {
				$( '#edd-add-order-no-items-error' ).slideDown();
			} else {
				$( '#edd-add-order-no-items-error' ).slideUp();
			}

			if ( $( '.order-customer-info' ).is( ':visible' ) ) {
				$( '#edd-add-order-customer-error' ).slideDown();
			} else {
				$( '#edd-add-order-customer-error' ).slideUp();
			}

			if ( $( '.notice' ).is( ':visible' ) ) {
				$( '#publishing-action .spinner' ).css( 'visibility', 'hidden' );
				return false;
			}
		} );
	},
};

jQuery( document ).ready( function( $ ) {
	EDD_Add_Order.init();
} );
