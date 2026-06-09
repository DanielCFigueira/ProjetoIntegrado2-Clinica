<?php
require_once 'autoload.php';
use App\Controllers\SiteController;

$controller = new SiteController();
$controller->index();
?>