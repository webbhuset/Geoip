<?php

class Webbhuset_Geoip_Model_Database
    extends Webbhuset_Geoip_Model_Abstract
{
    public function getDatFileDownloadDate()
    {
        return file_exists($this->localFile) ? filemtime($this->localFile) : 0;
    }

    public function getArchivePath()
    {
        return $this->localArchive;
    }

    /**
     * Verifies that we have write permission
     *
     * @return string error messge or empty string
     */
    public function checkFilePermissions()
    {
        /** @var $helper Webbhuset_Geoip_Helper_Data */
        $helper = Mage::helper('webbhusetgeoip');

        $dir = Mage::getBaseDir('var') . '/' . $this->localDir;
        if (file_exists($dir)) {
            if (!is_dir($dir)) {
                return sprintf($helper->__('%s exists but it is a file, not a directory.'), 'var/' . $this->localDir);
            } elseif ((!file_exists($this->localFile) || !file_exists($this->localArchive)) && !is_writable($dir)) {
                return sprintf($helper->__('%s exists but is not writable.'), 'var/' . $this->localDir);
            } elseif (file_exists($this->localFile) && !is_writable($this->localFile)) {
                return sprintf($helper->__('%s is not writable.'), 'var/' . $this->localDir . '/GeoLite2-Country.mmdb');
            } elseif (file_exists($this->localArchive) && !is_writable($this->localArchive)) {
                return sprintf($helper->__('%s is not writable.'), 'var/' . $this->localDir . '/GeoLite2-Country.tar.gz');
            }
        } elseif (!@mkdir($dir)) {
            return sprintf($helper->__('Can\'t create %s directory.'), 'var/' . $this->localDir);
        }

        return '';
    }

    /**
     * Adds a list of countries to the list of allowed countries
     *
     * @return string JSON that contains status (error|success) and a message
     */
    public function update()
    {
        /** @var $helper Webbhuset_Geoip_Helper_Data */
        $helper = Mage::helper('webbhusetgeoip');

        if ($permissions_error = $this->checkFilePermissions()) {
            $response['message'] = $permissions_error;
        } else {
            $remote_file_size = $helper->getSize($this->remoteArchive);
            if ($remote_file_size < 100000) {
                $response['message'] = $helper->__('You are banned from downloading the file. Please try again in several hours.');
            } else {
                /** @var $_session Mage_Core_Model_Session */
                $_session = Mage::getSingleton('core/session');
                $_session->setData('_geoip_file_size', $remote_file_size);

                $src = fopen($this->remoteArchive, 'r');
                $target = fopen($this->localArchive, 'w');
                stream_copy_to_stream($src, $target);
                fclose($target);

                $destination = Mage::getBaseDir('var') . '/' . $this->localDir .'/';

                $response   = ['status' => 'error'];
                $countryDb  = 'GeoLite2-Country.mmdb';

                if (filesize($this->localArchive)) {
                    if ($helper->extract($this->localArchive, $destination, $countryDb)) {
                        $response['status'] = 'success';
                        $format = Mage::app()->getLocale()->getDateTimeFormat(Mage_Core_Model_Locale::FORMAT_TYPE_MEDIUM);
                        $response['date'] = Mage::app()->getLocale()->date(filemtime($this->localFile))->toString($format);
                    } else {
                        $response['message'] = $helper->__('UnGzipping failed');
                    }
                } else {
                    $response['message'] = $helper->__('Download failed.');
                }
            }
        }

        echo json_encode($response);
    }
}
