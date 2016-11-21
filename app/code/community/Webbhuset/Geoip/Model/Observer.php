<?php
/**
 * Webbhsuet Geoip Observer
 *
 * @category    Webbhuset
 * @package     Webbhuset_Geoip
 * @author      Webbhuset <info@webbhsuet.se>
 */
class Webbhuset_Geoip_Model_Observer
{
    /**
     * Redirect to allowed store with Geoip
     *
     * @param Varien_Object $observer
     *
     * @return void
     */
    public function redirectStore(Varien_Event_Observer $observer)
    {
        $enabled        = Mage::getStoreConfigFlag('geoip/general/enabled');
        $lockStore      = Mage::getStoreConfigFlag('geoip/general/lock');
        $exceptions     = $this->_getExceptions();
        $fallbackStore  = $this->_getFallbackStore();

        if (!$enabled) {
            return;
        }

        $this->checkNoRoute();

        $geoIP          = Mage::getSingleton('geoip/country');
        $currentCountry = $geoIP->getCountry();
        $response       = Mage::app()->getResponse();
        $session        = Mage::getSingleton('core/session');
        $result         = new Varien_Object(array('should_proceed' => 1));


        Mage::dispatchEvent('wh_geoip_redirect_store_before', array('result' => $result));

        if (
            isset($_SERVER['HTTP_USER_AGENT']) && preg_match('/bot|crawl|slurp|spider/i', $_SERVER['HTTP_USER_AGENT'])
        ) {
            $result->setShouldProceed(false);
        }

        if (!$result->getShouldProceed()) {
            return;
        }

        if ($this->_validateException($exceptions)) {
            return;
        }

        if ($geoIP->isCountryAllowed($currentCountry)) {
            $session->setIsGeoipRedirected(true);
            return;
        }

        $result = new Varien_Object(array('locked_store' => $lockStore));
        Mage::dispatchEvent('wh_geoip_redirect_store_check_locked_before', array('result' => $result));

        // Only redirect once per session if lock is not enabled
        if (!$result->getLockedStore() && $session->getIsGeoipRedirected()) {
            return;
        }

        // If locked mode is on and country is not in allowed countries: Don't
        // redirect when we are on fallback store.
        if ($result->getLockedStore()
            && $session->getNotInAllowedList()
            && Mage::app()->getStore()->getId() == $fallbackStore->getId()
        ) {
            return;
        }

        $store = $this->_getStoreForCountry($currentCountry);

        if (!$store) {
            $store = $fallbackStore;
            $session->setNotInAllowedList(true);
        }

        $event = new Varien_Object(
            array(
                'store_url'         => $store->getCurrentUrl(false),
            )
        );

        Mage::dispatchEvent('wh_geoip_redirect_store_set_redirect_before', array('result' => $event));

        $session->setIsGeoipRedirected(true);

        $response->setRedirect($event->getStoreUrl())->sendResponse();

        exit;
    }

    /**
     * Check if store code is in url on 404 page if this setting is enabled in admin.
     * Will redirect if store code is not.
     *
     * @param Varien_Event_Observer $observer
     * @access public
     * @return void
     */
    public function checkNoRoute()
    {
        $enabled = Mage::getStoreConfigFlag('geoip/general/enabled');

        if (!$enabled) {
            return;
        }

        if (!$this->_canBeStoreCodeInUrl()) {
            return;
        }

        $noRoute        = explode('/', Mage::app()->getStore()->getConfig('web/default/no_route'));
        $moduleName     = isset($noRoute[0]) ? $noRoute[0] : 'core';
        $controllerName = isset($noRoute[1]) ? $noRoute[1] : 'index';
        $actionName     = isset($noRoute[2]) ? $noRoute[2] : 'index';
        $request        = Mage::app()->getRequest();
        $requestUri     = trim($request->getRequestUri(), '/');
        $urlStoreCode   = explode('/', $requestUri, 2);
        $urlStoreCode   = isset($urlStoreCode[0]) ? $urlStoreCode[0] : '';
        $storeInUrl     = true;

        try {
            Mage::app()->getStore($urlStoreCode);
        } catch (Mage_Core_Model_Store_Exception $e) {
            $storeInUrl = false;
        }

        if ($storeInUrl) {
            return;
        }

        if ($moduleName        == $request->getModuleName()
            && $controllerName == $request->getControllerName()
            && $actionName     == $request->getActionName()
        ) {
            $geoIP          = Mage::getSingleton('geoip/country');
            $currentCountry = $geoIP->getCountry();
            $response       = Mage::app()->getResponse();

            if ($currentCountry) {
                $store = $this->_getStoreForCountry($currentCountry);
            } else {
                $store = $this->_getFallbackStore();
            }

            $response->setRedirect($store->getCurrentUrl(false))->sendResponse();
            exit;
        }
    }

    /**
     * Get configured fallback store
     *
     * @return Mage_Core_Model_Store
     */
    protected function _getFallbackStore()
    {
        $fallbackStore = Mage::getStoreConfig('geoip/general/fallback');

        return Mage::app()->getStore($fallbackStore);
    }

    /**
     * Get exceptions for redirect
     *
     * @return array
     */
    protected function _getExceptions()
    {
        $config = Mage::getStoreConfig('geoip/general/exceptions');
        $config = preg_split('/$\R?^/m', $config);
        $result = array(
            'frontname'     => array(),
            'controller'    => array(),
            'action'        => array(),
        );

        foreach ($config as $exception) {
            $exception = trim($exception);
            if (empty($exception)) {
                continue;
            }

            $parts = explode('/', trim($exception));

            switch (count($parts)) {
                case 0:
                    break;
                case 1:
                    $result['frontname'][] = $exception;
                    break;
                case 2:
                    $result['controller'][] = array(
                        'frontname'     => $parts[0],
                        'controller'    => $parts[1],
                    );
                    break;
                case 3:
                    $result['action'][] = array(
                        'frontname'     => $parts[0],
                        'controller'    => $parts[1],
                        'action'        => $parts[2],
                    );
                    break;
                default:
                    break;
            }
        }

        return $result;
    }

    /**
     * Validate Exceptions to see if we should abort redirect.
     *
     * @param array $exceptions
     *
     * @return boolean
     */
    protected function _validateException($exceptions)
    {
        $request        = Mage::app()->getRequest();
        $frontname      = $request->getModuleName();
        $controller     = $request->getControllerName();
        $action         = $request->getActionName();

        // Check if whole frontname should be excluded
        foreach ($exceptions['frontname'] as $frontnameEx) {
            if ($frontnameEx == $frontname) {
                return true;
            }
        }

        // Check if specific controller should be excluded
        foreach ($exceptions['controller'] as $controllerEx) {
            if ($controllerEx['frontname'] == $frontname
                && $controllerEx['controller'] == $controller
            ) {
                return true;
            }
        }

        // Check if specific action should be excluded
        foreach ($exceptions['action'] as $actionEx) {
            if ($actionEx['frontname'] == $frontname
                && $actionEx['controller'] == $controller
                && $actionEx['action'] == $action
            ) {
                return true;
            }
        }

        return false;
    }

    /**
     * Get Store witch country is allowed in
     *
     * @param string $country
     *
     * @return Mage_Core_Model_Store|null
     */
    protected function _getStoreForCountry($country)
    {
        $store = $this->_matchDefaultCountry($country);

        if ($store && $store->getId()) {
            return $store;
        }

        $store = $this->_matchAllowedCountry($country);

        if ($store && $store->getId()) {
            return $store;
        }

        return null;
    }

    /**
     * Match default country on store
     *
     * @param string $country
     *
     * @return Mage_Core_Model_Store|null
     */
    public function _matchDefaultCountry($country)
    {
        $stores = Mage::app()->getStores();

        foreach ($stores as $store) {
            if (!$store->getIsActive()) {
                continue;
            }
            $result = new Varien_Object(array('is_allowed' => 1, 'store' => $store));
            Mage::dispatchEvent(
                'wh_geoip_redirect_match_default_country_before',
                array('result' => $result)
            );

            $defaultCountry = Mage::getStoreConfig('general/country/default', $store->getId());
            if ($result->getIsAllowed() && $defaultCountry == $country) {
                return $store;
            }
        }

        return null;
    }

    /**
     * Match any store witch country is allowed in
     *
     * @param string $country
     *
     * @return Mage_Core_Model_Store|null
     */
    public function _matchAllowedCountry($country)
    {
        $stores = Mage::app()->getStores();

        foreach ($stores as $store) {
            if (!$store->getIsActive()) {
                continue;
            }
            $result = new Varien_Object(array('is_allowed' => 1, 'store' => $store));
            Mage::dispatchEvent(
                'wh_geoip_redirect_match_allowed_country_before',
                array('result' => $result)
            );

            if ($result->getIsAllowed() && Mage::helper('webbhusetgeoip')->isCountryAllowed($country, $store)) {
                return $store;
            }
        }

        return null;
    }

    /**
     * Check if can be store code as part of url
     *
     * @return bool
     */
    protected function _canBeStoreCodeInUrl()
    {
        return Mage::isInstalled() && Mage::getStoreConfigFlag(Mage_Core_Model_Store::XML_PATH_STORE_IN_URL);
    }
}
