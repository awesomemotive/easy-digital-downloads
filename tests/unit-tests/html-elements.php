<?php

/**
 * Test HTML Elements
 */
class Test_HTML_Elements extends WP_UnitTestCase {
	protected $_post_id = null;

	public function setUp() {
		parent::setUp();

		$wp_factory = new WP_UnitTest_Factory;
		$post_id = $wp_factory->post->create( array( 'post_title' => 'Test Download', 'post_type' => 'download', 'post_status' => 'publish' ) );
		$this->_post_id = $post_id;

		$slug     = defined( 'EDD_SLUG' ) ? EDD_SLUG : 'downloads';

		$category_labels = array(
			'name' 				=> _x( 'Download Categories', 'taxonomy general name', 'edd' ),
			'singular_name' 	=> _x( 'Category', 'taxonomy singular name', 'edd' ),
			'search_items' 		=> __( 'Search Categories', 'edd'  ),
			'all_items' 		=> __( 'All Categories', 'edd'  ),
			'parent_item' 		=> __( 'Parent Category', 'edd'  ),
			'parent_item_colon' => __( 'Parent Category:', 'edd'  ),
			'edit_item' 		=> __( 'Edit Category', 'edd'  ),
			'update_item' 		=> __( 'Update Category', 'edd'  ),
			'add_new_item' 		=> __( 'Add New Category', 'edd'  ),
			'new_item_name' 	=> __( 'New Category Name', 'edd'  ),
			'menu_name' 		=> __( 'Categories', 'edd'  ),
		);

		$category_args = apply_filters( 'edd_download_category_args', array(
				'hierarchical' 	=> true,
				'labels' 		=> apply_filters('edd_download_category_labels', $category_labels),
				'show_ui' 		=> true,
				'query_var' 	=> 'download_category',
				'rewrite' 		=> array('slug' => $slug . '/category', 'with_front' => false, 'hierarchical' => true ),
				'capabilities'  => array( 'manage_terms' => 'manage_product_terms','edit_terms' => 'edit_product_terms','assign_terms' => 'assign_product_terms','delete_terms' => 'delete_product_terms' )
			)
		);
		register_taxonomy( 'download_category', array('download'), $category_args );

		foreach ( get_object_taxonomies('download') as $tax ) :
			wp_insert_term( 'eBooks', $tax );
			wp_insert_term( 'Plugins', $tax );
		endforeach;
	}

	public function test_product_dropdown() {
		$expected = '<select name="edd_products" id="edd_products"><option value="'. $this->_post_id .'">Test Download</option></select>';
		$this->assertEquals( $expected, EDD()->html->product_dropdown() );
	}

	public function test_discount_dropdown() {
		$wp_factory = new WP_UnitTest_Factory;
		$post_id = $wp_factory->post->create( array( 'post_type' => 'edd_discount', 'post_status' => 'publish' ) );

		$meta = array(
			'type' => 'percent',
			'amount' => '20',
			'code' => '20OFF',
			'product_condition' => 'all',
			'start' => '12/12/2000 00:00:00',
			'expiration' => '12/31/2050 23:59:59',
			'max_uses' => 10,
			'uses' => 54,
			'min_price' => 128,
			'is_not_global' => true,
			'product_condition' => 'any',
			'is_single_use' => true
		);

		foreach( $meta as $key => $value ) {
			update_post_meta( $post_id, '_edd_discount_' . $key, $value );
		}

		$expected = '<select name="edd_discounts" id="edd_discounts"><option value="'. $post_id .'">Post title 1</option></select>';

		$this->assertEquals( $expected, EDD()->html->dicount_dropdown() );
	}

	public function test_year_dropdown() {
		$expected = '<select name="year" id="year"><option value="2008">2008</option><option value="2009">2009</option><option value="2010">2010</option><option value="2011">2011</option><option value="2012">2012</option><option value="2013" selected=\'selected\'>2013</option></select>';
		$this->assertEquals( $expected, EDD()->html->year_dropdown() );
	}

	public function test_month_dropdown() {
		$out = EDD()->html->month_dropdown();
		$this->assertContains( '<select name="month" id="month">', $out );
		$this->assertContains( '<option value="1">', $out );
		$this->assertContains( '<option value="2">', $out );
		$this->assertContains( '<option value="3">', $out );
		$this->assertContains( '<option value="4">', $out );
		$this->assertContains( '<option value="5">', $out );
		$this->assertContains( '<option value="6">', $out );
		$this->assertContains( '<option value="7">', $out );
		$this->assertContains( '<option value="8">', $out );
		$this->assertContains( '<option value="9">', $out );
		$this->assertContains( '<option value="10">', $out );
		$this->assertContains( '<option value="11">', $out );
		$this->assertContains( '<option value="12">', $out );
	}
}