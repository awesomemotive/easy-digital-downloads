<?php
/**
 * Featured images for terms.
 *
 * @package   edd-blocks
 * @copyright 2022 Easy Digital Downloads
 * @license   GPL2+
 * @since 2.0
 */
namespace EDD\Blocks\Terms;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

class Images {

	/**
	 * The term image meta key.
	 *
	 * @var string
	 */
	private $meta_key = 'download_term_image';

	/**
	 * The taxonomies for which term images are registered.
	 *
	 * @var array
	 */
	private $taxonomies = array( 'download_category', 'download_tag' );

	/**
	 * Constructor
	 *
	 * @since  2.0
	 */
	public function __construct() {
		register_meta(
			'term',
			$this->meta_key,
			array(
				'type'   => 'integer',
				'single' => true,
			)
		);

		foreach ( $this->taxonomies as $taxonomy ) {
			add_action( "{$taxonomy}_add_form_fields", array( $this, 'add_taxonomy_meta_fields' ), 5, 2 );
			add_action( "{$taxonomy}_edit_form_fields", array( $this, 'edit_taxonomy_meta_fields' ), 5, 2 );
			add_action( "edited_{$taxonomy}", array( $this, 'save_term_meta' ) );
			add_action( "create_{$taxonomy}", array( $this, 'save_term_meta' ) );
			add_action( "edit_{$taxonomy}", array( $this, 'save_term_meta' ) );
			add_filter( "manage_edit-{$taxonomy}_columns", array( $this, 'add_column' ) );
			add_action( "manage_{$taxonomy}_custom_column", array( $this, 'manage_taxonomy_column' ), 10, 3 );
		}
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_scripts' ) );
	}

	/**
	 * Enqueues the JS needed for uploading term images.
	 *
	 * @since 2.0
	 * @return void
	 */
	public function enqueue_scripts() {

		$screen = get_current_screen();
		if ( empty( $screen ) || ! in_array( $screen->taxonomy, $this->taxonomies, true ) ) {
			return;
		}

		wp_enqueue_media();
		wp_enqueue_script( 'edd-term-image-upload', EDD_BLOCKS_URL . 'assets/js/term-image.js', array( 'jquery', 'media-upload', 'thickbox' ), EDD_VERSION, true );
		wp_localize_script(
			'edd-term-image-upload',
			'EDDTermImages',
			array(
				'text' => __( 'Select Image', 'easy-digital-downloads' ),
			)
		);
	}

	/**
	 * Displays the term image UI for adding a new term.
	 *
	 * @since 2.0
	 *
	 * @return void
	 */
	public function add_taxonomy_meta_fields() {
		?>
		<div class="form-field term-image-wrap">
			<?php wp_nonce_field( "{$this->meta_key}_save-settings", "{$this->meta_key}_nonce", false ); ?>
			<label for="<?php echo esc_attr( $this->meta_key ); ?>" class="screen-reader-text"><?php esc_html_e( 'Term Image', 'easy-digital-downloads' ); ?></label>
			<?php $this->render_buttons( $this->meta_key ); ?>
			<p class="description">
				<?php esc_html_e( 'Set Term Image.', 'easy-digital-downloads' ); ?>
			</p>
		</div>
		<?php
	}

	/**
	 * Show the image UI for existing terms.
	 *
	 * @param WP_Term $term
	 * @return void
	 */
	public function edit_taxonomy_meta_fields( $term ) {

		$term_id  = $term->term_id;
		$image_id = $this->get_meta( $term_id );

		wp_nonce_field( "{$this->meta_key}_save-settings", "{$this->meta_key}_nonce", false );
		?>
		<tr class="form-field term-image-wrap">
			<th scope="row" >
				<label for="<?php echo esc_attr( $this->meta_key ); ?>">
					<?php esc_html_e( 'Term Image', 'easy-digital-downloads' ); ?>
				</label>
			</th>
			<td>
				<?php
				if ( $image_id ) {
					$this->render_image_preview( $image_id, $term->name );
				}
				$this->render_buttons( $this->meta_key, $image_id );
				?>
				<p class="description">
					<?php
					printf(
						/* translators: 1. name of the term */
						esc_attr__( 'Set Term Image for %1$s.', 'easy-digital-downloads' ),
						esc_attr( $term->name )
					);
					?>
				</p>
			</td>
		</tr>
		<?php
	}

	/**
	 * Renders the image preview.
	 *
	 * @since 2.0
	 * @param int    $id  The image ID.
	 * @param string $alt The alt text.
	 * @return void
	 */
	private function render_image_preview( $id, $alt = '' ) {
		if ( empty( $id ) ) {
			return;
		}

		/* translators: the placeholder refers to which featured image */
		$alt_text = sprintf( __( '%s featured image', 'easy-digital-downloads' ), esc_attr( $alt ) );
		$preview  = wp_get_attachment_image_src( (int) $id, 'medium' );

		?>
		<div class="upload-image-preview">
			<img src="<?php echo esc_url( $preview[0] ); ?>" alt="<?php echo esc_attr( $alt_text ); ?>" />
		</div>
		<?php
	}

	/**
	 * Show image select/delete buttons
	 *
	 * @param string $name name for value/ID/class
	 * @param int    $id   image ID
	 *
	 * @since 2.3.0
	 */
	public function render_buttons( $name, $id = '' ) {
		?>
		<input type="hidden" class="upload-image-id" name="<?php echo esc_attr( $name ); ?>" value="<?php echo absint( $id ); ?>" />
		<button id="<?php echo esc_attr( $name ); ?>" class="upload-image button-secondary">
			<?php esc_html_e( 'Select Image', 'easy-digital-downloads' ); ?>
		</button>
		<button class="delete-image button-secondary"<?php echo empty( $id ) ? 'style="display:none;"' : ''; ?>>
			<?php esc_html_e( 'Delete Image', 'easy-digital-downloads' ); ?>
		</button>
		<?php
	}

	/**
	 * Save extra taxonomy fields callback function.
	 * @param $term_id int the id of the term
	 *
	 * @since 2.0
	 */
	public function save_term_meta( $term_id ) {
		if ( ! $this->user_can_save( "{$this->meta_key}_save-settings", "{$this->meta_key}_nonce" ) ) {
			return;
		}
		$input = filter_input( INPUT_POST, $this->meta_key, FILTER_SANITIZE_NUMBER_INT );
		if ( $input ) {
			update_term_meta( $term_id, $this->meta_key, absint( $input ) );
		} else {
			delete_term_meta( $term_id, $this->meta_key );
		}
	}

	/**
	 * Adds a featured image column for download terms.
	 *
	 * @param array $columns The array of registered columns.
	 */
	public function add_column( $columns ) {

		$new_columns = $columns;
		array_splice( $new_columns, 1 );

		$new_columns['featured_image'] = __( 'Image', 'easy-digital-downloads' );

		return array_merge( $new_columns, $columns );
	}

	/**
	 * Render the featured image column for terms.
	 *
	 * @param  string $value   Blank (because WP).
	 * @param  string $column  Column ID.
	 * @param  int    $term_id The term ID.
	 */
	public function manage_taxonomy_column( $value, $column, $term_id ) {

		if ( 'featured_image' !== $column ) {
			return;
		}

		$image_id = $this->get_meta( $term_id );
		if ( ! $image_id ) {
			return;
		}

		$source = wp_get_attachment_image_src( $image_id, 'thumbnail' );
		if ( ! $source ) {
			return;
		}

		$taxonomy = ! empty( $_POST['taxonomy'] ) ? sanitize_text_field( $_POST['taxonomy'] ) : null;
		$taxonomy = ! is_null( $taxonomy ) ? $taxonomy : get_current_screen()->taxonomy;
		?>
		<img
			src="<?php echo esc_url( $source[0] ); ?>"
			alt="<?php echo esc_attr( get_term( $term_id, $taxonomy )->name ); ?>"
			width="60"
		/>
		<?php
	}

	/**
	 * Get the current term meta or option, if it exists.
	 *
	 * @param $term_id
	 *
	 * @return mixed|void
	 * @since 2.0
	 */
	private function get_meta( $term_id ) {
		return get_term_meta( $term_id, $this->meta_key, true );
	}

	/**
	 * Determines if the user has permission to save the information from the submenu
	 * page.
	 *
	 * @since    2.0
	 *
	 * @param    string $action The name of the action specified on the submenu page
	 * @param    string $nonce  The nonce specified on the submenu page
	 *
	 * @return   bool True if the user has permission to save; false, otherwise.
	 */
	private function user_can_save( $action, $nonce ) {
		$is_nonce_set   = isset( $_POST[ $nonce ] );
		$is_valid_nonce = false;

		if ( $is_nonce_set ) {
			$is_valid_nonce = wp_verify_nonce( $_POST[ $nonce ], $action );
		}

		return ( $is_nonce_set && $is_valid_nonce );
	}
}
