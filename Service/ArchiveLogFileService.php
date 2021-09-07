<?php

namespace Klevu\Logger\Service;

use Klevu\Logger\Api\ArchiveLogFileServiceInterface;
use Klevu\Logger\Exception\ArchiveLogFileException;
use Klevu\Logger\Validator\ArgumentValidationTrait;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Filesystem\Io\File as FileIo;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;

/**
 * File archiver service, inserting current timestamp between filename and extension
 * Saves to same directory as original file
 */
class ArchiveLogFileService implements ArchiveLogFileServiceInterface
{
    use ArgumentValidationTrait;

    /**
     * @var FileIo
     */
    private $fileIo;

    /**
     * @var DirectoryList
     */
    private $directoryList;

    /**
     * @var TimezoneInterface
     */
    private $timezone;

    /**
     * @var array
     */
    private $permittedArchiveLocations = [];

    /**
     * ArchiveLogFileService constructor.
     * @param FileIo $fileIo
     * @param DirectoryList $directoryList
     * @param TimezoneInterface $timezone
     * @param string[]|null $permittedArchiveLocations
     */
    public function __construct(
        FileIo $fileIo,
        DirectoryList $directoryList,
        TimezoneInterface $timezone,
        array $permittedArchiveLocations = null
    ) {
        $this->fileIo = $fileIo;
        $this->directoryList = $directoryList;
        $this->timezone = $timezone;
        if (null !== $permittedArchiveLocations) {
            array_walk($permittedArchiveLocations, [$this, 'addPermittedArchiveLocation']);
        }
    }

    /**
     * @param string $permittedArchiveLocation
     * @return void
     * @throws \InvalidArgumentException
     */
    private function addPermittedArchiveLocation($permittedArchiveLocation)
    {
        $this->validateStringArgument($permittedArchiveLocation, __METHOD__, 'permittedArchiveLocation', false);

        $permittedArchiveLocation = trim($permittedArchiveLocation);
        if ($permittedArchiveLocation[0] !== DIRECTORY_SEPARATOR) {
            $permittedArchiveLocation = $this->directoryList->getRoot()
                . DIRECTORY_SEPARATOR
                . $permittedArchiveLocation;
        }

        if (!in_array($permittedArchiveLocation, $this->permittedArchiveLocations, true)) {
            $this->permittedArchiveLocations[] = $permittedArchiveLocation;
        }
    }

    /**
     * @param string $filePath
     * @return string Archive file location
     * @throws ArchiveLogFileException
     */
    public function execute($filePath)
    {
        $this->validateStringArgument($filePath, __METHOD__, 'file', false);

        $archiveLogFileException = new ArchiveLogFileException(__(
            'File "%1" could not be archived. See errors for further details.',
            $filePath
        ));
        try {
            if (!$this->isPermittedLocation($filePath)) {
                $archiveLogFileException->addError(__(
                    'File Path %1 is not permitted to be modified by the file archival service',
                    $filePath
                ));
            }
        } catch (\InvalidArgumentException $e) {
            $archiveLogFileException->addError(__($e->getMessage()));
        }

        $archiveFilePath = '';
        try {
            $archiveFilePath = $this->getArchiveFilePath($filePath);

            if ($archiveFilePath === $filePath) {
                $archiveLogFileException->addError(__(
                    'Archive location for file "%1" is the same as its current location. Cannot archive.',
                    $filePath
                ));
            }
        } catch (LocalizedException $e) {
            $archiveLogFileException->addError(__($e->getMessage()));
        } catch (\InvalidArgumentException $e) {
            $archiveLogFileException->addError(__($e->getMessage()));
        }

        if ($archiveLogFileException->wasErrorAdded()) {
            throw $archiveLogFileException;
        }

        try {
            if (!$this->fileIo->fileExists($filePath)) {
                throw new LocalizedException(__('File "%1" could not be found', $filePath));
            }

            $this->fileIo->checkAndCreateFolder($this->fileIo->dirname($archiveFilePath), 0755);

            if (!$this->fileIo->mv($filePath, $archiveFilePath)) {
                $archiveLogFileException->addError(__(
                    'Could not move file "%1" to archive location "%2".',
                    $filePath,
                    $archiveFilePath
                ));
            }
        } catch (LocalizedException $e) {
            $archiveLogFileException->addError(__($e->getMessage()));
        }

        if ($archiveLogFileException->wasErrorAdded()) {
            throw $archiveLogFileException;
        }

        return $archiveFilePath;
    }

    /**
     * @param $filePath
     * @return bool
     * @throws \InvalidArgumentException
     */
    public function isPermittedLocation($filePath)
    {
        $this->validateStringArgument($filePath, __METHOD__, 'originalFilePath', false);

        if (!$this->permittedArchiveLocations) {
            return true;
        }

        $return = false;
        foreach ($this->permittedArchiveLocations as $permittedArchiveLocation) {
            if ($this->fileIo->allowedPath($filePath, $permittedArchiveLocation)) {
                $return = true;
                break;
            }
        }

        return $return;
    }

    /**
     * @param $originalFilePath
     * @return string
     * @throws \InvalidArgumentException
     * @throws LocalizedException
     */
    public function getArchiveFilePath($originalFilePath)
    {
        $this->validateStringArgument($originalFilePath, __METHOD__, 'originalFilePath', false);

        $pathinfo = $this->fileIo->getPathInfo($originalFilePath);

        $directory = trim(isset($pathinfo['dirname']) ? $pathinfo['dirname'] : $this->directoryList->getRoot());

        $filename = trim(isset($pathinfo['filename']) ? $pathinfo['filename'] : '');
        if (!$filename) {
            throw new LocalizedException(__(
                'Cannot generate archive filepath for "%1". Location must have a filename',
                $originalFilePath
            ));
        }

        $extension = trim(isset($pathinfo['extension']) ? $pathinfo['extension'] : '');

        return $directory
            . DIRECTORY_SEPARATOR
            . $filename
            . '.'
            . $this->timezone->scopeTimeStamp()
            . '.'
            . $extension;
    }
}
