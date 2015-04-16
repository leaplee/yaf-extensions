<?php

define("APP_PATH", dirname(__FILE__) . '/../application');
define("RUNTIME_PATH", dirname(__FILE__) . '/../runtimes/');

$app = new Yaf_Application(APP_PATH . "/conf/application.ini");
$app->bootstrap()->run();