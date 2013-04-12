echo -e "Installing PHPUnit Test Helpers..."
pear channel-discover pear.phpunit.de
pecl install phpunit/test_helpers
export JPHP_INI=`php --ini | grep "Loaded Configuration" | sed -e "s|.*:\s*||"`
export JXDB=`php --ini | grep "xdebug" | sed -e "s/.*:\s*//" -e "s/,.*//"`
if [ $JXDB -a -f $JXDB ]; then sed -i='' -e "s/^/;/g" $JXDB; fi
if [[ ! `cat $JPHP_INI | grep 'test_helpers.so'` ]]; then echo "zend_extension=`php -r "echo ini_get('extension_dir');"`/test_helpers.so" >> $JPHP_INI; fi
echo -e "Installed PHPUnit Test Helpers"