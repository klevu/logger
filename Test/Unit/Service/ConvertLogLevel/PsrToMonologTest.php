<?php
/** @noinspection PhpFullyQualifiedNameUsageInspection */
/** @noinspection DuplicatedCode */

namespace Klevu\Logger\Test\Unit\Service\ConvertLogLevel;

use Klevu\Logger\Service\ConvertLogLevel\PsrToMonolog;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use PHPUnit\Framework\TestCase;

class PsrToMonologTest extends TestCase
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

        /** @var PsrToMonolog $convertLogLevelService */
        $convertLogLevelService = $this->objectManager->getObject(PsrToMonolog::class);

        $fixtures = [
            \Psr\Log\LogLevel::EMERGENCY => \Monolog\Logger::EMERGENCY,
            \Psr\Log\LogLevel::ALERT => \Monolog\Logger::ALERT,
            \Psr\Log\LogLevel::CRITICAL => \Monolog\Logger::CRITICAL,
            \Psr\Log\LogLevel::ERROR => \Monolog\Logger::ERROR,
            \Psr\Log\LogLevel::WARNING => \Monolog\Logger::WARNING,
            \Psr\Log\LogLevel::NOTICE => \Monolog\Logger::NOTICE,
            \Psr\Log\LogLevel::INFO => \Monolog\Logger::INFO,
            \Psr\Log\LogLevel::DEBUG => \Monolog\Logger::DEBUG,
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

        /** @var PsrToMonolog $convertLogLevelService */
        $convertLogLevelService = $this->objectManager->getObject(PsrToMonolog::class);

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

        /** @var PsrToMonolog $convertLogLevelService */
        $convertLogLevelService = $this->objectManager->getObject(PsrToMonolog::class);

        $fixtures = [
            \Monolog\Logger::EMERGENCY => \Psr\Log\LogLevel::EMERGENCY,
            \Monolog\Logger::ALERT => \Psr\Log\LogLevel::ALERT,
            \Monolog\Logger::CRITICAL => \Psr\Log\LogLevel::CRITICAL,
            \Monolog\Logger::ERROR => \Psr\Log\LogLevel::ERROR,
            \Monolog\Logger::WARNING => \Psr\Log\LogLevel::WARNING,
            \Monolog\Logger::NOTICE => \Psr\Log\LogLevel::NOTICE,
            \Monolog\Logger::INFO => \Psr\Log\LogLevel::INFO,
            \Monolog\Logger::DEBUG => \Psr\Log\LogLevel::DEBUG,
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

        /** @var PsrToMonolog $convertLogLevelService */
        $convertLogLevelService = $this->objectManager->getObject(PsrToMonolog::class);

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
