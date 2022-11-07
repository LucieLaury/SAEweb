<?php

use iutnc\netVOD\db\ConnectionFactory;
use iutnc\netVOD\dispatcher\Dispatcher;

require_once "vendor/autoload.php";

ConnectionFactory::setConfig("DBConfig.ini");
$action = (isset($_GET['action'])) ? $_GET['action'] : "";
$dispatch = new Dispatcher($action);
$dispatch->run();