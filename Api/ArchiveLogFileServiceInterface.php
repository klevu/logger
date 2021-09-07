<?php

namespace Klevu\Logger\Api;

use Klevu\Logger\Exception\ArchiveLogFileException;

/**
 * Service to archive files. Utilised by Stores > Configuration when clearing log files
 */
interface ArchiveLogFileServiceInterface
{
    /**
     * Archives the specified file.
     * Concrete implementations determine the procedure for archival
     *
     * @param string $filePath Absolute path, or relative from installation root, of file to be archived
     * @return string Archive file location
     * @throws ArchiveLogFileException
     */
    public function execute($filePath);
}
