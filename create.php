<?php
/**
 * Created by PhpStorm.
 * Author: Javlon Sodikov
 * Date time: 16.06.2016 14:18
 */

include "config.php";
include "classes/mysql.php";
include "classes/createProject.php";

$framework = preg_replace('/[^a-zA-Z0-9_\.\-]/', '', $argv['1']);
$projectName = preg_replace('/[^a-zA-Z0-9_\.\-]/', '', $argv['2']);

$options['projectName'] = $projectName;

$db = new db($options['dbRoot'], $options['dbPass'], 'information_schema', $options['dbHost']);
$options['db'] = $db;
$project = new createProject($options);

switch ($framework) {
    case "yii":
    case "yii2":
        $project->yii2Install();
        $project->createDir();
        $project->createDb();
        $project->createDbUser();
        $project->registerHosts();
        $project->registerWebsite();
        $project->reloadApache();
        $project->printDbConnection();
        break;
    case "ci":
        break;
    default:
        echo "Example:\r\n create yii2 projectname";
}