<?php

namespace Klevu\Logger\Controller\Adminhtml;

use Klevu\Logger\Api\LogFileNameProviderInterface;
use Klevu\Logger\Api\StoreScopeResolverInterface;
use Klevu\Logger\Validator\ArgumentValidationTrait;
use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\App\Response\Http\FileFactory;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Archive\ArchiveInterface;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Filesystem\Io\File as FileIo;
use Psr\Log\LoggerInterface;

/**
 * Abstract controller to provide log download actions in backend
 * Extended with relevant services injected for relevant log types
 */
abstract class AbstractLogDownload extends Action
{
    use ArgumentValidationTrait;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var FileIo
     */
    private $fileIo;

    /**
     * @var DirectoryList
     */
    private $directoryList;

    /**
     * @var StoreScopeResolverInterface
     */
    private $storeScopeResolver;

    /**
     * @var ArchiveInterface
     */
    private $archiveService;

    /**
     * @var FileFactory
     */
    private $fileFactory;

    /**
     * @var LogFileNameProviderInterface
     */
    private $logFileNameProvider;

    /**
     * @var int|mixed
     */
    private $maxFileSize = 1073741824;

    /**
     * AbstractLogDownload constructor.
     * @param Context $context
     * @param LoggerInterface $logger
     * @param DirectoryList $directoryList
     * @param FileIo $fileIo
     * @param StoreScopeResolverInterface $storeScopeResolver
     * @param LogFileNameProviderInterface $logFileNameProvider
     * @param ArchiveInterface $archiveService
     * @param FileFactory $fileFactory
     * @param int|null $maxFileSize
     */
    public function __construct(
        Context $context,
        LoggerInterface $logger,
        FileIo $fileIo,
        DirectoryList $directoryList,
        StoreScopeResolverInterface $storeScopeResolver,
        LogFileNameProviderInterface $logFileNameProvider,
        ArchiveInterface $archiveService,
        FileFactory $fileFactory,
        $maxFileSize = null
    ) {
        $this->validateIntArgument($maxFileSize, __METHOD__, 'maxFileSize', true);

        parent::__construct($context);

        $this->logger = $logger;
        $this->fileIo = $fileIo;
        $this->directoryList = $directoryList;
        $this->storeScopeResolver = $storeScopeResolver;
        $this->logFileNameProvider = $logFileNameProvider;
        $this->archiveService = $archiveService;
        $this->fileFactory = $fileFactory;
        if (null !== $maxFileSize) {
            $this->maxFileSize = $maxFileSize;
        }
    }

    /**
     * {@inheritdoc}
     * @return ResponseInterface|ResultInterface
     */
    public function execute()
    {
        try {
            $logFileName = $this->logFileNameProvider->execute(
                $this->storeScopeResolver->getCurrentStore()
            );
            $sourceFilePath = $this->directoryList->getPath(DirectoryList::LOG)
                . DIRECTORY_SEPARATOR
                . $logFileName;

            if (!$this->fileIo->fileExists($sourceFilePath)) {
                $this->messageManager->addErrorMessage(__('Source file "%1" could not be found', $sourceFilePath));

                return $this->_redirect($this->_redirect->getRefererUrl());
            }

            if (null !== $this->maxFileSize && filesize($sourceFilePath) > $this->maxFileSize) {
                $this->messageManager->addErrorMessage(__(
                    'Source file "%1" larger than max downloadable size of %2 bytes',
                    $sourceFilePath,
                    $this->maxFileSize
                ));

                return $this->_redirect($this->_redirect->getRefererUrl());
            }

            $destinationFilePath = $this->directoryList->getPath(DirectoryList::VAR_DIR)
                . DIRECTORY_SEPARATOR
                . $logFileName
                . '.zip';
            $this->archiveService->pack($sourceFilePath, $destinationFilePath);

            return $this->fileFactory->create(
                $logFileName . '.zip',
                [
                    'type' => 'filename',
                    'value' => $destinationFilePath,
                    'rm' => true,
                ],
                DirectoryList::ROOT,
                'application/zip'
            );
        } catch (LocalizedException $e) {
            $this->messageManager->addExceptionMessage($e);
            $this->logger->error($e->getMessage(), [
                'method' => __METHOD__,
                'originalException' => $e,
            ]);
        } catch (\Exception $e) {
            $this->messageManager->addErrorMessage(__(
                'A system error occurred while downloading the file. Please check logs for details.'
            ));
            $this->logger->error($e->getMessage(), [
                'method' => __METHOD__,
                'originalException' => $e,
            ]);
        }

        return $this->_redirect($this->_redirect->getRefererUrl());
    }
}
