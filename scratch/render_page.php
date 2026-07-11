<?php
session_start();
$_SESSION['user_id'] = 1;
$_GET['embed'] = 'true';
$_SERVER['REQUEST_METHOD'] = 'GET';
$_SERVER['HTTP_HOST'] = 'localhost';
$_SERVER['REQUEST_URI'] = '/calibration%20certificate/certificates/' . $argv[1];

include __DIR__ . '/../certificates/' . $argv[1];
