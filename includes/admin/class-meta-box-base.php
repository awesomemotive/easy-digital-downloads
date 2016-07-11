<?php
/**
 * EDD Admin Meta Box Base class.
 * Provides a base structure for EDD content meta boxes.
 *
 * @package     EDD
 * @subpackage  Admin/Tools/EDD_Meta_Box_Base
 * @copyright   Copyright (c) 2016, Pippin Williamson
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       2.7
 */

// Exit if accessed directly
if ( ! defined('ABSPATH') ) {
    exit;
}

/**
 * The main EDD_Meta_Box_Base class.
 * This class may be extended using the example below.
 *
 * @abstract
 * @since  2.7
 */
abstract class EDD_Meta_Box_Base {

    /**
     * An EDD meta box can be added to EDD by any
     * 3rd-party source, by extending this class.
     *
     * Example:
     *
     * class My_Integration_EDD_Meta_Box extends EDD_Meta_Box_Base {
     *
     *    public $meta_box_id   = 'my_integration_edd_metabox';
     *
     *    public $meta_box_name = 'My Integration EDD Meta box';
     *
     *    public $edd_screen    = 'edit-download'
     *    // Or, an array, if you'd like the meta box to be present on multiple screens:
     *    public $edd_screen    = array( 'edit-download', 'download' );
     *
     *    public function content() {
     *        $this->my_meta_box_content();
     *    }
     *
     *    public function my_meta_box_content() {
     *        echo __( 'Here is some content', 'easy-digital-downloads' );
     *    }
     *
     * }
     *
     * new My_Integration_EDD_Meta_Box;
     *
     **/

    /**
     * The ID of the meta box. Must be unique.
     *
     * @abstract
     * @access  public
     * @var     $meta_box_id The ID of the meta box
     * @since   2.7
     */
    public $meta_box_id;

    /**
     * The name of the meta box. Must be unique.
     *
     * @abstract
     * @access  public
     * @var     $meta_box_name The name of the meta box
     * @since   2.7
     */
    public $meta_box_name;

    /**
     * The EDD screen on which to show the meta box.
     *
     * @access  private
     * @var     $edd_screen The screen ID of the page on which to display this meta box.
     * @since   2.7
     */
    private $edd_screen = array(
        'edit-download',
        'download',
        'download_page_edd-payment-history',
        'download_page_edd-customers',
        'download_page_edd-discounts',
        'download_page_edd-reports'
    );

    /**
     * The position in which the meta box will be loaded.
     * EDD uses custom meta box contexts.
     * These contexts are listed below.
     *
     * 'primary'   will load in the left column
     * 'secondary' will load in the center column
     * 'tertiary'  will load in the right column
     *
     * All columns will collapse as needed on smaller screens,
     * as WordPress core meta boxes are in use.
     *
     * @access  public
     * @var     $context
     * @since   2.7
     */
    public $context = 'primary';

    /**
     * Constructor
     *
     * @access  public
     * @return void
     * @since   2.7
     */
    public function __construct() {
        $this->init();
    }

    /**
     * Initialize
     *
     * @access  public
     * @return  void
     * @since   2.7
     */
    public function init() {
        add_action( 'add_meta_box',           array( $this, 'add_meta_box' ) );
        add_action( 'edd_reports_meta_boxes', array( $this, 'add_meta_box' ) );
        $this->meta_box_name = __( 'EDD meta box name', 'affiliate-wp' );
    }

    /**
     * Adds the meta box
     *
     * @return  A meta box which will display on the specified EDD admin page.
     * @uses    add_meta_box
     * @since   2.7
     */
    public function add_meta_box() {
        add_meta_box(
            $this->meta_box_id,
            __( $this->meta_box_name, 'easy-digital-downloads' ),
            array( $this, 'get_content' ),
            $this->edd_screen,
            $this->context,
            'default'
        );
    }

    /**
     * Gets the content set in $this->content(),
     * which is retrieved by $this->_content().
     *
     * @return mixed string The content of the meta box.
     * @since  1.9
     */
    public function get_content() {
        /**
         * Filter the title tag content for an admin page.
         *
         * @param string $content The content of the meta box, set in $this->content()
         * @since 2.7
         *
         */
        $content = $this->_content();
        return apply_filters( 'edd_meta_box_' . $this->meta_box_id, $content );
    }

    /**
     * A protected method which echoes the $this->content().
     *
     * @return mixed string The content of the meta box.
     * @since  2.7
     */
    protected function _content() {
        return $this->content();
    }

    /**
     * Defines the meta box content, as well as a
     * filter by which the content may be adjusted.
     *
     * Use this method in your child class to define
     * the content of your meta box.
     *
     * Given a $meta_box_id value of 'my-metabox-id',
     * the filter would be: edd_meta_box_my-meta-box-id.
     *
     * @return mixed string The content of the meta box
     * @since  2.7
     */
    abstract public function content();
}
