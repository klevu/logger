<?php
/** @noinspection PhpFullyQualifiedNameUsageInspection */
/** @noinspection DuplicatedCode */

namespace Klevu\Logger\Test\Unit\Service\ConvertLogLevel;

use Klevu\Logger\Service\ConvertLogLevel\PsrToZend;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use PHPUnit\Framework\TestCase;

class PsrToZendTest extends TestCase
{
    /**
     * @var ObjectManager
     */
    private $objectManager;

    /**
     * Tests known Psr levels return corresponding Zend level
     * Happy Path
     *
     * @return void
     */
    public function testToNumericKnown()
    {
        $this->setupPhp5();

        /** @var PsrToZend $convertLogLevelService */
        $convertLogLevelService = $this->objectManager->getObject(PsrToZend::class);

        $fixtures = [
            \Psr\Log\LogLevel::EMERGENCY => \Klevu\Logger\Constants::ZEND_LOG_EMERG,
            \Psr\Log\LogLevel::ALERT => \Klevu\Logger\Constants::ZEND_LOG_ALERT,
            \Psr\Log\LogLevel::CRITICAL => \Klevu\Logger\Constants::ZEND_LOG_CRIT,
            \Psr\Log\LogLevel::ERROR => \Klevu\Logger\Constants::ZEND_LOG_ERR,
            \Psr\Log\LogLevel::WARNING => \Klevu\Logger\Constants::ZEND_LOG_WARN,
            \Psr\Log\LogLevel::NOTICE => \Klevu\Logger\Constants::ZEND_LOG_NOTICE,
            \Psr\Log\LogLevel::INFO => \Klevu\Logger\Constants::ZEND_LOG_INFO,
            \Psr\Log\LogLevel::DEBUG => \Klevu\Logger\Constants::ZEND_LOG_DEBUG,
        ];
        foreach ($fixtures as $value => $expectedResult) {
            $actualResult = $convertLogLevelService->toNumeric($value);
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

        /** @var PsrToZend $convertLogLevelService */
        $convertLogLevelService = $this->objectManager->getObject(PsrToZend::class);

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
     * Tests known Zend levels return corresponding Psr level
     * Happy Path
     *
     * @return void
     */
    public function testFromNumericKnown()
    {
        $this->setupPhp5();

        /** @var PsrToZend $convertLogLevelService */
        $convertLogLevelService = $this->objectManager->getObject(PsrToZend::class);

        $fixtures = [
            \Klevu\Logger\Constants::ZEND_LOG_EMERG => \Psr\Log\LogLevel::EMERGENCY,
            \Klevu\Logger\Constants::ZEND_LOG_ALERT => \Psr\Log\LogLevel::ALERT,
            \Klevu\Logger\Constants::ZEND_LOG_CRIT => \Psr\Log\LogLevel::CRITICAL,
            \Klevu\Logger\Constants::ZEND_LOG_ERR => \Psr\Log\LogLevel::ERROR,
            \Klevu\Logger\Constants::ZEND_LOG_WARN => \Psr\Log\LogLevel::WARNING,
            \Klevu\Logger\Constants::ZEND_LOG_NOTICE => \Psr\Log\LogLevel::NOTICE,
            \Klevu\Logger\Constants::ZEND_LOG_INFO => \Psr\Log\LogLevel::INFO,
            \Klevu\Logger\Constants::ZEND_LOG_DEBUG => \Psr\Log\LogLevel::DEBUG,
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

        /** @var PsrToZend $convertLogLevelService */
        $convertLogLevelService = $this->objectManager->getObject(PsrToZend::class);

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
