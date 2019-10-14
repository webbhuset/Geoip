<?php
/**
 * Webbhuset GeoIP Status Block
 *
 * @category    Webbhuset
 * @package     Webbhuset_Geoip
 * @author      Webbhuset <info@webbhuset.se>
 */
class Webbhuset_Geoip_Block_System_Config_Status
    extends Mage_Adminhtml_Block_System_Config_Form_Field
{
    /**
     * Remove scope label
     *
     * @param  Varien_Data_Form_Element_Abstract $element
     * @return string
     */
    public function render(Varien_Data_Form_Element_Abstract $element)
    {
        $element->unsScope()->unsCanUseWebsiteValue()->unsCanUseDefaultValue();

        return parent::render($element);
    }

    protected function _getElementHtml(Varien_Data_Form_Element_Abstract $element)
    {
        $database = Mage::getModel('webbhusetgeoip/database');
        if ($date = $database->getDatFileDownloadDate()) {
            $format = Mage::app()->getLocale()->getDateTimeFormat(Mage_Core_Model_Locale::FORMAT_TYPE_MEDIUM);
            $date = Mage::app()->getLocale()->date(intval($date))->toString($format);
        } else {
            $date = '-';
        }

        return '<div id="sync_update_date">' . $date . '</div>';
    }
}
