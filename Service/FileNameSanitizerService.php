<?php

namespace Klevu\Logger\Service;

use Klevu\Logger\Api\FileNameSanitizerServiceInterface;
use Klevu\Logger\Validator\ArgumentValidationTrait;

/**
 * Abstracts functionality from Base Logger Handler class' private into callable service
 */
class FileNameSanitizerService implements FileNameSanitizerServiceInterface
{
    use ArgumentValidationTrait;

    /**
     * Sanitizes file name, including directory traversal
     *
     * @param string $fileName
     * @return string
     * @see \Magento\Framework\Logger\Handler\Base::sanitizeFileName
     */
    public function execute($fileName)
    {
        $this->validateStringArgument($fileName, __METHOD__, 'fileName', false);

        $parts = explode('/', $fileName);
        $parts = array_filter($parts, function ($value) {
            return !in_array($value, ['', '.', '..']);
        });

        return implode('/', $parts);
    }
}
