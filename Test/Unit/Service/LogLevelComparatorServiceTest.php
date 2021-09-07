<?php

namespace Klevu\Logger\Test\Unit\Service;

use Klevu\Logger\Service\LogLevelComparatorService;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use PHPUnit\Framework\TestCase;

class LogLevelComparatorServiceTest extends TestCase
{
    /**
     * @var ObjectManager
     */
    private $objectManager;

    /**
     * Tests comparison when low value is high priority (eg Zend)
     *
     * @return void
     */
    public function testLowValueIsHighPriority()
    {
        $this->setupPhp5();

        /** @var LogLevelComparatorService $logLevelComparator */
        $logLevelComparator = $this->objectManager->getObject(
            LogLevelComparatorService::class,
            ['lowValueIsHighPriority' => true]
        );

        $this->assertSame(
            -1,
            $logLevelComparator->compare(600, 300),
            'Log level: 600; Min log level: 300'
        );
        $this->assertSame(
            0,
            $logLevelComparator->compare(300, 300),
            'Log level: 300; Min log level: 300'
        );
        $this->assertSame(
            1,
            $logLevelComparator->compare(300, 600),
            'Log level: 300; Min log level: 600'
        );
    }

    /**
     * Tests comparison when low value is low priority (eg Monolog)
     *
     * @return void
     */
    public function testLowValueIsLowPriority()
    {
        $this->setupPhp5();

        /** @var LogLevelComparatorService $logLevelComparator */
        $logLevelComparator = $this->objectManager->getObject(
            LogLevelComparatorService::class,
            ['lowValueIsHighPriority' => false]
        );

        $this->assertSame(
            1,
            $logLevelComparator->compare(600, 300),
            'Log level: 600; Min log level: 300'
        );
        $this->assertSame(
            0,
            $logLevelComparator->compare(300, 300),
            'Log level: 300; Min log level: 300'
        );
        $this->assertSame(
            -1,
            $logLevelComparator->compare(300, 600),
            'Log level: 300; Min log level: 600'
        );
    }

    /**
     * @return void
     * @todo Move to setUp when PHP 5.x is no longer supported
     */
    private function setupPhp5()
    {
        $this->objectManager = new ObjectManager($this);
    }
}
