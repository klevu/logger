<?php

namespace Klevu\Logger\Api;

use Magento\Store\Api\Data\StoreInterface;

interface LogFileNameProviderInterface
{
    const DEFAULT_BASE_FILENAME = 'Klevu.log';

    /**
     * Returns the file name to which logs will be written
     *
     * @param StoreInterface $store Store for which to retrieve log file path
     * @return string File name to which logs should be written
     */
    public function execute(StoreInterface $store);
}
