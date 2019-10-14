<?php
class Webbhuset_GeoIP_Model_Abstract
{
    protected $localDir, $localFile, $record, $localArchive, $remoteArchive;

    public function __construct()
    {
        $this->localDir         = 'geoip';
        $this->localFile        = Mage::getBaseDir('var') . '/' . $this->localDir . '/GeoLite2-Country.mmdb';

        $this->localArchive     = Mage::getBaseDir('var') . '/' . $this->localDir . '/GeoLite2-Country.tar.gz';
        $this->remoteArchive    = 'http://geolite.maxmind.com/download/geoip/database/GeoLite2-Country.tar.gz';
    }

}
