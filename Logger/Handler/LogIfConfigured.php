<?php

namespace Klevu\Logger\Logger\Handler;

use Klevu\Logger\Api\ConvertLogLevelServiceInterface;
use Klevu\Logger\Api\FileNameSanitizerServiceInterface;
use Klevu\Logger\Api\LoggingEnabledServiceInterface;
use Klevu\Logger\Api\LogFileNameProviderInterface;
use Klevu\Logger\Api\StoreScopeResolverInterface;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Exception\FileSystemException;
use Magento\Framework\Filesystem\DriverInterface;
use Magento\Framework\Logger\Handler\Base as BaseHandler;
use Psr\Log\LogLevel;

/**
 * Handler class used to write log messages if logging is enabled, taking into
 *  account store configuration for both enabled and minimum logging level
 */
class LogIfConfigured extends BaseHandler
{
    /**
     * @var DirectoryList
     */
    private $directoryList;

    /**
     * @var StoreScopeResolverInterface
     */
    private $storeScopeResolver;

    /**
     * @var LogFileNameProviderInterface
     */
    private $logFileNameProvider;

    /**
     * @var LoggingEnabledServiceInterface
     */
    private $loggingEnabledService;

    /**
     * @var FileNameSanitizerServiceInterface
     */
    private $fileNameSanitizerService;

    /**
     * @var ConvertLogLevelServiceInterface
     */
    private $convertLogLevelService;

    /**
     * LogIfConfigured constructor.
     * @param DriverInterface $filesystem
     * @param DirectoryList $directoryList
     * @param StoreScopeResolverInterface $storeScopeResolver
     * @param LogFileNameProviderInterface $logFileNameProvider
     * @param LoggingEnabledServiceInterface $loggingEnabledService
     * @param FileNameSanitizerServiceInterface $fileNameSanitizerService
     * @param ConvertLogLevelServiceInterface $convertLogLevelService
     * @param null $filePath
     * @param null $fileName
     * @throws \Exception
     */
    public function __construct(
        DriverInterface $filesystem,
        DirectoryList $directoryList,
        StoreScopeResolverInterface $storeScopeResolver,
        LogFileNameProviderInterface $logFileNameProvider,
        LoggingEnabledServiceInterface $loggingEnabledService,
        FileNameSanitizerServiceInterface $fileNameSanitizerService,
        ConvertLogLevelServiceInterface $convertLogLevelService,
        $filePath = null,
        $fileName = null
    ) {
        $this->directoryList = $directoryList;
        $this->storeScopeResolver = $storeScopeResolver;
        $this->logFileNameProvider = $logFileNameProvider;
        $this->loggingEnabledService = $loggingEnabledService;
        $this->fileNameSanitizerService = $fileNameSanitizerService;
        $this->convertLogLevelService = $convertLogLevelService;

        // Filename set this way, rather than via parent constructor argument, for compatibility with 2.1.x
        $this->fileName = $fileName;
        parent::__construct($filesystem, $filePath);
    }

    /**
     * {@inheritdoc}
     *
     * @param array $record
     * @throws FileSystemException
     */
    public function write(array $record): void
    {
        $fileName = $this->logFileNameProvider->execute(
            $this->storeScopeResolver->getCurrentStore()
        );
        if (!$fileName) {
            return;
        }

        if ($fileName !== $this->fileName) {
            $this->resetFileProperties();
        }

        if (null === $this->fileName) {
            $this->fileName = $this->directoryList->getPath(DirectoryList::LOG)
                . DIRECTORY_SEPARATOR
                . $this->fileNameSanitizerService->execute($fileName);
            /// @todo Remove bc check for Utils existence when support for 2.1.x is dropped
            $this->url = class_exists('\Monolog\Utils') && method_exists(\Monolog\Utils::class, 'canonicalizePath')
                ? \Monolog\Utils::canonicalizePath($this->fileName)
                : $this->fileName;
        }

        parent::write($record);
    }

    /**
     * Checks configuration to determine whether class should handle message
     *
     * @param array $record
     * @return bool
     */
    public function isHandling(array $record): bool
    {
        // $record['level'] is monolog (EMERGENCY: 600 -> DEBUG: 100)
        // We convert to Psr as a standardised base
        $level = isset($record['level'])
            ? $this->convertLogLevelService->fromNumeric((int)$record['level'])
            : LogLevel::DEBUG;
        $store = $this->storeScopeResolver->getCurrentStore();

        return $this->loggingEnabledService->isLoggingEnabledForLevelInStore($level, $store);
    }

    /**
     * Resets filename related properties and closes stream if file handle currently open
     *
     * @return void
     */
    private function resetFileProperties()
    {
        $this->fileName = null;
        $this->url = null;
        $this->close();
    }
}
