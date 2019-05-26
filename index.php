<?php
header("Access-Control-Allow-Origin: *");
header('Content-Type: application/json');
require 'vendor/autoload.php';
use \Gumlet\ImageResize;
use \Gumlet\ImageResizeException;

$post_data = file_get_contents('php://input');
$image_data = json_decode($post_data, true);
$image_url = $image_data['imageUrl'];
$image_name = pathinfo($image_url)['basename'];

//Check image url is existed.
if(!isset($image_data['imageUrl'])){
  $result = [
    'status' => 'Failed',
    'error_message' => 'Please provide the url of image.',
  ];
  echo json_encode($result);
  return;
}

//Check file size and type, if everything is OK, download it.
if(!check_file_ok($image_url)){
  $result = [
    'status' => 'Failed',
    'error_message' => 'Input file should be an image, and the size should not larger than 500MB.',
  ];
  echo json_encode($result);
  return;
}
file_put_contents($image_name, fopen($image_url, 'r'));

//Resize image to fit size.
try{
  $image = new ImageResize($image_name);

  if(!isset($image_data['width']) || !isset($image_data['height'])){
    $result = [
      'status' => 'Failed',
      'error_message' => 'Please input width and height which you want to crop to.',
    ];
    unlink($image_name);
    echo json_encode($result);
    return;
  }
  if(!is_numeric($image_data['width']) || !is_numeric($image_data['height'])){
    $result = [
      'status' => 'Failed',
      'error_message' => 'Width and Height should be number.',
    ];
    unlink($image_name);
    echo json_encode($result);
    return;
  }

  $width = $image_data['width'];
  $height = $image_data['height'];
  $image->resizeToBestFit((int)$width, (int)$height, $allow_enlarge = TRUE);
  $image->save($image_name);
  $result = [
    'status' => 'Success',
    'cropped_image_data' => base64_encode(file_get_contents($image_name)),
  ];
  echo json_encode($result);
  unlink($image_name);
} catch (ImageResizeException $e) {
  $result = [
    'status' => 'Failed',
    'error_message' => $e->getMessage(),
  ];
  unlink($image_name);
  echo json_encode($result);
}

/**
* Function for check received file before download it.
*
* @param string $image_url The url of image.
*/
function check_file_ok($image_url){
  $headers = get_headers($image_url, true);
  $file_size = $headers['Content-Length'];
  $file_type = $headers['Content-Type'];

  //If the file more then 500MB, return FALSE
  if(!$file_size || $file_size > 500000000){
    return FALSE;
  }
  //If not image, return FALSE
  if($file_type !== 'image/png' && $file_type !== 'image/gif' && $file_type !== 'image/jpeg' && $file_type !== 'image/jpg'){
    return FALSE;
  }
  return TRUE;
}
?>
