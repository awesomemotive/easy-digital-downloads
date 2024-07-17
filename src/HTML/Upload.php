<?php
/**
 * Upload HTML Element
 *
 * @package EDD
 * @subpackage HTML
 * @since 3.3.0
 */

namespace EDD\HTML;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit; // @codeCoverageIgnore

/**
 * Class Upload
 *
 * @since 3.3.0
 * @package EDD\HTML
 */
class Upload extends Base {

	/**
	 * Gets the HTML for the upload field.
	 *
	 * @since 3.3.0
	 * @return string
	 */
	public function get() {
		ob_start();
		?>
		<div class="edd-upload-button-wrapper">
			<input
				type="text"
				class="regular-text"
				id="<?php echo esc_attr( $this->get_id() ); ?>"
				name="<?php echo esc_attr( $this->args['name'] ); ?>"
				value="<?php echo esc_url( $this->args['value'] ); ?>"
			>
			<button
				data-input="<?php echo esc_attr( $this->get_data_input() ); ?>"
				data-uploader-title="Attach File"
				data-uploader-button-text="Attach"
				class="edd_settings_upload_button button button-secondary"
			>
			<?php esc_html_e( 'Attach File', 'easy-digital-downloads' ); ?>
			</button>
		</div>
		<?php
		if ( ! empty( $this->args['desc'] ) ) {
			?>
			<p class="description"><?php echo wp_kses_post( $this->args['desc'] ); ?></p>
			<?php
		}

		return ob_get_clean();
	}

	/**
	 * Gets the default arguments for the upload field.
	 *
	 * @since 3.3.0
	 * @return array
	 */
	protected function defaults() {
		return array(
			'id'    => '',
			'name'  => 'upload',
			'value' => '',
			'label' => '',
			'desc'  => '',
			'class' => 'upload',
		);
	}

	/**
	 * Gets the ID for the upload field.
	 *
	 * @since 3.3.0
	 * @return string
	 */
	private function get_id() {
		return $this->args['id'] ?? $this->args['name'];
	}

	/**
	 * Gets the data input attribute.
	 *
	 * @since 3.3.0
	 * @return string
	 */
	private function get_data_input() {
		$id = $this->get_id();
		$id = str_replace( '[', '\\[', $id );
		$id = str_replace( ']', '\\]', $id );

		return '#' . $id;
	}
}
