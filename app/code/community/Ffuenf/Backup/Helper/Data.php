<?php
/**
 * Ffuenf_Backup extension
 * 
 * NOTICE OF LICENSE
 * 
 * This source file is subject to the MIT License
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/mit-license.php
 * 
 * @category   Ffuenf
 * @package    Ffuenf_Backup
 * @author     Achim Rosenhagen <a.rosenhagen@ffuenf.de>
 * @copyright  Copyright (c) 2015 ffuenf (http://www.ffuenf.de)
 * @license    http://opensource.org/licenses/mit-license.php MIT License
*/

class Ffuenf_Backup_Helper_Data extends Ffuenf_Backup_Helper_Core
{

    /**
    * config paths
    */
    const CONFIG_EXTENSION_ACTIVE       = 'backup/enable';
    const CONFIG_EXTENSION_MAGERUNPATH  = 'backup/magerun/path';
    const CONFIG_EXTENSION_RSYNCPATH    = 'backup/rsync/path';
    const CONFIG_EXTENSION_AWSCLIPATH   = 'backup/aws/path';
    const CONFIG_EXTENSION_GPGPATH      = 'backup/gpg/path';

    /**
    * Variable for if the extension is active
    *
    * @var bool
    */
    protected $bExtensionActive;

    /**
    * Variable for if magerun is available
    *
    * @var bool
    */
    protected $bMagerunAvailable;

    /**
    * Variable for if rsync is available
    *
    * @var bool
    */
    protected $bRsyncAvailable;

    /**
    * Variable for if aws-cli is available
    *
    * @var bool
    */
    protected $bAwsCliAvailable;

    /**
    * Variable for if gpg is available
    *
    * @var bool
    */
    protected $bGpgAvailable;

    /**
    * Variable for magerun path
    *
    * @var string
    */
    protected $sMagerunPath;

    /**
    * Variable for rsync path
    *
    * @var string
    */
    protected $sRsyncPath;

    /**
    * Variable for aws-cli path
    *
    * @var string
    */
    protected $sAwsCliPath;

    /**
    * Variable for gpg path
    *
    * @var string
    */
    protected $sGpgPath;

    /**
    * Check to see if the extension is active
    *
    * @return bool
    */
    public function isExtensionActive()
    {
        if ($this->bExtensionActive === null) {
            $this->bExtensionActive = $this->getStoreFlag(self::CONFIG_EXTENSION_ACTIVE, 'bExtensionActive');
        }
        return $this->bExtensionActive;
    }

    /**
    * Checks if magerun is available
    *
    * @return bool
    */
    public function isMagerunAvailable()
    {
        $output = $this->runMagerun(array('--version'));
        if (!isset($output[0]) || strpos($output[0], 'n98-magerun version') === false) {
            $this->bMagerunAvailable = false;
        } else {
            $this->bMagerunAvailable = true;
        }
        return $this->bMagerunAvailable;
    }

    /**
    * Checks if rsync is available
    *
    * @return bool
    */
    public function isRsyncAvailable()
    {
        $output = exec('which rsync');
        if (!is_file($output) || !$output == self::CONFIG_EXTENSION_RSYNCPATH) {
            $this->bRsyncAvailable = false;
        } else {
            $this->bRsyncAvailable = true;
        }
        return $this->bRsyncAvailable;
    }

    /**
    * Checks if aws-cli is available
    *
    * @return bool
    */
    public function isAwsCliAvailable()
    {
        $output = exec('which aws');
        if (!is_file($output) || !$output == self::CONFIG_EXTENSION_AWSCLIPATH) {
            $this->bAwsCliAvailable = false;
        } else {
            $this->bAwsCliAvailable = true;
        }
        return $this->bAwsCliAvailable;
    }

    /**
    * Checks if gpg is available
    *
    * @return bool
    * @throws Mage_Core_Exception
    */
    public function isGpgAvailable()
    {
        $output = exec('which gpg');
        if (!is_file($output) || !$output == self::CONFIG_EXTENSION_GPGPATH) {
            $this->bGpgAvailable = false;
        } else {
            $this->bGpgAvailable = true;
            if (in_array('gnupg', get_loaded_extensions()) != 1) {
                Mage::throwException('Error while loading gnupg php extension');
            }
        }
        return $this->bGpgAvailable;
    }

    /**
    * Get magerun path
    *
    * @return string
    * @throws Mage_Core_Exception
    */
    public function getMagerunPath()
    {
        $pathMagerun = $this->getStoreConfig(self::CONFIG_EXTENSION_MAGERUNPATH, 'sMagerunPath');
        $baseDir = Mage::getBaseDir();
        $path = $pathMagerun;
        if (!is_file($path)) {
            Mage::throwException('Could not find magerun at ' . $path);
        }
        return $path;
    }

    /**
    * Checks if magerun is present and returns the version number
    *
    * @return string (magerun version number)
    * @throws Mage_Core_Exception
    */
    public function checkMagerun()
    {
        $output = $this->runMagerun(array('--version'));
        if (!$this->getMagerunPath()) {
            Mage::throwException('No valid magerun found');
        }
        $matches = array();
        preg_match('/(\d+\.\d+\.\d)/', $output[0], $matches);
        return $matches[1];
    }

    /**
    * Run magerun
    *
    * @param array
    * @return array
    */
    public function runMagerun($options = array())
    {
        array_unshift($options, '--root-dir='.Mage::getBaseDir());
        array_unshift($options, '--no-interaction');
        array_unshift($options, '--no-ansi');
        $output = array();
        exec('php -d apc.enable_cli=0 ' . $this->getMagerunPath() . ' ' . implode(' ', $options), $output);
        return $output;
    }

    /**
    * Get rsync path
    *
    * @return string
    * @throws Mage_Core_Exception
    */
    public function getRsyncPath()
    {
        if ($this->isRsyncAvailable()) {
            return $this->getStoreConfig(self::CONFIG_EXTENSION_RSYNCPATH, 'sRsyncPath');
        } else {
            Mage::throwException('Could not find rsync');
        }
    }

    /**
    * Get aws-cli path
    *
    * @return string
    * @throws Mage_Core_Exception
    */
    public function getAwsCliPath()
    {
        if ($this->isAwsCliAvailable()) {
            return $this->getStoreConfig(self::CONFIG_EXTENSION_AWSCLIPATH, 'sAwsCliPath');
        } else {
            Mage::throwException('Could not find aws-cli');
        }
    }

    /**
    * Get gpg path
    *
    * @return string
    * @throws Mage_Core_Exception
    */
    public function getGpgPath()
    {
        if ($this->isGpgAvailable()) {
            return $this->getStoreConfig(self::CONFIG_EXTENSION_GPGPATH, 'sGpgPath');
        } else {
            Mage::throwException('Could not find gpg');
        }
    }

    /**
    * Explodes a string and trims all values for whitespace in the ends.
    * If $onlyNonEmptyValues is set, then all blank ('') values are removed.
    *
    * @see t3lib_div::trimExplode() in TYPO3
    * @param $delim
    * @param string $string
    * @param bool $removeEmptyValues If set, all empty values will be removed in output
    * @return array Exploded values
    */
    public function pregExplode($delim, $string, $removeEmptyValues = true)
    {
        $explodedValues = preg_split($delim, $string);
        $result = array_map('trim', $explodedValues);
        if ($removeEmptyValues) {
            $temp = array();
            foreach ($result as $value) {
                if ($value !== '') {
                    $temp[] = $value;
                }
            }
            $result = $temp;
        }
        return $result;
    }
}