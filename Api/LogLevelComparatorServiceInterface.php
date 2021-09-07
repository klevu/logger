<?php

namespace Klevu\Logger\Api;

interface LogLevelComparatorServiceInterface
{
    /**
     * Compares numeric log levels in a format usable by sort
     *
     * Priority is determined by the concrete implementation
     * For example
     *  Zend Logger runs 0 (EMERG) -> 7 (DEBUG)
     *  Monolog runs 600 (EMERGENCY) -> 100 (DEBUG)
     *
     * @param int $logLevel
     * @param int $minLogLevel
     * @return int Returns
     *              -1 if $logLevel has lower priority than $minLogLevel
     *               0 if $logLevel and $minLogLevel are equal priority
     *               1 if $logLevel has higher priority than $minLogLevel
     */
    public function compare($logLevel, $minLogLevel);
}
