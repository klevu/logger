<?php

namespace Klevu\Logger\Service;

use Klevu\Logger\Api\StoreScopeResolverInterface;
use Klevu\Logger\Constants;
use Klevu\Logger\Validator\ArgumentValidationTrait;
use Magento\Framework\App\Area;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\App\State as AppState;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Store\Api\Data\StoreInterface;
use Magento\Store\Model\Store;
use Magento\Store\Model\StoreManagerInterface;
use Psr\Log\LoggerInterface;

class StoreScopeResolver implements StoreScopeResolverInterface
{
    use ArgumentValidationTrait;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var AppState
     */
    private $appState;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var RequestInterface
     */
    private $request;

    /**
     * @var StoreInterface
     */
    private $currentStore;

    /**
     * StoreScopeResolver constructor.
     * @param LoggerInterface $logger
     * @param AppState $appState
     * @param StoreManagerInterface $storeManager
     * @param RequestInterface|null $request
     */
    public function __construct(
        LoggerInterface $logger,
        AppState $appState,
        StoreManagerInterface $storeManager,
        RequestInterface $request
    ) {
        $this->logger = $logger;
        $this->appState = $appState;
        $this->storeManager = $storeManager;
        $this->request = $request;
    }

    /**
     * @return StoreInterface
     */
    public function getCurrentStore()
    {
        if (null === $this->currentStore) {
            $this->currentStore = $this->resolveCurrentStore();
        }

        return $this->currentStore;
    }

    /**
     * @return StoreInterface
     */
    private function resolveCurrentStore()
    {
        $areaCode = Area::AREA_FRONTEND;
        try {
            $areaCode = $this->appState->getAreaCode();
        } catch (LocalizedException $e) {
            $this->logger->error($e->getMessage(), ['originalException' => $e]);
        }

        $store = null;
        switch ($areaCode) {
            case Area::AREA_ADMINHTML:
                $store = $this->resolveCurrentAdminhtmlStore();
                break;

            case Area::AREA_CRONTAB:
            case Area::AREA_GLOBAL:
                break;

            default:
                try {
                    $store = $this->storeManager->getStore();
                } catch (NoSuchEntityException $e) {
                    $this->logger->error($e->getMessage(), ['originalException' => $e]);
                }
                break;
        }

        if (null === $store) {
            try {
                $store = $this->storeManager->getStore(Store::ADMIN_CODE);
            } catch (NoSuchEntityException $e) {
                $this->logger->error($e->getMessage(), ['originalException' => $e]);
            }
        }

        return $store;
    }

    /**
     * @return StoreInterface|null
     */
    private function resolveCurrentAdminhtmlStore()
    {
        $return = null;
        if (!$this->request) {
            return $return;
        }

        $paramKeys = array_unique([
            'store',
            Constants::ADMIN_STORE_ID_PARAM,
        ]);
        $params = $this->request->getParams();
        $storeId = 0;
        foreach ($paramKeys as $paramKey) {
            if (isset($params[$paramKey])) {
                $storeId = (int)$params[$paramKey];
                break;
            }
        }

        try {
            $return = $this->storeManager->getStore($storeId);
        } catch (NoSuchEntityException $e) {
            $this->logger->error($e->getMessage(), ['originalException' => $e]);
        }

        return $return;
    }

    /**
     * @param StoreInterface $store
     */
    public function setCurrentStore(StoreInterface $store)
    {
        $this->currentStore = $store;
    }

    /**
     * @param int $storeId
     * @throws NoSuchEntityException
     */
    public function setCurrentStoreById($storeId)
    {
        $this->validateIntArgument($storeId, __METHOD__, 'storeId', false);

        $this->setCurrentStore($this->storeManager->getStore($storeId));
    }

    /**
     * @param string $storeCode
     * @throws NoSuchEntityException
     */
    public function setCurrentStoreByCode($storeCode)
    {
        $this->validateStringArgument($storeCode, __METHOD__, 'storeCode', false);

        $this->setCurrentStore($this->storeManager->getStore($storeCode));
    }
}
