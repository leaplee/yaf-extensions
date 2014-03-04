<?php

define("APP_PATH", dirname(__FILE__) . '/../application');
define("DATA_PATH", dirname(__FILE__) . '/../runtimes/');

//require "functions.php";

$app = new Yaf_Application(APP_PATH . "/conf/application.ini");
$app->bootstrap()->run();