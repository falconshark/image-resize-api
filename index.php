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
  $quality = isset($_GET['quality']) ? $_GET['quality'] : null;
  
  //Check the input for GET.
  if(!$image_url){
    show_error('Please provide the url of image.');
    return;
  }

  if(!$width && !$height){
    show_error('Please input width or height which you want to resize to.');
    return;
  }

  if($width && !is_numeric($width) || $height && !is_numeric($height) || $quality && !is_numeric($quality)){
    show_error('Width, Height, and Quality should be number.');
    return;
  }

  //Check file size and type, if everything is OK, download it.
  if(!check_file_ok($image_url)){
    show_error('Input file should be an image, and the size should not larger than 500MB.');
    return;
  }

  $folder_path = './temp-files/';
  if (!is_dir($folder_path)) mkdir($folder_path, 0777, true);

  $image_info = pathinfo(parse_url($image_url, PHP_URL_PATH));
  $image_path = $folder_path . $image_info['basename'];
  file_put_contents($image_path, fopen($image_url, 'r'));

  try{
    $image = new ImageResize($image_path);
    if($width && $height){
      $image->resizeToBestFit((int)$width, (int)$height, $allow_enlarge = TRUE);
    } else {
      if($width) $image->resizeToWidth((int)$width, $allow_enlarge = TRUE);
      if($height) $image->resizeToHeight((int)$height, $allow_enlarge = TRUE);
    }

    if(preg_match('/jpe?g|webp/', $image_info['extension']) && $quality){
      $image->save($image_path, null, (int)$quality);
    } else {
      $image->save($image_path);
    }
    $type = pathinfo($image_path, PATHINFO_EXTENSION);

    $image_content = file_get_contents($image_path);
    header("Content-Type: image/{$type}");
    header('Content-disposition: inline; filename=' . $image_info['filename'] . ".$type");
    header("Content-Length: " . strlen($image_content));
    header("Cache-Control: public", true);
    header("Pragma: public", true);
    echo $image_content;
    unlink($image_path);

  } catch (ImageResizeException $e) {
    unlink($image_path);
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

  $image_url = $image_data['imageUrl'];

  //Check file size and type, if everything is OK, download it.
  if(!check_file_ok($image_url)){
    show_error('Input file should be an image, and the size should not larger than 500MB.');
    return;
  }

  $folder_path = './temp_files/';
  if (!is_dir($folder_path)) mkdir($folder_path, 0777, true);

  $image_info = pathinfo(parse_url($image_url, PHP_URL_PATH));
  $image_path = $folder_path . pathinfo(parse_url($image_url, PHP_URL_PATH), PATHINFO_BASENAME);
  file_put_contents($image_path, fopen($image_url, 'r'));

  //Resize image to fit size.
  try{
    $width = isset($image_data['width']) ? $image_data['width'] : null;
    $height = isset($image_data['height']) ? $image_data['height'] : null;
    $quality = isset($image_data['quality']) ? $image_data['quality'] : null;
  
    if(!$width && !$height){
      show_error('Please input width or height which you want to resize to.');
      return;
    }
    if($width && !is_numeric($width) || $height && !is_numeric($height) || $quality && !is_numeric($quality)){
      unlink($image_path);
      show_error('Width, Height, and Quality should be number.');
      return;
    }

    $image = new ImageResize($image_path);
    if($width && $height){
      $image->resizeToBestFit((int)$width, (int)$height, $allow_enlarge = TRUE);
    } else {
      if($width) $image->resizeToWidth((int)$width, $allow_enlarge = TRUE);
      if($height) $image->resizeToHeight((int)$height, $allow_enlarge = TRUE);
    }

    if(preg_match('/jpe?g|webp/', $image_info['extension']) && $quality){
      $image->save($image_path, null, (int)$quality);
    } else {
      $image->save($image_path);
    }
    $type = pathinfo($image_path, PATHINFO_EXTENSION);

    $result = [
      'status' => 'Success',
      'filename' => $image_info['filename'] . ".$type",
      'cropped_image_data' => 'data:image/' . $type . ';base64,' . base64_encode(file_get_contents($image_path)),
    ];
    unlink($image_path);
    echo json_encode($result);

  } catch (ImageResizeException $e) {
    unlink($image_path);
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
  $headers = array_change_key_case(get_headers($image_url, true), CASE_LOWER);

  //if response code not 200, return RESPONSE CODE
  if(substr(array_values($headers)[0], 9, 3) != '200'){
    show_error(array_values($headers)[0]);
    exit;
  }

  $file_size = $headers['content-length'];
  $file_type = $headers['content-type'];

  //If the file more then 500MB, return FALSE
  if(!$file_size || $file_size > 500000000){
    return FALSE;
  }
  //If not image, return FALSE
  if(!preg_match('/image\/(png|jpe?g|gif|webp)/', $file_type)){
    return FALSE;
  }
  return TRUE;
}
