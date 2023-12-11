<?php

$container_laminas_auto = require 'config/container.php';

$dataBaseAdapter = $container_laminas_auto->get("mainDbUnitTest");

return $dataBaseAdapter;