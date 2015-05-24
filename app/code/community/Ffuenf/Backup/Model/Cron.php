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

class Ffuenf_Backup_Model_Cron
{

    /**
     * source directory for database backups
     */
    const DB_DIR = 'database';

    /**
     * source directory for file backups
     */
    const FILES_DIR = 'files';

    /**
     * config paths
     */
    const CONFIG_EXTENSION_LOCALDIR             = 'backup/general/local_directory';
    const CONFIG_EXTENSION_BACKUPDATABASE       = 'backup/general/backup_database';
    const CONFIG_EXTENSION_ENCRYPTDATABASE      = 'backup/gpg/encrypt_database';
    const CONFIG_EXTENSION_EXCLUDEDTABLES       = 'backup/general/excluded_tables';
    const CONFIG_EXTENSION_BACKUPFILES          = 'backup/general/backup_files';
    const CONFIG_EXTENSION_EXCLUDEDDIRECTORIES  = 'backup/general/excluded_directories';
    const CONFIG_EXTENSION_GNUPGHOME            = 'backup/gpg/home';
    const CONFIG_EXTENSION_GNUPRECIPIENT        = 'backup/gpg/recipient';
    const CONFIG_EXTENSION_AWSCLIPATH           = 'backup/aws/path';
    const CONFIG_EXTENSION_AWSREGION            = 'backup/aws/region';
    const CONFIG_EXTENSION_AWSACCESSKEYID       = 'backup/aws/access_key_id';
    const CONFIG_EXTENSION_AWSSECRETACCESSKEY   = 'backup/aws/secret_access_key';
    const CONFIG_EXTENSION_AWSTARGETLOCATION    = 'backup/aws/target_location';
    const CONFIG_EXTENSION_RSYNCPATH            = 'backup/rsync/path';

    protected $usingTempDir = false;
    protected $localDir;

    /**
     * backup
     *
     * @return string|array<string,array>
     */
    public function backup()
    {
        $didSomething = false;
        $statistics = array();
        $statistics['durations'] = array();
        $startTime = microtime(true);
        if (Mage::getStoreConfigFlag(self::CONFIG_EXTENSION_BACKUPDATABASE)) {
            $dirSegment = self::DB_DIR;
            $didSomething = true;
            $this->createDatabaseBackup();
            if (Mage::getStoreConfig(self::CONFIG_EXTENSION_ENCRYPTDATABASE) == 1) {
                $this->encryptDatabaseBackup();
            }
            $stopTime = microtime(true);
            $statistics['durations']['database backup'] = number_format($stopTime - $startTime, 2);
            $statistics['uploadinfo']['database backup'] = $this->upload($dirSegment);
            $startTime = $stopTime;
        }
        if (Mage::getStoreConfigFlag(self::CONFIG_EXTENSION_BACKUPFILES)) {
            $dirSegment = self::FILES_DIR;
            $didSomething = true;
            $this->createMediaBackup();
            $stopTime = microtime(true);
            $statistics['durations']['file backup'] = number_format($stopTime - $startTime, 2);
            $statistics['uploadinfo']['file backup'] = $this->upload($dirSegment);
            $startTime = $stopTime;
        }
        if (!$didSomething) {
            return 'NOTHING: Database and file backup are disabled.';
        }
        $stopTime = microtime(true);
        $statistics['durations']['upload'] = number_format($stopTime - $startTime, 2);
        // delete tmp directory if it was created
        // return some statistics (duration, filesize)
        return $statistics;
    }

    /**
     * createDatabaseBackup
     *
     * @return void
     * @throws Mage_Core_Exception
     */
    protected function createDatabaseBackup()
    {
        $helper = Mage::helper('ffuenf_backup');
        $res = touch(Mage::getBaseDir('var') . '/db_dump_in_progress.lock');
        if (!$res) {
            Mage::throwException('Error while creating lock file');
        }
        $excludedTables = Mage::getStoreConfig(self::CONFIG_EXTENSION_EXCLUDEDTABLES);
        $excludedTables = $helper->pregExplode('/\s+/', $excludedTables);
        $targetFile = $this->getLocalDirectory() . DS . self::DB_DIR . DS . 'combined_dump.sql';
        if (is_file($targetFile . '.gz')) {
            $res = unlink($targetFile . '.gz');
            if (!$res) {
                Mage::throwException('Error while deleting existing db dump at ' . $targetFile . '.gz');
            }
        }
        $helper->runMagerun(array(
            '-q',
            'db:dump',
            '--compression=gzip',
            '--strip="' . implode(' ', $excludedTables) . '"',
            $targetFile // magerun will create a combined_dump.sql.gz instead because of the compression
        ));
        if (!is_file($targetFile . '.gz')) {
            Mage::throwException('Could not find generated database dump at ' . $targetFile . '.gz');
        }
        $filesize = filesize($targetFile . '.gz');
        if ($filesize < 1024 * 10) { // 10 KB
            Mage::throwException('File is too small. Check contents at ' . $targetFile . '.gz');
        }
        // created.txt
        if (Mage::getStoreConfig(self::CONFIG_EXTENSION_ENCRYPTDATABASE) != 1) {
            $filename = $this->getLocalDirectory() . DS . self::DB_DIR . DS . 'created.txt';
            $res = file_put_contents($filename, time());
            if ($res === FALSE) {
                Mage::throwException('Error while writing ' . $filename);
            }
        }
        $res = unlink(Mage::getBaseDir('var') . '/db_dump_in_progress.lock');
        if ($res === FALSE) {
            Mage::throwException('Error while deleting lock file');
        }
    }

    /**
     * encryptDatabaseBackup
     *
     * @return void
     * @throws Mage_Core_Exception
     */
    protected function encryptDatabaseBackup()
    {
        $helper = Mage::helper('ffuenf_backup');
        if (!$helper->isGpgAvailable()) {
            Mage::throwException('gpg is not available)');
        }
        putenv('GNUPGHOME=' . Mage::getStoreConfig(self::CONFIG_EXTENSION_GNUPGHOME));
        $sourceFile = $this->getLocalDirectory() . DS . self::DB_DIR . DS . 'combined_dump.sql.gz';
        $targetFile = $this->getLocalDirectory() . DS . self::DB_DIR . DS . 'combined_dump.sql.gz.gpg';
        $recipient = Mage::getStoreConfig(self::CONFIG_EXTENSION_GNUPRECIPIENT);
        $filename = $this->getLocalDirectory() . DS . self::DB_DIR . DS . 'created.txt';
        $data = file_get_contents($sourceFile);
        try {
            $gpg = new gnupg();
            $gpg->seterrormode(gnupg::ERROR_EXCEPTION);
            $gpg->addencryptkey($recipient);
            $ciphertext = $gpg->encrypt($data);
            file_put_contents($targetFile, $ciphertext);
        } catch (Exception $e) {
            Mage::throwException('Error while encrypting database backup: ' . $e->getMessage());
        }
        $resSourcefile = unlink($sourceFile);
        if ($resSourcefile === FALSE) {
            Mage::throwException('Error while deleting unencrypted database backup');
        }
        $resFilename = file_put_contents($filename, time());
        if ($resFilename === FALSE) {
            Mage::throwException('Error while writing ' . $filename);
        }
    }

    /**
     * createMediaBackup
     *
     * @return void
     * @throws Mage_Core_Exception
     */
    protected function createMediaBackup()
    {
        $helper = Mage::helper('ffuenf_backup');
        if (!$helper->isRsyncAvailable()) {
            Mage::throwException('rsync is not available');
        }
        $rsync = $helper->getRsyncPath();
        $excludedDirs = Mage::getStoreConfig(self::CONFIG_EXTENSION_EXCLUDEDDIRECTORIES);
        $excludedDirs = $helper->pregExplode('/\s+/', $excludedDirs);
        $options = array(
            '--archive',
            '--no-o --no-p --no-g',
            '--force',
            '--omit-dir-times',
            '--ignore-errors',
            '--partial',
            '--delete-after',
            '--delete-excluded',
        );
        foreach ($excludedDirs as $dir) {
            $options[] = '--exclude='.$dir;
        }
        // source
        $options[] = rtrim(Mage::getBaseDir('media'), DS) . DS;
        // target
        $options[] = $this->getLocalDirectory() . DS . self::FILES_DIR . DS;
        $output = array();
        $returnVar = null;
        exec($rsync . ' ' . implode(' ', $options), $output, $returnVar);
        if ($returnVar !== null) {
            Mage::throwException('Error while rsyncing files to local directory');
        }
        $filename = $this->getLocalDirectory() . DS . self::FILES_DIR . DS . 'created.txt';
        $res = file_put_contents($filename, time());
        if ($res === FALSE) {
            Mage::throwException('Error while writing ' . $filename);
        }
    }

    /**
     * upload
     *
     * @param string $dirSegment
     * @return array<string,array>
     */
    protected function upload($dirSegment)
    {
        $type = $dirSegment;
        $uploadInfo = array();
        $localFile = $this->getLocalDirectory() . DS . $type . DS . 'created.txt';
        $remoteFile = $targetLocation . DS . $type . DS . 'created.txt';
        $options = array(
            $localFile,
            $remoteFile
        );
        $uploadInfo[$dirSegment] = $this->runAwsCli($options);
        return $uploadInfo;
    }

    /**
     * runAwsCli
     *
     * @param array
     * @return array<string,array>
     * @throws Mage_Core_Exception
     */
    protected function runAwsCli($options = array())
    {
        $helper = Mage::helper('ffuenf_backup');
        $awscli = Mage::getStoreConfig(self::CONFIG_EXTENSION_AWSCLIPATH);
        $region = Mage::getStoreConfig(self::CONFIG_EXTENSION_AWSREGION);
        $keyId = Mage::getStoreConfig(self::CONFIG_EXTENSION_AWSACCESSKEYID);
        $secret = Mage::getStoreConfig(self::CONFIG_EXTENSION_AWSSECRETACCESSKEY);
        $targetLocation = rtrim(Mage::getStoreConfig(self::CONFIG_EXTENSION_AWSTARGETLOCATION), DS);
        $output = array();
        $returnVar = null;
        $uploadInfo = array();
        $awsgeneraloptions = array(
            '--region ' . $region,
            's3',
            'cp'
        );
        $awsoptions = array_merge($awsgeneraloptions, $options);
        if (!$helper->isAwsCliAvailable()) {
            Mage::throwException('aws-cli is not available)');
        }
        if (empty($region)) {
            Mage::throwException('No region found (' . self::CONFIG_EXTENSION_AWSREGION . ')');
        }
        if (empty($keyId)) {
            Mage::throwException('No access key found (' . self::CONFIG_EXTENSION_AWSACCESSKEYID . ')');
        }
        if (empty($secret)) {
            Mage::throwException('No access secret found (' . self::CONFIG_EXTENSION_AWSSECRETACCESSKEY . ')');
        }
        if (empty($targetLocation)) {
            Mage::throwException('No target location set (' . self::CONFIG_EXTENSION_AWSTARGETLOCATION . ')');
        }
        if (strpos($targetLocation, 's3://') !== 0) {
            Mage::throwException('Invalid S3 target location (must start with s3://)');
        }
        try {
            putenv('AWS_ACCESS_KEY_ID=' . $keyId);
            putenv('AWS_SECRET_ACCESS_KEY=' . $secret);
            exec($awscli . ' ' . implode(' ', $awsoptions), $output, $returnVar);
        } catch (Exception $e) {
            Mage::throwException('Error while syncing directories: ' . $e->getMessage());
        }
        $uploadInfo['sync'] = array(
            'output' => implode("\n", $output),
            'returnVar' => $returnVar,
        );
        return $uploadInfo;
    }

    /**
     * getLocalDirectory
     *
     * @return string
     * @throws Mage_Core_Exception
     */
    protected function getLocalDirectory()
    {
        if (empty($this->localDir)) {
            // if configuration is empty create tmp directory and store that information
            $this->localDir = Mage::getStoreConfig(self::CONFIG_EXTENSION_LOCALDIR);
            $this->localDir = rtrim($this->localDir, DS);
            if (empty($this->localDir)) {
                $this->usingTempDir = true;
                Mage::throwException('Not implemented yet. Please provide configuration');
            }
            foreach (array($this->localDir, $this->localDir . DS . self::DB_DIR, $this->localDir . DS . self::FILES_DIR) as $dir) {
                if (!is_dir($dir)) {
                    Mage::throwException('Could not find local directory at ' . $dir);
                }
            }
        }
        return $this->localDir;
    }
}