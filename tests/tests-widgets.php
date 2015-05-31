<?php


/**
 * @group edd_widgets
 */
class Tests_Widgets extends WP_UnitTestCase {

	/**
	 * Test that the hooks in the file are good.
	 *
	 * @since 2.3.10
	 */
	public function test_file_hooks() {
		$this->assertNotFalse( has_action( 'widgets_init', 'edd_register_widgets' ) );
	}

	/**
	 * Test that the widgets are registered properly.
	 *
	 * @since 2.3.10
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
	 * @since 2.3.10
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
	 * @since 2.3.10
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
		$this->assertContains( '<li class="cart_item edd_subtotal"', $output );
		$this->assertContains( '<li class="cart_item edd_checkout"', $output );

	}

	/**
	 * Test that the cart widget update method returns the correct values.
	 *
	 * @since 2.3.10
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
	 * @since 2.3.10
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













	/**
	 * Test that the categories_widget widget exists with the right properties.
	 *
	 * @since 2.3.10
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
	 * @since 2.3.10
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
	 * @since 2.3.10
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
	 * @since 2.3.10
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

}