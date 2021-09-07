<?php

namespace Klevu\Logger\Service;

use Klevu\Logger\Api\LogFileNameProviderInterface;
use Klevu\Logger\Validator\ArgumentValidationTrait;
use Magento\Framework\Exception\ValidatorException;
use Magento\Framework\Filesystem\Io\File as FileIo;
use Magento\Framework\Validator\ValidatorInterface;
use Magento\Store\Api\Data\StoreInterface;
use Psr\Log\LoggerInterface;

/**
 * Provides the file name to which logs will be written, with support for store scope
 */
class LogFileNameProvider implements LogFileNameProviderInterface
{
    use ArgumentValidationTrait;

    const FILENAME_PART_SEPARATOR = '.';

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var FileIo
     */
    private $fileIo;

    /**
     * @var ValidatorInterface
     */
    private $fileNameValidator;

    /**
     * @var string
     */
    private $baseFileName = self::DEFAULT_BASE_FILENAME;

    /**
     * LogFileNameProvider constructor.
     * @param LoggerInterface $logger
     * @param FileIo $fileIo
     * @param ValidatorInterface $fileNameValidator
     * @param null $baseFileName
     */
    public function __construct(
        LoggerInterface $logger,
        FileIo $fileIo,
        ValidatorInterface $fileNameValidator,
        $baseFileName = null
    ) {
        $this->validateStringArgument($baseFileName, __METHOD__, 'baseFileName', true);

        $this->logger = $logger;
        $this->fileIo = $fileIo;
        $this->fileNameValidator = $fileNameValidator;
        if ($baseFileName) {
            $this->baseFileName = $baseFileName;
        }
    }

    /**
     * Returns the fileName to which logs will be written
     *
     * @param StoreInterface $store Store for which to retrieve log file path
     * @return string File name to which logs should be written
     * @throws ValidatorException
     */
    public function execute(StoreInterface $store)
    {
        $fileName = $this->baseFileName;
        if ($store->getId()) {
            $pathinfo = $this->fileIo->getPathInfo($fileName);

            $fileName = isset($pathinfo['filename']) ? $pathinfo['filename'] : '';
            $fileName .= static::FILENAME_PART_SEPARATOR . $store->getCode();

            if (isset($pathinfo['extension'])) {
                $fileName .= '.' . $pathinfo['extension'];
            }
        }

        try {
            if (!$this->fileNameValidator->isValid($fileName)) {
                throw new ValidatorException(__(implode('; ', $this->fileNameValidator->getMessages())));
            }
        } catch (\Zend_Validate_Exception $e) {
            throw new ValidatorException(__($e->getMessage()));
        }

        return $fileName;
    }
}
