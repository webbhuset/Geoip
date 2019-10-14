<?php

class Webbhuset_Geoip_Block_Adminhtml_Notifications
    extends Mage_Adminhtml_Block_Template
{
    public function checkFilePermissions()
    {
        $database = Mage::getModel('webbhusetgeoip/database');

        return $database->checkFilePermissions();
    }
}
