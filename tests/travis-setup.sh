git clone https://github.com/sebastianbergmann/php-test-helpers.git
sh -c "cd php-test-helpers && phpize && ./configure && make && sudo make install"
echo "zend_extension=`php -r "echo ini_get('extension_dir');"`/test_helpers.so" >> `php --ini | grep "Scan for additional .ini files in:" | sed -e "s|.*:\s*||"`/z_test_helpers.ini