<?php
/**
 * Sections Class
 *
 * @package     EDD
 * @subpackage  Admin
 * @copyright   Copyright (c) 2018, Easy Digital Downloads, LLC
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       3.0
 */

namespace EDD\Admin;

// Exit if accessed directly
defined( 'ABSPATH' ) || exit;

/**
 * Main class for creating a vertically tabbed UI
 *
 * @since 3.0
 */
class Sections {

	/**
	 * Section ID
	 *
	 * @since 3.0
	 *
	 * @var array
	 */
	protected $id = 'edd_general_';

	/**
	 * Sections
	 *
	 * @since 3.0
	 *
	 * @var array
	 */
	protected $sections = array();

	/**
	 * ID of the currently selected section
	 *
	 * @since 3.0
	 *
	 * @var string
	 */
	public $current_section = 'general';

	/**
	 * Whether to use JavaScript should
	 *
	 * @since 3.0
	 *
	 * @var string
	 */
	public $use_js = true;

	/**
	 * Base URL for each section
	 *
	 * @since 3.0
	 *
	 * @var string
	 */
	public $base_url = '';

	/**
	 * The object, if any
	 *
	 * @since 3.0
	 *
	 * @var object
	 */
	public $item = false;

	/** Public Methods ********************************************************/

	/**
	 * Constructor
	 *
	 * Might be useful someday
	 *
	 * @since 3.0
	 */
	public function __construct() {

	}

	/**
	 * Setup default sections
	 *
	 * @since 3.0
	 */
	public function set_sections( $sections = array() ) {
		if ( empty( $sections ) ) {
			$this->add_section( array(
				'id'       => 'general',
				'label'    => esc_html__( 'General', 'easy-digital-downloads' ),
				'callback' => array( $this, 'section_general' )
			) );
		} elseif ( count( $sections ) ) {
			foreach ( $sections as $section ) {
				$this->add_section( $section );
			}
		}
	}

	/**
	 * Setup the sections for the current item
	 *
	 * @since 3.0
	 *
	 * @param object $item
	 */
	public function set_item( $item = false ) {
		$this->item = $item;
	}

	/**
	 * Output the contents
	 *
	 * @since 3.0
	 */
	public function display() {
		$use_js = ! empty( $this->use_js )
			? ' use-js'
			: '';
		$role   = $this->use_js ? 'tablist' : 'menu';

		ob_start(); ?>

		<div class="edd-sections-wrap">
			<div class="edd-vertical-sections<?php echo $use_js; ?>">
				<ul class="section-nav" role="<?php echo esc_attr( $role ); ?>">
					<?php echo $this->get_all_section_links(); ?>
				</ul>

				<div class="section-wrap">
					<?php echo $this->get_all_section_contents(); ?>
				</div>
				<br class="clear" />
			</div>
			<?php $this->nonce_field();

			if ( ! empty( $this->item ) ) : ?>

				<input type="hidden" name="edd-item-id" value="<?php echo esc_attr( $this->item->id ); ?>" />

			<?php endif; ?>
		</div>

		<?php

		// Output current buffer
		echo ob_get_clean();
	}

	/** Private Methods *******************************************************/

	/**
	 * Add a section
	 *
	 * @since 3.0
	 *
	 * @param array $section
	 */
	private function add_section( $section = array() ) {
		$this->sections[] = (object) wp_parse_args( $section, array(
			'id'       => '',
			'label'    => '',
			'icon'     => 'admin-settings',
			'callback' => ''
		) );
	}

	/**
	 * Is a section the current section?
	 *
	 * @since 3.0
	 *
	 * @param string $section_id
	 *
	 * @return bool
	 */
	private function is_current_section( $section_id = '' ) {
		return ( $section_id === $this->current_section );
	}

	/**
	 * Output the nonce field for the meta box
	 *
	 * @since 3.0
	 */
	protected function nonce_field() {
		wp_nonce_field(
			'edd_' . $this->id . '_sections_nonce',
			'edd_' . $this->id . 'nonce_sections',
			true
		);
	}

	/**
	 * Get all section links
	 *
	 * @since 3.0
	 *
	 * @return string
	 */
	protected function get_all_section_links() {
		ob_start();

		// Loop through sections.
		foreach ( $this->sections as $section ) :

			// If were using JS, the URL is a hash, otherwise use query args.
			$url = $this->use_js
				? '#' . esc_attr( $this->id . $section->id )
				: add_query_arg( 'view', sanitize_key( $section->id ), $this->base_url );

			// Special selected section.
			$selected = ! $this->use_js && $this->is_current_section( $section->id )
				? 'aria-current="true"'
				: '';

			$class           = $this->is_current_section( $section->id ) ? 'section-title section-title--is-active' : 'section-title';
			$aria_attributes = $this->use_js ? 'role="tab" aria-controls="' . esc_attr( $this->id . $section->id ) . '"' : 'role="menuitem"';
			?>

			<li id="<?php echo esc_attr( $this->id . $section->id ); ?>-nav-item" class="<?php echo esc_attr( $class ); ?>" <?php echo $aria_attributes . ' ' . $selected; ?>>
				<a href="<?php echo esc_url( $url ); ?>">
					<span class="dashicons dashicons-<?php echo esc_attr( $section->icon ); ?>"></span>
					<span class="label">
						<?php echo $section->label; // Allow HTML. ?>
					</span>
				</a>
			</li>

			<?php
		endforeach;

		// Return current buffer.
		return ob_get_clean();
	}

	/**
	 * Get all section contents
	 *
	 * @since 3.0
	 *
	 * @return string
	 */
	protected function get_all_section_contents() {
		ob_start();

		// Maybe reduce sections down to single
		$sections = empty( $this->use_js )
			? wp_filter_object_list( $this->sections, array( 'id' => $this->current_section ) )
			: $this->sections;

		// Bail if no sections
		if ( empty( $sections ) ) {
			return;
		}

		// Loop through sections
		foreach ( $sections as $section ) :

			// Special selected section
			$selected = ! $this->is_current_section( $section->id )
				? 'style="display: none;"'
				: ''; ?>

			<div id="<?php echo esc_attr( $this->id . $section->id ); ?>" class="section-content" <?php echo $selected; ?>><?php

				// Callback or action
				if ( ! empty( $section->callback ) ) {
					if ( is_callable( $section->callback ) ) {
						call_user_func( $section->callback, $this->item );

					} elseif ( is_array( $section->callback ) && is_callable( $section->callback[0] ) ) {
						isset( $section->callback[1] )
							? call_user_func_array( $section->callback[0], $section->callback[1] )
							: call_user_func_array( $section->callback[0], array() );

					} else {
						_e( 'Invalid section', 'easy-digital-downloads' );
					}
				} else {
					die;
					do_action( 'edd_' . $section->id . 'section_contents', $this );
				}

			?></div>

		<?php endforeach;

		// Return current buffer
		return ob_get_clean();
	}

	/**
	 * Output a section
	 *
	 * @since 3.0
	 *
	 * @param object $item
	*/
	protected function section_general() {
		?>

		<table class="form-table rowfat">
			<tbody>
				<tr>
					<th>
						<label><?php esc_html_e( 'Empty', 'easy-digital-downloads' ); ?></label>
					</th>

					<td>
						&mdash;
					</td>
				</tr>
			</tbody>
		</table>

		<?php
	}
}
