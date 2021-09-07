<?php

namespace Klevu\Logger\Api;

use Magento\Store\Api\Data\StoreInterface;

/**
 * Service to test whether logging output is enabled for given parameters
 */
interface LoggingEnabledServiceInterface
{
    /**
     * Checks configuration settings to determine whether logging is enabled
     *  for specified log level in store view.
     *
     * @param string $logLevel Psr log level to check
     * @param StoreInterface $storeId Store to check configuration. Will default to current store
     * @return bool Returns true if logging enabled for passed log level or lower
     *              (eg CRITICAL will return true for ERROR)
     * @see Psr\Log\LogLevel
     */
    public function isLoggingEnabledForLevelInStore($logLevel, StoreInterface $store);
}
