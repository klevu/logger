<?php

namespace Klevu\Logger\Api;

use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Store\Api\Data\StoreInterface;

/**
 * Service to determine the applicable store for purposes of logging
 * Particularly applicable for admin activities, but also permits manually setting store
 */
interface StoreScopeResolverInterface
{
    /**
     * @return StoreInterface
     */
    public function getCurrentStore();

    /**
     * @param StoreInterface $store
     * @return void
     */
    public function setCurrentStore(StoreInterface $store);

    /**
     * @param int $storeId
     * @return void
     * @throws NoSuchEntityException
     */
    public function setCurrentStoreById($storeId);

    /**
     * @param string $storeCode
     * @return void
     * @throws NoSuchEntityException
     */
    public function setCurrentStoreByCode($storeCode);
}
