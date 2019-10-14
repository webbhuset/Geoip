<?php
/**
 * Webbhuset GeoIP Syncronize Block
 *
 * @category    Webbhuset
 * @package     Webbhuset_Geoip
 * @author      Webbhuset <info@webbhuset.se>
 */
class Webbhuset_Geoip_Block_System_Config_Synchronize extends Mage_Adminhtml_Block_System_Config_Form_Field
{
    /*
     * Set template
     */
    protected function _construct()
    {
        parent::_construct();
        $this->setTemplate('webbhuset/geoip/system/config/synchronize.phtml');
    }

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

    /**
     * Return element html
     *
     * @param  Varien_Data_Form_Element_Abstract $element
     * @return string
     */
    protected function _getElementHtml(Varien_Data_Form_Element_Abstract $element)
    {
        return $this->_toHtml();
    }

    /**
     * Return ajax url for synchronize button
     *
     * @return string
     */
    public function getAjaxSyncUrl()
    {
        return Mage::getSingleton('adminhtml/url')->getUrl('adminhtml/whgeoip/synchronize');
    }

    /**
     * Return ajax url for synchronize button
     *
     * @return string
     */
    public function getAjaxStatusUpdateUrl()
    {
        return Mage::getSingleton('adminhtml/url')->getUrl('adminhtml/whgeoip/status');
    }

    /**
     * Generate synchronize button html
     *
     * @return string
     */
    public function getButtonHtml()
    {
        /** @var $button Mage_Adminhtml_Block_Widget_Button */
        $button = $this->getLayout()->createBlock('adminhtml/widget_button');
        $button->setData([
                'id'        => 'synchronize_button',
                'label'     => $this->helper('adminhtml')->__('Synchronize'),
                'onclick'   => 'javascript:synchronize(); return false;'
            ]);

        return $button->toHtml();
    }

    /**
     * Retrieve last sync params settings
     *
     * Return array format:
     * array (
     *  => storage_type     int,
     *  => connection_name  string
     * )
     *
     * @return array
     */
    public function getSyncStorageParams()
    {
        $flag = Mage::getSingleton('core/file_storage')->getSyncFlag();
        $flagData = $flag->getFlagData();

        if ($flag->getState() == Mage_Core_Model_File_Storage_Flag::STATE_NOTIFIED
                && is_array($flagData)
            && isset($flagData['destination_storage_type']) && $flagData['destination_storage_type'] != ''
            && isset($flagData['destination_connection_name'])
        ) {
            $storageType    = $flagData['destination_storage_type'];
            $connectionName = $flagData['destination_connection_name'];
        } else {
            $storageType    = Mage_Core_Model_File_Storage::STORAGE_MEDIA_FILE_SYSTEM;
            $connectionName = '';
        }

        return [
            'storage_type'      => $storageType,
            'connection_name'   => $connectionName
        ];
    }
}
