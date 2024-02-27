<?php

namespace EDD\Tests\HTML;

use EDD\Tests\PHPUnit\EDD_UnitTestCase;

class Select extends EDD_UnitTestCase {

	public function test_customer_dropdown() {
		$customer_dropdown = EDD()->html->customer_dropdown();

		$this->assertStringContainsString( 'name="customers"', $customer_dropdown );
		$this->assertStringContainsString( 'id="customers"', $customer_dropdown );
		$this->assertStringContainsString( 'class="edd-select edd-select-chosen edd-customer-select"', $customer_dropdown );
		$this->assertStringContainsString( 'data-placeholder="Choose a Customer"', $customer_dropdown );
		$this->assertStringContainsString( 'data-search-type="customer"', $customer_dropdown );
		$this->assertStringContainsString( 'data-search-placeholder="Search Customers"', $customer_dropdown );
		$this->assertStringContainsString( 'No customers found', $customer_dropdown );
	}

	public function test_discount_dropdown() {
		$meta = array(
			'name'              => '50 Percent Off',
			'type'              => 'percent',
			'amount'            => '50',
			'code'              => '50PERCENTOFF',
			'product_condition' => 'all',
		);

		edd_store_discount( $meta );

		$dropdown = EDD()->html->discount_dropdown();

		$this->assertStringContainsString( 'name="edd_discounts"', $dropdown );
		$this->assertStringContainsString( 'id="discounts"', $dropdown );
		$this->assertStringContainsString( 'class="edd-select edd-select-chosen edd-user-select"', $dropdown );
	}

	public function test_category_dropdown() {
		$category_dropdown = EDD()->html->category_dropdown();
		$this->assertStringContainsString( 'name="edd_categories"', $category_dropdown );
		$this->assertStringContainsString( 'class="edd-select"', $category_dropdown );
		$this->assertStringContainsString( 'All Download Categories', $category_dropdown );
	}

	public function test_year_dropdown() {

		$year_dropdown = EDD()->html->year_dropdown();

		$this->assertStringContainsString( 'name="year"', $year_dropdown );
		$this->assertStringContainsString( 'id="edd_year_select_year"', $year_dropdown );
		$this->assertStringContainsString( 'class="edd-select"', $year_dropdown );
		$this->assertStringContainsString( '<option value="2020"', $year_dropdown );
		$this->assertStringContainsString( date( 'Y' ), $year_dropdown );
	}

	public function test_year_dropdown_variable() {
		$years_before = 5;
		$years_after  = 5;
		$current_year = date( 'Y' );
		$year_dropdown = EDD()->html->year_dropdown( 'year', 0, $years_before, $years_after );

		$this->assertStringContainsString( 'name="year"', $year_dropdown );
		$this->assertStringContainsString( 'id="edd_year_select_year"', $year_dropdown );
		$this->assertStringContainsString( 'class="edd-select"', $year_dropdown );

		$start_year = $current_year - $years_before;
		$end_year   = $current_year + $years_after;

		$this->assertStringContainsString( $start_year, $year_dropdown );
		$this->assertStringContainsString( $end_year, $year_dropdown );
	}

	public function test_month_dropdown() {
		$out = EDD()->html->month_dropdown();

		$this->assertStringContainsString( 'name="month"', $out );
		$this->assertStringContainsString( 'id="edd_month_select_month"', $out );
		$this->assertStringContainsString( 'class="edd-select"', $out );
		$this->assertStringContainsString( '<option value="1"', $out );
		$this->assertStringContainsString( '<option value="2"', $out );
		$this->assertStringContainsString( '<option value="3"', $out );
		$this->assertStringContainsString( '<option value="4"', $out );
		$this->assertStringContainsString( '<option value="5"', $out );
		$this->assertStringContainsString( '<option value="6"', $out );
		$this->assertStringContainsString( '<option value="7"', $out );
		$this->assertStringContainsString( '<option value="8"', $out );
		$this->assertStringContainsString( '<option value="9"', $out );
		$this->assertStringContainsString( '<option value="10"', $out );
		$this->assertStringContainsString( '<option value="11"', $out );
		$this->assertStringContainsString( '<option value="12"', $out );
	}

	public function test_category_select() {
		for ( $i = 0; $i < 5; $i++ ) {
			$category = wp_insert_term( 'Download Category ' . $i, 'download_category' );
			// create a post and assign it to the category
			$post = wp_insert_post(
				array(
					'post_title'   => 'Download ' . $i,
					'post_content' => 'Download ' . $i,
					'post_status'  => 'publish',
					'post_type'    => 'download',
					'post_author'  => 1,
					'post_date'    => date( 'Y-m-d H:i:s' ),
					'post_category' => array( $category['term_id'] ),
				)
			);
			wp_set_object_terms( $post, $category['term_id'], 'download_category' );
		}

		$dropdown = new \EDD\HTML\CategorySelect(
			array(
				'name'             => 'categories[]',
				'id'               => 'edd-categories',
				'selected'         => array(),
				'multiple'         => true,
				'chosen'           => true,
				'show_option_all'  => false,
				'show_option_none' => false,
				'number'           => 30,
			)
		);
		$category_dropdown = $dropdown->get();
		$this->assertStringContainsString( 'name="categories[]"', $category_dropdown );
		$this->assertStringContainsString( 'id="edd_categories"', $category_dropdown );
		$this->assertStringContainsString( 'class="edd-select edd-select-chosen"', $category_dropdown );
	}

	public function test_user_dropdown() {
		// create many users
		for ( $i = 0; $i < 5; $i++ ) {
			$user_id = $this->factory->user->create();
		}

		$user_dropdown = EDD()->html->user_dropdown();

		$this->assertStringContainsString( 'name="users"', $user_dropdown );
		$this->assertStringContainsString( 'id="users"', $user_dropdown );
		$this->assertStringContainsString( 'class="edd-select edd-select-chosen edd-user-select"', $user_dropdown );
		$this->assertStringContainsString( 'data-placeholder="Select a User"', $user_dropdown );
	}

	public function test_country_select() {
		$country_select = EDD()->html->country_select( array(), 'US' );

		$this->assertStringContainsString( 'name="edd_countries"', $country_select );
		$this->assertStringContainsString( '"US" selected', $country_select );
		$this->assertStringContainsString( 'class="edd-select edd-select-chosen edd_countries_filter"', $country_select );
	}

	public function test_region_select() {
		$region_select = EDD()->html->region_select( array(), 'US' );

		$this->assertStringContainsString( 'name="edd_regions"', $region_select );
		$this->assertStringContainsString( 'value="TN"', $region_select );
		$this->assertStringContainsString( 'class="edd-select edd-select-chosen edd_regions_filter"', $region_select );
	}
}
