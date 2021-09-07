<?php

namespace Klevu\Logger\Service;

use Klevu\Logger\Api\ConvertLogLevelServiceInterface;
use Klevu\Logger\Api\LoggingEnabledServiceInterface;
use Klevu\Logger\Api\LogLevelComparatorServiceInterface;
use Klevu\Logger\Validator\ArgumentValidationTrait;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Api\Data\StoreInterface;
use Magento\Store\Model\ScopeInterface;
use Magento\Store\Model\StoreManagerInterface;

/**
 * Checks whether logging should be performed given a combination of store id and log level
 */
class LoggingEnabledService implements LoggingEnabledServiceInterface
{
    use ArgumentValidationTrait;

    /**
     * @var ScopeConfigInterface
     */
    private $scopeConfig;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var LogLevelComparatorServiceInterface
     */
    private $logLevelComparatorService;

    /**
     * @var ConvertLogLevelServiceInterface
     */
    private $psrConvertLogLevelService;

    /**
     * @var ConvertLogLevelServiceInterface|null
     */
    private $configConvertLogLevelService;

    /**
     * @var string|null
     */
    private $isEnabledConfigPath;

    /**
     * @var string|null
     */
    private $minLogLevelConfigPath;

    /**
     * LoggingEnabledService constructor.
     * @param ScopeConfigInterface $scopeConfig
     * @param StoreManagerInterface $storeManager
     * @param LogLevelComparatorServiceInterface $logLevelComparatorService
     * @param ConvertLogLevelServiceInterface $psrConvertLogLevelService
     * @param ConvertLogLevelServiceInterface|null $configConvertLogLevelService
     * @param string|null $isEnabledConfigPath
     * @param string|null $minLogLevelConfigPath
     */
    public function __construct(
        ScopeConfigInterface $scopeConfig,
        StoreManagerInterface $storeManager,
        LogLevelComparatorServiceInterface $logLevelComparatorService,
        ConvertLogLevelServiceInterface $psrConvertLogLevelService,
        ConvertLogLevelServiceInterface $configConvertLogLevelService = null,
        $isEnabledConfigPath = null,
        $minLogLevelConfigPath = null
    ) {
        $this->validateStringArgument($isEnabledConfigPath, __METHOD__, 'isEnabledConfigPath', true);
        $this->validateStringArgument($minLogLevelConfigPath, __METHOD__, 'minLogLevelConfigPath', true);

        $this->scopeConfig = $scopeConfig;
        $this->storeManager = $storeManager;
        $this->logLevelComparatorService = $logLevelComparatorService;
        $this->psrConvertLogLevelService = $psrConvertLogLevelService;
        $this->configConvertLogLevelService = $configConvertLogLevelService;
        $this->isEnabledConfigPath = $isEnabledConfigPath;
        $this->minLogLevelConfigPath = $minLogLevelConfigPath;
    }

    /**
     * Checks configuration settings to determine whether logging is enabled
     *  for specified log level in store view.
     *
     * @param string $logLevel Psr log level to check
     * @param StoreInterface $store Store to check configuration. Will default to current store
     * @return bool Returns true if logging enabled for passed log level or lower
     *              (eg CRITICAL will return true for ERROR)
     * @see Psr\Log\LogLevel
     */
    public function isLoggingEnabledForLevelInStore($logLevel, StoreInterface $store)
    {
        $this->validateStringArgument($logLevel, __METHOD__, 'logLevel', false);

        return $this->isLoggingEnabledInStore($store)
            && $this->isLogLevelEnabledForStore($logLevel, $store);
    }

    /**
     * @param StoreInterface $store Store ID to check configuration. Will default to current store
     * @return bool Returns true if module's logging is enabled in Stores > Configuration
     */
    private function isLoggingEnabledInStore(StoreInterface $store)
    {
        if (!$this->isEnabledConfigPath) {
            return true;
        }

        return $this->scopeConfig->isSetFlag(
            $this->isEnabledConfigPath,
            $store->getId() ? ScopeInterface::SCOPE_STORES : ScopeConfigInterface::SCOPE_TYPE_DEFAULT,
            $store->getId()
        );
    }

    /**
     * @param string $logLevel Psr Log level to check
     * @param StoreInterface $store Store ID to check configuration. Will default to current store
     * @return bool Returns true if logging enabled for passed log level or lower in Stores > Configuration
     *              (eg CRITICAL will return true for ERROR)
     * @see Psr\Log\LogLevel
     */
    private function isLogLevelEnabledForStore($logLevel, StoreInterface $store)
    {
        $this->validateStringArgument($logLevel, __METHOD__, 'logLevel', false);

        if (!$this->minLogLevelConfigPath) {
            return true;
        }

        $minLogLevel = (string)$this->scopeConfig->getValue(
            $this->minLogLevelConfigPath,
            $store->getId() ? ScopeInterface::SCOPE_STORES : ScopeConfigInterface::SCOPE_TYPE_DEFAULT,
            $store->getId()
        );
        if ('' === $minLogLevel) {
            return true;
        }

        if ($this->configConvertLogLevelService) {
            $minLogLevel = $this->configConvertLogLevelService->toNumeric($minLogLevel);
        }

        return $this->logLevelComparatorService->compare(
            $this->psrConvertLogLevelService->toNumeric($logLevel),
            (int)$minLogLevel
        ) >= 0;
    }
}
