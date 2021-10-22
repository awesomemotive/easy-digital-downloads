/* global eddNotificationsVars */

document.addEventListener( 'alpine:init', () => {
	Alpine.store( 'eddNotifications', {
		isPanelOpen: false,
		notificationsLoaded: false,
		activeNotifications: [],
		inactiveNotifications: [],

		openPanel: function() {
			if ( this.notificationsLoaded ) {
				this.isPanelOpen = true;
				return;
			}

			this.isPanelOpen = true;

			this.getNotifications()
				.catch( error => {
					console.log( 'Notification error', error );
				} );
		},

		closePanel: function() {
			if ( this.isPanelOpen ) {
				this.isPanelOpen = false;
			}
		},

		getNotifications: function() {
			return fetch( eddNotificationsVars.restBase + '/notifications', {
				method: 'GET',
				credentials: 'same-origin',
				headers: {
					'Content-Type': 'application/json',
					'X-WP-Nonce': eddNotificationsVars.restNonce
				}
			} ).then( response => {
				if ( ! response.ok ) {
					return Promise.reject( response );
				}

				return response.json();
			} ).then( data => {
				this.activeNotifications = data.active;
				this.inactiveNotifications = data.dismissed;
				this.notificationsLoaded = true;
			} );
		},

		dismiss: function( id ) {
			console.log( 'Dismissing', id );
		}
	} );
} );
