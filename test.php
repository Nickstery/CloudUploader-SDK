<?php

require_once 'vendor/autoload.php';


phpinfo();

$dotenv = new Dotenv\Dotenv(__DIR__);
$dotenv->load();


$s3_bucket = $_ENV['test'];

print_r($s3_bucket);

