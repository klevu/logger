<?php

namespace Klevu\Logger\Validator;

use Magento\Framework\Filesystem\Io\File as FileIo;
use Magento\Framework\Validator\AbstractValidator;

/**
 * Validates that a file name is valid for use as a log destination
 */
class LogLocationFileNameValidator extends AbstractValidator
{
    /**
     * Lowercase array of extensions permitted in file names
     * @const string[]
     */
    const ALLOWED_EXTENSIONS = [
        'log',
    ];

    /**
     * Regular expression used to validate filename (does not include extension)
     * @const string
     */
    const FILENAME_VALIDATION_REGEX = '/^[a-zA-Z0-9_\-][a-zA-Z0-9_\-\.]*$/';

    /**
     * @var FileIo
     */
    private $fileIo;

    /**
     * LogLocationFileNameValidator constructor.
     * @param FileIo $fileIo
     */
    public function __construct(
        FileIo $fileIo
    ) {
        $this->fileIo = $fileIo;
    }

    /**
     * {@inheritdoc}
     *
     * @param mixed $value
     * @return bool
     */
    public function isValid($value)
    {
        return $this->validateType($value)
            && $this->validateNotEmpty($value)
            && $this->validateDoesNotContainIllegalCharacters($value)
            && $this->validateExtension($value)
            && $this->validateDoesNotContainDirectory($value);
    }

    /**
     * Validates value is of correct type (string or null)
     *
     * @param mixed $value
     * @return bool
     */
    private function validateType($value)
    {
        if (null !== $value && !is_string($value)) {
            $this->_addMessages([__(
                'File Name value must be string. Received "%1".',
                // phpcs:ignore Magento2.Functions.DiscouragedFunction.Discouraged
                gettype($value)
            )]);

            return false;
        }

        return true;
    }

    /**
     * Checks that the value is not empty and is not just a "dot file" (eg .log)
     *
     * @param mixed $value
     * @return bool
     */
    private function validateNotEmpty($value)
    {
        $value = trim((string)$value);

        if (!$value) {
            $this->_addMessages([__('File Name value cannot be empty')]);

            return false;
        }

        $pathinfo = $this->fileIo->getPathInfo($value);
        if (!isset($pathinfo['filename']) || !$pathinfo['filename']) {
            $this->_addMessages([__(
                'File Name value must contain both filename and extension. Received "%1".',
                $value
            )]);

            return false;
        }

        return true;
    }

    /**
     * Validates only whitelisted characters are present in filename
     *
     * @param mixed $value
     * @return bool
     */
    private function validateDoesNotContainIllegalCharacters($value)
    {
        $value = trim((string)$value);
        if (!$value) {
            return true;
        }

        $pathinfo = $this->fileIo->getPathInfo($value);
        if (!isset($pathinfo['filename']) || !$pathinfo['filename']) {
            return true;
        }

        if (!preg_match(static::FILENAME_VALIDATION_REGEX, $value)) {
            $this->_addMessages([__(
                'File Name value contains illegal characters. Received "%1". Please ensure filename contains only alphanumeric, underscore, dash, or period characters', // phpcs:ignore Generic.Files.LineLength.TooLong
                $value
            )]);

            return false;
        }

        return true;
    }

    /**
     * Validates that the file name has an extension, and that the extension is permitted
     *
     * @param mixed $value
     * @return bool
     */
    private function validateExtension($value)
    {
        $value = trim((string)$value);
        if (!$value) {
            return true;
        }

        $pathinfo = $this->fileIo->getPathInfo($value);
        if (!isset($pathinfo['extension']) || !trim($pathinfo['extension'])) {
            $this->_addMessages([__('File Name value must contain extension. Received "%1".')]);

            return false;
        }

        if (!in_array(strtolower($pathinfo['extension']), static::ALLOWED_EXTENSIONS, true)) {
            $this->_addMessages([__(
                'File Name extension is not a permitted value. Received "%1"; expected one of "%2".',
                $pathinfo['extension'],
                implode(',', static::ALLOWED_EXTENSIONS)
            )]);

            return false;
        }

        return true;
    }

    /**
     * Validates that the file name is not a nested path (eg Klevu/Search.log)
     *
     * @param mixed $value
     * @return bool
     */
    private function validateDoesNotContainDirectory($value)
    {
        $value = trim((string)$value);
        if (!$value) {
            return true;
        }

        $pathinfo = $this->fileIo->getPathInfo($value);
        if (isset($pathinfo['dirname']) && trim($pathinfo['dirname']) && '.' !== $pathinfo['dirname']) {
            $this->_addMessages([__(
                'File Name cannot contain a parent directory. Received "%1", with directory "%2".',
                $value,
                $pathinfo['dirname']
            )]);

            return false;
        }

        return true;
    }
}
