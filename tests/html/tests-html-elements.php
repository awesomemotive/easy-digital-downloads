<?php
namespace EDD\Tests\HTML;

use EDD\Tests\PHPUnit\EDD_UnitTestCase;

/**
 * EDD HTML Elements Tests
 *
 * @group edd_html
 *
 * @coversDefaultClass EDD_HTML_Elements
 */
class Elements extends EDD_UnitTestCase {

	public function test_class_instance() {
		$this->assertInstanceOf( '\\EDD\\HTML\\Elements', EDD()->html );
	}

	public function test_legacy_class_alias() {
		$legacy_class = new \EDD_HTML_Elements();

		$this->assertInstanceOf( '\\EDD\\HTML\\Elements', $legacy_class );
	}

	/**
	 * @covers EDD_HTML_Elements::select
	 */
	public function test_select_is_required() {
		$select = EDD()->html->select(
			array(
				'required' => true,
				'options'  => array(
					1 => '1',
					2 => '2',
					3 => '3',
				),
			)
		);

		$this->assertStringContainsString( 'required', $select );
	}

	/**
	 * @covers EDD_HTML_Elements::select
	 */
	public function test_select_is_not_required() {
		$select = EDD()->html->select(
			array(
				'options' => array(
					1 => '1',
					2 => '2',
					3 => '3',
				)
			)
		);

		$this->assertStringNotContainsString( 'required', $select );
	}

	/**
	 * @covers EDD_HTML_Elements::text
	 */
	public function test_text_is_required() {
		$this->assertStringContainsString( 'required', EDD()->html->text( array( 'required' => true ) ) );
	}

	/**
	 * @covers EDD_HTML_Elements::text
	 */
	public function test_text_is_not_required() {
		$this->assertStringNotContainsString( 'required', EDD()->html->text() );
	}

	public function test_checkbox() {
		$checkbox = EDD()->html->checkbox(
			array(
				'name'  => 'edd-checkbox',
				'label' => 'Checkbox',
			)
		);

		$this->assertStringContainsString( 'name="edd-checkbox"', $checkbox );
		$this->assertStringContainsString( '<label for="edd-checkbox">Checkbox</label>', $checkbox );
	}

	public function test_textarea() {
		$textarea = EDD()->html->textarea(
			array(
				'name'  => 'edd-textarea',
				'label' => 'Textarea',
			)
		);

		$this->assertStringContainsString( 'name="edd-textarea"', $textarea );
		$this->assertStringContainsString( '<label class="edd-label" for="edd-textarea">', $textarea );
	}

	public function test_text() {
		$text = EDD()->html->text(
			array(
				'id'    => 'edd-text',
				'name'  => 'edd-text',
				'label' => 'Text',
			)
		);

		$this->assertStringContainsString( 'name="edd-text"', $text );
		$this->assertStringContainsString( '<label class="edd-label" for="edd-text">', $text );
	}

	public function test_date_field() {
		$date_field = EDD()->html->date_field(
			array(
				'id'    => 'edd-date-field',
				'name'  => 'edd-date-field',
				'label' => 'Date Field',
			)
		);

		$this->assertStringContainsString( 'name="edd-date-field"', $date_field );
		$this->assertStringContainsString( 'edd_datepicker', $date_field );
		$this->assertStringContainsString( 'data-format="yyyy-mm-dd"', $date_field );
	}

	public function test_date_field_custom_class() {
		$date_field = EDD()->html->date_field(
			array(
				'id'    => 'edd-date-field',
				'name'  => 'edd-date-field',
				'label' => 'Date Field',
				'class' => 'custom-class',
			)
		);

		$this->assertStringContainsString( 'name="edd-date-field"', $date_field );
		$this->assertStringContainsString( 'edd_datepicker', $date_field );
		$this->assertStringContainsString( 'custom-class', $date_field );
		$this->assertStringContainsString( 'data-format="yyyy-mm-dd"', $date_field );
	}

	public function test_ajax_user_search() {
		$user_search = EDD()->html->ajax_user_search();

		$this->assertStringContainsString( 'name="user_id"', $user_search );
		$this->assertStringContainsString( 'data-placeholder="Select a User"', $user_search );
		$this->assertStringContainsString( 'edd-user-select', $user_search );
	}

	public function test_checkbox_toggle() {
		$toggle = new \EDD\HTML\CheckboxToggle(
			array(
				'name'  => 'once_per_customer',
				'label' => __( 'Prevent customers from using this discount more than once.', 'easy-digital-downloads' ),
			)
		);
		$output = $toggle->get();

		$this->assertStringContainsString( 'name="once_per_customer"', $output );
		$this->assertStringContainsString( 'edd-toggle', $output );
	}

	public function test_checkbox_toggle_disabled_readonly() {
		$toggle = new \EDD\HTML\CheckboxToggle(
			array(
				'name'    => 'once_per_customer',
				'label'   => __( 'Prevent customers from using this discount more than once.', 'easy-digital-downloads' ),
				'options' => array(
					'disabled' => true,
					'readonly' => true,
				),
			)
		);
		$output = $toggle->get();

		$this->assertStringContainsString( 'name="once_per_customer"', $output );
		$this->assertStringContainsString( 'edd-toggle', $output );
		$this->assertStringContainsString( 'disabled', $output );
		$this->assertStringContainsString( 'readonly', $output );
	}

	public function test_upload() {
		$upload = new \EDD\HTML\Upload(
			array(
				'id'   => 'edd-upload',
				'name' => 'edd-upload',
				'value' => 'http://example.com/image.jpg',
				'desc' => 'Upload or choose a logo to be displayed at the top of sales receipt emails. Displayed on HTML emails only.',
			)
		);
		$output = $upload->get();

		$this->assertStringContainsString( 'name="edd-upload"', $output );
		$this->assertStringContainsString( 'http://example.com/image.jpg', $output );
		$this->assertStringContainsString( 'Attach File', $output );
		$this->assertStringContainsString( 'Upload or choose a logo', $output );
	}

	/**
	 * Test multicheck with missing label.
	 * This covers the bug fix for issue #2305 where missing labels caused errors.
	 */
	public function test_multicheck_missing_label() {
		$multicheck = new \EDD\HTML\Multicheck(
			array(
				'name'    => 'test-options',
				'options' => array(
					'option1' => array(
						'label'   => 'Option 1',
						'checked' => true,
					),
					'option2' => array(
						// Missing label - should fallback to empty string
						'checked' => false,
					),
				),
			)
		);
		$output = $multicheck->get();

		$this->assertStringContainsString( 'name="test-options[option1]"', $output );
		$this->assertStringContainsString( 'Option 1', $output );
		// option2 has no label — verify the label tag renders with empty content.
		$this->assertMatchesRegularExpression( '/<label\b[^>]*for="test-options\[option2\]"[^>]*>\s*<\/label>/', $output );
	}

	/**
	 * Test multicheck with array label (invalid).
	 * This also covers issue #2305 - should not crash with array label.
	 */
	public function test_multicheck_array_label() {
		$multicheck = new \EDD\HTML\Multicheck(
			array(
				'name'    => 'test-options',
				'options' => array(
					'option1' => array(
						'label' => array( 'invalid', 'label' ), // Invalid: array instead of string
						'checked' => false,
					),
				),
			)
		);
		$output = $multicheck->get();

		$this->assertStringContainsString( 'name="test-options[option1]"', $output );
		// Array label should render as empty string, not crash.
		$this->assertMatchesRegularExpression( '/<label\b[^>]*for="test-options\[option1\]"[^>]*>\s*<\/label>/', $output );
	}

	/**
	 * The product dropdown does not offer another author's draft.
	 */
	public function test_get_products_omits_another_authors_draft() {
		$draft = self::factory()->post->create(
			array(
				'post_type'   => 'download',
				'post_status' => 'draft',
				'post_author' => self::factory()->user->create( array( 'role' => 'administrator' ) ),
			)
		);

		wp_set_current_user( self::factory()->user->create( array( 'role' => 'shop_vendor' ) ) );

		$ids = wp_list_pluck( EDD()->html->get_products(), 'ID' );

		$this->assertNotContains( $draft, $ids );
	}

	/**
	 * The product dropdown still offers a published product from another author.
	 *
	 * The control for the case above: without it, an empty dropdown would satisfy it too.
	 */
	public function test_get_products_includes_another_authors_published_product() {
		$published = self::factory()->post->create(
			array(
				'post_type'   => 'download',
				'post_status' => 'publish',
				'post_author' => self::factory()->user->create( array( 'role' => 'administrator' ) ),
			)
		);

		wp_set_current_user( self::factory()->user->create( array( 'role' => 'shop_vendor' ) ) );

		$ids = wp_list_pluck( EDD()->html->get_products(), 'ID' );

		$this->assertContains( $published, $ids );
	}

	/**
	 * Someone who can edit others' products is still offered their draft.
	 */
	public function test_get_products_includes_another_authors_draft_for_a_manager() {
		$draft = self::factory()->post->create(
			array(
				'post_type'   => 'download',
				'post_status' => 'draft',
				'post_author' => self::factory()->user->create( array( 'role' => 'administrator' ) ),
			)
		);

		wp_set_current_user( self::factory()->user->create( array( 'role' => 'shop_manager' ) ) );

		$ids = wp_list_pluck( EDD()->html->get_products(), 'ID' );

		$this->assertContains( $draft, $ids );
	}

	/**
	 * A published product is not crowded out of the limit by unreadable drafts.
	 */
	public function test_get_products_does_not_let_unreadable_drafts_crowd_out_a_published_product() {
		$owner = self::factory()->user->create( array( 'role' => 'administrator' ) );
		foreach ( range( 1, 3 ) as $i ) {
			self::factory()->post->create(
				array(
					'post_type'   => 'download',
					'post_status' => 'draft',
					'post_title'  => 'AAA Draft ' . $i,
					'post_author' => $owner,
				)
			);
		}

		$published = self::factory()->post->create(
			array(
				'post_type'   => 'download',
				'post_status' => 'publish',
				'post_title'  => 'ZZZ Published',
			)
		);

		wp_set_current_user( self::factory()->user->create( array( 'role' => 'shop_vendor' ) ) );

		$ids = wp_list_pluck( EDD()->html->get_products( array( 'number' => 1 ) ), 'ID' );

		$this->assertContains( $published, $ids );
	}

	/**
	 * A valid product is not crowded out of the limit by excluded bundles.
	 */
	public function test_get_products_does_not_let_excluded_bundles_crowd_out_a_valid_product() {
		foreach ( range( 1, 3 ) as $i ) {
			$bundle = self::factory()->post->create(
				array(
					'post_type'   => 'download',
					'post_status' => 'publish',
					'post_title'  => 'AAA Bundle ' . $i,
				)
			);
			update_post_meta( $bundle, '_edd_product_type', 'bundle' );
		}

		$simple = self::factory()->post->create(
			array(
				'post_type'   => 'download',
				'post_status' => 'publish',
				'post_title'  => 'ZZZ Simple',
			)
		);

		$ids = wp_list_pluck(
			EDD()->html->get_products(
				array(
					'number'  => 1,
					'bundles' => false,
				)
			),
			'ID'
		);

		$this->assertContains( $simple, $ids );
	}

	/**
	 * A negative number still means every matching product, not every product but the last.
	 */
	public function test_get_products_preserves_an_unlimited_request() {
		$titles = array( 'AAA Product', 'BBB Product', 'CCC Product' );
		foreach ( $titles as $title ) {
			self::factory()->post->create(
				array(
					'post_type'   => 'download',
					'post_status' => 'publish',
					'post_title'  => $title,
				)
			);
		}

		$names = wp_list_pluck( EDD()->html->get_products( array( 'number' => -1 ) ), 'post_title' );

		$this->assertContains( 'CCC Product', $names );
	}

	/**
	 * A store can still cap an otherwise unlimited request.
	 */
	public function test_get_products_honors_a_filtered_cap_on_an_unlimited_request() {
		foreach ( range( 1, 5 ) as $i ) {
			self::factory()->post->create(
				array(
					'post_type'   => 'download',
					'post_status' => 'publish',
					'post_title'  => 'Product ' . $i,
				)
			);
		}

		add_filter(
			'edd_product_dropdown_args',
			function ( $args ) {
				$args['posts_per_page'] = 2;

				return $args;
			}
		);

		$products = EDD()->html->get_products( array( 'number' => -1 ) );

		remove_all_filters( 'edd_product_dropdown_args' );

		$this->assertCount( 2, $products );
	}

	/**
	 * A callback can still tell an unlimited request apart from a bounded one.
	 */
	public function test_get_products_exposes_the_actual_requested_number_to_the_filter() {
		$captured = null;
		add_filter(
			'edd_product_dropdown_args',
			function ( $args ) use ( &$captured ) {
				$captured = $args['posts_per_page'];

				return $args;
			}
		);

		EDD()->html->get_products( array( 'number' => -1 ) );

		remove_all_filters( 'edd_product_dropdown_args' );

		$this->assertSame( -1, $captured );
	}

	/**
	 * A callback that raises posts_per_page above the requested number is honored, not
	 * truncated back down.
	 */
	public function test_get_products_honors_a_filtered_increase_above_the_requested_number() {
		foreach ( range( 1, 5 ) as $i ) {
			self::factory()->post->create(
				array(
					'post_type'   => 'download',
					'post_status' => 'publish',
					'post_title'  => 'Product ' . $i,
				)
			);
		}

		add_filter(
			'edd_product_dropdown_args',
			function ( $args ) {
				$args['posts_per_page'] = 5;

				return $args;
			}
		);

		$products = EDD()->html->get_products( array( 'number' => 2 ) );

		remove_all_filters( 'edd_product_dropdown_args' );

		$this->assertCount( 5, $products );
	}

	/**
	 * A negative number other than -1 is its own absolute value, not "unlimited".
	 */
	public function test_get_products_treats_other_negative_numbers_as_their_absolute_value() {
		$titles = array( 'AAA Product', 'BBB Product', 'CCC Product' );
		foreach ( $titles as $title ) {
			self::factory()->post->create(
				array(
					'post_type'   => 'download',
					'post_status' => 'publish',
					'post_title'  => $title,
				)
			);
		}

		$products = EDD()->html->get_products( array( 'number' => -2 ) );

		$this->assertCount( 2, $products );
	}

	/**
	 * The query orders by more than title, so paging by offset stays stable.
	 */
	public function test_get_products_orders_by_a_unique_tiebreaker() {
		$captured = null;
		add_filter(
			'edd_product_dropdown_args',
			function ( $args ) use ( &$captured ) {
				$captured = $args;

				return $args;
			}
		);

		EDD()->html->get_products();

		remove_all_filters( 'edd_product_dropdown_args' );

		$this->assertStringContainsString( 'ID', $captured['orderby'] );
	}

	/**
	 * A store can still reverse the dropdown's order.
	 *
	 * The control for the case above: the tie-breaker must not come at the cost of this.
	 */
	public function test_get_products_still_honors_a_filtered_order() {
		add_filter(
			'edd_product_dropdown_args',
			function ( $args ) {
				$args['order'] = 'DESC';

				return $args;
			}
		);

		$aaa = self::factory()->post->create(
			array(
				'post_type'   => 'download',
				'post_status' => 'publish',
				'post_title'  => 'AAA Product',
			)
		);
		$zzz = self::factory()->post->create(
			array(
				'post_type'   => 'download',
				'post_status' => 'publish',
				'post_title'  => 'ZZZ Product',
			)
		);

		$ids = wp_list_pluck( EDD()->html->get_products(), 'ID' );

		remove_all_filters( 'edd_product_dropdown_args' );

		$this->assertSame(
			array_search( $zzz, $ids, true ) < array_search( $aaa, $ids, true ),
			true,
			'DESC must still sort the Z-titled product before the A-titled one.'
		);
	}

	/**
	 * A filtered posts_per_page or offset is honored, not overwritten by the batching.
	 */
	public function test_get_products_honors_a_filtered_posts_per_page_and_offset() {
		$titles = array( 'AAA Product', 'BBB Product', 'CCC Product' );
		foreach ( $titles as $title ) {
			self::factory()->post->create(
				array(
					'post_type'   => 'download',
					'post_status' => 'publish',
					'post_title'  => $title,
				)
			);
		}

		add_filter(
			'edd_product_dropdown_args',
			function ( $args ) {
				$args['posts_per_page'] = 1;
				$args['offset']         = 1;

				return $args;
			}
		);

		$names = wp_list_pluck( EDD()->html->get_products( array( 'number' => 1 ) ), 'post_title' );

		remove_all_filters( 'edd_product_dropdown_args' );

		$this->assertSame( array( 'BBB Product' ), $names );
	}

	/**
	 * A filtered posts_per_page caps the result outright, rather than being paged past.
	 */
	public function test_get_products_treats_a_filtered_posts_per_page_as_a_hard_cap() {
		foreach ( range( 1, 5 ) as $i ) {
			self::factory()->post->create(
				array(
					'post_type'   => 'download',
					'post_status' => 'publish',
					'post_title'  => 'Product ' . $i,
				)
			);
		}

		add_filter(
			'edd_product_dropdown_args',
			function ( $args ) {
				$args['posts_per_page'] = 2;

				return $args;
			}
		);

		$products = EDD()->html->get_products( array( 'number' => 30 ) );

		remove_all_filters( 'edd_product_dropdown_args' );

		$this->assertCount( 2, $products );
	}

	/**
	 * A number passed as the string '-1' still means unlimited.
	 */
	public function test_get_products_treats_the_string_form_of_minus_one_as_unlimited() {
		$titles = array( 'AAA Product', 'BBB Product', 'CCC Product' );
		foreach ( $titles as $title ) {
			self::factory()->post->create(
				array(
					'post_type'   => 'download',
					'post_status' => 'publish',
					'post_title'  => $title,
				)
			);
		}

		$names = wp_list_pluck( EDD()->html->get_products( array( 'number' => '-1' ) ), 'post_title' );

		$this->assertContains( 'CCC Product', $names );
	}

	/**
	 * The dropdown args filter runs once, not once per pagination batch.
	 */
	public function test_get_products_applies_the_args_filter_only_once() {
		$owner = self::factory()->user->create( array( 'role' => 'administrator' ) );
		foreach ( range( 1, 35 ) as $i ) {
			self::factory()->post->create(
				array(
					'post_type'   => 'download',
					'post_status' => 'draft',
					'post_title'  => sprintf( 'AAA Draft %02d', $i ),
					'post_author' => $owner,
				)
			);
		}

		$published = self::factory()->post->create(
			array(
				'post_type'   => 'download',
				'post_status' => 'publish',
				'post_title'  => 'ZZZ Published',
			)
		);

		wp_set_current_user( self::factory()->user->create( array( 'role' => 'shop_vendor' ) ) );

		$calls = 0;
		add_filter(
			'edd_product_dropdown_args',
			function ( $args ) use ( &$calls ) {
				++$calls;

				return $args;
			}
		);

		$ids = wp_list_pluck( EDD()->html->get_products( array( 'number' => 1 ) ), 'ID' );

		remove_all_filters( 'edd_product_dropdown_args' );

		$this->assertContains( $published, $ids, 'Fixture: this needs a second pagination batch to be meaningful.' );
		$this->assertSame( 1, $calls, 'The filter must run once, not once per pagination batch.' );
	}

	/**
	 * A caller nothing can be filtered away from asks the database for what it wants, once.
	 */
	public function test_get_products_asks_for_only_what_it_wants_when_nothing_can_be_filtered() {
		wp_set_current_user( self::factory()->user->create( array( 'role' => 'shop_manager' ) ) );

		$this->assertTrue(
			\EDD\Downloads\Search::can_read_all_products(),
			'Fixture: this caller has to be one nothing can be filtered away from.'
		);

		$sizes = $this->record_requested_page_sizes();

		EDD()->html->get_products( array( 'number' => 30 ) );

		$this->assertSame( array( 30 ), $sizes(), 'One query, for exactly the number asked for.' );
	}

	/**
	 * A caller who may not see everything still gets a batch big enough to page with.
	 *
	 * The control for the case above: without it, never batching at all would satisfy it too.
	 */
	public function test_get_products_still_batches_for_a_caller_who_cannot_read_everything() {
		wp_set_current_user( self::factory()->user->create( array( 'role' => 'shop_vendor' ) ) );

		$this->assertFalse(
			\EDD\Downloads\Search::can_read_all_products(),
			'Fixture: this caller has to be one products can be filtered away from.'
		);

		$sizes = $this->record_requested_page_sizes();

		EDD()->html->get_products( array( 'number' => 30 ) );

		$recorded = $sizes();

		$this->assertNotEmpty( $recorded, 'Fixture: the recorder has to have seen the query.' );
		$this->assertSame( 60, $recorded[0], 'The first batch has to leave room for filtering.' );
	}

	/**
	 * A callback which removes the count rather than changing it does not warn.
	 */
	public function test_get_products_survives_a_callback_that_removes_the_count() {
		self::factory()->post->create(
			array(
				'post_type'   => 'download',
				'post_status' => 'publish',
				'post_title'  => 'Unset Count',
			)
		);

		add_filter(
			'edd_product_dropdown_args',
			function ( $product_args ) {
				unset( $product_args['posts_per_page'] );

				return $product_args;
			}
		);

		$products = EDD()->html->get_products( array( 'number' => 5 ) );

		remove_all_filters( 'edd_product_dropdown_args' );

		$this->assertIsArray( $products );
	}

	/**
	 * A batch nothing survives does not walk the whole catalog.
	 *
	 * A callback which changes the row shape leaves `filter_by_readability()` with nothing it
	 * recognizes, so every batch filters to zero and the loop's own exit condition never trips.
	 */
	public function test_get_products_stops_reading_batches_at_the_limit() {
		wp_set_current_user( self::factory()->user->create( array( 'role' => 'shop_vendor' ) ) );

		// Asking for 1 makes the batch 30, so 61 products are three batches: 30, 30, 1. Without a
		// bound the loop reads all three, because only the short last batch ends it.
		foreach ( range( 1, 61 ) as $i ) {
			self::factory()->post->create(
				array(
					'post_type'   => 'download',
					'post_status' => 'publish',
					'post_title'  => sprintf( 'Bounded %02d', $i ),
				)
			);
		}

		add_filter(
			'edd_product_dropdown_args',
			function ( $product_args ) {
				$product_args['fields'] = 'ids';

				return $product_args;
			}
		);
		add_filter(
			'edd/product_dropdown/batch_limit',
			function () {
				return 2;
			}
		);

		$sizes = $this->record_requested_page_sizes();

		$products = EDD()->html->get_products( array( 'number' => 1 ) );

		remove_all_filters( 'edd_product_dropdown_args' );
		remove_all_filters( 'edd/product_dropdown/batch_limit' );

		$recorded = $sizes();

		// Fixture: nothing survives, so the result can never grow and only the bound can stop it.
		$this->assertEmpty( $products, 'Fixture: nothing must survive filtering, or the bound is not what stopped this.' );
		$this->assertSame( array( 30, 30 ), $recorded, 'The loop must stop at the batch limit, not at the end of the catalog.' );
	}

	/**
	 * Records the posts_per_page every product query asks for.
	 *
	 * @return callable Returns the recorded page sizes, in order.
	 */
	private function record_requested_page_sizes() {
		$sizes = array();

		add_action(
			'pre_get_posts',
			function ( $query ) use ( &$sizes ) {
				if ( 'download' === $query->get( 'post_type' ) ) {
					$sizes[] = (int) $query->get( 'posts_per_page' );
				}
			}
		);

		return function () use ( &$sizes ) {
			return $sizes;
		};
	}
}
