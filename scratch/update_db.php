<?php
require 'c:/xampp/htdocs/calibration certificate/includes/config.php';
$db = getDB();
$db->query("UPDATE certificate_counter SET prefix = 'ICM' WHERE instrument_type_id = 13 AND prefix = 'CM'");
echo "DB prefix updated!\n";
