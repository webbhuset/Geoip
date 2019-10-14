<?php

use GeoIp2\Database\Reader;

/**
 * Webbhsuet Geoip Observer
 *
 * @category    Webbhuset
 * @package     Webbhuset_Geoip
 * @author      Webbhuset <info@webbhsuet.se>
 */
class Webbhuset_Geoip_Model_Country
    extends Webbhuset_Geoip_Model_Abstract
{
    protected $country, $reader;
    protected $allowedCountries = [];

    public function __construct()
    {
        parent::__construct();

        $this->_initReader();

        if (!$this->record && $this->reader) {
            try {
                $this->record = $this->reader->country(Mage::helper('webbhusetgeoip')->getIp());
                $allowCountries = explode(',', (string)Mage::getStoreConfig('general/country/allow'));
                $this->addAllowedCountry($allowCountries);
            } catch (Exception $e) {
                Mage::logException($e);
            }
        }
    }

    protected function _initReader() {
        try {
            $this->reader = new Reader($this->localFile);
        } catch (Exception $e) {
            Mage::logException($e);
        }
    }

    /**
     * Fetches a new country record from reader by IP and returns the iso code
     *
     * @param string $ip
     *
     * @return boolean
     */
    public function getCountryByIp($ip)
    {
        if (!filter_var($ip, FILTER_VALIDATE_IP)) {
            return false;
        }

        if (!$this->reader) {
            return false;
        }

        try {
            if ($this->record
                && $this->record->traits
                && $this->record->traits->ipAddress != $ip
            ) {
                $this->record = $this->reader->country($ip);
            } else if (!$this->record) {
                $this->record = $this->reader->country($ip);
            }

        } catch (Exception $e) {
            Mage::logException($e);
            return false;
        }

        return  $this->getCountry();
    }

    /**
     * Returns the curent records isoCode
     *
     * @return string
     */
    public function getCountry()
    {
        if (!$this->record) {
            return false;
        }

        if (!$this->record->country) {
            return false;
        }

        return $this->record->country->isoCode;
    }

    /**
     * Checks if current or supplied country is allowed in current store
     *
     * @param string $country
     *
     * @return boolean
     */
    public function isCountryAllowed($country = null)
    {
        if (!$country) {
            $country = $this->getCountry();
        }

        if (count($this->allowedCountries) && $country) {
            return in_array($country, $this->allowedCountries);
        }

        return false;
    }

    /**
     * Adds a list of countries to the list of allowed countries
     *
     * @param array $countries
     *
     * @return boolean
     */
    public function addAllowedCountry($countries)
    {
        if (!is_array($countries)) {
            $countries = [$countries];
        }

        $this->allowedCountries = array_merge($this->allowedCountries, $countries);
        return $this;
    }

    /**
     * Adds a list of countries to the list of allowed countries
     *
     *
     * @return array $countries
     */
    public function getAllowedCountries()
    {
        return $this->allowedCountries;
    }
}
