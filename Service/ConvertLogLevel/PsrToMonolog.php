<?php

namespace Klevu\Logger\Service\ConvertLogLevel;

use Klevu\Logger\Api\ConvertLogLevelServiceInterface;
use Klevu\Logger\Validator\ArgumentValidationTrait;
use Monolog\Logger;
use Psr\Log\LogLevel;

/**
 * Converts log levels between Psr format (string) and Monolog (int)
 */
class PsrToMonolog implements ConvertLogLevelServiceInterface
{
    use ArgumentValidationTrait;

    /**
     * @var int[]
     */
    private $psrLevelToMonolog = [
        LogLevel::EMERGENCY => Logger::EMERGENCY,
        LogLevel::ALERT => Logger::ALERT,
        LogLevel::CRITICAL => Logger::CRITICAL,
        LogLevel::ERROR => Logger::ERROR,
        LogLevel::WARNING => Logger::WARNING,
        LogLevel::NOTICE => Logger::NOTICE,
        LogLevel::INFO => Logger::INFO,
        LogLevel::DEBUG => Logger::DEBUG,
    ];

    /**
     * @var string[]
     */
    private $monologLevelToPsr;

    /**
     * @param string $logLevel
     * @return int|null
     */
    public function toNumeric($logLevel)
    {
        $this->validateStringArgument($logLevel, __METHOD__, 'toNumeric', false);

        return isset($this->psrLevelToMonolog[$logLevel])
            ? $this->psrLevelToMonolog[$logLevel]
            : null;
    }

    /**
     * @param int $logLevel
     * @return string|null
     */
    public function fromNumeric($logLevel)
    {
        $this->validateIntArgument($logLevel, __METHOD__, 'fromNumeric', false);

        if (null === $this->monologLevelToPsr) {
            $this->monologLevelToPsr = array_flip($this->psrLevelToMonolog);
        }

        return isset($this->monologLevelToPsr[$logLevel])
            ? $this->monologLevelToPsr[$logLevel]
            : null;
    }
}
