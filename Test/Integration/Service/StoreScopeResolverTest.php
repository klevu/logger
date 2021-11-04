<?php

namespace Klevu\Logger\Test\Integration\Service;

use Klevu\Logger\Service\StoreScopeResolver;
use Magento\Backend\App\Area\FrontNameResolver;
use Magento\Framework\App\AreaList;
use Magento\Framework\ObjectManagerInterface;
use Magento\Store\Model\Store;
use Magento\Store\Model\StoreManagerInterface;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\TestFramework\TestCase\AbstractController as AbstractControllerTestCase;

/**
 * Class StoreScopeResolverTest
 * @package Klevu\Logger\Test\Integration\Service
 */
class StoreScopeResolverTest  extends AbstractControllerTestCase
{
    /**
     * @var ObjectManagerInterface
     */
    private $objectManager;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * Tests resolving store code on frontend requests
     *
     * @magentoAppArea frontend
     * @magentoAppIsolation enabled
     * @magentoDbIsolation disabled
     * @magentoDataFixture loadStoreFixtures
     */
    public function testResolveCurrentStore_Frontend()
    {
        $this->setupPhp5();

        $this->storeManager->setCurrentStore('es_es');

        /** @var StoreScopeResolver $storeScopeResolver */
        $storeScopeResolver = $this->objectManager->get(StoreScopeResolver::class);

        $currentStore = $storeScopeResolver->getCurrentStore();

        $this->assertSame('es_es', $currentStore->getCode());
    }

    /**
     * Tests resolving store code in backend with no store scope request parameters
     *
     * @magentoAppArea adminhtml
     * @magentoCache all disabled
     * @magentoAppIsolation enabled
     * @magentoDbIsolation disabled
     * @magentoDataFixture loadStoreFixtures
     */
    public function testResolveCurrentStore_AdminhtmlNoRequestParam()
    {
        $this->setupPhp5();

        /** @var AreaList $areaList */
        $areaList = $this->objectManager->get(AreaList::class);
        $adminFrontName = $areaList->getFrontName('adminhtml');
        if (!$adminFrontName) {
            /** @var FrontNameResolver $backendFrontNameResolver */
            $backendFrontNameResolver = $this->objectManager->get(FrontNameResolver::class);
            $adminFrontName = $backendFrontNameResolver->getFrontName(true);
        }

        /** @var StoreScopeResolver $storeScopeResolver */
        $storeScopeResolver = $this->objectManager->get(StoreScopeResolver::class);

        $this->dispatch($adminFrontName . '/admin/system_config/edit/section/general');

        $currentStore = $storeScopeResolver->getCurrentStore();

        $this->assertSame(Store::ADMIN_CODE, $currentStore->getCode());
    }

    /**
     * Tests resolving store in backend when store scope has been changed
     *
     * @magentoAppArea adminhtml
     * @magentoCache all disabled
     * @magentoAppIsolation enabled
     * @magentoDbIsolation disabled
     * @magentoDataFixture loadStoreFixtures
     */
    public function testResolveCurrentStore_AdminhtmlWithStoreRequestParam()
    {
        $this->setupPhp5();

        /** @var AreaList $areaList */
        $areaList = $this->objectManager->get(AreaList::class);
        $adminFrontName = $areaList->getFrontName('adminhtml');
        if (!$adminFrontName) {
            /** @var FrontNameResolver $backendFrontNameResolver */
            $backendFrontNameResolver = $this->objectManager->get(FrontNameResolver::class);
            $adminFrontName = $backendFrontNameResolver->getFrontName(true);
        }

        /** @var StoreManagerInterface $storeManager */
        $storeManager = $this->objectManager->get(StoreManagerInterface::class);
        $fixtureStore = $storeManager->getStore('es_es');

        /** @var StoreScopeResolver $storeScopeResolver */
        $storeScopeResolver = $this->objectManager->get(StoreScopeResolver::class);

        $this->dispatch($adminFrontName . '/admin/system_config/edit/section/general/store/' . $fixtureStore->getId());

        $currentStore = $storeScopeResolver->getCurrentStore();

        $this->assertSame($fixtureStore->getCode(), $currentStore->getCode());
    }

    /**
     * Tests ability to override the resolved store with a manually set one
     *
     * @magentoAppArea frontend
     * @magentoAppIsolation enabled
     * @magentoDbIsolation disabled
     * @magentoDataFixture loadStoreFixtures
     * @depends testResolveCurrentStore_Frontend
     */
    public function testOverrideResolvedCurrentStore()
    {
        $this->setupPhp5();

        $this->storeManager->setCurrentStore('es_es');

        /** @var StoreScopeResolver $storeScopeResolver */
        $storeScopeResolver = $this->objectManager->get(StoreScopeResolver::class);
        $storeScopeResolver->setCurrentStore(
            $this->storeManager->getStore('default')
        );

        $currentStore = $storeScopeResolver->getCurrentStore();

        $this->assertSame('default', $currentStore->getCode());
    }

    /**
     * @return void
     * @todo Move to setUp when PHP 5.x is no longer supported
     */
    private function setupPhp5()
    {
        $this->objectManager = Bootstrap::getObjectManager();
        $this->storeManager = $this->objectManager->get(StoreManagerInterface::class);
    }

    /**
     * Loads store creation scripts because annotations use a relative path
     *  from integration tests root
     */
    public static function loadStoreFixtures()
    {
        include __DIR__ . '/../_files/storeFixtures.php';
    }
}
