#!/bin/bash

#######################################################
#
# TEST HOWTO
#
# Copy this file to e.g. set-local-test-env.sh and
# set the various variables below. Then source the
# file on you bash shell:
#
# ~/git/php-database$ . ./set-local-test-env.sh
#
# and start the PHP Unit tests:
#
# ~/git/php-database$ ./vendor/bin/phpunit tests
#
#######################################################

# The Database connect information
DB_TEST_HOST=www.example.com
DB_TEST_PORT=3306
DB_TEST_NAME=test
DB_TEST_PREFIX=test
DB_TEST_USER=username
DB_TEST_PASS=password

export DB_TEST_HOST
export DB_TEST_PORT
export DB_TEST_NAME
export DB_TEST_PREFIX
export DB_TEST_USER
export DB_TEST_PASS

