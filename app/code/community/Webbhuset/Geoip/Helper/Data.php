<?php
/**
 * Webbhuset GeoIP Helper
 *
 * @category    Webbhuset
 * @package     Webbhuset_Geoip
 * @author      Webbhuset <info@webbhuset.se>
 */
class Webbhuset_Geoip_Helper_Data extends Mage_Core_Helper_Abstract
{
    /**
     * Check if country is allowed on store.
     *
     * @param string $country
     * @param Mage_Core_Model_Store $store
     *
     * @return boolean
     */
    public function isCountryAllowed($country, $store)
    {
        $allowedCountries = Mage::getModel('directory/country')
            ->getResourceCollection()
            ->loadByStore($store)
            ->toOptionArray();

        foreach ($allowedCountries as $allowedCountry) {
            if ($allowedCountry['value'] == $country) {
                return true;
            }
        }

        return false;
    }
}
