/**
 * Onboarding Wizard.
 */

/**
 * Internal dependencies
 */
import { edd_attach_tooltips as setup_tooltips } from 'admin/components/tooltips';

var EDD_Onboarding = {

	vars: {
		nonce: '',
	},

	/**
	 * Run when page is loaded
	 * to initialize logic.
	 *
	 * @since 3.1
	 *
	 */
	init: function() {
		EDD_Onboarding.vars.nonce = $( '#_wpnonce' ).val();

		this.init_step_buttons();
		this.init_upload_buttons();
		this.start_onboarding();

		// Run current step logic.
		let current_step = EDD_Onboarding.get_step_class( $( '.edd-onboarding_current-step' ).val() );
		if ( current_step ) {
			EDD_Onboarding[ current_step ].init();
		}
	},

	/**
	 * Toggle the loading overlay.
	 *
	 * @since 3.1
	 *
	 * @param {bool} state True to show the loader, false to hide it.
	 */
	loading_state: function( state ) {
		$( '.edd-onboarding__loading-status' ).empty();
		$( '.edd-onboarding__loading' ).toggle( state );
		$( '.edd-onboarding' ).toggleClass( 'edd-onboarding__loading-in-progress', state );
	},

	/**
	 * Attach listeners to the control buttons.
	 *
	 * @since 3.1
	 *
	 */
	init_step_buttons: function() {
		// Go back button.
		$( document.body ).on( 'click', '.edd-onboarding__button-back', function( e ) {
			e.preventDefault();
			EDD_Onboarding.load_step( $( '.edd-onboarding_current-previous-step' ).val() );
		} );

		// Skip step button.
		$( document.body ).on( 'click', '.edd-onboarding__button-skip-step', function( e ) {
			e.preventDefault();
			EDD_Onboarding.next_step( true );
		} );

		// Save button.
		$( document.body ).on( 'click', '.edd-onboarding__button-save-step', function( e ) {
			e.preventDefault();
			let step_class = EDD_Onboarding.get_step_class( $( '.edd-onboarding_current-step' ).val() );
			if ( step_class ) {
				EDD_Onboarding[ step_class ].save().then( function() {
					EDD_Onboarding.next_step();
				} );
			}
		} );

		// Close and exit.
		$( document.body ).on( 'click', '.edd-onboarding__dismiss', function ( e ) {
			EDD_Onboarding.onboarding_skipped( true, e.target );
		} );
	},

	/**
	 * Upload image buttons.
	 *
	 * @since 3.1
	 *
	 */
	init_upload_buttons: function() {
		let file_frame;
		window.form_upload_field = false;
		$( document.body ).on( 'click', '.edd_settings_upload_button', function( e ) {
			e.preventDefault();

			const button = $( this );

			window.form_upload_field = $( button.data('input') );

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
				view.unset( 'playlist' );
				view.unset( 'video-playlist' );

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

	/**
	 * Attach listeners for when
	 * user starts its onboarding process.
	 *
	 * @since 3.1
	 *
	 */
	start_onboarding: function() {
		$( document.body ).on( 'click', '.edd-onboarding__welcome-screen-get-started', function( e ) {
			e.preventDefault();
			EDD_Onboarding.loading_state( true );

			var postData = {
				action: 'edd_onboarding_started',
				page: 'edd-onboarding-wizard',
				_wpnonce: EDD_Onboarding.vars.nonce,
			};
			$.post(
				ajaxurl,
				postData,
				function() {
					$( '.edd-onboarding__welcome-screen' ).hide();
					$( '.edd-onboarding__steps, .edd-onboarding__after-welcome-screen, .edd-onboarding__close-and-exit' ).show();
					EDD_Onboarding.loading_state( false );
				}
			);

		} );
	},

	/**
	 * Mark the Onboarding process
	 * as completed and redirect user.
	 *
	 * @since 3.1
	 *
	 */
	onboarding_completed: function( redirect ) {
		EDD_Onboarding.loading_state( true );

		var postData = {
			action: 'edd_onboarding_completed',
			page: 'edd-onboarding-wizard',
			_wpnonce: EDD_Onboarding.vars.nonce,
		};
		return $.post(
			ajaxurl,
			postData,
			function() {
				if ( redirect ) {
					window.location = $( '#edd-onboarding__exit' ).val();
				}
			}
		);
	},


	/**
	 * Mark the Onboarding process
	 * as skipped and redirect user.
	 *
	 * @since 3.1
	 *
	 */
	onboarding_skipped: function ( redirect, target ) {
		if ( !target.classList.contains( 'edd-promo-notice-dismiss' ) ) {
			EDD_Onboarding.loading_state( true );
		}

		var postData = {
			action: 'edd_onboarding_skipped',
			page: 'edd-onboarding-wizard',
			_wpnonce: EDD_Onboarding.vars.nonce,
		};
		return $.post(
			ajaxurl,
			postData,
			function() {
				if ( redirect ) {
					window.location = $( '#edd-onboarding__exit' ).val();
				}
			}
		);
	},


	/**
	 * Fetch the HTML for a specific
	 * requested step and load it onto screen.
	 *
	 * @since 3.1
	 *
	 * @param {string} step_name Step name.
	 */
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
				_wpnonce: EDD_Onboarding.vars.nonce,
			},
			success: function( data ) {
				// Replace step screen.
				$( '.edd-onboarding__current-step' ).html( data );

				// Run step specific logic.
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

				// Load tooltips.
				setup_tooltips( $( '.edd-help-tip' ) );

				// Reload email tags.
				document.dispatchEvent( new Event( 'DOMContentLoaded' ) );

			},
		} ).fail( function( response ) {
			if ( window.console && window.console.log ) {
				console.log( response );
			}
		} ).done( function( response ) {
			EDD_Onboarding.loading_state( false );
		} );
	},

	/**
	 * Load next step in the sequence.
	 *
	 * @since 3.1
	 *
	 */
	next_step: function( skipped = false ) {
		let next_step = $( '.edd-onboarding_current-next-step' ).val();
		if ( '' === next_step ) {
			if ( skipped ) {
				EDD_Onboarding.onboarding_completed( true );
			} else {
				return false;
			}
		}

		EDD_Onboarding.load_step( next_step );
	},

	/**
	 * Transform step name to pascal case and return
	 * transformed string name. False if class object does not exist.
	 *
	 * @since 3.1
	 *
	 * @param {string} step_name Step name.
	 */
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
		/**
		 * Initialize step specific logic.
		 *
		 * @since 3.1
		 *
		 */
		init: function() {},

		/**
		 * Save settings fields.
		 *
		 * @since 3.1
		 *
		 */
		save: function() {
			return $.ajax( {
				type: 'POST',
				url: $('.edd-settings-form').attr("action"),
				data: $('.edd-settings-form').serialize(),
				beforeSend: function() {
					EDD_Onboarding.loading_state( true );
				},
				success: function( data ) {
				},
			} ).fail( function( response ) {
				if ( window.console && window.console.log ) {
					console.log( response );
				}
			} );
		}
	},

	EDD_Onboarding_Payment_Methods: {
		/**
		 * Initialize step specific logic.
		 *
		 * @since 3.1
		 *
		 */
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
						onboardingWizard: true,
					},
					success: function( response ) {
						stripe_connect_account.removeClass( 'loading' )
						stripe_connect_actions.removeClass( 'loading' );

						// Account is sucessfully connected.
						if ( response.success ) {
							stripe_connect_account.html( response.data.message );
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
		/**
		 * There is nothing to save in this step.
		 *
		 * @since 3.1
		 *
		 */
		save: function() {
			return Promise.resolve();
		},
	},

	EDD_Onboarding_Configure_Emails: {

		vars: {
			wp_editor: false,
		},

		/**
		 * Initialize step specific logic.
		 *
		 * @since 3.1
		 *
		 */
		init: function() {
			// If WP Editor is already initialized, we have to destroy it first.
			if ( EDD_Onboarding.EDD_Onboarding_Configure_Emails.vars.wp_editor ) {
				wp.editor.remove( 'edd_settings_purchase_receipt' )
			}

			wp.editor.initialize(
				'edd_settings_purchase_receipt',
				{
				  tinymce: {
					wpautop: true,
					plugins : 'charmap colorpicker compat3x directionality fullscreen hr image lists media paste tabfocus textcolor wordpress wpautoresize wpdialogs wpeditimage wpemoji wpgallery wplink wptextpattern wpview',
					toolbar1: 'bold italic underline strikethrough | bullist numlist | blockquote hr wp_more | alignleft aligncenter alignright | link unlink | fullscreen | wp_adv',
					toolbar2: 'formatselect alignjustify forecolor | pastetext removeformat charmap | outdent indent | undo redo | wp_help'
				  },
				  quicktags: true,
				  mediaButtons: true,
				}
			  );

			// Append "Insert marker" button.
			$( '#edd-onboarding__insert-marker-button a' ).clone().appendTo( '.wp-media-buttons' );

			EDD_Onboarding.EDD_Onboarding_Configure_Emails.vars.wp_editor = true;
		},

		/**
		 * Save settings fields.
		 *
		 * @since 3.1
		 *
		 */
		save: function() {
			let editor_id                = 'edd_settings_purchase_receipt';
			let purchase_receipt_content = $( '#' + editor_id ).val();

			if( tinymce.get( editor_id ) ) {
				purchase_receipt_content = wp.editor.getContent(editor_id);
			}

			let data = {
				action: 'edd_onboarding_save_email',
				content: purchase_receipt_content,
				email_logo: $( '#email_logo' ).val(),
				from_name: $( '#from_name' ).val(),
				from_email: $( '#from_email' ).val(),
				nonce: EDD_Onboarding.vars.nonce,
			};

			return $.ajax( {
				type: 'POST',
				url: ajaxurl,
				data: data,
				beforeSend: function() {
					EDD_Onboarding.loading_state( true );
				},
				success: function( data ) {
				},
			} ).fail( function( response ) {
				if ( window.console && window.console.log ) {
					console.log( response );
				}
			} );
		}
	},

	EDD_Onboarding_Tools: {
		/**
		 * Initialize step specific logic.
		 *
		 * @since 3.1
		 *
		 */
		init: function() {
			this.get_selected_plugins();
			$( document.body ).on( 'change', '.edd-onboarding__plugin-install', function() {
				EDD_Onboarding.EDD_Onboarding_Tools.get_selected_plugins();
			} );
		},

		/**
		 * Check which plugins are selected and
		 * update the UI accordingly.
		 *
		 * @since 3.1
		 *
		 */
		get_selected_plugins: function() {
			let selected_plugins = [];

			$( '.edd-onboarding__selected-plugins' ).show();
			$( '.edd-onboarding__plugin-install:checked:not(:disabled)' ).each( function() {
				if ( $( this ).data( 'plugin-name' ) && $( this ).data( 'action' ).length > 0 ) {
					selected_plugins.push( $( this ).data( 'plugin-name' ) );
				}
			});

			$( '.edd-onboarding__selected-plugins-text' ).html( selected_plugins.join( ', ' ) );

			if ( selected_plugins.length === 0 ) {
				$( '.edd-onboarding__selected-plugins' ).hide();
			}
		},

		/**
		 * Handle saving of user telemetry settings and
		 * installation/activation of selected plugins.
		 * If there is an error it will show a specific error page.
		 *
		 * @since 3.1
		 *
		 */
		save: async function() {
			EDD_Onboarding.loading_state( true )

			// Save user telemetry details.
			await $.post(
					ajaxurl,
					{
						action: 'edd_onboarding_telemetry_settings',
						page: 'edd-onboarding-wizard',
						telemetry_toggle: $( '#edd-onboarding__telemery-toggle' ).is( ':checked' ),
						auto_register: $( '#auto-register' ).is( ':checked' ),
						_wpnonce: EDD_Onboarding.vars.nonce,
					},
					function() {
					}
				);

			// Get selected plugins.
			let selected_plugins = [];
			let installation_errors = [];
			$( '.edd-onboarding__plugin-install:checked:not(:disabled)' ).each( function() {
				if ( $( this ).data( 'plugin-name' ) && $( this ).data( 'action' ).length > 0 ) {
					selected_plugins.push({
						plugin_name: $(this).data( 'plugin-name' ),
						plugin_file: $(this).data( 'plugin-file' ),
						plugin_url: $(this).val(),
						action: $(this).data( 'action' ),
					});
				}
			} );

			// Install and activate selected plugins.
			for ( let plugin in selected_plugins ) {
				await new Promise((resolve, reject) => {
					let action      = selected_plugins[ plugin ].action;
					let plugin_key  = '';
					let ajax_action = '';
					let loader_text = '';

					switch ( action ) {
						case 'activate':
							ajax_action  = 'edd_activate_extension';
							loader_text  = EDDExtensionManager.activating;
							plugin_key   = selected_plugins[ plugin ].plugin_file;
							break;

						case 'install':
							ajax_action  = 'edd_install_extension';
							loader_text  = EDDExtensionManager.installing;
							plugin_key   = selected_plugins[ plugin ].plugin_url;
							break;
					}

					// Update loading text.
					$( '.edd-onboarding__loading-status' ).html( loader_text + ' ' + selected_plugins[plugin].plugin_name + '...' );

					let data = {
						action: ajax_action,
						nonce: EDDExtensionManager.extension_manager_nonce,
						plugin: plugin_key,
						type: 'plugin',
					};

					$.post( ajaxurl, data )
					.done( function ( res ) {
						if ( ! res.success ) {
							installation_errors.push( selected_plugins[plugin].plugin_name );
						}
						// Activation can happen very fast, so we want a fake delay for the UI.
						setTimeout( function() {
							resolve();
						}, 1500 );
					} );
				})
			}

			// All of the plugins were installed and activated.
			if ( installation_errors.length === 0 ) {
				if ( selected_plugins.length === 0 ) {
					return Promise.resolve();
				}

				// Show success screen.
				return new Promise( (resolve, reject) => {
					EDD_Onboarding.loading_state( false );
					$( '.edd-onboarding' ).toggleClass( 'edd-onboarding__loading-in-progress', true );
					$( '.edd-onboarding__install-success-wrapper' ).show();
					setTimeout(() => {
						$( '.edd-onboarding__install-success-wrapper' ).hide();
						$( '.edd-onboarding' ).toggleClass( 'edd-onboarding__loading-in-progress', false );
						resolve();
					}, 3200);
				});
			}

			// There were some errors while installing/activating.
			$( '.edd-onboarding__failed-plugins-text' ).html( installation_errors.join( ', ' ) );
			$( '.edd-onboarding__steps-indicator, .edd-onboarding__single-step-title, .edd-onboarding__single-step-subtitle, .edd-onboarding__single-step-footer, .edd-onboarding__install-plugins' ).slideUp();
			$( '.edd-onboarding__install-failed' ).slideDown();
			EDD_Onboarding.loading_state( false )

			return Promise.reject();
		}
	},

	EDD_Onboarding_Products: {
		/**
		 * Initialize step specific logic.
		 *
		 * @since 3.1
		 *
		 */
		init: function() {
			EDD_Onboarding.EDD_Onboarding_Products.init_variable_pricing_toggle();
			EDD_Onboarding.EDD_Onboarding_Products.init_files_toggle();
			$( '#edd_download_files' ).show();
		},

		/**
		 * Toggle between single price
		 * and variable price options.
		 *
		 * @since 3.1
		 *
		 */
		 init_variable_pricing_toggle: function() {
			$( document.body ).on( 'click', '.edd-onboarding__pricing-option-pill button', function( e ) {
				e.preventDefault();
				let is_variable_pricing = $( this ).data( 'variable-pricing' );

				// Toggle checkbox.
				$( '#edd_variable_pricing' ).prop( 'checked', is_variable_pricing );
				$( '#edd_variable_pricing' ).trigger( 'change' );
			} );


			$( document.body ).on( 'change', '#edd_variable_pricing', function( e ) {
				e.preventDefault();
				let is_variable_pricing = this.checked;

				// Active pill state.
				$( '.edd-onboarding__pricing-option-pill .active' ).removeClass( 'active' );
				$( '.edd-onboarding__pricing-option-pill button[data-variable-pricing="' + is_variable_pricing + '"]' ).addClass( 'active' );

				$( '.edd-onboarding__product-single-price' ).show();
				$( '.edd-onboarding__product-variable-price' ).hide();

				// Toggle views.
				if ( is_variable_pricing ) {
					$( '.edd-onboarding__product-variable-price' ).show();
					$( '.edd-onboarding__product-single-price' ).hide();
				}

				$( '.edd-onboarding__product-files-wrapper' ).show();
			} );
		},

		/**
		 * Toggle file uploading.
		 *
		 * @since 3.1
		 *
		 */
		 init_files_toggle: function() {
			$( document.body ).on( 'change', '#_edd_upload_files', function( e ) {
				e.preventDefault();
				$( '.edd-onboarding__product-files-row' ).toggle( this.checked );
			} );
		},

		/**
		 * Save product details and upon
		 * success redirect to the created product.
		 *
		 * @since 3.1
		 *
		 */
		save: function() {
			let form = $('.edd-onboarding__create-product-form');
			if ( ! form[0].reportValidity() ) {
				return Promise.reject();
			}

			let form_details = Object.fromEntries( new FormData( form[0] ) );

			return $.ajax( {
				type: 'POST',
				url: ajaxurl,
				data: {
					action: 'edd_onboarding_create_product',
					page: 'edd-onboarding-wizard',
					_wpnonce: EDD_Onboarding.vars.nonce,
					...form_details
				},
				beforeSend: function() {
					EDD_Onboarding.loading_state( true );
				},
				success: function( data ) {
					if ( data.success ) {

						$( '.edd-onboarding__edit-my-product' ).attr( 'href', decodeURI( data.redirect_url.replace(/&amp;/g, "&")  ) );
						$( '.edd-onboarding__single-step-inner' ).addClass( 'equal' );
						$( '.edd-onboarding__create-product-form, .edd-onboarding__single-step-title, .edd-onboarding__single-step-subtitle, .edd-onboarding__single-step-footer, .edd-onboarding__close-and-exit' ).hide();
						$( '.edd-onboarding__product-created' ).show();

						EDD_Onboarding.onboarding_completed( false );
					}

					EDD_Onboarding.loading_state( false );
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
