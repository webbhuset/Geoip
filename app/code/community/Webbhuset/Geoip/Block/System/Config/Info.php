<?php
/**
 * Webbhuset GeoIP Info Block
 *
 * @category    Webbhuset
 * @package     Webbhuset_Geoip
 * @author      Webbhuset <info@webbhuset.se>
 */
class Webbhuset_Geoip_Block_System_Config_Info
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
        $ip             = Mage::helper('webbhusetgeoip')->getIp();
        $geoIP          = Mage::getSingleton('webbhusetgeoip/country');
        $currentCountry = $geoIP->getCountry();

        if (!$currentCountry) {
            $currentCountry = "no match for IP";
        } else {
            $country        = Mage::getModel('directory/country')->loadByCode($currentCountry);
            $countryName    = $country->getName();
            $currentCountry = "{$countryName} ({$currentCountry})";
        }

        $html = "<table><tr><td><td><td></tr>";
        $html .="<tr><td>Country:<td><td>{$currentCountry}<td></tr>";
        $html .="<tr><td>Current IP:<td><td>{$ip}<td></tr>";
        $html .= "</table>";

        return $html;
    }
}
