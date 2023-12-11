@echo off

SET XDEBUG_MODE=coverage
vendor\bin\phpunit --coverage-clover clover.xml

pause