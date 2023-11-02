<?php

require_once(dirname(__DIR__, 1).'/vendor/autoload.php');


$dotenv = Dotenv\Dotenv::createImmutable(dirname(__DIR__, 1), '.env');
$dotenv->load();
