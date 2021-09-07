<?php

namespace Klevu\Logger\Service\ConvertLogLevel;

use Klevu\Logger\Api\ConvertLogLevelServiceInterface;
use Klevu\Logger\Constants;
use Klevu\Logger\Validator\ArgumentValidationTrait;
use Monolog\Logger as MonologLogger;

/**
 * Converts log levels between Zend format (must be cast to string) and Monolog (int)
 */
class ZendToMonolog implements ConvertLogLevelServiceInterface
{
    use ArgumentValidationTrait;

    /**
     * @var int[]
     */
    private $zendLevelToMonolog = [
        Constants::ZEND_LOG_EMERG => MonologLogger::EMERGENCY,
        Constants::ZEND_LOG_ALERT => MonologLogger::ALERT,
        Constants::ZEND_LOG_CRIT => MonologLogger::CRITICAL,
        Constants::ZEND_LOG_ERR => MonologLogger::ERROR,
        Constants::ZEND_LOG_WARN => MonologLogger::WARNING,
        Constants::ZEND_LOG_NOTICE => MonologLogger::NOTICE,
        Constants::ZEND_LOG_INFO => MonologLogger::INFO,
        Constants::ZEND_LOG_DEBUG => MonologLogger::DEBUG,
    ];

    /**
     * @var string[]
     */
    private $monologLevelToZend;

    /**
     * @param string $logLevel
     * @return int|null
     * @note Zend log levels are integers, but must be cast to string for conversion
     */
    public function toNumeric($logLevel)
    {
        $this->validateStringArgument($logLevel, __METHOD__, 'toNumeric', false);

        return isset($this->zendLevelToMonolog[$logLevel])
            ? $this->zendLevelToMonolog[$logLevel]
            : null;
    }

    /**
     * @param int $logLevel
     * @return string|null
     */
    public function fromNumeric($logLevel)
    {
        $this->validateIntArgument($logLevel, __METHOD__, 'fromNumeric', false);

        if (null === $this->monologLevelToZend) {
            $this->monologLevelToZend = array_flip($this->zendLevelToMonolog);
        }

        return isset($this->monologLevelToZend[$logLevel])
            ? (string)$this->monologLevelToZend[$logLevel]
            : null;
    }
}
