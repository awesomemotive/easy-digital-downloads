/**
 * Onboarding Wizard.
 */

 var EDD_Onboarding = {
	vars: {},
	init: function() {
		this.init_step_buttons();
		this.init_upload_buttons();
		this.start_onboarding();

		console.log( EDDExtensionManager );

		// Initialize current step logic.
		let current_step = EDD_Onboarding.get_step_class( $( '.edd-onboarding_current-step' ).val() );
		if ( current_step ) {
			EDD_Onboarding[ current_step ].init();
		}
	},
	loading_state: function( state ) {
		$( '.edd-onboarding__loading' ).toggle( state );
	},
	init_step_buttons: function() {
		// Go back and skip button.
		$( document.body ).on( 'click', '.edd-onboarding__button-back, .edd-onboarding__button-skip-step', function( e ) {
			e.preventDefault();
			EDD_Onboarding.load_step( $( this ).data( 'step' ) );
		} );

		// Save button.
		$( document.body ).on( 'click', '.edd-onboarding__button-save-step', function( e ) {
			e.preventDefault();

			let step_class = EDD_Onboarding.get_step_class( $( '.edd-onboarding_current-step' ).val() );
			if ( step_class ) {
				EDD_Onboarding[ step_class ].save();
			}

		} );
	},
	init_upload_buttons: function() {
		let file_frame;
		window.form_upload_field = false;
		$( document.body ).on( 'click', '.edd_settings_upload_button', function( e ) {
			e.preventDefault();

			const button = $( this );
			window.form_upload_field = $( this ).parent().prev();

			// If the media frame already exists, reopen it.
			if ( file_frame ) {
				//file_frame.uploader.uploader.param( 'post_id', set_to_post_id );
				file_frame.open();
				return;
			}

			// Create the media frame.
			file_frame = wp.media.frames.file_frame = wp.media( {
				title: button.data( 'uploader_title' ),
				library: { type: 'image' },
				button: { text: button.data( 'uploader_button_text' ) },
				multiple: false,
			} );

			file_frame.on( 'menu:render:default', function( view ) {
				// Store our views in an object.
				const views = {};

				// Unset default menu items
				view.unset( 'library-separator' );
				view.unset( 'gallery' );
				view.unset( 'featured-image' );
				view.unset( 'embed' );

				// Initialize the views in our view object.
				view.set( views );
			} );

			// When an image is selected, run a callback.
			file_frame.on( 'select', function() {
				const selection = file_frame.state().get( 'selection' );
				selection.each( function( attachment, index ) {
					attachment = attachment.toJSON();
					window.form_upload_field.val( attachment.url );
					// Check if we have a field for attachment ID connected to this upload button.
					if ( window.form_upload_field.data( 'attachment-id-field' ) ) {
						$( window.form_upload_field.data( 'attachment-id-field' ) ).val( attachment.id );
					}
				} );
			} );

			// Finally, open the modal
			file_frame.open();
		} );
	},
	start_onboarding: function() {
		$( document.body ).on( 'click', '.edd-onboarding__welcome-screen-get-started', function( e ) {
			e.preventDefault();
			$('.edd-onboarding__welcome-screen-get-started' ).html( 'LOADING...' );
			EDD_Onboarding.loading_state( true );

			var postData = {
				action: 'edd_onboarding_started',
				page: 'edd-onboarding-wizard',
				// _wpnonce: nonce,
			};
			$.post(
				ajaxurl,
				postData,
				function() {
					$( '.edd-onboarding__welcome-screen' ).fadeOut();
					$( '.edd-onboarding__steps' ).slideDown();
					$( '.edd-onboarding__steps, .edd-onboarding__after-welcome-screen, .edd-onboarding__close-and-exit' ).slideDown();
					EDD_Onboarding.loading_state( false );
				}
			);

		} );
	},
	load_step: function( step_name ) {
		EDD_Onboarding.loading_state( true );

		$.ajax( {
			type: 'GET',
			dataType: 'html',
			url: ajaxurl,
			data: {
				action: 'edd_onboarding_load_step',
				page: 'edd-onboarding-wizard',
				current_step: step_name,
				// _wpnonce: nonce,
			},
			success: function( data ) {
				// Replace step screen.
				$( '.edd-onboarding__current-step' ).html( data );

				let step_class = EDD_Onboarding.get_step_class( step_name );
				if ( step_class ) {
					EDD_Onboarding[ step_class ].init();
				}

				// Scroll step into view.
				setTimeout( function() {
					$('html, body').animate( { scrollTop: $( '.edd-onboarding__wrapper' ).offset().top - 45 }, 800 );
				}, 150 );

				// Change GET parameter to the current step.
				let query_params = new URLSearchParams( window.location.search );
				query_params.set( 'current_step', step_name );
				history.replaceState( null, null, '?' + query_params.toString() );

			},
		} ).fail( function( response ) {
			if ( window.console && window.console.log ) {
				console.log( response );
			}
		} ).done( function( response ) {
			EDD_Onboarding.loading_state( false );
		} );
	},
	next_step: function() {
		let next_step = $( '.edd-onboarding__button-save-step' ).data( 'step' );
		EDD_Onboarding.load_step( next_step );
	},
	get_step_class: function( step_name ) {
		let step_class = 'EDD_Onboarding_' + step_name.split( '_' ).map(element => {
			return element.charAt( 0 ).toUpperCase() + element.slice( 1 ).toLowerCase();
		} ).join( '_' );

		if ( typeof EDD_Onboarding[ step_class ] == 'undefined' ) {
			return false;
		}

		return step_class;
	},

	/**
	 * Specific steps logic.
	 */
	 EDD_Onboarding_Business_Info: {
		init: function() {},
		save: function() {
			$.ajax( {
				type: 'POST',
				url: $('.edd-settings-form').attr("action"),
				data: $('.edd-settings-form').serialize(),
				beforeSend: function() {
					EDD_Onboarding.loading_state( true );
				},
				success: function( data ) {
					EDD_Onboarding.next_step();
				},
			} ).fail( function( response ) {
				if ( window.console && window.console.log ) {
					console.log( response );
				}
			} );
		}
	},

	EDD_Onboarding_Payment_Methods: {
		init: function() {
			// If Stripe connectioon exsists, fetch the current account details.
			let stripe_connect_account = $( '#edds-stripe-connect-account' );
			let stripe_connect_actions = $( '#edds-stripe-disconnect-reconnect' )
			if ( stripe_connect_account ) {
				$.ajax( {
					type: 'POST',
					url: ajaxurl,
					data: {
						action: 'edds_stripe_connect_account_info',
						accountId: stripe_connect_account.data( 'account-id' ),
						nonce: stripe_connect_account.data( 'nonce' ),
					},
					success: function( response ) {
						if ( response.success ) {
							stripe_connect_account.html(  response.data.message );
							stripe_connect_account.addClass( `notice-${ response.data.status }` );
							if ( response.data.actions ) {
								stripe_connect_actions.html( response.data.actions );
							}
						} else {
							stripe_connect_account.html( response.data.message );
							stripe_connect_account.addClass( 'notice-error' );
						}
					},
				} ).fail( function( response ) {

				} );
			}
		},
		save: function() {
			EDD_Onboarding.next_step();
		},
	},

	EDD_Onboarding_Configure_Emails: {
		init: function() {},
		save: function() {
			$.ajax( {
				type: 'POST',
				url: $('.edd-settings-form').attr("action"),
				data: $('.edd-settings-form').serialize(),
				beforeSend: function() {
					EDD_Onboarding.loading_state( true );
				},
				success: function( data ) {
					EDD_Onboarding.next_step();
				},
			} ).fail( function( response ) {
				if ( window.console && window.console.log ) {
					console.log( response );
				}
			} );
		}
	},

	EDD_Onboarding_Tools: {
		init: function() {
			this.get_selected_plugins();
			$( document.body ).on( 'change', '.edd-onboarding__plugin-install', function() {
				EDD_Onboarding.EDD_Onboarding_Tools.get_selected_plugins();
			} );
		},
		get_selected_plugins: function() {
			let selected_plugins = [];
			$( '.edd-onboarding__plugin-install:checked:not(:disabled)' ).each( function() {
				selected_plugins.push( $(this).data( 'plugin-name' ) );
			});

			$( '.edd-onboarding__selected-plugins-text' ).html( selected_plugins.join( ', ' ) );
		},
		save: async function() {
			EDD_Onboarding.loading_state( true )

			let selected_plugins = [];
			let ajax_promises    = [];

			// Get selected plugins.
			$( '.edd-onboarding__plugin-install:checked:not(:disabled)' ).each( function() {
				selected_plugins.push({
					plugin_url: $(this).val(),
					plugin_name: $(this).data( 'plugin-name' ),
				});
			} );

			// Instal and activate selected plugins.
			// todo - MAKE AJAX CALL SEQUENTIAL!
			selected_plugins.forEach( function ( plugin ) {

				ajax_promises.push(
					new Promise((resolve, reject) => {
						let data = {
							action: 'edd_install_extension',
							nonce: EDDExtensionManager.extension_manager_nonce,
							plugin: plugin.plugin_url,
							type: 'plugin',
						};

						$.post( ajaxurl, data )
						.done( function ( res ) {
							console.log( res );
							// var thisStep = $btn.closest( '.edd-extension-manager__step' );
							if ( res.success ) {
								// var nextStep = thisStep.next();
								// if ( nextStep.length ) {
								// 	thisStep.fadeOut();
								// 	nextStep.prepend( '<div class="notice inline-notice notice-success"><p>' + res.data.message + '</p></div>' );
								// 	nextStep.fadeIn();
								// }
							} else {
								// thisStep.fadeOut();
								// var message = res.data.message;
								// /**
								//  * The install class returns an array of error messages, and res.data.message will be undefined.
								//  * In that case, we'll use the standard failure messages.
								//  */
								// if ( ! message ) {
								// 	if ( 'plugin' !== type ) {
								// 		message = EDDExtensionManager.extension_install_failed;
								// 	} else {
								// 		message = EDDExtensionManager.plugin_install_failed;
								// 	}
								// }
								// thisStep.after( '<div class="notice inline-notice notice-warning"><p>' + message + '</p></div>' );
							}
							resolve();
						} );

					})
				);

			} );

			// Save details about telemetry.

			return Promise.all( ajax_promises ).then( function() {
				EDD_Onboarding.loading_state( false )
			} );

		}
	},

	EDD_Onboarding_Products: {
		init: function() {},
		save: function() {
			let form = $('.edd-onboarding__create-product-form');
			if ( ! form[0].reportValidity() ) {
				return;
			}

			let form_details = Object.fromEntries( new FormData( form[0] ) );

			$.ajax( {
				type: 'POST',
				url: ajaxurl,
				data: {
					action: 'edd_onboarding_create_product',
					page: 'edd-onboarding-wizard',
					...form_details
				},
				beforeSend: function() {
					EDD_Onboarding.loading_state( true );
				},
				success: function( data ) {
					if ( data.success ) {
						window.location = decodeURI( data.redirect_url.replace(/&amp;/g, "&")  );
					} else {
						EDD_Onboarding.loading_state( false );
					}
				},
			} ).fail( function( response ) {
				if ( window.console && window.console.log ) {
					console.log( response );
				}
			} );
		}
	}
};

jQuery( document ).ready( function( $ ) {
	EDD_Onboarding.init();
} );
