<?php

include '../config/const.php';

use Controllers\BusController;

$api = new BusController();
print $api->run();
