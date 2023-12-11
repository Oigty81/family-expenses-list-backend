@echo off

SET XDEBUG_MODE=coverage
vendor\bin\phpunit --coverage-html _coverage-report

pause