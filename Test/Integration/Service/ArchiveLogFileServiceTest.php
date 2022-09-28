<?php

namespace Klevu\Logger\Test\Integration\Service;

use Klevu\Logger\Exception\ArchiveLogFileException;
use Klevu\Logger\Service\ArchiveLogFileService;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\ObjectManagerInterface;
use Magento\TestFramework\Helper\Bootstrap;
use PHPUnit\Framework\TestCase;

class ArchiveLogFileServiceTest extends TestCase
{
    /**
     * @var string
     */
    private $installDir;

    /**
     * @var ObjectManagerInterface
     */
    private $objectManager;

    /**
     * Tests happy path of archiving an existing file within var/log
     *
     * @magentoAppArea adminhtml
     * @magentoAppIsolation enabled
     * Happy Path
     */
    public function testExecuteValid()
    {
        $this->setupPhp5();

        /** @var ArchiveLogFileService $archiveLogFileService */
        $archiveLogFileService = $this->objectManager->create(ArchiveLogFileService::class, [
            'directoryList' => $this->objectManager->create(DirectoryList::class, [
                'root' => $this->installDir,
            ]),
            'permittedArchiveLocations' => [
                'var/log',
            ],
        ]);

        $sourceFilePath = $this->installDir . '/var/log/Klevu_Search.log';

        $this->createSourceFile($sourceFilePath);
        $this->assertFileExists($sourceFilePath);
        $sourceContentHash = crc32(file_get_contents($sourceFilePath));

        /** @noinspection PhpUnhandledExceptionInspection */
        $reportedDestinationFilePath = $archiveLogFileService->execute($sourceFilePath);
        if (method_exists($this, 'assertMatchesRegularExpression')) {
            $this->assertMatchesRegularExpression(
                '/' . preg_quote($this->installDir . '/var/log/Klevu_Search.', '/') . '[0-9]{9,11}' . preg_quote('.log', '/') . '/',
                $reportedDestinationFilePath
            );
        } else {
            $this->assertRegExp(
                '/' . preg_quote($this->installDir . '/var/log/Klevu_Search.', '/') . '[0-9]{9,11}' . preg_quote('.log', '/') . '/',
                $reportedDestinationFilePath
            );
        }
        if (method_exists($this, 'assertFileDoesNotExist')) {
            $this->assertFileDoesNotExist($sourceFilePath);
        } else {
            $this->assertFileNotExists($sourceFilePath);
        }
        $this->assertFileExists($reportedDestinationFilePath);

        $destinationContentHash = crc32(file_get_contents($reportedDestinationFilePath));
        $this->assertSame($sourceContentHash, $destinationContentHash);
    }

    /**
     * Tests expected failure attempting to archive file within non-whitelisted directory
     *
     * @magentoAppArea adminhtml
     * @magentoAppIsolation enabled
     */
    public function testExecuteIllegalLocation()
    {
        $this->setupPhp5();

        /** @var ArchiveLogFileService $archiveLogFileService */
        $archiveLogFileService = $this->objectManager->create(ArchiveLogFileService::class, [
            'directoryList' => $this->objectManager->create(DirectoryList::class, [
                'root' => $this->installDir,
            ]),
            'permittedArchiveLocations' => [
                'var/log',
            ],
        ]);

        $sourceFilePath = $this->installDir . '/etc/Klevu_Search.log';

        $this->createSourceFile($sourceFilePath);
        $this->assertFileExists($sourceFilePath);

        $destinationFilePath = $this->installDir . '/etc/Klevu_Search.' . time() . '.log';
        if (method_exists($this, 'expectException')) {
            $this->expectException(ArchiveLogFileException::class);
        } else {
            $this->setExpectedException(ArchiveLogFileException::class);
        }
        $reportedDestinationFilePath = $archiveLogFileService->execute($sourceFilePath);
        $this->assertEmpty($reportedDestinationFilePath, var_export($reportedDestinationFilePath, true));
        $this->assertFileExists($sourceFilePath);
        if (method_exists($this, 'assertFileDoesNotExist')) {
            $this->assertFileDoesNotExist($destinationFilePath);
        } else {
            $this->assertFileNotExists($destinationFilePath);
        }
    }

    /**
     * Tests expected failure when file to archive does not exist
     *
     * @magentoAppArea adminhtml
     * @magentoAppIsolation enabled
     */
    public function testExecuteMissingSourceFile()
    {
        $this->setupPhp5();

        /** @var ArchiveLogFileService $archiveLogFileService */
        $archiveLogFileService = $this->objectManager->create(ArchiveLogFileService::class, [
            'directoryList' => $this->objectManager->create(DirectoryList::class, [
                'root' => $this->installDir,
            ]),
            'permittedArchiveLocations' => [
                'var/log',
            ],
        ]);
        $sourceFilePath = $this->installDir . '/var/log/Klevu_Search' . crc32(time()) . '.log';

        if (method_exists($this, 'assertFileDoesNotExist')) {
            $this->assertFileDoesNotExist($sourceFilePath);
        } else {
            $this->assertFileNotExists($sourceFilePath);
        }

        if (method_exists($this, 'expectException')) {
            $this->expectException(ArchiveLogFileException::class);
        } else {
            $this->setExpectedException(ArchiveLogFileException::class);
        }
        $reportedDestinationFilePath = $archiveLogFileService->execute($sourceFilePath);
        $this->assertEmpty($reportedDestinationFilePath);
    }

    /**
     * @return void
     * @todo Move to setUp when PHP 5.x is no longer supported
     */
    private function setupPhp5()
    {
        $this->installDir = $GLOBALS['installDir'];
        $this->objectManager = Bootstrap::getObjectManager();
    }

    /**
     * @param string $sourceFilePath
     * @return void
     */
    private function createSourceFile($sourceFilePath)
    {
        if (!file_exists($sourceFilePath)) {
            touch($sourceFilePath);
            file_put_contents($sourceFilePath, 'Test Content');
        }

        if (!file_exists($sourceFilePath)) {
            throw new \RuntimeException(sprintf(
                'Could not create test source file !%s"',
                $sourceFilePath
            ));
        }
    }
}
