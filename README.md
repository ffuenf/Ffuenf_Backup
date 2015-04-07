[![GitHub tag](https://img.shields.io/github/tag/ffuenf/Aoe_Backup.svg)][tag]
[![Build Status](https://img.shields.io/travis/ffuenf/Aoe_Backup.svg)][travis]
[![Code Climate](https://codeclimate.com/github/ffuenf/Aoe_Backup/badges/gpa.svg)][codeclimate_gpa]
[![Test Coverage](https://codeclimate.com/github/ffuenf/Aoe_Backup/badges/coverage.svg)][codeclimate_coverage]

[tag]: https://github.com/ffuenf/Aoe_Backup
[travis]: https://travis-ci.org/ffuenf/Aoe_Backup
[codeclimate_gpa]: https://codeclimate.com/github/ffuenf/Aoe_Backup
[codeclimate_coverage]: https://codeclimate.com/github/ffuenf/Aoe_Backup


[![AOE](http://www.aoe.com/typo3conf/ext/aoe_template/i/aoe-logo.png)](http://www.aoe.com)

# Aoe_Backup Magento Module

This module will create a backup of the database and the media folder and will upload it (Currentl only S3 is supported).

## License
[OSL v3.0](http://opensource.org/licenses/OSL-3.0)

## Contributors
* [Fabrizio Branca](https://github.com/fbrnc) (AOE)
* [Achim Rosenhagen](https://github.com/arosenhagen) (ffuenf)

## Requirements
* n98-magerun
* aws cli
* rsync
* php-cli

## Setup
* Create S3 bucket
* create AWS key that has read and write access to that S3 bucket
* make sure cron is running (use Aoe_Scheduler to verify)
