<?php


/**
 * @group edd_widgets
 */
class Tests_Widgets extends WP_UnitTestCase {

	/**
	 * Test that the hooks in the file are good.
	 *
	 * @since 2.4.3
	 */
	public function test_file_hooks() {
		$this->assertNotFalse( has_action( 'widgets_init', 'edd_register_widgets' ) );
	}

	/**
	 * Test that the widgets are registered properly.
	 *
	 * @since 2.4.3
	 */
	public function test_register_widget() {

		edd_register_widgets();

		$widgets = array_keys( $GLOBALS['wp_widget_factory']->widgets );
		$this->assertContains( 'edd_cart_widget', $widgets );
		$this->assertContains( 'edd_categories_tags_widget', $widgets );
		$this->assertContains( 'edd_product_details_widget', $widgets );

	}

	/**
	 * Test that the cart widget exists with the right properties.
	 *
	 * @since 2.4.3
	 */
	public function test_cart_widget() {

		$widgets = $GLOBALS['wp_widget_factory']->widgets;
		$cart_widget = $widgets['edd_cart_widget'];

		$this->assertInstanceOf( 'edd_cart_widget', $cart_widget );
		$this->assertEquals( 'edd_cart_widget', $cart_widget->id_base );
		$this->assertEquals( 'Downloads Cart', $cart_widget->name );

	}

	/**
	 * Test that the widget() method outputs HTML.
	 *
	 * @since 2.4.3
	 */
	public function test_cart_widget_function_bail_checkout() {

		$widgets = $GLOBALS['wp_widget_factory']->widgets;
		$cart_widget = $widgets['edd_cart_widget'];

		$this->go_to( get_permalink( edd_get_option( 'purchase_page' ) ) );

		ob_start();
			$cart_widget->widget( array(
				'before_title'  => '',
				'after_title'   => '',
				'before_widget' => '',
				'after_widget'  => '',
			), array(
				'title'            => 'Cart',
				'hide_on_checkout' => true,
			) );
		$output = ob_get_clean();

		$this->assertEmpty( $output );

	}

	/**
	 * Test that the widget() method outputs HTML.
	 *
	 * @since 2.4.3
	 */
	public function test_cart_widget_function() {

		$widgets = $GLOBALS['wp_widget_factory']->widgets;
		$cart_widget = $widgets['edd_cart_widget'];

		ob_start();
			$cart_widget->widget( array(
				'before_title'  => '',
				'after_title'   => '',
				'before_widget' => '',
				'after_widget'  => '',
			), array(
				'title'            => 'Cart',
				'hide_on_checkout' => true,
			) );
		$output = ob_get_clean();

		$this->assertContains( 'Number of items in cart:', $output );
		$this->assertContains( '<li class="cart_item empty">', $output );
		$this->assertContains( '<li class="cart_item edd-cart-meta edd_total"', $output );
		$this->assertContains( '<li class="cart_item edd_checkout"', $output );

	}

	/**
	 * Test that the cart widget update method returns the correct values.
	 *
	 * @since 2.4.3
	 */
	public function test_cart_widget_update() {

		$widgets = $GLOBALS['wp_widget_factory']->widgets;
		$cart_widget = $widgets['edd_cart_widget'];

		$updated = $cart_widget->update( array( 'title' => 'Your Cart', 'hide_on_checkout' => true ), array( 'title' => 'Cart', 'hide_on_checkout' => false ) );

		$this->assertEquals( $updated, array( 'title' => 'Your Cart', 'hide_on_checkout' => true ) );

	}

	/**
	 * Test that the cart widget form method outputs HTML.
	 *
	 * @since 2.4.3
	 */
	public function test_cart_widget_form() {

		$widgets = $GLOBALS['wp_widget_factory']->widgets;
		$cart_widget = $widgets['edd_cart_widget'];

		ob_start();
			$cart_widget->form( array() );
		$output = ob_get_clean();

		$this->assertRegExp( '/<label for="(.*)">(.*)<\/label>/', $output );
		$this->assertRegExp( '/<input class="widefat" id="(.*)" name="(.*)" type="text" value="(.*)"\/>/', $output );
		$this->assertRegExp( '/<input (.*) id="(.*)" name="(.*)" type="checkbox" \/>/', $output );
		$this->assertRegExp( '/<label for="(.*)">(.*)<\/label>/', $output );

	}

	/** Categories tags widget */

	/**
	 * Test that the categories_widget widget exists with the right properties.
	 *
	 * @since 2.4.3
	 */
	public function test_categories_tags_widget() {

		$widgets = $GLOBALS['wp_widget_factory']->widgets;
		$categories_widget = $widgets['edd_categories_tags_widget'];

		$this->assertInstanceOf( 'edd_categories_tags_widget', $categories_widget );
		$this->assertEquals( 'edd_categories_tags_widget', $categories_widget->id_base );
		$this->assertEquals( 'Downloads Categories / Tags', $categories_widget->name );

	}

	/**
	 * Test that the widget() method outputs HTML.
	 *
	 * @since 2.4.3
	 */
	public function test_categories_tags_widget_function() {

		$widgets = $GLOBALS['wp_widget_factory']->widgets;
		$categories_widget = $widgets['edd_categories_tags_widget'];
		$download = EDD_Helper_Download::create_simple_download();
		$terms = wp_set_object_terms( $download->ID, array( 'test1', 'test2' ), 'download_category', false );

		$this->go_to( $download->ID );


		ob_start();
			$categories_widget->widget( array(
				'before_title'  => '',
				'after_title'   => '',
				'before_widget' => '',
				'after_widget'  => '',
			), array(
				'title'      => 'Cart',
				'taxonomy'   => 'download_category',
				'count'      => true,
				'hide_empty' => true,
			) );
		$output = ob_get_clean();

		$this->assertContains( '<ul class="edd-taxonomy-widget">', $output );
		$this->assertContains( '<li class="cat-item cat-item-' . reset( $terms ), $output );
		$this->assertContains( '<li class="cat-item cat-item-' . end( $terms ), $output );

		EDD_Helper_Download::delete_download( $download->ID );

	}

	/**
	 * Test that the categories widget update method returns the correct values.
	 *
	 * @since 2.4.3
	 */
	public function test_categories_tags_widget_update() {

		$widgets = $GLOBALS['wp_widget_factory']->widgets;
		$categories_widget = $widgets['edd_categories_tags_widget'];

		$updated = $categories_widget->update(
			array( 'title' => 'Categories', 'taxonomy' => 'download_category', 'count' => true, 'hide_empty' => true ),
			array( 'title' => 'Tags', 'taxonomy' => 'download_tag', 'count' => true, 'hide_empty' => true )
		);

		$this->assertEquals( $updated, array( 'title' => 'Categories', 'taxonomy' => 'download_category', 'count' => true, 'hide_empty' => true ) );

	}

	/**
	 * Test that the cart widget form method outputs HTML.
	 *
	 * @since 2.4.3
	 */
	public function test_categories_tags_widget_form() {

		$widgets = $GLOBALS['wp_widget_factory']->widgets;
		$categories_widget = $widgets['edd_categories_tags_widget'];

		ob_start();
			$categories_widget->form( array() );
		$output = ob_get_clean();

		$this->assertRegExp( '/<label for="(.*)">Title:<\/label>/', $output );
		$this->assertRegExp( '/<input class="widefat" id="(.*)" name="(.*)" type="text" value="(.*)"\/>/', $output );
		$this->assertRegExp( '/<label for="(.*)">Taxonomy:<\/label>/', $output );
		$this->assertRegExp( '/<option value="download_category" (.*)>(.*)<\/option>/', $output );
		$this->assertRegExp( '/<option value="download_tag" (.*)>(.*)<\/option>/', $output );
		$this->assertRegExp( '/<label for="(.*)">Show Count:<\/label>/', $output );
		$this->assertRegExp( '/<label for="(.*)">Hide Empty Categories:<\/label>/', $output );

	}

	/** Product details widget */

	/**
	 * Test that the edd_product_details widget exists with the right properties.
	 *
	 * @since 2.4.3
	 */
	public function test_edd_product_details_widget() {

		$widgets = $GLOBALS['wp_widget_factory']->widgets;
		$categories_widget = $widgets['edd_product_details_widget'];

		$this->assertInstanceOf( 'EDD_Product_Details_Widget', $categories_widget );
		$this->assertEquals( 'edd_product_details', $categories_widget->id_base );
		$this->assertEquals( 'Download Details', $categories_widget->name );

	}

	/**
	 * Test that the widget() method returns when the visiting page is invalid.
	 *
	 * @since 2.4.3
	 */
	public function test_edd_product_details_widget_function_bail_no_download() {

		$this->go_to( '/' );
		$widgets = $GLOBALS['wp_widget_factory']->widgets;
		$details_widget = $widgets['edd_product_details_widget'];

		$this->assertNull( $details_widget->widget( array(), array( 'download_id' => 'current' ) ) );

	}

	/**
	 * Test that the widget() method uses the current post when 'download_id' is set to 'current'.
	 *
	 * @since 2.4.3
	 */
	public function test_edd_product_details_widget_function_bail_download() {

		$download = EDD_Helper_Download::create_simple_download();
		$this->go_to( get_permalink( $download->ID ) );
		$widgets = $GLOBALS['wp_widget_factory']->widgets;
		$details_widget = $widgets['edd_product_details_widget'];

		ob_start();
			$details_widget->widget( array(
				'before_title'  => '',
				'after_title'   => '',
				'before_widget' => '',
				'after_widget'  => '',
			), array(
				'title'           => 'Cart',
				'download_id'     => 'current',
				'download_title'  => 'download_category',
				'purchase_button' => true,
				'categories'      => true,
				'tags'            => true,
			) );
		$output = ob_get_clean();

		$this->assertNotEmpty( $output );

		EDD_Helper_Download::delete_download( $download->ID );

	}

	/**
	 * Test that the widget() method outputs HTML.
	 *
	 * @since 2.4.3
	 */
	public function test_edd_product_details_widget_function() {

		$widgets = $GLOBALS['wp_widget_factory']->widgets;
		$details_widget = $widgets['edd_product_details_widget'];
		$download = EDD_Helper_Download::create_simple_download();
		$terms = wp_set_object_terms( $download->ID, array( 'test1' ), 'download_category', false );

		$this->go_to( $download->ID );

		ob_start();
			$details_widget->widget( array(
				'before_title'  => '',
				'after_title'   => '',
				'before_widget' => '',
				'after_widget'  => '',
			), array(
				'title'           => 'Cart',
				'download_id'     => $download->ID,
				'download_title'  => 'download_category',
				'purchase_button' => true,
				'categories'      => true,
				'tags'            => true,
			) );
		$output = ob_get_clean();

		$this->assertContains( '<h3>' . $download->post_title . '</h3>', $output );
		$this->assertRegExp( '/<form id="edd_purchase_[0-9]+" class="edd_download_purchase_form edd_purchase_[0-9]+" method="post">/', $output );
		$this->assertContains( '<input type="hidden" name="edd_action" class="edd_action_input" value="add_to_cart">', $output );
		$this->assertContains( '<input type="hidden" name="download_id" value="' . $download->ID . '">', $output );
		$this->assertContains( '<p class="edd-meta">', $output );
		$this->assertContains( '<span class="categories">Download Category: ', $output );

		EDD_Helper_Download::delete_download( $download->ID );

	}

	/**
	 * Test that the cart widget form method outputs HTML.
	 *
	 * @since 2.4.3
	 */
	public function test_edd_product_details_widget_form() {

		$widgets = $GLOBALS['wp_widget_factory']->widgets;
		$categories_widget = $widgets['edd_product_details_widget'];

		ob_start();
			$categories_widget->form( array() );
		$output = ob_get_clean();

		$this->assertRegExp( '/<label for="widget-edd_product_details--title">Title:<\/label>/', $output );
		$this->assertRegExp( '/<input class="widefat" id="widget-edd_product_details--title" name="widget-edd_product_details\[\]\[title\]" type="text" value="(.*)" \/>/', $output );
		$this->assertRegExp( '/Display Type:/', $output );
		$this->assertRegExp( '/<label for="widget-edd_product_details--download_id">Download:<\/label>/', $output );
		$this->assertRegExp( '/<select class="widefat" name="widget-edd_product_details\[\]\[download_id\]" id="widget-edd_product_details--download_id">/', $output );
		$this->assertRegExp( '/<input  checked=\'checked\' id="widget-edd_product_details--download_title" name="widget-edd_product_details\[\]\[download_title\]" type="checkbox" \/>/', $output );
		$this->assertRegExp( '/<label for="widget-edd_product_details--download_title">Show Title<\/label>/', $output );
		$this->assertRegExp( '/<input  checked=\'checked\' id="widget-edd_product_details--purchase_button" name="widget-edd_product_details\[\]\[purchase_button\]" type="checkbox" \/>/', $output );
		$this->assertRegExp( '/<label for="widget-edd_product_details--purchase_button">Show Purchase Button<\/label>/', $output );
		$this->assertRegExp( '/<input  checked=\'checked\' id="widget-edd_product_details--categories" name="widget-edd_product_details\[\]\[categories\]" type="checkbox" \/>/', $output );
		$this->assertRegExp( '/<label for="widget-edd_product_details--categories">Show Download Categories<\/label>/', $output );
		$this->assertRegExp( '/<input  checked=\'checked\' id="widget-edd_product_details--tags" name="widget-edd_product_details\[\]\[tags\]" type="checkbox" \/>/', $output );
		$this->assertRegExp( '/<label for="widget-edd_product_details--tags">Show Download Tags<\/label>/', $output );

	}

	/**
	 * Test that the categories widget update method returns the correct values.
	 *
	 * @since 2.4.3
	 */
	public function test_edd_product_details_widget_update() {

		$widgets = $GLOBALS['wp_widget_factory']->widgets;
		$details_widget = $widgets['edd_product_details_widget'];

		$updated = $details_widget->update(
			array( 'title' => 'Details', 'download_id' => 123, 'display_type' => 'specific', 'download_title' => true, 'purchase_button' => true, 'categories' => true, 'tags' => true ),
			array( 'title' => 'OLD Details', 'display_type' => 'specific', 'download_id' => 123, 'download_title' => false, 'purchase_button' => false, 'categories' => false, 'tags' => false )
		);

		$this->assertEquals( $updated, array( 'title' => 'Details', 'display_type' => 'specific', 'download_id' => 123, 'download_title' => true, 'purchase_button' => true, 'categories' => true, 'tags' => true ) );

	}

}
