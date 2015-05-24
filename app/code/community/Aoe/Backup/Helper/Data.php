<?php
/**
* Class ${NAME}
*
* @author Fabrizio Branca
* @since 2014-10-07
*/
class Aoe_Backup_Helper_Data extends Aoe_Backup_Helper_Core {

  /**
  * Path for the config for extension active status
  */
  const CONFIG_EXTENSION_ACTIVE = 'system/aoe_backup/enable';

  /**
  * Path for the config for n98-magerun path
  */
  const CONFIG_EXTENSION_N98PATH = 'system/aoe_backup/path_n98';

  /**
  * Variable for if the extension is active
  *
  * @var bool
  */
  protected $bExtensionActive;

  /**
  * Variable for for n98-magerun path
  *
  * @var string
  */
  protected $sN98MagerunPath;
 
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
  * Variable for if n98-magerun is available
  *
  * @var bool
  */
  protected $bN98MagerunAvailable;

  /**
  * Checks if n98-magerun is available
  */
  public function isN98MagerunAvailable()
  {
    $output = $this->runN98Magerun(array('--version'));
    if (!isset($output[0]) || strpos($output[0], 'n98-magerun version') === false) {
      $this->bN98MagerunAvailable = false;
    } else {
      $this->bN98MagerunAvailable = true;
    }
    return $this->bN98MagerunAvailable;
  }

  /**
  * Checks if n98-magerun is present and returns the version number
  */
  public function checkN98Magerun()
  {
    $output = $this->runN98Magerun(array('--version'));
    if (!$this->getN98MagerunPath()) {
      Mage::throwException('No valid n98-magerun found');
    }
    $matches = array();
    preg_match('/(\d+\.\d+\.\d)/', $output[0], $matches);
    return $matches[1];
  }

  /**
  * Get n98-magerun path
  *
  * @return string
  * @throws Mage_Core_Exception
  */
  public function getN98MagerunPath() {
    $pathN98 = $this->getStoreConfig(self::CONFIG_EXTENSION_N98PATH, 'sN98MagerunPath');
    $baseDir = Mage::getBaseDir();
    $path = $baseDir . DS . $pathN98;
    if (!is_file($path)) {
      Mage::throwException('Could not find n98-magerun at ' . $path);
    }
    return $path;
  }

  public function runN98Magerun($options=array()) {
    array_unshift($options, '--root-dir='.Mage::getBaseDir());
    array_unshift($options, '--no-interaction');
    array_unshift($options, '--no-ansi');
    $output = array();
    exec('php -d apc.enable_cli=0 ' . $this->getN98MagerunPath() . ' ' . implode(' ', $options), $output);
    return $output;
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