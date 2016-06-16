<?php
/**
 * Created by PhpStorm.
 * Author: Javlon Sodikov
 * Date time: 16.06.2016 14:18
 */

include "config.php";
include "mysql.php";

class createProject
{
    private $projectsPath;
    private $os;
    private $apachePath;
    private $projectName;

    function __construct($options)
    {
        foreach ($options as $key => $val) {
            $this->{$key} = $val;
        }
        if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
            $this->os = "WIN";
            $this->apachePath = $this->apachePath ? $this->apachePath : 'D:/Ampps/apache/';
            $this->projectsPath = $this->projectsPath ? $this->projectsPath : "d:/www/";
            @mkdir($this->projectsPath);
        } else {
            $this->os = "LINUX"; // any none windows systems
            $this->projectsPath = $this->projectsPath ? $this->projectsPath : "~/www/";
            @mkdir($this->projectsPath);
        }
    }

    function reloadApache()
    {
        $this->console("Restarting apache");
        if ($this->os == "WIN") {
            if ($this->apacheRestartType == "kill") {
                exec('Taskkill /IM httpd.exe /F');
                exec('start /b ' . $this->apachePath . 'bin/httpd -d ' . $this->apachePath);
            } else {
                exec('net stop ' . $this->apacheService);
                exec('net start ' . $this->apacheService);
            }
        } else {
            exec('service ' . $this->apacheService . ' restart'); //retart| reload | gracefull etc
        }
    }

    private function console($message)
    {
        echo $message . PHP_EOL;
    }

    function registerWebsite()
    {
        $this->console("Registering website");
        $path = $this->projectsPath . $this->projectName . ($this->publicFolder ? "/" . $this->publicFolder : "");
        @mkdir($path . '/cgi-bin/', 0777, true);
        $site = '
#### ' . $this->projectName . ' VirtualHost ####


<VirtualHost 127.0.0.1:80>
    <Directory "' . $path . '">
        Options FollowSymLinks Indexes
        AllowOverride All
        Order deny,allow
        allow from All
    </Directory>
    ServerName ' . $this->projectName . '
    ServerAlias www.' . $this->projectName . '
    ScriptAlias /cgi-bin/ "' . $path . '/cgi-bin/"
    DocumentRoot "' . $path . '"
    ErrorLog "' . $this->apachePath . 'logs/' . $this->projectName . '.local.err"
    CustomLog "' . $this->apachePath . 'logs/' . $this->projectName . '.local.log" combined
</VirtualHost>

####################################
';
        file_put_contents($this->apachePath . 'conf/extra/httpd-vhosts.conf', $site, FILE_APPEND);
    }

    function registerHosts()
    {
        $this->console("Adding hostname to hosts file");
        $data = "\r\n127.0.0.1  $this->projectName\r\n";
        file_put_contents("c:/windows/system32/drivers/etc/hosts", $data, FILE_APPEND);
    }

    function createDb()
    {
        $this->console("Creating mysql database");
        $this->db->query("CREATE DATABASE " . $this->projectName . " DEFAULT CHARACTER SET utf8 DEFAULT COLLATE utf8_general_ci;");
    }

    function createDbUser()
    {
        $this->console("Creating mysql user");
        $pass = $this->createPass();
        $this->db->query("CREATE USER '" . $this->projectName . "'@'localhost' IDENTIFIED BY '" . $pass . "'");
        $this->db->query("GRANT USAGE ON *.* TO '" . $this->projectName . "'@'localhost' IDENTIFIED BY '" . $pass . "' REQUIRE NONE WITH MAX_QUERIES_PER_HOUR 0 MAX_CONNECTIONS_PER_HOUR 0 MAX_UPDATES_PER_HOUR 0 MAX_USER_CONNECTIONS 0;");
        $this->db->query("CREATE DATABASE IF NOT EXISTS `" . $this->projectName . "`;");
        $this->db->query("GRANT ALL PRIVILEGES ON `" . $this->projectName . "`.* TO '" . $this->projectName . "'@'localhost';");
        $this->db->query("GRANT ALL PRIVILEGES ON `" . $this->projectName . "%`.* TO '" . $this->projectName . "'@'localhost';");
        $this->db->query("FLUSH PRIVILEGES;");
    }

    function createPass()
    {
        $alphabet = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ1234567890';
        $pass = array(); //remember to declare $pass as an array
        $alphaLength = strlen($alphabet) - 1; //put the length -1 in cache
        for ($i = 0; $i < 8; $i++) {
            $n = rand(0, $alphaLength);
            $pass[] = $alphabet[$n];
        }
        $this->dbUserPass = implode($pass); //turn the array into a string
        return $this->dbUserPass = implode($pass);
    }

    function printDbConnection()
    {
        if (!$this->dbUserPass) {
            echo "db not created";
            return;
        }
        $file = "<?php 
        \$sqlhost='$this->dbHost';
        \$sqllogin='$this->projectName';
        \$sqlpass='$this->dbUserPass';";
        file_put_contents($this->projectPath . $this->projectName . "/connection.php", $file);
        echo PHP_EOL . PHP_EOL . $this->projectName . " - created " . PHP_EOL;
        echo "Project path :" . $this->projectsPath . $this->projectName . PHP_EOL;
        echo "Mysql user name :" . $this->projectName . PHP_EOL;
        echo "Mysql user pass :" . $this->dbUserPass . PHP_EOL;
    }

    function yii2Install()
    {
        $this->console("Installing Yii2");
        exec('composer global require "fxp/composer-asset-plugin:~1.1.1"');
        exec('cd ' . $this->projectsPath . ' & composer create-project --prefer-dist yiisoft/yii2-app-basic ' . $this->projectName);
    }
}

//$options example
//$options located in config.php
/*$options = [
    'projectPath' => 'd:/www/', // websites location
    'publicFolder' => 'web', //public folder path if it differs from project folder. If project folder and public folder located in same please leave Public fodler empty
    'apachePath' => 'D:/Ampps/apache/',//where to register virtual hosts
    'mysqlClientPath' => 'd:/Ampps/mysql/bin/mysql', //in linux just "mysql"
    'dbHost' => 'localhost', // DB host
    'dbRoot' => 'toor', //DB super user aka root
    'dbPass' => 'WDmR2uysnxyWQ8bW', //DB password
    'apacheService' => 'httpd', //Apache service name in the system
    'apacheRestartType' =>'kill' // kill|restart in windows apache can be started from command line without being registered in services
];*/


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