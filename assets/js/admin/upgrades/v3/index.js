const EDD_v3_Upgrades = {
	init: function() {
		// Listen for toggle on the checkbox.
		$( 'edd-v3-migration-confirmation' ).on( 'change', ( e ) => {
			if ( this.checked ) {
				$( '#edd-v3-migration-button' ).removeClass( 'disabled' ).prop( 'disabled', false );
			} else {
				$( '#edd-v3-migration-button' ).addClass( 'disabled' ).prop( 'disabled', 'disabled' );
			}
		} );

		$( '#edd-v3-migration' ).on( 'submit', ( e ) => {
			e.preventDefault();

			EDD_v3_Upgrades.processStep( false, 1 );
		} )
	},

	processStep: function( upgrade_key, step ) {
		let data = {
			action: 'edd_process_v3_upgrade',
			_nonce: $( '#edd-v3-migration' ).find( '#_wpnonce' ).val(),
			upgrade_key: upgrade_key,
			step: step
		}

		$.ajax( {
			type: 'POST',
			data: data,
			url: ajaxurl,
			success: function( response ) {
				if ( response.data.upgrade_completed ) {
					EDD_v3_Upgrades.markUpgradeComplete( upgrade_key );
				}

				EDD_v3_Upgrades.processStep( response.data.next_upgrade, response.data.next_step );
			}
		} ).fail( ( data ) => {
			// @todo
		} )
	},

	markUpgradeComplete: function( upgrade_key ) {

	}
}

jQuery( document ).ready( function( $ ) {
	EDD_v3_Upgrades.init();
} );
