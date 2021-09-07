<?php
/** @noinspection DuplicatedCode */

namespace Klevu\Logger\Test\Integration\Logger;

use Magento\Store\Model\Store;
use Magento\Store\Model\StoreManagerInterface;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\TestFramework\ObjectManager;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;

class LoggerTest extends TestCase
{
    // Defined as constant because this is a virtualType
    const LOGGER_FQCN = 'Klevu\Logger\Logger\Logger';

    /**
     * @var string
     */
    private $installDir;

    /**
     * @var ObjectManager
     */
    private $objectManager;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * Tests logging at EMERGENCY level when logging is enabled for level
     *
     * @magentoAppArea adminhtml
     * @magentoCache all disabled
     * @magentoAppIsolation enabled
     * @magentoConfigFixture default/klevu_logger/configuration/enable_logging 1
     * @magentoConfigFixture default/klevu_logger/configuration/min_log_level 7
     * @return void
     */
    public function testLogWhenEnabledForAdmin()
    {
        $this->setupPhp5();

        $logFileName = 'Klevu.log';
        $logFilePath = $this->installDir . '/var/log/' . $logFileName;

        $this->storeManager->setCurrentStore(Store::ADMIN_CODE);

        /** @var LoggerInterface $logger */
        $logger = $this->objectManager->get(self::LOGGER_FQCN);

        $this->removeExistingLogFile($logFilePath);
        $this->assertFalse(file_exists($logFilePath), 'Log file ' . $logFileName . ' exists before write');

        $logger->emergency(__METHOD__);
        $this->assertTrue(file_exists($logFilePath), 'Log file ' . $logFileName . ' exists after write');
    }

    /**
     * @magentoAppArea frontend
     * @magentoCache all disabled
     * @magentoAppIsolation enabled
     * @magentoConfigFixture default/klevu_logger/configuration/enable_logging 0
     * @magentoConfigFixture default_store klevu_logger/configuration/enable_logging 1
     * @magentoConfigFixture default_store klevu_logger/configuration/min_log_level 7
     */
    public function testLogWhenEnabledForStore()
    {
        $this->setupPhp5();

        $logFileName = 'Klevu.default.log';
        $logFilePath = $this->installDir . '/var/log/' . $logFileName;

        $this->storeManager->setCurrentStore('default');

        /** @var LoggerInterface $logger */
        $logger = $this->objectManager->get(self::LOGGER_FQCN);

        $this->removeExistingLogFile($logFilePath);
        $this->assertFalse(file_exists($logFilePath), 'Log file ' . $logFileName . ' exists before write');

        $logger->emergency(__METHOD__);
        $this->assertTrue(file_exists($logFilePath), 'Log file ' . $logFileName . ' exists after write');
    }

    /**
     * @magentoAppArea frontend
     * @magentoCache all disabled
     * @magentoAppIsolation enabled
     * @magentoConfigFixture default/klevu_logger/configuration/enable_logging 1
     * @magentoConfigFixture default_store klevu_logger/configuration/enable_logging 0
     * @magentoConfigFixture default_store klevu_logger/configuration/min_log_level 7
     */
    public function testLogWhenDisabledForStore()
    {
        $this->setupPhp5();

        $logFileName = 'Klevu.default.log';
        $logFilePath = $this->installDir . '/var/log/' . $logFileName;

        $this->storeManager->setCurrentStore('default');

        /** @var LoggerInterface $logger */
        $logger = $this->objectManager->get(self::LOGGER_FQCN);

        $this->removeExistingLogFile($logFilePath);
        $this->assertFalse(file_exists($logFilePath), 'Log file ' . $logFileName . ' exists before write');

        $logger->emergency(__METHOD__);
        $this->assertFalse(file_exists($logFilePath), 'Log file ' . $logFileName . ' exists after write');
    }

    /**
     * @magentoAppArea adminhtml
     * @magentoCache all disabled
     * @magentoAppIsolation enabled
     * @magentoConfigFixture default/klevu_logger/configuration/enable_logging 1
     * @magentoConfigFixture default/klevu_logger/configuration/min_log_level 3
     * @return void
     */
    public function testLogLowerLevelThanConfiguredForAdmin()
    {
        $this->setupPhp5();

        $logFileName = 'Klevu.log';
        $logFilePath = $this->installDir . '/var/log/' . $logFileName;

        $this->storeManager->setCurrentStore(Store::ADMIN_CODE);

        /** @var LoggerInterface $logger */
        $logger = $this->objectManager->get(self::LOGGER_FQCN);

        $this->removeExistingLogFile($logFilePath);
        $this->assertFalse(file_exists($logFilePath), 'Log file ' . $logFileName . ' exists before write');

        $logger->debug(__METHOD__);
        $this->assertFalse(file_exists($logFilePath), 'Log file ' . $logFileName . ' exists after write');
    }

    /**
     * @magentoAppArea adminhtml
     * @magentoCache all disabled
     * @magentoAppIsolation enabled
     * @magentoConfigFixture default/klevu_logger/configuration/enable_logging 1
     * @magentoConfigFixture default/klevu_logger/configuration/min_log_level 7
     * @magentoConfigFixture default_store klevu_logger/configuration/min_log_level 3
     * @return void
     */
    public function testLogLowerLevelThanConfiguredForStore()
    {
        $this->setupPhp5();

        $logFileName = 'Klevu.default.log';
        $logFilePath = $this->installDir . '/var/log/' . $logFileName;

        $this->storeManager->setCurrentStore('default');

        /** @var LoggerInterface $logger */
        $logger = $this->objectManager->get(self::LOGGER_FQCN);

        $this->removeExistingLogFile($logFilePath);
        $this->assertFalse(file_exists($logFilePath), 'Log file ' . $logFileName . ' exists before write');

        $logger->debug(__METHOD__);
        $this->assertFalse(file_exists($logFilePath), 'Log file ' . $logFileName . ' exists after write');
    }

    /**
     * @depends testLogWhenEnabledForAdmin
     * @magentoAppArea adminhtml
     * @magentoCache all disabled
     * @magentoAppIsolation enabled
     * @magentoConfigFixture default/klevu_logger/configuration/enable_logging 1
     * @magentoConfigFixture default/klevu_logger/configuration/min_log_level 7
     * @return void
     */
    public function testLoggedFileContents()
    {
        $this->setupPhp5();

        $logFileName = 'Klevu.log';
        $logFilePath = $this->installDir . '/var/log/' . $logFileName;
        $testMessage = 'Test message';

        $this->storeManager->setCurrentStore(Store::ADMIN_CODE);

        /** @var LoggerInterface $logger */
        $logger = $this->objectManager->get(self::LOGGER_FQCN);

        $this->removeExistingLogFile($logFilePath);
        $logger->emergency($testMessage);

        $fileContents = file_get_contents($logFilePath);
        if (method_exists($this, 'assertStringContainsString')) {
            $this->assertStringContainsString('.EMERGENCY', $fileContents, 'Log file ' . $logFileName . ' contains level');
            $this->assertStringContainsString($testMessage, $fileContents, 'Log file ' . $logFileName . ' contains message string');
        } else {
            $this->assertContains('.EMERGENCY', $fileContents, 'Log file ' . $logFileName . ' contains level');
            $this->assertContains($testMessage, $fileContents, 'Log file ' . $logFileName . ' contains message string');
        }
    }

    /**
     * Uncomment to benchmark performance
     *
     * @return void
     */
//    public function testLogBenchmark()
//    {
//        $klevuLogger = $this->objectManager->get(static::LOGGER_FQCN);
//        $coreLogger = $this->objectManager->get(LoggerInterface::class);
//
//        $klevuStartTime = microtime(true);
//        for ($i=0; $i<1000; $i++) {
//            $klevuLogger->info(sprintf('Test Log Benchmark iteration %s', $i));
//        }
//        $klevuDelta = microtime(true) - $klevuStartTime;
//
//        $coreStartTime = microtime(true);
//        for ($i=0; $i<1000; $i++) {
//            $coreLogger->info(sprintf('Test Log Benchmark iteration %s', $i));
//        }
//        $coreDelta = microtime(true) - $coreStartTime;
//
//        $this->assertLessThan($coreDelta, $klevuDelta);
//    }

    /**
     * @return void
     * @todo Move to setUp when PHP 5.x is no longer supported
     */
    private function setupPhp5()
    {
        $this->installDir = $GLOBALS['installDir'];
        $this->objectManager = Bootstrap::getObjectManager();
        $this->storeManager = $this->objectManager->get(StoreManagerInterface::class);
    }

    /**
     * @param $filePath
     * @return void
     */
    private function removeExistingLogFile($filePath)
    {
        if (file_exists($filePath)) {
            unlink($filePath);
        }
    }
}
