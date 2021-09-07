<?php

namespace Klevu\Logger\Api;

interface FileNameSanitizerServiceInterface
{
    /**
     * Sanitizes file name, including directory traversal
     *
     * @param string $fileName
     * @return string
     */
    public function execute($fileName);
}
