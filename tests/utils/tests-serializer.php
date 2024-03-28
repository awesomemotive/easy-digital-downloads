<?php

namespace EDD\Tests\Utils;

use EDD\Utils\Data\Serializer as Utility;
use EDD\Tests\PHPUnit\EDD_UnitTestCase;

class Serializer extends EDD_UnitTestCase {

	public function test_maybe_unserialize_valid_data() {
		$valid_data        = self::get_valid_post_meta_string();
		$unserialized_data = Utility::maybe_unserialize( $valid_data );

		$this->assertIsArray( $unserialized_data );
	}

	public function test_fix_possible_serialization() {
		$valid_data   = self::get_invalid_valid_post_meta_string();
		$invalid_data = substr( $valid_data, 0 );
		$fixed_data   = Utility::fix_possible_serialization( $invalid_data );

		$this->assertIsArray( Utility::maybe_unserialize( $fixed_data ) );
	}

	public function test_maybe_unserialize_invalid_data() {
		$invalid_data        = self::get_invalid_valid_post_meta_string();
		$unserialized_data = Utility::maybe_unserialize( $invalid_data );

		$this->assertIsArray( $unserialized_data );
	}

	private static function get_valid_post_meta_string() {
		return 'a:9:{s:6:"amount";s:5:"16.15";s:4:"date";s:19:"2012-05-08 21:27:33";s:5:"email";s:22:"admin@eddunittests.net";s:3:"key";s:32:"70a6ea41f4fde6d51f79f3ac74f1f93a";s:8:"currency";s:3:"USD";s:9:"downloads";s:55:"a:1:{i:0;a:2:{s:2:"id";s:3:"989";s:7:"options";a:0:{}}}";s:9:"user_info";s:159:"a:5:{s:2:"id";s:2:"18";s:5:"email";s:22:"admin@eddunittests.net";s:10:"first_name";s:7:"Alfredd";s:9:"last_name";s:7:"Jenkins";s:8:"discount";s:9:"EDDADDONS";}";s:12:"cart_details";s:188:"a:1:{i:0;a:5:{s:4:"name";s:30:"Zendesk &#8211; Single Sign On";s:2:"id";s:3:"989";s:11:"item_number";a:2:{s:2:"id";s:3:"989";s:7:"options";a:0:{}}s:5:"price";s:2:"19";s:8:"quantity";i:1;}}";s:7:"user_id";s:2:"99";}';
	}

	/**
	 * The last_name count is 8, but the actual string is 7 characters long.
	 * This is to replicate post meta that was stored incorrectly,
	 * for example for last names with apostrophes.
	 *
	 * @return string
	 */
	private static function get_invalid_valid_post_meta_string() {
		return 'a:9:{s:6:"amount";s:5:"16.15";s:4:"date";s:19:"2012-05-08 21:27:33";s:5:"email";s:22:"admin@eddunittests.net";s:3:"key";s:32:"70a6ea41f4fde6d51f79f3ac74f1f93a";s:8:"currency";s:3:"USD";s:9:"downloads";s:55:"a:1:{i:0;a:2:{s:2:"id";s:3:"989";s:7:"options";a:0:{}}}";s:9:"user_info";s:159:"a:5:{s:2:"id";s:2:"18";s:5:"email";s:22:"admin@eddunittests.net";s:10:"first_name";s:7:"Alfredd";s:9:"last_name";s:8:"Jenkins";s:8:"discount";s:9:"EDDADDONS";}";s:12:"cart_details";s:188:"a:1:{i:0;a:5:{s:4:"name";s:30:"Zendesk &#8211; Single Sign On";s:2:"id";s:3:"989";s:11:"item_number";a:2:{s:2:"id";s:3:"989";s:7:"options";a:0:{}}s:5:"price";s:2:"19";s:8:"quantity";i:1;}}";s:7:"user_id";s:2:"99";}';
	}
}
