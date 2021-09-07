<?php

use Magento\TestFramework\Helper\Bootstrap;

$fixtures = [
    'default' => 'Default Store View',
    'es_es' => 'Spanish Store View',
];

$objectManager = Bootstrap::getObjectManager();
/** @var \Magento\Store\Model\StoreManagerInterface $storeManager */
$storeManager = $objectManager->get(\Magento\Store\Model\StoreManagerInterface::class);

$website = $storeManager->getWebsite();

$sortOrder = 0;
foreach ($fixtures as $storeCode => $storeName) {
    $store = $objectManager->create(\Magento\Store\Model\Store::class);
    if ($store->load($storeCode)->getId()) {
        continue;
    }

    $sortOrder += 10;

    $store->setCode($storeCode);
    $store->setWebsiteId($website->getId());
    $store->setGroupId($website->getDefaultGroupId());
    $store->setName($storeName);
    $store->setSortOrder($sortOrder);
    $store->setIsActive(1);
    $store->save();
}

