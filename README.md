Image Resize API
===

Simple Web API for resize image easily.

Powered by [php-image-resize](https://github.com/gumlet/php-image-resize) library.


API URL
-----------
https://resize.sardo.work

Function
-----------
Currently, it will only resize image best to fit. Maybe add more function in the future.

Installation
-----------
You can host this file on your Web server. Support PHP 5.6 or PHP 7.

Install the dependencies:

```bash
$ composer install
```

Parameter
-----------

All of the paramter is required, otherwise server will return error message.

### ImageUrl
The url of image. Accept png, jpg and gif, and the file which not larger than 500MB.

### Width
The width of resized image. Must be integer.

### Height
The height of resized image. Must be integer.

Usage
-----------

You can call the API to resize image with post method. Currently it only accept remote image.

```js
fetch('https://apiserver.com', {
  body:JSON.stringify({
    width: 500,
    height: 300,
    imageUrl: 'http://example.com/image.png',
  }),
  headers: {
    'content-type': 'application/json',
  },
  method: 'POST',
})
.then(response => response.json())
.then((result) =>{
  console.log(result);
});
```

If everything is OK, it will return a JSON which contain status and cropped image data (Data url of resized image):

```js
{ status: 'Success',
  cropped_image_data:
   '/9j/4AAQSkZJRgABAQAAAQABAAD//gA7Q1JFQVRPUjogZ2QtanBlZyB2MS4wICh1c2luZyBJSkcgSlBFRyB2OTApLCBxdWFsaXR5ID0gODUK/9sAQwAFAwQEBAMFBAQEBQUFBgcMCAcHBwcPCwsJDBEPEhIRDxERExYcFxMUGhURERghGBodHR8fHxMXIiQiHiQcHh8e/9sAQwEFBQUHBgcOCAgOHhQRFB4eHh4eHh4eHh4eHh4eHh4eHh4eHh4eHh4eHh4eHh4eHh4eHh4eHh4eHh4eHh4eHh4e/8IAEQgBLAHWAwEiAAIRAQMRAf/EABwAAAEFAQEBAAAAAAAAAAAAAAIAAQQFBgMHCP/EABkBAQEBAQEBAAAAAAAAAAAAAAABAgMEB...' }
```
The API **will not** keep your image.

Otherwise it will return a json which contain error message. For example:
```js
{ status: 'Failed',
  error_message": 'Please provide the url of image.'}
```

Frontend Test
-----------

You can test the api function without browser, there are a pre-ready script at frontend-test folder. 
It requires Node.js (Recommanded to use v10.x.x Version) to run.

Before use the script, install the dependencies by npm, then copy the config file:
```bash
cd frontend-test
npm install
cp config.example.json config.json
```

Edit the config file:
```js
{
  "serverUrl": "Your api server url",
  "imageUrl": "Image for testing",
}
```
Then you can run it now.
```bash
node upload.js
```

Issues
-----------
If there are any bug, or request new function, please feel feel to open an issues or pull request.
