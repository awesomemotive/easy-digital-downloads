<?php

namespace EDD\Admin\Promos\Notices;

class Five_Star_Review_Dashboard extends Notice {

	/**
	 * Action hook for displaying the notice.
	 */
	const DISPLAY_HOOK = 'edd_dashboard_sales_widget';

	/**
	 * Type of promotional notice.
	 */
	const TYPE = 'dashboard-widget';

	public function display() {
		$this->_display();
	}

	public function _display() {
		?>
		<div class="edd-five-stars">
			<p><?php esc_html_e( 'Hey, I noticed you\'ve made quite a few sales with Easy Digital Downloads - that’s awesome! Could you please do me a BIG favor and give it a 5-star rating on WordPress to help us spread the word and boost our motivation?', 'easy-digital-downloads' ); ?></p>
			<p><strong><?php echo wp_kses( __( '~ Chris Klosowski<br>President of Easy Digital Downloads', 'easy-digital-downloads' ), array( 'br' => array() ) ); ?></strong></p>
			<p>
				<a href="https://wordpress.org/support/plugin/easy-digital-downloads/reviews/?filter=5#new-post" class="wpforms-notice-dismiss wpforms-review-out" target="_blank" rel="noopener noreferrer"><?php esc_html_e( 'Ok, you deserve it', 'easy-digital-downloads' ); ?></a><br>
				<a class="wpforms-notice-dismiss" target="_blank" rel="noopener noreferrer"><?php esc_html_e( 'Nope, maybe later', 'easy-digital-downloads' ); ?></a><br>
				<a class="wpforms-notice-dismiss" target="_blank" rel="noopener noreferrer"><?php esc_html_e( 'I already did', 'easy-digital-downloads' ); ?></a>
			</p>
		</div>
		<?php
	}

	protected function _should_display() {
		$activated = get_option( 'edd_activation', false );
		if ( $activated ) {
			if ( ( $activated + ( DAY_IN_SECONDS * 30 ) ) > time() ) {
				// return false;
			}
		} else {
			// update_option( 'edd_activation', time() );
			// return false;
		}
		$payments = edd_count_payments();
		if ( $payments && 50 > $payments->publish ) {
			return false;
		}

		return true;
	}
}
