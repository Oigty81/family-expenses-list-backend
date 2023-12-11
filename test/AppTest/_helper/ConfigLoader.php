<?php

$container_laminas_auto = require 'config/container.php';

$config = $container_laminas_auto->get("config")["config"];

return $config;