<?php

class Webbhuset_Geoip_Adminhtml_WhgeoipController
    extends Mage_Adminhtml_Controller_Action
{
    public function statusAction()
    {
        $session  = Mage::getSingleton('core/session');
        $database = Mage::getModel('webbhusetgeoip/database');

        $_realSize  = filesize($database->getArchivePath());
        $_totalSize = $session->getData('_geoip_file_size');

        echo $_totalSize ? $_realSize / $_totalSize * 100 : 0;
    }

    public function synchronizeAction()
    {
        $database = Mage::getModel('webbhusetgeoip/database');
        $database->update();
    }
}
