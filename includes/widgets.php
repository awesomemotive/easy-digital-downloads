<?php
/**
 * Widgets
 *
 * @package     EDD
 * @subpackage  Widgets
 * @copyright   Copyright (c) 2014, Pippin Williamson
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
 * Cart Widget
 *
 * Downloads cart widget class.
 *
 * @since 1.0
 * @return void
*/
class edd_cart_widget extends WP_Widget {
	/** Constructor */
	function edd_cart_widget() {
		parent::WP_Widget( false, __( 'Downloads Cart', 'edd' ), array( 'description' => __( 'Display the downloads shopping cart', 'edd' ) ) );
	}

	/** @see WP_Widget::widget */
	function widget( $args, $instance ) {
		extract( $args );
		$title = apply_filters( 'widget_title', $instance[ 'title' ], $instance, $id );

		global $post, $edd_options;

		echo $before_widget;
		if ( $title ) {
			echo $before_title . $title . $after_title;
		}
		do_action( 'edd_before_cart_widget' );
		edd_shopping_cart( true );
		do_action( 'edd_after_cart_widget' );
		echo $after_widget;
	}

	/** @see WP_Widget::update */
	function update( $new_instance, $old_instance ) {
		$instance = $old_instance;
		$instance['title'] = strip_tags( $new_instance['title'] );
		$instance['quantity'] = isset( $new_instance['quantity'] ) ? strip_tags( $new_instance['quantity'] ) : '';
		return $instance;
	}

	/** @see WP_Widget::form */
	function form( $instance ) {
		$title = isset( $instance[ 'title' ] ) ? esc_attr( $instance[ 'title' ] ) : '';
		?>
		<p>
       		<label for="<?php echo esc_attr( $this->get_field_id( 'title' ) ); ?>"><?php _e( 'Title:', 'edd' ); ?></label>
     		<input class="widefat" id="<?php echo esc_attr( $this->get_field_id( 'title' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'title' ) ); ?>" type="text" value="<?php echo $title; ?>"/>
    	</p>
    
   		 <?php
	}
}

/**
 * Categories / Tags Widget
 *
 * Downloads categories / tags widget class.
 *
 * @since 1.0
 * @return void
*/
class edd_categories_tags_widget extends WP_Widget {
	/** Constructor */
	function edd_categories_tags_widget() {
		parent::WP_Widget( false, __( 'Downloads Categories / Tags', 'edd' ), array( 'description' => __( 'Display the downloads categories or tags', 'edd' ) ) );
	}

	/** @see WP_Widget::widget */
	function widget( $args, $instance ) {
		extract( $args );

		$title      = apply_filters( 'widget_title', $instance[ 'title' ], $instance, $id );
		$tax        = $instance['taxonomy'];
		$count      = $instance['count'] == 'on' ? 1 : 0;
		$hide_empty = $instance['hide_empty'] == 'on' ? 1 : 0;


		global $post, $edd_options;

		echo $before_widget;
		if ( $title ) {
			echo $before_title . $title . $after_title;
		}

		do_action( 'edd_before_taxonomy_widget' );

		echo "<ul class=\"edd-taxonomy-widget\">\n";
		wp_list_categories('title_li=&taxonomy=' . $tax . '&show_count=' . $count . '&hide_empty=' . $hide_empty);
		echo "</ul>\n";

		do_action( 'edd_after_taxonomy_widget' );
		echo $after_widget;
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
			<label for="<?php echo esc_attr( $this->get_field_id( 'title' ) ); ?>"><?php _e( 'Title:', 'edd' ); ?></label>
			<input class="widefat" id="<?php echo esc_attr( $this->get_field_id( 'title' ) ); ?>"
				   name="<?php echo esc_attr( $this->get_field_name( 'title' ) ); ?>" type="text" value="<?php echo $title; ?>"/>
		</p>
		<p>
			<label for="<?php echo esc_attr( $this->get_field_id( 'taxonomy' ) ); ?>"><?php _e( 'Taxonomy:', 'edd' ); ?></label>
			<select name="<?php echo esc_attr( $this->get_field_name( 'taxonomy' ) ); ?>" id="<?php echo esc_attr( $this->get_field_id( 'taxonomy' ) ); ?>">
				<option value="download_category" <?php selected( 'download_category', $taxonomy ); ?>><?php _e( 'Categories', 'edd' ); ?></option>
				<option value="download_tag" <?php selected( 'download_tag', $taxonomy ); ?>><?php _e( 'Tags', 'edd' ); ?></option>
			</select>
		</p>
		<p>
			<label for="<?php echo $this->get_field_id( 'count' ); ?>"><?php _e( 'Show Count:', 'edd' ); ?></label>
			<input <?php checked( $instance['count'], 'on' ); ?> id="<?php echo $this->get_field_id( 'count' ); ?>" name="<?php echo $this->get_field_name( 'count' ); ?>" type="checkbox" />
		</p>
		<p>
			<label for="<?php echo $this->get_field_id( 'hide_empty' ); ?>"><?php _e( 'Hide Empty Categories:', 'edd' ); ?></label>
			<input <?php checked( $instance['hide_empty'], 'on' ); ?> id="<?php echo $this->get_field_id( 'hide_empty' ); ?>" name="<?php echo $this->get_field_name( 'hide_empty' ); ?>" type="checkbox" />
		</p>
	<?php
	}
}


/**
 * Product Details Widget
 * 
 * Displays a product's details in a widget
 *
 * @since 1.9
 * @return void
 */
class EDD_Product_Details_Widget extends WP_Widget {

    /** Constructor */
	public function __construct() {
		parent::__construct(
			'edd_product_details',
			sprintf( __( '%s Details', 'edd' ), edd_get_label_singular() ),
			array( 
				'description' => sprintf( __( 'Display the details of a specific %s', 'edd' ), edd_get_label_singular() ),
			)
		);
	}

    /** @see WP_Widget::widget */
    public function widget( $args, $instance ) {
        extract( $args );

        if ( 'current' == $instance['download_id'] && ! is_singular( 'download' ) ) {
        	return;
        }

       	// set correct download ID
        if ( 'current' == $instance['download_id'] && is_singular( 'download' ) ) {
        	$download_id = get_the_ID();
        } else {
        	$download_id = absint( $instance['download_id'] );
        }

        // Variables from widget settings
		$title              = apply_filters( 'widget_title', $instance[ 'title' ], $instance, $id );
      	$download_title 	= $instance['download_title'] ? apply_filters( 'edd_product_details_widget_download_title', '<h3>' . get_the_title( $download_id ) . '</h3>', $download_id ) : '';
       	$purchase_button 	= $instance['purchase_button'] ? apply_filters( 'edd_product_details_widget_purchase_button', edd_get_purchase_link( array( 'download_id' => $download_id ) ), $download_id ) : '';
    	$categories 		= $instance['categories'] ? $instance['categories'] : '';
    	$tags 				= $instance['tags'] ? $instance['tags'] : '';
	
        // Used by themes. Opens the widget
        echo $before_widget;

        // Display the widget title
        if( $title ) {
            echo $before_title . $title . $after_title;
		}

        do_action( 'edd_product_details_widget_before_title' , $instance , $download_id );

     	// download title
        echo $download_title;

        do_action( 'edd_product_details_widget_before_purchase_button' , $instance , $download_id );
        // purchase button
        echo $purchase_button;

      	// categories and tags
    	$category_list = $categories ? get_the_term_list( $download_id, 'download_category', '', ', ' ) : '';
    	$tag_list = $tags ? get_the_term_list( $download_id, 'download_tag', '', ', ' ) : '';

        $text = '';

        if( $category_list || $tag_list ) {
        	$text .= '<p class="edd-meta">';

        	if( $category_list )
	        	$text .= '<span class="categories">' . __( 'Categories: %1$s', 'edd' ). '</span><br/>';

	        if ( $tag_list )
	            $text .= '<span class="tags">' . __( 'Tags: %2$s', 'edd' ) . '</span>';

        	$text .= '</p>';
        }
        
        do_action( 'edd_product_details_widget_before_categories_and_tags' , $instance , $download_id );

        printf( $text, $category_list, $tag_list );
        
        do_action( 'edd_product_details_widget_before_end' , $instance , $download_id );

        // Used by themes. Closes the widget
        echo $after_widget;
    }

   	/** @see WP_Widget::form */
    public function form( $instance ) {
        // Set up some default widget settings.
        $defaults = array(
            'title' 			=> sprintf( __( '%s Details', 'edd' ), edd_get_label_singular() ),
            'download_id' 		=> 'current',
            'download_title' 	=> 'on',
            'purchase_button' 	=> 'on',
            'categories' 		=> 'on',
            'tags' 				=> 'on'
        );

        $instance = wp_parse_args( (array) $instance, $defaults ); ?>
        
        <!-- Title -->
        <p>
            <label for="<?php echo esc_attr( $this->get_field_id( 'title' ) ); ?>"><?php _e( 'Title:', 'edd' ) ?></label>
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
            <label for="<?php echo esc_attr( $this->get_field_id( 'download_id' ) ); ?>"><?php printf( __( '%s', 'edd' ), edd_get_label_singular() ); ?></label>
            <select class="widefat" name="<?php echo esc_attr( $this->get_field_name( 'download_id' ) ); ?>" id="<?php echo esc_attr( $this->get_field_id( 'download_id' ) ); ?>">
            	<option value="current"><?php _e( 'Use current', 'edd' ); ?></option>
            <?php foreach ( $downloads as $download ) { ?>
                <option <?php selected( absint( $instance['download_id'] ), $download->ID ); ?> value="<?php echo esc_attr( $download->ID ); ?>"><?php echo $download->post_title; ?></option>
            <?php } ?>
            </select>
        </p>

        <!-- Download title -->
        <p>
            <input <?php checked( $instance['download_title'], 'on' ); ?> id="<?php echo esc_attr( $this->get_field_id( 'download_title' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'download_title' ) ); ?>" type="checkbox" />
            <label for="<?php echo esc_attr( $this->get_field_id( 'download_title' ) ); ?>"><?php _e( 'Show Title', 'edd' ); ?></label>
        </p>

        <!-- Show purchase button -->
        <p>
            <input <?php checked( $instance['purchase_button'], 'on' ); ?> id="<?php echo esc_attr( $this->get_field_id( 'purchase_button' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'purchase_button' ) ); ?>" type="checkbox" />
            <label for="<?php echo esc_attr( $this->get_field_id( 'purchase_button' ) ); ?>"><?php _e( 'Show Purchase Button', 'edd' ); ?></label>
        </p>

        <!-- Show download categories -->
        <p>
            <input <?php checked( $instance['categories'], 'on' ); ?> id="<?php echo esc_attr( $this->get_field_id( 'categories' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'categories' ) ); ?>" type="checkbox" />
            <label for="<?php echo esc_attr( $this->get_field_id( 'categories' ) ); ?>"><?php _e( 'Show Categories', 'edd' ); ?></label>
        </p>

        <!-- Show download tags -->
        <p>
            <input <?php checked( $instance['tags'], 'on' ); ?> id="<?php echo esc_attr( $this->get_field_id( 'tags' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'tags' ) ); ?>" type="checkbox" />
            <label for="<?php echo esc_attr( $this->get_field_id( 'tags' ) ); ?>"><?php _e( 'Show Tags', 'edd' ); ?></label>
        </p>
        
        <?php do_action( 'edd_product_details_widget_form' , $instance ); ?>
    <?php }

    /** @see WP_Widget::update */
    public function update( $new_instance, $old_instance ) {
        $instance = $old_instance;

        $instance['title'] 				= strip_tags( $new_instance['title'] );
        $instance['download_id']		= strip_tags( $new_instance['download_id'] );
    	$instance['download_title']		= isset( $new_instance['download_title'] )    ? $new_instance['download_title']  : '';
        $instance['purchase_button'] 	= isset( $new_instance['purchase_button'] )   ? $new_instance['purchase_button'] : '';
        $instance['categories'] 		= isset( $new_instance['categories'] )        ? $new_instance['categories']      : '';
        $instance['tags'] 				= isset( $new_instance['tags'] )              ? $new_instance['tags']            : '';

        do_action( 'edd_product_details_widget_update' , $instance );
        
        return $instance;
    } 

}



/**
 * Register Widgets
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
