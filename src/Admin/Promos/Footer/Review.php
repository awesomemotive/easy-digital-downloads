<?php
/**
 * Adds review text to the EDD footer in the admin.
 *
 * @package     EDD
 * @subpackage  Admin/Footer
 * @since       3.2.4
 */

namespace EDD\Admin\Promos\Footer;

/**
 * Class Review
 *
 * @since 3.2.4
 */
class Review {

	/**
	 * Adds review text to the EDD footer in the admin.
	 *
	 * @since 3.2.4
	 *
	 * @param string $text The current footer text.
	 * @return string
	 */
	public static function review_message( $text ) {
		$text = sprintf(
			wp_kses( /* translators: $1$s - Easy Digital Downloads plugin name, $2$s - Full markup for WP.org review link, $3$s - Full markup for WP.org review link. */
				__( 'Please rate %1$s %2$s on %3$s to help us spread the word.', 'easy-digital-downloads' ),
				array(
					'a' => array(
						'href'   => array(),
						'target' => array(),
						'rel'    => array(),
					),
				)
			),
			'<strong>Easy Digital Downloads</strong>',
			'<a href="https://wordpress.org/support/plugin/easy-digital-downloads/reviews/?filter=5#new-post" target="_blank" rel="noopener noreferrer">&#9733;&#9733;&#9733;&#9733;&#9733;</a>',
			'<a href="https://wordpress.org/support/plugin/easy-digital-downloads/reviews/?filter=5#new-post" target="_blank" rel="noopener noreferrer">WordPress.org</a>',
		);

		return $text;
	}
}
