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
    /**
     * Get size of remote file
     *
     * @param $file
     * @return mixed
     */
    public function getSize($file)
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $file);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HEADER, true);
        curl_setopt($ch, CURLOPT_NOBODY, true);
        curl_exec($ch);

        return curl_getinfo($ch, CURLINFO_CONTENT_LENGTH_DOWNLOAD);
    }

    /**
     * Extracts .mmdb database file from .tar.gz archive
     *
     * @param $archive
     * @param $destination
     * @return int
     */
    public function extract($archive, $destination, $files)
    {
        if (!is_array($files)) {
            $files = [$files];
        }

        try {
            $archive = new PharData($archive);

            foreach (new RecursiveIteratorIterator($archive) as $file) {
                if (!in_array($file->getFileName(), $files)) {
                    continue;
                }

                $path     = basename($archive->current()->getPathName());
                $fileName = $file->getFileName();
                $fullPath = "{$destination}/{$path}";

                if ($archive->extractTo($destination, "{$path}/{$fileName}", true)) {
                    rename("{$fullPath}/$fileName", "{$destination}/{$fileName}");
                    rmdir($fullPath);

                    return filesize("{$destination}/{$fileName}");
                }
            }

        } catch (Exception $e) {
            Mage::logException($e);
        }

        return 0;
    }
}
