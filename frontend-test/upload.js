/* This script is for testing upload function without broswer. */
const fs = require('fs');
const fetch = require('node-fetch');
const settingFile = fs.readFileSync('./setting.json');
const setting = JSON.parse(settingFile);
fetch(setting.serverUrl, {
  body:JSON.stringify({
    imageUrl: 'https://s3-ap-southeast-1.amazonaws.com/sardo-website/FRhZ7Gk2pzCY2gkruIJNbTqhj6RR8bFjCsrZWbL3.jpeg',
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
