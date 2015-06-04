<?php

/**
 * Ffuenf_Backup extension.
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the MIT License
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/mit-license.php
 *
 * @category   Ffuenf
 *
 * @author     Achim Rosenhagen <a.rosenhagen@ffuenf.de>
 * @copyright  Copyright (c) 2015 ffuenf (http://www.ffuenf.de)
 * @license    http://opensource.org/licenses/mit-license.php MIT License
 */

/**
 * @see Ffuenf_Backup_Helper_Data
 *
 * @loadSharedFixture shared
 */
class Ffuenf_Backup_Test_Helper_Data extends EcomDev_PHPUnit_Test_Case
{
    /**
     * Tests is extension active.
     *
     * @test
     * @covers Ffuenf_Backup_Helper_Data::isExtensionActive
     */
    public function testIsExtensionActive()
    {
        $this->assertTrue(
            Mage::helper('ffuenf_backup')->isExtensionActive(),
            'Extension is not active please check config'
        );
    }

    /**
     * Tests is magerun available.
     *
     * @test
     * @loadFixture
     * @covers Ffuenf_Backup_Helper_Data::isMagerunAvailable
     */
    public function testIsMagerunAvailable()
    {
        $this->assertTrue(
            Mage::helper('ffuenf_backup')->isMagerunAvailable(),
            'No valid magerun found'
        );
    }

    /**
     * Tests is rsync available.
     *
     * @test
     * @loadFixture
     * @covers Ffuenf_Backup_Helper_Data::isRsyncAvailable
     */
    public function testIsRsyncAvailable()
    {
        $this->assertTrue(
            Mage::helper('ffuenf_backup')->isRsyncAvailable(),
            'No valid rsync found'
        );
    }

    /**
     * Tests is aws-cli available.
     *
     * @test
     * @loadFixture
     * @covers Ffuenf_Backup_Helper_Data::isAwsCliAvailable
     */
    public function testIsAwsCliAvailable()
    {
        $this->assertTrue(
            Mage::helper('ffuenf_backup')->isAwsCliAvailable(),
            'No valid aws-cli found'
        );
    }

    /**
     * Tests is gpg available.
     *
     * @test
     * @loadFixture
     */
    public function testIsGpgAvailable()
    {
        $this->assertTrue(
            Mage::helper('ffuenf_backup')->isGpgAvailable(),
            'No valid gpg found'
        );
    }
}
