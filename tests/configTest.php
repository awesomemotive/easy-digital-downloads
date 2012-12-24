<?php
/**
 * ConfigTest Tests
 * Some basic tests
 */
class ConfigTest extends WP_UnitTestCase {
    function test_is_email_only_letters_with_dot_com_domain() {
        $this->assertEquals( 'chriscct7@gmail.com', is_email( 'chriscct7@gmail.com' ) );
    }
    
    function test_is_email_should_not_allow_missing_tld() {
        $this->assertFalse( is_email( 'chriscct7@gmail' ) );
    }
    
    function test_is_email_should_allow_bg_domain() {
        $this->assertEquals( 'chriscct7@gmail.bg', is_email( 'chriscct7@gmail.bg' ) );
    }
}