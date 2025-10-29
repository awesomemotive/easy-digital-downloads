/**
 * User Verification
 *
 * Handles user email verification resend functionality with countdown and AJAX polling.
 *
 * @package EDD/Assets/JS
 * @since   3.5.3
 */

( function () {
	'use strict';

	/**
	 * User Verification Handler.
	 *
	 * @since 3.5.3
	 */
	class UserVerification {
		/**
		 * Constructor.
		 *
		 * @since 3.5.3
		 */
		constructor () {
			if ( typeof eddVerification === 'undefined' ) {
				console.error( 'UserVerification: eddVerification is not defined.' );
				return;
			}

			this.config = eddVerification;
			this.modal = null;
			this.pollInterval = null;
			this.countdownInterval = null;
			this.secondsRemaining = 0;

			this.init();
		}

		/**
		 * Initialize.
		 *
		 * @since 3.5.3
		 * @return {void}
		 */
		init () {
			// Initialize modal.
			this.modal = new globalThis.EDDModal( 'edd-verification-modal' );

			// Bind resend button.
			const resendButton = document.getElementById( 'edd-verification-resend' );
			resendButton?.addEventListener( 'click', () => this.handleResendClick() );
		}

		/**
		 * Handle resend button click.
		 *
		 * @since 3.5.3
		 * @return {void}
		 */
		handleResendClick () {
			this.modal.open( '<p>' + this.config.strings.sending + '</p>' );
			this.modal.showLoading();

			// Send AJAX request to resend verification email.
			this.sendResendRequest();
		}

		/**
		 * Send AJAX request to resend verification email.
		 *
		 * @since 3.5.3
		 * @return {void}
		 */
		sendResendRequest () {
			const formData = new FormData();
			formData.append( 'action', 'edd_resend_verification_email' );
			formData.append( 'nonce', this.config.nonce );

			fetch( this.config.ajax_url, {
				method: 'POST',
				body: formData,
				credentials: 'same-origin'
			} )
				.then( response => response.json() )
				.then( data => {
					this.modal.hideLoading();

					if ( data.success ) {
						this.showSuccessState( data.data.message );
						this.startCountdown();
					} else {
						this.showErrorState( data.data.message, data.data.seconds_remain );
					}
				} )
				.catch( error => {
					this.modal.hideLoading();
					this.showErrorState( this.config.strings.error );
					console.error( 'UserVerification error:', error );
				} );
		}

		/**
		 * Show success state with countdown.
		 *
		 * @since 3.5.3
		 * @param {string} message Success message.
		 * @return {void}
		 */
		showSuccessState ( message ) {
			const content = `
				<div class="edd-modal__message--success">
					<p>${ message }</p>
				</div>
				<p>${ this.config.strings.check_email }</p>
				<div class="edd-modal__countdown edd-countdown__seconds">
					${ this.config.countdown }
				</div>
				<p class="edd-modal__verifying">
					${ this.config.strings.verifying }
				</p>
			`;

			this.modal.setContent( content );
		}

	/**
	 * Show error state.
	 *
	 * @since 3.5.3
	 * @param {string} message Error message.
	 * @param {number} secondsRemain Seconds remaining for rate limit.
	 * @return {void}
	 */
	showErrorState ( message, secondsRemain ) {
		let content = `
			<div class="edd-modal__message--error">
				<p id="edd-verification-error-message">${ message }</p>
			</div>
		`;

		content += `
			<button type="button" class="button edd-modal__close-button" id="edd-modal__close-button">
				${ this.config.strings.close }
			</button>
		`;

		this.modal.setContent( content );

		// Start countdown if we have seconds remaining
		if ( secondsRemain && secondsRemain > 0 ) {
			this.secondsRemaining = secondsRemain;
			this.startErrorCountdown();
		}
	}

	/**
	 * Start countdown timer.
	 *
	 * @since 3.5.3
	 * @param {number} seconds Number of seconds to count down from.
	 * @param {Function} onComplete Callback when countdown reaches zero.
	 * @return {void}
	 */
	startCountdownTimer ( seconds, onComplete ) {
		// Clear any existing countdown first.
		this.stopCountdown();

		this.secondsRemaining = seconds;

		// Update countdown every second.
		this.countdownInterval = setInterval( () => {
			this.secondsRemaining--;

			const countdownEl = this.modal.querySelector( '.edd-countdown__seconds' );
			if ( countdownEl ) {
				countdownEl.textContent = this.secondsRemaining;
			}

			if ( this.secondsRemaining <= 0 ) {
				this.stopCountdown();
				if ( onComplete ) {
					onComplete();
				}
			}
		}, 1000 );
	}

	/**
	 * Start countdown timer for rate limit errors.
	 *
	 * @since 3.5.3
	 * @return {void}
	 */
	startErrorCountdown () {
		this.startCountdownTimer(
			this.secondsRemaining,
			() => this.modal.close()
		);
	}

	/**
	 * Start countdown timer and polling.
	 *
	 * @since 3.5.3
	 * @return {void}
	 */
	startCountdown () {
		this.startCountdownTimer(
			this.config.countdown,
			() => this.showResendButton()
		);

		// Start polling for verification status.
		this.startPolling();
	}

		/**
		 * Stop countdown timer.
		 *
		 * @since 3.5.3
		 * @return {void}
		 */
		stopCountdown () {
			if ( this.countdownInterval ) {
				clearInterval( this.countdownInterval );
				this.countdownInterval = null;
			}
		}

		/**
		 * Show resend button after countdown.
		 *
		 * @since 3.5.3
		 * @return {void}
		 */
		showResendButton () {
			const content = `
				<p>${ this.config.strings.check_email }</p>
				<button type="button" class="button" id="edd-resend-again-button">
					${ this.config.strings.resend_email }
				</button>
			`;

			this.modal.setContent( content );

			// Bind resend button.
			setTimeout( () => {
				const resendBtn = this.modal.querySelector( '#edd-resend-again-button' );
				resendBtn?.addEventListener( 'click', () => {
					this.handleResendClick();
				} );
			}, 100 );
		}

		/**
		 * Start polling for verification status.
		 *
		 * @since 3.5.3
		 * @return {void}
		 */
		startPolling () {
			// Poll every 15 seconds.
			this.pollInterval = setInterval( () => {
				this.checkVerificationStatus();
			}, this.config.poll_interval * 1000 );
		}

		/**
		 * Stop polling for verification status.
		 *
		 * @since 3.5.3
		 * @return {void}
		 */
		stopPolling () {
			if ( this.pollInterval ) {
				clearInterval( this.pollInterval );
				this.pollInterval = null;
			}
		}

		/**
		 * Check verification status via AJAX.
		 *
		 * @since 3.5.3
		 * @return {void}
		 */
		checkVerificationStatus () {
			const formData = new FormData();
			formData.append( 'action', 'edd_check_verification_status' );
			formData.append( 'nonce', this.config.nonce );

			fetch( this.config.ajax_url, {
				method: 'POST',
				body: formData,
				credentials: 'same-origin'
			} )
				.then( response => response.json() )
				.then( data => {
					if ( data.success && data.data.is_verified ) {
						this.stopPolling();
						this.stopCountdown();
						this.showVerifiedState();
					}
				} )
				.catch( error => {
					console.error( 'Verification status check error:', error );
				} );
		}

		/**
		 * Show verified state and refresh page.
		 *
		 * @since 3.5.3
		 * @return {void}
		 */
		showVerifiedState () {
			const content = `
				<div class="edd-modal__message--success">
					<p>${ this.config.strings.verified }</p>
				</div>
			`;

			this.modal.setContent( content );

			// Refresh page after 2 seconds.
			setTimeout( () => {
				globalThis.location.reload();
			}, 2000 );
		}
	}

	// Initialize when DOM is ready.
	if ( document.readyState === 'loading' ) {
		document.addEventListener( 'DOMContentLoaded', () => {
			new UserVerification();
		} );
	} else {
		new UserVerification();
	}
} )();

