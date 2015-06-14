Ffuenf_Backup
=============
[![GitHub tag](https://img.shields.io/github/tag/ffuenf/Ffuenf_Backup.svg)][tag]
[![Build Status](https://img.shields.io/travis/ffuenf/Ffuenf_Backup.svg)][travis]
[![Code Quality](https://scrutinizer-ci.com/g/ffuenf/Ffuenf_Backup/badges/quality-score.png)][code_quality]
[![Code Coverage](https://scrutinizer-ci.com/g/ffuenf/Ffuenf_Backup/badges/coverage.png)][code_coverage]
[![Code Climate](https://codeclimate.com/github/ffuenf/Ffuenf_Backup/badges/gpa.svg)][codeclimate_gpa]
[![PayPal Donate](https://img.shields.io/badge/paypal-donate-blue.svg)][paypal_donate]

[tag]: https://github.com/ffuenf/Ffuenf_Backup
[travis]: https://travis-ci.org/ffuenf/Ffuenf_Backup
[code_quality]: https://scrutinizer-ci.com/g/ffuenf/Ffuenf_Backup
[code_coverage]: https://scrutinizer-ci.com/g/ffuenf/Ffuenf_Backup
[codeclimate_gpa]: https://codeclimate.com/github/ffuenf/Ffuenf_Backup
[paypal_donate]: https://www.paypal.com/cgi-bin/webscr?cmd=_s-xclick&hosted_button_id=J2PQS2WLT2Y8W&item_name=Magento%20Extension%3a%20Ffuenf_Backup&item_number=Ffuenf_Backup&currency_code=EUR

This extension will create a backup of the database and the media folder and will upload it (Currently only S3 is supported).

Requirements
------------

* n98-magerun
* aws cli
* rsync
* php-cli

Setup
-----

install aws-li
```bash
wget https://s3.amazonaws.com/aws-cli/awscli-bundle.zip -O /tmp/awscli-bundle.zip
unzip /tmp/awscli-bundle.zip
sudo ./awscli-bundle/install -i /usr/local/aws -b /usr/local/bin/aws
```

install rsync
```bash
sudo apt-get update -qq
sudo apt-get install -y rsync
```

install gnupg
```bash
sudo apt-get update -qq
sudo apt-get install -y libgpgme11-dev gnupg2
pecl install gnupg
```

* Create S3 bucket
* create AWS key that has read and write access to that S3 bucket
* make sure cron is running (use [Aoe_Scheduler](https://github.com/AOEpeople/Aoe_Scheduler) to verify)

Platform
--------

The following versions are supported and tested:

* Magento Community Edition 1.6.2.0
* Magento Community Edition 1.7.0.2
* Magento Community Edition 1.8.1.0
* Magento Community Edition 1.9.1.1

Other versions are assumed to work.

Installation
------------

Use [modman](https://github.com/colinmollenhour/modman) to install:
```
modman init
modman clone https://github.com/ffuenf/Ffuenf_Backup
```

Deinstallation
--------------

Use [modman](https://github.com/colinmollenhour/modman) to clear all files and symlinks:
```
modman clean Ffuenf_Backup
```
see `uninstall.sql` to clear all entries of this extension from your database.

Development
-----------
1. Fork the repository from GitHub.
2. Clone your fork to your local machine:

        $ git clone https://github.com/USER/Ffuenf_Backup

3. Create a git branch

        $ git checkout -b my_bug_fix

4. Make your changes/patches/fixes, committing appropriately
5. Push your changes to GitHub
6. Open a Pull Request

Credits
-------

* [Fabrizio Branca](https://github.com/fbrnc) (AOE)

License and Author
------------------

- Author:: Achim Rosenhagen (<a.rosenhagen@ffuenf.de>)
- Copyright:: 2015, ffuenf

The MIT License (MIT)

Permission is hereby granted, free of charge, to any person obtaining a copy
of this software and associated documentation files (the "Software"), to deal
in the Software without restriction, including without limitation the rights
to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
copies of the Software, and to permit persons to whom the Software is
furnished to do so, subject to the following conditions:

The above copyright notice and this permission notice shall be included in all
copies or substantial portions of the Software.

THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE
SOFTWARE.
