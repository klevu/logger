<?php

namespace Klevu\Logger\Service;

use Klevu\Logger\Api\LogLevelComparatorServiceInterface;
use Klevu\Logger\Validator\ArgumentValidationTrait;

/**
 * Service to compare numeric log levels to determine whether a passed value is
 *  of a higher or equal priority.
 * Takes into account both high -> low ordering (eg Monolog) and low -> high (eg Zend/Laminas)
 */
class LogLevelComparatorService implements LogLevelComparatorServiceInterface
{
    use ArgumentValidationTrait;

    /**
     * @var bool
     */
    private $lowValueIsHighPriority;

    /**
     * LogLevelComparatorService constructor.
     * @param bool $lowValueIsHighPriority
     */
    public function __construct(
        $lowValueIsHighPriority
    ) {
        $this->validateBoolArgument($lowValueIsHighPriority, __METHOD__, 'lowValueIsHighPriority', false);
        $this->lowValueIsHighPriority = $lowValueIsHighPriority;
    }

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
    public function compare($logLevel, $minLogLevel)
    {
        $this->validateIntArgument($logLevel, __METHOD__, 'logLevel', false);
        $this->validateIntArgument($logLevel, __METHOD__, 'minLogLevel', false);

        switch (true) {
            // Levels equal
            case $logLevel === $minLogLevel:
                $return = 0;
                break;

            // $logLevel is higher priority
            case $this->lowValueIsHighPriority && $logLevel < $minLogLevel:
            case !$this->lowValueIsHighPriority && $logLevel > $minLogLevel:
                $return = 1;
                break;

            // $logLevel is lower priority
            case $this->lowValueIsHighPriority && $logLevel > $minLogLevel:
            case !$this->lowValueIsHighPriority && $logLevel < $minLogLevel:
            default:
                $return = -1;
                break;
        }

        return $return;
    }
}
