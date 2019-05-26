<?php
header("Access-Control-Allow-Origin: *");
header('Content-Type: application/json');
require 'vendor/autoload.php';
use \Gumlet\ImageResize;

echo json_encode([
  'Hello' => 'Hello World',
], JSON_PRETTY_PRINT);
 ?>
