<?php
/**
 * Widgets
 *
 * @package     Easy Digital Downloads
 * @subpackage  Widgets
 * @copyright   Copyright (c) 2012, Pippin Williamson
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       1.0 
*/


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
 * @access      private
 * @since       1.0 
 * @return      void
*/
    
class edd_cart_widget extends WP_Widget {

    /** constructor */
    function edd_cart_widget()
    {
        parent::WP_Widget(false, __('Downloads Cart', 'edd'), array('description' => __('Display the downloads shopping cart', 'edd')));
    }

    /** @see WP_Widget::widget */
    function widget($args, $instance)
    {
        extract($args);
        $title = apply_filters('widget_title', $instance['title']);
        $quantity = isset($instance['quantity']) ? $instance['quantity'] : false;

        global $post, $edd_options;

        echo $before_widget;
        if ($title) {
            if ($quantity == 1) {
                $quantity = ' - <span class="edd-cart-quantity">' . edd_get_cart_quantity() . '</span>';
            } else {
                $quantity = '';
            }
            echo $before_title . $title . $quantity . $after_title;
        }
        do_action('edd_before_cart_widget');
        edd_shopping_cart(true);
        do_action('edd_after_cart_widget');
        echo $after_widget;
    }

    /** @see WP_Widget::update */
    function update($new_instance, $old_instance)
    {
        $instance = $old_instance;
        $instance['title'] = strip_tags($new_instance['title']);
        $instance['quantity'] = strip_tags($new_instance['quantity']);
        return $instance;
    }

    /** @see WP_Widget::form */
    function form($instance)
    {
        $title = isset($instance['title']) ? esc_attr($instance['title']) : '';
        $quantity = isset($instance['quantity']) ? esc_attr($instance['quantity']) : '';
        ?>
    <p>
        <label for="<?php echo $this->get_field_id('title'); ?>"><?php _e('Title:', 'edd'); ?></label>
        <input class="widefat" id="<?php echo $this->get_field_id('title'); ?>"
               name="<?php echo $this->get_field_name('title'); ?>" type="text" value="<?php echo $title; ?>"/>
    </p>
    <p>
        <label for="<?php echo $this->get_field_id('quantity'); ?>"><?php _e('Show Quantity:', 'edd'); ?></label>
        <input id="<?php echo $this->get_field_id('quantity'); ?>"
               name="<?php echo $this->get_field_name('quantity'); ?>" type="checkbox"
               value="1" <?php checked('1', $quantity); ?>/>
    </p>
    <?php
    }
}


/**
 * Categories / Tags Widget
 *
 * Downloads categories / tags widget class.
 *
 * @access      private
 * @since       1.0 
 * @return      void
*/

class edd_categories_tags_widget extends WP_Widget {

    /** constructor */
    function edd_categories_tags_widget()
    {
        parent::WP_Widget(false, __('Downloads Categories / Tags', 'edd'), array('description' => __('Display the downloads categories or tags', 'edd')));
    }

    /** @see WP_Widget::widget */
    function widget($args, $instance)
    {
        extract($args);
        $title = apply_filters('widget_title', $instance['title']);
        $tax = $instance['taxonomy'];
        
        global $post, $edd_options;

        echo $before_widget;
        if ($title) {
            echo $before_title . $title . $after_title;
        }
        
        do_action('edd_before_taxonomy_widget');
        $terms = get_terms($tax);

			if (is_wp_error($terms)) {
			    return;
			} else {
	            echo "<ul class=\"edd-taxonomy-widget\">\n";
			    foreach ($terms as $term) {
			        echo '<li><a href="' . get_term_link( $term ) . '" title="' . esc_attr( $term->name ) . '" rel="bookmark">' . $term->name . '</a></li>'."\n";
			    }
			    echo "</ul>\n";
			}
        
        do_action('edd_after_taxonomy_widget');
        echo $after_widget;
    }

    /** @see WP_Widget::update */
    function update($new_instance, $old_instance)
    {
        $instance = $old_instance;
        $instance['title'] = strip_tags($new_instance['title']);
        $instance['taxonomy'] = strip_tags($new_instance['taxonomy']);
        return $instance;
    }

    /** @see WP_Widget::form */
    function form($instance)
    {
        $title = isset($instance['title']) ? esc_attr($instance['title']) : '';
        $taxonomy = isset($instance['taxonomy']) ? esc_attr($instance['taxonomy']) : 'download_category';
        ?>
	    <p>
	        <label for="<?php echo $this->get_field_id('title'); ?>"><?php _e('Title:', 'edd'); ?></label>
	        <input class="widefat" id="<?php echo $this->get_field_id('title'); ?>"
	               name="<?php echo $this->get_field_name('title'); ?>" type="text" value="<?php echo $title; ?>"/>
	    </p>
	    <p>
	        <label for="<?php echo $this->get_field_id('taxonomy'); ?>"><?php _e('Taxonomy:', 'edd'); ?></label>
	        <select name="<?php echo $this->get_field_name('taxonomy'); ?>" id="<?php echo $this->get_field_id('taxonomy'); ?>">
	            <option value="download_category" <?php selected('download_category',$taxonomy); ?>><?php _e('Categories', 'edd'); ?></option>
	            <option value="download_tag" <?php selected('download_tag',$taxonomy); ?>><?php _e('Tags', 'edd'); ?></option>
	        </select>
	    </p>
    <?php
    }
}


/**
 * Register Widgets
 *
 * Registers the EDD Widgets.
 *
 * @access      private
 * @since       1.0 
 * @return      void
*/

function edd_register_widgets() {
    register_widget('edd_cart_widget');
    register_widget('edd_categories_tags_widget');
}
add_action('widgets_init', 'edd_register_widgets');

/*
|--------------------------------------------------------------------------
| DASHBOARD WIDGETS
|--------------------------------------------------------------------------
|
| Disabled for now.
|
*/

/*
function edd_stats_widgets() {
   wp_add_dashboard_widget('edd_sales_widget', __('Download Sales', 'edd'), 'edd_dashboard_sales_widget');
   wp_add_dashboard_widget('edd_earnings_widget', __('Download Earnings', 'edd'), 'edd_dashboard_earnings_widget');
}
add_action('wp_dashboard_setup', 'edd_stats_widgets', 999);

function edd_dashboard_sales_widget() {
	edd_show_download_sales_graph();
}

function edd_dashboard_earnings_widget() {
	edd_show_download_earnings_graph();
}
*/