# Mako Framework Tests

[![Build Status](http://img.shields.io/travis/mako-framework/framework/master.svg?style=flat)](http://img.shields.io/travis/mako-framework/framework.svg?style=flat)

Here you'll find all the Mako framework tests. They are divided in to groups so you can easily run the tests you want.

	php vendor/bin/phpunit  --group unit

	php vendor/bin/phpunit  --exclude-group integration

| Group                | Description                                                           |
|----------------------|-----------------------------------------------------------------------|
| unit                 | All unit tests                                                        |
| integration          | All integration tests                                                 |
| integration:database | All integration tests that touch the database (SQLite in memory)      |