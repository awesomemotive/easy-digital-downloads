<?php

namespace EDD\Tests\Downloads;

use EDD\Tests\Helpers\EDD_Helper_Download;
use EDD\Tests\PHPUnit\EDD_UnitTestCase;

class Search extends EDD_UnitTestCase {

	public static function setUpBeforeClass(): void {
		parent::setUpBeforeClass();

		self::factory()->post->create_many(
			5,
			array(
				'post_type' => 'download',
			)
		);
	}

	public static function tearDownAfterClass(): void {
		EDD_Helper_Download::delete_all_downloads();

		parent::tearDownAfterClass();
	}

	public function tearDown(): void {
		parent::tearDown();
		unset( $_GET['s'] );
	}

	public function test_search_empty_string() {
		$_GET['s'] = 'test';
		$search    = new \EDD\Downloads\Search();
		$results   = $search->search();

		$this->assertEmpty( $results );
	}

	public function test_search() {
		$_GET['s'] = 'Post title';
		$search    = new \EDD\Downloads\Search();
		$results   = $search->search();

		$this->assertCount( 5, $results );
	}

	/**
	 * Search for a specific title.
	 *
	 * @return void
	 */
	public function test_search_specific_title() {
		self::factory()->post->create(
			array(
				'post_type'  => 'download',
				'post_title' => 'Post title Specific',
			)
		);
		self::factory()->post->create(
			array(
				'post_type'  => 'download',
				'post_title' => 'Post title Again Specific',
			)
		);

		$_GET['s'] = '"Post title Specific"';
		$search    = new \EDD\Downloads\Search();
		$results   = $search->search();

		$this->assertCount( 1, $results );
	}

	/**
	 * Search for a fuzzy title.
	 *
	 * @return void
	 */
	public function test_search_fuzzy_title() {
		self::factory()->post->create(
			array(
				'post_type'  => 'download',
				'post_title' => 'Post title Fuzzy',
			)
		);
		self::factory()->post->create(
			array(
				'post_type'  => 'download',
				'post_title' => 'Post title Again Fuzzy',
			)
		);

		$_GET['s'] = 'Post title Fuzzy';
		$search    = new \EDD\Downloads\Search();
		$results   = $search->search();

		$this->assertCount( 2, $results );
	}
}
