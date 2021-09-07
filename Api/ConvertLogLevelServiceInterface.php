<?php

namespace Klevu\Logger\Api;

/**
 * Service to convert between to and from numeric log levels
 * @see Monolog\Logger
 * @see Psr\Log\LogLevel
 */
interface ConvertLogLevelServiceInterface
{
    /**
     * @param string $logLevel
     * @return int|null
     */
    public function toNumeric($logLevel);

    /**
     * @param int $logLevel
     * @return string|null
     */
    public function fromNumeric($logLevel);
}
