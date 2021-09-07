<?php
/** @noinspection PhpFullyQualifiedNameUsageInspection */
/** @noinspection DuplicatedCode */

namespace Klevu\Logger\Test\Unit\Service\ConvertLogLevel;

use Klevu\Logger\Service\ConvertLogLevel\ZendToMonolog;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use PHPUnit\Framework\TestCase;

class ZendToMonologTest extends TestCase
{
    /**
     * @var ObjectManager
     */
    private $objectManager;

    /**
     * Tests known Psr levels return corresponding Monolog level
     * Happy Path
     *
     * @return void
     */
    public function testToNumericKnown()
    {
        $this->setupPhp5();

        /** @var ZendToMonolog $convertLogLevelService */
        $convertLogLevelService = $this->objectManager->getObject(ZendToMonolog::class);

        $fixtures = [
            \Klevu\Logger\Constants::ZEND_LOG_EMERG => \Monolog\Logger::EMERGENCY,
            \Klevu\Logger\Constants::ZEND_LOG_ALERT => \Monolog\Logger::ALERT,
            \Klevu\Logger\Constants::ZEND_LOG_CRIT => \Monolog\Logger::CRITICAL,
            \Klevu\Logger\Constants::ZEND_LOG_ERR => \Monolog\Logger::ERROR,
            \Klevu\Logger\Constants::ZEND_LOG_WARN => \Monolog\Logger::WARNING,
            \Klevu\Logger\Constants::ZEND_LOG_NOTICE => \Monolog\Logger::NOTICE,
            \Klevu\Logger\Constants::ZEND_LOG_INFO => \Monolog\Logger::INFO,
            \Klevu\Logger\Constants::ZEND_LOG_DEBUG => \Monolog\Logger::DEBUG,
        ];
        foreach ($fixtures as $value => $expectedResult) {
            $actualResult = $convertLogLevelService->toNumeric((string)$value);
            $this->assertSame($expectedResult, $actualResult, var_export($value, true));
        }
    }

    /**
     * Tests unknown Psr levels return null
     *
     * @return void
     */
    public function testToNumericUnknown()
    {
        $this->setupPhp5();

        /** @var ZendToMonolog $convertLogLevelService */
        $convertLogLevelService = $this->objectManager->getObject(ZendToMonolog::class);

        $unknownFixtures = [
            'CRIT',
            'foo',
            '100',
        ];
        foreach ($unknownFixtures as $fixture) {
            $this->assertNull($convertLogLevelService->toNumeric($fixture), var_export($fixture, true));
        }

        $invalidFixtures = [
            99999,
            100.00,
            false,
            [],
        ];
        foreach ($invalidFixtures as $fixture) {
            if (method_exists($this, 'expectException')) {
                $this->expectException(\InvalidArgumentException::class);
            } elseif (method_exists($this, 'setExpectedException')) {
                $this->setExpectedException(\InvalidArgumentException::class);
            }
            $convertLogLevelService->toNumeric($fixture);
        }
    }

    /**
     * Tests known Monolog levels return corresponding Psr level
     * Happy Path
     *
     * @return void
     */
    public function testFromNumericKnown()
    {
        $this->setupPhp5();

        /** @var ZendToMonolog $convertLogLevelService */
        $convertLogLevelService = $this->objectManager->getObject(ZendToMonolog::class);

        $fixtures = [
            \Monolog\Logger::EMERGENCY => (string)\Klevu\Logger\Constants::ZEND_LOG_EMERG,
            \Monolog\Logger::ALERT => (string)\Klevu\Logger\Constants::ZEND_LOG_ALERT,
            \Monolog\Logger::CRITICAL => (string)\Klevu\Logger\Constants::ZEND_LOG_CRIT,
            \Monolog\Logger::ERROR => (string)\Klevu\Logger\Constants::ZEND_LOG_ERR,
            \Monolog\Logger::WARNING => (string)\Klevu\Logger\Constants::ZEND_LOG_WARN,
            \Monolog\Logger::NOTICE => (string)\Klevu\Logger\Constants::ZEND_LOG_NOTICE,
            \Monolog\Logger::INFO => (string)\Klevu\Logger\Constants::ZEND_LOG_INFO,
            \Monolog\Logger::DEBUG => (string)\Klevu\Logger\Constants::ZEND_LOG_DEBUG,
        ];
        foreach ($fixtures as $value => $expectedResult) {
            $actualResult = $convertLogLevelService->fromNumeric($value);
            $this->assertSame($expectedResult, $actualResult, var_export($value, true));
        }
    }

    /**
     * Tests unknown Monolog levels return null
     *
     * @return void
     */
    public function testFromNumericUnknown()
    {
        $this->setupPhp5();

        /** @var ZendToMonolog $convertLogLevelService */
        $convertLogLevelService = $this->objectManager->getObject(ZendToMonolog::class);

        $unknownFixtures = [
            99999,
        ];
        foreach ($unknownFixtures as $fixture) {
            $this->assertNull($convertLogLevelService->fromNumeric($fixture), var_export($fixture, true));
        }

        $invalidFixtures = [
            100.00,
            'CRIT',
            'foo',
            '100',
            false,
            [],
        ];
        foreach ($invalidFixtures as $fixture) {
            if (method_exists($this, 'expectException')) {
                $this->expectException(\InvalidArgumentException::class);
            } elseif (method_exists($this, 'setExpectedException')) {
                $this->setExpectedException(\InvalidArgumentException::class);
            }
            $convertLogLevelService->fromNumeric($fixture);
        }
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
