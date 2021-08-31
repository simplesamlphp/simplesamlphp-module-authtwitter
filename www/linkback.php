<?php

namespace SimpleSAML\Module\authtwitter;

use SimpleSAML\Configuration;
use SimpleSAML\Session;
use Symfony\Component\HttpFoundation\Request;

$config = Configuration::getInstance();
$session = Session::getSessionFromRequest();
$request = Request::createFromGlobals();

$controller = new Controller\Twitter($config, $session);
$response = $controller->linkback($request);
$response->send();
