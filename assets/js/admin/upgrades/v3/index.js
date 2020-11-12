const EDD_v3_Upgrades = {
	inProgress: false,

	init: function() {
		// Listen for toggle on the checkbox.
		$( '.edd-v3-migration-confirmation' ).on( 'change', function( e ) {
			const wrapperForm = $( this ).closest( '.edd-v3-migration' );
			const formSubmit = wrapperForm.find( 'button' );

			if ( e.target.checked ) {
				formSubmit.removeClass( 'disabled' ).prop( 'disabled', false );
			} else {
				formSubmit.addClass( 'disabled' ).prop( 'disabled', true );
			}
		} );

		$( '.edd-v3-migration' ).on( 'submit', function( e ) {
			e.preventDefault();

			if ( EDD_v3_Upgrades.inProgress ) {
				return;
			}

			EDD_v3_Upgrades.inProgress = true;

			const migrationForm = $( this );
			const upgradeKeyField = migrationForm.find( 'input[name="upgrade_key"]' );
			let upgradeKey = false;

			if ( upgradeKeyField.length && upgradeKeyField.val() ) {
				upgradeKey = upgradeKeyField.val();
			}

			// Disable submit button.
			migrationForm.find( 'button' )
				.removeClass( 'button-primary' )
				.addClass( 'button-secondary disabled updating-message' )
				.prop( 'disabled', true );

			// Disable checkbox.
			migrationForm.find( 'input' ).prop( 'disabled', true );

			// If this is the main migration, mark the first non-complete item as in progress.
			if ( 'edd-v3-migration' === migrationForm.attr( 'id' ) ) {
				const firstNonCompleteUpgrade = $( '#edd-migration-progress li:not(.edd-upgrade-complete)' );
				if ( firstNonCompleteUpgrade.length ) {
					EDD_v3_Upgrades.markUpgradeInProgress( firstNonCompleteUpgrade.data( 'upgrade' ) );
				}
			}

			EDD_v3_Upgrades.processStep( upgradeKey, 1, migrationForm.find( 'input[name="_wpnonce"]' ).val() );
		} )
	},

	processStep: function( upgrade_key, step, nonce ) {
		let data = {
			action: 'edd_process_v3_upgrade',
			_ajax_nonce: nonce,
			upgrade_key: upgrade_key,
			step: step
		}

		$.ajax( {
			type: 'POST',
			data: data,
			url: ajaxurl,
			success: function( response ) {
				if ( response.data.upgrade_completed ) {
					EDD_v3_Upgrades.markUpgradeComplete( response.data.upgrade_processed );

					// If we just completed legacy data removal then we're all done!
					if ( 'v30_legacy_data_removed' === response.data.upgrade_processed ) {
						EDD_v3_Upgrades.legacyDataRemovalComplete();

						return;
					}
				} else if( response.data.percentage ) {
					// Update percentage for the upgrade we just processed.
					EDD_v3_Upgrades.updateUpgradePercentage( response.data.upgrade_processed, response.data.percentage );
				}

				if ( response.data.next_upgrade && 'v30_legacy_data_removed' === response.data.next_upgrade ) {
					EDD_v3_Upgrades.inProgress = false;

					// Legacy data removal is next, which we do not start automatically.
					EDD_v3_Upgrades.showLegacyDataRemoval();
				} else if ( response.data.next_upgrade ) {
					// If this is a different upgrade, mark the next one as in process.
					if ( response.data.next_upgrade !== response.data.upgrade_processed ) {
						EDD_v3_Upgrades.markUpgradeInProgress( response.data.next_upgrade );
					}

					// Start the next upgrade (or continuation of current) automatically.
					EDD_v3_Upgrades.processStep( response.data.next_upgrade, response.data.next_step, response.data.nonce );
				} else {
					EDD_v3_Upgrades.inProgress = false;
					EDD_v3_Upgrades.stopAllSpinners();
				}
			}
		} ).fail( ( data ) => {
			// @todo
		} )
	},

	markUpgradeInProgress: function( upgradeKey ) {
		const upgradeRow = $( '#edd-v3-migration-' + upgradeKey );
		if ( ! upgradeRow.length ) {
			return;
		}

		const statusIcon = upgradeRow.find( '.dashicons' );
		if ( ! statusIcon.length ) {
			return;
		}

		statusIcon.removeClass( 'dashicons-ellipsis' ).addClass( 'dashicons-update' );

		upgradeRow.find( '.edd-migration-percentage' ).removeClass( 'edd-hidden' );
	},

	updateUpgradePercentage: function( upgradeKey, newPercentage ) {
		const upgradeRow = $( '#edd-v3-migration-' + upgradeKey );
		if ( ! upgradeRow.length ) {
			return;
		}

		upgradeRow.find( '.edd-migration-percentage-value' ).text( newPercentage );
	},

	markUpgradeComplete: function( upgradeKey ) {
		const upgradeRow = $( '#edd-v3-migration-' + upgradeKey );
		if ( ! upgradeRow.length ) {
			return;
		}

		upgradeRow.addClass( 'edd-upgrade-complete' );

		const statusIcon = upgradeRow.find( '.dashicons' );
		if ( ! statusIcon.length ) {
			return;
		}

		statusIcon.removeClass( 'dashicons-ellipsis' ).addClass( 'dashicons-yes' );

		// Update percentage to 100%;
		upgradeRow.find( '.edd-migration-percentage-value' ).text( 100 );
	},

	showLegacyDataRemoval: function() {
		// Un-spin the main submit button.
		$( '#edd-v3-migration-button' ).removeClass( 'updating-message' );

		// Show the "migration complete" message.
		$( '#edd-v3-migration-complete' ).removeClass( 'edd-hidden' );

		const dataRemovalWrapper = $( '#edd-v3-remove-legacy-data' );
		if ( ! dataRemovalWrapper.length ) {
			return;
		}

		dataRemovalWrapper.removeClass( 'edd-hidden' );
	},

	legacyDataRemovalComplete: function() {
		const wrapper = $( '#edd-v3-remove-legacy-data' );
		if ( ! wrapper.length ) {
			return;
		}

		wrapper.find( 'form' ).addClass( 'edd-hidden' );
		wrapper.find( '#edd-v3-legacy-data-removal-complete' ).removeClass( 'edd-hidden' );
	},

	stopAllSpinners: function() {

	}
}

jQuery( document ).ready( function( $ ) {
	EDD_v3_Upgrades.init();
} );
