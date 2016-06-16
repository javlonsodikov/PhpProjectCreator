<?php
/**
 * Created by PhpStorm.
 * Author: Javlon Sodikov
 * Date time: 16.06.2016 14:18
 */


$options = [
    'projectPath' => 'd:/www/', // websites location
    'publicFolder' => 'web', //public folder path if it differs from project folder. If project folder and public folder located in same please leave Public fodler empty
    'apachePath' => 'D:/Ampps/apache/',//where to register virtual hosts
    'mysqlClientPath' => 'd:/Ampps/mysql/bin/mysql', //in linux just "mysql"
    'dbHost' => 'localhost', // DB host
    'dbRoot' => 'toor', //DB super user aka root
    'dbPass' => 'WDmR2uysnxyWQ8bW', //DB password
    'apacheService' => 'httpd', //Apache service name in the system
    'apacheRestartType' =>'kill' // kill|restart in windows apache can be started from command line without being registered in services
];
