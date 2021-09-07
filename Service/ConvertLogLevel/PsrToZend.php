<?php

namespace Klevu\Logger\Service\ConvertLogLevel;

use Klevu\Logger\Api\ConvertLogLevelServiceInterface;
use Klevu\Logger\Constants;
use Klevu\Logger\Validator\ArgumentValidationTrait;
use Psr\Log\LogLevel;

/**
 * Converts log levels between Psr format (string) and Zend (int)
 */
class PsrToZend implements ConvertLogLevelServiceInterface
{
    use ArgumentValidationTrait;

    /**
     * @var int[]
     */
    private $psrLevelToZend = [
        LogLevel::EMERGENCY => Constants::ZEND_LOG_EMERG,
        LogLevel::ALERT => Constants::ZEND_LOG_ALERT,
        LogLevel::CRITICAL => Constants::ZEND_LOG_CRIT,
        LogLevel::ERROR => Constants::ZEND_LOG_ERR,
        LogLevel::WARNING => Constants::ZEND_LOG_WARN,
        LogLevel::NOTICE => Constants::ZEND_LOG_NOTICE,
        LogLevel::INFO => Constants::ZEND_LOG_INFO,
        LogLevel::DEBUG => Constants::ZEND_LOG_DEBUG,
    ];

    /**
     * @var string[]
     */
    private $zendLevelToPsr;

    /**
     * @param string $logLevel
     * @return int|null
     */
    public function toNumeric($logLevel)
    {
        $this->validateStringArgument($logLevel, __METHOD__, 'logLevel', false);

        return isset($this->psrLevelToZend[$logLevel])
            ? $this->psrLevelToZend[$logLevel]
            : null;
    }

    /**
     * @param int $logLevel
     * @return string|null
     */
    public function fromNumeric($logLevel)
    {
        $this->validateIntArgument($logLevel, __METHOD__, 'logLevel', false);

        if (null === $this->zendLevelToPsr) {
            $this->zendLevelToPsr = array_flip($this->psrLevelToZend);
        }

        return isset($this->zendLevelToPsr[$logLevel])
            ? $this->zendLevelToPsr[$logLevel]
            : null;
    }
}
