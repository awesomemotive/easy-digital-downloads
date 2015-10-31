<?php
/**
 * Widgets
 *
 * Widgets related funtions and widget registration.
 *
 * @package     EDD
 * @subpackage  Widgets
 * @copyright   Copyright (c) 2015, Pippin Williamson
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       1.0
*/

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

/*
|--------------------------------------------------------------------------
| FRONT-END WIDGETS
|--------------------------------------------------------------------------
|
| - Cart Widget
| - Categories / Tags Widget
|
*/

/**
 * Cart Widget.
 *
 * Downloads cart widget class.
 *
 * @since 1.0
 * @return void
*/
class edd_cart_widget extends WP_Widget {
	/** Constructor */
	function __construct() {
		parent::__construct( 'edd_cart_widget', __( 'Downloads Cart', 'easy-digital-downloads' ), array( 'description' => __( 'Display the downloads shopping cart', 'easy-digital-downloads' ) ) );
	}

	/** @see WP_Widget::widget */
	function widget( $args, $instance ) {

		if ( isset( $instance['hide_on_checkout'] ) && edd_is_checkout() ) {
			return;
		}

		$args['id']        = ( isset( $args['id'] ) ) ? $args['id'] : 'edd_cart_widget';
		$instance['title'] = ( isset( $instance['title'] ) ) ? $instance['title'] : '';

		$title = apply_filters( 'widget_title', $instance['title'], $instance, $args['id'] );

		echo $args['before_widget'];

		if ( $title ) {
			echo $args['before_title'] . $title . $args['after_title'];
		}

		do_action( 'edd_before_cart_widget' );

		edd_shopping_cart( true );

		do_action( 'edd_after_cart_widget' );

		echo $args['after_widget'];
	}

	/** @see WP_Widget::update */
	function update( $new_instance, $old_instance ) {
		$instance = $old_instance;

		$instance['title']            = strip_tags( $new_instance['title'] );
		$instance['hide_on_checkout'] = isset( $new_instance['hide_on_checkout'] );

		return $instance;
	}

	/** @see WP_Widget::form */
	function form( $instance ) {

		$defaults = array(
			'title'            => '',
			'hide_on_checkout' => ''
		);

		$instance = wp_parse_args( (array) $instance, $defaults ); ?>
		<p>
			<label for="<?php echo esc_attr( $this->get_field_id( 'title' ) ); ?>"><?php _e( 'Title:', 'easy-digital-downloads' ); ?></label>
			<input class="widefat" id="<?php echo esc_attr( $this->get_field_id( 'title' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'title' ) ); ?>" type="text" value="<?php echo $instance['title']; ?>"/>
		</p>

		<!-- Hide on Checkout Page -->
		<p>
			<input <?php checked( $instance['hide_on_checkout'], true ); ?> id="<?php echo esc_attr( $this->get_field_id( 'hide_on_checkout' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'hide_on_checkout' ) ); ?>" type="checkbox" />
			<label for="<?php echo esc_attr( $this->get_field_id( 'hide_on_checkout' ) ); ?>"><?php _e( 'Hide on Checkout Page', 'easy-digital-downloads' ); ?></label>
		</p>

		<?php
	}
}

/**
 * Categories / Tags Widget.
 *
 * Downloads categories / tags widget class.
 *
 * @since 1.0
 * @return void
*/
class edd_categories_tags_widget extends WP_Widget {
	/** Constructor */
	function __construct() {
		parent::__construct( 'edd_categories_tags_widget', __( 'Downloads Categories / Tags', 'easy-digital-downloads' ), array( 'description' => __( 'Display the downloads categories or tags', 'easy-digital-downloads' ) ) );
	}

	/** @see WP_Widget::widget */
	function widget( $args, $instance ) {
		// Set defaults,
		$args['id']           = ( isset( $args['id'] ) ) ? $args['id'] : 'edd_categories_tags_widget';
		$instance['title']    = ( isset( $instance['title'] ) ) ? $instance['title'] : '';
		$instance['taxonomy'] = ( isset( $instance['taxonomy'] ) ) ? $instance['taxonomy'] : 'download_category';

		$title      = apply_filters( 'widget_title', $instance['title'], $instance, $args['id'] );
		$tax        = $instance['taxonomy'];
		$count      = isset( $instance['count'] ) && $instance['count'] == 'on' ? 1 : 0;
		$hide_empty = isset( $instance['hide_empty'] ) && $instance['hide_empty'] == 'on' ? 1 : 0;

		echo $args['before_widget'];

		if ( $title ) {
			echo $args['before_title'] . $title . $args['after_title'];
		}

		do_action( 'edd_before_taxonomy_widget' );

		echo "<ul class=\"edd-taxonomy-widget\">\n";
			wp_list_categories( 'title_li=&taxonomy=' . $tax . '&show_count=' . $count . '&hide_empty=' . $hide_empty );
		echo "</ul>\n";

		do_action( 'edd_after_taxonomy_widget' );

		echo $args['after_widget'];
	}

	/** @see WP_Widget::update */
	function update( $new_instance, $old_instance ) {
		$instance = $old_instance;
		$instance['title'] = strip_tags( $new_instance['title'] );
		$instance['taxonomy'] = strip_tags( $new_instance['taxonomy'] );
		$instance['count'] = isset( $new_instance['count'] ) ? $new_instance['count'] : '';
		$instance['hide_empty'] = isset( $new_instance['hide_empty'] ) ? $new_instance['hide_empty'] : '';
		return $instance;
	}

	/** @see WP_Widget::form */
	function form( $instance ) {
		// Set up some default widget settings.
		$defaults = array(
			'title'         => '',
			'taxonomy'      => 'download_category',
			'count'         => 'off',
			'hide_empty'    => 'off'
		);

		$instance = wp_parse_args( (array) $instance, $defaults ); ?>
		<p>
			<label for="<?php echo esc_attr( $this->get_field_id( 'title' ) ); ?>"><?php _e( 'Title:', 'easy-digital-downloads' ); ?></label>
			<input class="widefat" id="<?php echo esc_attr( $this->get_field_id( 'title' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'title' ) ); ?>" type="text" value="<?php echo $instance['title']; ?>"/>
		</p>
		<p>
			<label for="<?php echo esc_attr( $this->get_field_id( 'taxonomy' ) ); ?>"><?php _e( 'Taxonomy:', 'easy-digital-downloads' ); ?></label>
			<select name="<?php echo esc_attr( $this->get_field_name( 'taxonomy' ) ); ?>" id="<?php echo esc_attr( $this->get_field_id( 'taxonomy' ) ); ?>">
				<?php
				$category_labels = edd_get_taxonomy_labels( 'download_category' );
				$tag_labels      = edd_get_taxonomy_labels( 'download_tag' );
				?>
				<option value="download_category" <?php selected( 'download_category', $instance['taxonomy'] ); ?>><?php echo $category_labels['name']; ?></option>
				<option value="download_tag" <?php selected( 'download_tag', $instance['taxonomy'] ); ?>><?php echo $tag_labels['name']; ?></option>
			</select>
		</p>
		<p>
			<label for="<?php echo $this->get_field_id( 'count' ); ?>"><?php _e( 'Show Count:', 'easy-digital-downloads' ); ?></label>
			<input <?php checked( $instance['count'], 'on' ); ?> id="<?php echo $this->get_field_id( 'count' ); ?>" name="<?php echo $this->get_field_name( 'count' ); ?>" type="checkbox" />
		</p>
		<p>
			<label for="<?php echo $this->get_field_id( 'hide_empty' ); ?>"><?php _e( 'Hide Empty Categories:', 'easy-digital-downloads' ); ?></label>
			<input <?php checked( $instance['hide_empty'], 'on' ); ?> id="<?php echo $this->get_field_id( 'hide_empty' ); ?>" name="<?php echo $this->get_field_name( 'hide_empty' ); ?>" type="checkbox" />
		</p>
	<?php
	}
}


/**
 * Product Details Widget.
 *
 * Displays a product's details in a widget.
 *
 * @since 1.9
 * @return void
 */
class EDD_Product_Details_Widget extends WP_Widget {

	/** Constructor */
	public function __construct() {
		parent::__construct(
			'edd_product_details',
			sprintf( __( '%s Details', 'easy-digital-downloads' ), edd_get_label_singular() ),
			array(
				'description' => sprintf( __( 'Display the details of a specific %s', 'easy-digital-downloads' ), edd_get_label_singular() ),
			)
		);
	}

	/** @see WP_Widget::widget */
	public function widget( $args, $instance ) {
		$args['id'] = ( isset( $args['id'] ) ) ? $args['id'] : 'edd_download_details_widget';

		if ( ! isset( $instance['download_id'] ) || ( 'current' == $instance['download_id'] && ! is_singular( 'download' ) ) ) {
			return;
		}

		// set correct download ID.
		if ( 'current' == $instance['download_id'] && is_singular( 'download' ) ) {
			$download_id = get_the_ID();
		} else {
			$download_id = absint( $instance['download_id'] );
		}

		// Variables from widget settings.
		$title              = apply_filters( 'widget_title', $instance['title'], $instance, $args['id'] );
		$download_title 	= $instance['download_title'] ? apply_filters( 'edd_product_details_widget_download_title', '<h3>' . get_the_title( $download_id ) . '</h3>', $download_id ) : '';
		$purchase_button 	= $instance['purchase_button'] ? apply_filters( 'edd_product_details_widget_purchase_button', edd_get_purchase_link( array( 'download_id' => $download_id ) ), $download_id ) : '';
		$categories 		= $instance['categories'] ? $instance['categories'] : '';
		$tags 				= $instance['tags'] ? $instance['tags'] : '';

		// Used by themes. Opens the widget.
		echo $args['before_widget'];

		// Display the widget title.
		if( $title ) {
			echo $args['before_title'] . $title . $args['after_title'];
		}

		do_action( 'edd_product_details_widget_before_title' , $instance , $download_id );

		// download title.
		echo $download_title;

		do_action( 'edd_product_details_widget_before_purchase_button' , $instance , $download_id );
		// purchase button.
		echo $purchase_button;

		// categories and tags.
		$category_list     = $categories ? get_the_term_list( $download_id, 'download_category', '', ', ' ) : '';
		$category_count    = count( get_the_terms( $download_id, 'download_category' ) );
		$category_labels   = edd_get_taxonomy_labels( 'download_category' );
		$category_label    = $category_count > 1 ? $category_labels['name'] : $category_labels['singular_name'];

		$tag_list     = $tags ? get_the_term_list( $download_id, 'download_tag', '', ', ' ) : '';
		$tag_count    = count( get_the_terms( $download_id, 'download_tag' ) );
		$tag_taxonomy = edd_get_taxonomy_labels( 'download_tag' );
		$tag_label    = $tag_count > 1 ? $tag_taxonomy['name'] : $tag_taxonomy['singular_name'];

		$text = '';

		if( $category_list || $tag_list ) {
			$text .= '<p class="edd-meta">';

			if( $category_list ) {

				$text .= '<span class="categories">%1$s: %2$s</span><br/>';
			}

			if ( $tag_list ) {
				$text .= '<span class="tags">%3$s: %4$s</span>';
			}

			$text .= '</p>';
		}

		do_action( 'edd_product_details_widget_before_categories_and_tags', $instance, $download_id );

		printf( $text, $category_label, $category_list, $tag_label, $tag_list );

		do_action( 'edd_product_details_widget_before_end', $instance, $download_id );

		// Used by themes. Closes the widget.
		echo $args['after_widget'];
	}

	/** @see WP_Widget::form */
	public function form( $instance ) {
		// Set up some default widget settings.
		$defaults = array(
			'title' 			=> sprintf( __( '%s Details', 'easy-digital-downloads' ), edd_get_label_singular() ),
			'download_id' 		=> 'current',
			'download_title' 	=> 'on',
			'purchase_button' 	=> 'on',
			'categories' 		=> 'on',
			'tags' 				=> 'on'
		);

		$instance = wp_parse_args( (array) $instance, $defaults ); ?>

		<!-- Title -->
		<p>
			<label for="<?php echo esc_attr( $this->get_field_id( 'title' ) ); ?>"><?php _e( 'Title:', 'easy-digital-downloads' ) ?></label>
			<input class="widefat" id="<?php echo esc_attr( $this->get_field_id( 'title' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'title' ) ); ?>" type="text" value="<?php echo $instance['title']; ?>" />
		</p>

			<!-- Download -->
		 <?php
			$args = array(
				'post_type'      => 'download',
				'posts_per_page' => -1,
				'post_status'    => 'publish',
			);
			$downloads = get_posts( $args );
		?>
		<p>
			<label for="<?php echo esc_attr( $this->get_field_id( 'download_id' ) ); ?>"><?php printf( __( '%s', 'easy-digital-downloads' ), edd_get_label_singular() ); ?></label>
			<select class="widefat" name="<?php echo esc_attr( $this->get_field_name( 'download_id' ) ); ?>" id="<?php echo esc_attr( $this->get_field_id( 'download_id' ) ); ?>">
				<option value="current"><?php _e( 'Use current', 'easy-digital-downloads' ); ?></option>
			<?php foreach ( $downloads as $download ) { ?>
				<option <?php selected( absint( $instance['download_id'] ), $download->ID ); ?> value="<?php echo esc_attr( $download->ID ); ?>"><?php echo $download->post_title; ?></option>
			<?php } ?>
			</select>
		</p>

		<!-- Download title -->
		<p>
			<input <?php checked( $instance['download_title'], 'on' ); ?> id="<?php echo esc_attr( $this->get_field_id( 'download_title' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'download_title' ) ); ?>" type="checkbox" />
			<label for="<?php echo esc_attr( $this->get_field_id( 'download_title' ) ); ?>"><?php _e( 'Show Title', 'easy-digital-downloads' ); ?></label>
		</p>

		<!-- Show purchase button -->
		<p>
			<input <?php checked( $instance['purchase_button'], 'on' ); ?> id="<?php echo esc_attr( $this->get_field_id( 'purchase_button' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'purchase_button' ) ); ?>" type="checkbox" />
			<label for="<?php echo esc_attr( $this->get_field_id( 'purchase_button' ) ); ?>"><?php _e( 'Show Purchase Button', 'easy-digital-downloads' ); ?></label>
		</p>

		<!-- Show download categories -->
		<p>
			<?php $category_labels = edd_get_taxonomy_labels( 'download_category' ); ?>
			<input <?php checked( $instance['categories'], 'on' ); ?> id="<?php echo esc_attr( $this->get_field_id( 'categories' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'categories' ) ); ?>" type="checkbox" />
			<label for="<?php echo esc_attr( $this->get_field_id( 'categories' ) ); ?>"><?php printf( __( 'Show %s', 'easy-digital-downloads' ), $category_labels['name'] ); ?></label>
		</p>

		<!-- Show download tags -->
		<p>
			<?php $tag_labels = edd_get_taxonomy_labels( 'download_tag' ); ?>
			<input <?php checked( $instance['tags'], 'on' ); ?> id="<?php echo esc_attr( $this->get_field_id( 'tags' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'tags' ) ); ?>" type="checkbox" />
			<label for="<?php echo esc_attr( $this->get_field_id( 'tags' ) ); ?>"><?php printf( __( 'Show %s', 'easy-digital-downloads' ), $tag_labels['name'] ); ?></label>
		</p>

		<?php do_action( 'edd_product_details_widget_form' , $instance ); ?>
	<?php }

	/** @see WP_Widget::update */
	public function update( $new_instance, $old_instance ) {
		$instance = $old_instance;

		$instance['title']           = strip_tags( $new_instance['title'] );
		$instance['download_id']     = strip_tags( $new_instance['download_id'] );
		$instance['download_title']  = isset( $new_instance['download_title'] )  ? $new_instance['download_title']  : '';
		$instance['purchase_button'] = isset( $new_instance['purchase_button'] ) ? $new_instance['purchase_button'] : '';
		$instance['categories']      = isset( $new_instance['categories'] )      ? $new_instance['categories']      : '';
		$instance['tags']            = isset( $new_instance['tags'] )            ? $new_instance['tags']            : '';

		do_action( 'edd_product_details_widget_update', $instance );

		return $instance;
	}

}



/**
 * Register Widgets.
 *
 * Registers the EDD Widgets.
 *
 * @since 1.0
 * @return void
 */
function edd_register_widgets() {
	register_widget( 'edd_cart_widget' );
	register_widget( 'edd_categories_tags_widget' );
	register_widget( 'edd_product_details_widget' );
}
add_action( 'widgets_init', 'edd_register_widgets' );
