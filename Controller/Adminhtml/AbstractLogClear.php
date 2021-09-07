<?php

namespace Klevu\Logger\Controller\Adminhtml;

use Klevu\Logger\Api\ArchiveLogFileServiceInterface;
use Klevu\Logger\Api\LogFileNameProviderInterface;
use Klevu\Logger\Api\StoreScopeResolverInterface;
use Klevu\Logger\Exception\ArchiveLogFileException;
use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Filesystem\Io\File as FileIo;
use Psr\Log\LoggerInterface;

/**
 * Abstract controller to provide log clearing actions in backend
 * Extended with relevant services injected for relevant log types
 */
abstract class AbstractLogClear extends Action
{
    /**
     * @var DirectoryList
     */
    private $directoryList;

    /**
     * @var FileIo
     */
    private $fileIo;

    /**
     * @var StoreScopeResolverInterface
     */
    private $storeScopeResolver;

    /**
     * @var LogFileNameProviderInterface
     */
    private $logFileNameProvider;

    /**
     * @var ArchiveLogFileServiceInterface
     */
    private $archiveLogFileService;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * AbstractLogClear constructor.
     * @param Context $context
     * @param LoggerInterface $logger
     * @param DirectoryList $directoryList
     * @param FileIo $fileIo
     * @param StoreScopeResolverInterface $storeScopeResolver
     * @param LogFileNameProviderInterface $logFileNameProvider
     * @param ArchiveLogFileServiceInterface $archiveLogFileService
     */
    public function __construct(
        Context $context,
        LoggerInterface $logger,
        DirectoryList $directoryList,
        FileIo $fileIo,
        StoreScopeResolverInterface $storeScopeResolver,
        LogFileNameProviderInterface $logFileNameProvider,
        ArchiveLogFileServiceInterface $archiveLogFileService
    ) {
        parent::__construct($context);

        $this->logger = $logger;
        $this->directoryList = $directoryList;
        $this->fileIo = $fileIo;
        $this->storeScopeResolver = $storeScopeResolver;
        $this->logFileNameProvider = $logFileNameProvider;
        $this->archiveLogFileService = $archiveLogFileService;
    }

    /**
     * @return ResultInterface
     */
    public function execute()
    {
        /** @var ResultInterface $returnRedirect */
        $returnRedirect = $this->_redirect($this->_redirect->getRefererUrl());

        try {
            $logFileName = $this->logFileNameProvider->execute(
                $this->storeScopeResolver->getCurrentStore()
            );
            $filePath = $this->directoryList->getPath(DirectoryList::LOG)
                . DIRECTORY_SEPARATOR
                . $logFileName;
            $archiveFilePath = $this->archiveLogFileService->execute($filePath);
            $archiveFilePathPathinfo = $this->fileIo->getPathInfo($archiveFilePath);

            if ($archiveFilePath) {
                $this->messageManager->addSuccessMessage(__(
                    'File "%1" has been successfully renamed to "%2".',
                    $logFileName,
                    isset($archiveFilePathPathinfo['basename']) ? $archiveFilePathPathinfo['basename'] : ''
                ));
            } else {
                $this->messageManager->addErrorMessage(__(
                    'An error occurred while renaming file "%1". Please try again and, if this issue persists, contact support',
                    $logFileName
                ));
            }
        } catch (ArchiveLogFileException $e) {
            $this->messageManager->addExceptionMessage($e);
            foreach ($e->getErrors() as $error) {
                $this->logger->error($error->getMessage(), [
                    'method' => __METHOD__,
                    'originalException' => $error,
                ]);
            }
        } catch (LocalizedException $e) {
            $this->messageManager->addExceptionMessage($e);
            $this->logger->error($e->getMessage(), [
                'method' => __METHOD__,
                'originalException' => $e,
            ]);
        } catch (\Exception $e) {
            $this->messageManager->addErrorMessage(__(
                'A system error occurred while archiving the file. Please check logs for details.'
            ));
            $this->logger->error($e->getMessage(), [
                'method' => __METHOD__,
                'originalException' => $e,
            ]);
        }

        return $returnRedirect;
    }
}
