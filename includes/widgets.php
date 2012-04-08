<?php

/***********************
* front end widgets
***********************/

// register all widgets
function edd_register_widgets() {
	register_widget('edd_cart_widget');
}
add_action('widgets_init', 'edd_register_widgets');

/**
 * Downloads Cart Widget Class
 */
class edd_cart_widget extends WP_Widget {

    /** constructor */
    function edd_cart_widget() {
        parent::WP_Widget(false, $name = __('Downloads Cart', 'edd'), array('description' => __('Display the downloads shopping cart', 'edd')));	
    }

    /** @see WP_Widget::widget */
    function widget($args, $instance) {	
        extract( $args );
        $title 	= apply_filters('widget_title', $instance['title']);
		$quantity = isset($instance['quantity']) ? $instance['quantity'] : false;
		
		global $post, $edd_options;
		
		echo $before_widget; 
            if ( $title ) {
				if($quantity == 1) {
					$quantity = ' - <span class="edd-cart-quantity">' . edd_get_cart_quantity() . '</span>';
				} else {
					$quantity = '';
				}
                echo $before_title . $title . $quantity . $after_title;
			}	
			do_action('edd_before_cart_widget');
			echo edd_cart_widget();
			do_action('edd_after_cart_widget');
		echo $after_widget;
    }

    /** @see WP_Widget::update */
    function update($new_instance, $old_instance) {		
		$instance = $old_instance;
		$instance['title'] = strip_tags($new_instance['title']);
		$instance['quantity'] = strip_tags($new_instance['quantity']);
        return $instance;
    }

    /** @see WP_Widget::form */
    function form($instance) {	
        $title = esc_attr($instance['title']);
        $quantity = esc_attr($instance['quantity']);
        ?>
         <p>
          <label for="<?php echo $this->get_field_id('title'); ?>"><?php _e('Title:', 'edd'); ?></label> 
          <input class="widefat" id="<?php echo $this->get_field_id('title'); ?>" name="<?php echo $this->get_field_name('title'); ?>" type="text" value="<?php echo $title; ?>" />
        </p>
		<p>
          <label for="<?php echo $this->get_field_id('quantity'); ?>"><?php _e('Show Quantity:', 'edd'); ?></label> 
          <input id="<?php echo $this->get_field_id('quantity'); ?>" name="<?php echo $this->get_field_name('quantity'); ?>" type="checkbox" value="1" <?php checked('1', $quantity); ?>/>
        </p>
        <?php 
    }
}

/***********************
* dashboard widgets
* -- disabled for now
***********************/
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
	edd_show_download_eanings_graph();
}
*/