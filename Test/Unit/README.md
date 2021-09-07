Unit Tests
==========

Backwards Compatibility
-----------------------

When running tests in Magento < 2.2.x, please search and replace the following string
```php
use PHPUnit\Framework\TestCase;
```
with
```php
use \PHPUnit_Framework_TestCase as TestCase;
```
in all files.