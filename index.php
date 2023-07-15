<?php
header("Access-Control-Allow-Origin: *");
require 'vendor/autoload.php';

use \Gumlet\ImageResize;
use \Gumlet\ImageResizeException;

$router = new \Bramus\Router\Router();

/* Get Method */
$router->get('/', function(){
  $image_url = isset($_GET['imageUrl']) ? $_GET['imageUrl'] : null;
  $width = isset($_GET['width']) ? $_GET['width'] : null;
  $height = isset($_GET['height']) ? $_GET['height'] : null;
  
  //Check the input for GET.
  if(!$image_url){
    show_error('Please provide the url of image.');
    return;
  }

  if(!$width && !$height){
    show_error('Please input width or height which you want to crop to.');
    return;
  }

  if($width && !is_numeric($width) || $height && !is_numeric($height)){
    show_error('Width or Height should be number.');
    return;
  }

  $folder_path = './temp-files/';
  if (!is_dir($folder_path)) mkdir($folder_path, 0777, true);

  $image_name = $folder_path . pathinfo($image_url)['basename'];

  //Check file size and type, if everything is OK, download it.
  if(!check_file_ok($image_url)){
    show_error('Input file should be an image, and the size should not larger than 500MB.');
    return;
  }

  file_put_contents($image_name, fopen($image_url, 'r'));

  try{
    $image = new ImageResize($image_name);
    if($width && $height){
      $image->resizeToBestFit((int)$width, (int)$height, $allow_enlarge = TRUE);
    } else {
      if($width) $image->resizeToWidth((int)$width, $allow_enlarge = TRUE);
      if($height) $image->resizeToHeight((int)$height, $allow_enlarge = TRUE);
    }
    $image->save($image_name);
    $type = pathinfo($image_name, PATHINFO_EXTENSION);

    header("Content-Type: image/{$type}");
    header("Content-Length: " . strlen(file_get_contents($image_name)));
    header("Cache-Control: public", true);
    header("Pragma: public", true);
    echo file_get_contents($image_name);
    unlink($image_name);
  } catch (ImageResizeException $e) {
    unlink($image_name);
    show_error($e->getMessage());
  }
});


/* Post method */

$router->post('/', function() {
  header('Content-Type: application/json');
  $post_data = file_get_contents('php://input');
  $image_data = json_decode($post_data, true);

  //Check image url is existed.
  if(!isset($image_data['imageUrl'])){
    show_error('Please provide the url of image.');
    return;
  }

  $folder_path = './temp_files/';
  if (!is_dir($folder_path)) mkdir($folder_path, 0777, true);

  $image_url = $image_data['imageUrl'];
  $image_name = $folder_path . pathinfo($image_url)['basename'];

  //Check file size and type, if everything is OK, download it.
  if(!check_file_ok($image_url)){
    show_error('Input file should be an image, and the size should not larger than 500MB.');
    return;
  }

  file_put_contents($image_name, fopen($image_url, 'r'));

  //Resize image to fit size.
  try{
  $width = isset($image_data['width']) ? $image_data['width'] : null;
  $height = isset($image_data['height']) ? $image_data['height'] : null;
  
    if(!$width && !$height){
      show_error('Please input width or height which you want to crop to.');
      return;
    }
    if($width && !is_numeric($width) || $height && !is_numeric($height)){
      unlink($image_name);
      show_error('Width and Height should be number.');
      return;
    }
    $image = new ImageResize($image_name);
    if($width && $height){
      $image->resizeToBestFit((int)$width, (int)$height, $allow_enlarge = TRUE);
    } else {
      if($width) $image->resizeToWidth((int)$width, $allow_enlarge = TRUE);
      if($height) $image->resizeToHeight((int)$height, $allow_enlarge = TRUE);
    }
    $image->save($image_name);
    $type = pathinfo($image_name, PATHINFO_EXTENSION);
    $result = [
      'status' => 'Success',
      'cropped_image_data' => 'data:image/' . $type . ';base64,' . base64_encode(file_get_contents($image_name)),
    ];
    unlink($image_name);
    echo json_encode($result);

  } catch (ImageResizeException $e) {
    unlink($image_name);
    show_error($e->getMessage());
  }
});

$router->run();

/**
* Function for show error mesage in JSON format.
*
* @param string $message The error message which should be displayed.
*/
function show_error($message){
  $result = [
    'status' => 'Failed',
    'error_message' => $message,
  ];
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
  if($file_type !== 'image/png' && $file_type !== 'image/gif' && $file_type !== 'image/jpeg' && $file_type !== 'image/jpg' && $file_type !== 'image/webp'){
    return FALSE;
  }
  return TRUE;
}
