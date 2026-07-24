<?php
session_start();

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);


require_once '../app/Config/Database.php';
require_once '../app/Core/Model.php';
require_once '../app/Core/Controller.php';
require_once '../app/Core/App.php';
require_once '../app/bootstrap.php';

$app = new App();