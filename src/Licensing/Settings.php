<?php
/**
 * Handles the settings fields for extension licenses.
 */
namespace EDD\Licensing;

class Settings {
	use Traits\Controls;

	/**
	 * The array of options for the settings field.
	 *
	 * @var array
	 */
	private $args;

	/**
	 * The license object.
	 *
	 * @var \EDD\Licensing\License
	 */
	private $license;

	/**
	 * The license key.
	 *
	 * @var string
	 */
	private $license_key;

	public function __construct( $args ) {
		$this->args        = $args;
		$this->license     = new License( $this->args['name'], $this->args['options']['is_valid_license_option'] );
		$this->license_key = $this->license->key;
		$this->name        = $this->args['name'];

		$this->set_up_license_data();
		$this->do_settings_field();
		add_action( 'admin_print_footer_scripts', array( $this, 'do_script' ) );
	}

	/**
	 * Adds the licensing JS to the screen.
	 *
	 * @since 3.1.1
	 * @return void
	 */
	public function do_script() {
		if ( wp_script_is( 'edd-licensing' ) ) {
			return;
		}
		wp_enqueue_script( 'edd-licensing', EDD_PLUGIN_URL . 'assets/js/edd-admin-licensing.js', array( 'jquery' ), EDD_VERSION, true );
		wp_localize_script(
			'edd-licensing',
			'EDDLicenseHandler',
			array(
				'activating'   => __( 'Activating', 'easy-digital-downloads' ),
				'deactivating' => __( 'Deactivating', 'easy-digital-downloads' ),
			)
		);
		wp_print_scripts( 'edd-licensing' );
		?>
		<style>p.submit{display:none;}</style>
		<?php
	}

	/**
	 * Renders the license key settings field.
	 *
	 * @since 3.1.1
	 * @return void
	 */
	private function do_settings_field() {

		if ( ! $this->included_in_pass ) {
			?>
			<div class="edd-license__control">
				<input
					type="password"
					autocomplete="off"
					class="regular-text"
					id="edd_settings[<?php echo esc_attr( $this->args['id'] ); ?>]"
					name="edd_settings[<?php echo esc_attr( $this->args['id'] ); ?>]"
					value="<?php echo sanitize_key( $this->license_key ); ?>"
					<?php echo $this->included_in_pass ? ' readonly' : ''; ?>
					<?php if ( ! empty( $this->args['options']['item_id'] ) ) : ?>
						data-item="<?php echo esc_attr( $this->args['options']['item_id'] ); ?>"
					<?php endif; ?>
					data-name="<?php echo esc_attr( $this->args['name'] ); ?>"
					data-key="<?php echo esc_attr( $this->args['id'] ); ?>"
				/>
				<?php
				if ( ! empty( $this->args['options']['api_url'] ) ) {
					?>
					<input type="hidden" name="apiurl" value="<?php echo esc_url( $this->args['options']['api_url'] ); ?>" />
					<?php
				}
				$this->get_actions( $this->license->license, true );
				?>
			</div>
			<?php
		}
		$this->do_message();
		do_action( 'edd/admin/settings/licenses/settings_field', $this->license, $this->included_in_pass );
	}
}
