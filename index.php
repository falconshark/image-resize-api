<?php
header("Access-Control-Allow-Origin: *");
header('Content-Type: application/json');
require 'vendor/autoload.php';
use \Gumlet\ImageResize;
use \Gumlet\ImageResizeException;

$router = new \Bramus\Router\Router();

$route->get('/', function(){
  //Check the input for GET.
  if(!isset($_GET['imageUrl'])){
    show_error('Please provide the url of image.');
    return;
  }
  if(!isset($_GET['width']) || !isset($_GET['height'])){
    show_error('Please input width and height which you want to crop to.');
    return;
  }
  if(!is_numeric($_GET['width']) || !is_numeric($_GET['height'])){
    show_error('Width and Height should be number.');
    return;
  }
}

$router->post('/', function() {
  $post_data = file_get_contents('php://input');
  $image_data = json_decode($post_data, true);

  //Check image url is existed.
  if(!isset($image_data['imageUrl'])){
    show_error('Please provide the url of image.');
    return;
  }

  $image_url = $image_data['imageUrl'];
  $image_name = './temp_files/' . pathinfo($image_url)['basename'];

  //Check file size and type, if everything is OK, download it.
  if(!check_file_ok($image_url)){
    show_error('Input file should be an image, and the size should not larger than 500MB.');
    return;
  }

  file_put_contents($image_name, fopen($image_url, 'r'));

  //Resize image to fit size.
  try{
    if(!isset($image_data['width']) || !isset($image_data['height'])){
      show_error('Please input width and height which you want to crop to.');
      return;
    }
    if(!is_numeric($image_data['width']) || !is_numeric($image_data['height'])){
      unlink($image_name);
      show_error('Width and Height should be number.');
      return;
    }
    $image = new ImageResize($image_name);
    $width = $image_data['width'];
    $height = $image_data['height'];
    $image->resizeToBestFit((int)$width, (int)$height, $allow_enlarge = TRUE);
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
  if($file_type !== 'image/png' && $file_type !== 'image/gif' && $file_type !== 'image/jpeg' && $file_type !== 'image/jpg'){
    return FALSE;
  }
  return TRUE;
}
?>
