<?php

class Webbhuset_Geoip_Model_Cron
{
    public function run()
    {
        /** @var $database Webbhuset_Geoip_Model_Database */
        $database = Mage::getModel('webbhusetgeoip/database');
        $database->update();
    }
}
