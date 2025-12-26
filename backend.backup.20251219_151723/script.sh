#!/bin/bash
(crontab -l | grep -v "/usr/bin/php /Applications/MAMP/htdocs/Backend-habiibii/artisan dm:disbursement") | crontab -

(crontab -l | grep -v "/usr/bin/php /Applications/MAMP/htdocs/Backend-habiibii/artisan store:disbursement") | crontab -

