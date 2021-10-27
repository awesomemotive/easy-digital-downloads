/* global eddNotificationsVars */

document.addEventListener( 'alpine:init', () => {
	Alpine.store( 'eddNotifications', {
		isPanelOpen: false,
		notificationsLoaded: false,
		numberActiveNotifications: 0,
		activeNotifications: [],
		inactiveNotifications: [],

		init: function() {
			const eddNotifications = this;

			document.addEventListener( 'keydown', function( e ) {
				if ( e.key === 'Escape' ) {
					eddNotifications.closePanel();
				}
			} );
		},

		openPanel: function() {
			const panelHeader = document.getElementById( 'edd-notifications-header' );

			if ( this.notificationsLoaded ) {
				this.isPanelOpen = true;
				if ( panelHeader ) {
					panelHeader.focus();
				}

				return;
			}

			this.isPanelOpen = true;

			this.apiRequest( '/notifications', 'GET' )
				.then( data => {
					this.activeNotifications = data.active;
					this.inactiveNotifications = data.dismissed;
					this.notificationsLoaded = true;

					if ( panelHeader ) {
						panelHeader.focus();
					}
				} )
				.catch( error => {
					console.log( 'Notification error', error );
				} );
		},

		closePanel: function() {
			this.isPanelOpen = false;
		},

		apiRequest: function( endpoint, method ) {
			return fetch( eddNotificationsVars.restBase + endpoint, {
				method: method,
				credentials: 'same-origin',
				headers: {
					'Content-Type': 'application/json',
					'X-WP-Nonce': eddNotificationsVars.restNonce
				}
			} ).then( response => {
				if ( ! response.ok ) {
					return Promise.reject( response );
				}

				/*
				 * Returning response.text() instead of response.json() because dismissing
				 * a notification doesn't return a JSON response, so response.json() will break.
				 */
				return response.text();
				//return response.json();
			} ).then( data => {
				return data ? JSON.parse( data ) : null;
			} );
		} ,

		dismiss: function( event, index ) {
			if ( 'undefined' === typeof this.activeNotifications[ index ] ) {
				return;
			}

			event.target.disabled = true;

			const notification = this.activeNotifications[ index ];

			this.apiRequest( '/notifications/' + notification.id, 'DELETE' )
				.then( response => {
					this.activeNotifications.splice( index, 1 );
					this.numberActiveNotifications = this.activeNotifications.length;
				} )
				.catch( error => {
					console.log( 'Dismiss error', error );
				} );
		}
	} );
} );
