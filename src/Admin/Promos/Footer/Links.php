<?php
/**
 * Adds EDD Link text to the EDD footer in the admin.
 *
 * @package     EDD
 * @subpackage  Admin/Footer
 * @since       3.2.4
 */

namespace EDD\Admin\Promos\Footer;

/**
 * Class Links
 *
 * @since 3.2.4
 */
class Links {

	/**
	 * Adds review text to the EDD footer in the admin.
	 *
	 * @since 3.2.4
	 */
	public static function footer_content() {
		$footer_links = array(
			array(
				'url'    => edd_is_pro() ?
					edd_link_helper(
						'https://easydigitaldownloads.com/support/',
						array(
							'utm_medium'  => 'admin-footer',
							'utm_content' => 'support',
						),
					) : 'https://wordpress.org/support/plugin/easy-digital-downloads/',
				'text'   => __( 'Support', 'easy-digital-downloads' ),
				'target' => '_blank',
			),
			array(
				'url'    => edd_link_helper(
					'https://easydigitaldownloads.com/docs/',
					array(
						'utm_medium'  => 'admin-footer',
						'utm_content' => 'docs',
					),
				),
				'text'   => __( 'Docs', 'easy-digital-downloads' ),
				'target' => '_blank',
			),
		);

		$screen = get_current_screen();
		if ( 'download_page_edd-about' !== $screen->id ) {
			$footer_links[] = array(
				'url'  => admin_url( 'edit.php?post_type=download&page=edd-about' ),
				'text' => __( 'Free Plugins', 'easy-digital-downloads' ),
			);
		}

		$links_count = count( $footer_links );
		?>

		<div class="edd-footer-promotion">
			<p>
				<?php echo esc_html( __( 'Made with ♥ by the Easy Digital Downloads Team', 'easy-digital-downloads' ) ); ?>
			</p>
			<ul class="edd-footer-promotion-links">
				<?php foreach ( $footer_links as $key => $item ) : ?>
					<li>
						<?php
						$attributes = array(
							'href'   => esc_url( $item['url'] ),
							'target' => isset( $item['target'] ) ? $item['target'] : false,
							'rel'    => isset( $item['target'] ) ? 'noopener noreferrer' : false,
						);

						$attributes_string = implode(
							' ',
							array_map(
								function ( $attr_name, $attr_value ) {
									if ( ! $attr_value ) {
										return '';
									}

									return sprintf( '%1s="%2s"', $attr_name, $attr_value );
								},
								array_keys( $attributes ),
								$attributes
							)
						);

						printf(
							'<a %1s>%2$s</a>%3$s',
							$attributes_string,
							esc_html( $item['text'] ),
							$links_count === $key + 1 ? '' : '<span>/</span>'
						);
						?>
					</li>
				<?php endforeach; ?>
			</ul>
			<ul class="edd-footer-promotion-social">
				<li>
					<a href="https://www.facebook.com/eddwp" target="_blank" rel="noopener noreferrer">
						<svg width="16" height="16" aria-hidden="true">
							<path d="M16 8.05A8.02 8.02 0 0 0 8 0C3.58 0 0 3.6 0 8.05A8 8 0 0 0 6.74 16v-5.61H4.71V8.05h2.03V6.3c0-2.02 1.2-3.15 3-3.15.9 0 1.8.16 1.8.16v1.98h-1c-1 0-1.31.62-1.31 1.27v1.49h2.22l-.35 2.34H9.23V16A8.02 8.02 0 0 0 16 8.05Z"/>
						</svg>
						<span class="screen-reader-text">Facebook</span>
					</a>
				</li>
				<li>
					<a href="https://www.instagram.com/easydigitaldownloads/" target="_blank" rel="noopener noreferrer">
						<svg width="16" height="16" aria-hidden="true">
							<path d="M8.016 4.39c-2 0-3.594 1.626-3.594 3.594 0 2 1.594 3.594 3.594 3.594a3.594 3.594 0 0 0 3.593-3.594c0-1.968-1.625-3.593-3.593-3.593Zm0 5.938a2.34 2.34 0 0 1-2.344-2.344c0-1.28 1.031-2.312 2.344-2.312a2.307 2.307 0 0 1 2.312 2.312c0 1.313-1.031 2.344-2.312 2.344Zm4.562-6.062a.84.84 0 0 0-.844-.844.84.84 0 0 0-.843.844.84.84 0 0 0 .843.843.84.84 0 0 0 .844-.843Zm2.375.843c-.062-1.125-.312-2.125-1.125-2.937-.812-.813-1.812-1.063-2.937-1.125-1.157-.063-4.625-.063-5.782 0-1.125.062-2.093.312-2.937 1.125-.813.812-1.063 1.812-1.125 2.937-.063 1.157-.063 4.625 0 5.782.062 1.125.312 2.093 1.125 2.937.844.813 1.812 1.063 2.937 1.125 1.157.063 4.625.063 5.782 0 1.125-.062 2.125-.312 2.937-1.125.813-.844 1.063-1.812 1.125-2.937.063-1.157.063-4.625 0-5.782Zm-1.5 7c-.219.625-.719 1.094-1.312 1.344-.938.375-3.125.281-4.125.281-1.032 0-3.22.094-4.125-.28a2.37 2.37 0 0 1-1.344-1.345c-.375-.906-.281-3.093-.281-4.125 0-1-.094-3.187.28-4.125a2.41 2.41 0 0 1 1.345-1.312c.906-.375 3.093-.281 4.125-.281 1 0 3.187-.094 4.125.28.593.22 1.062.72 1.312 1.313.375.938.281 3.125.281 4.125 0 1.032.094 3.22-.28 4.125Z"/>
						</svg>
						<span class="screen-reader-text">Instagram</span>
					</a>
				</li>
				<li>
					<a href="https://www.linkedin.com/company/easy-digital-downloads/" target="_blank" rel="noopener noreferrer">
						<svg width="16" height="16" aria-hidden="true">
							<path d="M14 1H1.97C1.44 1 1 1.47 1 2.03V14c0 .56.44 1 .97 1H14a1 1 0 0 0 1-1V2.03C15 1.47 14.53 1 14 1ZM5.22 13H3.16V6.34h2.06V13ZM4.19 5.4a1.2 1.2 0 0 1-1.22-1.18C2.97 3.56 3.5 3 4.19 3c.65 0 1.18.56 1.18 1.22 0 .66-.53 1.19-1.18 1.19ZM13 13h-2.1V9.75C10.9 9 10.9 8 9.85 8c-1.1 0-1.25.84-1.25 1.72V13H6.53V6.34H8.5v.91h.03a2.2 2.2 0 0 1 1.97-1.1c2.1 0 2.5 1.41 2.5 3.2V13Z"/>
						</svg>
						<span class="screen-reader-text">LinkedIn</span>
					</a>
				</li>
				<li>
					<a href="https://twitter.com/eddwp" target="_blank" rel="noopener noreferrer">
					<svg width="16" height="16" viewBox="0 0 24 24" aria-hidden="true">
						<path d="M18.244 2.25h3.308l-7.227 8.26 8.502 11.24H16.17l-5.214-6.817L4.99 21.75H1.68l7.73-8.835L1.254 2.25H8.08l4.713 6.231zm-1.161 17.52h1.833L7.084 4.126H5.117z"></path>
					</svg>
					<span class="screen-reader-text"><?php esc_html_e( 'X (Formerly Twitter)', 'easy-digital-downloads' ); ?></span>
					</a>
				</li>
			</ul>
		</div>
		<?php
	}
}
